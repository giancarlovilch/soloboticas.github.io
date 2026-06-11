<?php

date_default_timezone_set('America/Lima');

// Timeout de sesión: 8 horas de inactividad (cubre un turno completo)
session_start();
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['_last_act']) && (time() - $_SESSION['_last_act']) > 28800) {
        session_unset();
        session_destroy();
        session_start();
    } else {
        $_SESSION['_last_act'] = time();
    }
}

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(200);
    exit;
}

// Carga de dependencias del Core y Helpers
require_once __DIR__ . '/../src/Helpers/Response.php';
require_once __DIR__ . '/../src/Core/Controller.php';
require_once __DIR__ . '/../src/Core/Router.php';
require_once __DIR__ . '/../src/Core/Database.php';

// Inclusión de Controladores
require_once __DIR__ . '/../src/Controllers/PostulanteController.php';
require_once __DIR__ . '/../src/Controllers/CatalogoController.php';
require_once __DIR__ . '/../src/Controllers/HomeController.php';
require_once __DIR__ . '/../src/Controllers/AuthController.php';
require_once __DIR__ . '/../src/Controllers/AsistenciaController.php';
require_once __DIR__ . '/../src/Controllers/AdminController.php';
require_once __DIR__ . '/../src/Controllers/StaffController.php';
require_once __DIR__ . '/../src/Controllers/CajaController.php';
require_once __DIR__ . '/../src/Controllers/HorarioController.php';
require_once __DIR__ . '/../src/Controllers/IncidenciaContableController.php';

/**
 * Lógica para detección de rutas y Base Path
 */
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$basePath = rtrim(str_replace('/index.php', '', $scriptName), '/');

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestPath = parse_url($requestUri, PHP_URL_PATH) ?? '/';

if ($basePath !== '' && strpos($requestPath, $basePath) === 0) {
    $requestPath = substr($requestPath, strlen($basePath));
}

// --- MEJORA: Normalizar barras finales para evitar errores 404 en el Router ---
$requestPath = ($requestPath !== '/') ? rtrim($requestPath, '/') : '/';
$requestPath = ($requestPath === '') ? '/' : $requestPath;

define('APP_BASE_PATH', $basePath);

// Instancia del Router[cite: 8]
$router = new Router();

// --- RUTAS PÚBLICAS Y VISTAS ---
$router->get('/', [HomeController::class, 'index']);
$router->get('/login', [HomeController::class, 'loginView']);
$router->get('/postulacion/acceso', [PostulanteController::class, 'accessView']);
$router->get('/postulacion/formulario', [PostulanteController::class, 'formView']);
$router->get('/catalogos/postulacion', [CatalogoController::class, 'getAll']);

// --- RUTAS DE PROCESO DE POSTULANTE ---
$router->post('/postulantes/check-dni', [PostulanteController::class, 'checkDni']);
$router->post('/postulantes/validate-access', [PostulanteController::class, 'validateAccess']);
$router->post('/postulaciones',        [PostulanteController::class, 'apply']);
$router->get('/postulaciones/{dni}',   [PostulanteController::class, 'getApplicationView']);
$router->post('/postulantes/foto',     [PostulanteController::class, 'uploadFoto']);

// --- AUTENTICACIÓN ---
$router->post('/login',          [AuthController::class, 'login']);
$router->post('/logout',         [AuthController::class, 'logout']);
$router->get('/logout',          [AuthController::class, 'logout']);
$router->get('/api/auth/verify', [AuthController::class, 'verifyToken']);

