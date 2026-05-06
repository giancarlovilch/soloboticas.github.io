<?php

require_once __DIR__ . '/../Core/Controller.php';
require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';

class ReporteController extends Controller
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    private function requireAdmin(): void
    {
        if (!isset($_SESSION['user_id']) || ($_SESSION['user_rol'] ?? '') !== 'ADMIN') {
            header('Location: ' . APP_BASE_PATH . '/login');
            exit;
        }
    }

    // ── GET /admin/reportes ────────────────────────────────
    public function index(): void
    {
        $this->requireAdmin();
        $basePath  = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
        $userName  = $_SESSION['user_name'] ?? 'Administrador';
        require_once __DIR__ . '/../../views/admin/reportes/index.php';
    }

    // ── GET /admin/reportes/arqueos ────────────────────────
    public function arqueos(): void
    {
        $this->requireAdmin();
        $basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
        $userName = $_SESSION['user_name'] ?? 'Administrador';

        $db = Database::getConnection();

        // Filtros
        $filtroLocal     = isset($_GET['local'])     ? (int)$_GET['local']    : 0;
        $filtroResultado = $_GET['resultado']        ?? '';
        $filtroDesde     = $_GET['desde']            ?? date('Y-m-01');
        $filtroHasta     = $_GET['hasta']            ?? date('Y-m-d');

        $where  = "sc.estado IN ('CERRADA','APROBADA')";
        $params = [];

        if ($filtroLocal > 0) {
            $where .= " AND l.id_local = :lid";
            $params['lid'] = $filtroLocal;
        }
        if ($filtroResultado !== '') {
            $where .= " AND dc.resultado_cuadre = :res";
            $params['res'] = $filtroResultado;
        }
        if ($filtroDesde) {
            $where .= " AND sc.fecha_operacion >= :desde";
            $params['desde'] = $filtroDesde;
        }
        if ($filtroHasta) {
            $where .= " AND sc.fecha_operacion <= :hasta";
            $params['hasta'] = $filtroHasta;
        }

        $sql = "SELECT
                    sc.id_sesion,
                    sc.fecha_operacion,
                    sc.fecha_apertura,
                    sc.turno_id,
                    c.descripcion  AS caja_desc,
                    l.descripcion  AS local_desc,
                    l.id_local,
                    t.descripcion  AS turno_desc,
                    p.nombres      AS cajera_nombre,
                    pv.nombres     AS vendedor_nombre,
                    dc.resultado_cuadre,
                    dc.diferencia,
                    dc.total_efectivo_contado  AS lo_que_es,
                    dc.total_ventas_sistema    AS ventas,
                    dc.total_gastos_sistema    AS gastos,
                    dc.saldo_proximo_dia       AS base_siguiente
                FROM sesion_caja sc
                INNER JOIN caja c      ON sc.caja_id              = c.id_caja
                INNER JOIN local l     ON c.local_id               = l.id_local
                INNER JOIN turno t     ON sc.turno_id              = t.id_turno
                INNER JOIN postulante p ON sc.postulante_apertura_id = p.id_postulante
                LEFT JOIN sesion_participante sp
                    ON sp.sesion_id = sc.id_sesion AND sp.rol_participacion = 'VENDEDORA'
                LEFT JOIN postulante pv ON sp.postulante_id = pv.id_postulante
                LEFT JOIN detalle_cuadre dc ON dc.sesion_id = sc.id_sesion
                WHERE {$where}
                ORDER BY sc.fecha_operacion DESC, sc.id_sesion DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $registros = $stmt->fetchAll();

        // Catálogos para los filtros
        $locales = $db->query(
            "SELECT id_local AS id, descripcion FROM local WHERE activo = 1 ORDER BY descripcion"
        )->fetchAll();

        // Totales rápidos
        $totalFaltante  = 0;
        $totalSobrante  = 0;
        $countFaltante  = 0;
        $countSobrante  = 0;
        $countConforme  = 0;
        foreach ($registros as $r) {
            $dif = (float)($r['diferencia'] ?? 0);
            if (abs($dif) < 0.01)      $countConforme++;
            elseif ($dif < 0) { $countFaltante++; $totalFaltante += abs($dif); }
            else               { $countSobrante++; $totalSobrante += $dif; }
        }

        require_once __DIR__ . '/../../views/admin/reportes/arqueos.php';
    }
}
