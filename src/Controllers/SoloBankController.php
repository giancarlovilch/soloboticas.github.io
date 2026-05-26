<?php

require_once __DIR__ . '/../Core/Controller.php';
require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Repositories/SoloBankRepository.php';
require_once __DIR__ . '/../Repositories/CajaRepository.php';
require_once __DIR__ . '/../../config/env.php';

class SoloBankController extends Controller
{
    private SoloBankRepository $repo;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->repo = new SoloBankRepository();
    }

    private function autenticarWebhook(): void
    {
        $keyEsperada = env('SOLOBANK_API_KEY', '');
        if (empty($keyEsperada)) {
            $this->error('SOLOBANK_API_KEY no configurada en servidor', 500); exit;
        }
        $headers     = function_exists('getallheaders') ? getallheaders() : [];
        $keyRecibida = $headers['X-SoloBank-Key'] ?? $headers['x-solobank-key'] ?? '';
        if (!hash_equals($keyEsperada, $keyRecibida)) {
            $this->error('API Key inválida', 401); exit;
        }
    }

    private function requireAuth(): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . APP_BASE_PATH . '/login'); exit;
        }
    }

    // ── POST /api/solobank/vale  (llamado por Python) ─────────────────────────
    public function recibirVale(): void
    {
        $this->autenticarWebhook();
        $d = $this->getJsonInput();

        if (empty($d['codigo']) || empty($d['caja']) || empty($d['fecha'])) {
            $this->validationError('Faltan campos: codigo, caja, fecha');
            return;
        }

        $this->repo->upsertVale([
            'codigo' => trim($d['codigo']),
            'caja'   => trim($d['caja']),
            'fecha'  => trim($d['fecha']),
            'turno'  => trim($d['turno'] ?? 'Tarde'),
            'total'  => (float) ($d['total']  ?? 0),
            'conteo' => (int)   ($d['conteo'] ?? 0),
        ]);

        $this->success('Vale recibido');
    }

    // ── GET /api/solobank/vales/disponibles  (para el select en caja) ─────────
    public function apiDisponibles(): void
    {
        if (!isset($_SESSION['user_id'])) { $this->error('No autenticado', 401); return; }
        $vales = $this->repo->getValesDisponibles();
        $this->success('OK', ['vales' => $vales]);
    }

    // ── POST /caja/api/sesion/{id}/solobank  (cajera asigna un vale) ──────────
    public function usarVale(int $sesionId): void
    {
        if (!isset($_SESSION['user_id'])) { $this->error('No autenticado', 401); return; }
        $d      = $this->getAllInput();
        $codigo = trim($d['codigo'] ?? '');

        if (!$codigo) { $this->validationError('Falta codigo del vale'); return; }

        // Obtener datos del vale
        $vales = $this->repo->getValesDisponibles();
        $vale  = null;
        foreach ($vales as $v) {
            if ($v['codigo'] === $codigo) { $vale = $v; break; }
        }
        if (!$vale) { $this->error('Vale no disponible o ya fue usado', 409); return; }

        // Insertar movimiento_sesion con modo SoloBank
        $cajaRepo = new CajaRepository();
        $movId    = $cajaRepo->addPagoSoloBank(
            $sesionId,
            (int) $_SESSION['user_id'],
            $codigo,
            (float) $vale['total']
        );

        // Marcar vale como usado
        $ok = $this->repo->usarVale($codigo, $sesionId, $movId);
        if (!$ok) {
            // Rollback: alguien más lo tomó antes
            $cajaRepo->deletePagoDigital($movId);
            $this->error('Vale ya fue tomado por otra sesión', 409);
            return;
        }

        $this->success('Vale SoloBank asignado', [
            'id_movimiento' => $movId,
            'monto'         => $vale['total'],
        ]);
    }

    // ── DELETE /caja/api/solobank-mov/{movId}  (cajera quita un vale) ─────────
    public function quitarVale(int $movId): void
    {
        if (!isset($_SESSION['user_id'])) { $this->error('No autenticado', 401); return; }
        $cajaRepo = new CajaRepository();
        $ok       = $cajaRepo->deletePagoDigital($movId);
        if ($ok) {
            $this->repo->liberarVale($movId);
            $this->success('Vale liberado');
        } else {
            $this->error('No se puede quitar: movimiento no encontrado o aprobado', 409);
        }
    }

    // ── GET /admin/solobank-vales  (dashboard admin) ──────────────────────────
    public function vistaAdmin(): void
    {
        $this->requireAuth();
        $basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
        $userName = $_SESSION['user_name'] ?? 'Usuario';
        $vales    = $this->repo->getAll();
        require_once __DIR__ . '/../../views/admin/solobank-vales.php';
    }
}
