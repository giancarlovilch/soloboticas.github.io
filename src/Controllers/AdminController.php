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

        // Datos para la página de economía
        $economiaDatos = null;
        if ($page === 'economia') {
            require_once __DIR__ . '/../Core/Database.php';
            $db = \Database::getConnection();

            $ecoMes  = $_GET['mes']        ?? date('Y-m');
            $ecoPid  = isset($_GET['trabajador']) ? (int)$_GET['trabajador'] : 0;
            $ecoTipo = $_GET['tipo']       ?? '';
            if (!preg_match('/^\d{4}-\d{2}$/', $ecoMes)) $ecoMes = date('Y-m');
            [$ecoAnio, $ecoNmes] = explode('-', $ecoMes);
            $ecoDesde = "{$ecoAnio}-{$ecoNmes}-01";
            $ecoHasta = date('Y-m-t', strtotime($ecoDesde));

            $ecoWhere  = "DATE(pp.fecha_pago) BETWEEN :desde AND :hasta";
            $ecoParams = ['desde' => $ecoDesde, 'hasta' => $ecoHasta];
            if ($ecoPid)  { $ecoWhere .= " AND pp.postulante_beneficiario_id = :pid"; $ecoParams['pid'] = $ecoPid; }
            if ($ecoTipo) { $ecoWhere .= " AND pp.tipo_pago = :tipo"; $ecoParams['tipo'] = $ecoTipo; }

            $stmtPagos = $db->prepare(
                "SELECT pp.id_pago_personal, pp.monto, pp.tipo_pago, pp.estado,
                        pp.fecha_pago, pp.numero_operacion,
                        sc.fecha_operacion, sc.turno_id,
                        l.descripcion  AS local_desc,
                        pb.nombres     AS beneficiario_nombre,
                        pe.nombres     AS emisor_nombre
                 FROM pago_personal pp
                 INNER JOIN sesion_caja sc ON sc.id_sesion     = pp.sesion_id
                 INNER JOIN caja ca        ON ca.id_caja       = sc.caja_id
                 INNER JOIN local l        ON l.id_local       = ca.local_id
                 INNER JOIN postulante pb  ON pb.id_postulante = pp.postulante_beneficiario_id
                 INNER JOIN postulante pe  ON pe.id_postulante = pp.postulante_emisor_id
                 WHERE {$ecoWhere}
                 ORDER BY pp.fecha_pago DESC"
            );
            $stmtPagos->execute($ecoParams);
            $ecoPagos = $stmtPagos->fetchAll();

            // Incluir pagos a personal registrados como corrección en cuadres cerrados
            $ajWhere  = "ae.tipo = 'PERSONAL'
                         AND DATE(sc.fecha_operacion) BETWEEN :desde AND :hasta";
            $ajParams = ['desde' => $ecoDesde, 'hasta' => $ecoHasta];
            if ($ecoPid)  { $ajWhere .= " AND ae.ref_id = :pid";       $ajParams['pid']  = $ecoPid; }
            if ($ecoTipo) { $ajWhere .= " AND ae.tipo_pago = :tipo";   $ajParams['tipo'] = $ecoTipo; }

            $stmtAj = $db->prepare(
                "SELECT ae.id_ajuste AS id_pago_personal,
                        ae.monto,
                        COALESCE(ae.tipo_pago, 'OTROS') AS tipo_pago,
                        'AJUSTE_CUADRE'                 AS estado,
                        sc.fecha_operacion              AS fecha_pago,
                        ae.descripcion                  AS numero_operacion,
                        sc.fecha_operacion, sc.turno_id,
                        l.descripcion  AS local_desc,
                        pb.nombres     AS beneficiario_nombre,
                        pe.nombres     AS emisor_nombre
                 FROM ajuste_esperado ae
                 INNER JOIN sesion_caja sc ON sc.id_sesion     = ae.sesion_id
                 INNER JOIN caja ca        ON ca.id_caja       = sc.caja_id
                 INNER JOIN local l        ON l.id_local       = ca.local_id
                 INNER JOIN postulante pb  ON pb.id_postulante = ae.ref_id
                 INNER JOIN postulante pe  ON pe.id_postulante = ae.postulante_id
                 WHERE {$ajWhere}
                 ORDER BY sc.fecha_operacion DESC"
            );
            $stmtAj->execute($ajParams);
            $ajPagos = $stmtAj->fetchAll();

            // Unir y ordenar por fecha descendente
            $ecoPagos = array_merge($ecoPagos, $ajPagos);
            usort($ecoPagos, fn($a, $b) => strcmp($b['fecha_pago'], $a['fecha_pago']));

            $ecoTrabajadores = $db->query(
                "SELECT p.id_postulante AS id, p.nombres AS nombre
                 FROM postulante p INNER JOIN usuario u ON u.postulante_id = p.id_postulante
                 WHERE u.activo = 1 ORDER BY p.nombres"
            )->fetchAll();

            // ── Ingresos diarios (cálculo en tiempo real) ─────
            $slotsWhere  = "hs.fecha_dia BETWEEN :desde AND :hasta AND rh.codigo IN ('CAJERA','VENDEDORA','ALMACENERA')";
            $slotsParams = ['desde' => $ecoDesde, 'hasta' => $ecoHasta];
            if ($ecoPid) { $slotsWhere .= " AND hs.postulante_id = :pid"; $slotsParams['pid'] = $ecoPid; }

            $stmtSlots = $db->prepare(
                "SELECT hs.id_slot, hs.fecha_dia, hs.turno_id, hs.local_id,
                        hs.postulante_id,
                        rh.codigo      AS rol_codigo,
                        rh.descripcion AS rol_desc,
                        l.descripcion  AS local_desc,
                        p.nombres      AS trabajador_nombre
                 FROM horario_slot hs
                 INNER JOIN rol_horario rh ON hs.rol_horario_id = rh.id_rol_horario
                 INNER JOIN local l        ON hs.local_id       = l.id_local
                 INNER JOIN postulante p   ON p.id_postulante   = hs.postulante_id
                 INNER JOIN asistencia a   ON a.postulante_id   = hs.postulante_id
                                          AND a.fecha           = hs.fecha_dia
                                          AND (a.turno_id = hs.turno_id OR a.turno_id IS NULL)
                                          AND a.estado != 'FALTA'
                                          AND (a.llegada_puntualidad IS NOT NULL OR a.salida_puntualidad IS NOT NULL)
                 WHERE {$slotsWhere}
                 ORDER BY hs.fecha_dia DESC, p.nombres ASC, hs.turno_id ASC"
            );
            $stmtSlots->execute($slotsParams);
            $ecoSlots = $stmtSlots->fetchAll();

            $getBase = function(string $rol, string $fecha) use ($db): float {
                $s = $db->prepare(
                    "SELECT monto FROM tarifa_base_rol
                     WHERE rol_codigo = :rol AND fecha_vigencia <= :fecha
                     ORDER BY fecha_vigencia DESC LIMIT 1"
                );
                $s->execute(['rol' => $rol, 'fecha' => $fecha]);
                return (float)($s->fetchColumn() ?: 0);
            };

            $getBono = function(string $tipo, float $metrica, string $fecha) use ($db): float {
                if ($metrica <= 0) return 0.0;
                $s = $db->prepare(
                    "SELECT monto_bono FROM configuracion_bono
                     WHERE tipo = :tipo
                       AND fecha_vigencia = (
                           SELECT MAX(fecha_vigencia) FROM configuracion_bono
                           WHERE tipo = :tipo2 AND fecha_vigencia <= :fecha
                       )
                       AND :metrica >= desde
                       AND (:metrica2 <= hasta OR hasta IS NULL)
                     LIMIT 1"
                );
                $s->execute(['tipo'=>$tipo,'tipo2'=>$tipo,'fecha'=>$fecha,'metrica'=>$metrica,'metrica2'=>$metrica]);
                return (float)($s->fetchColumn() ?: 0);
            };

            $getSesion = function(int $localId, int $turnoId, string $fecha) use ($db): ?array {
                $s = $db->prepare(
                    "SELECT sc.id_sesion, dc.num_operaciones_bcp, COALESCE(rv.monto,0) AS ventas
                     FROM sesion_caja sc
                     INNER JOIN caja ca ON ca.id_caja = sc.caja_id
                     LEFT JOIN detalle_cuadre dc ON dc.sesion_id = sc.id_sesion
                     LEFT JOIN reporte_venta rv  ON rv.sesion_id = sc.id_sesion
                     WHERE ca.local_id = :lid AND sc.turno_id = :tid
                       AND sc.fecha_operacion = :fecha AND sc.estado IN ('CERRADA','APROBADA')
                     ORDER BY sc.id_sesion DESC LIMIT 1"
                );
                $s->execute(['lid'=>$localId,'tid'=>$turnoId,'fecha'=>$fecha]);
                $r = $s->fetch();
                return $r ?: null;
            };

            $getVentasVendedora = function(int $pid, int $sesionId) use ($db): float {
                $s = $db->prepare(
                    "SELECT COALESCE(rv.monto,0) FROM sesion_participante sp
                     INNER JOIN reporte_venta rv ON rv.sesion_id = sp.sesion_id
                     WHERE sp.postulante_id = :pid AND sp.sesion_id = :sid
                       AND sp.rol_participacion = 'VENDEDORA' LIMIT 1"
                );
                $s->execute(['pid'=>$pid,'sid'=>$sesionId]);
                return (float)($s->fetchColumn() ?: 0);
            };

            $ecoIngresos = [];
            $ecoTotalIngresos = 0.0;
            $ecoTotalBonos    = 0.0;

            foreach ($ecoSlots as $slot) {
                $rol   = $slot['rol_codigo'];
                $fecha = $slot['fecha_dia'];
                $base  = $getBase($rol, $fecha);
                $bonoV = 0.0; $bonoO = 0.0;

                if (in_array($rol, ['CAJERA','VENDEDORA'])) {
                    $sesion = $getSesion($slot['local_id'], $slot['turno_id'], $fecha);
                    if ($sesion) {
                        if ($rol === 'CAJERA') {
                            $bonoO = $getBono('OPERACIONES_BCP', (float)($sesion['num_operaciones_bcp'] ?? 0), $fecha);
                        } else {
                            $bonoV = $getBono('VENTAS', $getVentasVendedora($slot['postulante_id'], $sesion['id_sesion']), $fecha);
                        }
                    }
                }

                $total = $base + $bonoV + $bonoO;
                $ecoTotalIngresos += $total;
                $ecoTotalBonos    += $bonoV + $bonoO;
                $ecoIngresos[] = array_merge($slot, ['base'=>$base,'bono_v'=>$bonoV,'bono_o'=>$bonoO,'total'=>$total]);
            }

            $economiaDatos = compact(
                'ecoPagos','ecoTrabajadores','ecoMes','ecoDesde','ecoHasta','ecoPid','ecoTipo',
                'ecoIngresos','ecoTotalIngresos','ecoTotalBonos'
            );
        }

        // Datos para la página de bonos
        $bonosDatos = null;
        if ($page === 'bonos') {
            require_once __DIR__ . '/../Core/Database.php';
            $db = \Database::getConnection();
            $tarifas  = $db->query("SELECT * FROM tarifa_base_rol ORDER BY rol_codigo, fecha_vigencia DESC")->fetchAll();
            $bonosV   = $db->query("SELECT * FROM configuracion_bono WHERE tipo='VENTAS' ORDER BY fecha_vigencia DESC, desde ASC")->fetchAll();
            $bonosOps = $db->query("SELECT * FROM configuracion_bono WHERE tipo='OPERACIONES_BCP' ORDER BY fecha_vigencia DESC, desde ASC")->fetchAll();
            $bonosDatos = compact('tarifas','bonosV','bonosOps');
        }

        require_once __DIR__ . '/../../views/admin/dashboard.php';
    }

    /** POST /admin/api/tarifa-base/agregar */
    public function addTarifaBase(): void
    {
        $this->middlewareAdmin();
        $data = $this->getAllInput();
        $rol    = $data['rol_codigo']     ?? '';
        $monto  = (float)($data['monto'] ?? 0);
        $fecha  = $data['fecha_vigencia'] ?? '';
        if (!in_array($rol, ['CAJERA','VENDEDORA','ALMACENERA']) || $monto <= 0 || !$fecha) {
            $this->error('Datos inválidos', 422); return;
        }
        require_once __DIR__ . '/../Core/Database.php';
        \Database::getConnection()->prepare(
            "INSERT INTO tarifa_base_rol (rol_codigo, monto, fecha_vigencia) VALUES (:rol, :monto, :fecha)"
        )->execute(['rol' => $rol, 'monto' => $monto, 'fecha' => $fecha]);
        $this->success('Tarifa agregada.');
    }

    /** POST /admin/api/tarifa-base/{id}/eliminar */
    public function eliminarTarifaBase(int $id): void
    {
        $this->middlewareAdmin();
        require_once __DIR__ . '/../Core/Database.php';
        $db = \Database::getConnection();
        $check = $db->prepare("SELECT rol_codigo, fecha_vigencia FROM tarifa_base_rol WHERE id = :id");
        $check->execute(['id' => $id]);
        $row = $check->fetch();
        if (!$row) { $this->error('No encontrado', 404); return; }
        $count = $db->prepare("SELECT COUNT(*) FROM tarifa_base_rol WHERE rol_codigo = :rol");
        $count->execute(['rol' => $row['rol_codigo']]);
        if ((int)$count->fetchColumn() <= 1) { $this->error('No se puede eliminar la única tarifa de este rol.', 400); return; }
        $db->prepare("DELETE FROM tarifa_base_rol WHERE id = :id")->execute(['id' => $id]);
        $this->success('Tarifa eliminada.');
    }

    /** POST /admin/api/bono/agregar */
    public function addBono(): void
    {
        $this->middlewareAdmin();
        $data  = $this->getAllInput();
        $tipo  = $data['tipo']           ?? '';
        $desde = (float)($data['desde'] ?? 0);
        $hasta = ($data['hasta'] !== '' && $data['hasta'] !== null) ? (float)$data['hasta'] : null;
        $bono  = (float)($data['monto_bono']      ?? 0);
        $fecha = $data['fecha_vigencia'] ?? '';
        if (!in_array($tipo, ['VENTAS','OPERACIONES_BCP']) || $desde < 0 || $bono < 0 || !$fecha) {
            $this->error('Datos inválidos', 422); return;
        }
        require_once __DIR__ . '/../Core/Database.php';
        \Database::getConnection()->prepare(
            "INSERT INTO configuracion_bono (tipo, desde, hasta, monto_bono, fecha_vigencia)
             VALUES (:tipo, :desde, :hasta, :bono, :fecha)"
        )->execute(['tipo'=>$tipo,'desde'=>$desde,'hasta'=>$hasta,'bono'=>$bono,'fecha'=>$fecha]);
        $this->success('Rango de bono agregado.');
    }

    /** POST /admin/api/bono/{id}/eliminar */
    public function eliminarBono(int $id): void
    {
        $this->middlewareAdmin();
        require_once __DIR__ . '/../Core/Database.php';
        $db = \Database::getConnection();
        $check = $db->prepare("SELECT id FROM configuracion_bono WHERE id = :id");
        $check->execute(['id' => $id]);
        if (!$check->fetch()) { $this->error('No encontrado', 404); return; }
        $db->prepare("DELETE FROM configuracion_bono WHERE id = :id")->execute(['id' => $id]);
        $this->success('Rango eliminado.');
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
