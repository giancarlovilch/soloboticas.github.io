<?php

require_once __DIR__ . '/../Core/Controller.php';
require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Repositories/PlinRepository.php';

class PlinController extends Controller
{
    private PlinRepository $repo;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->repo = new PlinRepository();
    }

    private function requireAuth(): int
    {
        if (!isset($_SESSION['user_id'])) {
            $isApi = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
                  || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
            if ($isApi) { $this->error('No autenticado', 401); exit; }
            header('Location: ' . APP_BASE_PATH . '/login');
            exit;
        }
        return (int) $_SESSION['user_id'];
    }

    // ── GET /plin ──────────────────────────────────────────
    public function index(): void
    {
        $userId   = $this->requireAuth();
        $basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
        $userName = $_SESSION['user_name'] ?? 'Usuario';
        $userRol  = $_SESSION['user_rol']  ?? 'STAFF';

        $sesiones     = $this->repo->getSesiones(60);
        $pagosLibres  = $this->repo->getPagosLibres(100);
        $totalLibres  = $this->repo->totalPagosLibres();
        $cajas        = $this->repo->getCajasActivas();
        $turnos       = $this->repo->getTurnos();
        $staff        = $this->repo->getStaffActivo();

        require_once __DIR__ . '/../../views/plin/index.php';
    }

    // ── GET /plin/sesion/{id} ──────────────────────────────
    public function sesion(int $id): void
    {
        $userId   = $this->requireAuth();
        $basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
        $userName = $_SESSION['user_name'] ?? 'Usuario';
        $userRol  = $_SESSION['user_rol']  ?? 'STAFF';

        $sesion = $this->repo->getSesion($id);
        if (!$sesion) { $this->notFound('Sesión PLIN no encontrada'); return; }

        $pagosLibres    = $this->repo->getPagosLibres(100);
        $pagosReclamados = $this->repo->getPagosReclamados($id);

        require_once __DIR__ . '/../../views/plin/sesion.php';
    }

    // ── POST /plin/api/sesion ─────────────────────────────
    public function apiCrearSesion(): void
    {
        $userId = $this->requireAuth();
        $d      = $this->getAllInput();

        $cajaId     = (int) ($d['caja_id']      ?? 0);
        $turnoId    = (int) ($d['turno_id']      ?? 0);
        $fecha      = trim($d['fecha']           ?? date('Y-m-d'));
        $cajeraId   = !empty($d['cajera_id'])   ? (int)$d['cajera_id']   : null;
        $vendedoraId= !empty($d['vendedora_id']) ? (int)$d['vendedora_id']: null;

        if (!$cajaId || !$turnoId || !$fecha) {
            $this->validationError('Faltan: caja_id, turno_id, fecha');
            return;
        }

        $id = $this->repo->crearSesion($cajaId, $turnoId, $fecha, $cajeraId, $vendedoraId, $userId);
        $this->created('Sesión PLIN abierta', ['id' => $id]);
    }

    // ── GET /plin/api/sesion/{id}/pagos-libres ─────────────
    public function apiPagosLibres(): void
    {
        $this->requireAuth();
        $pagos = $this->repo->getPagosLibres(100);
        $this->success('OK', ['pagos' => $pagos, 'total' => count($pagos)]);
    }

    // ── POST /plin/api/sesion/{id}/reclamar/{pagoId} ───────
    public function apiReclamar(int $sesionId, int $pagoId): void
    {
        $userId = $this->requireAuth();
        $ok = $this->repo->reclamarPago($sesionId, $pagoId, $userId);
        if (!$ok) {
            $this->error('El pago ya fue reclamado o la sesión está cerrada', 409);
            return;
        }
        $sesion = $this->repo->getSesion($sesionId);
        $this->success('Pago reclamado', [
            'total_reclamado' => $sesion['total_reclamado'],
            'num_pagos'       => $sesion['num_pagos'],
        ]);
    }

    // ── POST /plin/api/sesion/{id}/cerrar ──────────────────
    public function apiCerrarSesion(int $id): void
    {
        $this->requireAuth();
        $this->repo->cerrarSesion($id);
        $sesion = $this->repo->getSesion($id);
        $this->success('Sesión cerrada', [
            'total_reclamado' => $sesion['total_reclamado'],
            'num_pagos'       => $sesion['num_pagos'],
        ]);
    }

    // ── GET /plin/api/sesion/{id}/reclamados ───────────────
    public function apiReclamados(int $id): void
    {
        $this->requireAuth();
        $pagos = $this->repo->getPagosReclamados($id);
        $this->success('OK', ['pagos' => $pagos]);
    }
}
