<?php

require_once __DIR__ . '/../Core/Controller.php';
require_once __DIR__ . '/../Repositories/TerminalPosRepository.php';
require_once __DIR__ . '/../Repositories/CajaRepository.php';

class TerminalPosController extends Controller
{
    private TerminalPosRepository $repo;
    private CajaRepository $cajaRepo;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->repo     = new TerminalPosRepository();
        $this->cajaRepo = new CajaRepository();
    }

    private function requireAdmin(): int
    {
        if (!isset($_SESSION['user_id'])) {
            $isApi = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
                  || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
            if ($isApi) { $this->error('No autenticado', 401); exit; }
            header('Location: ' . APP_BASE_PATH . '/login');
            exit;
        }
        if (($_SESSION['user_rol'] ?? '') !== 'ADMIN') {
            $this->error('Solo el administrador puede acceder a Conciliación', 403);
            exit;
        }
        return (int)$_SESSION['user_id'];
    }

    // ── GET /caja/conciliacion ──────────────────────────────
    public function vista(): void
    {
        $this->requireAdmin();
        $basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
        $userName = $_SESSION['user_name'] ?? 'Usuario';
        $userRol  = $_SESSION['user_rol']  ?? 'ADMIN';

        $filtros = [
            'desde'       => $_GET['desde'] ?? date('Y-m-d', strtotime('-30 days')),
            'hasta'       => $_GET['hasta'] ?? date('Y-m-d'),
            'caja_id'     => isset($_GET['caja']) ? (int)$_GET['caja'] : 0,
            'terminal_id' => $_GET['terminal'] ?? '',
            'estado'      => $_GET['estado'] ?? '',
        ];

        $lotes            = $this->repo->getLotes($filtros);
        $mapeo            = $this->repo->getMapeoTerminales();
        $terminalesSinMap = $this->repo->getTerminalesSinMapear();
        $cajas            = $this->cajaRepo->getCajasActivas();

        require_once __DIR__ . '/../../views/caja/conciliacion.php';
    }

    // ── POST /caja/api/conciliacion/importar ────────────────
    public function importar(): void
    {
        $this->requireAdmin();

        if (empty($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
            $this->error('Selecciona un archivo .txt válido', 422);
            return;
        }

        $filename = $_FILES['archivo']['name'];
        if (!str_ends_with(strtolower($filename), '.txt')) {
            $this->error('El archivo debe ser .txt', 422);
            return;
        }

        try {
            $resumen = $this->repo->importarArchivo($_FILES['archivo']['tmp_name'], $filename);
            $this->success('Archivo importado', $resumen);
        } catch (\Throwable $e) {
            $this->error('No se pudo importar: ' . $e->getMessage(), 422);
        }
    }

    // ── POST /caja/api/conciliacion/mapeo ───────────────────
    public function guardarMapeo(): void
    {
        $this->requireAdmin();
        $data       = $this->getAllInput();
        $terminalId = trim($data['terminal_id'] ?? '');
        $cajaId     = (int)($data['caja_id'] ?? 0);

        if (!$terminalId || !$cajaId) {
            $this->error('terminal_id y caja_id son requeridos', 422);
            return;
        }

        $this->repo->upsertMapeoTerminal($terminalId, $cajaId);
        $this->success('Mapeo guardado');
    }

    // ── GET /caja/api/conciliacion/lote/{id}/transacciones ──
    public function transaccionesDeLote(int $loteId): void
    {
        $this->requireAdmin();
        $txns = $this->repo->getTransaccionesDeLote($loteId);
        $this->success('OK', $txns);
    }

    // ── POST /caja/api/conciliacion/transaccion/{id}/incluir ─
    public function toggleIncluido(int $id): void
    {
        $this->requireAdmin();
        $data     = $this->getAllInput();
        $incluido = !empty($data['incluido']);

        $ok = $this->repo->toggleIncluidoTransaccion($id, $incluido);
        if (!$ok) { $this->error('Transacción no encontrada', 404); return; }
        $this->success($incluido ? 'Transacción incluida' : 'Transacción excluida');
    }
}
