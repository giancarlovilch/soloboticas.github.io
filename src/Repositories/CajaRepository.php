<?php

require_once __DIR__ . '/../Core/Database.php';

class CajaRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // ── Catálogos ──────────────────────────────────────────
    public function getLocales(): array
    {
        return $this->db->query(
            "SELECT id_local AS id, descripcion FROM local WHERE activo = 1 ORDER BY descripcion"
        )->fetchAll();
    }

    public function getCajasByLocal(int $localId): array
    {
        $stmt = $this->db->prepare(
            "SELECT id_caja AS id, descripcion FROM caja WHERE local_id = :lid AND activo = 1 ORDER BY descripcion"
        );
        $stmt->execute(['lid' => $localId]);
        return $stmt->fetchAll();
    }

    public function getTurnos(): array
    {
        return $this->db->query(
            "SELECT id_turno AS id, descripcion FROM turno WHERE activo = 1 ORDER BY id_turno"
        )->fetchAll();
    }

    public function getConceptosGasto(): array
    {
        return $this->db->query(
            "SELECT id_concepto AS id, descripcion FROM concepto_gastos_local WHERE activo = 1 ORDER BY descripcion"
        )->fetchAll();
    }

    public function getStaffActivo(): array
    {
        return $this->db->query(
            "SELECT p.id_postulante AS id, p.nombres AS nombre_completo
             FROM postulante p INNER JOIN usuario u ON p.id_postulante = u.postulante_id
             WHERE u.activo = 1 ORDER BY p.nombres"
        )->fetchAll();
    }

    // ── Saldo base del día anterior para una caja ──────────
    // Toma el saldo_proximo_dia del último arqueo cerrado o aprobado que tenga detalle.
    // Un ajuste de cierre (rectificación) ya modifica saldo_proximo_dia directamente.
    public function getSaldoBase(int $cajaId): float
    {
        $stmt = $this->db->prepare(
            "SELECT dc.saldo_proximo_dia
             FROM sesion_caja sc
             INNER JOIN detalle_cuadre dc ON sc.id_sesion = dc.sesion_id
             WHERE sc.caja_id = :cid
               AND sc.estado IN ('APROBADA','CERRADA')
             ORDER BY sc.id_sesion DESC
             LIMIT 1"
        );
        $stmt->execute(['cid' => $cajaId]);
        return (float)($stmt->fetchColumn() ?? 0);
    }

    // ── Sesiones ───────────────────────────────────────────
    public function crearSesion(array $data): int
    {
        $saldo = $this->getSaldoBase((int)$data['caja_id']);

        $stmt = $this->db->prepare(
            "INSERT INTO sesion_caja
                (caja_id, turno_id, postulante_apertura_id, estado, saldo_inicial, fecha_operacion)
             VALUES (:caja, :turno, :apertura, 'ABIERTA', :saldo, CURDATE())"
        );
        $stmt->execute([
            'caja'     => $data['caja_id'],
            'turno'    => $data['turno_id'],
            'apertura' => $data['postulante_id'],
            'saldo'    => $saldo,
        ]);
        $id = (int)$this->db->lastInsertId();

        // Cajera responsable
        $this->db->prepare(
            "INSERT INTO sesion_participante (sesion_id, postulante_id, rol_participacion, responsable_faltante)
             VALUES (:sid, :pid, 'CAJERA', 1)"
        )->execute(['sid' => $id, 'pid' => $data['postulante_id']]);

        // Vendedor/a si se indicó
        if (!empty($data['vendedor_id'])) {
            $this->db->prepare(
                "INSERT INTO sesion_participante (sesion_id, postulante_id, rol_participacion, responsable_faltante)
                 VALUES (:sid, :pid, 'VENDEDORA', 0)"
            )->execute(['sid' => $id, 'pid' => $data['vendedor_id']]);
        }

        return $id;
    }

    public function getSesionById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT sc.*,
                    c.descripcion  AS caja_desc,
                    l.id_local, l.descripcion AS local_desc,
                    t.descripcion  AS turno_desc,
                    p.nombres AS cajera_nombre
             FROM sesion_caja sc
             INNER JOIN caja c ON sc.caja_id = c.id_caja
             INNER JOIN local l ON c.local_id = l.id_local
             INNER JOIN turno t ON sc.turno_id = t.id_turno
             INNER JOIN postulante p ON sc.postulante_apertura_id = p.id_postulante
             WHERE sc.id_sesion = :id"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /** Lista de sesiones con filtro de estado */
    public function getSesionesByEstado(string $estado): array
    {
        $stmt = $this->db->prepare(
            "SELECT sc.id_sesion, sc.estado, sc.saldo_inicial, sc.fecha_operacion,
                    c.descripcion AS caja_desc, l.descripcion AS local_desc,
                    t.descripcion AS turno_desc,
                    p.nombres AS cajera_nombre
             FROM sesion_caja sc
             INNER JOIN caja c ON sc.caja_id = c.id_caja
             INNER JOIN local l ON c.local_id = l.id_local
             INNER JOIN turno t ON sc.turno_id = t.id_turno
             INNER JOIN postulante p ON sc.postulante_apertura_id = p.id_postulante
             WHERE sc.estado = :estado
             ORDER BY sc.fecha_operacion DESC, sc.fecha_apertura DESC"
        );
        $stmt->execute(['estado' => $estado]);
        return $stmt->fetchAll();
    }

    /** Sesiones recientes con filtros de local y mes */
    public function getSesionesRecientes(int $localId = 0, string $mes = ''): array
    {
        if (!$mes) $mes = date('Y-m');
        [$anio, $nmes] = explode('-', $mes);
        $desdeFecha = "{$anio}-{$nmes}-01";
        $hastaFecha = date('Y-m-t', strtotime($desdeFecha));

        $where  = "sc.fecha_operacion BETWEEN :desde AND :hasta";
        $params = ['desde' => $desdeFecha, 'hasta' => $hastaFecha];

        if ($localId > 0) {
            $where .= " AND l.id_local = :lid";
            $params['lid'] = $localId;
        }

        $sql = "SELECT sc.id_sesion, sc.estado, sc.saldo_inicial,
                       sc.fecha_operacion, sc.fecha_apertura,
                       c.descripcion AS caja_desc, l.id_local,
                       l.descripcion AS local_desc,
                       t.descripcion AS turno_desc,
                       p.nombres AS cajera_nombre,
                       pv.nombres AS vendedor_nombre,
                       (SELECT prev.id_sesion
                        FROM sesion_caja prev
                        INNER JOIN caja pc ON prev.caja_id = pc.id_caja
                        WHERE pc.id_caja = sc.caja_id
                          AND prev.id_sesion < sc.id_sesion
                        ORDER BY prev.id_sesion DESC LIMIT 1
                       ) AS sesion_anterior_id
                FROM sesion_caja sc
                INNER JOIN caja c ON sc.caja_id = c.id_caja
                INNER JOIN local l ON c.local_id = l.id_local
                INNER JOIN turno t ON sc.turno_id = t.id_turno
                INNER JOIN postulante p ON sc.postulante_apertura_id = p.id_postulante
                LEFT JOIN sesion_participante sp ON sp.sesion_id = sc.id_sesion
                    AND sp.rol_participacion = 'VENDEDORA'
                LEFT JOIN postulante pv ON sp.postulante_id = pv.id_postulante
                WHERE {$where}
                ORDER BY sc.fecha_operacion DESC, sc.fecha_apertura DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ── Detalle cuadre — activos físicos (LO QUE ES) ──────
    // LO QUE ES = efectivo físico + saldo agente BCP.
    // Este total se convierte en la base del día siguiente.
    // Los pagos digitales (yape/visa/etc.) van en movimiento_sesion y
    // RESTAN de "LO QUE SE DICE" (no de lo físico).
    public function upsertDetalleCuadre(int $sesionId, array $activos): void
    {
        $d   = fn($v) => ($v === '' || $v === null) ? 0.00 : round((float)$v, 2);
        $int = fn($v) => ($v === '' || $v === null) ? 0 : (int)$v;

        // LO QUE ES = suma de todo el efectivo físico + saldo agente BCP
        $loQueEs = $d($activos['caja_exterior']) + $d($activos['monedas'])
                 + $d($activos['billetes'])      + $d($activos['caja_fuerte'])
                 + $d($activos['agente_bcp']);

        $numOps = $int($activos['num_operaciones_bcp'] ?? 0);

        $sql = "INSERT INTO detalle_cuadre
                    (sesion_id, monto_caja_exterior, monto_monedas, monto_billetes_caja,
                     monto_billetes_caja_fuerte, monto_agente_bcp,
                     total_efectivo_contado, saldo_proxima_efectivo, saldo_proximo_dia)
                VALUES
                    (:sid, :ext, :mon, :bil, :fut, :agb, :tef, :spef, :spd)
                ON DUPLICATE KEY UPDATE
                    monto_caja_exterior        = VALUES(monto_caja_exterior),
                    monto_monedas              = VALUES(monto_monedas),
                    monto_billetes_caja        = VALUES(monto_billetes_caja),
                    monto_billetes_caja_fuerte = VALUES(monto_billetes_caja_fuerte),
                    monto_agente_bcp           = VALUES(monto_agente_bcp),
                    total_efectivo_contado     = VALUES(total_efectivo_contado),
                    saldo_proxima_efectivo     = VALUES(saldo_proxima_efectivo),
                    saldo_proximo_dia          = VALUES(saldo_proximo_dia)";

        $this->db->prepare($sql)->execute([
            'sid'  => $sesionId,
            'ext'  => $d($activos['caja_exterior']),
            'mon'  => $d($activos['monedas']),
            'bil'  => $d($activos['billetes']),
            'fut'  => $d($activos['caja_fuerte']),
            'agb'  => $d($activos['agente_bcp']),
            'tef'  => $loQueEs,
            'spef' => $loQueEs,
            'spd'  => $loQueEs, // base día siguiente = LO QUE ES (físico + BCP agente)
        ]);

        // Guardar número de operaciones BCP (columna opcional)
        try {
            $this->db->prepare(
                "UPDATE detalle_cuadre SET num_operaciones_bcp = :ops WHERE sesion_id = :sid"
            )->execute(['ops' => $numOps, 'sid' => $sesionId]);
        } catch (\PDOException) { /* columna aún no existe en BD antigua */ }
    }

    // ── Pagos digitales individuales (movimiento_sesion INGRESO) ──
    public function addPagoDigital(int $sesionId, int $registradorId, int $modoId, float $monto, ?string $numOp): int
    {
        $this->db->prepare(
            "INSERT INTO movimiento_sesion
                (sesion_id, tipo_movimiento_id, modo_id, postulante_registro_id, monto, numero_operacion, estado)
             VALUES (:sid, 1, :modo, :reg, :mon, :num, 'PENDIENTE')"
        )->execute(['sid' => $sesionId, 'modo' => $modoId, 'reg' => $registradorId, 'mon' => $monto, 'num' => $numOp]);
        return (int)$this->db->lastInsertId();
    }

    public function getPagosDigitalesBySesion(int $sesionId): array
    {
        $stmt = $this->db->prepare(
            "SELECT ms.id_movimiento, ms.monto, ms.numero_operacion, ms.estado,
                    ms.fecha_movimiento, m.descripcion AS modo_desc, m.id_modo
             FROM movimiento_sesion ms
             INNER JOIN modo m ON ms.modo_id = m.id_modo
             WHERE ms.sesion_id = :sid AND ms.tipo_movimiento_id = 1
             ORDER BY ms.fecha_movimiento DESC"
        );
        $stmt->execute(['sid' => $sesionId]);
        return $stmt->fetchAll();
    }

    public function deletePagoDigital(int $movId): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM movimiento_sesion
             WHERE id_movimiento = :id AND tipo_movimiento_id = 1 AND estado = 'PENDIENTE'"
        );
        $stmt->execute(['id' => $movId]);
        return $stmt->rowCount() > 0;
    }

    public function confirmarPagoDigital(int $movId, int $revisorId, string $estado): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE movimiento_sesion
             SET estado = :est, postulante_revision_id = :rev, fecha_revision = NOW()
             WHERE id_movimiento = :id AND tipo_movimiento_id = 1"
        );
        $stmt->execute(['est' => $estado, 'rev' => $revisorId, 'id' => $movId]);
        return $stmt->rowCount() > 0;
    }

    /** Todos los pagos digitales para la vista del supervisor */
    public function getAllPagosDigitales(string $estado = '', int $localId = 0, int $cajaId = 0): array
    {
        $where  = "ms.tipo_movimiento_id = 1";
        $params = [];
        if ($estado) {
            $where .= " AND ms.estado = :est";
            $params['est'] = $estado;
        }
        if ($localId > 0) {
            $where .= " AND l.id_local = :lid";
            $params['lid'] = $localId;
        }
        if ($cajaId > 0) {
            $where .= " AND sc.caja_id = :cid";
            $params['cid'] = $cajaId;
        }
        $sql = "SELECT ms.id_movimiento, ms.monto, ms.numero_operacion, ms.estado,
                       ms.fecha_movimiento, m.descripcion AS modo_desc,
                       sc.id_sesion, sc.fecha_operacion,
                       c.descripcion AS caja_desc, l.descripcion AS local_desc,
                       p.nombres AS cajera_nombre,
                       pv.nombres AS vendedor_nombre
                FROM movimiento_sesion ms
                INNER JOIN modo m ON ms.modo_id = m.id_modo
                INNER JOIN sesion_caja sc ON ms.sesion_id = sc.id_sesion
                INNER JOIN caja c ON sc.caja_id = c.id_caja
                INNER JOIN local l ON c.local_id = l.id_local
                INNER JOIN postulante p ON sc.postulante_apertura_id = p.id_postulante
                LEFT JOIN sesion_participante sp ON sp.sesion_id = sc.id_sesion
                    AND sp.rol_participacion = 'VENDEDORA'
                LEFT JOIN postulante pv ON sp.postulante_id = pv.id_postulante
                WHERE {$where}
                ORDER BY ms.fecha_movimiento DESC
                LIMIT 500";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Total de pagos digitales aprobados para la sesión (se usa en el cuadre) */
    public function sumDigitalAprobado(int $sesionId): float
    {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM(monto),0) FROM movimiento_sesion
             WHERE sesion_id = :sid AND tipo_movimiento_id = 1 AND estado = 'APROBADO'"
        );
        $stmt->execute(['sid' => $sesionId]);
        return (float)$stmt->fetchColumn();
    }

    /** Suma todos los digitales declarados (PENDIENTE + APROBADO).
     *  Los RECHAZADO no cuentan: se probó que eran falsos y no restan del efectivo esperado. */
    public function sumDigitalDeclarado(int $sesionId): float
    {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM(monto),0) FROM movimiento_sesion
             WHERE sesion_id = :sid AND tipo_movimiento_id = 1
               AND estado IN ('PENDIENTE','APROBADO')"
        );
        $stmt->execute(['sid' => $sesionId]);
        return (float)$stmt->fetchColumn();
    }

    // ── Gastos ─────────────────────────────────────────────
    /** Borra todos los gastos variables de la sesión (para re-insertar en update) */
    public function clearGastosSesion(int $sesionId): void
    {
        $this->db->prepare("DELETE FROM movimiento_sesion WHERE sesion_id = :sid AND tipo_movimiento_id = 2")
                 ->execute(['sid' => $sesionId]);
        $this->db->prepare("DELETE FROM pago_personal WHERE sesion_id = :sid")
                 ->execute(['sid' => $sesionId]);
        $this->db->prepare("DELETE FROM pago_local WHERE sesion_id = :sid")
                 ->execute(['sid' => $sesionId]);
    }

    public function insertGastoPersonal(int $sesionId, int $emisorId, int $beneficiarioId, float $monto, ?string $comprobante): void
    {
        $this->db->prepare(
            "INSERT INTO pago_personal (sesion_id, postulante_emisor_id, postulante_beneficiario_id, monto, numero_operacion, estado)
             VALUES (:sid, :emi, :ben, :mon, :comp, 'PAGADO')"
        )->execute(['sid' => $sesionId, 'emi' => $emisorId, 'ben' => $beneficiarioId, 'mon' => $monto, 'comp' => $comprobante]);
    }

    public function insertGastoLocal(int $sesionId, int $localId, int $emisorId, int $conceptoId, float $monto, ?string $comprobante): void
    {
        $this->db->prepare(
            "INSERT INTO pago_local (sesion_id, local_id, postulante_emisor_id, concepto_id, monto, numero_operacion, estado)
             VALUES (:sid, :loc, :emi, :con, :mon, :comp, 'APROBADO')"
        )->execute(['sid' => $sesionId, 'loc' => $localId, 'emi' => $emisorId, 'con' => $conceptoId, 'mon' => $monto, 'comp' => $comprobante]);
    }

    public function insertGastoOtro(int $sesionId, int $registradorId, string $descripcion, float $monto, ?string $comprobante): void
    {
        // Otros gastos van a movimiento_sesion como EGRESO en modo EFECTIVO
        $this->db->prepare(
            "INSERT INTO movimiento_sesion (sesion_id, tipo_movimiento_id, modo_id, postulante_registro_id, descripcion, monto, numero_operacion, estado)
             VALUES (:sid, 2, 1, :reg, :desc, :mon, :comp, 'APROBADO')"
        )->execute(['sid' => $sesionId, 'reg' => $registradorId, 'desc' => $descripcion, 'mon' => $monto, 'comp' => $comprobante]);
    }

    // ── Resumen de gastos de la sesión ─────────────────────
    public function getGastosSesion(int $sesionId): array
    {
        $gastos = [];

        // Pagos personal
        $stmt = $this->db->prepare(
            "SELECT 'PERSONAL' AS tipo, pp.id_pago_personal AS id, pp.monto, pp.numero_operacion AS comprobante,
                    pb.nombres AS descripcion, pp.postulante_beneficiario_id AS ref_id
             FROM pago_personal pp
             INNER JOIN postulante pb ON pp.postulante_beneficiario_id = pb.id_postulante
             WHERE pp.sesion_id = :sid"
        );
        $stmt->execute(['sid' => $sesionId]);
        $gastos = array_merge($gastos, $stmt->fetchAll());

        // Pagos local
        $stmt = $this->db->prepare(
            "SELECT 'LOCAL' AS tipo, pl.id_pago_local AS id, pl.monto, pl.numero_operacion AS comprobante,
                    cg.descripcion, pl.concepto_id AS ref_id
             FROM pago_local pl
             INNER JOIN concepto_gastos_local cg ON pl.concepto_id = cg.id_concepto
             WHERE pl.sesion_id = :sid"
        );
        $stmt->execute(['sid' => $sesionId]);
        $gastos = array_merge($gastos, $stmt->fetchAll());

        // Otros movimientos EGRESO
        $stmt = $this->db->prepare(
            "SELECT 'OTRO' AS tipo, ms.id_movimiento AS id, ms.monto, ms.numero_operacion AS comprobante,
                    ms.descripcion, NULL AS ref_id
             FROM movimiento_sesion ms
             WHERE ms.sesion_id = :sid AND ms.tipo_movimiento_id = 2"
        );
        $stmt->execute(['sid' => $sesionId]);
        $gastos = array_merge($gastos, $stmt->fetchAll());

        return $gastos;
    }

    public function sumGastosSesion(int $sesionId): float
    {
        $gastos = $this->getGastosSesion($sesionId);
        return array_sum(array_column($gastos, 'monto'));
    }

    // ── Cerrar sesión (→ PENDIENTE_VENTA) ──────────────────
    public function cerrarSesion(int $sesionId, int $postulanteId): void
    {
        $this->db->prepare(
            "UPDATE sesion_caja SET
                estado = 'PENDIENTE_VENTA',
                bloqueado = 1,
                postulante_cierre_id = :pid,
                fecha_cierre = NOW()
             WHERE id_sesion = :sid"
        )->execute(['pid' => $postulanteId, 'sid' => $sesionId]);
    }

    // ── Ventas y cuadre ────────────────────────────────────
    public function insertVenta(int $sesionId, int $vendedorId, float $monto): void
    {
        // Borrar venta previa si existe (evitar duplicados en re-submit)
        $this->db->prepare("DELETE FROM reporte_venta WHERE sesion_id = :sid")->execute(['sid' => $sesionId]);
        $this->db->prepare(
            "INSERT INTO reporte_venta (sesion_id, postulante_vendedor_id, monto)
             VALUES (:sid, :vid, :mon)"
        )->execute(['sid' => $sesionId, 'vid' => $vendedorId, 'mon' => $monto]);
    }

    public function calcularYGuardarCuadre(int $sesionId, float $ventas): array
    {
        $sesion = $this->getSesionById($sesionId);
        if (!$sesion) return [];

        // Siempre usar la base real del predecesor (no el saldo_inicial almacenado,
        // que puede estar desactualizado si el arqueo anterior tuvo ajustes posteriores).
        $saldo_inicial = $this->getSaldoBase((int)$sesion['caja_id']);

        // Actualizar saldo_inicial en la sesión para que quede consistente
        $this->db->prepare("UPDATE sesion_caja SET saldo_inicial = :sal WHERE id_sesion = :sid")
                 ->execute(['sal' => $saldo_inicial, 'sid' => $sesionId]);

        $total_gastos  = $this->sumGastosSesion($sesionId);

        // LO QUE ES: activos físicos (caja exterior + monedas + billetes + caja fuerte + agente BCP)
        $dc = $this->db->prepare("SELECT * FROM detalle_cuadre WHERE sesion_id = :sid");
        $dc->execute(['sid' => $sesionId]);
        $detalle = $dc->fetch() ?? [];

        $efectivo_fisico  = (float)($detalle['total_efectivo_contado'] ?? 0); // = LO QUE ES

        // LO QUE SE DICE: base + ventas - gastos - digitales aprobados
        // Los pagos digitales (yape/plin/visa/pos/trans) restan porque no son efectivo físico.
        $digital_aprobado = $this->sumDigitalDeclarado($sesionId);
        $total_esperado   = $saldo_inicial + $ventas - $total_gastos - $digital_aprobado;

        // Diferencia: LO QUE ES − LO QUE SE DICE
        $total_contado = $efectivo_fisico; // solo lo físico
        $diferencia    = $total_contado - $total_esperado;

        $resultado = abs($diferencia) < 0.01 ? 'CONSISTENTE'
                   : ($diferencia > 0 ? 'SOBRANTE' : 'FALTANTE');

        // Actualizar detalle_cuadre
        $this->db->prepare(
            "UPDATE detalle_cuadre SET
                total_contado_general  = :tco,
                total_ventas_sistema   = :ven,
                total_gastos_sistema   = :gas,
                total_esperado_sistema = :esp,
                diferencia             = :dif,
                resultado_cuadre       = :res
             WHERE sesion_id = :sid"
        )->execute([
            'tco' => $total_contado,
            'ven' => $ventas,
            'gas' => $total_gastos,
            'esp' => $total_esperado,
            'dif' => $diferencia,
            'res' => $resultado,
            'sid' => $sesionId,
        ]);

        // Cambiar estado → CERRADA
        $this->db->prepare(
            "UPDATE sesion_caja SET
                estado = 'CERRADA',
                saldo_final_sistema = :esp,
                saldo_final_contado = :con,
                diferencia_final    = :dif
             WHERE id_sesion = :sid"
        )->execute([
            'esp' => $total_esperado,
            'con' => $total_contado,
            'dif' => $diferencia,
            'sid' => $sesionId,
        ]);

        return [
            'saldo_inicial'      => $saldo_inicial,
            'total_ventas'       => $ventas,
            'total_gastos'       => $total_gastos,
            'total_esperado'     => $total_esperado,
            'efectivo_fisico'    => $efectivo_fisico,
            'digital_aprobado'   => $digital_aprobado,
            'total_contado'      => $total_contado,
            'diferencia'         => $diferencia,
            'resultado'          => $resultado,
            'saldo_proximo_dia'  => (float)($detalle['saldo_proximo_dia'] ?? 0),
        ];
    }

    // ── Reporte completo ───────────────────────────────────
    public function getReporte(int $sesionId): array
    {
        $sesion  = $this->getSesionById($sesionId);
        $dc      = $this->db->prepare("SELECT * FROM detalle_cuadre WHERE sesion_id = :sid");
        $dc->execute(['sid' => $sesionId]);
        $detalle = $dc->fetch() ?? [];

        $rv = $this->db->prepare("SELECT monto, postulante_vendedor_id FROM reporte_venta WHERE sesion_id = :sid LIMIT 1");
        $rv->execute(['sid' => $sesionId]);
        $venta = $rv->fetch();

        $gastos  = $this->getGastosSesion($sesionId);
        $rectifs = $this->getRectificaciones($sesionId);

        // Pagos digitales detallados para el reporte (LO QUE SE DICE)
        $digitales        = $this->getPagosDigitalesBySesion($sesionId);
        $digital_aprobado = $this->sumDigitalDeclarado($sesionId);

        // Vendedor (si existe)
        $vStmt = $this->db->prepare(
            "SELECT pv.nombres AS vendedor_nombre
             FROM sesion_participante sp
             INNER JOIN postulante pv ON sp.postulante_id = pv.id_postulante
             WHERE sp.sesion_id = :sid AND sp.rol_participacion = 'VENDEDORA' LIMIT 1"
        );
        $vStmt->execute(['sid' => $sesionId]);
        $vendedor = $vStmt->fetchColumn() ?: null;

        return compact('sesion', 'detalle', 'venta', 'gastos', 'rectifs', 'digitales', 'digital_aprobado', 'vendedor');
    }

    // ── Eliminar sesión y todos sus hijos ──────────────────
    public function eliminarSesion(int $sesionId): void
    {
        // Orden de borrado respetando FKs
        $tablas = [
            "DELETE FROM rectificacion_cuadre  WHERE sesion_id = :sid",
            "DELETE FROM detalle_cuadre        WHERE sesion_id = :sid",
            "DELETE FROM movimiento_sesion     WHERE sesion_id = :sid",
            "DELETE FROM pago_personal         WHERE sesion_id = :sid",
            "DELETE FROM pago_local            WHERE sesion_id = :sid",
            "DELETE FROM reporte_venta         WHERE sesion_id = :sid",
            "DELETE FROM sesion_participante   WHERE sesion_id = :sid",
            "DELETE FROM sesion_caja           WHERE id_sesion = :sid",
        ];
        foreach ($tablas as $sql) {
            $this->db->prepare($sql)->execute(['sid' => $sesionId]);
        }
    }

    public function verificarPasswordAdmin(int $postulanteId, string $password): bool
    {
        $stmt = $this->db->prepare(
            "SELECT password FROM usuario WHERE postulante_id = :pid LIMIT 1"
        );
        $stmt->execute(['pid' => $postulanteId]);
        $hash = $stmt->fetchColumn();
        return $hash && password_verify($password, $hash);
    }

    // ── Rectificaciones ────────────────────────────────────
    public function addRectificacion(int $sesionId, int $registraId, string $tipo, float $monto, string $descripcion): void
    {
        $this->db->prepare(
            "INSERT INTO rectificacion_cuadre
                (sesion_id, postulante_registra_id, tipo_rectificacion, monto, descripcion_contexto, estado)
             VALUES (:sid, :reg, :tipo, :mon, :desc, 'APROBADA')"
        )->execute(['sid' => $sesionId, 'reg' => $registraId, 'tipo' => $tipo, 'mon' => $monto, 'desc' => $descripcion]);

        // Actualizar saldo_proximo_dia de este arqueo
        $this->db->prepare(
            "UPDATE detalle_cuadre
             SET saldo_proximo_dia      = saldo_proximo_dia + :mon,
                 saldo_proxima_efectivo = saldo_proxima_efectivo + :mon2
             WHERE sesion_id = :sid"
        )->execute(['mon' => $monto, 'mon2' => $monto, 'sid' => $sesionId]);

        // Propagar el nuevo saldo como base del siguiente turno de la misma caja
        // (si ya fue creado mientras este arqueo aún estaba abierto o siendo ajustado)
        $cajaStmt = $this->db->prepare("SELECT caja_id FROM sesion_caja WHERE id_sesion = :sid");
        $cajaStmt->execute(['sid' => $sesionId]);
        $cajaId = $cajaStmt->fetchColumn();

        $nuevoSaldo = $this->db->prepare(
            "SELECT saldo_proximo_dia FROM detalle_cuadre WHERE sesion_id = :sid"
        );
        $nuevoSaldo->execute(['sid' => $sesionId]);
        $saldoFinal = (float)$nuevoSaldo->fetchColumn();

        // Actualizar el saldo_inicial del siguiente turno abierto o pendiente
        $this->db->prepare(
            "UPDATE sesion_caja
             SET saldo_inicial = :sal
             WHERE caja_id = :cid
               AND id_sesion > :sid
               AND estado IN ('ABIERTA', 'PENDIENTE_VENTA')
             ORDER BY id_sesion ASC
             LIMIT 1"
        )->execute(['sal' => $saldoFinal, 'cid' => $cajaId, 'sid' => $sesionId]);
    }

    /** Propaga saldo_proximo_dia al siguiente turno abierto de la misma caja */
    public function propagarBase(int $sesionId): float
    {
        $cajaStmt = $this->db->prepare("SELECT caja_id FROM sesion_caja WHERE id_sesion = :sid");
        $cajaStmt->execute(['sid' => $sesionId]);
        $cajaId = $cajaStmt->fetchColumn();

        $saldoStmt = $this->db->prepare(
            "SELECT saldo_proximo_dia FROM detalle_cuadre WHERE sesion_id = :sid"
        );
        $saldoStmt->execute(['sid' => $sesionId]);
        $saldo = (float)$saldoStmt->fetchColumn();

        $this->db->prepare(
            "UPDATE sesion_caja
             SET saldo_inicial = :sal
             WHERE caja_id = :cid
               AND id_sesion > :sid
               AND estado IN ('ABIERTA','PENDIENTE_VENTA')
             ORDER BY id_sesion ASC
             LIMIT 1"
        )->execute(['sal' => $saldo, 'cid' => $cajaId, 'sid' => $sesionId]);

        return $saldo;
    }

    public function getRectificaciones(int $sesionId): array
    {
        $stmt = $this->db->prepare(
            "SELECT rc.*, p.nombres AS registrado_por
             FROM rectificacion_cuadre rc
             INNER JOIN postulante p ON rc.postulante_registra_id = p.id_postulante
             WHERE rc.sesion_id = :sid ORDER BY rc.fecha_rectificacion DESC"
        );
        $stmt->execute(['sid' => $sesionId]);
        return $stmt->fetchAll();
    }
}
