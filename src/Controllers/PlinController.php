<?php

require_once __DIR__ . '/../Core/Controller.php';
require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Repositories/PlinRepository.php';

class PlinController extends Controller
{
    private PlinRepository $repo;

    public function __construct()
    {
        $this->repo = new PlinRepository();
    }

    // ── GET /plin ──────────────────────────────────────────
    public function index(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
        $fecha    = $_GET['fecha'] ?? date('Y-m-d');
        $userRol  = $_SESSION['user_rol'] ?? null;
        $backUrl  = ($userRol === 'STAFF') ? $basePath . '/staff' : $basePath . '/admin/dashboard';

        // Validar formato
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            $fecha = date('Y-m-d');
        }

        $pagos   = $this->repo->getPagosPorFecha($fecha);
        $resumen = $this->repo->getTotalPorFecha($fecha);

        require_once __DIR__ . '/../../views/plin/index.php';
    }

    // ── GET /plin/api/visor ────────────────────────────────
    public function apiVisor(): void
    {
        $fecha   = $_GET['fecha']    ?? date('Y-m-d');
        $sinceId = (int) ($_GET['since_id'] ?? 0);

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            $fecha = date('Y-m-d');
        }

        $pagos   = $this->repo->getPagosPorFecha($fecha, $sinceId);
        $resumen = $this->repo->getTotalPorFecha($fecha);

        $this->success('OK', [
            'pagos'    => $pagos,
            'total'    => (float) $resumen['total'],
            'cantidad' => (int)   $resumen['cantidad'],
        ]);
    }
}
