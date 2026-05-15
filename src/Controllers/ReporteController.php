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
                    dc.diferencia,
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
}