// --- MÓDULO DE HORARIOS ---
$router->get('/horario',                        [HorarioController::class, 'index']);
$router->get('/horario/log',                    [HorarioController::class, 'log']);
$router->get('/horario/asistencia',             [HorarioController::class, 'asistencia']);
$router->get('/horario/siguiente',              [HorarioController::class, 'siguiente']);
$router->get('/horario/historial',              [HorarioController::class, 'historial']);
$router->get('/horario/solicitudes',            [HorarioController::class, 'solicitudes']);
$router->post('/horario/api/slot/{id}/liberar-admin',   [HorarioController::class, 'liberarSlotAdmin']);
$router->post('/horario/api/solicitud/cubrir',                  [HorarioController::class, 'cubrir']);
$router->post('/horario/api/solicitud/{id}/anular',             [HorarioController::class, 'anularSolicitud']);
$router->post('/horario/api/solicitud/{id}/revertir-propia',    [HorarioController::class, 'revertirCoberturaPropia']);
$router->post('/horario/api/solicitud/{id}/revertir',           [HorarioController::class, 'revertirCobertura']);
$router->get('/horario/informacion',            [HorarioController::class, 'informacion']);
$router->post('/admin/api/penalidad/{id}',      [HorarioController::class, 'actualizarPenalidad']);
$router->get('/horario/api/semana/{id}',        [HorarioController::class, 'getSlots']);
$router->post('/horario/api/semana/crear',      [HorarioController::class, 'crearSemana']);
$router->post('/horario/api/semana/{id}/cerrar',[HorarioController::class, 'cerrarSemana']);
$router->post('/horario/api/slot/intercambiar', [HorarioController::class, 'intercambiarSlots']);
$router->post('/horario/api/slot/asignar',      [HorarioController::class, 'asignarSlot']);
$router->post('/horario/api/slot/liberar',      [HorarioController::class, 'liberarSlot']);
$router->get('/horario/api/staff-turno',        [HorarioController::class, 'getStaffTurno']);
$router->get('/horario/api/trabajadores',       [HorarioController::class, 'getTrabajadores']);

// --- MÓDULO DE CAJA ---
$router->get('/caja',                          [CajaController::class, 'index']);
$router->get('/caja/sesion/nueva',             [CajaController::class, 'nueva']);
$router->get('/caja/sesion/{id}',              [CajaController::class, 'editarSesion']);
$router->get('/caja/{id}/ventas',              [CajaController::class, 'ventasView']);
$router->get('/caja/reporte/{id}',             [CajaController::class, 'reporte']);
$router->post('/caja/api/sesion/crear',        [CajaController::class, 'crearSesion']);
$router->post('/caja/api/sesion/guardar',      [CajaController::class, 'guardarSesion']);
$router->post('/caja/api/{id}/ventas',         [CajaController::class, 'submitVentas']);
$router->post('/caja/api/reporte/{id}/rectificar',        [CajaController::class, 'rectificar']);
$router->post('/caja/api/rectificacion/{id}/eliminar',    [CajaController::class, 'eliminarRectificacion']);
$router->post('/caja/api/sesion/{id}/conteo',                [CajaController::class, 'apiConteo']);
$router->post('/caja/api/sesion/{id}/ajuste-esperado',       [CajaController::class, 'addAjusteEsperado']);
$router->post('/caja/api/ajuste-esperado/{id}/eliminar',     [CajaController::class, 'deleteAjusteEsperado']);
$router->post('/caja/api/sesion/{id}/comentario',            [CajaController::class, 'guardarComentario']);
$router->post('/caja/api/sesion/{id}/respuesta',       [CajaController::class, 'guardarRespuesta']);
$router->post('/caja/api/sesion/{id}/eliminar',        [CajaController::class, 'eliminarSesion']);
$router->post('/caja/api/sesion/{id}/sincronizar-base', [CajaController::class, 'sincronizarBase']);
$router->get('/caja/pagos-digitales',                    [CajaController::class, 'pagosDigitalesView']);
$router->get('/caja/api/sesion/{id}/pagos-digitales',    [CajaController::class, 'getPagosDigitales']);
$router->post('/caja/api/sesion/{id}/pago-digital',      [CajaController::class, 'addPagoDigital']);
$router->post('/caja/api/pago-digital/{id}/confirmar',   [CajaController::class, 'confirmarPago']);
$router->post('/caja/api/pago-digital/{id}/eliminar',    [CajaController::class, 'deletePagoDigitalRoute']);
$router->post('/caja/api/sesion/{id}/pago-digital/{movId}/eliminar-admin', [CajaController::class, 'eliminarPagoAdmin']);
$router->get('/caja/api/catalogos',                      [CajaController::class, 'apiCatalogos']);
$router->get('/caja/api/cajas/{id}',                     [CajaController::class, 'apiCajasByLocal']);
$router->get('/caja/api/saldo/{id}',                     [CajaController::class, 'apiSaldoBase']);
$router->post('/caja/api/sesion/{id}/corregir-venta',          [CajaController::class, 'corregirVenta']);
$router->get('/caja/transferir',                              [CajaController::class, 'transferirIndex']);
$router->post('/caja/api/transferir/solicitar',               [CajaController::class, 'solicitarTransferencia']);
$router->post('/caja/api/transferir/{id}/confirmar',          [CajaController::class, 'confirmarTransferenciaAction']);
$router->post('/caja/api/transferir/{id}/anular',             [CajaController::class, 'anularTransferenciaAction']);

