<?php

require_once __DIR__ . '/../Core/Database.php';

class HorarioRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Configuración de slots por local, leída desde plantilla_horario.
     * Devuelve [local_id => [codigo_rol => cantidad]]  (igual que antes pero desde BD).
     */
    public function getSlotsConfig(): array
    {
        $stmt = $this->db->query(
            "SELECT ph.local_id, rh.codigo, ph.cantidad, rh.id_rol_horario, rh.orden
             FROM plantilla_horario ph
             INNER JOIN rol_horario rh ON ph.rol_horario_id = rh.id_rol_horario
             GROUP BY ph.local_id, rh.id_rol_horario, rh.codigo, ph.cantidad, rh.orden
             ORDER BY ph.local_id, rh.orden"
        );
        $config = [];
        foreach ($stmt->fetchAll() as $r) {
            $config[$r['local_id']][$r['codigo']] = (int)$r['cantidad'];
        }
        return $config;
    }

    /**
     * Roles disponibles: [codigo => ['desc' => '...', 'opcional' => 0/1]]
     */
    public function getRoles(): array
    {
        $stmt = $this->db->query(
            "SELECT codigo, descripcion, es_opcional, color FROM rol_horario ORDER BY orden"
        );
        $roles = [];
        foreach ($stmt->fetchAll() as $r) {
            $roles[$r['codigo']] = [
                'desc'     => $r['descripcion'],
                'opcional' => (bool)$r['es_opcional'],
                'color'    => $r['color'],
            ];
        }
        return $roles;
    }

    // ── SEMANAS ────────────────────────────────────────────

    /** Semana vigente: contiene el día de hoy */
    public function getSemanaVigente(): ?array
    {
        $stmt = $this->db->query(
            "SELECT * FROM semana
             WHERE fecha_inicio <= CURDATE() AND fecha_fin >= CURDATE()
             LIMIT 1"
        );
        return $stmt->fetch() ?: null;
    }

    /** Próxima semana: empieza después de hoy */
    public function getSemanaProxima(): ?array
    {
        $stmt = $this->db->query(
            "SELECT * FROM semana
             WHERE fecha_inicio > CURDATE()
             ORDER BY fecha_inicio ASC
             LIMIT 1"
        );
        return $stmt->fetch() ?: null;
    }

    /** Devuelve la semana abierta más próxima (actual o siguiente) */
    public function getSemanaActual(): ?array
    {
        $stmt = $this->db->query(
            "SELECT * FROM semana
             WHERE fecha_fin >= CURDATE()
             ORDER BY fecha_inicio ASC
             LIMIT 1"
        );
        return $stmt->fetch() ?: null;
    }

    public function getSemanaById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM semana WHERE id_semana = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /** Crea la semana siguiente al lunes próximo si no existe */
    public function crearProximaSemana(): array
    {
        // Calcular lunes próximo
        $hoy     = new DateTime('now', new DateTimeZone('America/Lima'));
        $diaSem  = (int)$hoy->format('N'); // 1=Lun … 7=Dom
        $diasHasta = $diaSem === 7 ? 1 : (8 - $diaSem); // días hasta el próximo lunes
        $lunes   = clone $hoy;
        $lunes->modify("+{$diasHasta} days");
        $domingo = clone $lunes;
        $domingo->modify('+6 days');

        $fi = $lunes->format('Y-m-d');
        $ff = $domingo->format('Y-m-d');

        // Verificar si ya existe
        $chk = $this->db->prepare("SELECT * FROM semana WHERE fecha_inicio = :fi LIMIT 1");
        $chk->execute(['fi' => $fi]);
        $existente = $chk->fetch();
        if ($existente) return $existente;

        $this->db->prepare(
            "INSERT INTO semana (fecha_inicio, fecha_fin, estado) VALUES (:fi, :ff, 'ABIERTA')"
        )->execute(['fi' => $fi, 'ff' => $ff]);

        $id     = (int)$this->db->lastInsertId();
        $semana = $this->getSemanaById($id);

        // Pre-crear todos los slots vacíos
        $this->inicializarSlots($semana);

        // Clonar asignaciones de la semana más reciente con datos
        $this->clonarAsignacionesRecientes($id);

        return $semana;
    }

    /** Copia las asignaciones de la semana anterior más reciente a la nueva semana */
    private function clonarAsignacionesRecientes(int $semanaDestinoId): void
    {
        // Buscar la semana con más asignaciones reciente (excluyendo la nueva)
        $stmt = $this->db->prepare(
            "SELECT s.id_semana
             FROM semana s
             INNER JOIN horario_slot hs ON hs.semana_id = s.id_semana
             WHERE hs.postulante_id IS NOT NULL
               AND s.id_semana != :did
             GROUP BY s.id_semana
             ORDER BY s.fecha_inicio DESC
             LIMIT 1"
        );
        $stmt->execute(['did' => $semanaDestinoId]);
        $origenId = $stmt->fetchColumn();
        if (!$origenId) return;

        // Copiar asignaciones emparejando por día de la semana
        $this->db->prepare(
            "UPDATE horario_slot dest
             INNER JOIN horario_slot src
                 ON  src.semana_id      = :origen
                 AND src.local_id       = dest.local_id
                 AND src.turno_id       = dest.turno_id
                 AND DAYOFWEEK(src.fecha_dia) = DAYOFWEEK(dest.fecha_dia)
                 AND src.rol_horario_id = dest.rol_horario_id
                 AND src.slot_num       = dest.slot_num
                 AND src.postulante_id  IS NOT NULL
             SET dest.postulante_id    = src.postulante_id,
                 dest.fecha_asignacion = NOW()
             WHERE dest.semana_id = :destino"
        )->execute(['origen' => $origenId, 'destino' => $semanaDestinoId]);
    }

    /** Rellena horario_slot con todos los asientos vacíos para la semana */
    private function inicializarSlots(array $semana): void
    {
        // Leer plantilla desde BD
        $plantilla = $this->db->query(
            "SELECT ph.local_id, ph.turno_id, ph.rol_horario_id, ph.cantidad
             FROM plantilla_horario ph
             ORDER BY ph.local_id, ph.turno_id, ph.rol_horario_id"
        )->fetchAll();

        $stmt = $this->db->prepare(
            "INSERT IGNORE INTO horario_slot
                (semana_id, local_id, turno_id, fecha_dia, rol_horario_id, slot_num)
             VALUES (:sid, :lid, :tid, :fdia, :rid, :num)"
        );

        $inicio = new DateTime($semana['fecha_inicio']);

        for ($d = 0; $d < 7; $d++) {
            $dia   = clone $inicio;
            $dia->modify("+{$d} days");
            $fecha = $dia->format('Y-m-d');

            foreach ($plantilla as $p) {
                for ($n = 1; $n <= $p['cantidad']; $n++) {
                    $stmt->execute([
                        'sid'  => $semana['id_semana'],
                        'lid'  => $p['local_id'],
                        'tid'  => $p['turno_id'],
                        'fdia' => $fecha,
                        'rid'  => $p['rol_horario_id'],
                        'num'  => $n,
                    ]);
                }
            }
        }
    }

    public function cerrarSemana(int $semanaId): void
    {
        $this->db->prepare("UPDATE semana SET estado = 'CERRADA' WHERE id_semana = :id")
                 ->execute(['id' => $semanaId]);
    }

    /** Cierra automáticamente semanas cuya fecha_fin ya pasó */
    public function cerrarSemanasVencidas(): void
    {
        $this->db->exec(
            "UPDATE semana SET estado = 'CERRADA'
             WHERE estado = 'ABIERTA' AND fecha_fin < CURDATE()"
        );
    }

    /**
     * Algoritmo rolling: garantiza que siempre existan 3 semanas consecutivas.
     *  - W   (actual)   → congelada automáticamente al vencer
     *  - W+1 (siguiente)→ editable, espejo de W al crearse
     *  - W+2 (tras sig.)→ editable, espejo de W+1 al crearse
     *
     * Solo crea las semanas que faltan; nunca sobreescribe las existentes.
     */
    public function asegurarSemanasRolling(): void
    {
        $actual = $this->getSemanaVigente();
        if (!$actual) return;

        $siguiente = $this->crearSemanaEspejo($actual);       // W+1
        if ($siguiente) $this->crearSemanaEspejo($siguiente); // W+2
    }

    /** Crea la semana siguiente a $base (1 semana después) si no existe, clonando sus datos */
    private function crearSemanaEspejo(array $base): ?array
    {
        $lunes   = (new DateTime($base['fecha_inicio']))->modify('+7 days');
        $domingo = (clone $lunes)->modify('+6 days');
        $fi      = $lunes->format('Y-m-d');
        $ff      = $domingo->format('Y-m-d');

        $chk = $this->db->prepare("SELECT * FROM semana WHERE fecha_inicio = :fi LIMIT 1");
        $chk->execute(['fi' => $fi]);
        $existente = $chk->fetch();
        if ($existente) return $existente; // ya existe, no tocar

        // Crear nueva semana
        $this->db->prepare(
            "INSERT INTO semana (fecha_inicio, fecha_fin, estado) VALUES (:fi, :ff, 'ABIERTA')"
        )->execute(['fi' => $fi, 'ff' => $ff]);

        $id    = (int)$this->db->lastInsertId();
        $nueva = $this->getSemanaById($id);

        $this->inicializarSlots($nueva);
        $this->clonarAsignaciones($base['id_semana'], $id);

        return $nueva;
    }

    /** Copia asignaciones de una semana a otra emparejando por día de la semana (Lun→Lun, Mar→Mar…) */
    public function clonarAsignaciones(int $origenId, int $destinoId): void
    {
        $this->db->prepare(
            "UPDATE horario_slot dest
             INNER JOIN horario_slot src
                 ON  src.semana_id      = :origen
                 AND src.local_id       = dest.local_id
                 AND src.turno_id       = dest.turno_id
                 AND DAYOFWEEK(src.fecha_dia) = DAYOFWEEK(dest.fecha_dia)
                 AND src.rol_horario_id = dest.rol_horario_id
                 AND src.slot_num       = dest.slot_num
                 AND src.postulante_id  IS NOT NULL
             SET dest.postulante_id    = src.postulante_id,
                 dest.fecha_asignacion = NOW()
             WHERE dest.semana_id = :destino"
        )->execute(['origen' => $origenId, 'destino' => $destinoId]);
    }

    // ── ESPEJO ────────────────────────────────────────────

    /** 2.ª semana futura ABIERTA = espejo (no editable directamente) */
    public function getSemanaEspejo(): ?array
    {
        $stmt = $this->db->query(
            "SELECT * FROM semana
             WHERE fecha_inicio > CURDATE() AND estado = 'ABIERTA'
             ORDER BY fecha_inicio ASC
             LIMIT 1 OFFSET 1"
        );
        return $stmt->fetch() ?: null;
    }

    private function esEspejo(int $semanaId): bool
    {
        $espejo = $this->getSemanaEspejo();
        return $espejo !== null && (int)$espejo['id_semana'] === $semanaId;
    }

    /**
     * Propaga un cambio de slot (asignar/liberar) al slot equivalente del espejo.
     * Solo actúa si el slot pertenece a la 1.ª semana futura (editable).
     */
    private function propagarEspejo(int $slotId, ?int $postulanteId): void
    {
        $espejo = $this->getSemanaEspejo();
        if (!$espejo) return;

        $slot = $this->getSlotById($slotId);
        if (!$slot) return;

        // Solo propagar desde la semana editable
        $proxima = $this->getSemanaProxima();
        if (!$proxima || (int)$slot['semana_id'] !== (int)$proxima['id_semana']) return;

        $stmt = $this->db->prepare(
            "SELECT id_slot FROM horario_slot
             WHERE semana_id      = :eid
               AND local_id       = :lid
               AND turno_id       = :tid
               AND DAYOFWEEK(fecha_dia) = DAYOFWEEK(:fdia)
               AND rol_horario_id = :rid
               AND slot_num       = :snum
             LIMIT 1"
        );
        $stmt->execute([
            'eid'  => $espejo['id_semana'],
            'lid'  => $slot['local_id'],
            'tid'  => $slot['turno_id'],
            'fdia' => $slot['fecha_dia'],
            'rid'  => $slot['rol_horario_id'],
            'snum' => $slot['slot_num'],
        ]);
        $espejoSlotId = $stmt->fetchColumn();
        if (!$espejoSlotId) return;

        if ($postulanteId !== null) {
            $this->db->prepare(
                "UPDATE horario_slot SET postulante_id = :pid, fecha_asignacion = NOW() WHERE id_slot = :id"
            )->execute(['pid' => $postulanteId, 'id' => $espejoSlotId]);
        } else {
            $this->db->prepare(
                "UPDATE horario_slot SET postulante_id = NULL, fecha_asignacion = NULL WHERE id_slot = :id"
            )->execute(['id' => $espejoSlotId]);
        }
    }

    /** Semanas pasadas (cerradas) para el historial, últimas 20 */
    public function getSemanasHistorial(int $limit = 20): array
    {
        $stmt = $this->db->query(
            "SELECT * FROM semana
             WHERE fecha_fin < CURDATE()
             ORDER BY fecha_inicio DESC
             LIMIT {$limit}"
        );
        return $stmt->fetchAll();
    }

    // ── SLOTS ─────────────────────────────────────────────

    /** Devuelve todos los slots de la semana con info de quién los ocupa y solicitud activa más reciente */
    public function getSlotsBySemana(int $semanaId): array
    {
        $stmt = $this->db->prepare(
            "SELECT hs.*,
                    rh.codigo      AS rol_puesto,
                    rh.es_opcional,
                    DAYOFWEEK(hs.fecha_dia) - 1 AS dia_semana_raw,
                    IF(DAYOFWEEK(hs.fecha_dia)=1, 7, DAYOFWEEK(hs.fecha_dia)-1) AS dia_semana,
                    p.nombres                        AS trabajador_nombre,
                    sc.tipo                          AS solicitud_tipo,
                    sc.id_solicitud                  AS id_solicitud_activa,
                    sc.postulante_solicitante_id     AS solicitud_solicitante_id,
                    sc.postulante_original_id        AS cobertura_original_id,
                    po.nombres                       AS original_nombre,
                    IF(sc.tipo = 'COBERTURA', 1, 0) AS es_cobertura,
                    IF(EXISTS(
                        SELECT 1 FROM asistencia a2
                        WHERE a2.postulante_id = hs.postulante_id
                          AND a2.fecha         = hs.fecha_dia
                          AND a2.turno_id      = hs.turno_id
                    ), 1, 0)                         AS encuestado
             FROM horario_slot hs
             INNER JOIN rol_horario rh ON hs.rol_horario_id = rh.id_rol_horario
             LEFT  JOIN postulante p   ON hs.postulante_id  = p.id_postulante
             LEFT  JOIN solicitud_cambio sc
                     ON sc.id_solicitud = (
                            SELECT MAX(s2.id_solicitud)
                            FROM solicitud_cambio s2
                            WHERE s2.slot_id = hs.id_slot AND s2.estado = 'ACTIVA'
                        )
             LEFT  JOIN postulante po  ON po.id_postulante = sc.postulante_original_id
             WHERE hs.semana_id = :sid
             ORDER BY hs.local_id, hs.turno_id, hs.fecha_dia, rh.id_rol_horario, hs.slot_num"
        );
        $stmt->execute(['sid' => $semanaId]);
        return $stmt->fetchAll();
    }

    /** Asigna un trabajador a un slot. Valida conflictos. */
    public function asignarSlot(int $slotId, int $semanaId, int $postulanteId): string
    {
        // Verificar que el slot existe y está libre
        $slot = $this->getSlotById($slotId);
        if (!$slot) return 'Slot no encontrado';

        // Bloquear edición directa del espejo
        if ($this->esEspejo($semanaId) || $this->esEspejo((int)$slot['semana_id'])) {
            return 'La semana espejo se actualiza automáticamente; no puede editarse directamente';
        }

        if ($slot['postulante_id'] && $slot['postulante_id'] != $postulanteId) {
            return 'Este turno ya está tomado por otra persona';
        }

        $esLimpieza = ($slot['rol_puesto'] === 'LIMPIEZA');

        if ($esLimpieza) {
            // Debe tener turno no-limpieza en el mismo local Y mismo turno ese día
            $trabajaLocal = $this->db->prepare(
                "SELECT COUNT(*) FROM horario_slot hs
                 INNER JOIN rol_horario rh ON hs.rol_horario_id = rh.id_rol_horario
                 WHERE hs.semana_id     = :sid
                   AND hs.postulante_id = :pid
                   AND hs.local_id      = :lid
                   AND hs.turno_id      = :tid
                   AND hs.fecha_dia     = :fdia
                   AND rh.codigo       != 'LIMPIEZA'"
            );
            $trabajaLocal->execute([
                'sid'  => $semanaId,
                'pid'  => $postulanteId,
                'lid'  => $slot['local_id'],
                'tid'  => $slot['turno_id'],
                'fdia' => $slot['fecha_dia'],
            ]);
            if ((int)$trabajaLocal->fetchColumn() === 0) {
                return 'Solo puedes tomar limpieza en el local y turno donde trabajas ese día';
            }
        } else {
            // Un trabajador solo puede ocupar UN slot por turno+día
            $conflicto = $this->db->prepare(
                "SELECT id_slot FROM horario_slot
                 WHERE semana_id     = :sid
                   AND postulante_id = :pid
                   AND turno_id      = :tid
                   AND fecha_dia     = :fdia
                   AND id_slot      != :sid_actual
                 LIMIT 1"
            );
            $conflicto->execute([
                'sid'        => $semanaId,
                'pid'        => $postulanteId,
                'tid'        => $slot['turno_id'],
                'fdia'       => $slot['fecha_dia'],
                'sid_actual' => $slotId,
            ]);
            if ($conflicto->fetch()) {
                return 'Ya tienes un turno asignado en este horario (mismo turno y día)';
            }
        }

        // Asignar
        $this->db->prepare(
            "UPDATE horario_slot
             SET postulante_id = :pid, fecha_asignacion = NOW()
             WHERE id_slot = :id"
        )->execute(['pid' => $postulanteId, 'id' => $slotId]);

        // Propagar al espejo
        $this->propagarEspejo($slotId, $postulanteId);

        return 'ok';
    }

    public function liberarSlot(int $slotId, int $postulanteId): string
    {
        $slot = $this->getSlotById($slotId);
        if (!$slot) return 'Slot no encontrado';

        // Bloquear edición directa del espejo
        if ($this->esEspejo((int)$slot['semana_id'])) {
            return 'La semana espejo se actualiza automáticamente; no puede editarse directamente';
        }

        $stmt = $this->db->prepare(
            "UPDATE horario_slot
             SET postulante_id = NULL, fecha_asignacion = NULL
             WHERE id_slot = :id AND postulante_id = :pid"
        );
        $stmt->execute(['id' => $slotId, 'pid' => $postulanteId]);

        if ($stmt->rowCount() === 0) return 'Solo puedes liberar tus propios turnos';

        // Propagar al espejo antes del cascade (para que el estado sea consistente)
        if ($slot['rol_puesto'] !== 'LIMPIEZA') {
            $this->propagarEspejo($slotId, null);
            $this->limpiezaCascade($postulanteId, (int)$slot['semana_id'], (int)$slot['local_id'], (int)$slot['turno_id'], $slot['fecha_dia']);
        } else {
            $this->propagarEspejo($slotId, null);
        }

        return 'ok';
    }

    private function limpiezaCascade(int $postulanteId, int $semanaId, int $localId, int $turnoId, string $fechaDia): void
    {
        // Verifica si aún tiene algún slot no-LIMPIEZA en ese local/turno/día
        $check = $this->db->prepare(
            "SELECT COUNT(*) FROM horario_slot hs
             INNER JOIN rol_horario rh ON hs.rol_horario_id = rh.id_rol_horario
             WHERE hs.semana_id     = :sid
               AND hs.postulante_id = :pid
               AND hs.local_id      = :lid
               AND hs.turno_id      = :tid
               AND hs.fecha_dia     = :fdia
               AND rh.codigo       != 'LIMPIEZA'"
        );
        $check->execute(['sid' => $semanaId, 'pid' => $postulanteId, 'lid' => $localId, 'tid' => $turnoId, 'fdia' => $fechaDia]);

        if ((int)$check->fetchColumn() === 0) {
            // Sin respaldo → liberar su LIMPIEZA del mismo local/turno/día
            $this->db->prepare(
                "UPDATE horario_slot hs
                 INNER JOIN rol_horario rh ON hs.rol_horario_id = rh.id_rol_horario
                 SET hs.postulante_id = NULL, hs.fecha_asignacion = NULL
                 WHERE hs.semana_id     = :sid
                   AND hs.postulante_id = :pid
                   AND hs.local_id      = :lid
                   AND hs.turno_id      = :tid
                   AND hs.fecha_dia     = :fdia
                   AND rh.codigo        = 'LIMPIEZA'"
            )->execute(['sid' => $semanaId, 'pid' => $postulanteId, 'lid' => $localId, 'tid' => $turnoId, 'fdia' => $fechaDia]);
        }

        // Si la semana modificada es la editable, propagar cascade también al espejo
        $espejo  = $this->getSemanaEspejo();
        $proxima = $this->getSemanaProxima();
        if (!$espejo || !$proxima || $semanaId !== (int)$proxima['id_semana']) return;

        $checkE = $this->db->prepare(
            "SELECT COUNT(*) FROM horario_slot hs
             INNER JOIN rol_horario rh ON hs.rol_horario_id = rh.id_rol_horario
             WHERE hs.semana_id     = :sid
               AND hs.postulante_id = :pid
               AND hs.local_id      = :lid
               AND hs.turno_id      = :tid
               AND DAYOFWEEK(hs.fecha_dia) = DAYOFWEEK(:fdia)
               AND rh.codigo       != 'LIMPIEZA'"
        );
        $checkE->execute(['sid' => $espejo['id_semana'], 'pid' => $postulanteId, 'lid' => $localId, 'tid' => $turnoId, 'fdia' => $fechaDia]);

        if ((int)$checkE->fetchColumn() === 0) {
            $this->db->prepare(
                "UPDATE horario_slot hs
                 INNER JOIN rol_horario rh ON hs.rol_horario_id = rh.id_rol_horario
                 SET hs.postulante_id = NULL, hs.fecha_asignacion = NULL
                 WHERE hs.semana_id     = :sid
                   AND hs.postulante_id = :pid
                   AND hs.local_id      = :lid
                   AND hs.turno_id      = :tid
                   AND DAYOFWEEK(hs.fecha_dia) = DAYOFWEEK(:fdia)
                   AND rh.codigo        = 'LIMPIEZA'"
            )->execute(['sid' => $espejo['id_semana'], 'pid' => $postulanteId, 'lid' => $localId, 'tid' => $turnoId, 'fdia' => $fechaDia]);
        }
    }

    public function getSlotById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT hs.*, rh.codigo AS rol_puesto, rh.es_opcional
             FROM horario_slot hs
             INNER JOIN rol_horario rh ON hs.rol_horario_id = rh.id_rol_horario
             WHERE hs.id_slot = :id"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /** Slots de un día específico con nombres y locales */
    public function getSlotsByFecha(string $fecha): array
    {
        $stmt = $this->db->prepare(
            "SELECT hs.*,
                    rh.codigo      AS rol_puesto,
                    rh.es_opcional,
                    p.nombres      AS trabajador_nombre,
                    l.descripcion  AS local_desc,
                    t.descripcion  AS turno_desc
             FROM horario_slot hs
             INNER JOIN rol_horario rh ON hs.rol_horario_id = rh.id_rol_horario
             INNER JOIN local  l ON hs.local_id  = l.id_local
             INNER JOIN turno  t ON hs.turno_id  = t.id_turno
             LEFT  JOIN postulante p ON hs.postulante_id = p.id_postulante
             WHERE hs.fecha_dia = :fecha
             ORDER BY hs.local_id, hs.turno_id, rh.id_rol_horario, hs.slot_num"
        );
        $stmt->execute(['fecha' => $fecha]);
        return $stmt->fetchAll();
    }

    /** Registra la cobertura: sustituye el slot y guarda en solicitud_cambio */
    public function cubrirSlot(int $slotId, int $solicitanteId, ?string $comentario = null): string
    {
        $slot = $this->getSlotById($slotId);
        if (!$slot) return 'Slot no encontrado';

        $hoy      = new DateTime('now', new DateTimeZone('America/Lima'));
        $fechaDia = new DateTime($slot['fecha_dia']);
        if ($fechaDia < $hoy->setTime(0, 0)) {
            return 'No se pueden modificar turnos de días pasados';
        }

        $originalId = $slot['postulante_id'] ?: null;
        if ($originalId && $originalId == $solicitanteId) return 'No puedes cubrir tu propio turno';

        // Transacción con lock: evita que dos personas tomen el mismo slot simultáneamente
        $this->db->beginTransaction();
        try {
            // Bloquear el slot para lectura exclusiva
            $stmtLock = $this->db->prepare(
                "SELECT postulante_id FROM horario_slot WHERE id_slot = :id FOR UPDATE"
            );
            $stmtLock->execute(['id' => $slotId]);
            $actualOwnerId = $stmtLock->fetchColumn();

            // Verificar que el estado no cambió desde que se abrió el modal
            if ($originalId && (int)$actualOwnerId !== (int)$originalId) {
                $this->db->rollBack();
                return 'Este puesto fue modificado por otra persona mientras esperabas. Recarga el horario e intenta de nuevo.';
            }
            if (!$originalId && $actualOwnerId !== null && $actualOwnerId !== false && (int)$actualOwnerId !== 0) {
                $this->db->rollBack();
                return 'Este puesto fue tomado por otra persona mientras esperabas. Recarga el horario.';
            }

            // Validación LIMPIEZA
            if ($slot['rol_puesto'] === 'LIMPIEZA') {
                $trabajaLocal = $this->db->prepare(
                    "SELECT COUNT(*) FROM horario_slot hs
                     INNER JOIN rol_horario rh ON hs.rol_horario_id = rh.id_rol_horario
                     WHERE hs.semana_id     = :sid AND hs.postulante_id = :pid
                       AND hs.local_id      = :lid AND hs.turno_id      = :tid
                       AND hs.fecha_dia     = :fdia AND rh.codigo       != 'LIMPIEZA'"
                );
                $trabajaLocal->execute([
                    'sid'=>$slot['semana_id'],'pid'=>$solicitanteId,
                    'lid'=>$slot['local_id'],'tid'=>$slot['turno_id'],'fdia'=>$slot['fecha_dia'],
                ]);
                if ((int)$trabajaLocal->fetchColumn() === 0) {
                    $this->db->rollBack();
                    return 'Solo puedes tomar limpieza en el local y turno donde trabajas ese día';
                }
            } else {
                // Validación conflicto: 1 slot por turno+día
                $conflicto = $this->db->prepare(
                    "SELECT id_slot FROM horario_slot
                     WHERE semana_id = :sid AND postulante_id = :pid
                       AND turno_id  = :tid AND fecha_dia     = :fdia
                       AND id_slot  != :sid_actual LIMIT 1"
                );
                $conflicto->execute([
                    'sid'=>$slot['semana_id'],'pid'=>$solicitanteId,
                    'tid'=>$slot['turno_id'],'fdia'=>$slot['fecha_dia'],'sid_actual'=>$slotId,
                ]);
                if ($conflicto->fetch()) {
                    $this->db->rollBack();
                    return 'Ya tienes un turno asignado en ese horario (mismo turno y día)';
                }
            }

            // Reasignar y registrar
            $this->db->prepare(
                "UPDATE horario_slot SET postulante_id = :pid, fecha_asignacion = NOW() WHERE id_slot = :id"
            )->execute(['pid' => $solicitanteId, 'id' => $slotId]);

            $tipo = $originalId ? 'COBERTURA' : 'CAMBIO';
            $this->db->prepare(
                "INSERT INTO solicitud_cambio
                    (slot_id, semana_id, tipo, postulante_solicitante_id, postulante_original_id, notas)
                 VALUES (:sid, :mid, :tipo, :sol, :ori, :notas)"
            )->execute([
                'sid'=>$slotId,'mid'=>$slot['semana_id'],'tipo'=>$tipo,
                'sol'=>$solicitanteId,'ori'=>$originalId,'notas'=>$comentario,
            ]);

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }

        return 'ok';
    }

    /** Intercambia dos slots del mismo turno+día (el iniciador y otra persona) */
    public function intercambiarSlots(int $miSlotId, int $otroSlotId, int $postulanteId): string
    {
        // Validaciones previas rápidas (sin lock aún)
        $miSlot   = $this->getSlotById($miSlotId);
        $otroSlot = $this->getSlotById($otroSlotId);
        if (!$miSlot || !$otroSlot) return 'Slot no encontrado';

        if ($miSlot['turno_id'] !== $otroSlot['turno_id'] || $miSlot['fecha_dia'] !== $otroSlot['fecha_dia']) {
            return 'Solo puedes intercambiar puestos del mismo turno y día';
        }
        $hoy = new DateTime('now', new DateTimeZone('America/Lima'));
        if ((new DateTime($miSlot['fecha_dia'])) < $hoy->setTime(0, 0)) {
            return 'No se pueden modificar turnos de días pasados';
        }

        // Transacción con lock para evitar intercambios concurrentes
        $this->db->beginTransaction();
        try {
            // Bloquear ambos slots para lectura exclusiva
            $stmtLock = $this->db->prepare(
                "SELECT id_slot, postulante_id, semana_id FROM horario_slot
                 WHERE id_slot IN (:id1, :id2)
                 FOR UPDATE"
            );
            $stmtLock->execute(['id1' => $miSlotId, 'id2' => $otroSlotId]);
            $locked = [];
            foreach ($stmtLock->fetchAll() as $r) {
                $locked[$r['id_slot']] = $r;
            }

            // Verificar estado actual (puede haber cambiado desde que se abrió el modal)
            $slotMio   = $locked[$miSlotId]   ?? null;
            $slotOtro  = $locked[$otroSlotId] ?? null;
            if (!$slotMio || !$slotOtro) {
                $this->db->rollBack();
                return 'Slot no encontrado';
            }
            if ((int)$slotMio['postulante_id'] !== $postulanteId) {
                $this->db->rollBack();
                return 'Tu puesto ya fue modificado por otra acción. Recarga el horario e intenta de nuevo.';
            }
            $otraPersonaId = (int)($slotOtro['postulante_id'] ?? 0);
            if (!$otraPersonaId) {
                $this->db->rollBack();
                return 'El otro puesto quedó libre mientras esperabas. Usa cubrir en su lugar.';
            }
            if ($otraPersonaId === $postulanteId) {
                $this->db->rollBack();
                return 'No puedes intercambiar contigo mismo';
            }

            // Realizar el intercambio
            $this->db->prepare(
                "UPDATE horario_slot SET postulante_id = :pid, fecha_asignacion = NOW() WHERE id_slot = :id"
            )->execute(['pid' => $otraPersonaId, 'id' => $miSlotId]);

            $this->db->prepare(
                "UPDATE horario_slot SET postulante_id = :pid, fecha_asignacion = NOW() WHERE id_slot = :id"
            )->execute(['pid' => $postulanteId, 'id' => $otroSlotId]);

            // Registrar ambos movimientos
            $this->db->prepare(
                "INSERT INTO solicitud_cambio (slot_id, semana_id, tipo, postulante_solicitante_id, postulante_original_id)
                 VALUES (:sid, :mid, 'INTERCAMBIO', :sol, :ori)"
            )->execute(['sid'=>$otroSlotId,'mid'=>$slotOtro['semana_id'],'sol'=>$postulanteId,'ori'=>$otraPersonaId]);

            $this->db->prepare(
                "INSERT INTO solicitud_cambio (slot_id, semana_id, tipo, postulante_solicitante_id, postulante_original_id)
                 VALUES (:sid, :mid, 'INTERCAMBIO', :sol, :ori)"
            )->execute(['sid'=>$miSlotId,'mid'=>$slotMio['semana_id'],'sol'=>$postulanteId,'ori'=>$postulanteId]);

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }

        return 'ok';
    }

    /** Revierte ambos slots de un intercambio (usado por staff y admin) */
    private function revertirIntercambio(array $sol, int $revertidorId): string
    {
        $slotPropio = $this->getSlotById((int)$sol['slot_id']);
        if (!$slotPropio) return 'Slot no encontrado';

        $hoy = new DateTime('now', new DateTimeZone('America/Lima'));
        if ((new DateTime($slotPropio['fecha_dia'])) < $hoy->setTime(0, 0)) {
            return 'No se pueden revertir asignaciones de días pasados';
        }

        // El partner siempre lo busco por el iniciador original (ambos registros lo tienen como solicitante)
        $iniciadorId = (int)$sol['postulante_solicitante_id'];
        $stmtPar = $this->db->prepare(
            "SELECT sc2.*
             FROM solicitud_cambio sc2
             INNER JOIN horario_slot hs2 ON hs2.id_slot = sc2.slot_id
             INNER JOIN horario_slot hs1 ON hs1.id_slot = :sid1
             WHERE sc2.id_solicitud            != :id
               AND sc2.tipo                     = 'INTERCAMBIO'
               AND sc2.estado                   = 'ACTIVA'
               AND sc2.postulante_solicitante_id = :pid
               AND hs2.fecha_dia                 = hs1.fecha_dia
               AND hs2.turno_id                  = hs1.turno_id
             LIMIT 1"
        );
        $stmtPar->execute(['sid1'=>$sol['slot_id'], 'id'=>$sol['id_solicitud'], 'pid'=>$iniciadorId]);
        $par = $stmtPar->fetch();
        if (!$par) return 'No se encontró el par del intercambio para revertir';

        // Restaurar ambos slots a sus ocupantes originales
        $this->db->prepare(
            "UPDATE horario_slot SET postulante_id = :pid, fecha_asignacion = NOW() WHERE id_slot = :sid"
        )->execute(['pid' => $sol['postulante_original_id'], 'sid' => $sol['slot_id']]);

        $this->db->prepare(
            "UPDATE horario_slot SET postulante_id = :pid, fecha_asignacion = NOW() WHERE id_slot = :sid"
        )->execute(['pid' => $par['postulante_original_id'], 'sid' => $par['slot_id']]);

        // Marcar ambas solicitudes como REVERTIDAS (revertida_por = quien está revirtiendo)
        $this->db->prepare(
            "UPDATE solicitud_cambio
             SET estado = 'REVERTIDA', revertida_por = :quien, fecha_reversion = NOW()
             WHERE id_solicitud IN (:id1, :id2)"
        )->execute(['quien'=>$revertidorId, 'id1'=>$sol['id_solicitud'], 'id2'=>$par['id_solicitud']]);

        return 'ok';
    }

    /** El propio solicitante revierte su asignación en modo cubrir (COBERTURA o CAMBIO) */
    public function revertirCoberturaPropia(int $solicitudId, int $solicitanteId): string
    {
        // Para INTERCAMBIO: tanto el iniciador como el otro participante pueden revertir
        // Para COBERTURA/CAMBIO: solo el solicitante original
        $stmt = $this->db->prepare(
            "SELECT * FROM solicitud_cambio
             WHERE id_solicitud = :id
               AND tipo        IN ('COBERTURA', 'CAMBIO', 'INTERCAMBIO')
               AND estado       = 'ACTIVA'
               AND (postulante_solicitante_id = :pid
                    OR (tipo = 'INTERCAMBIO' AND postulante_original_id = :pid2))"
        );
        $stmt->execute(['id' => $solicitudId, 'pid' => $solicitanteId, 'pid2' => $solicitanteId]);
        $sol = $stmt->fetch();
        if (!$sol) return 'Solicitud no encontrada o no tienes permiso para revertirla';

        // Intercambio: revertir ambos slots simultáneamente
        if ($sol['tipo'] === 'INTERCAMBIO') {
            return $this->revertirIntercambio($sol, $solicitanteId);
        }

        $slot = $this->getSlotById((int)$sol['slot_id']);
        if (!$slot) return 'Slot no encontrado';

        $hoy      = new DateTime('now', new DateTimeZone('America/Lima'));
        $fechaDia = new DateTime($slot['fecha_dia']);
        if ($fechaDia < $hoy->setTime(0, 0)) {
            return 'No se pueden revertir asignaciones de días pasados';
        }

        // COBERTURA → restaurar persona original; CAMBIO (slot libre) → dejar vacío
        if ($sol['postulante_original_id']) {
            $this->db->prepare(
                "UPDATE horario_slot
                 SET postulante_id = :pid, fecha_asignacion = NOW()
                 WHERE id_slot = :sid"
            )->execute(['pid' => $sol['postulante_original_id'], 'sid' => $sol['slot_id']]);
        } else {
            $this->db->prepare(
                "UPDATE horario_slot
                 SET postulante_id = NULL, fecha_asignacion = NULL
                 WHERE id_slot = :sid"
            )->execute(['sid' => $sol['slot_id']]);
        }

        $this->db->prepare(
            "UPDATE solicitud_cambio
             SET estado = 'REVERTIDA', revertida_por = :quien, fecha_reversion = NOW()
             WHERE id_solicitud = :id"
        )->execute(['quien' => $solicitanteId, 'id' => $solicitudId]);

        return 'ok';
    }

    /** Admin libera cualquier slot (requiere contraseña) */
    public function liberarSlotAdmin(int $slotId, int $adminId, string $password): string
    {
        $stmt = $this->db->prepare("SELECT password FROM usuario WHERE postulante_id = :pid LIMIT 1");
        $stmt->execute(['pid' => $adminId]);
        $hash = $stmt->fetchColumn();
        if (!$hash || !password_verify($password, $hash)) return 'Contraseña incorrecta';

        $slot = $this->getSlotById($slotId);
        if (!$slot) return 'Slot no encontrado';

        // Bloquear edición directa del espejo
        if ($this->esEspejo((int)$slot['semana_id'])) {
            return 'La semana espejo se actualiza automáticamente; no puede editarse directamente';
        }

        if (!$slot['postulante_id']) return 'El slot ya está libre';

        // No permitir quitar a alguien que ya fue encuestado
        $chkSurvey = $this->db->prepare(
            "SELECT id_asistencia FROM asistencia
             WHERE postulante_id = :pid AND fecha = :fecha AND turno_id = :tid LIMIT 1"
        );
        $chkSurvey->execute([
            'pid'   => $slot['postulante_id'],
            'fecha' => $slot['fecha_dia'],
            'tid'   => $slot['turno_id'],
        ]);
        if ($chkSurvey->fetch()) {
            return 'Este trabajador ya fue encuestado para este turno. No se puede eliminar del horario.';
        }

        $removidoId = $slot['postulante_id'];

        $this->db->prepare(
            "UPDATE horario_slot SET postulante_id = NULL, fecha_asignacion = NULL WHERE id_slot = :id"
        )->execute(['id' => $slotId]);

        // Registrar el evento
        $this->db->prepare(
            "INSERT INTO solicitud_cambio (slot_id, semana_id, tipo, postulante_solicitante_id, postulante_original_id, notas)
             VALUES (:sid, :mid, 'CAMBIO', :admin, :removido, 'Eliminado del horario por administrador')"
        )->execute([
            'sid'     => $slotId,
            'mid'     => $slot['semana_id'],
            'admin'   => $adminId,
            'removido'=> $removidoId,
        ]);

        // Propagar al espejo
        $this->propagarEspejo($slotId, null);

        if ($slot['rol_puesto'] !== 'LIMPIEZA') {
            $this->limpiezaCascade($removidoId, (int)$slot['semana_id'], (int)$slot['local_id'], (int)$slot['turno_id'], $slot['fecha_dia']);
        }

        return 'ok';
    }

    /** Revierte una cobertura: restaura el slot al trabajador original */
    public function revertirCobertura(int $solicitudId, int $adminId, string $password): string
    {
        // Verificar contraseña admin
        $stmt = $this->db->prepare("SELECT password FROM usuario WHERE postulante_id = :pid LIMIT 1");
        $stmt->execute(['pid' => $adminId]);
        $hash = $stmt->fetchColumn();
        if (!$hash || !password_verify($password, $hash)) return 'Contraseña incorrecta';

        // Obtener la solicitud (COBERTURA o INTERCAMBIO)
        $stmt = $this->db->prepare("SELECT * FROM solicitud_cambio WHERE id_solicitud = :id AND tipo IN ('COBERTURA','INTERCAMBIO') AND estado = 'ACTIVA'");
        $stmt->execute(['id' => $solicitudId]);
        $sol = $stmt->fetch();
        if (!$sol) return 'Solicitud no encontrada o ya revertida';

        // Intercambio: revertir ambos slots
        if ($sol['tipo'] === 'INTERCAMBIO') {
            return $this->revertirIntercambio($sol, $adminId);
        }

        // Restaurar slot al trabajador original (puede ser NULL si era libre)
        $this->db->prepare(
            "UPDATE horario_slot SET postulante_id = :pid, fecha_asignacion = NOW() WHERE id_slot = :sid"
        )->execute(['pid' => $sol['postulante_original_id'], 'sid' => $sol['slot_id']]);

        // Marcar como revertida
        $this->db->prepare(
            "UPDATE solicitud_cambio
             SET estado = 'REVERTIDA', revertida_por = :admin, fecha_reversion = NOW()
             WHERE id_solicitud = :id"
        )->execute(['admin' => $adminId, 'id' => $solicitudId]);

        return 'ok';
    }

    /** Conceptos de penalidad/beneficio/tarifa activos */
    public function getConceptosPenalidad(): array
    {
        return $this->db->query(
            "SELECT * FROM concepto_penalidad WHERE activo = 1 ORDER BY tipo, monto DESC"
        )->fetchAll();
    }

    /** Historial de solicitudes recientes */
    public function getSolicitudesRecientes(int $limit = 50): array
    {
        $stmt = $this->db->query(
            "SELECT sc.*, sc.estado AS sol_estado,
                    rh.codigo AS rol_puesto,
                    hs.turno_id, hs.fecha_dia,
                    l.descripcion AS local_desc,
                    t.descripcion AS turno_desc,
                    ps.nombres AS solicitante_nombre,
                    po.nombres AS original_nombre
             FROM solicitud_cambio sc
             INNER JOIN horario_slot hs ON sc.slot_id  = hs.id_slot
             INNER JOIN rol_horario rh  ON hs.rol_horario_id = rh.id_rol_horario
             INNER JOIN local l ON hs.local_id = l.id_local
             INNER JOIN turno t ON hs.turno_id  = t.id_turno
             INNER JOIN postulante ps ON sc.postulante_solicitante_id = ps.id_postulante
             LEFT  JOIN postulante po ON sc.postulante_original_id    = po.id_postulante
             ORDER BY sc.fecha_solicitud DESC
             LIMIT {$limit}"
        );
        return $stmt->fetchAll();
    }

    /** Locales y turnos donde la cajera tiene asignación hoy */
    public function getHorarioCajeraHoy(int $postulanteId): array
    {
        $stmt = $this->db->prepare(
            "SELECT hs.local_id, hs.turno_id, l.descripcion AS local_desc
             FROM horario_slot hs
             INNER JOIN rol_horario rh ON hs.rol_horario_id = rh.id_rol_horario
             INNER JOIN local l        ON hs.local_id       = l.id_local
             WHERE hs.fecha_dia     = CURDATE()
               AND hs.postulante_id = :pid
               AND rh.codigo        = 'CAJERA'
             ORDER BY hs.local_id, hs.turno_id"
        );
        $stmt->execute(['pid' => $postulanteId]);
        return $stmt->fetchAll();
    }

    /** Personal asignado en horario para un local+turno en el día de hoy */
    public function getStaffPorTurnoHoy(int $localId, int $turnoId): array
    {
        $stmt = $this->db->prepare(
            "SELECT hs.postulante_id, p.nombres AS nombre,
                    rh.codigo AS rol, rh.descripcion AS rol_desc
             FROM horario_slot hs
             INNER JOIN rol_horario rh ON hs.rol_horario_id = rh.id_rol_horario
             INNER JOIN postulante p   ON hs.postulante_id  = p.id_postulante
             WHERE hs.local_id       = :lid
               AND hs.turno_id       = :tid
               AND hs.fecha_dia      = CURDATE()
               AND hs.postulante_id  IS NOT NULL
               AND rh.codigo         = 'VENDEDORA'
             ORDER BY rh.orden, p.nombres"
        );
        $stmt->execute(['lid' => $localId, 'tid' => $turnoId]);
        return $stmt->fetchAll();
    }

    /** Estadísticas del mes: resumen personal + top 10 por rol.
     *  Umbrales de confiabilidad: ventas >= S/500, operaciones >= 100.
     *  Valores por debajo o NULL se excluyen de los cálculos. */
    public function getEstadisticasMes(int $postulanteId, string $fechaDesde, string $fechaHasta): array
    {
        // ── Resumen personal ──────────────────────────────────
        $stmt = $this->db->prepare(
            "SELECT
                SUM(CASE WHEN rol_codigo='VENDEDORA' AND ventas_monto    IS NOT NULL THEN 1 ELSE 0 END) AS turnos_vend,
                SUM(CASE WHEN rol_codigo='CAJERA'    AND operaciones_bcp IS NOT NULL THEN 1 ELSE 0 END) AS turnos_caj,
                ROUND(AVG(CASE WHEN rol_codigo='VENDEDORA' THEN ventas_monto    END), 2) AS prom_ventas,
                ROUND(SUM(CASE WHEN rol_codigo='VENDEDORA' THEN ventas_monto    END), 2) AS total_ventas,
                ROUND(MAX(CASE WHEN rol_codigo='VENDEDORA' THEN ventas_monto    END), 2) AS max_ventas,
                ROUND(MIN(CASE WHEN rol_codigo='VENDEDORA' THEN ventas_monto    END), 2) AS min_ventas,
                ROUND(AVG(CASE WHEN rol_codigo='CAJERA'    THEN operaciones_bcp END), 1) AS prom_ops,
                ROUND(SUM(CASE WHEN rol_codigo='CAJERA'    THEN operaciones_bcp END), 0) AS total_ops,
                ROUND(MAX(CASE WHEN rol_codigo='CAJERA'    THEN operaciones_bcp END), 0) AS max_ops,
                ROUND(MIN(CASE WHEN rol_codigo='CAJERA'    THEN operaciones_bcp END), 0) AS min_ops,
                COUNT(DISTINCT local_id)                                                   AS locales_distintos,
                COUNT(DISTINCT fecha)                                                       AS dias_trabajados
             FROM horario_rendimiento
             WHERE postulante_id = :pid AND fecha BETWEEN :desde AND :hasta"
        );
        $stmt->execute(['pid'=>$postulanteId,'desde'=>$fechaDesde,'hasta'=>$fechaHasta]);
        $personal = $stmt->fetch();

        // ── Top 5 vendedoras (promedio y total sobre turnos con ventas) ──
        $stmtV = $this->db->prepare(
            "SELECT p.nombres,
                    COUNT(*)                       AS turnos_con_datos,
                    ROUND(AVG(hr.ventas_monto), 2) AS prom_ventas,
                    ROUND(SUM(hr.ventas_monto), 2) AS total_ventas
             FROM horario_rendimiento hr
             INNER JOIN postulante p ON p.id_postulante = hr.postulante_id
             WHERE hr.rol_codigo   = 'VENDEDORA'
               AND hr.ventas_monto IS NOT NULL
               AND hr.fecha BETWEEN :desde AND :hasta
             GROUP BY hr.postulante_id, p.nombres
             HAVING COUNT(*) >= 10
             ORDER BY prom_ventas DESC
             LIMIT 10"
        );
        $stmtV->execute(['desde'=>$fechaDesde,'hasta'=>$fechaHasta]);
        $topVend = $stmtV->fetchAll();

        // ── Top 10 cajeras ──
        $stmtC = $this->db->prepare(
            "SELECT p.nombres,
                    COUNT(*)                            AS turnos_con_datos,
                    ROUND(AVG(hr.operaciones_bcp), 1)  AS prom_ops,
                    SUM(hr.operaciones_bcp)             AS total_ops
             FROM horario_rendimiento hr
             INNER JOIN postulante p ON p.id_postulante = hr.postulante_id
             WHERE hr.rol_codigo       = 'CAJERA'
               AND hr.operaciones_bcp  IS NOT NULL
               AND hr.fecha BETWEEN :desde AND :hasta
             GROUP BY hr.postulante_id, p.nombres
             HAVING COUNT(*) >= 10
             ORDER BY prom_ops DESC
             LIMIT 10"
        );
        $stmtC->execute(['desde'=>$fechaDesde,'hasta'=>$fechaHasta]);
        $topCaj = $stmtC->fetchAll();

        // ── Puesto del usuario en su rol principal ────────────
        $puestoPropio = null;
        $rolPropio    = null;

        if (($personal['turnos_vend'] ?? 0) >= 10) {
            $rolPropio = 'VENDEDORA';
            $miProm    = $personal['prom_ventas'];
            $s = $this->db->prepare(
                "SELECT COUNT(*) + 1
                 FROM (SELECT AVG(ventas_monto) AS p
                       FROM horario_rendimiento
                       WHERE rol_codigo='VENDEDORA' AND ventas_monto IS NOT NULL
                         AND fecha BETWEEN :desde AND :hasta
                       GROUP BY postulante_id
                       HAVING COUNT(*) >= 10 AND p > :miprom) t"
            );
            $s->execute(['desde'=>$fechaDesde,'hasta'=>$fechaHasta,'miprom'=>$miProm]);
            $puestoPropio = (int)$s->fetchColumn();
        } elseif (($personal['turnos_caj'] ?? 0) >= 10) {
            $rolPropio = 'CAJERA';
            $miProm    = $personal['prom_ops'];
            $s = $this->db->prepare(
                "SELECT COUNT(*) + 1
                 FROM (SELECT AVG(operaciones_bcp) AS p
                       FROM horario_rendimiento
                       WHERE rol_codigo='CAJERA' AND operaciones_bcp IS NOT NULL
                         AND fecha BETWEEN :desde AND :hasta
                       GROUP BY postulante_id
                       HAVING COUNT(*) >= 10 AND p > :miprom) t"
            );
            $s->execute(['desde'=>$fechaDesde,'hasta'=>$fechaHasta,'miprom'=>$miProm]);
            $puestoPropio = (int)$s->fetchColumn();
        }

        return compact('personal','topVend','topCaj','puestoPropio','rolPropio');
    }

    /** Reporte de asistencia según horario: slots ocupados de fechas pasadas */
    public function getReporteAsistencia(
        ?int    $filtroPersona = null,
        ?int    $filtroLocal   = null,
        ?string $fechaDesde    = null,
        ?string $fechaHasta    = null
    ): array {
        $where  = ['hs.postulante_id IS NOT NULL'];
        $params = [];

        if ($filtroPersona) {
            $where[]       = 'hs.postulante_id = :pid';
            $params['pid'] = $filtroPersona;
        }
        if ($filtroLocal) {
            $where[]       = 'hs.local_id = :lid';
            $params['lid'] = $filtroLocal;
        }
        if ($fechaDesde) {
            $where[]         = 'hs.fecha_dia >= :desde';
            $params['desde'] = $fechaDesde;
        }
        if ($fechaHasta) {
            $where[]         = 'hs.fecha_dia <= :hasta';
            $params['hasta'] = $fechaHasta;
        } else {
            $where[] = 'hs.fecha_dia < CURDATE()';
        }

        $whereStr = 'WHERE ' . implode(' AND ', $where);

        $stmt = $this->db->prepare(
            "SELECT hs.fecha_dia, hs.local_id, hs.turno_id, hs.slot_num,
                    hs.postulante_id,
                    rh.codigo      AS rol_puesto,
                    rh.descripcion AS rol_desc,
                    rh.orden,
                    l.descripcion  AS local_desc,
                    t.descripcion  AS turno_desc,
                    p.nombres      AS trabajador_nombre,
                    hr.operaciones_bcp,
                    hr.ventas_monto
             FROM horario_slot hs
             INNER JOIN rol_horario rh ON hs.rol_horario_id = rh.id_rol_horario
             INNER JOIN local l        ON hs.local_id       = l.id_local
             INNER JOIN turno t        ON hs.turno_id       = t.id_turno
             INNER JOIN postulante p   ON hs.postulante_id  = p.id_postulante
             LEFT  JOIN horario_rendimiento hr ON hr.horario_slot_id = hs.id_slot
             {$whereStr}
             ORDER BY hs.fecha_dia DESC, hs.local_id, hs.turno_id, rh.orden, hs.slot_num
             LIMIT 600"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Log de coberturas/cambios con filtros opcionales por persona y local */
    public function getLogCoberturas(?int $filtroPersona = null, ?int $filtroLocal = null, int $limit = 200): array
    {
        $where  = [];
        $params = [];

        if ($filtroPersona) {
            $where[]            = 'sc.postulante_solicitante_id = :pid';
            $params['pid']      = $filtroPersona;
        }
        if ($filtroLocal) {
            $where[]            = 'hs.local_id = :lid';
            $params['lid']      = $filtroLocal;
        }

        $whereStr = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $this->db->prepare(
            "SELECT sc.id_solicitud, sc.tipo, sc.estado AS sol_estado,
                    sc.notas, sc.fecha_solicitud, sc.fecha_reversion,
                    rh.codigo      AS rol_puesto,
                    hs.fecha_dia,  hs.turno_id, hs.local_id, hs.slot_num,
                    l.descripcion  AS local_desc,
                    t.descripcion  AS turno_desc,
                    ps.nombres     AS solicitante_nombre,
                    po.nombres     AS original_nombre,
                    pr.nombres     AS revertida_por_nombre
             FROM solicitud_cambio sc
             INNER JOIN horario_slot hs ON sc.slot_id            = hs.id_slot
             INNER JOIN rol_horario rh  ON hs.rol_horario_id     = rh.id_rol_horario
             INNER JOIN local l         ON hs.local_id           = l.id_local
             INNER JOIN turno t         ON hs.turno_id           = t.id_turno
             INNER JOIN postulante ps   ON sc.postulante_solicitante_id = ps.id_postulante
             LEFT  JOIN postulante po   ON sc.postulante_original_id    = po.id_postulante
             LEFT  JOIN postulante pr   ON sc.revertida_por             = pr.id_postulante
             {$whereStr}
             ORDER BY sc.fecha_solicitud DESC
             LIMIT {$limit}"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Anula completamente una solicitud: revierte el slot Y borra el registro (solo admin) */
    public function anularSolicitud(int $solicitudId, int $adminId, string $password): string
    {
        // Verificar contraseña admin
        $stmt = $this->db->prepare("SELECT password FROM usuario WHERE postulante_id = :pid LIMIT 1");
        $stmt->execute(['pid' => $adminId]);
        $hash = $stmt->fetchColumn();
        if (!$hash || !password_verify($password, $hash)) return 'Contraseña incorrecta';

        $stmt = $this->db->prepare(
            "SELECT * FROM solicitud_cambio WHERE id_solicitud = :id AND estado = 'ACTIVA'"
        );
        $stmt->execute(['id' => $solicitudId]);
        $sol = $stmt->fetch();
        if (!$sol) return 'Solicitud no encontrada o ya fue procesada';

        $idsABorrar = [$solicitudId];

        if ($sol['tipo'] === 'INTERCAMBIO') {
            // Restaurar ambos slots y borrar ambos registros
            $stmtPar = $this->db->prepare(
                "SELECT sc2.*
                 FROM solicitud_cambio sc2
                 INNER JOIN horario_slot hs2 ON hs2.id_slot = sc2.slot_id
                 INNER JOIN horario_slot hs1 ON hs1.id_slot = :sid1
                 WHERE sc2.id_solicitud            != :id
                   AND sc2.tipo                     = 'INTERCAMBIO'
                   AND sc2.estado                   = 'ACTIVA'
                   AND sc2.postulante_solicitante_id = :pid
                   AND hs2.fecha_dia                 = hs1.fecha_dia
                   AND hs2.turno_id                  = hs1.turno_id
                 LIMIT 1"
            );
            $stmtPar->execute([
                'sid1' => $sol['slot_id'],
                'id'   => $solicitudId,
                'pid'  => $sol['postulante_solicitante_id'],
            ]);
            $par = $stmtPar->fetch();

            if ($par) {
                $idsABorrar[] = $par['id_solicitud'];
                // Restaurar slot del par
                $this->db->prepare(
                    "UPDATE horario_slot SET postulante_id = :pid, fecha_asignacion = NOW() WHERE id_slot = :sid"
                )->execute(['pid' => $par['postulante_original_id'], 'sid' => $par['slot_id']]);
            }
        }

        // Restaurar slot principal
        if ($sol['postulante_original_id']) {
            $this->db->prepare(
                "UPDATE horario_slot SET postulante_id = :pid, fecha_asignacion = NOW() WHERE id_slot = :sid"
            )->execute(['pid' => $sol['postulante_original_id'], 'sid' => $sol['slot_id']]);
        } else {
            $this->db->prepare(
                "UPDATE horario_slot SET postulante_id = NULL, fecha_asignacion = NULL WHERE id_slot = :sid"
            )->execute(['sid' => $sol['slot_id']]);
        }

        // Borrar los registros de la BD
        $in = implode(',', array_map('intval', $idsABorrar));
        $this->db->exec("DELETE FROM solicitud_cambio WHERE id_solicitud IN ({$in})");

        return 'ok';
    }

    /** Lista de trabajadores activos para el picker de admin */
    public function getTrabajadores(): array
    {
        $stmt = $this->db->query(
            "SELECT p.id_postulante AS id, p.nombres AS nombre
             FROM postulante p
             INNER JOIN usuario u ON u.postulante_id = p.id_postulante
             WHERE u.activo = 1
             ORDER BY p.nombres ASC"
        );
        return $stmt->fetchAll();
    }

    /** Historial de semanas para navegación */
    public function getSemanasRecientes(int $limit = 8): array
    {
        $stmt = $this->db->query(
            "SELECT * FROM semana ORDER BY fecha_inicio DESC LIMIT {$limit}"
        );
        return $stmt->fetchAll();
    }
}
