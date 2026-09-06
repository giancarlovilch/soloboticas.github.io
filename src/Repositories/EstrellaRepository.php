<?php

require_once __DIR__ . '/../Core/Database.php';

/**
 * Sistema de estrellas: rojas = asistencia automática, azules = tareas de limpieza.
 * Independiente de las fichas de entrada/salida.
 *
 * Las azules se autoasignan (tabla limpieza_registro): cada quien se anota la
 * actividad que hizo en un local y fecha; el primero que la ocupa se la queda
 * congelada, solo un admin puede reasignarla. El voto entre compañeros
 * (estrella_voto/estrella_reporte/estrella_ajuste) es el sistema anterior — se
 * deja de usar para registros nuevos, pero sigue sumando en los cálculos para
 * no perder lo ya ganado antes del cambio.
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
    private const CLAVE_SOL_POR_ESTRELLA     = 'SOL_POR_ESTRELLA';

    private const DEFAULTS = [
        self::CLAVE_ROJA_TURNO           => 2,
        self::CLAVE_BASE_ROJAS           => 50,
        self::CLAVE_BASE_AZULES          => 50,
        self::CLAVE_SOL_POR_ESTRELLA     => 0.10,
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

    /**
     * Elimina un valor vigente de una clave (para corregir un cambio de tasa mal puesto:
     * al quedar dos fechas de vigencia superpuestas, la más reciente siempre gana y no basta
     * con "agregar" un valor viejo encima — hay que borrar el que sobra).
     */
    private function eliminarParametro(string $clave, int $id): string|bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM configuracion_estrella WHERE id = :id AND clave = :clave"
        );
        $stmt->execute(['id' => $id, 'clave' => $clave]);
        return $stmt->rowCount() > 0 ? true : 'No se encontró ese registro.';
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

    /** Elimina una tasa de estrellas rojas registrada por error */
    public function eliminarTasaRoja(int $id): string|bool
    {
        return $this->eliminarParametro(self::CLAVE_ROJA_TURNO, $id);
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

    // ── Valor de cada actividad por local (ej. barrer en Local 3 vale doble) ──
    /**
     * Estrellas que vale una actividad en un local puntual, en una fecha dada. Si nunca
     * se configuró un valor especial para ese local, cae al valor base de la actividad
     * (tarea_limpieza.estrellas_max) — así un local sin overrides funciona igual que antes.
     */
    public function getValorTarea(int $tareaId, int $localId, string $fecha): int
    {
        $stmt = $this->db->prepare(
            "SELECT estrellas, fecha_vigencia FROM valor_local_tarea
             WHERE tarea_id = :t AND local_id = :l ORDER BY fecha_vigencia ASC"
        );
        $stmt->execute(['t' => $tareaId, 'l' => $localId]);
        $filas = $stmt->fetchAll();

        $tarea = $this->getTarea($tareaId);
        $valor = (int)($tarea['estrellas_max'] ?? 0);
        foreach ($filas as $f) {
            if ($f['fecha_vigencia'] <= $fecha) $valor = (int)$f['estrellas'];
            else break;
        }
        return $valor;
    }

    /**
     * Matriz [tarea_id][local_id] => valor vigente hoy, para la tabla resumen del admin.
     * Incluye todas las tareas (activas o no) y todos los locales de limpieza.
     */
    public function getMatrizValores(): array
    {
        $hoy = date('Y-m-d');
        $matriz = [];
        foreach ($this->getTareasTodas() as $t) {
            foreach ($this->getLocalesLimpieza() as $l) {
                $matriz[(int)$t['id_tarea']][(int)$l['id_local']] = $this->getValorTarea((int)$t['id_tarea'], (int)$l['id_local'], $hoy);
            }
        }
        return $matriz;
    }

    /** Historial completo de valores por local (todas las tareas y locales), el más reciente primero */
    public function getValoresLocalHistorial(): array
    {
        return $this->db->query(
            "SELECT vlt.id, vlt.estrellas, vlt.fecha_vigencia, vlt.creado_en,
                    tl.descripcion AS tarea, l.descripcion AS local_desc
             FROM valor_local_tarea vlt
             INNER JOIN tarea_limpieza tl ON tl.id_tarea = vlt.tarea_id
             INNER JOIN local l           ON l.id_local  = vlt.local_id
             ORDER BY vlt.fecha_vigencia DESC, vlt.id DESC"
        )->fetchAll();
    }

    /** Agrega un nuevo valor vigente desde una fecha para una actividad en un local */
    public function agregarValorLocal(int $tareaId, int $localId, int $estrellas, string $fechaVigencia): string|bool
    {
        if ($estrellas < 0) return 'El monto no puede ser negativo';
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaVigencia)) return 'Fecha inválida';
        if (!$this->getTarea($tareaId)) return 'Actividad no válida';

        $localCheck = $this->db->prepare("SELECT 1 FROM local WHERE id_local = :id");
        $localCheck->execute(['id' => $localId]);
        if (!$localCheck->fetchColumn()) return 'Local no válido';

        $this->db->prepare(
            "INSERT INTO valor_local_tarea (tarea_id, local_id, estrellas, fecha_vigencia)
             VALUES (:t, :l, :est, :fecha)"
        )->execute(['t' => $tareaId, 'l' => $localId, 'est' => $estrellas, 'fecha' => $fechaVigencia]);
        return true;
    }

    /** Elimina un valor por local registrado por error (mismo criterio que las otras tasas) */
    public function eliminarValorLocal(int $id): string|bool
    {
        $stmt = $this->db->prepare("DELETE FROM valor_local_tarea WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0 ? true : 'No se encontró ese registro.';
    }

    // ── Locales y staff para el sistema de limpieza ────────
    /** Locales donde se hace limpieza (Administración queda excluida por estar inactiva) */
    public function getLocalesLimpieza(): array
    {
        return $this->db->query(
            "SELECT id_local, descripcion FROM local WHERE activo = 1 ORDER BY id_local"
        )->fetchAll();
    }

    /** Staff activo, para el selector de reasignación del admin */
    public function getStaffActivo(): array
    {
        return $this->db->query(
            "SELECT p.id_postulante AS id, p.nombres AS nombre
             FROM postulante p INNER JOIN usuario u ON u.postulante_id = p.id_postulante
             WHERE u.activo = 1 ORDER BY p.nombres ASC"
        )->fetchAll();
    }

    // ── Autoasignación de limpieza (reemplaza el voto entre compañeros) ──
    /** Turnos activos (mañana/tarde), para las dos filas de cada día en la cuadrícula */
    public function getTurnos(): array
    {
        return $this->db->query(
            "SELECT id_turno, descripcion FROM turno WHERE activo = 1 ORDER BY id_turno"
        )->fetchAll();
    }

    /** Registros de limpieza ya asignados en un rango, para armar la tabla del mes */
    public function getRegistrosLimpieza(string $desde, string $hasta): array
    {
        $stmt = $this->db->prepare(
            "SELECT lr.fecha, lr.local_id, lr.tarea_id, lr.turno_id, lr.postulante_id, p.nombres AS nombre
             FROM limpieza_registro lr
             INNER JOIN postulante p ON p.id_postulante = lr.postulante_id
             WHERE lr.fecha BETWEEN :desde AND :hasta"
        );
        $stmt->execute(['desde' => $desde, 'hasta' => $hasta]);
        return $stmt->fetchAll();
    }

    /**
     * Slots (tarea+local+turno+fecha) ya cubiertos por el sistema anterior de votos entre
     * compañeros, en un rango. Aunque el voto viejo ya distinguía por turno, a veces dos
     * voluntarios distintos calificaron a dos personas distintas en el mismo turno — para
     * esos pocos casos se listan todos los beneficiarios. Sirve para: (1) que la cuadrícula
     * muestre esos turnos como ya ocupados en vez de libres, y (2) evitar que alguien vuelva
     * a autoasignarse un turno que ya sumó estrellas (eso lo contaría dos veces en el ranking).
     */
    public function getSlotsLegado(string $desde, string $hasta): array
    {
        $stmt = $this->db->prepare(
            "SELECT ev.id_voto, ev.local_id, ev.tarea_id, ev.turno_id, ev.fecha,
                    ev.beneficiario_id, p.nombres AS nombre, ev.calificacion, pv.nombres AS votante_nombre,
                    l.descripcion AS local_desc, tl.descripcion AS tarea_desc, t.descripcion AS turno_desc
             FROM estrella_voto ev
             INNER JOIN postulante p     ON p.id_postulante  = ev.beneficiario_id
             INNER JOIN postulante pv    ON pv.id_postulante = ev.votante_id
             INNER JOIN local l          ON l.id_local       = ev.local_id
             INNER JOIN tarea_limpieza tl ON tl.id_tarea      = ev.tarea_id
             INNER JOIN turno t          ON t.id_turno       = ev.turno_id
             WHERE ev.fecha BETWEEN :desde AND :hasta AND ev.sancionado = 0
             ORDER BY ev.fecha DESC, ev.id_voto DESC"
        );
        $stmt->execute(['desde' => $desde, 'hasta' => $hasta]);
        return $stmt->fetchAll();
    }

    /** Existe algún voto (no sancionado) del sistema anterior para ese slot exacto */
    private function existeSlotLegado(int $tareaId, int $localId, int $turnoId, string $fecha): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM estrella_voto
             WHERE tarea_id = :t AND local_id = :l AND turno_id = :turno AND fecha = :f AND sancionado = 0 LIMIT 1"
        );
        $stmt->execute(['t' => $tareaId, 'l' => $localId, 'turno' => $turnoId, 'f' => $fecha]);
        return (bool)$stmt->fetchColumn();
    }

    /**
     * Elimina un voto del sistema anterior (ej. alguien marcó que un compañero limpió y
     * no era cierto), pidiendo la contraseña de quien lo borra — para cuando se hace desde
     * el portal de staff. Al borrarlo, ese beneficiario deja de sumar esas estrellas
     * automáticamente (el balance se recalcula solo, no hace falta un ajuste aparte).
     */
    public function eliminarVotoLegado(int $adminId, int $votoId, string $password): string|bool
    {
        $stmt = $this->db->prepare("SELECT password FROM usuario WHERE postulante_id = :pid LIMIT 1");
        $stmt->execute(['pid' => $adminId]);
        $hash = $stmt->fetchColumn();
        if (!$hash || !password_verify($password, $hash)) return 'Contraseña incorrecta';

        return $this->eliminarVoto($votoId);
    }

    /** Igual que arriba, pero sin pedir contraseña — para el panel de admin (ya gateado por sesión) */
    public function eliminarVoto(int $votoId): string|bool
    {
        $stmt = $this->db->prepare("DELETE FROM estrella_voto WHERE id_voto = :id");
        $stmt->execute(['id' => $votoId]);
        return $stmt->rowCount() > 0 ? true : 'No se encontró ese registro.';
    }

    /**
     * Autoasignación de una actividad de limpieza (barrer, trapear, ...) para un local,
     * turno y fecha. Cada (tarea, local, turno, fecha) es un solo casillero: una vez
     * ocupado queda congelado, solo un admin puede reasignarlo (a sí mismo o a otra persona).
     */
    public function asignarLimpieza(
        int $postulanteId, int $tareaId, int $localId, int $turnoId, string $fecha,
        string $password, bool $isAdmin, ?int $asignarA = null
    ): array|string {
        $stmt = $this->db->prepare("SELECT password FROM usuario WHERE postulante_id = :pid LIMIT 1");
        $stmt->execute(['pid' => $postulanteId]);
        $hash = $stmt->fetchColumn();
        if (!$hash || !password_verify($password, $hash)) return 'Contraseña incorrecta';

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) return 'Fecha inválida';

        $tarea = $this->getTarea($tareaId);
        if (!$tarea) return 'Actividad no válida';

        $localCheck = $this->db->prepare("SELECT 1 FROM local WHERE id_local = :id AND activo = 1");
        $localCheck->execute(['id' => $localId]);
        if (!$localCheck->fetchColumn()) return 'Local no válido';

        $turnoCheck = $this->db->prepare("SELECT 1 FROM turno WHERE id_turno = :id AND activo = 1");
        $turnoCheck->execute(['id' => $turnoId]);
        if (!$turnoCheck->fetchColumn()) return 'Turno no válido';

        $hoy = date('Y-m-d');
        $primerDiaMes = date('Y-m-01');
        if (!$isAdmin && ($fecha < $primerDiaMes || $fecha > $hoy)) {
            return 'Solo puedes registrar días de este mes, hasta hoy';
        }
        if ($isAdmin && $fecha > $hoy) {
            return 'No puedes registrar un día que aún no llega';
        }

        // Cualquiera puede elegir quién hizo la limpieza (no solo autoasignarse) siempre que
        // el casillero esté libre — confirma con su propia contraseña, no la de esa persona.
        // Reasignar un casillero YA ocupado sigue siendo exclusivo de un administrador
        // (se valida más abajo, al revisar si el slot ya existe).
        $beneficiarioId = $asignarA ?? $postulanteId;

        $checkPost = $this->db->prepare("SELECT 1 FROM postulante WHERE id_postulante = :id");
        $checkPost->execute(['id' => $beneficiarioId]);
        if (!$checkPost->fetchColumn()) return 'Trabajador no válido';

        $existe = $this->db->prepare(
            "SELECT id_registro FROM limpieza_registro
             WHERE tarea_id = :t AND local_id = :l AND turno_id = :turno AND fecha = :f"
        );
        $existe->execute(['t' => $tareaId, 'l' => $localId, 'turno' => $turnoId, 'f' => $fecha]);
        $idExistente = $existe->fetchColumn();

        if ($idExistente && !$isAdmin) {
            return 'Ya se registró esta actividad ese turno — solo un administrador puede cambiarlo';
        }

        // Ese slot ya sumó estrellas por el sistema de votos anterior: no se puede volver
        // a asignar (ni el admin), porque duplicaría el conteo en el ranking del mes.
        if (!$idExistente && $this->existeSlotLegado($tareaId, $localId, $turnoId, $fecha)) {
            return 'Ese turno ya quedó registrado por el sistema anterior de votos — no se puede volver a asignar';
        }

        $estrellas = $this->getValorTarea($tareaId, $localId, $fecha);

        if ($idExistente) {
            $this->db->prepare(
                "UPDATE limpieza_registro
                 SET postulante_id = :pid, estrellas = :est, registrado_por = :reg, fecha_registro = CURRENT_TIMESTAMP
                 WHERE id_registro = :id"
            )->execute(['pid' => $beneficiarioId, 'est' => $estrellas, 'reg' => $postulanteId, 'id' => $idExistente]);
        } else {
            $this->db->prepare(
                "INSERT INTO limpieza_registro (tarea_id, local_id, turno_id, fecha, postulante_id, estrellas, registrado_por)
                 VALUES (:t, :l, :turno, :f, :pid, :est, :reg)"
            )->execute(['t' => $tareaId, 'l' => $localId, 'turno' => $turnoId, 'f' => $fecha, 'pid' => $beneficiarioId, 'est' => $estrellas, 'reg' => $postulanteId]);
        }

        $nombreStmt = $this->db->prepare("SELECT nombres FROM postulante WHERE id_postulante = :id");
        $nombreStmt->execute(['id' => $beneficiarioId]);

        return [
            'ok' => true, 'estrellas' => $estrellas, 'tarea' => $tarea,
            'postulante_id' => $beneficiarioId, 'nombre' => $nombreStmt->fetchColumn(),
        ];
    }

    /**
     * Quita una autoasignación puesta por error, dejando el casillero libre de nuevo.
     * Exclusivo de un administrador. No aplica a los registros "legado" del sistema de
     * votos anterior (esos viven en otra tabla, con otra forma) — si el casillero no
     * existe en limpieza_registro, no hay nada que quitar desde acá.
     */
    public function quitarAsignacionLimpieza(
        int $adminId, int $tareaId, int $localId, int $turnoId, string $fecha, string $password
    ): string|bool {
        $stmt = $this->db->prepare("SELECT password FROM usuario WHERE postulante_id = :pid LIMIT 1");
        $stmt->execute(['pid' => $adminId]);
        $hash = $stmt->fetchColumn();
        if (!$hash || !password_verify($password, $hash)) return 'Contraseña incorrecta';

        $stmt = $this->db->prepare(
            "DELETE FROM limpieza_registro WHERE tarea_id = :t AND local_id = :l AND turno_id = :turno AND fecha = :f"
        );
        $stmt->execute(['t' => $tareaId, 'l' => $localId, 'turno' => $turnoId, 'f' => $fecha]);
        return $stmt->rowCount() > 0 ? true : 'No había ninguna asignación registrada ahí.';
    }

    /**
     * Ranking mensual de limpieza: cuántas veces se anotó cada quien en cada actividad,
     * de mayor a menor total (incluye a todo el staff activo, aunque tenga 0, para que
     * se note quién no está limpiando). Suma también los votos históricos del sistema
     * anterior por compañeros, para no perder lo ya ganado en el mes de transición.
     */
    public function getRankingLimpieza(string $desde, string $hasta): array
    {
        $desdeEf = $this->epochFloor($desde);
        $staff = $this->getStaffActivo();

        $conteo = [];
        $stmt = $this->db->prepare(
            "SELECT lr.postulante_id AS pid, tl.codigo, COUNT(*) AS n
             FROM limpieza_registro lr INNER JOIN tarea_limpieza tl ON tl.id_tarea = lr.tarea_id
             WHERE lr.fecha BETWEEN :desde AND :hasta GROUP BY lr.postulante_id, tl.codigo"
        );
        $stmt->execute(['desde' => $desdeEf, 'hasta' => $hasta]);
        foreach ($stmt->fetchAll() as $r) {
            $conteo[(int)$r['pid']][$r['codigo']] = ($conteo[(int)$r['pid']][$r['codigo']] ?? 0) + (int)$r['n'];
        }

        $stmtVoto = $this->db->prepare(
            "SELECT ev.beneficiario_id AS pid, tl.codigo, COUNT(*) AS n
             FROM estrella_voto ev INNER JOIN tarea_limpieza tl ON tl.id_tarea = ev.tarea_id
             WHERE ev.fecha BETWEEN :desde AND :hasta AND ev.sancionado = 0
             GROUP BY ev.beneficiario_id, tl.codigo"
        );
        $stmtVoto->execute(['desde' => $desdeEf, 'hasta' => $hasta]);
        foreach ($stmtVoto->fetchAll() as $r) {
            $conteo[(int)$r['pid']][$r['codigo']] = ($conteo[(int)$r['pid']][$r['codigo']] ?? 0) + (int)$r['n'];
        }

        $tareas = $this->getTareas();
        $out = [];
        foreach ($staff as $s) {
            $pid = (int)$s['id'];
            $fila = $conteo[$pid] ?? [];
            $total = 0;
            $porTarea = [];
            foreach ($tareas as $t) {
                $n = $fila[$t['codigo']] ?? 0;
                $porTarea[$t['codigo']] = $n;
                $total += $n;
            }
            $out[] = ['id' => $pid, 'nombre' => $s['nombre'], 'tareas' => $porTarea, 'total' => $total];
        }

        usort($out, fn($a, $b) => $b['total'] <=> $a['total'] ?: strcmp($a['nombre'], $b['nombre']));

        $posicion = 0; $anterior = null; $rango = 0;
        foreach ($out as &$fila) {
            $posicion++;
            if ($fila['total'] !== $anterior) { $rango = $posicion; $anterior = $fila['total']; }
            $fila['pos'] = $rango;
        }
        return $out;
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

        $stmtAutoasignadas = $this->db->prepare(
            "SELECT COALESCE(SUM(estrellas), 0) FROM limpieza_registro
             WHERE postulante_id = :pid AND fecha BETWEEN :desde AND :hasta"
        );
        $stmtAutoasignadas->execute(['pid' => $postulanteId, 'desde' => $desdeEf, 'hasta' => $hasta]);
        $azulesTareas += (float)$stmtAutoasignadas->fetchColumn();

        $stmtAjustes = $this->db->prepare(
            "SELECT COALESCE(SUM(estrellas), 0) FROM estrella_ajuste
             WHERE postulante_id = :pid AND fecha BETWEEN :desde AND :hasta"
        );
        $stmtAjustes->execute(['pid' => $postulanteId, 'desde' => $desdeEf, 'hasta' => $hasta]);
        $azulesAjustes = (int)$stmtAjustes->fetchColumn();

        $baseRojas  = $this->getParametro(self::CLAVE_BASE_ROJAS, $desdeEf);
        $baseAzules = $this->getParametro(self::CLAVE_BASE_AZULES, $desdeEf);
        $rojas  = $baseRojas  + $rojasTareas;
        $azules = round($baseAzules + $azulesTareas + $azulesAjustes, 1);
        $diferencia = $rojas - $azules;

        return [
            'turnos'         => $turnos,
            'rojas'          => $rojas,
            'azules_tareas'  => round($azulesTareas, 1),
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

        $staff = $this->getStaffActivo();

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

        $stmtAuto = $this->db->prepare(
            "SELECT lr.postulante_id AS pid, tl.codigo, lr.estrellas
             FROM limpieza_registro lr INNER JOIN tarea_limpieza tl ON tl.id_tarea = lr.tarea_id
             WHERE lr.fecha BETWEEN :desde AND :hasta"
        );
        $stmtAuto->execute(['desde' => $desdeEf, 'hasta' => $hasta]);
        foreach ($stmtAuto->fetchAll() as $g) {
            $pid = (int)$g['pid'];
            $ganadasMap[$pid]['total'] = ($ganadasMap[$pid]['total'] ?? 0) + (int)$g['estrellas'];
            $codigo = $g['codigo'];
            $ganadasMap[$pid][$codigo] = ($ganadasMap[$pid][$codigo] ?? 0) + 1;
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
            $azulesAjustes = $ajustesMap[$pid] ?? 0;
            $rojas        = $baseRojas  + ($rojasTareasMap[$pid] ?? 0);
            $azules       = round($baseAzules + $azulesTareas + $azulesAjustes, 1);
            $diferencia   = $rojas - $azules;
            $out[] = [
                'id'            => $pid,
                'nombre'        => $s['nombre'],
                'turnos'        => $turnos,
                'rojas'         => $rojas,
                'azules_tareas' => $azulesTareas,
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
            "SELECT tarea, estrellas_max, fecha, local_desc, turno_desc, azules, sancionado FROM (
                SELECT tl.descripcion AS tarea, tl.estrellas_max, ev.fecha, l.descripcion AS local_desc,
                       t.descripcion AS turno_desc, ev.calificacion AS azules, ev.sancionado
                FROM estrella_voto ev
                INNER JOIN tarea_limpieza tl ON tl.id_tarea = ev.tarea_id
                INNER JOIN local l           ON l.id_local  = ev.local_id
                INNER JOIN turno t           ON t.id_turno  = ev.turno_id
                WHERE ev.beneficiario_id = :pid1 AND ev.fecha BETWEEN :desde1 AND :hasta1

                UNION ALL

                SELECT tl.descripcion AS tarea, tl.estrellas_max, lr.fecha, l.descripcion AS local_desc,
                       NULL AS turno_desc, lr.estrellas AS azules, 0 AS sancionado
                FROM limpieza_registro lr
                INNER JOIN tarea_limpieza tl ON tl.id_tarea  = lr.tarea_id
                INNER JOIN local l           ON l.id_local   = lr.local_id
                WHERE lr.postulante_id = :pid2 AND lr.fecha BETWEEN :desde2 AND :hasta2
            ) x
            ORDER BY fecha DESC"
        );
        $stmt->execute([
            'pid1' => $postulanteId, 'desde1' => $desdeEf, 'hasta1' => $hasta,
            'pid2' => $postulanteId, 'desde2' => $desdeEf, 'hasta2' => $hasta,
        ]);
        $rows = $stmt->fetchAll();
        foreach ($rows as &$r) {
            $r['sancionado'] = (bool)$r['sancionado'];
            $r['azules']     = $r['sancionado'] ? 0 : (int)$r['azules'];
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
