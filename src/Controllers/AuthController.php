<?php

require_once __DIR__ . '/../Core/Controller.php';
require_once __DIR__ . '/../Services/AuthService.php';
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';

class AuthController extends Controller
{
    private AuthService $service;

    public function __construct()
    {
        $this->service = new AuthService();
    }

    /**
     * POST /login
     * Autentica al usuario. Devuelve JWT + establece sesión para la web.
     * El servidor Python usa solo el JWT del body; el browser usa la cookie de sesión.
     */
    public function login(): void
    {
        $data   = $this->getAllInput();
        $result = $this->service->login($data);

        if (!$result['success']) {
            $this->error($result['message'], $result['status']);
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['user_id']   = $result['data']['postulante_id'];
        $_SESSION['user_name'] = $result['data']['username'];
        $_SESSION['user_rol']  = $result['data']['rol'];

        $this->success($result['message'], $result['data']);
    }

    /**
     * GET /api/auth/verify
     * Verifica un Bearer token. Diseñado para ser consumido por el servidor Python.
     *
     * Ejemplo de uso desde Python:
     *   headers = {"Authorization": "Bearer <token>"}
     *   r = requests.get("http://servidor/api/auth/verify", headers=headers)
     */
    public function verifyToken(): void
    {
        $payload = AuthMiddleware::requireAuth();

        $this->success('Token válido', [
            'postulante_id'  => $payload['sub']       ?? null,
            'username'       => $payload['username']  ?? null,
            'rol'            => $payload['rol']        ?? null,
            'nombre_completo' => $payload['full_name'] ?? null,
            'expira_en'      => $payload['exp']        ?? null,
        ]);
    }

    /**
     * POST /logout
     * Destruye la sesión del navegador. Para Python no es necesario (JWT es stateless).
     */
    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_destroy();

        $isJson = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')
               || !empty($_SERVER['HTTP_X_REQUESTED_WITH']);

        if ($isJson) {
            $this->success('Sesión cerrada');
        } else {
            header('Location: ' . APP_BASE_PATH . '/login');
            exit;
        }
    }
}
