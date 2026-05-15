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
        return null; // hora_ingreso/hora_salida eliminados
    }

    // ── Historial de un trabajador (últimas N sesiones) ──
    // Puede haber varias filas por día si trabajó en distintos locales.
    public function getByPostulante(int $id, int $limit = 30): array
    {
        $sql = "SELECT a.*, l.descripcion AS local_desc
                FROM {$this->table} a
                LEFT JOIN local l ON a.local_id = l.id_local
                WHERE a.postulante_id = :id
                ORDER BY a.fecha DESC, a.id_asistencia DESC
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
        return []; // hora_ingreso eliminado — usar registrarParaCompanhero
    }

    public function marcarSalida(int $id): bool
    {
        return false; // hora_salida eliminado — usar registrarParaCompanhero
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
                    a.id_asistencia, a.postulante_id,
                    CONCAT(p.nombres, ' ', p.apellidos) AS nombre_completo,
                    p.num_documento,
                    a.local_id, l.descripcion AS local_desc,
                    a.fecha, a.turno_id, a.estado,
                    a.llegada_puntualidad, a.area_ordenada_ingreso, a.area_limpia_ingreso,
                    a.aseo_personal, a.vestimenta, a.unas, a.cabello,
                    a.salida_puntualidad, a.estado_area_cierre, a.limpieza_area_cierre,
                    a.area_ordenada_cierre, a.participo_apertura_cierre,
                    a.uso_celular, a.calificacion_turno, a.comentarios_ficha,
                    a.justificacion, a.observacion,
                    pr.nombres AS registrado_por_nombre
                FROM {$this->table} a
                INNER JOIN postulante p  ON a.postulante_id = p.id_postulante
                LEFT JOIN local l        ON a.local_id = l.id_local
                LEFT JOIN postulante pr  ON a.registrado_por_id = pr.id_postulante
                WHERE " . implode(' AND ', $where) . "
                ORDER BY a.fecha DESC, a.id_asistencia DESC
                LIMIT {$limit}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ── Staff: registros propios por rango de fechas ──────
    public function getByPostulanteRango(int $postulanteId, string $desde, string $hasta): array
    {
        $stmt = $this->db->prepare(
            "SELECT a.id_asistencia, a.postulante_id, a.fecha, a.turno_id, a.estado,
                    a.llegada_puntualidad, a.area_ordenada_ingreso, a.area_limpia_ingreso,
                    a.aseo_personal, a.vestimenta, a.unas, a.cabello,
                    a.salida_puntualidad, a.estado_area_cierre, a.limpieza_area_cierre,
                    a.area_ordenada_cierre, a.participo_apertura_cierre,
                    a.uso_celular, a.calificacion_turno, a.comentarios_ficha,
                    a.local_id, l.descripcion AS local_desc,
                    pr.nombres AS registrado_por_nombre
             FROM asistencia a
             LEFT JOIN local l       ON a.local_id          = l.id_local
             LEFT JOIN postulante pr ON a.registrado_por_id = pr.id_postulante
             WHERE a.postulante_id = :pid AND a.fecha BETWEEN :desde AND :hasta
             ORDER BY a.fecha ASC, COALESCE(a.turno_id, 0) ASC"
        );
        $stmt->execute(['pid' => $postulanteId, 'desde' => $desde, 'hasta' => $hasta]);
        return $stmt->fetchAll();
    }

    /** Registra ficha de asistencia (encuesta) para otro trabajador */
    public function registrarParaCompanhero(
        int $postulanteId, int $registradorId, string $fecha, int $turnoId,
        string $seccion, array $campos, string $password
    ): bool|string {
        $stmt = $this->db->prepare("SELECT password FROM usuario WHERE postulante_id = :pid LIMIT 1");
        $stmt->execute(['pid' => $registradorId]);
        $hash = $stmt->fetchColumn();
        if (!$hash || !password_verify($password, $hash)) return 'Contraseña incorrecta';
        if ($postulanteId === $registradorId) return 'No puedes registrar tu propia ficha';

        // Buscar registro existente para esa fecha+turno
        $existing = $this->db->prepare(
            "SELECT id_asistencia FROM asistencia
             WHERE postulante_id = :pid AND fecha = :fecha
               AND (turno_id = :tid OR turno_id IS NULL)
             ORDER BY turno_id DESC, id_asistencia DESC LIMIT 1"
        );
        $existing->execute(['pid' => $postulanteId, 'fecha' => $fecha, 'tid' => $turnoId]);
        $existId = (int)($existing->fetchColumn() ?: 0);

        if ($seccion === 'FALTA') {
            if ($existId) {
                $this->db->prepare(
                    "UPDATE asistencia SET estado='FALTA', turno_id=:tid,
                     llegada_puntualidad=NULL, area_ordenada_ingreso=NULL, area_limpia_ingreso=NULL,
                     aseo_personal=NULL, vestimenta=NULL, unas=NULL, cabello=NULL,
                     salida_puntualidad=NULL, estado_area_cierre=NULL, limpieza_area_cierre=NULL,
                     area_ordenada_cierre=NULL, participo_apertura_cierre=NULL,
                     uso_celular=NULL, calificacion_turno=NULL, comentarios_ficha=NULL,
                     registrado_por_id=:reg WHERE id_asistencia=:id"
                )->execute(['tid' => $turnoId, 'reg' => $registradorId, 'id' => $existId]);
            } else {
                $this->db->prepare(
                    "INSERT INTO asistencia (postulante_id, registrado_por_id, fecha, turno_id, estado)
                     VALUES (:pid, :reg, :fecha, :tid, 'FALTA')"
                )->execute(['pid' => $postulanteId, 'reg' => $registradorId, 'fecha' => $fecha, 'tid' => $turnoId]);
            }
            return true;
        }

        $yn = fn($k) => array_key_exists($k, $campos) && $campos[$k] !== null ? (int)$campos[$k] : null;

        if ($seccion === 'ENTRADA') {
            $llegada = $campos['llegada_puntualidad'] ?? null;
            $estado  = match($llegada) {
                'MUY_TEMPRANO' => 'EXTRA',
                'TEMPRANO'     => 'TEMPRANO',
                'TARDE', 'MUY_TARDE' => 'TARDE',
                default        => 'A TIEMPO',
            };
            $fields = [
                'llegada_puntualidad'   => $llegada,
                'area_ordenada_ingreso' => $yn('area_ordenada_ingreso'),
                'area_limpia_ingreso'   => $yn('area_limpia_ingreso'),
                'aseo_personal'         => $campos['aseo_personal'] ?? null,
                'vestimenta'            => $campos['vestimenta']    ?? null,
                'unas'                  => $campos['unas']          ?? null,
                'cabello'               => $campos['cabello']       ?? null,
                'estado'                => $estado,
            ];
        } else { // SALIDA
            $fields = [
                'salida_puntualidad'       => $campos['salida_puntualidad']       ?? null,
                'estado_area_cierre'       => $campos['estado_area_cierre']       ?? null,
                'limpieza_area_cierre'     => $yn('limpieza_area_cierre'),
                'area_ordenada_cierre'     => $yn('area_ordenada_cierre'),
                'participo_apertura_cierre'=> $yn('participo_apertura_cierre'),
                'uso_celular'              => $campos['uso_celular']              ?? null,
                'calificacion_turno'       => $campos['calificacion_turno']       ?? null,
            ];
        }

        $cf = trim($campos['comentarios_ficha'] ?? '');
        if ($cf !== '') $fields['comentarios_ficha'] = substr($cf, 0, 200);
        $fields['registrado_por_id'] = $registradorId;
        $fields['turno_id']          = $turnoId;

        if ($existId) {
            $sets = implode(', ', array_map(fn($k) => "{$k} = :{$k}", array_keys($fields)));
            $fields['id'] = $existId;
            $this->db->prepare("UPDATE asistencia SET {$sets} WHERE id_asistencia = :id")->execute($fields);
        } else {
            $fields['postulante_id'] = $postulanteId;
            $fields['fecha']         = $fecha;
            if (!isset($fields['estado'])) $fields['estado'] = 'A TIEMPO';
            $cols = implode(', ', array_keys($fields));
            $vals = implode(', ', array_map(fn($k) => ":{$k}", array_keys($fields)));
            $this->db->prepare("INSERT INTO asistencia ({$cols}) VALUES ({$vals})")->execute($fields);
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

    public function actualizarTiemposPropio(int $asistenciaId, int $postulanteId, string $ingreso, ?string $salida): bool
    {
        return false; // reemplazado por registrarParaCompanhero
    }

    // ── Admin: actualizar cualquier registro ──────────────
    public function actualizar(int $id, array $data): bool
    {
        $intOrNull  = fn($v) => ($v === '' || $v === null) ? null : (int)$v;
        $timeOrNull = fn($v) => ($v === '' || $v === null) ? null : $v;

        $sn = fn($v) => ($v === '' || $v === null) ? null : (string)$v;
        $in = fn($v) => ($v === '' || $v === null) ? null : (int)$v;

        $sql = "UPDATE {$this->table} SET
                    estado                    = :estado,
                    llegada_puntualidad       = :lleg,
                    area_ordenada_ingreso     = :aoi,
                    area_limpia_ingreso       = :ali,
                    aseo_personal             = :aseo,
                    vestimenta                = :vest,
                    unas                      = :unas,
                    cabello                   = :cab,
                    salida_puntualidad        = :sal_punt,
                    estado_area_cierre        = :eac,
                    limpieza_area_cierre      = :lac,
                    area_ordenada_cierre      = :aoc,
                    participo_apertura_cierre = :pac,
                    uso_celular               = :uso_cel,
                    calificacion_turno        = :calif,
                    comentarios_ficha         = :coment,
                    justificacion             = :justif,
                    observacion               = :obs,
                    local_id                  = :local
                WHERE id_asistencia = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'estado'    => $data['estado']                     ?? 'FALTA',
            'lleg'      => $sn($data['llegada_puntualidad']    ?? null),
            'aoi'       => $in($data['area_ordenada_ingreso']  ?? null),
            'ali'       => $in($data['area_limpia_ingreso']    ?? null),
            'aseo'      => $sn($data['aseo_personal']          ?? null),
            'vest'      => $sn($data['vestimenta']             ?? null),
            'unas'      => $sn($data['unas']                   ?? null),
            'cab'       => $sn($data['cabello']                ?? null),
            'sal_punt'  => $sn($data['salida_puntualidad']     ?? null),
            'eac'       => $sn($data['estado_area_cierre']     ?? null),
            'lac'       => $in($data['limpieza_area_cierre']   ?? null),
            'aoc'       => $in($data['area_ordenada_cierre']   ?? null),
            'pac'       => $in($data['participo_apertura_cierre'] ?? null),
            'uso_cel'   => $sn($data['uso_celular']            ?? null),
            'calif'     => $sn($data['calificacion_turno']     ?? null),
            'coment'    => $sn($data['comentarios_ficha']      ?? null),
            'justif'    => $sn($data['justificacion']          ?? null),
            'obs'       => $data['observacion']                ?? 'PENDIENTE',
            'local'     => $in($data['local_id']               ?? null),
            'id'        => $id,
        ]);
        return $stmt->rowCount() > 0 || true; // siempre true si no hay error
    }

    // ── Admin: crear registro manual (falta / ingreso tardío) ─
    public function crear(int $postulanteId, string $fecha, array $data): bool
    {
        $intOrNull = fn($v) => ($v === '' || $v === null) ? null : (int)$v;

        $sql = "INSERT INTO {$this->table}
                    (postulante_id, local_id, fecha, estado, justificacion, observacion)
                VALUES
                    (:pid, :local, :fecha, :estado, :justif, :obs)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'pid'     => $postulanteId,
            'local'   => $intOrNull($data['local_id'] ?? null),
            'fecha'   => $fecha,
            'estado'  => $data['estado']        ?? 'FALTA',
            'justif'  => $data['justificacion'] ?? null,
            'obs'     => $data['observacion']   ?? 'PENDIENTE',
        ]);
        return true;
    }

    // ── Todos los slots del pasado con su ficha (admin) ──
    public function getAllSlots(string $desde, string $hasta, int $postulanteId = 0, bool $soloSinCalificar = false, int $excludeId = 0): array
    {
        $where  = ["hs.postulante_id IS NOT NULL", "hs.fecha_dia <= CURDATE()"];
        $params = [];

        if ($desde) { $where[] = "hs.fecha_dia >= :desde"; $params['desde'] = $desde; }
        if ($hasta) { $where[] = "hs.fecha_dia <= :hasta"; $params['hasta'] = $hasta; }
        if ($postulanteId) { $where[] = "hs.postulante_id = :pid"; $params['pid'] = $postulanteId; }
        if ($excludeId)    { $where[] = "hs.postulante_id != :excl"; $params['excl'] = $excludeId; }
        if ($soloSinCalificar) {
            $where[] = "(a.id_asistencia IS NULL OR (a.estado != 'FALTA' AND (a.llegada_puntualidad IS NULL OR a.salida_puntualidad IS NULL)))";
        }

        $sql = "SELECT
                    hs.id_slot, hs.fecha_dia, hs.turno_id,
                    t.descripcion  AS turno_desc,
                    p.id_postulante AS postulante_id,
                    p.nombres      AS trabajador_nombre,
                    l.descripcion  AS local_desc,
                    rh.descripcion AS rol_desc,
                    a.id_asistencia,
                    a.estado,
                    a.llegada_puntualidad, a.area_ordenada_ingreso, a.area_limpia_ingreso,
                    a.aseo_personal, a.vestimenta, a.unas, a.cabello,
                    a.salida_puntualidad, a.estado_area_cierre, a.limpieza_area_cierre,
                    a.area_ordenada_cierre, a.participo_apertura_cierre,
                    a.uso_celular, a.calificacion_turno, a.comentarios_ficha,
                    a.justificacion, a.observacion, a.local_id,
                    pr.nombres AS registrado_por_nombre
                FROM horario_slot hs
                INNER JOIN postulante p   ON hs.postulante_id   = p.id_postulante
                INNER JOIN local l        ON hs.local_id         = l.id_local
                INNER JOIN turno t        ON hs.turno_id         = t.id_turno
                INNER JOIN rol_horario rh ON hs.rol_horario_id   = rh.id_rol_horario
                LEFT JOIN asistencia a    ON  a.postulante_id    = hs.postulante_id
                                          AND a.fecha            = hs.fecha_dia
                                          AND (a.turno_id = hs.turno_id OR a.turno_id IS NULL)
                LEFT JOIN postulante pr   ON a.registrado_por_id = pr.id_postulante
                WHERE " . implode(' AND ', $where) . "
                ORDER BY hs.fecha_dia DESC, hs.turno_id ASC, p.nombres ASC
                LIMIT 500";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Admin crea o actualiza ficha sin requerir contraseña */
    public function upsertParaAdmin(int $postulanteId, string $fecha, int $turnoId, array $data): void
    {
        $sn = fn($v) => ($v === '' || $v === null) ? null : (string)$v;
        $in = fn($v) => ($v === '' || $v === null) ? null : (int)$v;

        $existing = $this->db->prepare(
            "SELECT id_asistencia FROM asistencia
             WHERE postulante_id = :pid AND fecha = :fecha
               AND (turno_id = :tid OR turno_id IS NULL)
             ORDER BY turno_id DESC, id_asistencia DESC LIMIT 1"
        );
        $existing->execute(['pid' => $postulanteId, 'fecha' => $fecha, 'tid' => $turnoId]);
        $existId = (int)($existing->fetchColumn() ?: 0);

        $fields = [
            'turno_id'                 => $turnoId,
            'estado'                   => $data['estado']                     ?? 'FALTA',
            'llegada_puntualidad'      => $sn($data['llegada_puntualidad']    ?? null),
            'area_ordenada_ingreso'    => $in($data['area_ordenada_ingreso']  ?? null),
            'area_limpia_ingreso'      => $in($data['area_limpia_ingreso']    ?? null),
            'aseo_personal'            => $sn($data['aseo_personal']          ?? null),
            'vestimenta'               => $sn($data['vestimenta']             ?? null),
            'unas'                     => $sn($data['unas']                   ?? null),
            'cabello'                  => $sn($data['cabello']                ?? null),
            'salida_puntualidad'       => $sn($data['salida_puntualidad']     ?? null),
            'estado_area_cierre'       => $sn($data['estado_area_cierre']     ?? null),
            'limpieza_area_cierre'     => $in($data['limpieza_area_cierre']   ?? null),
            'area_ordenada_cierre'     => $in($data['area_ordenada_cierre']   ?? null),
            'participo_apertura_cierre'=> $in($data['participo_apertura_cierre'] ?? null),
            'uso_celular'              => $sn($data['uso_celular']            ?? null),
            'calificacion_turno'       => $sn($data['calificacion_turno']     ?? null),
            'comentarios_ficha'        => $sn($data['comentarios_ficha']      ?? null),
            'justificacion'            => $sn($data['justificacion']          ?? null),
            'observacion'              => $data['observacion']                ?? 'PENDIENTE',
            'local_id'                 => $in($data['local_id']               ?? null),
        ];

        if ($existId) {
            $sets = implode(', ', array_map(fn($k) => "{$k} = :{$k}", array_keys($fields)));
            $fields['id'] = $existId;
            $this->db->prepare("UPDATE asistencia SET {$sets} WHERE id_asistencia = :id")->execute($fields);
        } else {
            $fields['postulante_id'] = $postulanteId;
            $fields['fecha']         = $fecha;
            $cols = implode(', ', array_keys($fields));
            $vals = implode(', ', array_map(fn($k) => ":{$k}", array_keys($fields)));
            $this->db->prepare("INSERT INTO asistencia ({$cols}) VALUES ({$vals})")->execute($fields);
        }
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
