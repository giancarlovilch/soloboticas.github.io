<?php

require_once __DIR__ . '/../Core/Database.php';

/**
 * Encuesta de desempeño entre compañeros: 7 aspectos de 1 a 10, sin valor
 * económico — solo informativo. Independiente de la asistencia/nómina.
 */
class EncuestaRepository
{
    private PDO $db;

    public const ASPECTOS = ['puntualidad', 'orden', 'higiene', 'presentacion', 'animo', 'uso_celular', 'confianza'];

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /** Compañeros (excluye al registrador) para el filtro */
    public function getTrabajadores(): array
    {
        $stmt = $this->db->query(
            "SELECT p.id_postulante AS id, p.nombres AS nombre
             FROM postulante p INNER JOIN usuario u ON u.postulante_id = p.id_postulante
             WHERE u.activo = 1 ORDER BY p.nombres ASC"
        );
        return $stmt->fetchAll();
    }

    /**
     * Turnos de compañeros aún no calificados por este evaluador (o todos, según filtro).
     */
    public function getPendientes(
        string $desde, string $hasta, int $filtroTrabajador, bool $soloSinCalificar, int $evaluadorId
    ): array {
        $where  = ["hs.postulante_id IS NOT NULL", "hs.postulante_id != :evaluador", "hs.fecha_dia <= CURDATE()"];
        $params = ['evaluador' => $evaluadorId];

        if ($desde) { $where[] = "hs.fecha_dia >= :desde"; $params['desde'] = $desde; }
        if ($hasta) { $where[] = "hs.fecha_dia <= :hasta"; $params['hasta'] = $hasta; }
        if ($filtroTrabajador) { $where[] = "hs.postulante_id = :pid"; $params['pid'] = $filtroTrabajador; }
        if ($soloSinCalificar)  { $where[] = "ed.id_encuesta IS NULL"; }

        $sql = "SELECT hs.id_slot, hs.fecha_dia, hs.turno_id,
                       t.descripcion  AS turno_desc,
                       p.id_postulante AS postulante_id,
                       p.nombres      AS trabajador_nombre,
                       l.descripcion  AS local_desc,
                       rh.descripcion AS rol_desc,
                       ed.id_encuesta, ed.puntualidad, ed.orden, ed.higiene, ed.presentacion,
                       ed.animo, ed.uso_celular, ed.confianza
                FROM horario_slot hs
                INNER JOIN postulante p   ON hs.postulante_id   = p.id_postulante
                INNER JOIN local l        ON hs.local_id        = l.id_local
                INNER JOIN turno t        ON hs.turno_id        = t.id_turno
                INNER JOIN rol_horario rh ON hs.rol_horario_id  = rh.id_rol_horario
                LEFT JOIN encuesta_desempeno ed
                       ON  ed.evaluado_id  = hs.postulante_id
                       AND ed.fecha        = hs.fecha_dia
                       AND ed.turno_id     = hs.turno_id
                       AND ed.evaluador_id = :evaluador2
                WHERE " . implode(' AND ', $where) . "
                ORDER BY hs.fecha_dia DESC, hs.turno_id ASC, p.nombres ASC
                LIMIT 500";
        $params['evaluador2'] = $evaluadorId;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Registra o actualiza (si ya existía) la calificación de un evaluador sobre un compañero/turno */
    public function registrarEncuesta(
        int $evaluadorId, int $evaluadoId, string $fecha, int $turnoId, array $valores, string $password
    ): string|true {
        $stmt = $this->db->prepare("SELECT password FROM usuario WHERE postulante_id = :pid LIMIT 1");
        $stmt->execute(['pid' => $evaluadorId]);
        $hash = $stmt->fetchColumn();
        if (!$hash || !password_verify($password, $hash)) return 'Contraseña incorrecta';

        if ($evaluadorId === $evaluadoId) return 'No puedes calificarte a ti mismo';

        $vals = [];
        foreach (self::ASPECTOS as $a) {
            $v = (int)($valores[$a] ?? 0);
            if ($v < 1 || $v > 10) return 'Todos los aspectos deben calificarse del 1 al 10';
            $vals[$a] = $v;
        }

        $sql = "INSERT INTO encuesta_desempeno
                    (evaluado_id, evaluador_id, fecha, turno_id,
                     puntualidad, orden, higiene, presentacion, animo, uso_celular, confianza)
                VALUES
                    (:evaluado, :evaluador, :fecha, :turno,
                     :puntualidad, :orden, :higiene, :presentacion, :animo, :uso_celular, :confianza)
                ON DUPLICATE KEY UPDATE
                    puntualidad = VALUES(puntualidad), orden = VALUES(orden), higiene = VALUES(higiene),
                    presentacion = VALUES(presentacion), animo = VALUES(animo),
                    uso_celular = VALUES(uso_celular), confianza = VALUES(confianza)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_merge(
            ['evaluado' => $evaluadoId, 'evaluador' => $evaluadorId, 'fecha' => $fecha, 'turno' => $turnoId],
            $vals
        ));
        return true;
    }

