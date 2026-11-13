<?php

require_once __DIR__ . '/../Core/Controller.php';
require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Repositories/CajaRepository.php';
require_once __DIR__ . '/../../config/env.php';

/**
 * Puente server-a-servidor con soloboticas-softpharma (Django, panel de
 * conciliación en softpharma.solodata.cc). Dirección inversa de
 * SoftpharmaApiClient.php: ahí este mismo sitio LLAMA a Django para
 * preguntar por SOFTPHARMA; acá Django llama a este sitio para preguntar
 * qué venta tiene registrada soloboticas.com, y para corregirla cuando no
 * coincide con el ERP. Misma API key (SOFTPHARMA_API_KEY) en ambos
 * sentidos -- es el mismo par de servicios de confianza.
 */
class SoftpharmaBridgeController extends Controller
{
    private CajaRepository $repo;

    public function __construct()
    {
        $this->repo = new CajaRepository();
    }

    private function autenticarWebhook(): void
    {
        $keyEsperada = env('SOFTPHARMA_API_KEY', '');
        if (empty($keyEsperada)) {
            $this->error('SOFTPHARMA_API_KEY no configurada en servidor', 500); exit;
        }
        $headers     = function_exists('getallheaders') ? getallheaders() : [];
        $keyRecibida = $headers['X-Api-Key'] ?? $headers['x-api-key'] ?? $_SERVER['HTTP_X_API_KEY'] ?? '';
        if (!hash_equals($keyEsperada, trim($keyRecibida))) {
            $this->error('API Key inválida', 401); exit;
        }
    }

    /** GET /api/softpharma-bridge/ventas-dia?fecha=YYYY-MM-DD&local=SB2 */
    public function apiVentasDia(): void
    {
        $this->autenticarWebhook();
        $fecha = $_GET['fecha'] ?? '';
        $local = $_GET['local'] ?? '';

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) { $this->error('fecha inválida', 422); return; }
        $localId = $this->repo->getLocalIdPorSoftpharmaCodigo($local);
        if (!$localId) { $this->error('local inválido', 422); return; }

        $sesiones = $this->repo->getSesionesParaBridge($fecha, $localId);
        $basePath = rtrim((string)env('APP_PUBLIC_URL', 'https://www.soloboticas.com/public'), '/');

        $out = array_map(function ($s) use ($basePath) {
            return [
                'sesion_id'           => (int)$s['id_sesion'],
                'estado'              => $s['estado'],
                'turno'               => $s['turno_desc'],
                'cajera_nombre'       => $s['cajera_nombre'],
                'cajera_fecha_cierre' => $s['cajera_fecha_cierre'],
                'vendedora_nombre'    => $s['vendedora_nombre'],
                'vendedora_usuario'   => $s['softpharma_usuario'],
                'venta_actual'        => (float)$s['venta_actual'],
                'reporte_url'         => "{$basePath}/caja/reporte/{$s['id_sesion']}",
            ];
        }, $sesiones);

        $this->success('OK', ['fecha' => $fecha, 'local' => $local, 'sesiones' => $out]);
    }

    /** GET /api/softpharma-bridge/cierres-asignados?fecha=YYYY-MM-DD */
    public function apiCierresAsignados(): void
    {
        $this->autenticarWebhook();
        $fecha = $_GET['fecha'] ?? '';
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) { $this->error('fecha inválida', 422); return; }

        $this->success('OK', ['fecha' => $fecha, 'id_caja_erp' => $this->repo->getIdsCajaErpAsignados($fecha)]);
    }

    /** POST /api/softpharma-bridge/asignar-venta */
    public function apiAsignarVenta(): void
    {
        $this->autenticarWebhook();
        $d = $this->getJsonInput();

        $sesionId = (int)($d['sesion_id'] ?? 0);
        $idCaja   = (int)($d['id_caja_erp'] ?? 0);
        $usuario  = trim($d['usuario_erp'] ?? '');
        $monto    = isset($d['venta_neta']) ? (float)$d['venta_neta'] : -1;

        if (!$sesionId || !$idCaja || !$usuario || $monto < 0) {
            $this->error('sesion_id, id_caja_erp, usuario_erp y venta_neta son requeridos', 422); return;
        }

        $sesion = $this->repo->getSesionById($sesionId);
        if (!$sesion) { $this->error('Sesión no encontrada', 404); return; }
        if (!in_array($sesion['estado'], ['CERRADA', 'EN_REVISION', 'APROBADA', 'OBSERVADA', 'RECHAZADA'], true)) {
            $this->error('La sesión todavía no tiene un cuadre calculado (sigue abierta)', 409); return;
        }

        try {
            $this->repo->registrarAsignacionErp($sesionId, [
                'id_caja' => $idCaja, 'usuario' => $usuario,
                'nombre' => $d['nombre_erp'] ?? null, 'venta_neta' => $monto,
                'cierre' => $d['cierre'] ?? null,
            ], null); // null = asignado desde el panel de conciliación, no un admin de soloboticas.com
        } catch (\PDOException $e) {
            $this->error('Ese cierre ya fue asignado a otro cuadre', 409); return;
        }

        $totalNuevo = $this->repo->getTotalVentaActual($sesionId) + $monto;
        $this->repo->addCorreccionVenta(
            $sesionId, $totalNuevo,
            "Venta SOFTPHARMA asignada desde el panel de conciliación: {$usuario} (S/ " . number_format($monto, 2) . ")",
            null
        );
        $this->repo->marcarVentaResueltaEnSesion($sesionId);

        $this->success('Venta actualizada', ['sesion_id' => $sesionId, 'total_ventas' => $totalNuevo]);
    }
}
