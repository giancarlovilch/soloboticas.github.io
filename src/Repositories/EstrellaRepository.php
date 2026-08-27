<?php

require_once __DIR__ . '/../Core/Database.php';

/**
 * Sistema de estrellas: rojas = asistencia automática, azules = tareas de limpieza
 * votadas por un compañero. Independiente de las fichas de entrada/salida.
 *
 * Un solo voto ya otorga el valor completo de la calificación (sin promediar).
 * El control de fraude es el sistema de denuncias: si 2 personas ajenas al voto
 * lo denuncian, se sanciona automáticamente a beneficiario y votante.
 */
class EstrellaRepository
{
    private PDO $db;

    public const EPOCH = '2026-08-24';

    // Claves de configuracion_estrella. Los valores reales viven en la BD (con fecha
    // de vigencia e historial); estos son solo el nombre de la clave y un respaldo
    // por si esa clave nunca se llegó a insertar en la tabla.
    private const CLAVE_ROJA_TURNO           = 'EST_ROJA_POR_TURNO';
    private const CLAVE_BASE_ROJAS           = 'BASE_ROJAS';
    private const CLAVE_BASE_AZULES          = 'BASE_AZULES';
    private const CLAVE_AZUL_POR_VOTO        = 'EST_AZUL_POR_VOTO';
    private const CLAVE_SOL_POR_ESTRELLA     = 'SOL_POR_ESTRELLA';
    private const CLAVE_MAX_REPORTES_SANCION = 'MAX_REPORTES_SANCION';
    private const CLAVE_PENALTY_BENEFICIARIO = 'PENALTY_BENEFICIARIO';
    private const CLAVE_PENALTY_VOTANTE      = 'PENALTY_VOTANTE';

    private const DEFAULTS = [
        self::CLAVE_ROJA_TURNO           => 2,
        self::CLAVE_BASE_ROJAS           => 50,
        self::CLAVE_BASE_AZULES          => 50,
        self::CLAVE_AZUL_POR_VOTO        => 1,
        self::CLAVE_SOL_POR_ESTRELLA     => 0.10,
        self::CLAVE_MAX_REPORTES_SANCION => 2,
        self::CLAVE_PENALTY_BENEFICIARIO => -50,
        self::CLAVE_PENALTY_VOTANTE      => -100,
    ];

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    private function epochFloor(string $desde): string
    {
        return $desde < self::EPOCH ? self::EPOCH : $desde;
    }

    // ── Configuración con vigencia por fecha (montos, tasas, penalidades) ──
    /**
     * Valor de una clave vigente en una fecha puntual (para cálculos "de un solo momento":
     * la penalidad que aplica HOY, el colchón vigente al inicio del mes consultado, etc.)
     */
    private function getParametro(string $clave, string $fecha): float
    {
        return $this->resolverParametro($this->getParametrosOrdenados($clave), $clave, $fecha);
    }

    /** Todos los valores configurados para una clave, de más antiguo a más reciente */
    private function getParametrosOrdenados(string $clave): array
    {
        $stmt = $this->db->prepare(
            "SELECT valor, fecha_vigencia FROM configuracion_estrella
             WHERE clave = :clave ORDER BY fecha_vigencia ASC"
        );
        $stmt->execute(['clave' => $clave]);
        return $stmt->fetchAll();
    }

    /** Resuelve qué valor aplicaba en una fecha dada, a partir de una lista ya ordenada ASC */
    private function resolverParametro(array $valoresOrdenados, string $clave, string $fecha): float
    {
        $valor = self::DEFAULTS[$clave] ?? 0.0;
        foreach ($valoresOrdenados as $v) {
            if ($v['fecha_vigencia'] <= $fecha) $valor = (float)$v['valor'];
            else break;
        }
        return $valor;
    }