    /** Promedios por aspecto de un trabajador (2 decimales) en un rango de fechas */
    public function getPromedios(int $evaluadoId, string $desde, string $hasta): array
    {
        $cols = implode(', ', array_map(fn($a) => "ROUND(AVG($a), 2) AS $a", self::ASPECTOS));
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS total_encuestas, $cols
             FROM encuesta_desempeno
             WHERE evaluado_id = :pid AND fecha BETWEEN :desde AND :hasta"
        );
        $stmt->execute(['pid' => $evaluadoId, 'desde' => $desde, 'hasta' => $hasta]);
        $row = $stmt->fetch();
        foreach (self::ASPECTOS as $a) {
            $row[$a] = $row[$a] !== null ? (float)$row[$a] : null;
        }
        $row['total_encuestas'] = (int)$row['total_encuestas'];
        return $row;
    }

    /** Detalle de encuestas recibidas por un trabajador, sin exponer quién calificó */
    public function getDetalleRecibidas(int $evaluadoId, string $desde, string $hasta): array
    {
        $stmt = $this->db->prepare(
            "SELECT ed.fecha, ed.turno_id, t.descripcion AS turno_desc,
                    ed.puntualidad, ed.orden, ed.higiene, ed.presentacion, ed.animo, ed.uso_celular, ed.confianza
             FROM encuesta_desempeno ed
             INNER JOIN turno t ON t.id_turno = ed.turno_id
             WHERE ed.evaluado_id = :pid AND ed.fecha BETWEEN :desde AND :hasta
             ORDER BY ed.fecha DESC"
        );
        $stmt->execute(['pid' => $evaluadoId, 'desde' => $desde, 'hasta' => $hasta]);
        return $stmt->fetchAll();
    }

    /** Ranking del staff activo por un aspecto, para comparar equipo (admin) */
    public function getRanking(string $aspecto, string $desde, string $hasta): array
    {
        if (!in_array($aspecto, self::ASPECTOS, true)) $aspecto = 'puntualidad';
        $stmt = $this->db->prepare(
            "SELECT p.id_postulante AS id, p.nombres AS nombre,
                    ROUND(AVG(ed.$aspecto), 2) AS promedio, COUNT(*) AS total
             FROM encuesta_desempeno ed
             INNER JOIN postulante p ON p.id_postulante = ed.evaluado_id
             WHERE ed.fecha BETWEEN :desde AND :hasta
             GROUP BY ed.evaluado_id, p.nombres
             ORDER BY promedio DESC"
        );
        $stmt->execute(['desde' => $desde, 'hasta' => $hasta]);
        return $stmt->fetchAll();
    }

    /** Evaluaciones con confianza baja — posible trampa, robo o trabajo sucio del evaluado (admin) */
    public function getConfianzaBaja(string $desde, string $hasta, int $umbral = 4): array
    {
        $stmt = $this->db->prepare(
            "SELECT ed.fecha, ed.confianza,
                    pe.nombres AS evaluado_nombre, pr.nombres AS evaluador_nombre
             FROM encuesta_desempeno ed
             INNER JOIN postulante pe ON pe.id_postulante = ed.evaluado_id
             INNER JOIN postulante pr ON pr.id_postulante = ed.evaluador_id
             WHERE ed.fecha BETWEEN :desde AND :hasta AND ed.confianza <= :umbral
             ORDER BY ed.confianza ASC, ed.fecha DESC"
        );
        $stmt->execute(['desde' => $desde, 'hasta' => $hasta, 'umbral' => $umbral]);
        return $stmt->fetchAll();
    }
}
