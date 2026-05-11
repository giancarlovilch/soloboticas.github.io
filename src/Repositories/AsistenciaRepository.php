<?php

require_once __DIR__ . '/../Core/Database.php';

class AsistenciaRepository
{
    private PDO $db;
    private string $table = 'asistencia';

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // ── Total de sesiones de hoy (abiertas + cerradas) ──────
    public function countTodayByPostulante(int $id): int
    {
        $sql  = "SELECT COUNT(*) FROM {$this->table} WHERE postulante_id = :id AND fecha = CURDATE()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return (int)$stmt->fetchColumn();
    }

    // ── Sesión ABIERTA de hoy (entrada marcada, sin salida) ──
    // Devuelve null si no hay sesión abierta → el trabajador puede marcar entrada.
    // Si hay una sesión abierta → debe marcar salida primero.
    // Permite múltiples sesiones al día (distintos locales).
    public function getTodayByPostulante(int $id): ?array
    {
        $sql = "SELECT a.*, l.descripcion AS local_desc
                FROM {$this->table} a
                LEFT JOIN local l ON a.local_id = l.id_local
                WHERE a.postulante_id = :id
                  AND a.fecha = CURDATE()
                  AND a.hora_salida IS NULL
                ORDER BY a.hora_ingreso DESC
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    // ── Historial de un trabajador (últimas N sesiones) ──
    // Puede haber varias filas por día si trabajó en distintos locales.
    public function getByPostulante(int $id, int $limit = 30): array
    {
        $sql = "SELECT a.*, l.descripcion AS local_desc
                FROM {$this->table} a
                LEFT JOIN local l ON a.local_id = l.id_local
                WHERE a.postulante_id = :id
                ORDER BY a.fecha DESC, a.hora_ingreso DESC
                LIMIT {$limit}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetchAll();
    }

    // ── Marcar ingreso ────────────────────────────────────
    // Turnos y márgenes:
    //   TURNO MAÑANA
    //   06:00 – 07:05 → A TIEMPO  (5 min de gracia)
    //   07:06 – 07:59 → TARDE
    //   08:00 – 13:59 → EXTRA
    //
    //   TURNO TARDE
    //   14:00 – 15:05 → TEMPRANO  (5 min de gracia)
    //   15:06 – 15:59 → TARDE
    //   16:00 – 21:59 → EXTRA
    public static function calcularEstadoEntrada(int $hora, int $minuto): string
    {
        $t = $hora * 60 + $minuto;

        if ($t <= 7 * 60 + 5)  return 'A TIEMPO';  // 06:00 – 07:10
        if ($t <  8 * 60)      return 'TARDE';      // 07:06 – 07:59
        if ($t <  14 * 60)     return 'EXTRA';      // 08:00 – 13:59
        if ($t <= 15 * 60 + 5) return 'TEMPRANO';   // 14:00 – 15:10
        if ($t <  16 * 60)     return 'TARDE';      // 15:06 – 15:59
        return 'EXTRA';                              // 16:00 – 21:59
    }

    public function marcarIngreso(int $id, ?int $localId = null): array
    {
        $tz     = new DateTimeZone('America/Lima');
        $now    = new DateTime('now', $tz);
        $hora   = (int)$now->format('H');
        $minuto = (int)$now->format('i');
        $estado = self::calcularEstadoEntrada($hora, $minuto);

        $sql = "INSERT INTO {$this->table}
                    (postulante_id, local_id, fecha, hora_ingreso, estado)
                VALUES (:id, :local, CURDATE(), NOW(), :estado)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id'     => $id,
            'local'  => $localId,
            'estado' => $estado,
        ]);
        return $this->getTodayByPostulante($id) ?? [];
    }

    // ── Marcar salida en la sesión abierta más reciente ──
    public function marcarSalida(int $id): bool
    {
        // Cierra la sesión abierta más reciente del día (hora_salida IS NULL)
        $sql = "UPDATE {$this->table}
                SET hora_salida = NOW()
                WHERE postulante_id = :id
                  AND fecha = CURDATE()
                  AND hora_salida IS NULL
                ORDER BY hora_ingreso DESC
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    // ── Admin: todos los registros con filtros ────────────
    public function getAll(string $fecha = '', int $postulanteId = 0, int $limit = 100): array
    {
        $where = ['1=1'];
        $params = [];

        if ($fecha) {
            $where[]           = 'a.fecha = :fecha';
            $params['fecha']   = $fecha;
        }
        if ($postulanteId) {
            $where[]           = 'a.postulante_id = :pid';
            $params['pid']     = $postulanteId;
        }

        $sql = "SELECT
                    a.id_asistencia,
                    a.postulante_id,
                    CONCAT(p.nombres, ' ', p.apellidos) AS nombre_completo,
                    p.num_documento,
                    a.local_id,
                    l.descripcion AS local_desc,
                    a.fecha,
                    a.hora_ingreso,
                    a.hora_salida,
                    a.estado,
                    a.justificacion,
                    a.observacion
                FROM {$this->table} a
                INNER JOIN postulante p  ON a.postulante_id = p.id_postulante
                LEFT JOIN local l       ON a.local_id = l.id_local
                WHERE " . implode(' AND ', $where) . "
                ORDER BY a.fecha DESC, a.hora_ingreso DESC
                LIMIT {$limit}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ── Staff: registros propios por rango de fechas ──────
    public function getByPostulanteRango(int $postulanteId, string $desde, string $hasta): array
    {
        $stmt = $this->db->prepare(
            "SELECT a.*, l.descripcion AS local_desc,
                    pr.nombres AS registrado_por_nombre
             FROM asistencia a
             LEFT JOIN local l      ON a.local_id          = l.id_local
             LEFT JOIN postulante pr ON a.registrado_por_id = pr.id_postulante
             WHERE a.postulante_id = :pid AND a.fecha BETWEEN :desde AND :hasta
             ORDER BY a.fecha ASC, a.hora_ingreso ASC"
        );
        $stmt->execute(['pid' => $postulanteId, 'desde' => $desde, 'hasta' => $hasta]);
        return $stmt->fetchAll();
    }

    /** Registra asistencia para otro trabajador (quien registra != quien asiste) */
    public function registrarParaCompanhero(
        int $postulanteId, int $registradorId, string $fecha,
        string $horaIngreso, ?string $horaSalida, ?int $localId,
        array $checklist, string $password
    ): bool|string {
        // Verificar contraseña del registrador
        $stmt = $this->db->prepare("SELECT password FROM usuario WHERE postulante_id = :pid LIMIT 1");
        $stmt->execute(['pid' => $registradorId]);
        $hash = $stmt->fetchColumn();
        if (!$hash || !password_verify($password, $hash)) return 'Contraseña incorrecta';

        // No puede registrarse a sí mismo
        if ($postulanteId === $registradorId) return 'No puedes registrar tu propia asistencia';

        // Si no hay hora de entrada → FALTA
        $esFalta = empty($horaIngreso);
        $estadoCalculado = $esFalta ? 'FALTA' : self::calcularEstadoEntrada(
            (int)substr($horaIngreso, 0, 2),
            (int)substr($horaIngreso, 3, 2)
        );

        $ingresoVal = $esFalta ? null : "{$fecha} {$horaIngreso}:00";
        $salidaVal  = (!$esFalta && $horaSalida) ? "{$fecha} {$horaSalida}:00" : null;

        // Upsert: si ya existe para esa fecha, actualizar; si no, crear
        $existing = $this->db->prepare(
            "SELECT id_asistencia FROM asistencia WHERE postulante_id = :pid AND fecha = :fecha ORDER BY id_asistencia DESC LIMIT 1"
        );
        $existing->execute(['pid' => $postulanteId, 'fecha' => $fecha]);
        $existId = $existing->fetchColumn();

        if ($existId) {
            $this->db->prepare(
                "UPDATE asistencia SET hora_ingreso = :ing, hora_salida = :sal, estado = :est,
                         registrado_por_id = :reg, local_id = :lid
                 WHERE id_asistencia = :id"
            )->execute([
                'ing' => $ingresoVal,
                'sal' => $salidaVal,
                'est' => $estadoCalculado,
                'reg' => $registradorId,
                'lid' => $localId,
                'id'  => $existId,
            ]);
            $asistId = $existId;
        } else {
            $this->db->prepare(
                "INSERT INTO asistencia (postulante_id, registrado_por_id, local_id, fecha, hora_ingreso, hora_salida, estado)
                 VALUES (:pid, :reg, :lid, :fecha, :ing, :sal, :est)"
            )->execute([
                'pid'   => $postulanteId,
                'reg'   => $registradorId,
                'lid'   => $localId,
                'fecha' => $fecha,
                'ing'   => $ingresoVal,
                'sal'   => $salidaVal,
                'est'   => $estadoCalculado,
            ]);
            $asistId = (int)$this->db->lastInsertId();
        }

        // Reemplazar checklist (borrar antes para evitar duplicados)
        $this->db->prepare("DELETE FROM asistencia_checklist WHERE asistencia_id = :id")
                 ->execute(['id' => $asistId]);
        if (!empty($checklist)) {
            $this->guardarChecklist($asistId, $checklist);
        }

        return true;
    }

    /** Carga todos los checklist de un conjunto de asistencia IDs de una vez */
    public function getChecklistPorIds(array $ids): array
    {
        if (empty($ids)) return [];
        $in   = implode(',', array_map('intval', $ids));
        $stmt = $this->db->query(
            "SELECT ac.asistencia_id, ac.checklist_id, ac.cumplido, c.descripcion, c.tipo
             FROM asistencia_checklist ac
             INNER JOIN checklist c ON ac.checklist_id = c.id_checklist
             WHERE ac.asistencia_id IN ({$in})
             ORDER BY c.tipo DESC, c.id_checklist ASC"
        );
        $result = [];
        foreach ($stmt->fetchAll() as $r) {
            $result[$r['asistencia_id']][] = $r;
        }
        return $result;
    }

    /** Elimina un registro de FALTA (cualquier compañero con contraseña) */
    public function revertirFalta(int $asistenciaId, int $registradorId, string $password): bool|string
    {
        $stmt = $this->db->prepare("SELECT password FROM usuario WHERE postulante_id = :pid LIMIT 1");
        $stmt->execute(['pid' => $registradorId]);
        $hash = $stmt->fetchColumn();
        if (!$hash || !password_verify($password, $hash)) return 'Contraseña incorrecta';

        $check = $this->db->prepare(
            "SELECT id_asistencia FROM asistencia WHERE id_asistencia = :id AND estado = 'FALTA'"
        );
        $check->execute(['id' => $asistenciaId]);
        if (!$check->fetchColumn()) return 'Registro no encontrado o no es una falta';

        $this->db->prepare("DELETE FROM asistencia_checklist WHERE asistencia_id = :id")->execute(['id' => $asistenciaId]);
        $this->db->prepare("DELETE FROM asistencia WHERE id_asistencia = :id")->execute(['id' => $asistenciaId]);
        return true;
    }

    /** Staff actualiza sus propios tiempos (requiere que sea su registro) */
    public function actualizarTiemposPropio(int $asistenciaId, int $postulanteId, string $ingreso, ?string $salida): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE asistencia
             SET hora_ingreso = :ing, hora_salida = :sal
             WHERE id_asistencia = :id AND postulante_id = :pid"
        );
        $stmt->execute(['ing' => $ingreso, 'sal' => $salida ?: null, 'id' => $asistenciaId, 'pid' => $postulanteId]);
        return $stmt->rowCount() > 0;
    }

    // ── Admin: actualizar cualquier registro ──────────────
    public function actualizar(int $id, array $data): bool
    {
        $intOrNull  = fn($v) => ($v === '' || $v === null) ? null : (int)$v;
        $timeOrNull = fn($v) => ($v === '' || $v === null) ? null : $v;

        $sql = "UPDATE {$this->table} SET
                    hora_ingreso  = :ingreso,
                    hora_salida   = :salida,
                    estado        = :estado,
                    justificacion = :justif,
                    observacion   = :obs,
                    local_id      = :local
                WHERE id_asistencia = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'ingreso' => $timeOrNull($data['hora_ingreso'] ?? null),
            'salida'  => $timeOrNull($data['hora_salida']  ?? null),
            'estado'  => $data['estado']       ?? 'A TIEMPO',
            'justif'  => $data['justificacion'] ?? null,
            'obs'     => $data['observacion']  ?? 'PENDIENTE',
            'local'   => $intOrNull($data['local_id'] ?? null),
            'id'      => $id,
        ]);
        return $stmt->rowCount() > 0 || true; // siempre true si no hay error
    }

    // ── Admin: crear registro manual (falta / ingreso tardío) ─
    public function crear(int $postulanteId, string $fecha, array $data): bool
    {
        $intOrNull = fn($v) => ($v === '' || $v === null) ? null : (int)$v;

        $sql = "INSERT INTO {$this->table}
                    (postulante_id, local_id, fecha, hora_ingreso, hora_salida, estado, justificacion, observacion)
                VALUES
                    (:pid, :local, :fecha, :ingreso, :salida, :estado, :justif, :obs)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'pid'     => $postulanteId,
            'local'   => $intOrNull($data['local_id'] ?? null),
            'fecha'   => $fecha,
            'ingreso' => $data['hora_ingreso'] ?? null,
            'salida'  => $data['hora_salida']  ?? null,
            'estado'  => $data['estado']        ?? 'FALTA',
            'justif'  => $data['justificacion'] ?? null,
            'obs'     => $data['observacion']   ?? 'PENDIENTE',
        ]);
        return true;
    }

    // ── Usuarios con cuenta (para filtro admin) ───────────
    public function getUsuariosActivos(): array
    {
        $sql = "SELECT u.postulante_id AS id,
                       CONCAT(p.nombres, ' ', p.apellidos) AS nombre_completo,
                       p.num_documento
                FROM usuario u
                INNER JOIN postulante p ON u.postulante_id = p.id_postulante
                WHERE u.activo = 1
                ORDER BY p.nombres";
        return $this->db->query($sql)->fetchAll();
    }

    // ── Locales (para select en staff y admin) ────────────
    public function getLocales(): array
    {
        $sql = "SELECT id_local AS id, descripcion FROM local WHERE activo = 1 ORDER BY descripcion";
        return $this->db->query($sql)->fetchAll();
    }

    // ── Checklist de una asistencia específica ────────────
    public function getChecklistByAsistencia(int $asistenciaId): array
    {
        $sql = "SELECT ac.id_asistencia_checklist, ac.checklist_id, ac.cumplido, ac.observacion,
                       c.descripcion, c.tipo
                FROM asistencia_checklist ac
                INNER JOIN checklist c ON ac.checklist_id = c.id_checklist
                WHERE ac.asistencia_id = :id
                ORDER BY c.tipo DESC, c.id_checklist ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $asistenciaId]);
        return $stmt->fetchAll();
    }

    public function actualizarChecklistItem(int $itemId, int $cumplido, ?string $observacion): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE asistencia_checklist SET cumplido = :cum, observacion = :obs
             WHERE id_asistencia_checklist = :id"
        );
        $stmt->execute(['cum' => $cumplido, 'obs' => $observacion, 'id' => $itemId]);
        return $stmt->rowCount() > 0 || true;
    }

    // ── Checklist por tipo (APERTURA | CIERRE) ────────────
    public function getChecklistByTipo(string $tipo): array
    {
        $sql  = "SELECT id_checklist, descripcion FROM checklist WHERE tipo = :tipo AND activo = 1 ORDER BY id_checklist";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tipo' => $tipo]);
        return $stmt->fetchAll();
    }

    // ── Guardar respuestas del checklist ──────────────────
    public function guardarChecklist(int $asistenciaId, array $items): void
    {
        $sql  = "INSERT INTO asistencia_checklist (asistencia_id, checklist_id, cumplido, observacion)
                 VALUES (:aid, :cid, :cum, :obs)
                 ON DUPLICATE KEY UPDATE cumplido = VALUES(cumplido), observacion = VALUES(observacion)";
        $stmt = $this->db->prepare($sql);
        foreach ($items as $item) {
            $stmt->execute([
                'aid' => $asistenciaId,
                'cid' => (int)$item['checklist_id'],
                'cum' => !empty($item['cumplido']) ? 1 : 0,
                'obs' => $item['observacion'] ?? null,
            ]);
        }
    }
}