// --- INCIDENCIAS CONTABLES (staff + admin) ---
// Rutas estáticas ANTES de las dinámicas para evitar que {id} capture 'api'
$router->get('/incidencias',                           [IncidenciaContableController::class, 'index']);
$router->get('/incidencias/api/lista',                 [IncidenciaContableController::class, 'apiListar']);
$router->post('/incidencias/api/{id}/abonar',          [IncidenciaContableController::class, 'apiAbonar']);
$router->post('/incidencias/api/{id}/cerrar',          [IncidenciaContableController::class, 'apiCerrar']);
$router->post('/incidencias/api/{id}/reabrir',         [IncidenciaContableController::class, 'apiReabrir']);
$router->post('/incidencias/api/{id}/descripcion',     [IncidenciaContableController::class, 'apiDescripcion']);
$router->post('/incidencias/api/{id}/generar-vale',    [IncidenciaContableController::class, 'apiGenerarVale']);
$router->post('/incidencias/api/{id}/usar-vale',       [IncidenciaContableController::class, 'apiUsarVale']);
$router->post('/incidencias/api/{id}/anular-vale',     [IncidenciaContableController::class, 'apiAnularVale']);
$router->post('/incidencias/api/{id}/editar-vale',     [IncidenciaContableController::class, 'apiEditarVale']);
$router->post('/incidencias/api/{id}/revertir-vale',   [IncidenciaContableController::class, 'apiRevertirVale']);
$router->post('/incidencias/api/{id}/eliminar-movimiento', [IncidenciaContableController::class, 'apiEliminarMovimiento']);
$router->post('/incidencias/api/{id}/editar-movimiento',   [IncidenciaContableController::class, 'apiEditarMovimiento']);
$router->post('/incidencias/api/{id}/cobro-electronico',[IncidenciaContableController::class, 'apiAddCobro']);
$router->get('/incidencias/api/vales-disponibles',     [IncidenciaContableController::class, 'apiValesDisponibles']);
$router->get('/incidencias/sesion/{id}',               [IncidenciaContableController::class, 'porSesion']);
$router->get('/incidencias/{id}',                      [IncidenciaContableController::class, 'detalle']);

// --- PORTAL STAFF (colaboradores) ---
$router->get('/staff',                    [StaffController::class,       'index']);
$router->get('/staff/mi-horario',              [StaffController::class, 'miHorario']);
$router->post('/staff/api/asistencia/{id}/editar',   [StaffController::class, 'editarAsistencia']);
$router->post('/staff/api/asistencia/registrar',         [StaffController::class, 'registrarAsistencia']);
$router->post('/staff/api/asistencia/{id}/revertir',     [StaffController::class, 'revertirFalta']);
$router->get('/staff/economia',           [StaffController::class,       'economia']);
$router->get('/staff/info',               [StaffController::class,       'info']);
$router->get('/staff/api/historial',      [StaffController::class,       'historial']);
$router->get('/staff/api/checklist',      [StaffController::class,       'getChecklist']);
$router->post('/staff/asistencia/marcar', [StaffController::class,       'marcar']);

// --- ASISTENCIA (legado + admin) ---
$router->post('/asistencia/marcar',           [AsistenciaController::class, 'marcar']);
$router->get('/admin/api/asistencias',                    [AsistenciaController::class, 'adminListar']);
$router->get('/admin/api/asistencia/checklist',           [AsistenciaController::class, 'adminGetChecklist']);
$router->post('/admin/asistencia/checklist/actualizar',   [AsistenciaController::class, 'adminActualizarChecklist']);
$router->post('/admin/asistencia/actualizar',  [AsistenciaController::class, 'adminActualizar']);
$router->post('/admin/asistencia/crear',       [AsistenciaController::class, 'adminCrear']);
$router->post('/admin/asistencia/eliminar',    [AsistenciaController::class, 'adminEliminar']);

// --- MÓDULO DE BASE DE DATOS ---
require_once __DIR__ . '/../src/Controllers/DatabaseController.php';
$router->get('/admin/database',                  [DatabaseController::class, 'index']);
$router->get('/admin/database/sync-from-download', [DatabaseController::class, 'syncFromDownload']);
$router->get('/admin/database/download-full',    [DatabaseController::class, 'downloadFull']);
$router->get('/admin/database/apply',            [DatabaseController::class, 'applyToLocal']);
$router->post('/admin/database/upload',          [DatabaseController::class, 'uploadMigration']);

