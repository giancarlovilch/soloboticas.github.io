<?php

require_once __DIR__ . '/../Core/Database.php';

class SoloBankRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->ensureTable();
    }

    private function ensureTable(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS solobank_vales (
                id             INT AUTO_INCREMENT PRIMARY KEY,
                codigo         VARCHAR(64)    NOT NULL,
                caja           VARCHAR(20)    NOT NULL,
                fecha          DATE           NOT NULL,
                turno          VARCHAR(10)    NOT NULL DEFAULT 'Tarde',
                total          DECIMAL(10,2)  NOT NULL DEFAULT 0,
                conteo         INT            NOT NULL DEFAULT 0,
                estado         ENUM('DISPONIBLE','USADO') NOT NULL DEFAULT 'DISPONIBLE',
                sesion_id      INT            NULL,
                movimiento_id  INT            NULL,
                recibido_en    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
                actualizado_en DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_codigo (codigo),
                INDEX idx_estado (estado),
                INDEX idx_fecha  (fecha)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function upsertVale(array $d): void
    {
        $stmt = $this->db->prepare(
            "SELECT id, estado, movimiento_id FROM solobank_vales WHERE codigo = :codigo"
        );
        $stmt->execute(['codigo' => $d['codigo']]);
        $existing = $stmt->fetch();

        if ($existing) {
            $this->db->prepare("
                UPDATE solobank_vales
                   SET total = :total, conteo = :conteo, actualizado_en = NOW()
                 WHERE codigo = :codigo
            ")->execute(['total' => $d['total'], 'conteo' => $d['conteo'], 'codigo' => $d['codigo']]);

            // Si ya fue usado en caja, actualizar también el movimiento
            if ($existing['estado'] === 'USADO' && $existing['movimiento_id']) {
                $this->db->prepare(
                    "UPDATE movimiento_sesion SET monto = :monto WHERE id_movimiento = :id"
                )->execute(['monto' => $d['total'], 'id' => $existing['movimiento_id']]);
            }
        } else {
            $this->db->prepare("
                INSERT INTO solobank_vales (codigo, caja, fecha, turno, total, conteo)
                VALUES (:codigo, :caja, :fecha, :turno, :total, :conteo)
            ")->execute([
                'codigo' => $d['codigo'],
                'caja'   => $d['caja'],
                'fecha'  => $d['fecha'],
                'turno'  => $d['turno']  ?? 'Tarde',
                'total'  => $d['total'],
                'conteo' => $d['conteo'],
            ]);
        }
    }

    public function getValesDisponibles(): array
    {
        return $this->db->query("
            SELECT id, codigo, caja, fecha, turno, total, conteo
            FROM solobank_vales
            WHERE estado = 'DISPONIBLE'
            ORDER BY fecha DESC, recibido_en DESC
        ")->fetchAll();
    }

    public function usarVale(string $codigo, int $sesionId, int $movimientoId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE solobank_vales
               SET estado = 'USADO', sesion_id = :sid, movimiento_id = :mid
             WHERE codigo = :codigo AND estado = 'DISPONIBLE'
        ");
        $stmt->execute(['sid' => $sesionId, 'mid' => $movimientoId, 'codigo' => $codigo]);
        return $stmt->rowCount() > 0;
    }

    public function liberarVale(int $movimientoId): void
    {
        $this->db->prepare("
            UPDATE solobank_vales
               SET estado = 'DISPONIBLE', sesion_id = NULL, movimiento_id = NULL
             WHERE movimiento_id = :mid
        ")->execute(['mid' => $movimientoId]);
    }

    public function toggleEstado(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT id, estado, sesion_id, movimiento_id FROM solobank_vales WHERE id = :id"
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if (!$row) return null;

        if ($row['estado'] === 'DISPONIBLE') {
            // Marcar como usado manualmente (sin sesión vinculada)
            $this->db->prepare(
                "UPDATE solobank_vales SET estado = 'USADO' WHERE id = :id"
            )->execute(['id' => $id]);
            return ['nuevo_estado' => 'USADO', 'sesion_id' => null];
        } else {
            // Liberar: si tiene movimiento_sesion vinculado, borrarlo también
            // para que el cuadre de la cajera no quede con un cobro huérfano
            if (!empty($row['movimiento_id'])) {
                $this->db->prepare(
                    "DELETE FROM movimiento_sesion
                      WHERE id_movimiento = :mid AND tipo_movimiento_id = 1"
                )->execute(['mid' => $row['movimiento_id']]);
            }
            $this->db->prepare(
                "UPDATE solobank_vales
                    SET estado = 'DISPONIBLE', sesion_id = NULL, movimiento_id = NULL
                  WHERE id = :id"
            )->execute(['id' => $id]);
            return [
                'nuevo_estado' => 'DISPONIBLE',
                'sesion_id'    => $row['sesion_id'],
            ];
        }
    }

    public function getAll(?string $fecha = null): array
    {
        $where = $fecha ? "WHERE sv.fecha = :fecha" : "";
        $params = $fecha ? ['fecha' => $fecha] : [];
        $stmt = $this->db->prepare("
            SELECT sv.*,
                   c.descripcion AS caja_local_desc,
                   l.descripcion AS local_desc
            FROM solobank_vales sv
            LEFT JOIN sesion_caja sc ON sc.id_sesion = sv.sesion_id
            LEFT JOIN caja        c  ON c.id_caja    = sc.caja_id
            LEFT JOIN local       l  ON l.id_local   = c.local_id
            $where
            ORDER BY sv.fecha DESC, sv.recibido_en DESC
            LIMIT 500
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
