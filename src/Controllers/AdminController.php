<?php

require_once __DIR__ . '/../Core/Controller.php';
require_once __DIR__ . '/../Services/AdminService.php';
require_once __DIR__ . '/../Services/AuthService.php';
require_once __DIR__ . '/../Repositories/AdminRepository.php';
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';

class AdminController extends Controller
{
    private AdminService $service;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->service = new AdminService();
    }

    /**
     * Acepta ADMIN tanto por sesión PHP (browser) como por JWT Bearer (Python/API).
     */
    private function middlewareAdmin(): void
    {
        // Primero intentamos Bearer token (para Python)
        $auth = '';
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            $auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        }
        if (empty($auth)) {
            $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        }

        if (str_starts_with($auth, 'Bearer ')) {
            $payload = AuthMiddleware::requireAdmin();
            return;
        }

        // Fallback: sesión PHP para el browser
        if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] !== 'ADMIN') {
            $isApi = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
                  || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');

            if ($isApi) {
                $this->error('Sesión expirada o privilegios insuficientes', 401);
            }

            header('Location: ' . APP_BASE_PATH . '/login');
            exit;
        }
    }

    /**
     * Carga la vista principal del Dashboard y gestiona la carga de datos para edición[cite: 18]
     */
    /**
     * Carga la vista principal del Dashboard y gestiona la carga de datos para edición
     */
    public function index(): void
    {
        $this->middlewareAdmin();

        $page = $_GET['page'] ?? 'home';
        $p = null; // Mochila de datos del postulante
        $catalogos = []; // Mochila para los selects de la base de datos

        // Si la página es update, cargamos el detalle y los catálogos reales[cite: 14]
        if ($page === 'update' && isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            $repo = new AdminRepository();

            // 1. Obtenemos la información completa del postulante[cite: 14]
            $p = $repo->obtenerDetalleCompleto($id);

            // 2. Cargamos los catálogos reales para los selects dinámicos[cite: 14]
            $catalogos = [
                'generos'    => $repo->obtenerCatalogo('genero'),
                'viviendas'  => $repo->obtenerCatalogo('situacion_vivienda'),
                'puestos'    => $repo->obtenerCatalogo('puesto'),
                'etapas'     => $repo->obtenerCatalogo('etapa'),
                'skills'     => $repo->obtenerCatalogo('skill'),
                'niveles'    => $repo->obtenerCatalogo('nivel'),
                'tipos_est'  => $repo->obtenerCatalogo('tipo_estudio'),
                'estados_est' => $repo->obtenerCatalogo('estado'),
                'instituciones' => $repo->obtenerCatalogo('institucion'),
                'roles'          => $repo->obtenerCatalogo('rol'),
                'tipos_personal' => $repo->getTiposPersonal(),
            ];

            error_log("DEBUG: Cargando detalle y catálogos para ID: $id.");
        }

        require_once __DIR__ . '/../../views/admin/dashboard.php';
    }

    /**
     * Lista todos los postulantes registrados (Respuesta JSON)[cite: 18]
     */
    public function listarPostulantes(): void
    {
        $this->middlewareAdmin();

        try {
            $adminRepo = new AdminRepository();
            $data = $adminRepo->obtenerTodos(); // Ahora incluye fechas y alias
            $this->success("Lista completa de postulantes", $data);
        } catch (Exception $e) {
            $this->error("Error al obtener datos: " . $e->getMessage(), 500);
        }
    }

    /**
     * Cambia el estado del usuario (Activo/Inactivo)[cite: 18]
     */
    public function cambiarEstadoUsuario(): void
    {
        $this->middlewareAdmin();
        $data = $this->getAllInput();

        // Ajustado para recibir 'id' desde el fetch de status.js o detalle[cite: 15]
        $id = $data['id'] ?? null;
        $activo = $data['activo'] ?? null;

        if ($id === null || $activo === null) {
            $this->error("ID y estado son requeridos", 400);
        }

        $adminRepo = new AdminRepository();
        $res = $adminRepo->actualizarEstado((int)$id, (int)$activo);

        if (!$res) {
            $this->error("Error al actualizar estado", 500);
        }

        $mensaje = $activo ? "Acceso activado correctamente" : "Acceso bloqueado correctamente";
        $this->success($mensaje);
    }

    /**
     * Busca postulantes por nombre o DNI[cite: 18]
     */
    public function buscar(): void
    {
        $this->middlewareAdmin();
        $params = $this->getQueryParams();
        $termino = $params['q'] ?? '';

        if (empty($termino)) {
            $this->error("Proporcione un término de búsqueda", 400);
        }

        $adminRepo = new AdminRepository();
        $data = $adminRepo->buscarPostulantes($termino);
        $this->success("Resultados", $data);
    }

    /**
     * Procesa la contratación de un postulante[cite: 18]
     */
    public function contratar(): void
    {
        $this->middlewareAdmin();
        $data = $this->getAllInput();
        $result = $this->service->gestionarContratacion($data);

        if (!$result['success']) {
            $this->error($result['message'], 400);
        }
        $this->success($result['message']);
    }

    /**
     * POST /admin/postulante/eliminar
     * Elimina un postulante previa verificación de la contraseña del admin.
     * Body JSON: { "postulante_id": 5, "password": "clave_admin" }
     */
    public function eliminarPostulante(): void
    {
        $this->middlewareAdmin();
        $data = $this->getAllInput();

        $postulanteId = isset($data['postulante_id']) ? (int)$data['postulante_id'] : 0;
        $password     = $data['password'] ?? '';

        if (!$postulanteId || empty($password)) {
            $this->error('Datos incompletos', 400);
        }

        // Verificar contraseña del admin en sesión
        require_once __DIR__ . '/../Repositories/AuthRepository.php';
        $adminUsername = $_SESSION['user_name'] ?? '';
        $authRepo      = new AuthRepository();
        $adminUser     = $authRepo->findByUsername($adminUsername);

        if (!$adminUser || !password_verify($password, $adminUser['password'])) {
            $this->error('Contraseña incorrecta. Operación cancelada.', 403);
        }

        $db   = \Database::getConnection();
        $stmt = $db->prepare('DELETE FROM postulante WHERE id_postulante = :id');
        $stmt->execute(['id' => $postulanteId]);

        if ($stmt->rowCount() === 0) {
            $this->error('No se encontró el postulante', 404);
        }

        $this->success('Postulante eliminado correctamente');
    }

    public function apiDetallePostulante(): void
    {
        // $this->middlewareAdmin();
        $id = $_GET['id'] ?? null;

        if (!$id) {
            $this->error("ID no proporcionado", 400);
        }

        $repo = new AdminRepository();
        $data = $repo->obtenerDetalleCompleto((int)$id);

        if (empty($data)) {
            $this->error("Postulante no encontrado", 404);
        }

        $this->success("Detalle completo obtenido", $data);
    }

    /**
     * POST /admin/usuario/username
     * El admin cambia el nombre de usuario de cualquier cuenta.
     * Body JSON: { "postulante_id": 5, "nuevo_username": "MARIAFL" }
     */
    public function cambiarUsername(): void
    {
        $this->middlewareAdmin();
        $data = $this->getAllInput();

        $postulanteId   = isset($data['postulante_id']) ? (int)$data['postulante_id'] : 0;
        $nuevoUsername  = trim($data['nuevo_username'] ?? '');

        if (!$postulanteId || empty($nuevoUsername)) {
            $this->error('postulante_id y nuevo_username son requeridos', 400);
        }

        if (strlen($nuevoUsername) < 4) {
            $this->error('El username debe tener al menos 4 caracteres', 422);
        }

        $adminRepo = new AdminRepository();
        $result    = $adminRepo->actualizarUsername($postulanteId, $nuevoUsername);

        if ($result !== true) {
            $this->error($result, 409);
        }

        $this->success('Nombre de usuario actualizado correctamente');
    }

    /**
     * POST /admin/usuario/password
     * El admin cambia la contraseña de cualquier usuario.
     * Body JSON: { "postulante_id": 5, "nueva_password": "nuevaclave" }
     */
    public function cambiarPassword(): void
    {
        $this->middlewareAdmin();
        $data = $this->getAllInput();

        $postulante_id  = isset($data['postulante_id']) ? (int)$data['postulante_id'] : null;
        $nueva_password = $data['nueva_password'] ?? '';

        if (!$postulante_id || empty($nueva_password)) {
            $this->error('postulante_id y nueva_password son requeridos', 400);
        }

        $authService = new AuthService();
        $result = $authService->changePassword($postulante_id, $nueva_password);

        if (!$result['success']) {
            $this->error($result['message'], 400);
        }

        $this->success($result['message']);
    }

    /**
     * Procesa la actualización integral de un postulante
     */
    public function actualizarPostulante(): void
    {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!$data || !isset($data['id_postulante'])) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
            return;
        }

        try {
            $repo = new AdminRepository();
            $res = $repo->actualizarDetalleCompleto((int)$data['id_postulante'], $data);

            echo json_encode([
                'success' => $res,
                'message' => $res ? 'Expediente actualizado' : 'Error interno en el repositorio'
            ]);
        } catch (Exception $e) {
            // Esto te dirá si falta un campo en la DB o si hay un error de FK
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
