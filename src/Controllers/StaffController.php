<?php

require_once __DIR__ . '/../Core/Controller.php';
require_once __DIR__ . '/../Services/AsistenciaService.php';
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';

class StaffController extends Controller
{
    private AsistenciaService $service;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->service = new AsistenciaService();
    }

    private function getSessionUserId(): int
    {
        // Acepta JWT Bearer (Python/API) o sesión PHP (browser)
        $auth = '';
        if (function_exists('getallheaders')) {
            $h    = getallheaders();
            $auth = $h['Authorization'] ?? $h['authorization'] ?? '';
        }
        if (empty($auth)) {
            $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        }

        if (str_starts_with($auth, 'Bearer ')) {
            $payload = AuthMiddleware::requireAuth();  // exits on failure
            return (int)($payload['sub'] ?? 0);
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
        return (int)$_SESSION['user_id'];
    }

    /**
     * GET /staff
     * Carga el portal del colaborador (view HTML).
     */
    public function index(): void
    {
        $postulanteId = $this->getSessionUserId();
        $userName     = $_SESSION['user_name'] ?? 'Colaborador';
        $userRol      = $_SESSION['user_rol']  ?? 'STAFF';
        require_once __DIR__ . '/../../views/staff/dashboard.php';
    }

    /** GET /staff/mi-horario */
    public function miHorario(): void
    {
        $registradorId = $this->getSessionUserId();
        $basePath      = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
        $userName      = $_SESSION['user_name'] ?? 'Colaborador';
        $modo          = $_GET['modo'] ?? 'pendientes';

        require_once __DIR__ . '/../Core/Database.php';
        require_once __DIR__ . '/../Repositories/AsistenciaRepository.php';
        $db        = \Database::getConnection();
        $asistRepo = new AsistenciaRepository();

        // Lista de compañeros (excluye al propio registrador) para filtro
        $trabajadores = $db->query(
            "SELECT p.id_postulante AS id, p.nombres AS nombre
             FROM postulante p INNER JOIN usuario u ON u.postulante_id = p.id_postulante
             WHERE u.activo = 1 ORDER BY p.nombres ASC"
        )->fetchAll();

        if ($modo === 'mis-encuestas') {
            // ── Modo lectura: encuestas propias por mes ────────
            $filtroMes = $_GET['mes'] ?? date('Y-m');
            if (!preg_match('/^\d{4}-\d{2}$/', $filtroMes)) $filtroMes = date('Y-m');
            [$anio, $nmes] = explode('-', $filtroMes);
            $desde = "{$anio}-{$nmes}-01";
            $hasta = date('Y-m-t', strtotime($desde));

            $postulanteId    = $registradorId;
            $nombreTrabajador = $userName;
            $esPropioHorario  = true;

            $stmtSlots = $db->prepare(
                "SELECT hs.id_slot, hs.fecha_dia, hs.turno_id,
                        l.descripcion AS local_desc,
                        t.descripcion AS turno_desc,
                        rh.descripcion AS rol_desc,
                        sc_cubre.id_solicitud AS cubre_id,
                        po.nombres AS cubrió_a
                 FROM horario_slot hs
                 INNER JOIN local l         ON hs.local_id       = l.id_local
                 INNER JOIN turno t         ON hs.turno_id       = t.id_turno
                 INNER JOIN rol_horario rh  ON hs.rol_horario_id = rh.id_rol_horario
                 LEFT JOIN solicitud_cambio sc_cubre
                     ON sc_cubre.slot_id = hs.id_slot
                     AND sc_cubre.postulante_solicitante_id = :pid1
                     AND sc_cubre.tipo = 'COBERTURA' AND sc_cubre.estado = 'ACTIVA'
                 LEFT JOIN postulante po ON sc_cubre.postulante_original_id = po.id_postulante
                 WHERE hs.postulante_id = :pid2
                   AND hs.fecha_dia BETWEEN :desde AND :hasta
                 ORDER BY hs.fecha_dia ASC, hs.turno_id ASC"
            );
            $stmtSlots->execute(['pid1' => $postulanteId, 'pid2' => $postulanteId, 'desde' => $desde, 'hasta' => $hasta]);
            $slots = $stmtSlots->fetchAll();

            $stmtRemp = $db->prepare(
                "SELECT hs.fecha_dia, hs.turno_id, l.descripcion AS local_desc,
                        rh.descripcion AS rol_desc, ps.nombres AS reemplazado_por, sc.notas
                 FROM solicitud_cambio sc
                 INNER JOIN horario_slot hs ON sc.slot_id = hs.id_slot
                 INNER JOIN local l  ON hs.local_id = l.id_local
                 INNER JOIN turno t  ON hs.turno_id  = t.id_turno
                 INNER JOIN rol_horario rh ON hs.rol_horario_id = rh.id_rol_horario
                 INNER JOIN postulante ps ON sc.postulante_solicitante_id = ps.id_postulante
                 WHERE sc.postulante_original_id = :pid AND sc.tipo = 'COBERTURA' AND sc.estado = 'ACTIVA'
                   AND hs.fecha_dia BETWEEN :desde AND :hasta
                 ORDER BY hs.fecha_dia ASC, hs.turno_id ASC"
            );
            $stmtRemp->execute(['pid' => $postulanteId, 'desde' => $desde, 'hasta' => $hasta]);
            $reemplazos = $stmtRemp->fetchAll();

            $asistencias = $asistRepo->getByPostulanteRango($postulanteId, $desde, $hasta);
            $asistPorFecha = [];
            foreach ($asistencias as $a) {
                $tid = (int)($a['turno_id'] ?? 0);
                $asistPorFecha[$a['fecha']][$tid] = $a;
            }
            $rempPorFecha = [];
            foreach ($reemplazos as $r) {
                $rempPorFecha[$r['fecha_dia']][$r['turno_id']] = $r;
            }

        } else {
            // ── Modo pendientes: fichas de compañeros ──────────
            $desde            = $_GET['desde'] ?? date('Y-m-01');
            $hasta            = $_GET['hasta'] ?? date('Y-m-d');
            $filtroTrabajador = isset($_GET['trabajador']) ? (int)$_GET['trabajador'] : 0;
            // Si el formulario fue enviado explícitamente ($filtro presente), respetar el checkbox.
            // En la carga inicial (sin $filtro) el default es true.
            $soloSinCalif = isset($_GET['filtro']) ? isset($_GET['sin_calif']) : true;

            $slotsData = $asistRepo->getAllSlots(
                $desde, $hasta, $filtroTrabajador, $soloSinCalif, $registradorId
            );

            // Variables de modo "mis-encuestas" vacías para evitar errores en la vista
            $slots = $asistPorFecha = $rempPorFecha = [];
            $filtroMes = date('Y-m');
            $postulanteId = $registradorId;
            $esPropioHorario = false;
            $nombreTrabajador = '';
        }

        require_once __DIR__ . '/../../views/staff/mi_horario.php';
    }

    /** POST /staff/api/asistencia/registrar — un compañero llena la ficha */
    public function registrarAsistencia(): void
    {
        $registradorId = $this->getSessionUserId();
        $data = $this->getAllInput();

        $postulanteId = (int)($data['postulante_id'] ?? 0);
        $fecha        = $data['fecha']    ?? '';
        $turnoId      = (int)($data['turno_id'] ?? 0);
        $seccion      = strtoupper(trim($data['seccion'] ?? ''));
        $password     = trim($data['password'] ?? '');

        if (!$postulanteId || !$fecha || !$turnoId || !$password
            || !in_array($seccion, ['ENTRADA', 'SALIDA', 'FALTA'], true)) {
            $this->error('Faltan datos requeridos', 422);
        }

        require_once __DIR__ . '/../Repositories/AsistenciaRepository.php';
        $result = (new AsistenciaRepository())->registrarParaCompanhero(
            $postulanteId, $registradorId, $fecha, $turnoId, $seccion, $data, $password
        );

        if ($result === true) $this->success('Ficha actualizada.');
        else $this->error($result, 401);
    }

    /** POST /staff/api/asistencia/{id}/revertir — elimina registro de FALTA */
    public function revertirFalta(int $id): void
    {
        $registradorId = $this->getSessionUserId();
        $password = trim($this->getAllInput()['password'] ?? '');
        if (empty($password)) $this->error('La contraseña es requerida', 400);

        require_once __DIR__ . '/../Repositories/AsistenciaRepository.php';
        $result = (new AsistenciaRepository())->revertirFalta($id, $registradorId, $password);

        if ($result === true) $this->success('Falta revertida. El registro fue eliminado.');
        else $this->error($result, 401);
    }

    /** POST /staff/api/asistencia/{id}/editar — obsoleto */
    public function editarAsistencia(int $id): void
    {
        $this->error('Usar /staff/api/asistencia/registrar con la nueva ficha.', 410);
    }

    /** GET /staff/economia — pagos recibidos + ingresos diarios calculados */
    public function economia(): void
    {
        $postulanteId = $this->getSessionUserId();
        $basePath     = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
        $userName     = $_SESSION['user_name'] ?? 'Colaborador';

        $filtroMes = $_GET['mes'] ?? date('Y-m');
        if (!preg_match('/^\d{4}-\d{2}$/', $filtroMes)) $filtroMes = date('Y-m');
        [$anio, $nmes] = explode('-', $filtroMes);
        $desde = "{$anio}-{$nmes}-01";
        $hasta = date('Y-m-t', strtotime($desde));

        require_once __DIR__ . '/../Core/Database.php';
        $db = \Database::getConnection();

        // ── 1. Pagos recibidos ────────────────────────────
        $stmtPagos = $db->prepare(
            "SELECT pp.id_pago_personal, pp.monto, pp.tipo_pago, pp.estado,
                    pp.fecha_pago, pp.numero_operacion,
                    sc.fecha_operacion, sc.turno_id,
                    l.descripcion AS local_desc,
                    pe.nombres    AS emisor_nombre
             FROM pago_personal pp
             INNER JOIN sesion_caja sc ON sc.id_sesion     = pp.sesion_id
             INNER JOIN caja ca        ON ca.id_caja       = sc.caja_id
             INNER JOIN local l        ON l.id_local       = ca.local_id
             INNER JOIN postulante pe  ON pe.id_postulante = pp.postulante_emisor_id
             WHERE pp.postulante_beneficiario_id = :pid
               AND DATE(pp.fecha_pago) BETWEEN :desde AND :hasta
             ORDER BY pp.fecha_pago DESC"
        );
        $stmtPagos->execute(['pid' => $postulanteId, 'desde' => $desde, 'hasta' => $hasta]);
        $pagos = $stmtPagos->fetchAll();

        // ── 2. Slots asistidos del mes ────────────────────
        $stmtSlots = $db->prepare(
            "SELECT hs.id_slot, hs.fecha_dia, hs.turno_id, hs.local_id,
                    rh.codigo      AS rol_codigo,
                    rh.descripcion AS rol_desc,
                    l.descripcion  AS local_desc
             FROM horario_slot hs
             INNER JOIN rol_horario rh ON hs.rol_horario_id = rh.id_rol_horario
             INNER JOIN local l        ON hs.local_id       = l.id_local
             INNER JOIN asistencia a   ON a.postulante_id   = hs.postulante_id
                                      AND a.fecha           = hs.fecha_dia
                                      AND (a.turno_id = hs.turno_id OR a.turno_id IS NULL)
                                      AND a.estado != 'FALTA'
                                      AND (a.llegada_puntualidad IS NOT NULL OR a.salida_puntualidad IS NOT NULL)
             WHERE hs.postulante_id = :pid
               AND hs.fecha_dia BETWEEN :desde AND :hasta
               AND rh.codigo IN ('CAJERA','VENDEDORA','ALMACENERA')
             ORDER BY hs.fecha_dia DESC, hs.turno_id ASC"
        );
        $stmtSlots->execute(['pid' => $postulanteId, 'desde' => $desde, 'hasta' => $hasta]);
        $slots = $stmtSlots->fetchAll();

        // ── Helpers de cálculo ────────────────────────────
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
                "SELECT sc.id_sesion, dc.num_operaciones_bcp,
                        COALESCE(rv.monto, 0) AS ventas
                 FROM sesion_caja sc
                 INNER JOIN caja ca ON ca.id_caja = sc.caja_id
                 LEFT JOIN detalle_cuadre dc ON dc.sesion_id = sc.id_sesion
                 LEFT JOIN reporte_venta rv  ON rv.sesion_id = sc.id_sesion
                 WHERE ca.local_id = :lid AND sc.turno_id = :tid
                   AND sc.fecha_operacion = :fecha
                   AND sc.estado IN ('CERRADA','APROBADA')
                 ORDER BY sc.id_sesion DESC LIMIT 1"
            );
            $s->execute(['lid'=>$localId,'tid'=>$turnoId,'fecha'=>$fecha]);
            $r = $s->fetch();
            return $r ?: null;
        };

        $getVentasVendedora = function(int $pid, int $sesionId) use ($db): float {
            $s = $db->prepare(
                "SELECT COALESCE(rv.monto, 0) AS ventas
                 FROM sesion_participante sp
                 INNER JOIN reporte_venta rv ON rv.sesion_id = sp.sesion_id
                 WHERE sp.postulante_id = :pid AND sp.sesion_id = :sid
                   AND sp.rol_participacion = 'VENDEDORA'
                 LIMIT 1"
            );
            $s->execute(['pid'=>$pid,'sid'=>$sesionId]);
            return (float)($s->fetchColumn() ?: 0);
        };

        // ── 3. Calcular ingresos por slot ─────────────────
        $ingresos = [];
        $totalIngresos = 0.0;
        $totalBonos    = 0.0;

        foreach ($slots as $slot) {
            $rol   = $slot['rol_codigo'];
            $fecha = $slot['fecha_dia'];
            $base  = $getBase($rol, $fecha);
            $bonoV = 0.0;
            $bonoO = 0.0;

            if (in_array($rol, ['CAJERA','VENDEDORA'])) {
                $sesion = $getSesion($slot['local_id'], $slot['turno_id'], $fecha);
                if ($sesion) {
                    if ($rol === 'CAJERA') {
                        $ops   = (float)($sesion['num_operaciones_bcp'] ?? 0);
                        $bonoO = $getBono('OPERACIONES_BCP', $ops, $fecha);
                    } else {
                        $ventas = $getVentasVendedora($postulanteId, $sesion['id_sesion']);
                        $bonoV  = $getBono('VENTAS', $ventas, $fecha);
                    }
                }
            }

            $total          = $base + $bonoV + $bonoO;
            $totalIngresos += $total;
            $totalBonos    += $bonoV + $bonoO;

            $ingresos[] = array_merge($slot, [
                'base'       => $base,
                'bono_v'     => $bonoV,
                'bono_o'     => $bonoO,
                'total'      => $total,
            ]);
        }

        $totalPagado = array_sum(array_column(
            array_filter($pagos, fn($p) => in_array($p['estado'], ['PAGADO','CONFIRMADO_BENEFICIARIO','APROBADO'])),
            'monto'
        ));
        $mesPasado    = date('Y-m', strtotime($desde . ' -1 month'));
        $mesSiguiente = date('Y-m', strtotime($desde . ' +1 month'));
        $mesActual    = date('Y-m');

        // ── Tarifas y bonos vigentes (para sección informativa) ──
        $hoyStr = date('Y-m-d');

        $stmtTar = $db->prepare(
            "SELECT t1.* FROM tarifa_base_rol t1
             WHERE t1.fecha_vigencia = (
                 SELECT MAX(t2.fecha_vigencia) FROM tarifa_base_rol t2
                 WHERE t2.rol_codigo = t1.rol_codigo AND t2.fecha_vigencia <= :hoy
             )
             ORDER BY FIELD(t1.rol_codigo,'CAJERA','VENDEDORA','ALMACENERA')"
        );
        $stmtTar->execute(['hoy' => $hoyStr]);
        $tarifasInfo = [];
        foreach ($stmtTar->fetchAll() as $t) {
            $tarifasInfo[$t['rol_codigo']] = $t;
        }

        foreach (['VENTAS' => 'bonosVInfo', 'OPERACIONES_BCP' => 'bonosOInfo'] as $tipo => $varName) {
            $vigMax = $db->prepare(
                "SELECT MAX(fecha_vigencia) FROM configuracion_bono
                 WHERE tipo = :tipo AND fecha_vigencia <= :hoy"
            );
            $vigMax->execute(['tipo' => $tipo, 'hoy' => $hoyStr]);
            $fechaVig = $vigMax->fetchColumn();

            $$varName = [];
            if ($fechaVig) {
                $stmtB = $db->prepare(
                    "SELECT * FROM configuracion_bono
                     WHERE tipo = :tipo AND fecha_vigencia = :vig
                     ORDER BY desde ASC"
                );
                $stmtB->execute(['tipo' => $tipo, 'vig' => $fechaVig]);
                $$varName = $stmtB->fetchAll();
            }
        }

        require_once __DIR__ . '/../../views/staff/economia.php';
    }

    /** GET /staff/info — página de información interna */
    public function info(): void
    {
        $this->getSessionUserId(); // requiere sesión
        $basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
        require_once __DIR__ . '/../../views/staff/info.php';
    }

    /**
     * GET /staff/api/historial
     * JSON: historial de asistencia propio.
     */
    public function historial(): void
    {
        $postulanteId = $this->getSessionUserId();
        $result = $this->service->getHistorialPropio($postulanteId);
        $this->success($result['message'], $result['data']);
    }

    /**
     * POST /staff/asistencia/marcar
     * Body JSON: { tipo:"ENTRADA"|"SALIDA", local_id:2, password:"xxx", checklist:[...] }
     */
    public function marcar(): void
    {
        $postulanteId = $this->getSessionUserId();
        $data         = $this->getAllInput();

        $tipo      = strtoupper(trim($data['tipo']     ?? 'ENTRADA'));
        $localId   = isset($data['local_id']) ? (int)$data['local_id'] : null;
        $password  = $data['password']  ?? '';
        $checklist = $data['checklist'] ?? [];

        if (empty($password)) {
            $this->error('La contraseña es obligatoria para confirmar la asistencia.', 422);
        }

        $result = $this->service->marcarAsistencia($postulanteId, $tipo, $localId, $password, $checklist);

        if (!$result['success']) {
            $this->error($result['message'], $result['status'] ?? 400);
        }

        // Incluir sesiones_hoy dentro del data para que llegue al frontend
        $this->success($result['message'], [
            'sesion'       => $result['data'],
            'sesiones_hoy' => $result['sesiones_hoy'] ?? 0,
        ]);
    }

    /**
     * GET /staff/api/checklist?tipo=APERTURA|CIERRE
     * Devuelve los ítems del checklist para el tipo dado.
     */
    public function getChecklist(): void
    {
        $this->getSessionUserId(); // solo requiere estar autenticado
        $tipo  = strtoupper(trim($_GET['tipo'] ?? 'APERTURA'));
        if (!in_array($tipo, ['APERTURA', 'CIERRE'], true)) {
            $this->error("tipo debe ser APERTURA o CIERRE", 422);
        }

        require_once __DIR__ . '/../Repositories/AsistenciaRepository.php';
        $repo  = new AsistenciaRepository();
        $items = $repo->getChecklistByTipo($tipo);
        $this->success('OK', $items);
    }
}
