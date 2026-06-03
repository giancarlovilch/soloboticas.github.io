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
                    IF(sc.tipo = 'COBERTURA', 1, 0) AS es_cobertura
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

        if ($slot['rol_puesto'] === 'LIMPIEZA') {
            // Limpieza: solo requiere estar en ese local+turno+día en un rol no-limpieza
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
                'sid'  => $slot['semana_id'],
                'pid'  => $solicitanteId,
                'lid'  => $slot['local_id'],
                'tid'  => $slot['turno_id'],
                'fdia' => $slot['fecha_dia'],
            ]);
            if ((int)$trabajaLocal->fetchColumn() === 0) {
                return 'Solo puedes tomar limpieza en el local y turno donde trabajas ese día';
            }
        } else {
            // Rol normal: máximo 1 slot por turno+día
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
                'sid'        => $slot['semana_id'],
                'pid'        => $solicitanteId,
                'tid'        => $slot['turno_id'],
                'fdia'       => $slot['fecha_dia'],
                'sid_actual' => $slotId,
            ]);
            if ($conflicto->fetch()) {
                return 'Ya tienes un turno asignado en ese horario (mismo turno y día)';
            }
        }

        // Reasignar el slot
        $this->db->prepare(
            "UPDATE horario_slot
             SET postulante_id = :pid, fecha_asignacion = NOW()
             WHERE id_slot = :id"
        )->execute(['pid' => $solicitanteId, 'id' => $slotId]);

        // COBERTURA si había alguien, CAMBIO si el slot estaba libre
        $tipo = $originalId ? 'COBERTURA' : 'CAMBIO';
        $this->db->prepare(
            "INSERT INTO solicitud_cambio
                (slot_id, semana_id, tipo, postulante_solicitante_id, postulante_original_id, notas)
             VALUES (:sid, :mid, :tipo, :sol, :ori, :notas)"
        )->execute([
            'sid'   => $slotId,
            'mid'   => $slot['semana_id'],
            'tipo'  => $tipo,
            'sol'   => $solicitanteId,
            'ori'   => $originalId,
            'notas' => $comentario,
        ]);

        return 'ok';
    }

    /** El propio solicitante revierte su asignación en modo cubrir (COBERTURA o CAMBIO) */
    public function revertirCoberturaPropia(int $solicitudId, int $solicitanteId): string
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM solicitud_cambio
             WHERE id_solicitud              = :id
               AND tipo                     IN ('COBERTURA', 'CAMBIO')
               AND estado                   = 'ACTIVA'
               AND postulante_solicitante_id = :pid"
        );
        $stmt->execute(['id' => $solicitudId, 'pid' => $solicitanteId]);
        $sol = $stmt->fetch();
        if (!$sol) return 'Solicitud no encontrada o no tienes permiso para revertirla';

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

        // Obtener la solicitud
        $stmt = $this->db->prepare("SELECT * FROM solicitud_cambio WHERE id_solicitud = :id AND tipo = 'COBERTURA' AND estado = 'ACTIVA'");
        $stmt->execute(['id' => $solicitudId]);
        $sol = $stmt->fetch();
        if (!$sol) return 'Cobertura no encontrada o ya revertida';

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