// --- MÓDULO DE REPORTES ---
require_once __DIR__ . '/../src/Controllers/ReporteController.php';

// --- LECTOR DE PAGOS BBVA/PLIN (app móvil) ---
require_once __DIR__ . '/../src/Controllers/PagoBBVAController.php';
require_once __DIR__ . '/../src/Controllers/PlinController.php';
$router->get('/admin/reportes',         [ReporteController::class, 'index']);
$router->get('/admin/reportes/arqueos',    [ReporteController::class, 'arqueos']);
$router->get('/admin/reportes/coberturas',   [ReporteController::class, 'coberturas']);
$router->get('/admin/reportes/asistencias',       [ReporteController::class, 'asistencias']);
$router->get('/admin/reportes/resumen-trabajadores', [ReporteController::class, 'resumenTrabajadores']);
$router->get('/admin/reportes/graficas',             [ReporteController::class, 'graficas']);

// --- RUTAS DE ADMINISTRACIÓN (INTRANET) ---
// Registramos las rutas de API antes del dispatch
$router->get('/admin/dashboard', [AdminController::class, 'index']); 
$router->get('/admin/postulantes', [AdminController::class, 'listarPostulantes']);
$router->post('/admin/contratar', [AdminController::class, 'contratar']);
$router->get('/admin/buscar-postulante', [AdminController::class, 'buscar']);
$router->post('/admin/usuario/estado',    [AdminController::class, 'cambiarEstadoUsuario']);
$router->post('/admin/usuario/password',  [AdminController::class, 'cambiarPassword']);
$router->post('/admin/usuario/username',  [AdminController::class, 'cambiarUsername']);
$router->post('/admin/postulante/eliminar',[AdminController::class, 'eliminarPostulante']);

// APIS
$router->get('/admin/api/postulante-detalle', [AdminController::class, 'apiDetallePostulante']);
$router->post('/admin/postulante/actualizar', [AdminController::class, 'actualizarPostulante']);
$router->post('/admin/api/deposito-kgyr',                  [AdminController::class, 'apiDepositoCrear']);
$router->post('/admin/api/deposito-kgyr/{id}/toggle',      [AdminController::class, 'apiDepositoToggle']);
$router->post('/admin/api/tarifa-base/agregar',      [AdminController::class, 'addTarifaBase']);
$router->post('/admin/api/tarifa-base/{id}/eliminar',[AdminController::class, 'eliminarTarifaBase']);
$router->post('/admin/api/bono/agregar',             [AdminController::class, 'addBono']);
$router->post('/admin/api/bono/{id}/eliminar',       [AdminController::class, 'eliminarBono']);
$router->post('/admin/api/supervisor/agregar',       [AdminController::class, 'addSupervisor']);
$router->post('/admin/api/supervisor/{id}/eliminar', [AdminController::class, 'eliminarSupervisor']);
// BBVA/PLIN — Vista admin y API recepción (rutas originales intactas para el celular)
$router->get('/admin/bbva-pagos',      [PagoBBVAController::class, 'vista']);
$router->post('/api/bbva/pago',        [PagoBBVAController::class, 'registrar']);
$router->post('/api/bbva/pagos/lote',  [PagoBBVAController::class, 'registrarLote']);
$router->get('/api/bbva/pagos',        [PagoBBVAController::class, 'listar']);

// PLIN — Visor público de transacciones
$router->get('/plin',            [PlinController::class, 'index']);
$router->get('/plin/api/visor',  [PlinController::class, 'apiVisor']);

// SoloBank — Vales de cierres enviados desde Python
require_once __DIR__ . '/../src/Controllers/SoloBankController.php';
$router->post('/api/solobank/vale',                        [SoloBankController::class, 'recibirVale']);
$router->get('/api/solobank/vales/disponibles',            [SoloBankController::class, 'apiDisponibles']);
$router->post('/caja/api/sesion/{id}/solobank',            [SoloBankController::class, 'usarVale']);
$router->post('/caja/api/solobank-mov/{id}/quitar',        [SoloBankController::class, 'quitarVale']);
$router->get('/admin/solobank-vales',                      [SoloBankController::class, 'vistaAdmin']);
$router->post('/admin/solobank-vales/{id}/toggle',         [SoloBankController::class, 'toggleEstado']);

/**
 * EJECUCIÓN: El dispatch DEBE ir siempre AL FINAL[cite: 3, 8]
 */
$router->dispatch($_SERVER['REQUEST_METHOD'], $requestPath);