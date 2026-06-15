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

    // ── GET /admin/reportes/graficas ───────────────────────
    public function graficas(): void
    {
        $this->requireAdmin();
        $basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
        $userName = $_SESSION['user_name'] ?? 'Administrador';

        $db    = Database::getConnection();
        $hasta = $_GET['hasta'] ?? date('Y-m-d');
        $desde = $_GET['desde'] ?? date('Y-m-d', strtotime('-13 days', strtotime($hasta)));

        $colors = ['#0097A7','#f59e0b','#10b981','#8b5cf6','#ef4444','#3b82f6','#ec4899','#f97316','#84cc16'];

        // ── Operaciones BCP por cajera ───────────────────
        $stmtOps = $db->prepare(
            "SELECT sc.fecha_operacion AS fecha,
                    p.nombres AS cajera,
                    SUM(dc.num_operaciones_bcp) AS ops
             FROM sesion_caja sc
             INNER JOIN detalle_cuadre dc ON dc.sesion_id = sc.id_sesion
             INNER JOIN postulante p ON p.id_postulante = sc.postulante_apertura_id
             WHERE sc.fecha_operacion BETWEEN :desde AND :hasta
               AND sc.estado IN ('CERRADA','EN_REVISION','APROBADA','OBSERVADA','RECHAZADA')
             GROUP BY sc.fecha_operacion, sc.postulante_apertura_id
             ORDER BY sc.fecha_operacion ASC, p.nombres ASC"
        );
        $stmtOps->execute(['desde' => $desde, 'hasta' => $hasta]);

        $opsByDate = [];
        $cajerasSet = [];
        foreach ($stmtOps->fetchAll() as $r) {
            $opsByDate[$r['fecha']][$r['cajera']] = (int)$r['ops'];
            $cajerasSet[$r['cajera']] = true;
        }
        $cajeras = array_keys($cajerasSet);
        sort($cajeras);
        $opsDateLabels = array_keys($opsByDate);
        sort($opsDateLabels);

        $opsDatasets = [];
        foreach ($cajeras as $i => $cajera) {
            $data = [];
            foreach ($opsDateLabels as $f) {
                $data[] = $opsByDate[$f][$cajera] ?? 0;
            }
            $c = $colors[$i % count($colors)];
            $opsDatasets[] = ['label' => $cajera, 'data' => $data,
                'borderColor' => $c, 'backgroundColor' => $c . '22', 'tension' => 0.3, 'pointRadius' => 3];
        }
        $opsTotal = [];
        foreach ($opsDateLabels as $f) {
            $opsTotal[] = array_sum($opsByDate[$f]);
        }
        array_unshift($opsDatasets, ['label' => 'Total', 'data' => $opsTotal,
            'borderColor' => '#1e293b', 'backgroundColor' => '#1e293b18',
            'tension' => 0.3, 'pointRadius' => 3, 'borderWidth' => 2.5]);

        // ── Ventas diarias por caja ──────────────────────
        $stmtV = $db->prepare(
            "SELECT sc.fecha_operacion AS fecha,
                    ca.descripcion AS caja,
                    COALESCE(SUM(rv.monto), 0) AS ventas
             FROM sesion_caja sc
             INNER JOIN caja ca ON ca.id_caja = sc.caja_id
             LEFT JOIN reporte_venta rv ON rv.sesion_id = sc.id_sesion
             WHERE sc.fecha_operacion BETWEEN :desde AND :hasta
               AND sc.estado IN ('CERRADA','EN_REVISION','APROBADA','OBSERVADA','RECHAZADA')
             GROUP BY sc.fecha_operacion, sc.caja_id
             ORDER BY sc.fecha_operacion ASC, ca.descripcion ASC"
        );
        $stmtV->execute(['desde' => $desde, 'hasta' => $hasta]);

        $ventasByDate = [];
        $cajasSet = [];
        foreach ($stmtV->fetchAll() as $r) {
            $ventasByDate[$r['fecha']][$r['caja']] = (float)$r['ventas'];
            $cajasSet[$r['caja']] = true;
        }
        $cajas = array_keys($cajasSet);
        sort($cajas);
        $ventasDateLabels = array_keys($ventasByDate);
        sort($ventasDateLabels);

        $ventasDatasets = [];
        foreach ($cajas as $i => $caja) {
            $data = [];
            foreach ($ventasDateLabels as $f) {
                $data[] = round($ventasByDate[$f][$caja] ?? 0, 2);
            }
            $c = $colors[$i % count($colors)];
            $ventasDatasets[] = ['label' => $caja, 'data' => $data,
                'borderColor' => $c, 'backgroundColor' => $c . '22', 'tension' => 0.3, 'pointRadius' => 3];
        }
        $ventasTotal = [];
        foreach ($ventasDateLabels as $f) {
            $ventasTotal[] = round(array_sum($ventasByDate[$f]), 2);
        }
        array_unshift($ventasDatasets, ['label' => 'Total', 'data' => $ventasTotal,
            'borderColor' => '#1e293b', 'backgroundColor' => '#1e293b18',
            'tension' => 0.3, 'pointRadius' => 3, 'borderWidth' => 2.5]);

        // ── KPIs rápidos ─────────────────────────────────
        $totalOps    = array_sum($opsTotal);
        $promOps     = count($opsDateLabels) ? round($totalOps / count($opsDateLabels)) : 0;
        $totalVentas = array_sum($ventasTotal);
        $promVentas  = count($ventasDateLabels) ? round($totalVentas / count($ventasDateLabels), 2) : 0;

        require_once __DIR__ . '/../../views/admin/reportes/graficas.php';
    }

    // ── GET /admin/reportes/arqueos ────────────────────────
    public function arqueos(): void
    {
        $this->requireAdmin();
        $basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
        $userName = $_SESSION['user_name'] ?? 'Administrador';

        $db = Database::getConnection();

        // Filtros
        $filtroCaja      = isset($_GET['caja'])      ? (int)$_GET['caja']      : 0;
        $filtroResultado = $_GET['resultado']        ?? '';
        $filtroDesde     = $_GET['desde']            ?? date('Y-m-01');
        $filtroHasta     = $_GET['hasta']            ?? date('Y-m-d');

        $where  = "sc.estado IN ('CERRADA','APROBADA')";
        $params = [];

        if ($filtroCaja > 0) {
            $where .= " AND sc.caja_id = :cid";
            $params['cid'] = $filtroCaja;
        }
        if ($filtroResultado === 'SUPERAVIT') {
            $where .= " AND dc.resultado_cuadre IN ('CONSISTENTE','SOBRANTE')";
        } elseif ($filtroResultado === 'DEFICIT') {
            $where .= " AND dc.resultado_cuadre = 'FALTANTE'";
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
                    (
                        COALESCE(dc.total_efectivo_contado, 0) - (
                            COALESCE(sc.saldo_inicial, 0)
                            + COALESCE(dc.total_ventas_sistema, 0)
                            - COALESCE(dc.total_gastos_sistema, 0)
                            - COALESCE((
                                SELECT SUM(ms.monto)
                                FROM movimiento_sesion ms
                                WHERE ms.sesion_id = sc.id_sesion
                                  AND ms.tipo_movimiento_id = 1
                                  AND ms.estado IN ('PENDIENTE','APROBADO')
                            ), 0)
                        )
                    ) AS diferencia,
                    dc.total_efectivo_contado  AS lo_que_es,
                    dc.total_ventas_sistema    AS ventas,
                    dc.total_gastos_sistema    AS gastos,
                    dc.saldo_proximo_dia       AS base_siguiente,
                    sc.comentario_cajera,
                    sc.respuesta_admin,
                    (sc.comentario_cajera IS NOT NULL AND sc.respuesta_admin IS NULL) AS por_responder,
                    COALESCE((
                        SELECT SUM(rc.monto)
                        FROM rectificacion_cuadre rc
                        WHERE rc.sesion_id = sc.id_sesion
                    ), 0) AS sum_rectifs,
                    COALESCE((
                        SELECT SUM(CASE WHEN ae.accion = 'AGREGAR' THEN ae.monto ELSE -ae.monto END)
                        FROM ajuste_esperado ae
                        WHERE ae.sesion_id = sc.id_sesion
                    ), 0) AS sum_ajustes,
                    COALESCE((
                        SELECT SUM(cv.monto_nuevo - cv.monto_anterior)
                        FROM correccion_venta cv
                        WHERE cv.sesion_id = sc.id_sesion
                    ), 0) AS sum_corr_ventas
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

        // Catálogo de cajas para el filtro
        $cajas = $db->query(
            "SELECT c.id_caja AS id, CONCAT(l.descripcion, ' — ', c.descripcion) AS descripcion
             FROM caja c INNER JOIN local l ON c.local_id = l.id_local
             WHERE c.activo = 1 ORDER BY l.descripcion, c.descripcion"
        )->fetchAll();

        // Totales basados en difCorr (valor real tras rectificaciones y correcciones)
        $totalFaltante = 0;
        $totalSobrante = 0;
        $countFaltante = 0;
        $countSobrante = 0;
        foreach ($registros as $r) {
            $difCorr = (float)($r['diferencia']      ?? 0)
                     + (float)($r['sum_rectifs']      ?? 0)
                     + (float)($r['sum_ajustes']      ?? 0)
                     - (float)($r['sum_corr_ventas']  ?? 0);
            if ($difCorr < -0.01)     { $countFaltante++; $totalFaltante += abs($difCorr); }
            elseif ($difCorr > 0.01)  { $countSobrante++; $totalSobrante += $difCorr; }
        }

        require_once __DIR__ . '/../../views/admin/reportes/arqueos.php';
    }

    // ── GET /admin/reportes/coberturas ─────────────────────
    public function coberturas(): void
    {
        $this->requireAdmin();
        $basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
        $userName = $_SESSION['user_name'] ?? 'Administrador';
        $db       = Database::getConnection();

        $filtroMes   = $_GET['mes']   ?? date('Y-m');
        $filtroLocal = isset($_GET['local']) ? (int)$_GET['local'] : 0;

        [$anio, $nmes] = explode('-', $filtroMes);
        $desde = "{$anio}-{$nmes}-01";
        $hasta = date('Y-m-t', strtotime($desde));

        $whereLocal = $filtroLocal > 0 ? 'AND hs.local_id = :lid' : '';

        // Status 3: coberturas DADAS por cada persona
        $sql3 = "SELECT sc.postulante_solicitante_id AS postulante_id,
                        COUNT(*) AS coberturas_dadas
                 FROM solicitud_cambio sc
                 INNER JOIN horario_slot hs ON sc.slot_id = hs.id_slot
                 WHERE sc.tipo = 'COBERTURA' AND sc.estado = 'ACTIVA'
                   AND hs.fecha_dia BETWEEN :desde AND :hasta
                   {$whereLocal}
                 GROUP BY sc.postulante_solicitante_id";

        // Status 2: veces REEMPLAZADO (solo coberturas activas)
        $sql2 = "SELECT sc.postulante_original_id AS postulante_id,
                        COUNT(*) AS veces_reemplazado
                 FROM solicitud_cambio sc
                 INNER JOIN horario_slot hs ON sc.slot_id = hs.id_slot
                 WHERE sc.tipo = 'COBERTURA' AND sc.estado = 'ACTIVA'
                   AND sc.postulante_original_id IS NOT NULL
                   AND hs.fecha_dia BETWEEN :desde AND :hasta
                   {$whereLocal}
                 GROUP BY sc.postulante_original_id";

        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($filtroLocal > 0) $params['lid'] = $filtroLocal;

        $stmt3 = $db->prepare($sql3); $stmt3->execute($params);
        $coberturas = array_column($stmt3->fetchAll(), 'coberturas_dadas', 'postulante_id');

        $stmt2 = $db->prepare($sql2); $stmt2->execute($params);
        $reemplazos = array_column($stmt2->fetchAll(), 'veces_reemplazado', 'postulante_id');

        // Todos los trabajadores activos
        $trabajadores = $db->query(
            "SELECT p.id_postulante, p.nombres
             FROM postulante p INNER JOIN usuario u ON u.postulante_id = p.id_postulante
             WHERE u.activo = 1 ORDER BY p.nombres"
        )->fetchAll();

        // Detalle: eventos individuales del período
        $sqlDet = "SELECT sc.*,
                          ps.nombres AS quien_cubrió,
                          po.nombres AS a_quien_reemplazó,
                          hs.fecha_dia,
                          l.descripcion AS local_desc,
                          t.descripcion AS turno_desc,
                          rh.codigo AS rol_puesto
                   FROM solicitud_cambio sc
                   INNER JOIN horario_slot hs ON sc.slot_id = hs.id_slot
                   INNER JOIN local l  ON hs.local_id  = l.id_local
                   INNER JOIN turno t  ON hs.turno_id  = t.id_turno
                   INNER JOIN rol_horario rh ON hs.rol_horario_id = rh.id_rol_horario
                   INNER JOIN postulante ps ON sc.postulante_solicitante_id = ps.id_postulante
                   LEFT  JOIN postulante po ON sc.postulante_original_id    = po.id_postulante
                   WHERE sc.tipo = 'COBERTURA'
                     AND hs.fecha_dia BETWEEN :desde AND :hasta
                     {$whereLocal}
                   ORDER BY hs.fecha_dia DESC, sc.fecha_solicitud DESC";
        $stmtDet = $db->prepare($sqlDet); $stmtDet->execute($params);
        $detalle = $stmtDet->fetchAll();

        $locales = $db->query(
            "SELECT id_local AS id, descripcion FROM local WHERE activo = 1 ORDER BY descripcion"
        )->fetchAll();

        require_once __DIR__ . '/../../views/admin/reportes/coberturas.php';
    }

    // ── GET /admin/reportes/asistencias ────────────────────
    public function asistencias(): void
    {
        $this->requireAdmin();
        $basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
        $userName = $_SESSION['user_name'] ?? 'Administrador';
        $db       = Database::getConnection();

        $filtroMes        = $_GET['mes']        ?? date('Y-m');
        $filtroTrabajador = isset($_GET['trabajador']) ? (int)$_GET['trabajador'] : 0;

        [$anio, $nmes] = explode('-', $filtroMes);
        $desde = "{$anio}-{$nmes}-01";
        $hasta = date('Y-m-t', strtotime($desde));

        $where  = "a.fecha BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($filtroTrabajador > 0) {
            $where .= " AND a.postulante_id = :pid";
            $params['pid'] = $filtroTrabajador;
        }

        $registros = $db->prepare(
            "SELECT a.*,
                    CONCAT(p.nombres) AS trabajador_nombre,
                    l.descripcion     AS local_desc,
                    pr.nombres        AS registrado_por_nombre
             FROM asistencia a
             INNER JOIN postulante p  ON a.postulante_id      = p.id_postulante
             LEFT  JOIN local l       ON a.local_id           = l.id_local
             LEFT  JOIN postulante pr ON a.registrado_por_id  = pr.id_postulante
             WHERE {$where}
             ORDER BY a.fecha DESC, p.nombres ASC, a.turno_id ASC"
        );
        $registros->execute($params);
        $registros = $registros->fetchAll();

        // Checklists de todos los registros
        require_once __DIR__ . '/../Repositories/AsistenciaRepository.php';
        $asistRepo  = new AsistenciaRepository();
        $ids        = array_column($registros, 'id_asistencia');
        $checklists = $asistRepo->getChecklistPorIds($ids);

        // Resumen rápido
        $totalATiempo = 0; $totalTarde = 0; $totalFalta = 0; $totalOtro = 0;
        foreach ($registros as $r) {
            match($r['estado']) {
                'A TIEMPO' => $totalATiempo++,
                'TARDE'    => $totalTarde++,
                'FALTA'    => $totalFalta++,
                default    => $totalOtro++,
            };
        }

        $trabajadores = $db->query(
            "SELECT p.id_postulante AS id, p.nombres AS nombre
             FROM postulante p INNER JOIN usuario u ON u.postulante_id = p.id_postulante
             WHERE u.activo = 1 ORDER BY p.nombres"
        )->fetchAll();

        require_once __DIR__ . '/../../views/admin/reportes/asistencias.php';
    }

    // ── GET /admin/reportes/resumen-trabajadores ───────────
    public function resumenTrabajadores(): void
    {
        $this->requireAdmin();
        $basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
        $userName = $_SESSION['user_name'] ?? 'Administrador';
        $db       = Database::getConnection();

        $filtroMes = $_GET['mes'] ?? date('Y-m');
        [$anio, $nmes] = explode('-', $filtroMes);
        $desde = "{$anio}-{$nmes}-01";
        $hasta = date('Y-m-t', strtotime($desde));

        // 1. Todos los trabajadores activos
        $trabajadores = $db->query(
            "SELECT p.id_postulante AS id, p.nombres AS nombre
             FROM postulante p INNER JOIN usuario u ON u.postulante_id = p.id_postulante
             WHERE u.activo = 1 ORDER BY p.nombres"
        )->fetchAll();

        // 2. Resumen de asistencias
        $stmtA = $db->prepare(
            "SELECT postulante_id,
                    COUNT(*)                        AS total,
                    SUM(estado = 'A TIEMPO')        AS a_tiempo,
                    SUM(estado = 'TARDE')           AS tarde,
                    SUM(estado IN ('EXTRA','TEMPRANO')) AS extra,
                    SUM(estado = 'FALTA')           AS faltas
             FROM asistencia
             WHERE fecha BETWEEN :desde AND :hasta
             GROUP BY postulante_id"
        );
        $stmtA->execute(['desde' => $desde, 'hasta' => $hasta]);
        $asistMap = array_column($stmtA->fetchAll(), null, 'postulante_id');

        // 3. Ventas como vendedor/a
        $stmtV = $db->prepare(
            "SELECT sp.postulante_id,
                    COUNT(DISTINCT sc.id_sesion)  AS turnos_venta,
                    COALESCE(SUM(rv.monto), 0)    AS total_ventas
             FROM sesion_participante sp
             INNER JOIN sesion_caja sc   ON sc.id_sesion = sp.sesion_id
             INNER JOIN reporte_venta rv ON rv.sesion_id  = sc.id_sesion
             WHERE sp.rol_participacion = 'VENDEDORA'
               AND sc.fecha_operacion BETWEEN :desde AND :hasta
               AND sc.estado IN ('CERRADA','APROBADA')
             GROUP BY sp.postulante_id"
        );
        $stmtV->execute(['desde' => $desde, 'hasta' => $hasta]);
        $ventasMap = array_column($stmtV->fetchAll(), null, 'postulante_id');

        // 4. Limpiezas asignadas
        $stmtL = $db->prepare(
            "SELECT hs.postulante_id, COUNT(*) AS limpiezas
             FROM horario_slot hs
             INNER JOIN rol_horario rh ON hs.rol_horario_id = rh.id_rol_horario
             INNER JOIN semana s ON hs.semana_id = s.id_semana
             WHERE rh.codigo = 'LIMPIEZA'
               AND hs.postulante_id IS NOT NULL
               AND hs.fecha_dia BETWEEN :desde AND :hasta
             GROUP BY hs.postulante_id"
        );
        $stmtL->execute(['desde' => $desde, 'hasta' => $hasta]);
        $limpiezaMap = array_column($stmtL->fetchAll(), 'limpiezas', 'postulante_id');

        // 5. Operaciones BCP como cajera
        $stmtB = $db->prepare(
            "SELECT sc.postulante_apertura_id        AS postulante_id,
                    COUNT(DISTINCT sc.id_sesion)     AS turnos_caja,
                    COALESCE(SUM(dc.num_operaciones_bcp), 0) AS total_ops_bcp
             FROM sesion_caja sc
             INNER JOIN detalle_cuadre dc ON dc.sesion_id = sc.id_sesion
             WHERE sc.fecha_operacion BETWEEN :desde AND :hasta
               AND sc.estado IN ('CERRADA','APROBADA')
             GROUP BY sc.postulante_apertura_id"
        );
        $stmtB->execute(['desde' => $desde, 'hasta' => $hasta]);
        $bcpMap = array_column($stmtB->fetchAll(), null, 'postulante_id');

        // 6. Métricas de encuestas (nuevo sistema de fichas)
        $stmtE = $db->prepare(
            "SELECT postulante_id,
                    COUNT(*)                                          AS total_fichas,
                    SUM(calificacion_turno = 'EXCELENTE')            AS calif_excelente,
                    SUM(calificacion_turno = 'BUENO')                AS calif_bueno,
                    SUM(calificacion_turno = 'REGULAR')              AS calif_regular,
                    SUM(calificacion_turno = 'MALO')                 AS calif_malo,
                    SUM(llegada_puntualidad IN ('TARDE','MUY_TARDE')) AS llegadas_tarde,
                    SUM(salida_puntualidad  IN ('TARDE','MUY_TARDE')) AS salidas_tarde,
                    SUM(uso_celular = 'FRECUENTE')                   AS celular_frecuente,
                    SUM(aseo_personal IN ('DEFICIENTE'))             AS present_deficiente
             FROM asistencia
             WHERE fecha BETWEEN :desde AND :hasta
               AND estado != 'FALTA'
               AND (llegada_puntualidad IS NOT NULL OR salida_puntualidad IS NOT NULL)
             GROUP BY postulante_id"
        );
        $stmtE->execute(['desde' => $desde, 'hasta' => $hasta]);
        $encuestaMap = array_column($stmtE->fetchAll(), null, 'postulante_id');

        require_once __DIR__ . '/../../views/admin/reportes/resumen_trabajadores.php';
    }

    // ── GET /admin/reportes/gastos ─────────────────────────
    public function gastos(): void
    {
        $this->requireAdmin();
        $basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
        $userName = $_SESSION['user_name'] ?? 'Administrador';
        $db       = Database::getConnection();

        $mes     = $_GET['mes']   ?? date('Y-m');
        $localId = (isset($_GET['local']) && $_GET['local'] !== '') ? (int)$_GET['local'] : null;

        [$year, $month] = explode('-', $mes . '-01');
        $desde = "$year-$month-01";
        $hasta = "$year-$month-" . date('t', mktime(0, 0, 0, (int)$month, 1, (int)$year));

        $locales = $db->query("SELECT id_local, descripcion FROM local ORDER BY id_local")->fetchAll();

        $filtroLocal = $localId ? 'AND c.local_id = :lid' : '';
        $params      = $localId
            ? ['desde' => $desde, 'hasta' => $hasta, 'lid' => $localId]
            : ['desde' => $desde, 'hasta' => $hasta];

        $rows = [];

        // 1. Personal
        $s = $db->prepare("
            SELECT sc.fecha_operacion AS fecha,
                   l.id_local, l.descripcion AS local_desc,
                   'PERSONAL' AS categoria, pp.tipo_pago AS sub_categoria,
                   pb.nombres AS descripcion,
                   pp.monto
            FROM pago_personal pp
            JOIN sesion_caja sc ON sc.id_sesion = pp.sesion_id
            JOIN caja c         ON c.id_caja    = sc.caja_id
            JOIN local l        ON l.id_local   = c.local_id
            JOIN postulante pb  ON pb.id_postulante = pp.postulante_beneficiario_id
            WHERE pp.estado IN ('APROBADO','PAGADO','CONFIRMADO_BENEFICIARIO')
              AND sc.fecha_operacion BETWEEN :desde AND :hasta
              $filtroLocal
            ORDER BY sc.fecha_operacion");
        $s->execute($params);
        foreach ($s->fetchAll() as $r) $rows[] = $r;

        // 2. Local (alquiler, luz, agua, etc.)
        $s = $db->prepare("
            SELECT sc.fecha_operacion AS fecha,
                   l.id_local, l.descripcion AS local_desc,
                   'LOCAL' AS categoria,
                   COALESCE(cg.descripcion, 'Sin concepto') AS sub_categoria,
                   l2.descripcion AS descripcion,
                   pl.monto
            FROM pago_local pl
            JOIN sesion_caja sc         ON sc.id_sesion  = pl.sesion_id
            JOIN caja c                 ON c.id_caja     = sc.caja_id
            JOIN local l                ON l.id_local    = c.local_id
            JOIN local l2               ON l2.id_local   = pl.local_id
            LEFT JOIN concepto_gastos_local cg ON cg.id_concepto = pl.concepto_id
            WHERE pl.estado = 'APROBADO'
              AND sc.fecha_operacion BETWEEN :desde AND :hasta
              $filtroLocal
            ORDER BY sc.fecha_operacion");
        $s->execute($params);
        foreach ($s->fetchAll() as $r) $rows[] = $r;

        // 3. Otros (gastos libres)
        $s = $db->prepare("
            SELECT sc.fecha_operacion AS fecha,
                   l.id_local, l.descripcion AS local_desc,
                   'LIBRE' AS categoria,
                   NULL AS sub_categoria,
                   ms.descripcion,
                   ms.monto
            FROM movimiento_sesion ms
            JOIN sesion_caja sc ON sc.id_sesion = ms.sesion_id
            JOIN caja c         ON c.id_caja    = sc.caja_id
            JOIN local l        ON l.id_local   = c.local_id
            WHERE ms.tipo_movimiento_id = 2 AND ms.estado = 'APROBADO'
              AND sc.fecha_operacion BETWEEN :desde AND :hasta
              $filtroLocal
            ORDER BY sc.fecha_operacion");
        $s->execute($params);
        foreach ($s->fetchAll() as $r) $rows[] = $r;

        // 4. Depósitos a KGyR
        $s = $db->prepare("
            SELECT sc.fecha_operacion AS fecha,
                   l.id_local, l.descripcion AS local_desc,
                   'DEPOSITO' AS categoria,
                   NULL AS sub_categoria,
                   pd.numero_comprobante AS descripcion,
                   pd.monto
            FROM pago_deposito pd
            JOIN sesion_caja sc ON sc.id_sesion = pd.sesion_id
            JOIN caja c         ON c.id_caja    = sc.caja_id
            JOIN local l        ON l.id_local   = c.local_id
            WHERE sc.fecha_operacion BETWEEN :desde AND :hasta
              $filtroLocal
            ORDER BY sc.fecha_operacion");
        $s->execute($params);
        foreach ($s->fetchAll() as $r) $rows[] = $r;

        // 5. Facturas / comprobantes
        $s = $db->prepare("
            SELECT sc.fecha_operacion AS fecha,
                   l.id_local, l.descripcion AS local_desc,
                   'FACTURA' AS categoria,
                   pf.tipo_documento AS sub_categoria,
                   pf.numero_comprobante AS descripcion,
                   pf.monto
            FROM pago_factura pf
            JOIN sesion_caja sc ON sc.id_sesion = pf.sesion_id
            JOIN caja c         ON c.id_caja    = sc.caja_id
            JOIN local l        ON l.id_local   = c.local_id
            WHERE sc.fecha_operacion BETWEEN :desde AND :hasta
              $filtroLocal
            ORDER BY sc.fecha_operacion");
        $s->execute($params);
        foreach ($s->fetchAll() as $r) $rows[] = $r;

        // 6. Ajustes esperados PERSONAL (pagos a personal registrados como ajuste al cuadre)
        $s = $db->prepare("
            SELECT sc.fecha_operacion AS fecha,
                   l.id_local, l.descripcion AS local_desc,
                   'PERSONAL' AS categoria, ae.tipo_pago AS sub_categoria,
                   COALESCE(NULLIF(ae.descripcion, ''), pr.nombres) AS descripcion,
                   ae.monto
            FROM ajuste_esperado ae
            JOIN sesion_caja sc  ON sc.id_sesion       = ae.sesion_id
            JOIN caja c          ON c.id_caja           = sc.caja_id
            JOIN local l         ON l.id_local          = c.local_id
            LEFT JOIN postulante pr ON pr.id_postulante = ae.ref_id
            WHERE ae.tipo = 'PERSONAL' AND ae.accion = 'AGREGAR'
              AND sc.fecha_operacion BETWEEN :desde AND :hasta
              $filtroLocal
            ORDER BY sc.fecha_operacion");
        $s->execute($params);
        foreach ($s->fetchAll() as $r) $rows[] = $r;

        usort($rows, fn($a, $b) => strcmp($a['fecha'], $b['fecha']));

        // Resumen por categoría → sub_categoría
        $resumen      = [];
        $totalGeneral = 0.0;
        foreach ($rows as $r) {
            $cat   = $r['categoria'];
            $sub   = $r['sub_categoria'] ?? '';
            $monto = (float)$r['monto'];
            if (!isset($resumen[$cat])) $resumen[$cat] = ['total' => 0.0, 'subs' => []];
            $resumen[$cat]['total'] += $monto;
            if ($sub !== '') $resumen[$cat]['subs'][$sub] = ($resumen[$cat]['subs'][$sub] ?? 0.0) + $monto;
            $totalGeneral += $monto;
        }
        foreach ($resumen as &$v) {
            $v['total'] = round($v['total'], 2);
            foreach ($v['subs'] as &$sv) $sv = round($sv, 2);
            arsort($v['subs']);
        }
        unset($v, $sv);
        $totalGeneral = round($totalGeneral, 2);

        require_once __DIR__ . '/../../views/admin/reportes/gastos.php';
    }
}
