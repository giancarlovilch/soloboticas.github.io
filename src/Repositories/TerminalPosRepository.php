<?php

require_once __DIR__ . '/../Core/Database.php';

// Conciliación de cobros por POS (terminales de tarjeta). El proveedor (hoy Culqi)
// no tiene API: el usuario descarga periódicamente un .txt pipe-delimited y lo sube
// desde /caja/conciliacion. Cada terminal físico está fijo a una caja (caja_terminal).
// Las transacciones se agrupan por lote (columna "Lote" del archivo, corte de POS) y
// cada lote se asigna MANUALMENTE a un cuadre (sesion_caja) desde /incidencias/{id},
// igual que los vales SoloBank — nunca se auto-asigna por orden/horario.
class TerminalPosRepository
{
    private PDO $db;

    // Solo importado=1, devolucion=0, estado<>'rechazada' cuenta en los totales del lote.
    private const AGREGADO_WHERE = "incluido = 1 AND devolucion = 0 AND estado <> 'rechazada'";

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->ensureTables();
    }

    private function ensureTables(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS caja_terminal (
                id             INT AUTO_INCREMENT PRIMARY KEY,
                terminal_id    VARCHAR(20) NOT NULL,
                caja_id        INT NOT NULL,
                activo         TINYINT(1) NOT NULL DEFAULT 1,
                fecha_registro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_terminal (terminal_id),
                KEY idx_ct_caja (caja_id),
                CONSTRAINT fk_ct_caja FOREIGN KEY (caja_id) REFERENCES caja (id_caja) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->db->exec("
            CREATE TABLE IF NOT EXISTS cobro_terminal_lote (
                id                     INT AUTO_INCREMENT PRIMARY KEY,
                terminal_id            VARCHAR(20) NOT NULL,
                caja_id                INT NULL,
                fecha                  DATE NOT NULL,
                numero_lote            VARCHAR(10) NOT NULL,
                cantidad_transacciones INT NOT NULL DEFAULT 0,
                monto_total            DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                comision_total         DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                monto_abono_total      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                estado                 ENUM('DISPONIBLE','ASIGNADO') NOT NULL DEFAULT 'DISPONIBLE',
                sesion_id              INT NULL,
                asignado_por           INT NULL,
                fecha_asignacion       TIMESTAMP NULL,
                fecha_actualizacion    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_lote (terminal_id, fecha, numero_lote),
                KEY idx_lote_sesion (sesion_id),
                KEY idx_lote_caja (caja_id),
                CONSTRAINT fk_lote_caja   FOREIGN KEY (caja_id)   REFERENCES caja (id_caja) ON DELETE SET NULL,
                CONSTRAINT fk_lote_sesion FOREIGN KEY (sesion_id) REFERENCES sesion_caja (id_sesion) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->db->exec("
            CREATE TABLE IF NOT EXISTS cobro_terminal_importado (
                id                  INT AUTO_INCREMENT PRIMARY KEY,
                id_transaccion      VARCHAR(30) NOT NULL,
                id_venta            VARCHAR(60) NULL,
                lote_id             INT NULL,
                terminal_id         VARCHAR(20) NOT NULL,
                serie_terminal      VARCHAR(30) NULL,
                numero_lote         VARCHAR(10) NULL,
                marca               VARCHAR(20) NULL,
                tipo_pago           VARCHAR(10) NULL,
                ult4                VARCHAR(4)  NULL,
                nombre_banco        VARCHAR(80) NULL,
                codigo_referencia   VARCHAR(30) NULL,
                codigo_autorizacion VARCHAR(20) NULL,
                fecha_transaccion   DATE NOT NULL,
                hora_transaccion    TIME NOT NULL,
                devolucion          TINYINT(1) NOT NULL DEFAULT 0,
                monto_venta         DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                comision_emisor     DECIMAL(10,2) NULL,
                comision_culqi      DECIMAL(10,2) NULL,
                igv_total           DECIMAL(10,2) NULL,
                comision_total      DECIMAL(10,2) NULL,
                monto_abono         DECIMAL(10,2) NULL,
                estado              VARCHAR(30) NOT NULL,
                categoria_estado    VARCHAR(40) NULL,
                incluido            TINYINT(1) NOT NULL DEFAULT 1,
                archivo_origen      VARCHAR(150) NULL,
                fecha_importacion   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                fecha_actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_id_transaccion (id_transaccion),
                KEY idx_cti_lote (lote_id),
                KEY idx_cti_terminal_fecha (terminal_id, fecha_transaccion),
                CONSTRAINT fk_cti_lote FOREIGN KEY (lote_id) REFERENCES cobro_terminal_lote (id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    // ── Mapeo terminal → caja ───────────────────────────────
    public function getMapeoTerminales(): array
    {
        return $this->db->query("
            SELECT ct.id, ct.terminal_id, ct.caja_id, ct.activo,
                   c.descripcion AS caja_desc, l.descripcion AS local_desc
            FROM caja_terminal ct
            INNER JOIN caja c ON c.id_caja = ct.caja_id
            INNER JOIN local l ON l.id_local = c.local_id
            ORDER BY ct.terminal_id
        ")->fetchAll();
    }

    /** Terminales vistos en importaciones que aún no tienen fila en caja_terminal. */
    public function getTerminalesSinMapear(): array
    {
        return $this->db->query("
            SELECT DISTINCT cti.terminal_id
            FROM cobro_terminal_importado cti
            LEFT JOIN caja_terminal ct ON ct.terminal_id = cti.terminal_id
            WHERE ct.id IS NULL
            ORDER BY cti.terminal_id
        ")->fetchAll(PDO::FETCH_COLUMN);
    }

    public function upsertMapeoTerminal(string $terminalId, int $cajaId): void
    {
        $this->db->prepare("
            INSERT INTO caja_terminal (terminal_id, caja_id, activo)
            VALUES (:tid, :cid, 1)
            ON DUPLICATE KEY UPDATE caja_id = VALUES(caja_id), activo = 1
        ")->execute(['tid' => $terminalId, 'cid' => $cajaId]);

        $this->recalcLotesDeTerminal($terminalId);
    }

    // ── Importación del archivo ─────────────────────────────
    public function importarArchivo(string $tmpPath, string $filename): array
    {
        $lines = file($tmpPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false || count($lines) < 2) {
            throw new RuntimeException('El archivo está vacío o no tiene filas de datos.');
        }

        $headers = array_map('trim', explode('|', array_shift($lines)));
        $idx     = array_flip($headers);

        $col = function (array $cols, string $name) use ($idx) {
            if (!isset($idx[$name])) return null;
            $v = $cols[$idx[$name]] ?? '';
            $v = trim($v);
            return $v === '' ? null : $v;
        };
        $num = fn($v) => $v === null ? null : (float)$v;

        $upsert = $this->db->prepare("
            INSERT INTO cobro_terminal_importado
                (id_transaccion, id_venta, terminal_id, serie_terminal, numero_lote,
                 marca, tipo_pago, ult4, nombre_banco, codigo_referencia, codigo_autorizacion,
                 fecha_transaccion, hora_transaccion, devolucion, monto_venta,
                 comision_emisor, comision_culqi, igv_total, comision_total, monto_abono,
                 estado, categoria_estado, incluido, archivo_origen)
            VALUES
                (:id_transaccion, :id_venta, :terminal_id, :serie_terminal, :numero_lote,
                 :marca, :tipo_pago, :ult4, :nombre_banco, :codigo_referencia, :codigo_autorizacion,
                 :fecha_transaccion, :hora_transaccion, :devolucion, :monto_venta,
                 :comision_emisor, :comision_culqi, :igv_total, :comision_total, :monto_abono,
                 :estado, :categoria_estado, :incluido, :archivo_origen)
            ON DUPLICATE KEY UPDATE
                nombre_banco        = VALUES(nombre_banco),
                codigo_autorizacion = VALUES(codigo_autorizacion),
                monto_venta         = VALUES(monto_venta),
                comision_emisor     = VALUES(comision_emisor),
                comision_culqi      = VALUES(comision_culqi),
                igv_total           = VALUES(igv_total),
                comision_total      = VALUES(comision_total),
                monto_abono         = VALUES(monto_abono),
                estado              = VALUES(estado),
                categoria_estado    = VALUES(categoria_estado)
        ");

        $nuevos = 0;
        $actualizados = 0;
        $terminalesVistos = [];
        $lotesTocados = []; // key = terminal|fecha|numero_lote

        $this->db->beginTransaction();
        try {
            foreach ($lines as $line) {
                if (trim($line) === '') continue;
                $cols = explode('|', $line);

                $idTransaccion = $col($cols, 'ID Transaccion');
                $terminalId    = $col($cols, 'ID Terminal');
                $fecha         = $col($cols, 'Fecha de la transaccion');
                $hora          = $col($cols, 'Hora de la transaccion');
                $numeroLote    = $col($cols, 'Lote');
                if (!$idTransaccion || !$terminalId || !$fecha || !$hora) continue; // fila incompleta

                $terminalesVistos[$terminalId] = true;

                $params = [
                    'id_transaccion'      => $idTransaccion,
                    'id_venta'            => $col($cols, 'ID Venta'),
                    'terminal_id'         => $terminalId,
                    'serie_terminal'      => $col($cols, 'Serie Terminal'),
                    'numero_lote'         => $numeroLote,
                    'marca'               => $col($cols, 'Marca'),
                    'tipo_pago'           => $col($cols, 'Modo de pago'),
                    'ult4'                => $col($cols, 'Ult. 4 digitos'),
                    'nombre_banco'        => $col($cols, 'Nombre Banco'),
                    'codigo_referencia'   => $col($cols, 'Codigo Referencia'),
                    'codigo_autorizacion' => $col($cols, 'Codigo Autorizacion'),
                    'fecha_transaccion'   => $fecha,
                    'hora_transaccion'    => $hora,
                    'devolucion'          => $col($cols, 'Devolucion') === '1' ? 1 : 0,
                    'monto_venta'         => $num($col($cols, 'Monto VENTA')) ?? 0,
                    'comision_emisor'     => $num($col($cols, 'Comision Emisor')),
                    'comision_culqi'      => $num($col($cols, 'Comision Culqi')),
                    'igv_total'           => $num($col($cols, 'IGV TOTAL')),
                    'comision_total'      => $num($col($cols, 'Comision TOTAL')),
                    'monto_abono'         => $num($col($cols, 'Monto Aproximado Abono')),
                    'estado'              => $col($cols, 'Estado') ?? 'desconocido',
                    'categoria_estado'    => $col($cols, 'Categoria de Estado'),
                    'incluido'            => $col($cols, 'Devolucion') === '1' ? 0 : 1,
                    'archivo_origen'      => $filename,
                ];

                $upsert->execute($params);
                if ($upsert->rowCount() === 1) $nuevos++;
                else $actualizados++; // ON DUPLICATE KEY UPDATE reporta 2 filas afectadas

                if ($numeroLote) {
                    $lotesTocados["{$terminalId}|{$fecha}|{$numeroLote}"] = [$terminalId, $fecha, $numeroLote];
                }
            }

            foreach ($lotesTocados as [$terminalId, $fecha, $numeroLote]) {
                $this->recalcLote($terminalId, $fecha, $numeroLote);
            }

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }

        $sinMapear = array_values(array_diff(array_keys($terminalesVistos), array_column($this->getMapeoTerminales(), 'terminal_id')));

        return [
            'nuevos'               => $nuevos,
            'actualizados'         => $actualizados,
            'lotes_afectados'      => count($lotesTocados),
            'sin_terminal_mapeado' => $sinMapear,
        ];
    }

    // ── Recalculo de lotes (totales + caja resuelta) ────────
    private function recalcLote(string $terminalId, string $fecha, string $numeroLote): void
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) AS cantidad,
                   SUM(CASE WHEN " . self::AGREGADO_WHERE . " THEN monto_venta ELSE 0 END) AS monto_total,
                   SUM(CASE WHEN " . self::AGREGADO_WHERE . " THEN COALESCE(comision_total,0) ELSE 0 END) AS comision_total,
                   SUM(CASE WHEN " . self::AGREGADO_WHERE . " THEN COALESCE(monto_abono,0) ELSE 0 END) AS monto_abono_total
            FROM cobro_terminal_importado
            WHERE terminal_id = :tid AND fecha_transaccion = :f AND numero_lote = :l
        ");
        $stmt->execute(['tid' => $terminalId, 'f' => $fecha, 'l' => $numeroLote]);
        $agg = $stmt->fetch();
        if (!$agg) return;

        $cajaStmt = $this->db->prepare("SELECT caja_id FROM caja_terminal WHERE terminal_id = :tid AND activo = 1");
        $cajaStmt->execute(['tid' => $terminalId]);
        $cajaId = $cajaStmt->fetchColumn() ?: null;

        $this->db->prepare("
            INSERT INTO cobro_terminal_lote
                (terminal_id, caja_id, fecha, numero_lote, cantidad_transacciones, monto_total, comision_total, monto_abono_total)
            VALUES
                (:tid, :cid, :f, :l, :cant, :monto, :comision, :abono)
            ON DUPLICATE KEY UPDATE
                caja_id                = VALUES(caja_id),
                cantidad_transacciones = VALUES(cantidad_transacciones),
                monto_total            = VALUES(monto_total),
                comision_total         = VALUES(comision_total),
                monto_abono_total      = VALUES(monto_abono_total)
        ")->execute([
            'tid' => $terminalId, 'cid' => $cajaId, 'f' => $fecha, 'l' => $numeroLote,
            'cant' => (int)$agg['cantidad'], 'monto' => $agg['monto_total'],
            'comision' => $agg['comision_total'], 'abono' => $agg['monto_abono_total'],
        ]);

        $this->db->prepare("
            UPDATE cobro_terminal_importado cti
            INNER JOIN cobro_terminal_lote l
                ON l.terminal_id = cti.terminal_id AND l.fecha = cti.fecha_transaccion AND l.numero_lote = cti.numero_lote
            SET cti.lote_id = l.id
            WHERE cti.terminal_id = :tid AND cti.fecha_transaccion = :f AND cti.numero_lote = :l
        ")->execute(['tid' => $terminalId, 'f' => $fecha, 'l' => $numeroLote]);
    }

    private function recalcLotesDeTerminal(string $terminalId): void
    {
        $stmt = $this->db->prepare("
            SELECT DISTINCT fecha_transaccion, numero_lote
            FROM cobro_terminal_importado
            WHERE terminal_id = :tid AND numero_lote IS NOT NULL
        ");
        $stmt->execute(['tid' => $terminalId]);
        foreach ($stmt->fetchAll() as $row) {
            $this->recalcLote($terminalId, $row['fecha_transaccion'], $row['numero_lote']);
        }
    }

    // ── Consultas de lotes ───────────────────────────────────
    public function getLotes(array $filtros = []): array
    {
        $where  = "1=1";
        $params = [];
        if (!empty($filtros['desde'])) { $where .= " AND l.fecha >= :desde"; $params['desde'] = $filtros['desde']; }
        if (!empty($filtros['hasta'])) { $where .= " AND l.fecha <= :hasta"; $params['hasta'] = $filtros['hasta']; }
        if (!empty($filtros['caja_id'])) { $where .= " AND l.caja_id = :caja_id"; $params['caja_id'] = $filtros['caja_id']; }
        if (!empty($filtros['terminal_id'])) { $where .= " AND l.terminal_id = :terminal_id"; $params['terminal_id'] = $filtros['terminal_id']; }
        if (!empty($filtros['estado'])) { $where .= " AND l.estado = :estado"; $params['estado'] = $filtros['estado']; }

        $sql = "
            SELECT l.*, c.descripcion AS caja_desc, loc.descripcion AS local_desc,
                   ROW_NUMBER() OVER (PARTITION BY l.terminal_id, l.fecha ORDER BY l.numero_lote) AS orden_dia
            FROM cobro_terminal_lote l
            LEFT JOIN caja c ON c.id_caja = l.caja_id
            LEFT JOIN local loc ON loc.id_local = c.local_id
            WHERE {$where}
            ORDER BY l.fecha DESC, l.terminal_id, l.numero_lote
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getLotesDisponibles(int $cajaId): array
    {
        $stmt = $this->db->prepare("
            SELECT l.*,
                   ROW_NUMBER() OVER (PARTITION BY l.terminal_id, l.fecha ORDER BY l.numero_lote) AS orden_dia
            FROM cobro_terminal_lote l
            WHERE l.estado = 'DISPONIBLE' AND l.caja_id = :cid
            ORDER BY l.fecha DESC, l.terminal_id, l.numero_lote
        ");
        $stmt->execute(['cid' => $cajaId]);
        return $stmt->fetchAll();
    }

    public function getLotesAsignados(int $sesionId): array
    {
        $stmt = $this->db->prepare("
            SELECT l.*,
                   EXISTS(
                       SELECT 1 FROM cobro_terminal_importado cti
                       WHERE cti.lote_id = l.id AND " . self::AGREGADO_WHERE . " AND cti.estado = 'aprobada'
                   ) AS tiene_pendientes
            FROM cobro_terminal_lote l
            WHERE l.sesion_id = :sid
            ORDER BY l.fecha, l.numero_lote
        ");
        $stmt->execute(['sid' => $sesionId]);
        return $stmt->fetchAll();
    }

    /** Detalle transacción por transacción (incluye banco) de los lotes asignados a la sesión. */
    public function getTransaccionesAsignadasBySesion(int $sesionId): array
    {
        $stmt = $this->db->prepare("
            SELECT cti.*
            FROM cobro_terminal_importado cti
            INNER JOIN cobro_terminal_lote l ON l.id = cti.lote_id
            WHERE l.sesion_id = :sid
            ORDER BY cti.hora_transaccion
        ");
        $stmt->execute(['sid' => $sesionId]);
        return $stmt->fetchAll();
    }

    public function asignarLote(int $loteId, int $sesionId, int $adminId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE cobro_terminal_lote
               SET estado = 'ASIGNADO', sesion_id = :sid, asignado_por = :admin, fecha_asignacion = NOW()
             WHERE id = :id AND estado = 'DISPONIBLE'
        ");
        $stmt->execute(['sid' => $sesionId, 'admin' => $adminId, 'id' => $loteId]);
        $ok = $stmt->rowCount() > 0;
        if ($ok) $this->autoAprobarSiConforme($sesionId, $adminId);
        return $ok;
    }

    /**
     * Si el total de los lotes POS asignados a la sesión coincide (± 1 céntimo) con lo
     * que la cajera declaró en Visa/POS, aprueba automáticamente esos cobros pendientes.
     * Evita la doble revisión: asignar el sustento aquí y luego ir a /caja/auditoria a
     * aprobar el mismo monto que ya se verificó como conforme. Si no coincide, se deja
     * en PENDIENTE para que el admin lo revise manualmente en /caja/auditoria.
     */
    private function autoAprobarSiConforme(int $sesionId, int $adminId): void
    {
        $importado = $this->db->prepare(
            "SELECT COALESCE(SUM(monto_total), 0) FROM cobro_terminal_lote WHERE sesion_id = :sid"
        );
        $importado->execute(['sid' => $sesionId]);
        $totalImportado = (float)$importado->fetchColumn();

        $declarado = $this->db->prepare(
            "SELECT COALESCE(SUM(ms.monto), 0)
             FROM movimiento_sesion ms
             INNER JOIN modo m ON m.id_modo = ms.modo_id
             WHERE ms.sesion_id = :sid AND ms.tipo_movimiento_id = 1
               AND m.descripcion = 'Visa/POS' AND ms.estado IN ('PENDIENTE', 'APROBADO')"
        );
        $declarado->execute(['sid' => $sesionId]);
        $totalDeclarado = (float)$declarado->fetchColumn();

        if (abs(round($totalImportado - $totalDeclarado, 2)) >= 0.01) return;

        $this->db->prepare(
            "UPDATE movimiento_sesion ms
             INNER JOIN modo m ON m.id_modo = ms.modo_id
             SET ms.estado = 'APROBADO', ms.postulante_revision_id = :admin, ms.fecha_revision = NOW()
             WHERE ms.sesion_id = :sid AND ms.tipo_movimiento_id = 1
               AND m.descripcion = 'Visa/POS' AND ms.estado = 'PENDIENTE'"
        )->execute(['admin' => $adminId, 'sid' => $sesionId]);
    }

    public function quitarAsignacionLote(int $loteId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE cobro_terminal_lote
               SET estado = 'DISPONIBLE', sesion_id = NULL, asignado_por = NULL, fecha_asignacion = NULL
             WHERE id = :id AND estado = 'ASIGNADO'
        ");
        $stmt->execute(['id' => $loteId]);
        return $stmt->rowCount() > 0;
    }

    // ── Detalle de transacciones de un lote (incluye banco) ─
    public function getTransaccionesDeLote(int $loteId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM cobro_terminal_importado
            WHERE lote_id = :lid
            ORDER BY hora_transaccion
        ");
        $stmt->execute(['lid' => $loteId]);
        return $stmt->fetchAll();
    }

    public function toggleIncluidoTransaccion(int $id, bool $incluido): bool
    {
        $stmt = $this->db->prepare("SELECT terminal_id, fecha_transaccion, numero_lote FROM cobro_terminal_importado WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if (!$row) return false;

        $upd = $this->db->prepare("UPDATE cobro_terminal_importado SET incluido = :inc WHERE id = :id");
        $upd->execute(['inc' => $incluido ? 1 : 0, 'id' => $id]);

        if ($row['numero_lote']) {
            $this->recalcLote($row['terminal_id'], $row['fecha_transaccion'], $row['numero_lote']);
        }
        return true;
    }
}
