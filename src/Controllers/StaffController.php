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

        require_once __DIR__ . '/../Repositories/EstrellaRepository.php';
        $desdeMes = date('Y-m-01');
        $hastaMes = date('Y-m-t');
        $estrellas = (new EstrellaRepository())->getEstrellas($postulanteId, $desdeMes, $hastaMes);

        $cumpleanhos = $this->getCumpleanhosProximos();

        require_once __DIR__ . '/../Repositories/VotoBcpRepository.php';
        $votoRepo = new VotoBcpRepository();
        $mesEncuestaBcp = $votoRepo->getMesEncuestado();
        $yaVotoEncuestaBcp = $mesEncuestaBcp ? $votoRepo->yaVoto($postulanteId, $mesEncuestaBcp) : false;

        require_once __DIR__ . '/../../views/staff/dashboard.php';
    }

    /** Cumpleaños del personal habilitado y contratado, próximos 2 meses */
    private function getCumpleanhosProximos(): array
    {
        require_once __DIR__ . '/../Core/Database.php';
        $db = \Database::getConnection();

        $stmt = $db->query(
            "SELECT p.nombres, p.apellidos, p.fecha_nacimiento
             FROM postulante p
             INNER JOIN usuario u ON u.postulante_id = p.id_postulante
             WHERE u.activo = 1 AND p.etapa_id = 4 AND p.fecha_nacimiento IS NOT NULL"
        );

        $hoy   = new \DateTime('today');
        $limite = (clone $hoy)->modify('+2 months');
        $out   = [];
        foreach ($stmt->fetchAll() as $r) {
            $nac  = new \DateTime($r['fecha_nacimiento']);
            $prox = new \DateTime($hoy->format('Y') . '-' . $nac->format('m-d'));
            if ($prox < $hoy) $prox->modify('+1 year');
            if ($prox >= $limite) continue;
            $dias = (int)$hoy->diff($prox)->format('%a');

            $out[] = [
                'nombre' => trim($r['nombres'] . ' ' . $r['apellidos']),
                'dias'   => $dias,
            ];
        }

        usort($out, fn($a, $b) => $a['dias'] <=> $b['dias']);
        return $out;
    }

    private const MESES_LABEL = [1=>'enero',2=>'febrero',3=>'marzo',4=>'abril',5=>'mayo',6=>'junio',
        7=>'julio',8=>'agosto',9=>'septiembre',10=>'octubre',11=>'noviembre',12=>'diciembre'];

    /** GET /staff/encuesta-bcp — encuesta anónima mensual sobre uso del agente BCP de las cajeras */
    public function encuestaBcpView(): void
    {
        $postulanteId = $this->getSessionUserId();
        $basePath     = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
        $userName     = $_SESSION['user_name'] ?? 'Colaborador';

        require_once __DIR__ . '/../Repositories/VotoBcpRepository.php';
        $repo = new VotoBcpRepository();
        $mes  = $repo->getMesEncuestado();

        if (!$mes) { header('Location: ' . $basePath . '/staff'); exit; }
        if ($repo->yaVoto($postulanteId, $mes)) {
            header('Location: ' . $basePath . '/staff/encuesta-bcp/resultados'); exit;
        }

        [$anio, $nmes] = explode('-', $mes);
        $mesLabel = self::MESES_LABEL[(int)$nmes] . ' ' . $anio;

        $data     = $repo->getCajerasDelMes($mes, $postulanteId);
        $cajas    = $data['cajas'];
        $cajeras  = $data['cajeras'];

        require_once __DIR__ . '/../../views/staff/encuesta_bcp.php';
    }

    /** POST /staff/api/encuesta-bcp/registrar */
    public function encuestaBcpRegistrar(): void
    {
        $postulanteId = $this->getSessionUserId();
        $data = $this->getAllInput();

        $password   = trim($data['password'] ?? '');
        $votos      = $data['votos'] ?? [];
        $comentario = trim($data['comentario'] ?? '');
        if (!$password || !is_array($votos)) $this->error('Faltan datos requeridos', 422);

        require_once __DIR__ . '/../Repositories/VotoBcpRepository.php';
        $repo = new VotoBcpRepository();
        $mes  = $repo->getMesEncuestado();
        if (!$mes) $this->error('La encuesta de este mes ya no está disponible', 410);

        $result = $repo->registrarVotos($postulanteId, $mes, $votos, $password, $comentario);
        if ($result === true) $this->success('Encuesta enviada. ¡Gracias!');
        else $this->error($result, 400);
    }

    /** GET /staff/encuesta-bcp/resultados — públicos una vez que ya votaste ese mes */
    public function encuestaBcpResultados(): void
    {
        $postulanteId = $this->getSessionUserId();
        $basePath     = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
        $userName     = $_SESSION['user_name'] ?? 'Colaborador';
        $isAdmin      = ($_SESSION['user_rol'] ?? '') === 'ADMIN';

        require_once __DIR__ . '/../Repositories/VotoBcpRepository.php';
        $repo = new VotoBcpRepository();
        $mes  = $_GET['mes'] ?? $repo->getMesEncuestado();

        if (!$mes || (!$isAdmin && !$repo->yaVoto($postulanteId, $mes))) {
            header('Location: ' . $basePath . '/staff'); exit;
        }

        [$anio, $nmes] = explode('-', $mes);
        $mesLabel    = self::MESES_LABEL[(int)$nmes] . ' ' . $anio;
        $resultados  = $repo->getResultados($mes);
        $comentarios = $repo->getComentarios($mes);

        require_once __DIR__ . '/../../views/staff/encuesta_bcp_resultados.php';
    }

    /** GET /staff/estrellas — pantalla para ganar estrellas (votar limpieza de un compañero) */
    public function estrellas(): void
    {
        $postulanteId = $this->getSessionUserId();
        $basePath     = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
        $userName     = $_SESSION['user_name']   ?? 'Colaborador';

        require_once __DIR__ . '/../Repositories/EstrellaRepository.php';
        $repo  = new EstrellaRepository();
        $fecha = date('Y-m-d');

        $misLocales = $repo->getMisLocalesTurnoHoy($postulanteId, $fecha);
        $locales    = !empty($misLocales) ? $misLocales : $repo->getLocalesTurnoHoy($fecha);
        $tareas     = $repo->getTareas();

        require_once __DIR__ . '/../../views/staff/estrellas.php';
    }

    /** GET /staff/estrellas/resumen — reporte mensual propio (sin nombres de quién calificó) */
    public function estrellasResumen(): void
    {
        $postulanteId = $this->getSessionUserId();
        $basePath     = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
        $userName     = $_SESSION['user_name'] ?? 'Colaborador';

        $filtroMes = $_GET['mes'] ?? date('Y-m');
        if (!preg_match('/^\d{4}-\d{2}$/', $filtroMes)) $filtroMes = date('Y-m');
        [$anio, $nmes] = explode('-', $filtroMes);
        $desde = "{$anio}-{$nmes}-01";
        $hasta = date('Y-m-t', strtotime($desde));
        $mesActual = date('Y-m');

        require_once __DIR__ . '/../Repositories/EstrellaRepository.php';
        $repo = new EstrellaRepository();

        $estrellas = $repo->getEstrellas($postulanteId, $desde, $hasta);
        $detalleTareas = $repo->getDetalleTareasRecibidas($postulanteId, $desde, $hasta);
        $detalleVotos  = $repo->getDetalleVotosEmitidos($postulanteId, $desde, $hasta);
        $detalleTurnos = $repo->getDetalleTurnos($postulanteId, $desde, $hasta);

        require_once __DIR__ . '/../../views/staff/estrellas_resumen.php';
    }

    /** GET /staff/api/estrellas/companeros?local_id= */
    public function apiEstrellasCompaneros(): void
    {
        $postulanteId = $this->getSessionUserId();
        $localId = (int)($_GET['local_id'] ?? 0);
        $turnoId = (int)($_GET['turno_id'] ?? 0);
        if (!$localId || !$turnoId) $this->error('local_id y turno_id requeridos', 400);

        require_once __DIR__ . '/../Repositories/EstrellaRepository.php';
        $this->success('OK', (new EstrellaRepository())->getCompaneros($localId, $turnoId, $postulanteId, date('Y-m-d')));
    }

    /** GET /staff/api/estrellas/dia?local_id=&turno_id= — estrellas entregadas hoy en ese local/turno */
    public function apiEstrellasDia(): void
    {
        $viewerId = $this->getSessionUserId();
        $localId = (int)($_GET['local_id'] ?? 0);
        $turnoId = (int)($_GET['turno_id'] ?? 0);
        if (!$localId || !$turnoId) $this->error('local_id y turno_id requeridos', 400);

        require_once __DIR__ . '/../Repositories/EstrellaRepository.php';
        $this->success('OK', (new EstrellaRepository())->getVotosDelDia($localId, $turnoId, date('Y-m-d'), $viewerId));
    }

    /** POST /staff/api/estrellas/reportar — denunciar un voto como falso */
    public function apiEstrellasReportar(): void
    {
        $reportanteId = $this->getSessionUserId();
        $data = $this->getAllInput();

        $votoId   = (int)($data['voto_id'] ?? 0);
        $password = trim($data['password'] ?? '');
        if (!$votoId || !$password) $this->error('Faltan datos requeridos', 422);

        require_once __DIR__ . '/../Repositories/EstrellaRepository.php';
        $result = (new EstrellaRepository())->reportarVoto($votoId, $reportanteId, $password);

        if (is_array($result)) $this->success('Denuncia registrada.', $result);
        else $this->error($result, 400);
    }

    /** POST /staff/api/estrellas/votar */
    public function apiEstrellasVotar(): void
    {
        $votanteId = $this->getSessionUserId();
        $data = $this->getAllInput();

        $beneficiarioId = (int)($data['beneficiario_id'] ?? 0);
        $tareaId        = (int)($data['tarea_id']        ?? 0);
        $localId        = (int)($data['local_id']        ?? 0);
        $turnoId        = (int)($data['turno_id']        ?? 0);
        $calificacion   = (int)($data['calificacion']    ?? 0);
        $password       = trim($data['password']          ?? '');

        if (!$beneficiarioId || !$tareaId || !$localId || !$turnoId || !$calificacion || !$password) {
            $this->error('Faltan datos requeridos', 422);
        }

        require_once __DIR__ . '/../Repositories/EstrellaRepository.php';
        $result = (new EstrellaRepository())->registrarVoto(
            $votanteId, $beneficiarioId, $tareaId, $localId, $turnoId, $calificacion, date('Y-m-d'), $password
        );

        if (is_array($result)) $this->success('¡Estrellas registradas!', $result);
        else $this->error($result, 400);
    }

    /** GET /staff/mi-horario */
    public function miHorario(): void
    {
        $registradorId = $this->getSessionUserId();
        $basePath      = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
        $userName      = $_SESSION['user_name'] ?? 'Colaborador';
        $modo          = $_GET['modo'] ?? 'pendientes';

        require_once __DIR__ . '/../Repositories/EncuestaRepository.php';
        $encRepo = new EncuestaRepository();

        // Lista de compañeros (excluye al propio registrador) para filtro
        $trabajadores = $encRepo->getTrabajadores();

        if ($modo === 'mis-encuestas') {
            // ── Modo lectura: mis promedios del mes (sin decir quién calificó) ──
            $filtroMes = $_GET['mes'] ?? date('Y-m');
            if (!preg_match('/^\d{4}-\d{2}$/', $filtroMes)) $filtroMes = date('Y-m');
            [$anio, $nmes] = explode('-', $filtroMes);
            $desde = "{$anio}-{$nmes}-01";
            $hasta = date('Y-m-t', strtotime($desde));
            $mesActual = date('Y-m');

            $promedios = $encRepo->getPromedios($registradorId, $desde, $hasta);
            $detalle   = $encRepo->getDetalleRecibidas($registradorId, $desde, $hasta);

            $filtroTrabajador = 0; $soloSinCalif = false; $slotsData = [];
        } else {
            // ── Modo pendientes: calificar turnos de compañeros ──
            $desde            = $_GET['desde'] ?? date('Y-m-01');
            $hasta            = $_GET['hasta'] ?? date('Y-m-d');
            $filtroTrabajador = isset($_GET['trabajador']) ? (int)$_GET['trabajador'] : 0;
            $soloSinCalif     = isset($_GET['filtro']) ? isset($_GET['sin_calif']) : false;

            $slotsData = $encRepo->getPendientes($desde, $hasta, $filtroTrabajador, $soloSinCalif, $registradorId);

            $filtroMes = date('Y-m'); $mesActual = date('Y-m'); $promedios = []; $detalle = [];
        }

        require_once __DIR__ . '/../../views/staff/mi_horario.php';
    }

    /** POST /staff/api/encuesta/registrar — un compañero califica a otro (7 aspectos, 1-10) */
    public function registrarEncuesta(): void
    {
        $evaluadorId = $this->getSessionUserId();
        $data = $this->getAllInput();

        $evaluadoId = (int)($data['evaluado_id'] ?? 0);
        $fecha      = $data['fecha']    ?? '';
        $turnoId    = (int)($data['turno_id'] ?? 0);
        $password   = trim($data['password'] ?? '');

        if (!$evaluadoId || !$fecha || !$turnoId || !$password) {
            $this->error('Faltan datos requeridos', 422);
        }

        require_once __DIR__ . '/../Repositories/EncuestaRepository.php';
        $result = (new EncuestaRepository())->registrarEncuesta(
            $evaluadorId, $evaluadoId, $fecha, $turnoId, $data, $password
        );

        if ($result === true) $this->success('Calificación registrada.');
        else $this->error($result, 401);
    }

    /**
     * POST /staff/api/asistencia/registrar — usado por la apertura de turno en caja
     * (la cajera evalúa a la vendedora antes de abrir sesión). No relacionado con
     * la encuesta de /staff/mi-horario — ese flujo usa /staff/api/encuesta/registrar.
     */
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

        // ── 1b. Descuentos vía ajuste_esperado (cuadres cerrados) ────
        $stmtDesc = $db->prepare(
            "SELECT ae.id_ajuste, ae.monto, ae.accion, ae.descripcion, ae.fecha,
                    sc.fecha_operacion, sc.turno_id, sc.id_sesion,
                    l.descripcion AS local_desc,
                    p.nombres     AS admin_nombre
             FROM ajuste_esperado ae
             INNER JOIN sesion_caja sc ON sc.id_sesion   = ae.sesion_id
             INNER JOIN caja ca        ON ca.id_caja     = sc.caja_id
             INNER JOIN local l        ON l.id_local     = ca.local_id
             INNER JOIN postulante p   ON p.id_postulante = ae.postulante_id
             WHERE ae.tipo   = 'PERSONAL'
               AND ae.ref_id = :pid
               AND DATE(ae.fecha) BETWEEN :desde AND :hasta
             ORDER BY ae.fecha DESC"
        );
        $stmtDesc->execute(['pid' => $postulanteId, 'desde' => $desde, 'hasta' => $hasta]);
        $descuentosAdj = $stmtDesc->fetchAll();

        // ── 1c. Penalidades (descuentos informativos, ej. encuesta BCP) ──
        require_once __DIR__ . '/../Repositories/DescuentoRepository.php';
        $penalidades = (new DescuentoRepository())->listar($filtroMes, $postulanteId);

        // ── 2. Slots trabajados del mes (certificados y no certificados) ──
        $stmtSlots = $db->prepare(
            "SELECT hs.id_slot, hs.fecha_dia, hs.turno_id, hs.local_id,
                    rh.codigo      AS rol_codigo,
                    rh.descripcion AS rol_desc,
                    l.descripcion  AS local_desc,
                    CASE WHEN (a.llegada_puntualidad IS NOT NULL OR a.salida_puntualidad IS NOT NULL)
                         THEN 1 ELSE 0 END AS certificado
             FROM horario_slot hs
             INNER JOIN rol_horario rh ON hs.rol_horario_id = rh.id_rol_horario
             INNER JOIN local l        ON hs.local_id       = l.id_local
             LEFT JOIN asistencia a    ON a.postulante_id   = hs.postulante_id
                                      AND a.fecha           = hs.fecha_dia
                                      AND (a.turno_id = hs.turno_id OR a.turno_id IS NULL)
                                      AND a.estado != 'FALTA'
             WHERE hs.postulante_id = :pid
               AND hs.fecha_dia BETWEEN :desde AND :hasta
               AND hs.fecha_dia <= CURDATE()
               AND rh.codigo IN ('CAJERA','VENDEDORA','ALMACENERA','ABASTECIMIENTO','INVENTARIO','COMPRAS','AUDITORIA')
               AND NOT EXISTS (
                   SELECT 1 FROM asistencia af
                   WHERE af.postulante_id = hs.postulante_id
                     AND af.fecha         = hs.fecha_dia
                     AND (af.turno_id = hs.turno_id OR af.turno_id IS NULL)
                     AND af.estado = 'FALTA'
               )
             ORDER BY hs.fecha_dia DESC, hs.turno_id DESC"
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

        // Busca la sesión donde el trabajador participó con el rol dado en ese local+turno+fecha.
        // Evita el bug de múltiples cajas por local: en vez de tomar la última por ID,
        // busca directamente en sesion_participante la sesión correcta del trabajador.
        $getSesionParticipante = function(int $pid, string $rolPart, int $localId, int $turnoId, string $fecha) use ($db): ?array {
            $s = $db->prepare(
                "SELECT sc.id_sesion, dc.num_operaciones_bcp,
                        COALESCE(rv.monto, 0) + COALESCE((
                            SELECT SUM(cv.monto_nuevo - cv.monto_anterior)
                            FROM correccion_venta cv WHERE cv.sesion_id = sc.id_sesion
                        ), 0) AS ventas
                 FROM sesion_participante sp
                 INNER JOIN sesion_caja sc ON sc.id_sesion   = sp.sesion_id
                 INNER JOIN caja ca        ON ca.id_caja     = sc.caja_id
                 LEFT JOIN detalle_cuadre dc ON dc.sesion_id = sc.id_sesion
                 LEFT JOIN reporte_venta rv  ON rv.sesion_id = sc.id_sesion
                 WHERE sp.postulante_id    = :pid
                   AND sp.rol_participacion = :rol
                   AND ca.local_id         = :lid
                   AND sc.turno_id         = :tid
                   AND sc.fecha_operacion  = :fecha
                 LIMIT 1"
            );
            $s->execute(['pid'=>$pid,'rol'=>$rolPart,'lid'=>$localId,'tid'=>$turnoId,'fecha'=>$fecha]);
            $r = $s->fetch();
            return $r ?: null;
        };

        // ── Bono por tiempo de servicio: S/0.20 × meses completos desde fecha_ingreso ──
        $stmtFI = $db->prepare("SELECT fecha_ingreso FROM postulante WHERE id_postulante = :pid");
        $stmtFI->execute(['pid' => $postulanteId]);
        $fechaIngreso = $stmtFI->fetchColumn() ?: null;
        $bonoServicioMonto = 0.0;
        $mesesServicio = 0;
        if ($fechaIngreso) {
            $dtIngreso = new DateTime($fechaIngreso);
            $dtRef     = new DateTime($desde); // primer día del mes filtrado
            if ($dtRef > $dtIngreso) {
                $diff = $dtIngreso->diff($dtRef);
                $mesesServicio     = $diff->y * 12 + $diff->m;
                $bonoServicioMonto = round($mesesServicio * 0.20, 2);
            }
        }

        // ── Bono estudios: técnico o universitario, según estado ──
        $stmtEst = $db->prepare(
            "SELECT e.tipo_id, e.estado_id,
                    te.descripcion AS tipo_desc,
                    es.descripcion AS estado_desc
             FROM estudio e
             INNER JOIN tipo_estudio te ON te.id_tipo   = e.tipo_id
             INNER JOIN estado es       ON es.id_estado = e.estado_id
             WHERE e.postulante_id = :pid
               AND e.tipo_id IN (2, 3)
             ORDER BY e.tipo_id DESC, e.estado_id ASC
             LIMIT 1"
        );
        $stmtEst->execute(['pid' => $postulanteId]);
        $estudioInfo = $stmtEst->fetch() ?: null;

        $bonoEstudioMonto = 0.0;
        if ($estudioInfo) {
            $tipoEst  = (int)$estudioInfo['tipo_id'];
            $estadoId = (int)$estudioInfo['estado_id'];
            $avanzado = $estadoId === 3 ? 2 : ($estadoId === 1 ? 1 : 0);
            try {
                $stmtBE = $db->prepare(
                    "SELECT monto FROM bono_estudio_config
                     WHERE tipo_id = :tipo AND avanzado = :av
                       AND fecha_vigencia <= CURDATE()
                     ORDER BY fecha_vigencia DESC LIMIT 1"
                );
                $stmtBE->execute(['tipo' => $tipoEst, 'av' => $avanzado]);
                $montoRow = $stmtBE->fetchColumn();
            } catch (\Exception $e) { $montoRow = false; }
            if ($montoRow !== false) {
                $bonoEstudioMonto = (float)$montoRow;
            } else {
                $fallback = [3 => [0 => 3.0, 1 => 6.0, 2 => 9.0], 2 => [0 => 2.0, 1 => 4.0, 2 => 6.0]];
                $bonoEstudioMonto = $fallback[$tipoEst][$avanzado] ?? 0.0;
            }
        }

        // ── Pago por supervisión: S/ monto_dia × turno trabajado dentro del periodo asignado ──
        $stmtSup = $db->prepare(
            "SELECT fecha_desde, fecha_hasta, monto_dia FROM supervisor_periodo WHERE postulante_id = :pid"
        );
        $stmtSup->execute(['pid' => $postulanteId]);
        $supervisorPeriodos = $stmtSup->fetchAll();
        $getSupervisorPago = function(string $fecha) use ($supervisorPeriodos): float {
            foreach ($supervisorPeriodos as $per) {
                if ($fecha >= $per['fecha_desde'] && ($per['fecha_hasta'] === null || $fecha <= $per['fecha_hasta'])) {
                    return (float)$per['monto_dia'];
                }
            }
            return 0.0;
        };

        // ── 3. Calcular ingresos por slot ─────────────────
        $ingresos      = [];
        $totalIngresos = 0.0;
        $totalBonos    = 0.0;
        $totalIngCert  = 0.0;
        $totalIngNoCert = 0.0;

        foreach ($slots as $slot) {
            $rol   = $slot['rol_codigo'];
            $fecha = $slot['fecha_dia'];
            $base  = $getBase($rol, $fecha);
            $bonoV = 0.0;
            $bonoO = 0.0;

            if (in_array($rol, ['CAJERA','VENDEDORA'])) {
                $sesion = $getSesionParticipante($postulanteId, $rol, $slot['local_id'], $slot['turno_id'], $fecha);
                if ($sesion) {
                    if ($rol === 'CAJERA') {
                        $ops   = (float)($sesion['num_operaciones_bcp'] ?? 0);
                        $bonoO = $getBono('OPERACIONES_BCP', $ops, $fecha);
                    } else {
                        $bonoV = $getBono('VENTAS', (float)($sesion['ventas'] ?? 0), $fecha);
                    }
                }
            }

            $bonoE          = $bonoEstudioMonto;
            $bonoS          = $bonoServicioMonto + $getSupervisorPago($fecha);
            $total          = $base + $bonoV + $bonoO + $bonoE + $bonoS;
            $totalIngresos += $total;
            $totalBonos    += $bonoV + $bonoO + $bonoE + $bonoS;

            $esCertificado = (bool)($slot['certificado'] ?? false);
            if ($esCertificado) $totalIngCert   += $total;
            else                $totalIngNoCert += $total;

            $ingresos[] = array_merge($slot, [
                'base'        => $base,
                'bono_v'      => $bonoV,
                'bono_o'      => $bonoO,
                'bono_e'      => $bonoE,
                'bono_s'      => $bonoS,
                'total'       => $total,
                'certificado' => $esCertificado,
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
             ORDER BY FIELD(t1.rol_codigo,'CAJERA','VENDEDORA','ALMACENERA','ABASTECIMIENTO','INVENTARIO','COMPRAS','AUDITORIA')"
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

        $estBonoRef = [3=>[0=>3.0,1=>6.0,2=>9.0],2=>[0=>2.0,1=>4.0,2=>6.0]];
        try {
            $estBonoRows2 = $db->query(
                "SELECT b1.tipo_id, b1.avanzado, b1.monto
                 FROM bono_estudio_config b1
                 WHERE b1.fecha_vigencia = (
                     SELECT MAX(b2.fecha_vigencia) FROM bono_estudio_config b2
                     WHERE b2.tipo_id = b1.tipo_id AND b2.avanzado = b1.avanzado
                       AND b2.fecha_vigencia <= CURDATE()
                 )"
            )->fetchAll();
            foreach ($estBonoRows2 as $ebr) {
                $estBonoRef[(int)$ebr['tipo_id']][(int)$ebr['avanzado']] = (float)$ebr['monto'];
            }
        } catch (\Exception $e) { /* usa fallback */ }

        require_once __DIR__ . '/../Repositories/EstrellaRepository.php';
        $estrellas = (new EstrellaRepository())->getEstrellas($postulanteId, $desde, $hasta);

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
