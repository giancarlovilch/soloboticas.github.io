<?php

require_once __DIR__ . '/../Core/Controller.php';
require_once __DIR__ . '/../Repositories/IncidenciaContableRepository.php';
require_once __DIR__ . '/../Repositories/CajaRepository.php';
require_once __DIR__ . '/../Repositories/SoloBankRepository.php';

class IncidenciaContableController extends Controller
{
    private IncidenciaContableRepository $repo;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->repo = new IncidenciaContableRepository();
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
        return (int)$_SESSION['user_id'];
    }

    private function requireAdmin(): int
    {
        $id = $this->requireAuth();
        if (($_SESSION['user_rol'] ?? '') !== 'ADMIN') {
            $this->error('Solo el administrador puede realizar esta acción', 403);
            exit;
        }
        return $id;
    }

    // ── GET /incidencias ──────────────────────────────────

    public function index(): void
    {
        $postulanteId = $this->requireAuth();
        $basePath     = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
        $userName     = $_SESSION['user_name'] ?? 'Usuario';
        $userRol      = $_SESSION['user_rol']  ?? 'STAFF';

        $filtroEstado = $_GET['estado'] ?? '';
        $filtroTipo   = $_GET['tipo']   ?? '';

        $incidencias  = $this->repo->listar(
            $filtroEstado ?: null,
            $filtroTipo   ?: null
        );
        $totalAbiertos = $this->repo->contarAbiertos();

        require_once __DIR__ . '/../../views/incidencias/index.php';
    }

    // ── GET /incidencias/sesion/{sesionId} ───────────────
    // Busca incidencia existente; si no hay, crea una y redirige.

    public function porSesion(int $sesionId): void
    {
        $postulanteId = $this->requireAuth();

        // ¿Ya existe?
        $existentes = $this->repo->getBySesion($sesionId);
        if (!empty($existentes)) {
            header('Location: ' . APP_BASE_PATH . '/incidencias/' . $existentes[0]['id_incidencia']);
            exit;
        }

        // Crear una nueva a partir de los datos del arqueo
        $cajaRepo = new CajaRepository();
        $reporte  = $cajaRepo->getReporte($sesionId);
        $dc       = $reporte['detalle'] ?? [];
        $sesion   = $reporte['sesion'] ?? [];

        $diferencia    = (float)($dc['diferencia'] ?? 0);
        $tipo          = $diferencia >= 0 ? 'SOBRANTE' : 'FALTANTE';
        $monto         = round(abs($diferencia), 2);
        $responsableId = (int)($sesion['postulante_apertura_id'] ?? 0) ?: null;

        $id = $this->repo->crear(
            $sesionId,
            $tipo,
            $monto,
            $postulanteId,
            $responsableId,
            null,
            true
        );

        header('Location: ' . APP_BASE_PATH . '/incidencias/' . $id);
        exit;
    }

    // ── GET /incidencias/{id} ─────────────────────────────

    public function detalle(int $id): void
    {
        $postulanteId = $this->requireAuth();
        $basePath     = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
        $userName     = $_SESSION['user_name'] ?? 'Usuario';
        $userRol      = $_SESSION['user_rol']  ?? 'STAFF';

        $incidencia  = $this->repo->getById($id);
        if (!$incidencia) {
            header('Location: ' . APP_BASE_PATH . '/incidencias');
            exit;
        }
        $movimientos = $this->repo->getMovimientos($id);

        $cajaRepo      = new CajaRepository();
        $sbRepo        = new SoloBankRepository();
        $sesionId      = (int)$incidencia['sesion_origen_id'];
        $reporte       = $cajaRepo->getReporte($sesionId);
        $auditoriaCaja = $cajaRepo->getAuditoria($sesionId);
        $valesDisponibles   = $sbRepo->getValesDisponibles();
        $valesRegDisponibles = $this->repo->getValesDisponibles();
        $valesRegPropios     = $this->repo->getValesByIncidencia($id);

        extract($reporte);

        // Transferencias de saldo confirmadas pendientes de aplicarse en la caja de esta sesión
        $transferenciasPendientes = $cajaRepo->getTransferenciasPendientesAplicar((int)$sesion['caja_id']);

        // Retiros de efectivo para depósito a KGyR pendientes de aplicarse en la caja de esta sesión
        $retirosPendientes = $cajaRepo->getRetirosPendientesAplicar((int)$sesion['caja_id']);

        // Transferencias y retiros ya aplicados al cuadre de esta sesión (afectan "lo que se dice")
        $transferencias   = $cajaRepo->getTransferenciasAplicadas($sesionId);
        $retirosAplicados = $cajaRepo->getRetirosAplicados($sesionId);

        require_once __DIR__ . '/../../views/incidencias/detalle.php';
    }

    // ── POST /incidencias/api/{id}/abonar ─────────────────

    public function apiAbonar(int $id): void
    {
        $postulanteId = $this->requireAuth();
        $data = $this->getAllInput();

        $tiposValidos = ['ABONO', 'CARGO', 'CONDONACION', 'AJUSTE_ADMIN'];
        $tipo    = strtoupper(trim($data['tipo'] ?? ''));
        $monto   = round((float)($data['monto'] ?? 0), 2);
        $sesionId = isset($data['sesion_id']) ? (int)$data['sesion_id'] : null;
        $desc    = trim($data['descripcion'] ?? '');

        if (!in_array($tipo, $tiposValidos, true)) {
            $this->error('Tipo de movimiento inválido', 422);
            return;
        }
        if ($monto <= 0) {
            $this->error('El monto debe ser mayor a 0', 422);
            return;
        }

        $incidencia = $this->repo->getById($id);
        if (!$incidencia) { $this->notFound('Incidencia no encontrada'); return; }
        if ($incidencia['estado'] === 'CERRADO') {
            $this->error('La incidencia ya está cerrada', 409);
            return;
        }

        $this->repo->addMovimiento($id, $tipo, $monto, $postulanteId, $sesionId, $desc ?: null);
        $actualizada = $this->repo->getById($id);

        $this->success('Movimiento registrado', [
            'monto_pendiente' => $actualizada['monto_pendiente'],
            'estado'          => $actualizada['estado'],
        ]);
    }

    // ── POST /incidencias/api/{id}/cerrar ─────────────────

    public function apiCerrar(int $id): void
    {
        $postulanteId = $this->requireAdmin();

        $incidencia = $this->repo->getById($id);
        if (!$incidencia) { $this->notFound('Incidencia no encontrada'); return; }
        if ($incidencia['estado'] === 'CERRADO') {
            $this->error('Ya está cerrada', 409);
            return;
        }
        $esFaltante = $incidencia['tipo'] === 'FALTANTE';
        if ($esFaltante && (float)$incidencia['monto_pendiente'] > 10) {
            // Permitir cierre si la diferencia efectiva del arqueo ya está dentro del margen
            $cajaRepo  = new CajaRepository();
            $reporte   = $cajaRepo->getReporte((int)$incidencia['sesion_origen_id']);
            $dc        = $reporte['detalle'] ?? [];
            $rectifs   = $reporte['rectifs'] ?? [];
            $ajustes   = $reporte['ajustesEsperado'] ?? [];
            $corrDelta = (float)($reporte['sumCorrDelta'] ?? 0);
            $sumR = array_sum(array_map(fn($r) => (float)$r['monto'], $rectifs));
            $sumA = 0;
            foreach ($ajustes as $aj)
                $sumA += $aj['accion'] === 'AGREGAR' ? (float)$aj['monto'] : -(float)$aj['monto'];
            $difConSigno = round(
                ((float)($dc['total_efectivo_contado'] ?? 0) - (float)($dc['total_esperado_sistema'] ?? 0))
                + $sumR + $sumA - $corrDelta, 2
            );
            // Si la sesión ahora muestra sobrante (> 0), el faltante original quedó resuelto.
            // Solo bloquear si sigue siendo faltante (< 0) y supera el margen de S/ 10.
            if ($difConSigno < 0 && abs($difConSigno) > 10) {
                $this->error(
                    'No se puede cerrar: aún hay un faltante efectivo de S/ ' .
                    number_format(abs($difConSigno), 2) . '. Debe ser ≤ S/ 10.00.',
                    409
                );
                return;
            }
        }

        $this->repo->cerrarManual($id);
        $this->success('Incidencia cerrada manualmente');
    }

    // ── POST /incidencias/api/{id}/reabrir ────────────────

    public function apiReabrir(int $id): void
    {
        $postulanteId = $this->requireAdmin();

        $incidencia = $this->repo->getById($id);
        if (!$incidencia) { $this->notFound('Incidencia no encontrada'); return; }

        $this->repo->reabrirCaso($id);
        $this->success('Incidencia reabierta');
    }

    // ── POST /incidencias/api/{id}/descripcion ────────────

    public function apiDescripcion(int $id): void
    {
        $postulanteId = $this->requireAuth();
        $data = $this->getAllInput();
        $desc = trim($data['descripcion'] ?? '');

        $incidencia = $this->repo->getById($id);
        if (!$incidencia) { $this->notFound(); return; }

        $this->repo->actualizarDescripcion($id, $desc);
        $this->success('Descripción actualizada');
    }

    // ── GET /incidencias/api/lista (JSON) ─────────────────

    public function apiListar(): void
    {
        $this->requireAuth();
        $estado = $_GET['estado'] ?? null;
        $tipo   = $_GET['tipo']   ?? null;

        $rows = $this->repo->listar($estado ?: null, $tipo ?: null);
        $this->success('OK', $rows);
    }

    // ── POST /incidencias/api/{id}/generar-vale ────────────

    public function apiGenerarVale(int $id): void
    {
        $postulanteId = $this->requireAuth();
        $data  = $this->getAllInput();
        $monto = round((float)($data['monto'] ?? 0), 2);
        $desc  = trim($data['descripcion'] ?? '');

        if ($monto <= 0) { $this->error('El monto debe ser mayor a 0', 422); return; }

        $inc = $this->repo->getById($id);
        if (!$inc) { $this->notFound('Incidencia no encontrada'); return; }
        if ($inc['estado'] === 'CERRADO') { $this->error('La incidencia ya está cerrada', 409); return; }

        try {
            $codigo = $this->repo->generarVale($id, $monto, $desc, $postulanteId);
            $this->success('Vale generado', ['codigo' => $codigo]);
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage(), 422);
        }
    }

    // ── POST /incidencias/api/{id}/usar-vale ──────────────

    public function apiUsarVale(int $id): void
    {
        $postulanteId = $this->requireAuth();
        $data   = $this->getAllInput();
        $valeId = (int)($data['vale_id'] ?? 0);

        if (!$valeId) { $this->error('Selecciona un vale', 422); return; }

        $inc = $this->repo->getById($id);
        if (!$inc) { $this->notFound('Incidencia no encontrada'); return; }

        try {
            $this->repo->usarVale($valeId, $id, $postulanteId);
            $actualizada = $this->repo->getById($id);
            $this->success('Vale aplicado', [
                'monto_pendiente' => $actualizada['monto_pendiente'],
                'estado'          => $actualizada['estado'],
            ]);
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage(), 409);
        }
    }

    // ── POST /incidencias/api/{id}/eliminar-movimiento ───

    public function apiEliminarMovimiento(int $id): void
    {
        $this->requireAdmin();
        $data  = $this->getAllInput();
        $movId = (int)($data['mov_id'] ?? 0);
        if (!$movId) { $this->error('Movimiento no especificado', 422); return; }
        try {
            $this->repo->eliminarMovimiento($movId);
            $actualizada = $this->repo->getById($id);
            $this->success('Movimiento eliminado', [
                'monto_pendiente' => $actualizada['monto_pendiente'],
                'estado'          => $actualizada['estado'],
            ]);
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage(), 422);
        }
    }

    // ── POST /incidencias/api/{id}/editar-movimiento ──────

    public function apiEditarMovimiento(int $id): void
    {
        $this->requireAdmin();
        $data  = $this->getAllInput();
        $movId = (int)($data['mov_id'] ?? 0);
        $monto = round((float)($data['monto'] ?? 0), 2);
        $desc  = trim($data['descripcion'] ?? '');
        if (!$movId) { $this->error('Movimiento no especificado', 422); return; }
        if ($monto <= 0) { $this->error('El monto debe ser mayor a 0', 422); return; }
        try {
            $this->repo->editarMovimiento($movId, $monto, $desc);
            $this->success('Movimiento actualizado');
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage(), 422);
        }
    }

    // ── POST /incidencias/api/{id}/revertir-vale ─────────

    public function apiRevertirVale(int $id): void
    {
        $this->requireAdmin();
        $data   = $this->getAllInput();
        $valeId = (int)($data['vale_id'] ?? 0);
        if (!$valeId) { $this->error('Vale no especificado', 422); return; }
        try {
            $this->repo->revertirVale($valeId);
            $this->success('Vale revertido');
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage(), 409);
        }
    }

    // ── POST /incidencias/api/{id}/editar-vale ────────────

    public function apiEditarVale(int $id): void
    {
        $this->requireAdmin();
        $data   = $this->getAllInput();
        $valeId = (int)($data['vale_id'] ?? 0);
        $monto  = round((float)($data['monto'] ?? 0), 2);
        $desc   = trim($data['descripcion'] ?? '');

        if (!$valeId) { $this->error('Vale no especificado', 422); return; }
        if ($monto <= 0) { $this->error('El monto debe ser mayor a 0', 422); return; }

        $this->repo->editarVale($valeId, $monto, $desc);
        $this->success('Vale actualizado');
    }

    // ── POST /incidencias/api/{id}/anular-vale ────────────

    public function apiAnularVale(int $id): void
    {
        $postulanteId = $this->requireAdmin();
        $data   = $this->getAllInput();
        $valeId = (int)($data['vale_id'] ?? 0);
        if (!$valeId) { $this->error('Vale no especificado', 422); return; }
        $this->repo->anularVale($valeId, $postulanteId);
        $this->success('Vale anulado');
    }

    // ── POST /incidencias/api/{id}/cobro-electronico ───────
    // Agrega un pago digital a la sesión asociada, ignorando el flag bloqueado
    // (las sesiones cerradas siempre tienen bloqueado=1).

    public function apiAddCobro(int $id): void
    {
        $postulanteId = $this->requireAuth();
        $data  = $this->getAllInput();
        $modo  = (int)($data['modo_id'] ?? 0);
        $monto = round((float)($data['monto'] ?? 0), 2);
        $num   = trim($data['numero_operacion'] ?? '') ?: null;

        if (!$modo || $monto <= 0) {
            $this->error('modo_id y monto son requeridos', 422);
            return;
        }

        $inc = $this->repo->getById($id);
        if (!$inc) { $this->notFound('Incidencia no encontrada'); return; }

        $sesionId = (int)$inc['sesion_origen_id'];
        $movId = $this->cajaRepo()->addPagoDigital($sesionId, $postulanteId, $modo, $monto, $num);
        $this->cajaRepo()->recalcularDiferencia($sesionId);

        // Reflejar el cobro también en el caso: reduce el pendiente igual que un vale.
        $this->repo->addMovimiento(
            $id, 'ABONO', $monto, $postulanteId, $sesionId,
            "Cobro electrónico registrado (mov. #{$movId})"
        );

        $this->success('Cobro registrado', ['id_movimiento' => $movId]);
    }

    private function cajaRepo(): CajaRepository
    {
        static $repo = null;
        if ($repo === null) $repo = new CajaRepository();
        return $repo;
    }

    // ── GET /incidencias/api/vales-disponibles ─────────────

    public function apiValesDisponibles(): void
    {
        $this->requireAuth();
        $this->success('OK', $this->repo->getValesDisponibles());
    }
}