    /** Agrega un nuevo valor vigente desde una fecha para una clave — no borra el historial anterior */
    private function agregarParametro(string $clave, float $valor, string $fechaVigencia): string|bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaVigencia)) return 'Fecha inválida';

        $this->db->prepare(
            "INSERT INTO configuracion_estrella (clave, valor, fecha_vigencia) VALUES (:clave, :valor, :fecha)"
        )->execute(['clave' => $clave, 'valor' => $valor, 'fecha' => $fechaVigencia]);
        return true;
    }

    // ── Tasa de estrellas rojas por turno ───────────────────
    /** Historial completo de la tasa, la más reciente primero (para el admin) */
    public function getTasaRojaHistorial(): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, valor AS monto, fecha_vigencia, creado_en FROM configuracion_estrella
             WHERE clave = :clave ORDER BY fecha_vigencia DESC, id DESC"
        );
        $stmt->execute(['clave' => self::CLAVE_ROJA_TURNO]);
        return $stmt->fetchAll();
    }

    /** Tasa vigente hoy (para mostrarla como "el default actual") */
    public function getTasaRojaVigente(): int
    {
        return (int)$this->getParametro(self::CLAVE_ROJA_TURNO, date('Y-m-d'));
    }

    /** Agrega una nueva tasa de estrellas rojas vigente desde una fecha */
    public function agregarTasaRoja(int $monto, string $fechaVigencia): string|bool
    {
        if ($monto < 0) return 'El monto no puede ser negativo';
        return $this->agregarParametro(self::CLAVE_ROJA_TURNO, (float)$monto, $fechaVigencia);
    }

    // ── Estrellas azules por voto emitido ───────────────────
    /** Historial completo de la tasa, la más reciente primero (para el admin) */
    public function getTasaAzulVotoHistorial(): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, valor AS monto, fecha_vigencia, creado_en FROM configuracion_estrella
             WHERE clave = :clave ORDER BY fecha_vigencia DESC, id DESC"
        );
        $stmt->execute(['clave' => self::CLAVE_AZUL_POR_VOTO]);
        return $stmt->fetchAll();
    }

    /** Tasa vigente hoy (para mostrarla como "el default actual") */
    public function getTasaAzulVotoVigente(): float
    {
        return $this->getParametro(self::CLAVE_AZUL_POR_VOTO, date('Y-m-d'));
    }

    /** Agrega una nueva tasa de estrellas azules por voto vigente desde una fecha */
    public function agregarTasaAzulVoto(float $monto, string $fechaVigencia): string|bool
    {
        if ($monto < 0) return 'El monto no puede ser negativo';
        return $this->agregarParametro(self::CLAVE_AZUL_POR_VOTO, $monto, $fechaVigencia);
    }

    // ── Catálogo de tareas ─────────────────────────────────
    public function getTareas(): array
    {
        return $this->db->query(
            "SELECT * FROM tarea_limpieza WHERE activo = 1 ORDER BY orden ASC, id_tarea ASC"
        )->fetchAll();
    }

    public function getTarea(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM tarea_limpieza WHERE id_tarea = :id AND activo = 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    // ── Administración del catálogo (admin) ─────────────────
    /** Todas las tareas, activas e inactivas, para la pantalla de administración */
    public function getTareasTodas(): array
    {
        return $this->db->query(
            "SELECT * FROM tarea_limpieza ORDER BY orden ASC, id_tarea ASC"
        )->fetchAll();
    }

    public function crearTarea(string $codigo, string $descripcion, int $estrellasMax): string|bool
    {
        $codigo = strtoupper(trim($codigo));
        if ($codigo === '' || $descripcion === '' || $estrellasMax < 1) {
            return 'Datos inválidos';
        }
        $check = $this->db->prepare("SELECT 1 FROM tarea_limpieza WHERE codigo = :cod");
        $check->execute(['cod' => $codigo]);
        if ($check->fetchColumn()) return 'Ya existe una actividad con ese código';

        $orden = (int)$this->db->query("SELECT COALESCE(MAX(orden), 0) + 1 FROM tarea_limpieza")->fetchColumn();

        $this->db->prepare(
            "INSERT INTO tarea_limpieza (codigo, descripcion, estrellas_max, orden, activo)
             VALUES (:cod, :desc, :max, :orden, 1)"
        )->execute(['cod' => $codigo, 'desc' => $descripcion, 'max' => $estrellasMax, 'orden' => $orden]);
        return true;
    }

    public function actualizarTarea(int $id, string $descripcion, int $estrellasMax): string|bool
    {
        if ($descripcion === '' || $estrellasMax < 1) return 'Datos inválidos';
        $stmt = $this->db->prepare(
            "UPDATE tarea_limpieza SET descripcion = :desc, estrellas_max = :max WHERE id_tarea = :id"
        );
        $stmt->execute(['desc' => $descripcion, 'max' => $estrellasMax, 'id' => $id]);
        return true;
    }

    /** Activa/desactiva una tarea (no se elimina para no romper el historial de votos ya registrados) */
    public function toggleTarea(int $id): string|bool
    {
        $stmt = $this->db->prepare("UPDATE tarea_limpieza SET activo = 1 - activo WHERE id_tarea = :id");
        $stmt->execute(['id' => $id]);
        return true;
    }

    // ── Contexto para votar: local+turno y compañeros del día ──
    /**
     * Combos (local, turno) donde el propio usuario puede reconocer hoy: ambos turnos
     * de cada local donde tiene horario, no solo el que le tocó a él/ella (en locales
     * chicos de 2 personas, así puede reconocer al que trabajó en el otro turno).
     */
    public function getMisLocalesTurnoHoy(int $postulanteId, string $fecha): array
    {
        $stmt = $this->db->prepare(
            "SELECT DISTINCT l.id_local AS local_id, l.descripcion AS local_desc,
                    t.id_turno AS turno_id, t.descripcion AS turno_desc
             FROM horario_slot hs
             INNER JOIN local l ON l.id_local = hs.local_id
             CROSS JOIN turno t
             WHERE hs.postulante_id = :pid AND hs.fecha_dia = :fecha AND t.activo = 1
             ORDER BY l.descripcion, t.id_turno"
        );
        $stmt->execute(['pid' => $postulanteId, 'fecha' => $fecha]);
        return $stmt->fetchAll();
    }

    /** Combos (local, turno) con alguien programado hoy (fallback si el usuario no tiene turno hoy) */
    public function getLocalesTurnoHoy(string $fecha): array
    {
        $stmt = $this->db->prepare(
            "SELECT DISTINCT l.id_local AS local_id, l.descripcion AS local_desc,
                    t.id_turno AS turno_id, t.descripcion AS turno_desc
             FROM horario_slot hs
             INNER JOIN local l ON l.id_local = hs.local_id
             INNER JOIN turno t ON t.id_turno = hs.turno_id
             WHERE hs.fecha_dia = :fecha AND hs.postulante_id IS NOT NULL
             ORDER BY l.descripcion, t.id_turno"
        );
        $stmt->execute(['fecha' => $fecha]);
        return $stmt->fetchAll();
    }

    /**
     * Compañeros con turno hoy en el local+turno elegido (excluyendo al propio usuario),
     * con la cantidad de estrellas ya recibidas hoy (para mostrar "ya tiene encuestas").
     */
    public function getCompaneros(int $localId, int $turnoId, int $excludeId, string $fecha): array
    {
        $stmt = $this->db->prepare(
            "SELECT DISTINCT p.id_postulante AS id, p.nombres AS nombre, rh.descripcion AS rol_desc,
                    (SELECT COUNT(*) FROM estrella_voto ev
                      WHERE ev.beneficiario_id = p.id_postulante AND ev.local_id = :lid2
                        AND ev.turno_id = :tid2 AND ev.fecha = :fecha2) AS votos_hoy
             FROM horario_slot hs
             INNER JOIN postulante p   ON p.id_postulante   = hs.postulante_id
             INNER JOIN rol_horario rh ON rh.id_rol_horario = hs.rol_horario_id
             WHERE hs.local_id = :lid AND hs.turno_id = :tid AND hs.fecha_dia = :fecha
               AND hs.postulante_id != :excl
             ORDER BY p.nombres"
        );
        $stmt->execute([
            'lid' => $localId, 'tid' => $turnoId, 'fecha' => $fecha, 'excl' => $excludeId,
            'lid2' => $localId, 'tid2' => $turnoId, 'fecha2' => $fecha,
        ]);
        $rows = $stmt->fetchAll();
        foreach ($rows as &$r) $r['votos_hoy'] = (int)$r['votos_hoy'];
        return $rows;
    }

    private function yaVoto(int $votanteId, int $beneficiarioId, int $tareaId, string $fecha, int $turnoId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM estrella_voto
             WHERE votante_id = :vid AND beneficiario_id = :bid AND tarea_id = :tid
               AND fecha = :fecha AND turno_id = :turno"
        );
        $stmt->execute(['vid' => $votanteId, 'bid' => $beneficiarioId, 'tid' => $tareaId, 'fecha' => $fecha, 'turno' => $turnoId]);
        return (bool)$stmt->fetchColumn();
    }

    /**
     * Registra el voto de un compañero sobre una tarea de limpieza. Un solo voto ya
     * otorga el valor completo de la calificación — el control de abuso es el
     * sistema de denuncias, no exigir una segunda confirmación.
     */
    public function registrarVoto(
        int $votanteId, int $beneficiarioId, int $tareaId, int $localId, int $turnoId,
        int $calificacion, string $fecha, string $password
    ): array|string {
        $stmt = $this->db->prepare("SELECT password FROM usuario WHERE postulante_id = :pid LIMIT 1");
        $stmt->execute(['pid' => $votanteId]);
        $hash = $stmt->fetchColumn();
        if (!$hash || !password_verify($password, $hash)) return 'Contraseña incorrecta';

        if ($votanteId === $beneficiarioId) return 'No puedes darte estrellas a ti mismo';

        $tarea = $this->getTarea($tareaId);
        if (!$tarea) return 'Tarea no válida';
        if ($calificacion < 1 || $calificacion > (int)$tarea['estrellas_max']) {
            return "La calificación debe estar entre 1 y {$tarea['estrellas_max']}";
        }

        $checkSlot = $this->db->prepare(
            "SELECT 1 FROM horario_slot
             WHERE postulante_id = :pid AND local_id = :lid AND turno_id = :turno AND fecha_dia = :fecha LIMIT 1"
        );
        $checkSlot->execute(['pid' => $beneficiarioId, 'lid' => $localId, 'turno' => $turnoId, 'fecha' => $fecha]);
        if (!$checkSlot->fetchColumn()) return 'Ese compañero no tiene turno hoy en ese local/turno';

        if ($this->yaVoto($votanteId, $beneficiarioId, $tareaId, $fecha, $turnoId)) {
            return 'Ya calificaste esta actividad de este compañero en este turno';
        }

        $ins = $this->db->prepare(
            "INSERT INTO estrella_voto (beneficiario_id, votante_id, tarea_id, local_id, turno_id, fecha, calificacion)
             VALUES (:bid, :vid, :tid, :lid, :turno, :fecha, :calif)"
        );
        $ins->execute([
            'bid' => $beneficiarioId, 'vid' => $votanteId, 'tid' => $tareaId,
            'lid' => $localId, 'turno' => $turnoId, 'fecha' => $fecha, 'calif' => $calificacion,
        ]);

        return ['ok' => true, 'estrellas_otorgadas' => $calificacion, 'tarea' => $tarea];
    }

    // ── Auditoría del día y denuncias ──────────────────────
    /**
     * Estrellas entregadas hoy en un local+turno, con el estado de denuncias de cada una
     * (para el bloque "Estrellas entregadas hoy" con botón de denunciar).
     */
    public function getVotosDelDia(int $localId, int $turnoId, string $fecha, int $viewerId): array
    {
        $stmt = $this->db->prepare(
            "SELECT ev.id_voto, ev.calificacion, ev.sancionado,
                    ev.beneficiario_id, pb.nombres AS beneficiario_nombre,
                    ev.votante_id, pv.nombres AS votante_nombre,
                    tl.descripcion AS tarea,
                    (SELECT COUNT(*) FROM estrella_reporte er WHERE er.voto_id = ev.id_voto) AS reportes,
                    EXISTS(SELECT 1 FROM estrella_reporte er2
                            WHERE er2.voto_id = ev.id_voto AND er2.reportante_id = :viewer) AS ya_reporte
             FROM estrella_voto ev
             INNER JOIN postulante pb ON pb.id_postulante = ev.beneficiario_id
             INNER JOIN postulante pv ON pv.id_postulante = ev.votante_id
             INNER JOIN tarea_limpieza tl ON tl.id_tarea = ev.tarea_id
             WHERE ev.local_id = :lid AND ev.turno_id = :tid AND ev.fecha = :fecha
             ORDER BY ev.fecha_registro DESC"
        );
        $stmt->execute(['lid' => $localId, 'tid' => $turnoId, 'fecha' => $fecha, 'viewer' => $viewerId]);
        $rows = $stmt->fetchAll();
        foreach ($rows as &$r) {
            $r['reportes']    = (int)$r['reportes'];
            $r['ya_reporte']  = (bool)$r['ya_reporte'];
            $r['sancionado']  = (bool)$r['sancionado'];
            $r['puede_denunciar'] = !$r['sancionado'] && !$r['ya_reporte']
                && $viewerId !== (int)$r['beneficiario_id'] && $viewerId !== (int)$r['votante_id'];
        }
        return $rows;
    }

    /**
     * Denuncia un voto por falso (alguien dice que limpió y no era cierto). Solo puede
     * denunciar quien no es parte del voto. Al llegar a 2 denuncias se sanciona
     * automáticamente: -50 al beneficiario, -100 a quien otorgó el voto falso.
     */
    public function reportarVoto(int $votoId, int $reportanteId, string $password): array|string
    {
        $stmt = $this->db->prepare("SELECT password FROM usuario WHERE postulante_id = :pid LIMIT 1");
        $stmt->execute(['pid' => $reportanteId]);
        $hash = $stmt->fetchColumn();
        if (!$hash || !password_verify($password, $hash)) return 'Contraseña incorrecta';

        $voto = $this->db->prepare("SELECT * FROM estrella_voto WHERE id_voto = :id");
        $voto->execute(['id' => $votoId]);
        $voto = $voto->fetch();
        if (!$voto) return 'Registro no encontrado';
        if ((int)$voto['sancionado']) return 'Este registro ya fue sancionado';
        if ($reportanteId === (int)$voto['beneficiario_id'] || $reportanteId === (int)$voto['votante_id']) {
            return 'No puedes denunciar un voto en el que participas';
        }

        $check = $this->db->prepare("SELECT 1 FROM estrella_reporte WHERE voto_id = :vid AND reportante_id = :rid");
        $check->execute(['vid' => $votoId, 'rid' => $reportanteId]);
        if ($check->fetchColumn()) return 'Ya denunciaste este registro';

        $this->db->prepare(
            "INSERT INTO estrella_reporte (voto_id, reportante_id) VALUES (:vid, :rid)"
        )->execute(['vid' => $votoId, 'rid' => $reportanteId]);

        $count = $this->db->prepare("SELECT COUNT(*) FROM estrella_reporte WHERE voto_id = :vid");
        $count->execute(['vid' => $votoId]);
        $reportes = (int)$count->fetchColumn();

        $hoy = date('Y-m-d');
        $maxReportes = (int)$this->getParametro(self::CLAVE_MAX_REPORTES_SANCION, $hoy);

        $sancionado = false;
        if ($reportes >= $maxReportes) {
            $this->db->prepare("UPDATE estrella_voto SET sancionado = 1 WHERE id_voto = :id")->execute(['id' => $votoId]);

            $penaltyBeneficiario = $this->getParametro(self::CLAVE_PENALTY_BENEFICIARIO, $hoy);
            $penaltyVotante      = $this->getParametro(self::CLAVE_PENALTY_VOTANTE, $hoy);

            $this->db->prepare(
                "INSERT INTO estrella_ajuste (postulante_id, estrellas, motivo, voto_id, fecha)
                 VALUES (:pid, :est, :motivo, :vid, :fecha)"
            )->execute([
                'pid' => $voto['beneficiario_id'], 'est' => $penaltyBeneficiario,
                'motivo' => 'Sanción: recibió estrellas por una actividad denunciada como falsa',
                'vid' => $votoId, 'fecha' => $hoy,
            ]);
            $this->db->prepare(
                "INSERT INTO estrella_ajuste (postulante_id, estrellas, motivo, voto_id, fecha)
                 VALUES (:pid, :est, :motivo, :vid, :fecha)"
            )->execute([
                'pid' => $voto['votante_id'], 'est' => $penaltyVotante,
                'motivo' => 'Sanción: otorgó estrellas por una actividad denunciada como falsa',
                'vid' => $votoId, 'fecha' => $hoy,
            ]);
            $sancionado = true;
        }

        return ['ok' => true, 'reportes' => $reportes, 'maximo' => $maxReportes, 'sancionado' => $sancionado];
    }

    // ── Balance de estrellas ────────────────────────────────
    public function getEstrellas(int $postulanteId, string $desde, string $hasta): array
    {
        $desdeEf = $this->epochFloor($desde);

        $stmtTurnos = $this->db->prepare(
            "SELECT fecha, COUNT(*) AS n FROM asistencia
             WHERE postulante_id = :pid AND fecha BETWEEN :desde AND :hasta AND estado != 'FALTA'
             GROUP BY fecha"
        );
        $stmtTurnos->execute(['pid' => $postulanteId, 'desde' => $desdeEf, 'hasta' => $hasta]);
        $tasas = $this->getParametrosOrdenados(self::CLAVE_ROJA_TURNO);
        $turnos = 0;
        $rojasTareas = 0;
        foreach ($stmtTurnos->fetchAll() as $r) {
            $n = (int)$r['n'];
            $turnos      += $n;
            $rojasTareas += $n * $this->resolverParametro($tasas, self::CLAVE_ROJA_TURNO, $r['fecha']);
        }

        $stmtGanadas = $this->db->prepare(
            "SELECT COALESCE(SUM(calificacion), 0) FROM estrella_voto
             WHERE beneficiario_id = :pid AND fecha BETWEEN :desde AND :hasta AND sancionado = 0"
        );
        $stmtGanadas->execute(['pid' => $postulanteId, 'desde' => $desdeEf, 'hasta' => $hasta]);
        $azulesTareas = (float)$stmtGanadas->fetchColumn();

        $stmtVotos = $this->db->prepare(
            "SELECT fecha, COUNT(*) AS n FROM estrella_voto
             WHERE votante_id = :pid AND fecha BETWEEN :desde AND :hasta GROUP BY fecha"
        );
        $stmtVotos->execute(['pid' => $postulanteId, 'desde' => $desdeEf, 'hasta' => $hasta]);
        $tasasAzul = $this->getParametrosOrdenados(self::CLAVE_AZUL_POR_VOTO);
        $votosEmitidos = 0;
        $azulesVotos = 0;
        foreach ($stmtVotos->fetchAll() as $r) {
            $n = (int)$r['n'];
            $votosEmitidos += $n;
            $azulesVotos   += $n * $this->resolverParametro($tasasAzul, self::CLAVE_AZUL_POR_VOTO, $r['fecha']);
        }

        $stmtAjustes = $this->db->prepare(
            "SELECT COALESCE(SUM(estrellas), 0) FROM estrella_ajuste
             WHERE postulante_id = :pid AND fecha BETWEEN :desde AND :hasta"
        );
        $stmtAjustes->execute(['pid' => $postulanteId, 'desde' => $desdeEf, 'hasta' => $hasta]);
        $azulesAjustes = (int)$stmtAjustes->fetchColumn();

        $baseRojas  = $this->getParametro(self::CLAVE_BASE_ROJAS, $desdeEf);
        $baseAzules = $this->getParametro(self::CLAVE_BASE_AZULES, $desdeEf);
        $rojas  = $baseRojas  + $rojasTareas;
        $azules = round($baseAzules + $azulesTareas + $azulesVotos + $azulesAjustes, 1);
        $diferencia = $rojas - $azules;

        return [
            'turnos'         => $turnos,
            'rojas'          => $rojas,
            'votos_emitidos' => $votosEmitidos,
            'azules_tareas'  => round($azulesTareas, 1),
            'azules_votos'   => $azulesVotos,
            'azules_ajustes' => $azulesAjustes,
            'azules'         => $azules,
            'diferencia'     => round($diferencia, 1),
            'monto'          => round($diferencia * $this->getParametro(self::CLAVE_SOL_POR_ESTRELLA, $hasta), 2),
        ];
    }

    /** Balance de estrellas de todo el staff activo (para el resumen admin) */
    public function getEstrellasTodos(string $desde, string $hasta): array
    {
        $desdeEf = $this->epochFloor($desde);

        $staff = $this->db->query(
            "SELECT p.id_postulante AS id, p.nombres AS nombre
             FROM postulante p INNER JOIN usuario u ON u.postulante_id = p.id_postulante
             WHERE u.activo = 1 ORDER BY p.nombres ASC"
        )->fetchAll();

        $turnosMap = [];
        $rojasTareasMap = [];
        $stmtT = $this->db->prepare(
            "SELECT postulante_id, fecha, COUNT(*) AS n FROM asistencia
             WHERE fecha BETWEEN :desde AND :hasta AND estado != 'FALTA'
             GROUP BY postulante_id, fecha"
        );
        $stmtT->execute(['desde' => $desdeEf, 'hasta' => $hasta]);
        $tasas = $this->getParametrosOrdenados(self::CLAVE_ROJA_TURNO);
        foreach ($stmtT->fetchAll() as $r) {
            $pid = (int)$r['postulante_id'];
            $n = (int)$r['n'];
            $turnosMap[$pid] = ($turnosMap[$pid] ?? 0) + $n;
            $rojasTareasMap[$pid] = ($rojasTareasMap[$pid] ?? 0) + $n * $this->resolverParametro($tasas, self::CLAVE_ROJA_TURNO, $r['fecha']);
        }

        $ganadasMap = []; // pid => ['total' => float, 'CODIGO' => n_eventos]
        $stmtG = $this->db->prepare(
            "SELECT ev.beneficiario_id, tl.codigo, ev.calificacion
             FROM estrella_voto ev
             INNER JOIN tarea_limpieza tl ON tl.id_tarea = ev.tarea_id
             WHERE ev.fecha BETWEEN :desde AND :hasta AND ev.sancionado = 0"
        );
        $stmtG->execute(['desde' => $desdeEf, 'hasta' => $hasta]);
        foreach ($stmtG->fetchAll() as $g) {
            $pid = (int)$g['beneficiario_id'];
            $ganadasMap[$pid]['total'] = ($ganadasMap[$pid]['total'] ?? 0) + (int)$g['calificacion'];
            $codigo = $g['codigo'];
            $ganadasMap[$pid][$codigo] = ($ganadasMap[$pid][$codigo] ?? 0) + 1;
        }

        $azulesVotosMap = [];
        $stmtV = $this->db->prepare(
            "SELECT votante_id, fecha, COUNT(*) AS votos FROM estrella_voto
             WHERE fecha BETWEEN :desde AND :hasta GROUP BY votante_id, fecha"
        );
        $stmtV->execute(['desde' => $desdeEf, 'hasta' => $hasta]);
        $tasasAzul = $this->getParametrosOrdenados(self::CLAVE_AZUL_POR_VOTO);
        foreach ($stmtV->fetchAll() as $r) {
            $pid = (int)$r['votante_id'];
            $n = (int)$r['votos'];
            $azulesVotosMap[$pid] = ($azulesVotosMap[$pid] ?? 0)
                + $n * $this->resolverParametro($tasasAzul, self::CLAVE_AZUL_POR_VOTO, $r['fecha']);
        }

        $ajustesMap = [];
        $stmtA = $this->db->prepare(
            "SELECT postulante_id, SUM(estrellas) AS total FROM estrella_ajuste
             WHERE fecha BETWEEN :desde AND :hasta GROUP BY postulante_id"
        );
        $stmtA->execute(['desde' => $desdeEf, 'hasta' => $hasta]);
        foreach ($stmtA->fetchAll() as $r) $ajustesMap[(int)$r['postulante_id']] = (int)$r['total'];

        $solPorEstrella = $this->getParametro(self::CLAVE_SOL_POR_ESTRELLA, $hasta);
        $baseRojas  = $this->getParametro(self::CLAVE_BASE_ROJAS, $desdeEf);
        $baseAzules = $this->getParametro(self::CLAVE_BASE_AZULES, $desdeEf);

        $out = [];
        foreach ($staff as $s) {
            $pid          = (int)$s['id'];
            $turnos       = $turnosMap[$pid] ?? 0;
            $azulesTareas = round($ganadasMap[$pid]['total'] ?? 0, 1);
            $azulesVotos  = $azulesVotosMap[$pid] ?? 0;
            $azulesAjustes = $ajustesMap[$pid] ?? 0;
            $rojas        = $baseRojas  + ($rojasTareasMap[$pid] ?? 0);
            $azules       = round($baseAzules + $azulesTareas + $azulesVotos + $azulesAjustes, 1);
            $diferencia   = $rojas - $azules;
            $out[] = [
                'id'            => $pid,
                'nombre'        => $s['nombre'],
                'turnos'        => $turnos,
                'rojas'         => $rojas,
                'azules_tareas' => $azulesTareas,
                'azules_votos'  => $azulesVotos,
                'azules_ajustes'=> $azulesAjustes,
                'azules'        => $azules,
                'diferencia'    => round($diferencia, 1),
                'monto'         => round($diferencia * $solPorEstrella, 2),
                'tareas'        => array_diff_key($ganadasMap[$pid] ?? [], ['total' => true]),
            ];
        }
        return $out;
    }

    /**
     * Detalle de tareas por las que un trabajador recibió estrellas en el rango,
     * sin exponer quién lo calificó (solo la actividad, fecha, local, turno y calificación).
     */
    public function getDetalleTareasRecibidas(int $postulanteId, string $desde, string $hasta): array
    {
        $desdeEf = $this->epochFloor($desde);
        $stmt = $this->db->prepare(
            "SELECT tl.descripcion AS tarea, tl.estrellas_max, ev.fecha, l.descripcion AS local_desc,
                    t.descripcion AS turno_desc, ev.calificacion AS azules, ev.sancionado
             FROM estrella_voto ev
             INNER JOIN tarea_limpieza tl ON tl.id_tarea = ev.tarea_id
             INNER JOIN local l           ON l.id_local  = ev.local_id
             INNER JOIN turno t           ON t.id_turno  = ev.turno_id
             WHERE ev.beneficiario_id = :pid AND ev.fecha BETWEEN :desde AND :hasta
             ORDER BY ev.fecha DESC"
        );
        $stmt->execute(['pid' => $postulanteId, 'desde' => $desdeEf, 'hasta' => $hasta]);
        $rows = $stmt->fetchAll();
        foreach ($rows as &$r) {
            $r['sancionado'] = (bool)$r['sancionado'];
            $r['azules']     = $r['sancionado'] ? 0 : (int)$r['azules'];
        }
        return $rows;
    }

    /**
     * Detalle de los votos que el propio usuario emitió calificando a compañeros
     * (fuente de "1 azul por voto" — la única azul que no viene de tareas propias ni ajustes,
     * y que por eso no aparece en "Actividades reconocidas" del resumen del staff).
     */
    public function getDetalleVotosEmitidos(int $postulanteId, string $desde, string $hasta): array
    {
        $desdeEf = $this->epochFloor($desde);
        $stmt = $this->db->prepare(
            "SELECT ev.fecha, pb.nombres AS beneficiario_nombre, tl.descripcion AS tarea,
                    ev.calificacion, l.descripcion AS local_desc, t.descripcion AS turno_desc
             FROM estrella_voto ev
             INNER JOIN postulante pb     ON pb.id_postulante = ev.beneficiario_id
             INNER JOIN tarea_limpieza tl ON tl.id_tarea      = ev.tarea_id
             INNER JOIN local l           ON l.id_local       = ev.local_id
             INNER JOIN turno t           ON t.id_turno       = ev.turno_id
             WHERE ev.votante_id = :pid AND ev.fecha BETWEEN :desde AND :hasta
             ORDER BY ev.fecha DESC"
        );
        $stmt->execute(['pid' => $postulanteId, 'desde' => $desdeEf, 'hasta' => $hasta]);
        $rows = $stmt->fetchAll();
        $tasasAzul = $this->getParametrosOrdenados(self::CLAVE_AZUL_POR_VOTO);
        foreach ($rows as &$r) {
            $r['azul_ganado'] = $this->resolverParametro($tasasAzul, self::CLAVE_AZUL_POR_VOTO, $r['fecha']);
        }
        return $rows;
    }

    /** Detalle de turnos asistidos (fuente de las estrellas rojas) */
    public function getDetalleTurnos(int $postulanteId, string $desde, string $hasta): array
    {
        $desdeEf = $this->epochFloor($desde);
        $stmt = $this->db->prepare(
            "SELECT a.fecha, a.turno_id, l.descripcion AS local_desc
             FROM asistencia a
             LEFT JOIN local l ON l.id_local = a.local_id
             WHERE a.postulante_id = :pid AND a.fecha BETWEEN :desde AND :hasta AND a.estado != 'FALTA'
             ORDER BY a.fecha DESC"
        );
        $stmt->execute(['pid' => $postulanteId, 'desde' => $desdeEf, 'hasta' => $hasta]);
        $rows = $stmt->fetchAll();
        $tasas = $this->getParametrosOrdenados(self::CLAVE_ROJA_TURNO);
        foreach ($rows as &$r) {
            $r['tasa_roja'] = $this->resolverParametro($tasas, self::CLAVE_ROJA_TURNO, $r['fecha']);
        }
        return $rows;
    }
}
