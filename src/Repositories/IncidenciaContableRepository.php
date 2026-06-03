<?php

require_once __DIR__ . '/../Core/Database.php';

class IncidenciaContableRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // ── Crear una incidencia ───────────────────────────────

    public function crear(
        int     $sesionOrigenId,
        string  $tipo,
        float   $montoOriginal,
        int     $registradoPorId,
        ?int    $responsableId  = null,
        ?string $descripcion    = null,
        bool    $autoDetectado  = true
    ): int {
        $this->db->prepare("
            INSERT INTO incidencia_contable
                (sesion_origen_id, tipo, monto_original, monto_pendiente,
                 estado, descripcion, responsable_id, auto_detectado, registrado_por_id)
            VALUES
                (:sid, :tipo, :orig, :pen, 'ABIERTO', :desc, :resp, :auto, :reg)
        ")->execute([
            'sid'  => $sesionOrigenId,
            'tipo' => $tipo,
            'orig' => $montoOriginal,
            'pen'  => $montoOriginal,
            'desc' => $descripcion,
            'resp' => $responsableId,
            'auto' => (int)$autoDetectado,
            'reg'  => $registradoPorId,
        ]);
        return (int)$this->db->lastInsertId();
    }

    // ── Consultas ──────────────────────────────────────────

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT ic.*,
                   sc.fecha_operacion, sc.caja_id,
                   c.descripcion  AS caja_desc,
                   l.descripcion  AS local_desc,
                   t.descripcion  AS turno_desc,
                   p.nombres      AS responsable_nombre,
                   pr.nombres     AS registrado_por_nombre,
                   pv.nombres     AS vendedora_nombre
            FROM incidencia_contable ic
            LEFT JOIN sesion_caja sc  ON ic.sesion_origen_id = sc.id_sesion
            LEFT JOIN caja c          ON sc.caja_id = c.id_caja
            LEFT JOIN local l         ON c.local_id = l.id_local
            LEFT JOIN turno t         ON sc.turno_id = t.id_turno
            LEFT JOIN postulante p    ON ic.responsable_id = p.id_postulante
            LEFT JOIN postulante pr   ON ic.registrado_por_id = pr.id_postulante
            LEFT JOIN sesion_participante sp ON sp.sesion_id = sc.id_sesion
                AND sp.rol_participacion = 'VENDEDORA'
            LEFT JOIN postulante pv   ON pv.id_postulante = sp.postulante_id
            WHERE ic.id_incidencia = :id
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function listar(
        ?string $estado = null,
        ?string $tipo   = null,
        int     $limit  = 100
    ): array {
        $where  = '1=1';
        $params = [];

        if ($estado) { $where .= ' AND ic.estado = :estado'; $params['estado'] = $estado; }
        if ($tipo)   { $where .= ' AND ic.tipo   = :tipo';   $params['tipo']   = $tipo; }

        $stmt = $this->db->prepare("
            SELECT ic.*,
                   sc.fecha_operacion,
                   c.descripcion  AS caja_desc,
                   l.descripcion  AS local_desc,
                   t.descripcion  AS turno_desc,
                   p.nombres      AS responsable_nombre
            FROM incidencia_contable ic
            LEFT JOIN sesion_caja sc ON ic.sesion_origen_id = sc.id_sesion
            LEFT JOIN caja c         ON sc.caja_id = c.id_caja
            LEFT JOIN local l        ON c.local_id = l.id_local
            LEFT JOIN turno t        ON sc.turno_id = t.id_turno
            LEFT JOIN postulante p   ON ic.responsable_id = p.id_postulante
            WHERE {$where}
            ORDER BY
                FIELD(ic.estado,'ABIERTO','PARCIAL','CERRADO'),
                ic.fecha_apertura DESC
            LIMIT {$limit}
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getBySesion(int $sesionId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM incidencia_contable
            WHERE sesion_origen_id = :sid
            ORDER BY fecha_apertura DESC
        ");
        $stmt->execute(['sid' => $sesionId]);
        return $stmt->fetchAll();
    }

    public function contarAbiertos(): int
    {
        return (int)$this->db->query(
            "SELECT COUNT(*) FROM incidencia_contable WHERE estado IN ('ABIERTO','PARCIAL')"
        )->fetchColumn();
    }

    // ── Movimientos ────────────────────────────────────────

    public function addMovimiento(
        int     $incidenciaId,
        string  $tipo,
        float   $monto,
        int     $registradoPorId,
        ?int    $sesionId    = null,
        ?string $descripcion = null
    ): void {
        $this->db->prepare("
            INSERT INTO movimiento_incidencia_contable
                (incidencia_id, sesion_id, tipo, monto, descripcion, registrado_por_id)
            VALUES (:inc, :sid, :tipo, :mon, :desc, :reg)
        ")->execute([
            'inc'  => $incidenciaId,
            'sid'  => $sesionId,
            'tipo' => $tipo,
            'mon'  => $monto,
            'desc' => $descripcion,
            'reg'  => $registradoPorId,
        ]);

        $this->recalcularPendiente($incidenciaId);
    }

    public function getMovimientos(int $incidenciaId): array
    {
        $stmt = $this->db->prepare("
            SELECT mic.*,
                   p.nombres AS registrado_por_nombre
            FROM movimiento_incidencia_contable mic
            LEFT JOIN postulante p ON mic.registrado_por_id = p.id_postulante
            WHERE mic.incidencia_id = :id
            ORDER BY mic.fecha ASC
        ");
        $stmt->execute(['id' => $incidenciaId]);
        return $stmt->fetchAll();
    }

    // ── Estado y recálculo ─────────────────────────────────

    private function recalcularPendiente(int $incidenciaId): void
    {
        // Abonos y condonaciones reducen el pendiente; cargos lo aumentan
        $row = $this->db->prepare("
            SELECT
                ic.monto_original,
                COALESCE(SUM(
                    CASE WHEN mic.tipo IN ('ABONO','CONDONACION','AJUSTE_ADMIN') THEN mic.monto
                         WHEN mic.tipo = 'CARGO' THEN -mic.monto
                         ELSE 0 END
                ), 0) AS total_aplicado
            FROM incidencia_contable ic
            LEFT JOIN movimiento_incidencia_contable mic ON mic.incidencia_id = ic.id_incidencia
            WHERE ic.id_incidencia = :id
            GROUP BY ic.id_incidencia, ic.monto_original
        ");
        $row->execute(['id' => $incidenciaId]);
        $data = $row->fetch();
        if (!$data) return;

        $pendiente = max(0, round((float)$data['monto_original'] - (float)$data['total_aplicado'], 2));

        // Se cierra automáticamente cuando el pendiente cae dentro de ±10 soles
        $estado = 'ABIERTO';
        if ($pendiente <= 10) {
            $estado = 'CERRADO';
        } elseif ((float)$data['total_aplicado'] > 0) {
            $estado = 'PARCIAL';
        }

        $this->db->prepare("
            UPDATE incidencia_contable
               SET monto_pendiente = :pen,
                   estado          = :est,
                   fecha_cierre    = :fc
             WHERE id_incidencia   = :id
        ")->execute([
            'pen' => $pendiente,
            'est' => $estado,
            'fc'  => $estado === 'CERRADO' ? date('Y-m-d H:i:s') : null,
            'id'  => $incidenciaId,
        ]);
    }

    public function cerrarManual(int $incidenciaId): void
    {
        $this->db->prepare("
            UPDATE incidencia_contable
               SET estado = 'CERRADO', fecha_cierre = NOW(), monto_pendiente = 0
             WHERE id_incidencia = :id
        ")->execute(['id' => $incidenciaId]);
    }

    public function reabrirCaso(int $incidenciaId): void
    {
        $this->db->prepare("
            UPDATE incidencia_contable
               SET estado = 'ABIERTO', fecha_cierre = NULL
             WHERE id_incidencia = :id
        ")->execute(['id' => $incidenciaId]);
    }

    public function actualizarDescripcion(int $incidenciaId, string $descripcion): void
    {
        $this->db->prepare("
            UPDATE incidencia_contable SET descripcion = :desc WHERE id_incidencia = :id
        ")->execute(['desc' => $descripcion, 'id' => $incidenciaId]);
    }

    // ── Vales de regularización ────────────────────────────

    public function generarVale(int $incidenciaId, float $monto, string $descripcion, int $generadoPorId): string
    {
        $inc = $this->getById($incidenciaId);
        if (!$inc) throw new \RuntimeException('Incidencia no encontrada');

        $codigo = 'VR-' . date('ymd') . '-' . strtoupper(substr(uniqid(), -5));

        $sesionOrigenId = (int)$inc['sesion_origen_id'];

        $this->db->prepare("
            INSERT INTO vale_regularizacion
                (codigo, incidencia_origen_id, sesion_origen_id, monto, descripcion, generado_por_id)
            VALUES (:cod, :inc, :sid, :mon, :desc, :reg)
        ")->execute([
            'cod'  => $codigo,
            'inc'  => $incidenciaId,
            'sid'  => $sesionOrigenId,
            'mon'  => round($monto, 2),
            'desc' => $descripcion ?: null,
            'reg'  => $generadoPorId,
        ]);

        // Ajuste COBRO+QUITAR en la sesión origen: el sobrante queda explicado
        require_once __DIR__ . '/CajaRepository.php';
        (new \CajaRepository())->addAjusteEsperado(
            $sesionOrigenId,
            'COBRO',
            'QUITAR',
            "Vale regularización {$codigo} — salida sesión #{$sesionOrigenId}",
            round($monto, 2),
            $generadoPorId
        );

        return $codigo;
    }

    public function getValesDisponibles(): array
    {
        return $this->db->query("
            SELECT vr.*,
                   ic.tipo  AS inc_tipo,
                   sc.fecha_operacion,
                   l.descripcion AS local_desc,
                   p.nombres     AS generado_por_nombre
            FROM vale_regularizacion vr
            JOIN incidencia_contable ic ON ic.id_incidencia = vr.incidencia_origen_id
            JOIN sesion_caja sc         ON sc.id_sesion      = vr.sesion_origen_id
            JOIN caja c                 ON c.id_caja         = sc.caja_id
            JOIN local l                ON l.id_local        = c.local_id
            LEFT JOIN postulante p      ON p.id_postulante   = vr.generado_por_id
            WHERE vr.estado = 'DISPONIBLE'
            ORDER BY vr.generado_en DESC
        ")->fetchAll();
    }

    public function getValesByIncidencia(int $incidenciaId): array
    {
        $stmt = $this->db->prepare("
            SELECT vr.*,
                   p.nombres AS generado_por_nombre,
                   pu.nombres AS usado_por_nombre
            FROM vale_regularizacion vr
            LEFT JOIN postulante p  ON p.id_postulante  = vr.generado_por_id
            LEFT JOIN postulante pu ON pu.id_postulante = vr.usado_por_id
            WHERE vr.incidencia_origen_id = :id OR vr.incidencia_destino_id = :id2
            ORDER BY vr.generado_en DESC
        ");
        $stmt->execute(['id' => $incidenciaId, 'id2' => $incidenciaId]);
        return $stmt->fetchAll();
    }

    public function usarVale(int $valeId, int $incidenciaDestinoId, int $usuarioId): void
    {
        $stmt = $this->db->prepare("SELECT * FROM vale_regularizacion WHERE id = :id AND estado = 'DISPONIBLE'");
        $stmt->execute(['id' => $valeId]);
        $vale = $stmt->fetch();
        if (!$vale) throw new \RuntimeException('Vale no disponible o ya fue usado');

        $incDest = $this->getById($incidenciaDestinoId);
        if (!$incDest) throw new \RuntimeException('Incidencia destino no encontrada');

        $sesionDestId = (int)$incDest['sesion_origen_id'];
        $monto        = (float)$vale['monto'];
        $codigo       = $vale['codigo'];

        // 1. ABONO en la incidencia origen
        $this->addMovimiento(
            (int)$vale['incidencia_origen_id'],
            'ABONO',
            $monto,
            $usuarioId,
            $sesionDestId,
            "Regularizado con {$codigo} — sesión #{$sesionDestId}"
        );

        // 2. Ajuste AGREGAR al esperado de la sesión destino (explica el sobrante)
        require_once __DIR__ . '/CajaRepository.php';
        (new \CajaRepository())->addAjusteEsperado(
            $sesionDestId,
            'COBRO',
            'AGREGAR',
            "Vale regularización {$codigo} — incidencia #{$vale['incidencia_origen_id']} sesión #{$vale['sesion_origen_id']}",
            $monto,
            $usuarioId
        );

        // 3. Marcar vale como usado
        $this->db->prepare("
            UPDATE vale_regularizacion
               SET estado = 'USADO',
                   incidencia_destino_id = :idest,
                   sesion_destino_id     = :sdest,
                   usado_por_id          = :uid,
                   usado_en              = NOW()
             WHERE id = :id
        ")->execute([
            'idest' => $incidenciaDestinoId,
            'sdest' => $sesionDestId,
            'uid'   => $usuarioId,
            'id'    => $valeId,
        ]);
    }

    public function anularVale(int $valeId, int $usuarioId): void
    {
        $stmt = $this->db->prepare("SELECT codigo, sesion_origen_id FROM vale_regularizacion WHERE id = :id AND estado = 'DISPONIBLE'");
        $stmt->execute(['id' => $valeId]);
        $vale = $stmt->fetch();
        if (!$vale) return;

        $this->db->prepare("
            UPDATE vale_regularizacion SET estado = 'ANULADO' WHERE id = :id
        ")->execute(['id' => $valeId]);

        // Eliminar el ajuste QUITAR que se creó al generar el vale
        $this->db->prepare("
            DELETE FROM ajuste_esperado
             WHERE sesion_id = :sid
               AND tipo = 'COBRO'
               AND accion = 'QUITAR'
               AND descripcion LIKE :desc
        ")->execute(['sid' => (int)$vale['sesion_origen_id'], 'desc' => "Vale regularización {$vale['codigo']}%"]);
    }

    public function eliminarMovimiento(int $movId): void
    {
        $stmt = $this->db->prepare("SELECT incidencia_id FROM movimiento_incidencia_contable WHERE id_movimiento = :id AND tipo IN ('ABONO','CONDONACION')");
        $stmt->execute(['id' => $movId]);
        $row = $stmt->fetch();
        if (!$row) throw new \RuntimeException('Movimiento no encontrado o no es editable');

        $this->db->prepare("DELETE FROM movimiento_incidencia_contable WHERE id_movimiento = :id")->execute(['id' => $movId]);
        $this->recalcularPendiente((int)$row['incidencia_id']);
    }

    public function editarMovimiento(int $movId, float $monto, string $descripcion): void
    {
        $stmt = $this->db->prepare("SELECT incidencia_id FROM movimiento_incidencia_contable WHERE id_movimiento = :id AND tipo IN ('ABONO','CONDONACION')");
        $stmt->execute(['id' => $movId]);
        $row = $stmt->fetch();
        if (!$row) throw new \RuntimeException('Movimiento no encontrado o no es editable');

        $this->db->prepare("
            UPDATE movimiento_incidencia_contable
               SET monto = :monto, descripcion = :desc
             WHERE id_movimiento = :id
        ")->execute(['monto' => round($monto, 2), 'desc' => $descripcion ?: null, 'id' => $movId]);
        $this->recalcularPendiente((int)$row['incidencia_id']);
    }

    public function editarVale(int $valeId, float $monto, string $descripcion): void
    {
        $this->db->prepare("
            UPDATE vale_regularizacion
               SET monto = :monto, descripcion = :desc
             WHERE id = :id AND estado = 'DISPONIBLE'
        ")->execute(['monto' => round($monto, 2), 'desc' => $descripcion ?: null, 'id' => $valeId]);
    }

    public function revertirVale(int $valeId): void
    {
        $stmt = $this->db->prepare("SELECT * FROM vale_regularizacion WHERE id = :id AND estado = 'USADO'");
        $stmt->execute(['id' => $valeId]);
        $vale = $stmt->fetch();
        if (!$vale) throw new \RuntimeException('Vale no encontrado o no está en estado USADO');

        $codigo     = $vale['codigo'];
        $incOrigen  = (int)$vale['incidencia_origen_id'];
        $sesionDest = (int)$vale['sesion_destino_id'];

        // 1. Eliminar el ABONO generado en la incidencia origen
        $this->db->prepare("
            DELETE FROM movimiento_incidencia_contable
             WHERE incidencia_id = :inc
               AND tipo = 'ABONO'
               AND descripcion LIKE :desc
        ")->execute(['inc' => $incOrigen, 'desc' => "Regularizado con {$codigo}%"]);

        // 2. Eliminar el ajuste QUITAR de la sesión origen (creado al generar)
        $this->db->prepare("
            DELETE FROM ajuste_esperado
             WHERE sesion_id = :sid
               AND tipo = 'COBRO'
               AND accion = 'QUITAR'
               AND descripcion LIKE :desc
        ")->execute(['sid' => (int)$vale['sesion_origen_id'], 'desc' => "Vale regularización {$codigo}%"]);

        // 3. Eliminar el ajuste AGREGAR de la sesión destino (creado al aplicar)
        $this->db->prepare("
            DELETE FROM ajuste_esperado
             WHERE sesion_id = :sid
               AND tipo = 'COBRO'
               AND accion = 'AGREGAR'
               AND descripcion LIKE :desc
        ")->execute(['sid' => $sesionDest, 'desc' => "Vale regularización {$codigo}%"]);

        // 4. Marcar vale como ANULADO y limpiar datos de uso
        $this->db->prepare("
            UPDATE vale_regularizacion
               SET estado = 'ANULADO',
                   incidencia_destino_id = NULL,
                   sesion_destino_id     = NULL,
                   usado_por_id          = NULL,
                   usado_en              = NULL
             WHERE id = :id
        ")->execute(['id' => $valeId]);

        // 5. Recalcular pendiente de la incidencia origen
        $this->recalcularPendiente($incOrigen);
    }
}
