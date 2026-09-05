<?php

require_once __DIR__ . '/../Core/Controller.php';
require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Helpers/Response.php';

class VentaEmergenciaController extends Controller
{
    private PDO $db;

    private const LOCALES_VALIDOS = [2, 3, 4];

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->db = Database::getConnection();
    }

    // Mismo patrón de auth que CajaController: Bearer o sesión PHP.
    private function requireAuth(): int
    {
        $auth = '';
        if (function_exists('getallheaders')) {
            $h    = getallheaders();
            $auth = $h['Authorization'] ?? $h['authorization'] ?? '';
        }
        if (empty($auth)) $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (str_starts_with($auth, 'Bearer ')) {
            require_once __DIR__ . '/../Middleware/AuthMiddleware.php';
            $payload = AuthMiddleware::requireAuth();
            return (int) ($payload['sub'] ?? 0);
        }

        if (!isset($_SESSION['user_id'])) {
            $isApi = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
                  || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
            if ($isApi) {
                $this->error('No autenticado', 401);
                exit;
            }
            header('Location: ' . APP_BASE_PATH . '/login');
            exit;
        }
        return (int) $_SESSION['user_id'];
    }

    private function requireAdmin(): int
    {
        $id = $this->requireAuth();
        if (($_SESSION['user_rol'] ?? '') !== 'ADMIN') {
            $this->error('Solo el administrador puede acceder a esta sección', 403);
            exit;
        }
        return $id;
    }

    // ---------------------------------------------------------------
    // GET /ventas-emergencia
    // Página de registro: selector de local + búsqueda de producto + carrito.
    // ---------------------------------------------------------------
    public function registrarView(): void
    {
        $this->requireAuth();
        $basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
        $userName = $_SESSION['user_name'] ?? 'Usuario';
        $userRol  = $_SESSION['user_rol']  ?? 'STAFF';

        require_once __DIR__ . '/../../views/ventas_emergencia/registrar.php';
    }

    // ---------------------------------------------------------------
    // GET /ventas-emergencia/historial
    // Listado de tickets ya registrados, para pasar al ERP manualmente.
    // ---------------------------------------------------------------
    public function historialView(): void
    {
        $miId         = $this->requireAuth();
        $basePath     = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
        $userName     = $_SESSION['user_name'] ?? 'Usuario';
        $userRol      = $_SESSION['user_rol']  ?? 'STAFF';

        $filtroLocal = isset($_GET['local']) ? (int) $_GET['local'] : 0;
        $filtroFecha = $_GET['fecha'] ?? date('Y-m-d');

        $where  = [];
        $params = [];

        if ($filtroLocal) {
            $where[] = 've.id_local = :local';
            $params[':local'] = $filtroLocal;
        }
        if ($filtroFecha) {
            $where[] = 'DATE(ve.creado_en) = :fecha';
            $params[':fecha'] = $filtroFecha;
        }
        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        $stmt = $this->db->prepare("
            SELECT ve.id, ve.id_local, l.descripcion AS local_nombre,
                   ve.postulante_vendedor_id, p.nombres AS vendedor_nombre,
                   ve.total, ve.estado, ve.descargado_en, ve.creado_en
            FROM venta_emergencia ve
            INNER JOIN local l ON l.id_local = ve.id_local
            INNER JOIN postulante p ON p.id_postulante = ve.postulante_vendedor_id
            $whereSql
            ORDER BY ve.creado_en DESC
        ");
        $stmt->execute($params);
        $ventas = $stmt->fetchAll();

        $detalles = [];
        if ($ventas) {
            $ids = array_column($ventas, 'id');
            $in  = implode(',', array_fill(0, count($ids), '?'));
            $stmtDet = $this->db->prepare("
                SELECT venta_id, cod_producto, nombre_producto, precio_venta, cantidad, subtotal
                FROM venta_emergencia_detalle
                WHERE venta_id IN ($in)
                ORDER BY id ASC
            ");
            $stmtDet->execute($ids);
            foreach ($stmtDet->fetchAll() as $d) {
                $detalles[$d['venta_id']][] = $d;
            }
        }

        require_once __DIR__ . '/../../views/ventas_emergencia/historial.php';
    }

    // ---------------------------------------------------------------
    // GET /ventas-emergencia/api/productos?id_local=&q=
    // ---------------------------------------------------------------
    public function apiBuscarProductos(): void
    {
        $this->requireAuth();

        $idLocal = (int) ($_GET['id_local'] ?? 0);
        $q       = trim((string) ($_GET['q'] ?? ''));

        if (!in_array($idLocal, self::LOCALES_VALIDOS, true)) {
            $this->validationError('id_local inválido');
        }
        if (mb_strlen($q) < 2) {
            $this->success('OK', ['productos' => []]);
            return;
        }

        // precio_piso: el costo real, salvo que esté en 0 (dato no confiable en Softpharma
        // para productos sin compras registradas) — en ese caso el piso es el precio de catálogo.
        $stmt = $this->db->prepare("
            SELECT cod_producto, nombre_producto, precio_venta, stock,
                   CASE WHEN precio_costo > 0 THEN precio_costo ELSE precio_venta END AS precio_piso
            FROM producto_referencia
            WHERE id_local = :id_local
              AND (cod_producto LIKE :q1 OR nombre_producto LIKE :q2)
            ORDER BY (stock > 0) DESC, nombre_producto ASC
            LIMIT 20
        ");
        $like = '%' . $q . '%';
        $stmt->execute([
            ':id_local' => $idLocal,
            ':q1'       => $like,
            ':q2'       => $like,
        ]);

        $this->success('OK', ['productos' => $stmt->fetchAll()]);
    }

    // ---------------------------------------------------------------
    // POST /ventas-emergencia/api/registrar
    // Body: { id_local, items: [ { cod_producto, nombre_producto, precio_venta, cantidad } ] }
    // ---------------------------------------------------------------
    public function apiRegistrar(): void
    {
        $vendedorId = $this->requireAuth();

        $d       = $this->getJsonInput();
        $idLocal = (int) ($d['id_local'] ?? 0);
        $items   = $d['items'] ?? [];

        if (!in_array($idLocal, self::LOCALES_VALIDOS, true)) {
            $this->validationError('id_local inválido');
        }
        if (empty($items) || !is_array($items)) {
            $this->validationError('El ticket necesita al menos un producto');
        }

        $lineas = [];
        $total  = 0.0;

        foreach ($items as $it) {
            $cod = trim((string) ($it['cod_producto'] ?? ''));
            $cant = (float) ($it['cantidad'] ?? 0);
            $precio = (float) ($it['precio_venta'] ?? 0);

            if ($cod === '' || $cant <= 0) {
                continue;
            }

            $subtotal = round($precio * $cant, 2);
            $total += $subtotal;

            $lineas[] = [
                'cod_producto'    => $cod,
                'nombre_producto' => (string) ($it['nombre_producto'] ?? ''),
                'precio_venta'    => $precio,
                'cantidad'        => $cant,
                'subtotal'        => $subtotal,
            ];
        }

        if (empty($lineas)) {
            $this->validationError('El ticket necesita al menos un producto válido');
        }

        // Validación server-side: no se puede vender por debajo del precio de compra.
        // Ojo: varios productos de Softpharma tienen precio_costo = 0 (dato no confiable,
        // nunca se les registró una compra). En esos casos el piso pasa a ser el precio
        // de catálogo (precio_venta de producto_referencia), para no permitir descuentos
        // sobre un costo que en realidad no se conoce.
        $codigos = array_column($lineas, 'cod_producto');
        $in      = implode(',', array_fill(0, count($codigos), '?'));
        $stmtCostos = $this->db->prepare("
            SELECT cod_producto, precio_costo, precio_venta AS precio_catalogo
            FROM producto_referencia
            WHERE id_local = ? AND cod_producto IN ($in)
        ");
        $stmtCostos->execute(array_merge([$idLocal], $codigos));

        $pisos = [];
        foreach ($stmtCostos->fetchAll() as $row) {
            $costo = (float) $row['precio_costo'];
            $pisos[$row['cod_producto']] = $costo > 0 ? $costo : (float) $row['precio_catalogo'];
        }

        foreach ($lineas as $l) {
            $piso = $pisos[$l['cod_producto']] ?? null;
            if ($piso !== null && $l['precio_venta'] < $piso) {
                $this->validationError(
                    "El precio de \"{$l['nombre_producto']}\" (S/ {$l['precio_venta']}) no puede ser menor a S/ {$piso}"
                );
            }
        }

        $this->db->beginTransaction();

        try {
            $stmtVenta = $this->db->prepare("
                INSERT INTO venta_emergencia (id_local, postulante_vendedor_id, total, creado_en)
                VALUES (:id_local, :vendedor_id, :total, NOW())
            ");
            $stmtVenta->execute([
                ':id_local'    => $idLocal,
                ':vendedor_id' => $vendedorId,
                ':total'       => round($total, 2),
            ]);
            $ventaId = (int) $this->db->lastInsertId();

            $stmtDetalle = $this->db->prepare("
                INSERT INTO venta_emergencia_detalle
                    (venta_id, cod_producto, nombre_producto, precio_venta, cantidad, subtotal)
                VALUES
                    (:venta_id, :cod_producto, :nombre_producto, :precio_venta, :cantidad, :subtotal)
            ");
            foreach ($lineas as $l) {
                $stmtDetalle->execute([
                    ':venta_id'        => $ventaId,
                    ':cod_producto'    => $l['cod_producto'],
                    ':nombre_producto' => $l['nombre_producto'],
                    ':precio_venta'    => $l['precio_venta'],
                    ':cantidad'        => $l['cantidad'],
                    ':subtotal'        => $l['subtotal'],
                ]);
            }

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            Response::error('Error al registrar la venta: ' . $e->getMessage(), 500);
        }

        $this->created('Venta registrada', ['id' => $ventaId, 'total' => round($total, 2)]);
    }

    // ---------------------------------------------------------------
    // POST /ventas-emergencia/api/{id}/marcar-descargada
    // Alterna si ya se pasó manualmente al ERP.
    // ---------------------------------------------------------------
    public function apiMarcarDescargada(int $id): void
    {
        $this->requireAuth();

        $stmt = $this->db->prepare('SELECT descargado_en FROM venta_emergencia WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $venta = $stmt->fetch();

        if (!$venta) {
            $this->notFound('Venta no encontrada');
        }

        $nuevoValor = $venta['descargado_en'] ? null : date('Y-m-d H:i:s');

        $upd = $this->db->prepare('UPDATE venta_emergencia SET descargado_en = :v WHERE id = :id');
        $upd->execute([':v' => $nuevoValor, ':id' => $id]);

        $this->success('Actualizado', ['descargado_en' => $nuevoValor]);
    }

    // ---------------------------------------------------------------
    // POST /ventas-emergencia/api/{id}/anular
    // Anulación "suave" (no se borra, queda trazabilidad): solo el propio
    // vendedor de esa venta o un ADMIN pueden anular. No revierte — para
    // reactivar una anulada hace falta ser ADMIN (ver apiReactivar).
    // ---------------------------------------------------------------
    public function apiAnular(int $id): void
    {
        $miId  = $this->requireAuth();
        $admin = ($_SESSION['user_rol'] ?? '') === 'ADMIN';

        $stmt = $this->db->prepare('SELECT postulante_vendedor_id, estado FROM venta_emergencia WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $venta = $stmt->fetch();

        if (!$venta) {
            $this->notFound('Venta no encontrada');
        }
        if (!$admin && (int) $venta['postulante_vendedor_id'] !== $miId) {
            $this->forbidden('Solo puedes anular tus propias ventas');
        }
        if ($venta['estado'] === 'ANULADA') {
            $this->validationError('Esta venta ya está anulada');
        }

        $upd = $this->db->prepare('
            UPDATE venta_emergencia
            SET estado = "ANULADA", anulado_en = NOW(), anulado_por_id = :yo
            WHERE id = :id
        ');
        $upd->execute([':yo' => $miId, ':id' => $id]);

        $this->success('Venta anulada');
    }

    // ---------------------------------------------------------------
    // GET /admin/ventas-emergencia
    // Resumen para el admin: todas las ventas, filtrables por trabajador y fecha.
    // ---------------------------------------------------------------
    public function adminResumenView(): void
    {
        $this->requireAdmin();
        $basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
        $userName = $_SESSION['user_name'] ?? 'Usuario';
        $userRol  = $_SESSION['user_rol']  ?? 'ADMIN';

        $filtroLocal      = isset($_GET['local']) ? (int) $_GET['local'] : 0;
        $filtroVendedorId = isset($_GET['vendedor']) ? (int) $_GET['vendedor'] : 0;
        $filtroEstado     = $_GET['estado'] ?? '';
        $filtroDesde      = $_GET['desde'] ?? date('Y-m-01');
        $filtroHasta      = $_GET['hasta'] ?? date('Y-m-d');

        $where  = ['DATE(ve.creado_en) BETWEEN :desde AND :hasta'];
        $params = [':desde' => $filtroDesde, ':hasta' => $filtroHasta];

        if ($filtroLocal) {
            $where[] = 've.id_local = :local';
            $params[':local'] = $filtroLocal;
        }
        if ($filtroVendedorId) {
            $where[] = 've.postulante_vendedor_id = :vendedor';
            $params[':vendedor'] = $filtroVendedorId;
        }
        if (in_array($filtroEstado, ['REGISTRADA', 'ANULADA'], true)) {
            $where[] = 've.estado = :estado';
            $params[':estado'] = $filtroEstado;
        }
        $whereSql = 'WHERE ' . implode(' AND ', $where);

        $stmt = $this->db->prepare("
            SELECT ve.id, ve.id_local, l.descripcion AS local_nombre,
                   ve.postulante_vendedor_id, p.nombres AS vendedor_nombre,
                   ve.total, ve.estado, ve.descargado_en, ve.anulado_en, ve.creado_en
            FROM venta_emergencia ve
            INNER JOIN local l ON l.id_local = ve.id_local
            INNER JOIN postulante p ON p.id_postulante = ve.postulante_vendedor_id
            $whereSql
            ORDER BY ve.creado_en DESC
        ");
        $stmt->execute($params);
        $ventas = $stmt->fetchAll();

        // Lista de vendedores para el <select> de filtro (solo quienes ya tienen ventas registradas).
        $vendedores = $this->db->query("
            SELECT DISTINCT p.id_postulante, p.nombres
            FROM venta_emergencia ve
            INNER JOIN postulante p ON p.id_postulante = ve.postulante_vendedor_id
            ORDER BY p.nombres ASC
        ")->fetchAll();

        require_once __DIR__ . '/../../views/ventas_emergencia/admin_resumen.php';
    }

    // ---------------------------------------------------------------
    // POST /admin/ventas-emergencia/api/{id}/reactivar
    // Solo ADMIN: revierte una venta anulada de vuelta a REGISTRADA.
    // ---------------------------------------------------------------
    public function apiReactivar(int $id): void
    {
        $this->requireAdmin();

        $stmt = $this->db->prepare('SELECT estado FROM venta_emergencia WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $venta = $stmt->fetch();

        if (!$venta) {
            $this->notFound('Venta no encontrada');
        }

        $upd = $this->db->prepare('
            UPDATE venta_emergencia
            SET estado = "REGISTRADA", anulado_en = NULL, anulado_por_id = NULL
            WHERE id = :id
        ');
        $upd->execute([':id' => $id]);

        $this->success('Venta reactivada');
    }

    // ---------------------------------------------------------------
    // POST /admin/ventas-emergencia/api/{id}/eliminar
    // Solo ADMIN: borrado real (el detalle se va en cascada).
    // ---------------------------------------------------------------
    public function apiEliminar(int $id): void
    {
        $this->requireAdmin();

        $del = $this->db->prepare('DELETE FROM venta_emergencia WHERE id = :id');
        $del->execute([':id' => $id]);

        if ($del->rowCount() === 0) {
            $this->notFound('Venta no encontrada');
        }

        $this->success('Venta eliminada');
    }

    // ---------------------------------------------------------------
    // GET /ventas-emergencia/{id}/imprimir
    // Nota de venta imprimible (formato angosto, pensado para impresora térmica 80mm).
    // ---------------------------------------------------------------
    public function imprimirView(int $id): void
    {
        $this->requireAuth();
        $basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';

        $stmt = $this->db->prepare("
            SELECT ve.id, ve.id_local, l.descripcion AS local_nombre,
                   p.nombres AS vendedor_nombre, ve.total, ve.creado_en
            FROM venta_emergencia ve
            INNER JOIN local l ON l.id_local = ve.id_local
            INNER JOIN postulante p ON p.id_postulante = ve.postulante_vendedor_id
            WHERE ve.id = :id
        ");
        $stmt->execute([':id' => $id]);
        $venta = $stmt->fetch();

        if (!$venta) {
            $this->notFound('Venta no encontrada');
        }

        $stmtDet = $this->db->prepare("
            SELECT cod_producto, nombre_producto, precio_venta, cantidad, subtotal
            FROM venta_emergencia_detalle
            WHERE venta_id = :id
            ORDER BY id ASC
        ");
        $stmtDet->execute([':id' => $id]);
        $items = $stmtDet->fetchAll();

        require_once __DIR__ . '/../../views/ventas_emergencia/imprimir.php';
    }
}
