<?php

require_once __DIR__ . '/../Core/Database.php';

class PlinRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // ── Catálogos ──────────────────────────────────────────

    public function getCajasActivas(): array
    {
        return $this->db->query(
            "SELECT c.id_caja AS id, c.descripcion, l.descripcion AS local_desc
             FROM caja c INNER JOIN local l ON l.id_local = c.local_id
             WHERE c.activo = 1 ORDER BY l.descripcion, c.descripcion"
        )->fetchAll();
    }

    public function getTurnos(): array
    {
        return $this->db->query(
            "SELECT id_turno AS id, descripcion FROM turno WHERE activo = 1 ORDER BY id_turno"
        )->fetchAll();
    }

    public function getStaffActivo(): array
    {
        return $this->db->query(
            "SELECT p.id_postulante AS id, p.nombres
             FROM postulante p INNER JOIN usuario u ON p.id_postulante = u.postulante_id
             WHERE u.activo = 1 ORDER BY p.nombres"
        )->fetchAll();
    }

    // ── Sesiones PLIN ──────────────────────────────────────

    public function crearSesion(int $cajaId, int $turnoId, string $fecha,
                                 ?int $cajeraId, ?int $vendedoraId, int $abiertosPor): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO sesion_plin (caja_id, turno_id, fecha, cajera_id, vendedora_id, abierta_por)
            VALUES (:caja, :turno, :fecha, :cajera, :vendedora, :abierto)
        ");
        $stmt->execute([
            'caja'      => $cajaId,
            'turno'     => $turnoId,
            'fecha'     => $fecha,
            'cajera'    => $cajeraId,
            'vendedora' => $vendedoraId,
            'abierto'   => $abiertosPor,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function getSesion(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT sp.*,
                   c.descripcion  AS caja_desc,
                   l.descripcion  AS local_desc,
                   t.descripcion  AS turno_desc,
                   pcaj.nombres   AS cajera_nombre,
                   pven.nombres   AS vendedora_nombre,
                   pabi.nombres   AS abierta_por_nombre
            FROM sesion_plin sp
            INNER JOIN caja   c    ON c.id_caja     = sp.caja_id
            INNER JOIN local  l    ON l.id_local    = c.local_id
            INNER JOIN turno  t    ON t.id_turno    = sp.turno_id
            LEFT  JOIN postulante pcaj ON pcaj.id_postulante = sp.cajera_id
            LEFT  JOIN postulante pven ON pven.id_postulante = sp.vendedora_id
            LEFT  JOIN postulante pabi ON pabi.id_postulante = sp.abierta_por
            WHERE sp.id = :id
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function getSesiones(int $limit = 50): array
    {
        return $this->db->query("
            SELECT sp.*,
                   c.descripcion  AS caja_desc,
                   l.descripcion  AS local_desc,
                   t.descripcion  AS turno_desc,
                   pcaj.nombres   AS cajera_nombre,
                   pven.nombres   AS vendedora_nombre
            FROM sesion_plin sp
            INNER JOIN caja   c    ON c.id_caja     = sp.caja_id
            INNER JOIN local  l    ON l.id_local    = c.local_id
            INNER JOIN turno  t    ON t.id_turno    = sp.turno_id
            LEFT  JOIN postulante pcaj ON pcaj.id_postulante = sp.cajera_id
            LEFT  JOIN postulante pven ON pven.id_postulante = sp.vendedora_id
            ORDER BY sp.abierta_en DESC
            LIMIT $limit
        ")->fetchAll();
    }

    public function cerrarSesion(int $id): void
    {
        $stmt = $this->db->prepare("
            UPDATE sesion_plin
               SET estado = 'CERRADA', cerrada_en = NOW()
             WHERE id = :id AND estado = 'ABIERTA'
        ");
        $stmt->execute(['id' => $id]);
    }

    // ── Pagos PLIN (pagos_bbva) ────────────────────────────

    public function getPagosLibres(int $limit = 100): array
    {
        return $this->db->query("
            SELECT id, cliente, monto, fecha_notif, titulo, texto,
                   texto_big, subtexto, texto_extra, app_origen
            FROM pagos_bbva
            WHERE sesion_plin_id IS NULL
            ORDER BY timestamp_notif DESC
            LIMIT $limit
        ")->fetchAll();
    }

    public function getPagosBBVA(int $limit = 50): array
    {
        return $this->db->query("
            SELECT pb.id, pb.cliente, pb.monto, pb.fecha_notif,
                   pb.titulo, pb.texto, pb.subtexto, pb.app_origen,
                   pb.sesion_plin_id,
                   sp.id AS sp_id,
                   c.descripcion AS caja_desc
            FROM pagos_bbva pb
            LEFT JOIN sesion_plin sp ON sp.id = pb.sesion_plin_id
            LEFT JOIN caja c ON c.id_caja = sp.caja_id
            ORDER BY pb.timestamp_notif DESC
            LIMIT $limit
        ")->fetchAll();
    }

    public function reclamarPago(int $sesionId, int $pagoId, int $userId): bool
    {
        // Obtener pago libre (con lock)
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("
                SELECT id, monto, cliente, fecha_notif, app_origen,
                       titulo, texto, texto_big, subtexto, texto_extra,
                       texto_resumen, raw
                FROM pagos_bbva
                WHERE id = :id AND sesion_plin_id IS NULL
                FOR UPDATE
            ");
            $stmt->execute(['id' => $pagoId]);
            $pago = $stmt->fetch();

            if (!$pago) {
                $this->db->rollBack();
                return false; // ya reclamado o no existe
            }

            // Verificar sesión abierta
            $sSesion = $this->db->prepare(
                "SELECT id, caja_id FROM sesion_plin WHERE id = :id AND estado = 'ABIERTA'"
            );
            $sSesion->execute(['id' => $sesionId]);
            $sesion = $sSesion->fetch();
            if (!$sesion) {
                $this->db->rollBack();
                return false;
            }

            // Insertar en pagos_qr
            $ins = $this->db->prepare("
                INSERT INTO pagos_qr
                    (sesion_plin_id, pago_bbva_id, caja_id, monto, cliente,
                     fecha_notif, app_origen, titulo, texto, texto_big,
                     subtexto, texto_extra, texto_resumen, raw, reclamado_por)
                VALUES
                    (:sid, :pid, :cid, :monto, :cliente,
                     :fecha_notif, :app_origen, :titulo, :texto, :texto_big,
                     :subtexto, :texto_extra, :texto_resumen, :raw, :uid)
            ");
            $ins->execute([
                'sid'          => $sesionId,
                'pid'          => $pagoId,
                'cid'          => $sesion['caja_id'],
                'monto'        => $pago['monto'],
                'cliente'      => $pago['cliente'],
                'fecha_notif'  => $pago['fecha_notif'],
                'app_origen'   => $pago['app_origen'],
                'titulo'       => $pago['titulo'],
                'texto'        => $pago['texto'],
                'texto_big'    => $pago['texto_big'],
                'subtexto'     => $pago['subtexto'],
                'texto_extra'  => $pago['texto_extra'],
                'texto_resumen'=> $pago['texto_resumen'],
                'raw'          => $pago['raw'],
                'uid'          => $userId,
            ]);

            // Marcar pago_bbva como reclamado
            $this->db->prepare(
                "UPDATE pagos_bbva SET sesion_plin_id = :sid WHERE id = :id"
            )->execute(['sid' => $sesionId, 'id' => $pagoId]);

            // Actualizar totales de la sesión
            $this->db->prepare("
                UPDATE sesion_plin
                   SET total_reclamado = total_reclamado + :monto,
                       num_pagos       = num_pagos + 1
                 WHERE id = :id
            ")->execute(['monto' => $pago['monto'], 'id' => $sesionId]);

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getPagosReclamados(int $sesionId): array
    {
        $stmt = $this->db->prepare("
            SELECT pq.*, p.nombres AS reclamado_por_nombre
            FROM pagos_qr pq
            LEFT JOIN postulante p ON p.id_postulante = pq.reclamado_por
            WHERE pq.sesion_plin_id = :sid
            ORDER BY pq.reclamado_en DESC
        ");
        $stmt->execute(['sid' => $sesionId]);
        return $stmt->fetchAll();
    }

    public function totalPagosLibres(): int
    {
        return (int) $this->db->query(
            "SELECT COUNT(*) FROM pagos_bbva WHERE sesion_plin_id IS NULL"
        )->fetchColumn();
    }
}
