<?php

require_once __DIR__ . '/../Core/Controller.php';
require_once __DIR__ . '/../Services/AsistenciaService.php';
require_once __DIR__ . '/../Repositories/AsistenciaRepository.php';
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';

class AsistenciaController extends Controller
{
    private AsistenciaService    $service;
    private AsistenciaRepository $repo;
    private string               $authRole = '';

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->service = new AsistenciaService();
        $this->repo    = new AsistenciaRepository();
    }

    // ── Middleware: cualquier usuario autenticado ─────────
    // Extrae ID y rol ya sea de JWT o de sesión PHP.
    private function requireSession(): int
    {
        $auth = '';
        if (function_exists('getallheaders')) {
            $h    = getallheaders();
            $auth = $h['Authorization'] ?? $h['authorization'] ?? '';
        }
        if (empty($auth)) {
            $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        }

        if (str_starts_with($auth, 'Bearer ')) {
            $payload        = AuthMiddleware::requireAuth();  // exits on failure
            $this->authRole = $payload['rol'] ?? '';
            $id             = (int)($payload['sub'] ?? 0);
            return $id;
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

        $this->authRole = $_SESSION['user_rol'] ?? '';
        return (int)$_SESSION['user_id'];
    }

    private function requireAdmin(): void
    {
        if ($this->authRole !== 'ADMIN') {
            $this->error('Se requieren privilegios de administrador', 403);
            exit;
        }
    }

    // ── STAFF: POST /asistencia/marcar ────────────────────
    public function marcar(): void
    {
        $postulanteId = $this->requireSession();
        $data  = $this->getAllInput();
        $local = isset($data['local_id']) ? (int)$data['local_id'] : null;

        $result = $this->service->marcarAsistencia($postulanteId, $local);

        if (!$result['success']) {
            $this->error($result['message'], 409);
        }
        $this->success($result['message'], $result['data']);
    }

    // ── STAFF: GET /staff/api/historial ───────────────────
    public function historialPropio(): void
    {
        $postulanteId = $this->requireSession();
        $result = $this->service->getHistorialPropio($postulanteId);
        $this->success($result['message'], $result['data']);
    }

    // ── ADMIN: GET /admin/api/asistencia/checklist?asistencia_id=X ──
    public function adminGetChecklist(): void
    {
        $this->requireSession();
        $this->requireAdmin();
        $id = (int)($_GET['asistencia_id'] ?? 0);
        if (!$id) $this->error('asistencia_id requerido', 400);
        $items = $this->repo->getChecklistByAsistencia($id);
        $this->success('OK', $items);
    }

    // ── ADMIN: POST /admin/asistencia/checklist/actualizar ──
    public function adminActualizarChecklist(): void
    {
        $this->requireSession();
        $this->requireAdmin();
        $data    = $this->getAllInput();
        $itemId  = (int)($data['id_asistencia_checklist'] ?? 0);
        $cumplido = isset($data['cumplido']) ? (int)(bool)$data['cumplido'] : 0;
        $obs     = $data['observacion'] ?? null;
        if (!$itemId) $this->error('id_asistencia_checklist requerido', 400);
        $this->repo->actualizarChecklistItem($itemId, $cumplido, $obs ?: null);
        $this->success('Ítem actualizado');
    }

    // ── ADMIN: GET /admin/api/asistencias ─────────────────
    public function adminListar(): void
    {
        $this->requireSession();
        $this->requireAdmin();

        $q     = $this->getQueryParams();
        $desde = $q['desde'] ?? '';
        $hasta = $q['hasta'] ?? '';
        $pid   = isset($q['postulante_id']) ? (int)$q['postulante_id'] : 0;
        $sinCalif = !empty($q['sin_calificar']);

        $result = $this->service->adminListar($desde, $hasta, $pid, $sinCalif);
        $this->success($result['message'], $result['data']);
    }

    // ── ADMIN: POST /admin/asistencia/actualizar ──────────
    public function adminActualizar(): void
    {
        $this->requireSession();
        $this->requireAdmin();

        $data = $this->getAllInput();
        $id   = isset($data['id_asistencia']) ? (int)$data['id_asistencia'] : 0;

        $result = $this->service->adminActualizar($id, $data);

        if (!$result['success']) $this->error($result['message'], 400);
        $this->success($result['message']);
    }

    // ── ADMIN: POST /admin/asistencia/crear ───────────────
    public function adminCrear(): void
    {
        $this->requireSession();
        $this->requireAdmin();

        $data = $this->getAllInput();
        $pid  = (int)($data['postulante_id'] ?? 0);
        $fecha = $data['fecha'] ?? '';

        $result = $this->service->adminCrear($pid, $fecha, $data);

        if (!$result['success']) $this->error($result['message'], 400);
        $this->success($result['message']);
    }

    public function adminEliminar(): void
    {
        $this->requireSession();
        $this->requireAdmin();

        $data          = $this->getAllInput();
        $idAsistencia  = (int)($data['id_asistencia'] ?? 0);
        $password      = trim($data['password'] ?? '');

        if (!$idAsistencia || empty($password)) {
            $this->error('id_asistencia y contraseña requeridos', 400);
        }

        // Verificar contraseña del admin
        $postulanteId = (int)$_SESSION['user_id'];
        $db   = \Database::getConnection();
        $stmt = $db->prepare("SELECT password FROM usuario WHERE postulante_id = :pid LIMIT 1");
        $stmt->execute(['pid' => $postulanteId]);
        $hash = $stmt->fetchColumn();

        if (!$hash || !password_verify($password, $hash)) {
            $this->error('Contraseña incorrecta', 401);
        }

        // Borrar hijos primero (FK constraint)
        $db->prepare("DELETE FROM asistencia_checklist WHERE asistencia_id = :id")
           ->execute(['id' => $idAsistencia]);
        $db->prepare("DELETE FROM asistencia WHERE id_asistencia = :id")
           ->execute(['id' => $idAsistencia]);

        $this->success('Asistencia eliminada correctamente');
    }
}
