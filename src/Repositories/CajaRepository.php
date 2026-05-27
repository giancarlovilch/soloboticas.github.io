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

    public function getModosPago(): array
    {
        // id_modo = 1 es EFECTIVO; SoloBank tiene su propio UI — ambos excluidos
        return $this->db->query(
            "SELECT id_modo, descripcion FROM modo
              WHERE activo = 1 AND id_modo != 1 AND descripcion != 'SoloBank'
              ORDER BY id_modo"
        )->fetchAll();
    }

    public function getSoloBankModoId(): int
    {
        $row = $this->db->query(
            "SELECT id_modo FROM modo WHERE descripcion = 'SoloBank' LIMIT 1"
        )->fetch();
        if ($row) return (int) $row['id_modo'];
        $this->db->exec("INSERT INTO modo (descripcion, activo) VALUES ('SoloBank', 1)");
        return (int) $this->db->lastInsertId();
    }

    public function addPagoSoloBank(int $sesionId, int $registradorId, string $codigoVale, float $monto): int
    {
        $modoId = $this->getSoloBankModoId();
        $this->db->prepare("
            INSERT INTO movimiento_sesion
                (sesion_id, tipo_movimiento_id, modo_id, postulante_registro_id,
                 monto, numero_operacion, estado)
            VALUES (:sid, 1, :modo, :reg, :mon, :num, 'PENDIENTE')
        ")->execute([
            'sid'  => $sesionId,
            'modo' => $modoId,
            'reg'  => $registradorId,
            'mon'  => $monto,
            'num'  => $codigoVale,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function getTiposEgreso(): array
    {
        try {
            return $this->db->query(
                "SELECT id_tipo_egreso, etiqueta, modo_ref FROM tipo_egreso WHERE activo = 1 ORDER BY orden"
            )->fetchAll();
        } catch (\PDOException) {
            // Fallback mientras no se ejecute la migración tipo_egreso
            return [
                ['id_tipo_egreso' => 1, 'etiqueta' => 'Pago de Personal', 'modo_ref' => 'PERSONAL'],
                ['id_tipo_egreso' => 2, 'etiqueta' => 'Pago de Local',    'modo_ref' => 'CONCEPTO'],
                ['id_tipo_egreso' => 3, 'etiqueta' => 'Pago de Facturas', 'modo_ref' => 'CONCEPTO'],
                ['id_tipo_egreso' => 4, 'etiqueta' => 'Otro',             'modo_ref' => 'LIBRE'],
            ];
        }
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

    /** Sesiones recientes con filtros de caja, cajera y mes */
    public function getSesionesRecientes(int $cajaId = 0, int $cajeraId = 0, string $mes = ''): array
    {
        if (!$mes) $mes = date('Y-m');
        [$anio, $nmes] = explode('-', $mes);
        $desdeFecha = "{$anio}-{$nmes}-01";
        $hastaFecha = date('Y-m-t', strtotime($desdeFecha));

        $where  = "sc.fecha_operacion BETWEEN :desde AND :hasta";
        $params = ['desde' => $desdeFecha, 'hasta' => $hastaFecha];

        if ($cajaId > 0) {
            $where .= " AND sc.caja_id = :cid";
            $params['cid'] = $cajaId;
        }
        if ($cajeraId > 0) {
            $where .= " AND sc.postulante_apertura_id = :cajera";
            $params['cajera'] = $cajeraId;
        }

        $sql = "SELECT sc.id_sesion, sc.estado, sc.saldo_inicial,
                       sc.fecha_operacion, sc.fecha_apertura,
                       c.id_caja, c.descripcion AS caja_desc, l.id_local,
                       l.descripcion AS local_desc,
                       t.descripcion AS turno_desc,
                       p.id_postulante AS cajera_id,
                       p.nombres AS cajera_nombre,
                       pv.nombres AS vendedor_nombre,
                       dc.num_operaciones_bcp,
                       (SELECT prev.id_sesion
                        FROM sesion_caja prev
                        INNER JOIN caja pc ON prev.caja_id = pc.id_caja
                        WHERE pc.id_caja = sc.caja_id
                          AND prev.id_sesion < sc.id_sesion
                        ORDER BY prev.id_sesion DESC LIMIT 1
                       ) AS sesion_anterior_id,
                       (
                           COALESCE(dc.total_efectivo_contado, 0) - (
                               COALESCE(sc.saldo_inicial, 0)
                               + COALESCE(dc.total_ventas_sistema, 0)
                               - COALESCE(dc.total_gastos_sistema, 0)
                               - COALESCE((
                                   SELECT SUM(ms.monto)
                                   FROM movimiento_sesion ms
                                   WHERE ms.sesion_id = sc.id_sesion
                                     AND ms.tipo_movimiento_id = 1
                                     AND ms.estado IN ('PENDIENTE','APROBADO')
                               ), 0)
                           )
                       ) AS diferencia,
                       COALESCE((
                           SELECT SUM(rc.monto)
                           FROM rectificacion_cuadre rc
                           WHERE rc.sesion_id = sc.id_sesion
                       ), 0) AS sum_rectifs,
                       COALESCE((
                           SELECT SUM(CASE WHEN ae.accion = 'AGREGAR' THEN ae.monto ELSE -ae.monto END)
                           FROM ajuste_esperado ae
                           WHERE ae.sesion_id = sc.id_sesion
                       ), 0) AS sum_ajustes,
                       COALESCE((
                           SELECT SUM(cv.monto_nuevo - cv.monto_anterior)
                           FROM correccion_venta cv
                           WHERE cv.sesion_id = sc.id_sesion
                       ), 0) AS sum_corr_ventas
                FROM sesion_caja sc
                INNER JOIN caja c ON sc.caja_id = c.id_caja
                INNER JOIN local l ON c.local_id = l.id_local
                INNER JOIN turno t ON sc.turno_id = t.id_turno
                INNER JOIN postulante p ON sc.postulante_apertura_id = p.id_postulante
                LEFT JOIN sesion_participante sp ON sp.sesion_id = sc.id_sesion
                    AND sp.rol_participacion = 'VENDEDORA'
                LEFT JOIN postulante pv ON sp.postulante_id = pv.id_postulante
                LEFT JOIN detalle_cuadre dc ON dc.sesion_id = sc.id_sesion
                WHERE {$where}
                ORDER BY sc.fecha_operacion DESC, sc.fecha_apertura DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getCajasActivas(): array
    {
        return $this->db->query(
            "SELECT c.id_caja AS id, c.descripcion, l.descripcion AS local_desc
             FROM caja c INNER JOIN local l ON l.id_local = c.local_id
             WHERE c.activo = 1 ORDER BY l.descripcion, c.descripcion"
        )->fetchAll();
    }

    public function getCajerasActivas(): array
    {
        return $this->db->query(
            "SELECT DISTINCT p.id_postulante AS id, p.nombres
             FROM sesion_caja sc
             INNER JOIN postulante p ON p.id_postulante = sc.postulante_apertura_id
             ORDER BY p.nombres"
        )->fetchAll();
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

    public function getMovimientoById(int $movId, int $sesionId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT ms.id_movimiento, ms.sesion_id, ms.estado, ms.monto,
                    ms.numero_operacion, m.descripcion AS modo_desc
             FROM movimiento_sesion ms
             INNER JOIN modo m ON ms.modo_id = m.id_modo
             WHERE ms.id_movimiento = :mid AND ms.sesion_id = :sid
               AND ms.tipo_movimiento_id = 1"
        );
        $stmt->execute(['mid' => $movId, 'sid' => $sesionId]);
        return $stmt->fetch() ?: null;
    }

    public function adminDeleteMovimiento(int $movId): void
    {
        $this->db->prepare(
            "DELETE FROM movimiento_sesion
             WHERE id_movimiento = :id AND tipo_movimiento_id = 1"
        )->execute(['id' => $movId]);
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

    /** Recalcula diferencia/resultado en detalle_cuadre tras cambios retroactivos
     *  (asignación o liberación de vales SoloBank en sesiones ya cerradas). */
    public function recalcularDiferencia(int $sesionId): void
    {
        $sesion = $this->getSesionById($sesionId);
        if (!$sesion) return;

        $dcStmt = $this->db->prepare("SELECT * FROM detalle_cuadre WHERE sesion_id = :sid");
        $dcStmt->execute(['sid' => $sesionId]);
        $dc = $dcStmt->fetch();
        if (!$dc) return;

        $ventas         = (float)($dc['total_ventas_sistema']  ?? 0);
        $gastos         = (float)($dc['total_gastos_sistema']  ?? 0);
        $efectivoFisico = (float)($dc['total_efectivo_contado'] ?? 0);
        $saldoInicial   = (float)($sesion['saldo_inicial']      ?? 0);

        $digitalAprobado = $this->sumDigitalDeclarado($sesionId);
        $totalEsperado   = round($saldoInicial + $ventas - $gastos - $digitalAprobado, 2);
        $diferencia      = round($efectivoFisico - $totalEsperado, 2);
        $resultado       = abs($diferencia) < 0.01 ? 'CONSISTENTE'
                         : ($diferencia > 0 ? 'SOBRANTE' : 'FALTANTE');

        $this->db->prepare(
            "UPDATE detalle_cuadre SET
                total_esperado_sistema = :esp,
                diferencia             = :dif,
                resultado_cuadre       = :res
             WHERE sesion_id = :sid"
        )->execute([
            'esp' => $totalEsperado,
            'dif' => $diferencia,
            'res' => $resultado,
            'sid' => $sesionId,
        ]);
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
        $this->db->prepare("DELETE FROM pago_factura  WHERE sesion_id = :sid")
                 ->execute(['sid' => $sesionId]);
        $this->db->prepare("DELETE FROM pago_deposito WHERE sesion_id = :sid")
                 ->execute(['sid' => $sesionId]);
    }

    public function insertGastoPersonal(int $sesionId, int $emisorId, int $beneficiarioId, float $monto, ?string $tipoPago): void
    {
        $this->db->prepare(
            "INSERT INTO pago_personal (sesion_id, postulante_emisor_id, postulante_beneficiario_id, monto, tipo_pago, estado)
             VALUES (:sid, :emi, :ben, :mon, :tp, 'PAGADO')"
        )->execute(['sid' => $sesionId, 'emi' => $emisorId, 'ben' => $beneficiarioId, 'mon' => $monto, 'tp' => $tipoPago ?? 'PAGO_TOTAL']);
    }

    public function insertGastoLocal(int $sesionId, int $localId, int $emisorId, float $monto, ?string $comprobante, int $tipoEgresoId = 0, ?int $conceptoId = null): void
    {
        $this->db->prepare(
            "INSERT INTO pago_local (sesion_id, tipo_egreso_id, local_id, postulante_emisor_id, concepto_id, monto, numero_operacion, estado)
             VALUES (:sid, :teid, :loc, :emi, :con, :mon, :comp, 'APROBADO')"
        )->execute(['sid' => $sesionId, 'teid' => $tipoEgresoId ?: null, 'loc' => $localId, 'emi' => $emisorId, 'con' => $conceptoId, 'mon' => $monto, 'comp' => $comprobante]);
    }

    public function insertGastoOtro(int $sesionId, int $registradorId, string $descripcion, float $monto): void
    {
        $this->db->prepare(
            "INSERT INTO movimiento_sesion (sesion_id, tipo_movimiento_id, modo_id, postulante_registro_id, descripcion, monto, estado)
             VALUES (:sid, 2, 1, :reg, :desc, :mon, 'APROBADO')"
        )->execute(['sid' => $sesionId, 'reg' => $registradorId, 'desc' => $descripcion, 'mon' => $monto]);
    }

    public function insertGastoDeposito(int $sesionId, int $emisorId, float $monto, ?string $comprobante): void
    {
        $this->db->prepare(
            "INSERT INTO pago_deposito (sesion_id, postulante_emisor_id, monto, numero_comprobante)
             VALUES (:sid, :emi, :mon, :comp)"
        )->execute(['sid' => $sesionId, 'emi' => $emisorId, 'mon' => $monto, 'comp' => $comprobante]);
    }

    public function insertGastoFactura(int $sesionId, int $emisorId, string $tipoDoc, float $monto, ?string $comprobante): void
    {
        $this->db->prepare(
            "INSERT INTO pago_factura (sesion_id, postulante_emisor_id, tipo_documento, monto, numero_comprobante)
             VALUES (:sid, :emi, :tdoc, :mon, :comp)"
        )->execute(['sid' => $sesionId, 'emi' => $emisorId, 'tdoc' => $tipoDoc, 'mon' => $monto, 'comp' => $comprobante]);
    }

    // ── Resumen de gastos de la sesión ─────────────────────
    public function getGastosSesion(int $sesionId): array
    {
        // Cargar catálogo de tipos para enriquecer cada fila
        $teByMode = [];
        $teById   = [];
        try {
            foreach ($this->db->query("SELECT * FROM tipo_egreso ORDER BY orden")->fetchAll() as $te) {
                $teById[$te['id_tipo_egreso']] = $te;
                if ($te['activo'] && !isset($teByMode[$te['modo_ref']])) {
                    $teByMode[$te['modo_ref']] = $te;
                }
            }
        } catch (\PDOException) { /* tabla aún no migrada */ }

        $fallback = fn(string $modo, string $label) => [
            'id_tipo_egreso' => 0, 'etiqueta' => $label, 'modo_ref' => $modo,
        ];

        $gastos = [];

        // Pagos personal
        $stmt = $this->db->prepare(
            "SELECT pp.id_pago_personal AS id, pp.monto,
                    pp.tipo_pago, NULL AS comprobante,
                    pb.nombres AS descripcion, pp.postulante_beneficiario_id AS ref_id
             FROM pago_personal pp
             INNER JOIN postulante pb ON pp.postulante_beneficiario_id = pb.id_postulante
             WHERE pp.sesion_id = :sid"
        );
        $stmt->execute(['sid' => $sesionId]);
        $te = $teByMode['PERSONAL'] ?? $fallback('PERSONAL', 'Pago de Personal');
        foreach ($stmt->fetchAll() as $row) {
            $gastos[] = $row + [
                'tipo_egreso_id' => $te['id_tipo_egreso'],
                'etiqueta'       => $te['etiqueta'],
                'modo_ref'       => 'PERSONAL',
                'tipo_css'       => 'personal',
            ];
        }

        // Pagos local
        $stmt = $this->db->prepare(
            "SELECT pl.id_pago_local AS id, pl.monto, pl.numero_operacion AS comprobante,
                    l.descripcion, pl.local_id AS ref_id, pl.tipo_egreso_id,
                    pl.concepto_id, cg.descripcion AS concepto_desc
             FROM pago_local pl
             INNER JOIN local l ON pl.local_id = l.id_local
             LEFT JOIN concepto_gastos_local cg ON pl.concepto_id = cg.id_concepto
             WHERE pl.sesion_id = :sid"
        );
        $stmt->execute(['sid' => $sesionId]);
        $teLocal = $teByMode['LOCAL'] ?? $teByMode['CONCEPTO'] ?? $fallback('LOCAL', 'Pago de Local');
        foreach ($stmt->fetchAll() as $row) {
            $gastos[] = array_merge($row, [
                'tipo_egreso_id' => $teLocal['id_tipo_egreso'],
                'etiqueta'       => $teLocal['etiqueta'],
                'modo_ref'       => 'LOCAL',
                'tipo_css'       => 'concepto',
                'tipo_pago'      => null,
            ]);
        }

        // Otros pagos (movimiento_sesion EGRESO libre)
        $stmt = $this->db->prepare(
            "SELECT ms.id_movimiento AS id, ms.monto, NULL AS comprobante,
                    ms.descripcion, NULL AS ref_id, NULL AS tipo_pago,
                    NULL AS concepto_id, NULL AS concepto_desc, NULL AS tipo_documento
             FROM movimiento_sesion ms
             WHERE ms.sesion_id = :sid AND ms.tipo_movimiento_id = 2"
        );
        $stmt->execute(['sid' => $sesionId]);
        $teLibre = $teByMode['LIBRE'] ?? $fallback('LIBRE', 'Otros pagos');
        foreach ($stmt->fetchAll() as $row) {
            $gastos[] = $row + [
                'tipo_egreso_id' => $teLibre['id_tipo_egreso'],
                'etiqueta'       => $teLibre['etiqueta'],
                'modo_ref'       => 'LIBRE',
                'tipo_css'       => 'libre',
            ];
        }

        // Depósitos a KGyR
        $stmt = $this->db->prepare(
            "SELECT pd.id_pago_deposito AS id, pd.monto, pd.numero_comprobante AS comprobante,
                    NULL AS descripcion, NULL AS ref_id, NULL AS tipo_pago,
                    NULL AS concepto_id, NULL AS concepto_desc, NULL AS tipo_documento
             FROM pago_deposito pd WHERE pd.sesion_id = :sid"
        );
        $stmt->execute(['sid' => $sesionId]);
        $teDeposito = $teByMode['DEPOSITO'] ?? $fallback('DEPOSITO', 'Depósito a KGyR');
        foreach ($stmt->fetchAll() as $row) {
            $gastos[] = $row + [
                'tipo_egreso_id' => $teDeposito['id_tipo_egreso'],
                'etiqueta'       => $teDeposito['etiqueta'],
                'modo_ref'       => 'DEPOSITO',
                'tipo_css'       => 'personal',
            ];
        }

        // Pagos factura/compras
        $stmt = $this->db->prepare(
            "SELECT pf.id_pago_factura AS id, pf.monto, pf.numero_comprobante AS comprobante,
                    pf.tipo_documento, pf.tipo_documento AS descripcion, NULL AS ref_id
             FROM pago_factura pf
             WHERE pf.sesion_id = :sid"
        );
        $stmt->execute(['sid' => $sesionId]);
        $teFactura = $teByMode['FACTURA'] ?? $fallback('FACTURA', 'Pago de Facturas');
        foreach ($stmt->fetchAll() as $row) {
            $gastos[] = $row + [
                'tipo_egreso_id' => $teFactura['id_tipo_egreso'],
                'etiqueta'       => $teFactura['etiqueta'],
                'modo_ref'       => 'FACTURA',
                'tipo_css'       => 'concepto',
                'tipo_pago'      => null,
            ];
        }

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
        $total_esperado   = round($saldo_inicial + $ventas - $total_gastos - $digital_aprobado, 2);

        // Diferencia: LO QUE ES − LO QUE SE DICE
        $total_contado = $efectivo_fisico;
        $diferencia    = round($total_contado - $total_esperado, 2);

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

        $tiposRect       = $this->getTiposRectificacion();
        $ajustesEsperado = $this->getAjustesEsperado($sesionId);
        $modos           = $this->getModosPago();
        $staff           = $this->getStaffActivo();
        $locales         = $this->getLocales();
        $conceptos       = $this->getConceptosGasto();

        $correccionesVenta = $this->getCorreccionesVenta($sesionId);
        $sumCorrDelta = array_sum(array_map(
            fn($c) => (float)$c['monto_nuevo'] - (float)$c['monto_anterior'],
            $correccionesVenta
        ));

        return compact('sesion', 'detalle', 'venta', 'gastos', 'rectifs', 'tiposRect', 'digitales', 'digital_aprobado', 'vendedor', 'ajustesEsperado', 'modos', 'staff', 'locales', 'conceptos', 'correccionesVenta', 'sumCorrDelta');
    }

    // ── Eliminar sesión y todos sus hijos ──────────────────
    public function eliminarSesion(int $sesionId): void
    {
        // Liberar vales SoloBank vinculados antes de borrar los movimientos
        $this->db->prepare(
            "UPDATE solobank_vales
                SET estado = 'DISPONIBLE', sesion_id = NULL, movimiento_id = NULL
              WHERE sesion_id = :sid"
        )->execute(['sid' => $sesionId]);

        // Orden de borrado respetando FKs
        $tablas = [
            "DELETE FROM rectificacion_cuadre  WHERE sesion_id = :sid",
            "DELETE FROM ajuste_esperado       WHERE sesion_id = :sid",
            "DELETE FROM correccion_venta      WHERE sesion_id = :sid",
            "DELETE FROM detalle_cuadre        WHERE sesion_id = :sid",
            "DELETE FROM movimiento_sesion     WHERE sesion_id = :sid",
            "DELETE FROM pago_personal         WHERE sesion_id = :sid",
            "DELETE FROM pago_local            WHERE sesion_id = :sid",
            "DELETE FROM pago_factura          WHERE sesion_id = :sid",
            "DELETE FROM pago_deposito         WHERE sesion_id = :sid",
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
    public function getTiposRectificacion(): array
    {
        try {
            return $this->db->query(
                "SELECT id_tipo_rect, etiqueta, signo FROM tipo_rectificacion WHERE activo = 1 ORDER BY orden"
            )->fetchAll();
        } catch (\PDOException) {
            return [
                ['id_tipo_rect' => 1, 'etiqueta' => 'Efectivo encontrado',    'signo' =>  1],
                ['id_tipo_rect' => 2, 'etiqueta' => 'Devolución de efectivo', 'signo' => -1],
            ];
        }
    }

    public function addRectificacion(int $sesionId, int $registraId, int $tipoRectId, float $monto, string $descripcion): void
    {
        // Obtener signo del tipo para calcular monto firmado
        $signoStmt = $this->db->prepare("SELECT signo FROM tipo_rectificacion WHERE id_tipo_rect = :id");
        $signoStmt->execute(['id' => $tipoRectId]);
        $signo = (int)($signoStmt->fetchColumn() ?? 1);
        $montoFirmado = abs($monto) * $signo;

        // Mapa de compatibilidad con el enum heredado
        $tipoEnum = match($tipoRectId) {
            1 => 'DINERO_ENCONTRADO',
            2 => 'DEVOLUCION_DINERO',
            default => null,
        };

        $this->db->prepare(
            "INSERT INTO rectificacion_cuadre
                (sesion_id, postulante_registra_id, tipo_rectificacion, tipo_rect_id, monto, descripcion_contexto, estado)
             VALUES (:sid, :reg, :tipo, :trid, :mon, :desc, 'APROBADA')"
        )->execute(['sid' => $sesionId, 'reg' => $registraId, 'tipo' => $tipoEnum, 'trid' => $tipoRectId, 'mon' => $montoFirmado, 'desc' => $descripcion]);

        // Actualizar saldo_proximo_dia de este arqueo
        $this->db->prepare(
            "UPDATE detalle_cuadre
             SET saldo_proximo_dia      = saldo_proximo_dia + :mon,
                 saldo_proxima_efectivo = saldo_proxima_efectivo + :mon2
             WHERE sesion_id = :sid"
        )->execute(['mon' => $montoFirmado, 'mon2' => $montoFirmado, 'sid' => $sesionId]);

        $this->propagarBase($sesionId);
    }

    public function deleteRectificacion(int $rectId, int $adminId, string $password): bool|string
    {
        if (!$this->verificarPasswordAdmin($adminId, $password)) {
            return 'Contraseña incorrecta';
        }

        $stmt = $this->db->prepare("SELECT * FROM rectificacion_cuadre WHERE id_rectificacion = :id");
        $stmt->execute(['id' => $rectId]);
        $rect = $stmt->fetch();
        if (!$rect) return 'Rectificación no encontrada';

        // Revertir el ajuste en saldo_proximo_dia
        $montoInverso = -(float)$rect['monto'];
        $this->db->prepare(
            "UPDATE detalle_cuadre
             SET saldo_proximo_dia      = saldo_proximo_dia + :mon,
                 saldo_proxima_efectivo = saldo_proxima_efectivo + :mon2
             WHERE sesion_id = :sid"
        )->execute(['mon' => $montoInverso, 'mon2' => $montoInverso, 'sid' => $rect['sesion_id']]);

        $this->db->prepare("DELETE FROM rectificacion_cuadre WHERE id_rectificacion = :id")
            ->execute(['id' => $rectId]);

        $this->propagarBase((int)$rect['sesion_id']);

        return true;
    }

    /**
     * Propaga saldo_proximo_dia al siguiente turno de la misma caja.
     * Cubre todos los estados no definitivos (no solo ABIERTA/PENDIENTE_VENTA).
     * Si la sesión siguiente ya tiene cuadre guardado (CERRADA/EN_REVISION/etc.),
     * recalcula total_esperado, diferencia y resultado_cuadre para mantener consistencia.
     */
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

        // Sesión inmediatamente posterior de la misma caja (cualquier estado, salvo APROBADA)
        $nextStmt = $this->db->prepare(
            "SELECT id_sesion, estado FROM sesion_caja
             WHERE caja_id   = :cid
               AND id_sesion > :sid
               AND estado   != 'APROBADA'
             ORDER BY id_sesion ASC
             LIMIT 1"
        );
        $nextStmt->execute(['cid' => $cajaId, 'sid' => $sesionId]);
        $next = $nextStmt->fetch();

        if (!$next) return $saldo;

        $nextId     = (int)$next['id_sesion'];
        $nextEstado = $next['estado'];

        // Actualizar base
        $this->db->prepare("UPDATE sesion_caja SET saldo_inicial = :sal WHERE id_sesion = :sid")
                 ->execute(['sal' => $saldo, 'sid' => $nextId]);

        // Si ya tiene cuadre guardado, recalcular con la nueva base
        if (in_array($nextEstado, ['CERRADA', 'EN_REVISION', 'OBSERVADA', 'RECHAZADA'])) {
            $dcStmt = $this->db->prepare("SELECT * FROM detalle_cuadre WHERE sesion_id = :sid");
            $dcStmt->execute(['sid' => $nextId]);
            $dc = $dcStmt->fetch();

            if ($dc) {
                $ventas          = (float)($dc['total_ventas_sistema'] ?? 0);
                $gastos          = (float)($dc['total_gastos_sistema'] ?? 0);
                $digitalAprobado = $this->sumDigitalDeclarado($nextId);
                $efectivoFisico  = (float)($dc['total_efectivo_contado'] ?? 0);

                $nuevoEsperado = round($saldo + $ventas - $gastos - $digitalAprobado, 2);
                $nuevaDif      = round($efectivoFisico - $nuevoEsperado, 2);
                $nuevoResult   = abs($nuevaDif) < 0.01 ? 'CONSISTENTE'
                               : ($nuevaDif > 0       ? 'SOBRANTE'    : 'FALTANTE');

                $this->db->prepare(
                    "UPDATE detalle_cuadre SET
                        total_esperado_sistema = :esp,
                        diferencia             = :dif,
                        resultado_cuadre       = :res
                     WHERE sesion_id = :sid"
                )->execute([
                    'esp' => $nuevoEsperado,
                    'dif' => $nuevaDif,
                    'res' => $nuevoResult,
                    'sid' => $nextId,
                ]);

                $this->db->prepare(
                    "UPDATE sesion_caja SET
                        saldo_final_sistema = :esp,
                        diferencia_final    = :dif
                     WHERE id_sesion = :sid"
                )->execute([
                    'esp' => $nuevoEsperado,
                    'dif' => $nuevaDif,
                    'sid' => $nextId,
                ]);
            }
        }

        return $saldo;
    }

    // ── Ajustes al saldo esperado ──────────────────────────
    public function getAjustesEsperado(int $sesionId): array
    {
        $stmt = $this->db->prepare(
            "SELECT ae.*,
                    m.descripcion  AS modo_desc,
                    p.nombres      AS staff_desc,
                    l.descripcion  AS local_desc,
                    cg.descripcion AS concepto_desc
             FROM ajuste_esperado ae
             LEFT JOIN modo m                ON ae.modo_id = m.id_modo
             LEFT JOIN postulante p          ON ae.ref_id  = p.id_postulante  AND ae.tipo = 'PERSONAL'
             LEFT JOIN local l               ON ae.ref_id  = l.id_local        AND ae.tipo = 'LOCAL'
             LEFT JOIN concepto_gastos_local cg ON ae.ref2_id = cg.id_concepto AND ae.tipo = 'LOCAL'
             WHERE ae.sesion_id = :sid ORDER BY ae.fecha ASC"
        );
        $stmt->execute(['sid' => $sesionId]);
        return $stmt->fetchAll();
    }

    public function addAjusteEsperado(int $sesionId, string $tipo, string $accion, string $desc, float $monto, int $userId, array $extra = []): void
    {
        $this->db->prepare(
            "INSERT INTO ajuste_esperado
                (sesion_id, tipo, modo_id, ref_id, ref2_id, tipo_documento, tipo_pago, accion, descripcion, monto, postulante_id)
             VALUES (:sid, :tipo, :mid, :rid, :r2id, :tdoc, :tp, :acc, :desc, :mon, :uid)"
        )->execute([
            'sid'  => $sesionId,
            'tipo' => $tipo,
            'mid'  => $extra['modo_id']        ?? null,
            'rid'  => $extra['ref_id']         ?? null,
            'r2id' => $extra['ref2_id']        ?? null,
            'tdoc' => $extra['tipo_documento'] ?? null,
            'tp'   => $extra['tipo_pago']      ?? null,
            'acc'  => $accion,
            'desc' => $desc,
            'mon'  => abs($monto),
            'uid'  => $userId,
        ]);
    }

    public function deleteAjusteEsperado(int $ajusteId, int $adminId, string $password): bool|string
    {
        if (!$this->verificarPasswordAdmin($adminId, $password)) return 'Contraseña incorrecta';

        $stmt = $this->db->prepare("SELECT id_ajuste FROM ajuste_esperado WHERE id_ajuste = :id");
        $stmt->execute(['id' => $ajusteId]);
        if (!$stmt->fetch()) return 'Ajuste no encontrado';

        $this->db->prepare("DELETE FROM ajuste_esperado WHERE id_ajuste = :id")->execute(['id' => $ajusteId]);
        return true;
    }

    public function getRectificaciones(int $sesionId): array
    {
        $stmt = $this->db->prepare(
            "SELECT rc.*,
                    p.nombres AS registrado_por,
                    COALESCE(tr.etiqueta, rc.tipo_rectificacion) AS etiqueta,
                    COALESCE(tr.signo, IF(rc.monto >= 0, 1, -1)) AS signo
             FROM rectificacion_cuadre rc
             INNER JOIN postulante p ON rc.postulante_registra_id = p.id_postulante
             LEFT JOIN tipo_rectificacion tr ON rc.tipo_rect_id = tr.id_tipo_rect
             WHERE rc.sesion_id = :sid ORDER BY rc.fecha_rectificacion DESC"
        );
        $stmt->execute(['sid' => $sesionId]);
        return $stmt->fetchAll();
    }

    // ── Transferencias de saldo entre locales ──────────────

    public function getSaldosBaseCajas(): array
    {
        return $this->db->query(
            "SELECT c.id_caja, c.descripcion AS caja_desc, l.descripcion AS local_desc,
                    sc.id_sesion, dc.saldo_proximo_dia, sc.fecha_operacion, sc.estado AS sesion_estado
             FROM caja c
             INNER JOIN local l ON l.id_local = c.local_id
             LEFT JOIN sesion_caja sc ON sc.id_sesion = (
                 SELECT sc2.id_sesion
                 FROM sesion_caja sc2
                 INNER JOIN detalle_cuadre dc2 ON dc2.sesion_id = sc2.id_sesion
                 WHERE sc2.caja_id = c.id_caja
                 ORDER BY sc2.id_sesion DESC LIMIT 1
             )
             LEFT JOIN detalle_cuadre dc ON dc.sesion_id = sc.id_sesion
             WHERE c.activo = 1
             ORDER BY l.descripcion ASC, c.descripcion ASC"
        )->fetchAll();
    }

    private function getUltimaSesionConDetalle(int $cajaId): ?int
    {
        $stmt = $this->db->prepare(
            "SELECT sc.id_sesion
             FROM sesion_caja sc
             INNER JOIN detalle_cuadre dc ON dc.sesion_id = sc.id_sesion
             WHERE sc.caja_id = :cid
             ORDER BY sc.id_sesion DESC LIMIT 1"
        );
        $stmt->execute(['cid' => $cajaId]);
        $id = $stmt->fetchColumn();
        return $id ? (int)$id : null;
    }

    public function crearTransferencia(int $cajaOrigen, int $cajaDestino, float $monto, ?string $notas, int $solicitanteId): int
    {
        $this->db->prepare(
            "INSERT INTO transferencia_saldo (caja_origen_id, caja_destino_id, monto, notas, solicitante_id)
             VALUES (:ori, :des, :monto, :notas, :sol)"
        )->execute(['ori' => $cajaOrigen, 'des' => $cajaDestino, 'monto' => $monto, 'notas' => $notas, 'sol' => $solicitanteId]);
        return (int)$this->db->lastInsertId();
    }

    public function confirmarTransferencia(int $id, int $confirmadorId, string $password, string $comprobante): bool|string
    {
        $stmt = $this->db->prepare("SELECT password FROM usuario WHERE postulante_id = :pid LIMIT 1");
        $stmt->execute(['pid' => $confirmadorId]);
        $hash = $stmt->fetchColumn();
        if (!$hash || !password_verify($password, $hash)) return 'Contraseña incorrecta';

        $t = $this->db->prepare("SELECT * FROM transferencia_saldo WHERE id = :id AND estado = 'PENDIENTE'");
        $t->execute(['id' => $id]);
        $transfer = $t->fetch();
        if (!$transfer) return 'Transferencia no encontrada o ya procesada';

        // Aplicar a detalle_cuadre de la última sesión con detalle de cada caja
        foreach ([
            ['cid' => $transfer['caja_origen_id'],  'op' => '-'],
            ['cid' => $transfer['caja_destino_id'], 'op' => '+'],
        ] as $side) {
            $sesionId = $this->getUltimaSesionConDetalle($side['cid']);
            if ($sesionId) {
                $op = $side['op'];
                $this->db->prepare(
                    "UPDATE detalle_cuadre SET saldo_proximo_dia = COALESCE(saldo_proximo_dia, 0) {$op} :monto
                     WHERE sesion_id = :id"
                )->execute(['monto' => $transfer['monto'], 'id' => $sesionId]);
            }
        }

        $this->db->prepare(
            "UPDATE transferencia_saldo SET estado = 'CONFIRMADA', confirmador_id = :cid,
             numero_comprobante = :comp, confirmed_at = NOW() WHERE id = :id"
        )->execute(['cid' => $confirmadorId, 'comp' => $comprobante, 'id' => $id]);

        return true;
    }

    public function anularTransferencia(int $id, int $anuladorId, string $password): bool|string
    {
        $stmt = $this->db->prepare("SELECT password FROM usuario WHERE postulante_id = :pid LIMIT 1");
        $stmt->execute(['pid' => $anuladorId]);
        $hash = $stmt->fetchColumn();
        if (!$hash || !password_verify($password, $hash)) return 'Contraseña incorrecta';

        $t = $this->db->prepare("SELECT * FROM transferencia_saldo WHERE id = :id AND estado IN ('PENDIENTE','CONFIRMADA')");
        $t->execute(['id' => $id]);
        $transfer = $t->fetch();
        if (!$transfer) return 'Transferencia no encontrada o ya anulada';

        // Si estaba CONFIRMADA, revertir el saldo en detalle_cuadre
        if ($transfer['estado'] === 'CONFIRMADA') {
            foreach ([
                ['cid' => $transfer['caja_origen_id'],  'op' => '+'],
                ['cid' => $transfer['caja_destino_id'], 'op' => '-'],
            ] as $side) {
                $sesionId = $this->getUltimaSesionConDetalle($side['cid']);
                if ($sesionId) {
                    $op = $side['op'];
                    $this->db->prepare(
                        "UPDATE detalle_cuadre SET saldo_proximo_dia = COALESCE(saldo_proximo_dia, 0) {$op} :monto
                         WHERE sesion_id = :id"
                    )->execute(['monto' => $transfer['monto'], 'id' => $sesionId]);
                }
            }
        }

        $this->db->prepare(
            "UPDATE transferencia_saldo SET estado = 'ANULADA', anulador_id = :aid, anulada_at = NOW()
             WHERE id = :id"
        )->execute(['aid' => $anuladorId, 'id' => $id]);

        return true;
    }

    public function getTransferencias(): array
    {
        return $this->db->query(
            "SELECT t.*,
                    CONCAT(co.descripcion, ' · ', lo.descripcion) AS caja_origen_desc,
                    CONCAT(cd.descripcion, ' · ', ld.descripcion) AS caja_destino_desc,
                    ps.nombres AS solicitante_nombre,
                    pc.nombres AS confirmador_nombre,
                    pa.nombres AS anulador_nombre
             FROM transferencia_saldo t
             INNER JOIN caja co ON co.id_caja  = t.caja_origen_id
             INNER JOIN caja cd ON cd.id_caja  = t.caja_destino_id
             INNER JOIN local lo ON lo.id_local = co.local_id
             INNER JOIN local ld ON ld.id_local = cd.local_id
             INNER JOIN postulante ps ON ps.id_postulante = t.solicitante_id
             LEFT JOIN postulante pc  ON pc.id_postulante = t.confirmador_id
             LEFT JOIN postulante pa  ON pa.id_postulante = t.anulador_id
             ORDER BY t.created_at DESC LIMIT 100"
        )->fetchAll();
    }

    public function getTransferenciasByCaja(int $cajaId, string $fecha): array
    {
        $stmt = $this->db->prepare(
            "SELECT t.*,
                    CONCAT(co.descripcion, ' (', lo.descripcion, ')') AS caja_origen_desc,
                    CONCAT(cd.descripcion, ' (', ld.descripcion, ')') AS caja_destino_desc,
                    ps.nombres AS solicitante_nombre,
                    pc.nombres AS confirmador_nombre
             FROM transferencia_saldo t
             INNER JOIN caja co ON co.id_caja  = t.caja_origen_id
             INNER JOIN caja cd ON cd.id_caja  = t.caja_destino_id
             INNER JOIN local lo ON lo.id_local = co.local_id
             INNER JOIN local ld ON ld.id_local = cd.local_id
             INNER JOIN postulante ps ON ps.id_postulante = t.solicitante_id
             LEFT JOIN postulante pc  ON pc.id_postulante = t.confirmador_id
             WHERE (t.caja_origen_id = :cid OR t.caja_destino_id = :cid2)
               AND t.estado = 'CONFIRMADA'
               AND DATE(t.confirmed_at) = :fecha
             ORDER BY t.confirmed_at ASC"
        );
        $stmt->execute(['cid' => $cajaId, 'cid2' => $cajaId, 'fecha' => $fecha]);
        return $stmt->fetchAll();
    }

    // ── Correcciones de venta ──────────────────────────────
    public function getCorreccionesVenta(int $sesionId): array
    {
        $stmt = $this->db->prepare(
            "SELECT cv.id_correccion, cv.monto_anterior, cv.monto_nuevo, cv.motivo,
                    cv.fecha_registro, p.nombres AS registrado_por
             FROM correccion_venta cv
             LEFT JOIN postulante p ON cv.usuario_id = p.id_postulante
             WHERE cv.sesion_id = :sid
             ORDER BY cv.fecha_registro ASC"
        );
        $stmt->execute(['sid' => $sesionId]);
        return $stmt->fetchAll();
    }

    public function addCorreccionVenta(int $sesionId, float $montoNuevo, string $motivo, int $usuarioId): void
    {
        // monto_anterior = ventas originales + suma de deltas previos
        $baseStmt = $this->db->prepare(
            "SELECT total_ventas_sistema FROM detalle_cuadre WHERE sesion_id = :sid"
        );
        $baseStmt->execute(['sid' => $sesionId]);
        $totalOriginal = (float)($baseStmt->fetchColumn() ?? 0);

        $deltaStmt = $this->db->prepare(
            "SELECT COALESCE(SUM(monto_nuevo - monto_anterior), 0) FROM correccion_venta WHERE sesion_id = :sid"
        );
        $deltaStmt->execute(['sid' => $sesionId]);
        $sumDelta = (float)$deltaStmt->fetchColumn();

        $montoAnterior = round($totalOriginal + $sumDelta, 2);

        $this->db->prepare(
            "INSERT INTO correccion_venta (sesion_id, monto_anterior, monto_nuevo, motivo, usuario_id)
             VALUES (:sid, :ant, :nuevo, :mot, :uid)"
        )->execute([
            'sid'  => $sesionId,
            'ant'  => $montoAnterior,
            'nuevo' => round($montoNuevo, 2),
            'mot'  => $motivo !== '' ? $motivo : null,
            'uid'  => $usuarioId,
        ]);
    }
}
