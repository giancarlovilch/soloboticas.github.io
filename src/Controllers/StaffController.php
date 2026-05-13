<?php

require_once __DIR__ . '/../Core/Controller.php';
require_once __DIR__ . '/../Services/AsistenciaService.php';
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';

class StaffController extends Controller
{
    private AsistenciaService $service;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->service = new AsistenciaService();
    }

    private function getSessionUserId(): int
    {
        // Acepta JWT Bearer (Python/API) o sesión PHP (browser)
        $auth = '';
        if (function_exists('getallheaders')) {
            $h    = getallheaders();
            $auth = $h['Authorization'] ?? $h['authorization'] ?? '';
        }
        if (empty($auth)) {
            $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        }

        if (str_starts_with($auth, 'Bearer ')) {
            $payload = AuthMiddleware::requireAuth();  // exits on failure
            return (int)($payload['sub'] ?? 0);
        }

        if (!isset($_SESSION['user_id'])) {
            $isApi = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
                  || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
            if ($isApi) {
                $this->error('No autenticado', 401);
                exit;
            }
            header('Location: ' . APP_BASE_PATH . '/login');
            exit;
        }
        return (int)$_SESSION['user_id'];
    }

    /**
     * GET /staff
     * Carga el portal del colaborador (view HTML).
     */
    public function index(): void
    {
        $postulanteId = $this->getSessionUserId();
        $userName     = $_SESSION['user_name'] ?? 'Colaborador';
        $userRol      = $_SESSION['user_rol']  ?? 'STAFF';
        require_once __DIR__ . '/../../views/staff/dashboard.php';
    }

    /** GET /staff/mi-horario — vista mensual (propio o de un compañero) */
    public function miHorario(): void
    {
        $registradorId = $this->getSessionUserId();
        $basePath      = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
        $userName      = $_SESSION['user_name'] ?? 'Colaborador';

        // Si se elige otro trabajador, se muestra su horario
        $postulanteId = isset($_GET['trabajador']) ? (int)$_GET['trabajador'] : $registradorId;
        $esPropioHorario = ($postulanteId === $registradorId);

        $filtroMes = $_GET['mes'] ?? date('Y-m');
        // Validar formato Y-m
        if (!preg_match('/^\d{4}-\d{2}$/', $filtroMes)) {
            $filtroMes = date('Y-m');
        }
        [$anio, $nmes] = explode('-', $filtroMes);
        $desde = "{$anio}-{$nmes}-01";
        $hasta = date('Y-m-t', strtotime($desde));

        require_once __DIR__ . '/../Core/Database.php';
        require_once __DIR__ . '/../Repositories/AsistenciaRepository.php';
        $db        = \Database::getConnection();
        $asistRepo = new AsistenciaRepository();

        // Lista de trabajadores activos para el selector
        $trabajadores = $db->query(
            "SELECT p.id_postulante AS id, p.nombres AS nombre
             FROM postulante p INNER JOIN usuario u ON u.postulante_id = p.id_postulante
             WHERE u.activo = 1 ORDER BY p.nombres ASC"
        )->fetchAll();

        // Nombre del trabajador seleccionado
        $nombreTrabajador = $userName;
        foreach ($trabajadores as $t) {
            if ($t['id'] == $postulanteId) { $nombreTrabajador = $t['nombre']; break; }
        }

        // Checklist items para el modal de registro
        $checklistItems = $db->query(
            "SELECT id_checklist, descripcion, tipo FROM checklist WHERE activo = 1 ORDER BY tipo DESC, id_checklist ASC"
        )->fetchAll();

        // Slots asignados (actuales) + si fue cobertura
        $stmtSlots = $db->prepare(
            "SELECT hs.id_slot, hs.fecha_dia, hs.turno_id,
                    l.descripcion AS local_desc,
                    t.descripcion AS turno_desc,
                    rh.descripcion AS rol_desc,
                    sc_cubre.id_solicitud AS cubre_id,
                    po.nombres AS cubrió_a
             FROM horario_slot hs
             INNER JOIN local l         ON hs.local_id       = l.id_local
             INNER JOIN turno t         ON hs.turno_id       = t.id_turno
             INNER JOIN rol_horario rh  ON hs.rol_horario_id = rh.id_rol_horario
             LEFT JOIN solicitud_cambio sc_cubre
                 ON sc_cubre.slot_id = hs.id_slot
                 AND sc_cubre.postulante_solicitante_id = :pid1
                 AND sc_cubre.tipo = 'COBERTURA' AND sc_cubre.estado = 'ACTIVA'
             LEFT JOIN postulante po ON sc_cubre.postulante_original_id = po.id_postulante
             WHERE hs.postulante_id = :pid2
               AND hs.fecha_dia BETWEEN :desde AND :hasta
             ORDER BY hs.fecha_dia ASC, hs.turno_id ASC"
        );
        $stmtSlots->execute(['pid1' => $postulanteId, 'pid2' => $postulanteId, 'desde' => $desde, 'hasta' => $hasta]);
        $slots = $stmtSlots->fetchAll();

        // Slots donde fue reemplazado
        $stmtRemp = $db->prepare(
            "SELECT hs.fecha_dia, hs.turno_id, l.descripcion AS local_desc,
                    rh.descripcion AS rol_desc, ps.nombres AS reemplazado_por, sc.notas
             FROM solicitud_cambio sc
             INNER JOIN horario_slot hs ON sc.slot_id = hs.id_slot
             INNER JOIN local l  ON hs.local_id = l.id_local
             INNER JOIN turno t  ON hs.turno_id  = t.id_turno
             INNER JOIN rol_horario rh ON hs.rol_horario_id = rh.id_rol_horario
             INNER JOIN postulante ps ON sc.postulante_solicitante_id = ps.id_postulante
             WHERE sc.postulante_original_id = :pid AND sc.tipo = 'COBERTURA' AND sc.estado = 'ACTIVA'
               AND hs.fecha_dia BETWEEN :desde AND :hasta
             ORDER BY hs.fecha_dia ASC, hs.turno_id ASC"
        );
        $stmtRemp->execute(['pid' => $postulanteId, 'desde' => $desde, 'hasta' => $hasta]);
        $reemplazos = $stmtRemp->fetchAll();

        // Asistencias del mes
        $asistencias = $asistRepo->getByPostulanteRango($postulanteId, $desde, $hasta);

        // Checklists de todas las asistencias a la vez
        $asistIds = array_column($asistencias, 'id_asistencia');
        $checklists = $asistRepo->getChecklistPorIds($asistIds);

        // Indexar asistencias por fecha (puede haber varias por día: mañana y tarde)
        $asistPorFecha = [];
        foreach ($asistencias as $a) {
            $asistPorFecha[$a['fecha']][] = $a;
        }

        // Indexar reemplazos por fecha
        $rempPorFecha = [];
        foreach ($reemplazos as $r) {
            $rempPorFecha[$r['fecha_dia']][$r['turno_id']] = $r;
        }

        require_once __DIR__ . '/../../views/staff/mi_horario.php';
    }

    /** POST /staff/api/asistencia/registrar — un compañero registra la asistencia */
    public function registrarAsistencia(): void
    {
        $registradorId = $this->getSessionUserId();
        $data = $this->getAllInput();

        $postulanteId = (int)($data['postulante_id'] ?? 0);
        $fecha        = $data['fecha']        ?? '';
        $horaIngreso  = $data['hora_ingreso'] ?? '';
        $horaSalida   = $data['hora_salida']  ?? null;
        $localId      = isset($data['local_id']) ? (int)$data['local_id'] : null;
        $checklist    = $data['checklist']    ?? [];
        $password     = trim($data['password'] ?? '');

        if (!$postulanteId || !$fecha || !$password) {
            $this->error('Faltan datos requeridos', 422);
        }

        require_once __DIR__ . '/../Repositories/AsistenciaRepository.php';
        $repo   = new AsistenciaRepository();
        $result = $repo->registrarParaCompanhero(
            $postulanteId, $registradorId, $fecha,
            $horaIngreso, $horaSalida ?: null, $localId,
            $checklist, $password
        );

        if ($result === true) $this->success('Asistencia registrada correctamente.');
        else $this->error($result, 401);
    }

    /** POST /staff/api/asistencia/{id}/revertir — elimina registro de FALTA */
    public function revertirFalta(int $id): void
    {
        $registradorId = $this->getSessionUserId();
        $password = trim($this->getAllInput()['password'] ?? '');
        if (empty($password)) $this->error('La contraseña es requerida', 400);

        require_once __DIR__ . '/../Repositories/AsistenciaRepository.php';
        $result = (new AsistenciaRepository())->revertirFalta($id, $registradorId, $password);

        if ($result === true) $this->success('Falta revertida. El registro fue eliminado.');
        else $this->error($result, 401);
    }

    /** POST /staff/api/asistencia/{id}/editar */
    public function editarAsistencia(int $id): void
    {
        $postulanteId = $this->getSessionUserId();
        $data    = $this->getAllInput();
        $ingreso = trim($data['hora_ingreso'] ?? '');
        $salida  = trim($data['hora_salida']  ?? '') ?: null;
        $fecha   = trim($data['fecha']        ?? '');

        if (empty($ingreso) || empty($fecha)) $this->error('Hora de entrada y fecha son requeridas', 422);

        require_once __DIR__ . '/../Repositories/AsistenciaRepository.php';
        $repo = new AsistenciaRepository();
        $ok   = $repo->actualizarTiemposPropio($id, $postulanteId, "{$fecha} {$ingreso}:00", $salida ? "{$fecha} {$salida}:00" : null);

        if ($ok) $this->success('Horario actualizado.');
        else $this->error('No se pudo actualizar. Verifica que el registro sea tuyo.', 403);
    }

    /** GET /staff/info — página de información interna */
    public function info(): void
    {
        $this->getSessionUserId(); // requiere sesión
        $basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
        require_once __DIR__ . '/../../views/staff/info.php';
    }

    /**
     * GET /staff/api/historial
     * JSON: historial de asistencia propio.
     */
    public function historial(): void
    {
        $postulanteId = $this->getSessionUserId();
        $result = $this->service->getHistorialPropio($postulanteId);
        $this->success($result['message'], $result['data']);
    }

    /**
     * POST /staff/asistencia/marcar
     * Body JSON: { tipo:"ENTRADA"|"SALIDA", local_id:2, password:"xxx", checklist:[...] }
     */
    public function marcar(): void
    {
        $postulanteId = $this->getSessionUserId();
        $data         = $this->getAllInput();

        $tipo      = strtoupper(trim($data['tipo']     ?? 'ENTRADA'));
        $localId   = isset($data['local_id']) ? (int)$data['local_id'] : null;
        $password  = $data['password']  ?? '';
        $checklist = $data['checklist'] ?? [];

        if (empty($password)) {
            $this->error('La contraseña es obligatoria para confirmar la asistencia.', 422);
        }

        $result = $this->service->marcarAsistencia($postulanteId, $tipo, $localId, $password, $checklist);

        if (!$result['success']) {
            $this->error($result['message'], $result['status'] ?? 400);
        }

        // Incluir sesiones_hoy dentro del data para que llegue al frontend
        $this->success($result['message'], [
            'sesion'       => $result['data'],
            'sesiones_hoy' => $result['sesiones_hoy'] ?? 0,
        ]);
    }

    /**
     * GET /staff/api/checklist?tipo=APERTURA|CIERRE
     * Devuelve los ítems del checklist para el tipo dado.
     */
    public function getChecklist(): void
    {
        $this->getSessionUserId(); // solo requiere estar autenticado
        $tipo  = strtoupper(trim($_GET['tipo'] ?? 'APERTURA'));
        if (!in_array($tipo, ['APERTURA', 'CIERRE'], true)) {
            $this->error("tipo debe ser APERTURA o CIERRE", 422);
        }

        require_once __DIR__ . '/../Repositories/AsistenciaRepository.php';
        $repo  = new AsistenciaRepository();
        $items = $repo->getChecklistByTipo($tipo);
        $this->success('OK', $items);
    }
}
