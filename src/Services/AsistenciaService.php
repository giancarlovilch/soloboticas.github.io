<?php

require_once __DIR__ . '/../Repositories/AsistenciaRepository.php';
require_once __DIR__ . '/../Repositories/AuthRepository.php';

class AsistenciaService
{
    private AsistenciaRepository $repo;

    public function __construct()
    {
        $this->repo = new AsistenciaRepository();
    }

    /**
     * Marca ENTRADA o SALIDA con verificación de contraseña y guardado del checklist.
     *
     * @param int    $postulanteId
     * @param string $tipo        'ENTRADA' | 'SALIDA'
     * @param int|null $localId   Solo relevante en ENTRADA
     * @param string $password    Contraseña del colaborador
     * @param array  $checklist   [{checklist_id, cumplido, observacion?}]
     */
    public function marcarAsistencia(
        int    $postulanteId,
        string $tipo      = 'ENTRADA',
        ?int   $localId   = null,
        string $password  = '',
        array  $checklist = []
    ): array {
        // 1. Verificar contraseña
        $authRepo = new AuthRepository();
        $user     = $authRepo->findByPostulanteId($postulanteId);

        if (!$user || !password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Contraseña incorrecta.', 'status' => 401];
        }

        $hoy  = $this->repo->getTodayByPostulante($postulanteId);
        $hora = (int)(new DateTime('now', new DateTimeZone('America/Lima')))->format('H');

        if ($tipo === 'ENTRADA') {
            if ($hora < 6 || $hora >= 22) {
                return [
                    'success' => false,
                    'message' => 'Fuera de horario. Solo puedes marcar entrada entre las 6:00 AM y las 10:00 PM.',
                    'status'  => 422,
                ];
            }
            if ($hoy) {
                return ['success' => false, 'message' => 'Ya tienes una sesión abierta. Marca tu salida primero.', 'status' => 409];
            }
            $registro = $this->repo->marcarIngreso($postulanteId, $localId);
            if (!empty($checklist) && isset($registro['id_asistencia'])) {
                $this->repo->guardarChecklist((int)$registro['id_asistencia'], $checklist);
            }
            $sesionesHoy = $this->repo->countTodayByPostulante($postulanteId);
            return [
                'success'      => true,
                'tipo'         => 'entrada',
                'message'      => 'Entrada registrada correctamente',
                'data'         => $registro,
                'sesiones_hoy' => $sesionesHoy,
            ];
        }

        if ($tipo === 'SALIDA') {
            if (!$hoy) {
                return ['success' => false, 'message' => 'Primero debes registrar tu entrada.', 'status' => 409];
            }
            $this->repo->marcarSalida($postulanteId);
            $sesionesHoy = $this->repo->countTodayByPostulante($postulanteId);
            // Tras la salida ya no hay sesión abierta; devolvemos null para que el JS habilite entrada
            return [
                'success'      => true,
                'tipo'         => 'salida',
                'message'      => 'Salida registrada correctamente',
                'data'         => null,
                'sesiones_hoy' => $sesionesHoy,
            ];
        }

        return ['success' => false, 'message' => "Tipo inválido: usa 'ENTRADA' o 'SALIDA'.", 'status' => 422];
    }

    // ── STAFF: historial propio ───────────────────────────
    public function getHistorialPropio(int $postulanteId): array
    {
        $hoy          = $this->repo->getTodayByPostulante($postulanteId);
        $sesionesHoy  = $this->repo->countTodayByPostulante($postulanteId);
        $historial    = $this->repo->getByPostulante($postulanteId, 20);
        $locales      = $this->repo->getLocales();

        return [
            'success' => true,
            'message' => 'OK',
            'data'    => [
                'hoy'         => $hoy,           // sesión abierta (null si todo cerrado)
                'sesiones_hoy' => $sesionesHoy,  // total de sesiones hoy (para mostrar)
                'historial'   => $historial,
                'locales'     => $locales,
            ],
        ];
    }

    // ── ADMIN: listar slots del pasado con su ficha ───────
    public function adminListar(string $desde = '', string $hasta = '', int $postulanteId = 0, bool $soloSinCalificar = false): array
    {
        $registros = $this->repo->getAllSlots($desde, $hasta, $postulanteId, $soloSinCalificar);
        $usuarios  = $this->repo->getUsuariosActivos();
        $locales   = $this->repo->getLocales();

        return [
            'success' => true,
            'message' => 'OK',
            'data'    => compact('registros', 'usuarios', 'locales'),
        ];
    }

    // ── ADMIN: crear o actualizar ficha (upsert) ──────────
    public function adminActualizar(int $id, array $data): array
    {
        if ($id > 0) {
            $this->repo->actualizar($id, $data);
        } else {
            $pid   = (int)($data['postulante_id'] ?? 0);
            $fecha = $data['fecha'] ?? '';
            $tid   = (int)($data['turno_id'] ?? 0);
            if (!$pid || !$fecha || !$tid) return ['success' => false, 'message' => 'Faltan datos del slot'];
            $this->repo->upsertParaAdmin($pid, $fecha, $tid, $data);
        }
        return ['success' => true, 'message' => 'Ficha guardada'];
    }

    // ── ADMIN: crear registro manual ──────────────────────
    public function adminCrear(int $postulanteId, string $fecha, array $data): array
    {
        if (!$postulanteId || !$fecha) {
            return ['success' => false, 'message' => 'Faltan datos requeridos'];
        }
        $this->repo->crear($postulanteId, $fecha, $data);
        return ['success' => true, 'message' => 'Registro creado correctamente'];
    }
}
