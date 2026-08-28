<?php

require_once __DIR__ . '/../Core/Controller.php';
require_once __DIR__ . '/../Repositories/CajaRepository.php';
require_once __DIR__ . '/../Repositories/IncidenciaContableRepository.php';

class CajaController extends Controller
{
    private CajaRepository $repo;
    private IncidenciaContableRepository $incRepo;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->repo    = new CajaRepository();
        $this->incRepo = new IncidenciaContableRepository();
    }

    private function requireAuth(): int
    {
        // Acepta Bearer token (API/Python) o sesión PHP (browser)
        $auth = '';
        if (function_exists('getallheaders')) {
            $h    = getallheaders();
            $auth = $h['Authorization'] ?? $h['authorization'] ?? '';
        }
        if (empty($auth)) $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (str_starts_with($auth, 'Bearer ')) {
            require_once __DIR__ . '/../Middleware/AuthMiddleware.php';
            $payload = AuthMiddleware::requireAuth(); // exits on failure
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

    private function requireAdmin(): int
    {
        $id = $this->requireAuth();
        if (($_SESSION['user_rol'] ?? '') !== 'ADMIN') {
            $this->error('Solo el administrador puede acceder a Auditoría', 403);
            exit;
        }
        return $id;
    }

    private function requireAdminOrSupervisor(): int
    {
        $id = $this->requireAuth();
        if (!in_array($_SESSION['user_rol'] ?? '', ['ADMIN', 'SUPERVISOR'], true)) {
            $this->error('Solo administrador o supervisor pueden aprobar/rechazar', 403);
            exit;
        }
        return $id;
    }

    /**
     * Estos endpoints solo se usan desde la pantalla de incidencias para corregir una
     * sesión ya cerrada. Mientras el caso siga abierto (ABIERTO/PARCIAL), cualquier staff
     * puede corregir; una vez que el admin CIERRA el caso, solo el admin puede seguir editando.
     */
    private function requireEditableSesion(int $sesionId): int
    {
        $postulanteId = $this->requireAuth();
        if (($_SESSION['user_rol'] ?? '') !== 'ADMIN') {
            $incidencias = $this->incRepo->getBySesion($sesionId);
            foreach ($incidencias as $inc) {
                if ($inc['estado'] === 'CERRADO') {
                    $this->error('Este caso ya fue cerrado. Solo el administrador puede modificarlo.', 403);
                    exit;
                }
            }
            // Arqueo limpio (sin incidencia): editable solo el mismo día; al día siguiente
            // se sella solo y solo el administrador puede reabrirlo.
            if (empty($incidencias)) {
                $sesion = $this->repo->getSesionById($sesionId);
                $esHoy  = $sesion && substr((string)$sesion['fecha_operacion'], 0, 10) === date('Y-m-d');
                if (!$esHoy) {
                    $this->error('El arqueo ya quedó cerrado. Solo el administrador puede reabrirlo.', 403);
                    exit;
                }
            }
        }
        return $postulanteId;
    }

    // ── GET /caja ──────────────────────────────────────────
    public function index(): void
    {
        $postulanteId = $this->requireAuth();
        $basePath     = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
        $userName     = $_SESSION['user_name'] ?? 'Usuario';
        $userRol      = $_SESSION['user_rol']  ?? 'STAFF';

        $filtroCaja   = isset($_GET['caja'])   ? (int)$_GET['caja']   : 0;
        $filtroCajera = isset($_GET['cajera']) ? (int)$_GET['cajera'] : 0;
        $filtroMes    = $_GET['mes'] ?? date('Y-m');

        $pendientes = $this->repo->getSesionesByEstado('PENDIENTE_VENTA');
        $abiertas   = $this->repo->getSesionesByEstado('ABIERTA');
        $recientes  = $this->repo->getSesionesRecientes($filtroCaja, $filtroCajera, $filtroMes);
        $cajas      = $this->repo->getCajasActivas();
        $cajeras    = $this->repo->getCajerasActivas();

        require_once __DIR__ . '/../../views/caja/index.php';
    }

    // ── GET /caja/sesion/nueva ─────────────────────────────
    public function nueva(): void
    {
        $postulanteId = $this->requireAuth();
        $basePath     = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
        $userName     = $_SESSION['user_name'] ?? 'Usuario';
        $esAdminCaja  = ($_SESSION['user_rol'] ?? '') === 'ADMIN';

        $localesTodos = $this->repo->getLocales();
        $turnos       = $this->repo->getTurnos();
        $conceptos    = $this->repo->getConceptosGasto();
        $staff        = $this->repo->getStaffActivo();
        $tiposEgreso  = $this->repo->getTiposEgreso();
        $modos        = $this->repo->getModosPago();

        // Filtrar locales y turnos según horario de hoy (solo si no es admin)
        require_once __DIR__ . '/../Repositories/HorarioRepository.php';
        $horarioRepo    = new HorarioRepository();
        $horarioCajera  = $esAdminCaja ? [] : $horarioRepo->getHorarioCajeraHoy($postulanteId);

        // Mapa: local_id => [turno_id, ...] para JS
        $horarioCajeraMap = [];
        foreach ($horarioCajera as $h) {
            $horarioCajeraMap[$h['local_id']][] = (int)$h['turno_id'];
        }

        // Locales disponibles: admin ve todos, cajera solo los suyos
        if ($esAdminCaja) {
            $locales = $localesTodos;
        } else {
            $localIds = array_unique(array_column($horarioCajera, 'local_id'));
            $locales  = array_values(array_filter($localesTodos, fn($l) => in_array($l['id'], $localIds)));
        }

        require_once __DIR__ . '/../../views/caja/sesion.php';
    }

    // ── GET /caja/sesion/{id} ──────────────────────────────
    public function editarSesion(int $id): void
    {
        $postulanteId = $this->requireAuth();
        $basePath     = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
        $userName     = $_SESSION['user_name'] ?? 'Usuario';

        $sesion  = $this->repo->getSesionById($id);
        if (!$sesion || $sesion['estado'] !== 'ABIERTA') {
            header('Location: ' . APP_BASE_PATH . '/caja');
            exit;
        }

        // Siempre mostrar la base real del predecesor (no el saldo_inicial almacenado)
        $saldoReal = $this->repo->getSaldoBase((int)$sesion['caja_id']);
        // Actualizar si difiere (puede haber cambiado por ajustes en el arqueo anterior)
        if (abs($saldoReal - (float)$sesion['saldo_inicial']) > 0.001) {
            $db2 = \Database::getConnection();
            $db2->prepare("UPDATE sesion_caja SET saldo_inicial = :sal WHERE id_sesion = :sid")
                ->execute(['sal' => $saldoReal, 'sid' => $id]);
            $sesion['saldo_inicial'] = $saldoReal;
        }

        $locales     = $this->repo->getLocales();
        $turnos      = $this->repo->getTurnos();
        $conceptos   = $this->repo->getConceptosGasto();
        $staff       = $this->repo->getStaffActivo();
        $tiposEgreso = $this->repo->getTiposEgreso();
        $modos       = $this->repo->getModosPago();
        $gastos      = $this->repo->getGastosSesion($id);

        // Vales SoloBank disponibles para seleccionar
        require_once __DIR__ . '/../Repositories/SoloBankRepository.php';
        $sbRepo      = new SoloBankRepository();
        $soloBankVales = $sbRepo->getValesDisponibles();

        // Cargar detalle_cuadre si existe (activos ya guardados)
        $db     = \Database::getConnection();
        $dcStmt = $db->prepare("SELECT * FROM detalle_cuadre WHERE sesion_id = :sid");
        $dcStmt->execute(['sid' => $id]);
        $detalle = $dcStmt->fetch() ?? [];

        // Transferencias de saldo confirmadas que se aplicarán en el cuadre de cierre
        $transferenciasPendientes = $this->repo->getTransferenciasPendientesAplicar((int)$sesion['caja_id']);

        // Retiros de efectivo para depósito a KGyR que se aplicarán en el cuadre de cierre
        $retirosPendientes  = $this->repo->getRetirosPendientesAplicar((int)$sesion['caja_id']);

        // Ingresos bancarios a caja que se aplicarán en el cuadre de cierre
        $ingresosPendientes = $this->repo->getIngresosPendientesAplicar((int)$sesion['caja_id']);

        require_once __DIR__ . '/../../views/caja/sesion.php';
    }

    // ── POST /caja/api/sesion/crear ────────────────────────
    public function crearSesion(): void
    {
        $postulanteId = $this->requireAuth();
        $data = $this->getAllInput();
        $isAdminCrear = ($_SESSION['user_rol'] ?? '') === 'ADMIN';

        if (empty($data['caja_id']) || empty($data['turno_id'])) {
            $this->error('Caja y turno son requeridos', 400);
        }

        // Fecha de operación: solo el admin puede elegir una fecha distinta a hoy
        // (para registrar/corregir cuadres de días pasados).
        $fecha = date('Y-m-d');
        if ($isAdminCrear && !empty($data['fecha_operacion'])) {
            $fechaReq = trim($data['fecha_operacion']);
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaReq) || !strtotime($fechaReq)) {
                $this->error('Fecha inválida', 422);
            }
            if ($fechaReq > date('Y-m-d')) {
                $this->error('No se puede abrir una sesión con fecha futura', 422);
            }
            $fecha = $fechaReq;
        }

        // Bloquear solo si ya hay una sesión ACTIVA (abierta o pendiente de venta).
        // Se permiten múltiples cuadres por día siempre que el anterior esté cerrado.
        $db  = \Database::getConnection();
        $chk = $db->prepare(
            "SELECT id_sesion, estado FROM sesion_caja
             WHERE caja_id = :cid AND turno_id = :tid
               AND fecha_operacion = :fecha
               AND estado IN ('ABIERTA','PENDIENTE_VENTA')
             LIMIT 1"
        );
        $chk->execute(['cid' => (int)$data['caja_id'], 'tid' => (int)$data['turno_id'], 'fecha' => $fecha]);
        $active = $chk->fetch();
        if ($active) {
            $label = $active['estado'] === 'ABIERTA' ? 'en progreso' : 'pendiente de venta';
            $this->error(
                "Ya hay una sesión abierta para esta caja en este turno (estado: {$label}). "
                . "Ciérrala antes de abrir una nueva.",
                409
            );
        }

        // Verificar si la caja requiere vendedora
        $stmtCaja = $db->prepare("SELECT requiere_vendedora FROM caja WHERE id_caja = :cid LIMIT 1");
        $stmtCaja->execute(['cid' => (int)$data['caja_id']]);
        $requiereVendedora = (bool)($stmtCaja->fetchColumn() ?? 1);

        // Validar que quien abre la sesión es CAJERA en horario de hoy (solo si no es admin)
        if (!$isAdminCrear) {
            $chkCajera = $db->prepare(
                "SELECT hs.id_slot
                 FROM horario_slot hs
                 INNER JOIN caja c ON c.local_id = hs.local_id AND c.id_caja = :caja_id
                 INNER JOIN rol_horario rh ON hs.rol_horario_id = rh.id_rol_horario
                 WHERE hs.turno_id      = :tid
                   AND hs.fecha_dia     = CURDATE()
                   AND hs.postulante_id = :pid
                   AND rh.codigo        = 'CAJERA'
                 LIMIT 1"
            );
            $chkCajera->execute([
                'caja_id' => (int)$data['caja_id'],
                'tid'     => (int)$data['turno_id'],
                'pid'     => $postulanteId,
            ]);
            if (!$chkCajera->fetch()) {
                $this->error(
                    'No estás asignada en el horario como cajera para este local y turno hoy. '
                    . 'Corrija el horario en /horario antes de abrir la sesión.',
                    409
                );
            }
        }

        // Validar vendedora (solo si la caja lo requiere)
        $vendedorId = isset($data['vendedor_id']) ? (int)$data['vendedor_id'] : 0;
        if ($requiereVendedora && $vendedorId) {
            $chkHorario = $db->prepare(
                "SELECT hs.id_slot
                 FROM horario_slot hs
                 INNER JOIN caja c ON c.local_id = hs.local_id AND c.id_caja = :caja_id
                 INNER JOIN rol_horario rh ON hs.rol_horario_id = rh.id_rol_horario
                 WHERE hs.turno_id      = :tid
                   AND hs.fecha_dia     = :fecha
                   AND hs.postulante_id = :vid
                   AND rh.codigo        = 'VENDEDORA'
                 LIMIT 1"
            );
            $chkHorario->execute([
                'caja_id' => (int)$data['caja_id'],
                'tid'     => (int)$data['turno_id'],
                'vid'     => $vendedorId,
                'fecha'   => $fecha,
            ]);
            if (!$chkHorario->fetch()) {
                $this->error(
                    'Esta persona no está asignada en el horario para este local y turno en la fecha seleccionada. '
                    . 'Corrija el horario primero en /horario.',
                    409
                );
            }
        }

        // Cajera responsable: quien abre la sesión (self-service), o la persona que
        // el admin elija explícitamente (por ejemplo, al registrar un cuadre de un
        // día pasado). El admin SIEMPRE debe indicar cajera_id: nunca se asume que
        // el admin es la cajera para evitar sesiones mal atribuidas.
        $cajeraId = $postulanteId;
        if ($isAdminCrear) {
            if (empty($data['cajera_id'])) {
                $this->error('Selecciona la cajera', 422);
            }
            $cajeraId = (int)$data['cajera_id'];
            $chkCajeraSel = $db->prepare(
                "SELECT hs.id_slot
                 FROM horario_slot hs
                 INNER JOIN caja c ON c.local_id = hs.local_id AND c.id_caja = :caja_id
                 INNER JOIN rol_horario rh ON hs.rol_horario_id = rh.id_rol_horario
                 WHERE hs.turno_id      = :tid
                   AND hs.fecha_dia     = :fecha
                   AND hs.postulante_id = :pid
                   AND rh.codigo        = 'CAJERA'
                 LIMIT 1"
            );
            $chkCajeraSel->execute([
                'caja_id' => (int)$data['caja_id'],
                'tid'     => (int)$data['turno_id'],
                'pid'     => $cajeraId,
                'fecha'   => $fecha,
            ]);
            if (!$chkCajeraSel->fetch()) {
                $this->error(
                    'Esta persona no está asignada en el horario como cajera para este local, turno y fecha. '
                    . 'Corrija el horario primero en /horario.',
                    409
                );
            }
        }

        $data['postulante_id']   = $cajeraId;
        $data['vendedor_id']     = $vendedorId;
        $data['fecha_operacion'] = $fecha;
        $id = $this->repo->crearSesion($data);
        $this->success('Sesión creada', ['id_sesion' => $id]);
    }

    // ── POST /caja/api/sesion/guardar ──────────────────────
    // Guarda activos + gastos (borrador o cierre)
    public function guardarSesion(): void
    {
        $postulanteId = $this->requireAuth();
        $data = $this->getAllInput();

        $sesionId = (int)($data['sesion_id'] ?? 0);
        if (!$sesionId) $this->error('sesion_id requerido', 400);

        $sesion = $this->repo->getSesionById($sesionId);
        if (!$sesion) $this->error('Sesión no encontrada', 404);
        if ($sesion['bloqueado']) $this->error('La sesión está bloqueada. No se puede modificar.', 403);

        $this->db_transaction(function() use ($sesionId, $postulanteId, $data) {
            // 1. Guardar activos
            $this->repo->upsertDetalleCuadre($sesionId, $data['activos'] ?? []);

            // 2. Reemplazar gastos
            $this->repo->clearGastosSesion($sesionId);

            $localId = (int)$this->repo->getSesionById($sesionId)['id_local'];

            foreach ($data['gastos'] ?? [] as $gasto) {
                $monto   = round((float)($gasto['monto'] ?? 0), 2);
                $comp    = $gasto['comprobante'] ?? null;
                $modoRef = $gasto['modo_ref'] ?? '';
                $tipoId  = (int)($gasto['tipo_egreso_id'] ?? 0);
                if ($monto <= 0 || empty($modoRef)) continue;

                switch ($modoRef) {
                    case 'PERSONAL':
                        $this->repo->insertGastoPersonal(
                            $sesionId, $postulanteId,
                            (int)$gasto['ref_id'], $monto,
                            $gasto['tipo_pago'] ?? 'PAGO_TOTAL'
                        );
                        break;
                    case 'LOCAL':
                        $conceptoId = isset($gasto['concepto_id']) ? (int)$gasto['concepto_id'] : null;
                        $this->repo->insertGastoLocal(
                            $sesionId, (int)$gasto['ref_id'], $postulanteId,
                            $monto, $comp, $tipoId, $conceptoId
                        );
                        break;
                    case 'FACTURA':
                        $this->repo->insertGastoFactura(
                            $sesionId, $postulanteId,
                            $gasto['tipo_documento'] ?? 'BOLETA', $monto, $comp
                        );
                        break;
                    case 'DEPOSITO':
                        $this->repo->insertGastoDeposito(
                            $sesionId, $postulanteId, $monto, $comp
                        );
                        break;
                    case 'LIBRE':
                        $this->repo->insertGastoOtro(
                            $sesionId, $postulanteId,
                            $gasto['descripcion'] ?? 'Otros pagos', $monto
                        );
                        break;
                }
            }

            // 3. Si es cierre, bloquear la sesión y registrar rendimiento de cajera
            if (!empty($data['cerrar'])) {
                $this->repo->cerrarSesion($sesionId, $postulanteId);
                $this->repo->registrarRendimientoCajera($sesionId);
            }
        });

        $this->success(!empty($data['cerrar']) ? 'Sesión cerrada y enviada a pendientes' : 'Guardado correctamente');
    }

    // ── GET /caja/{id}/ventas ──────────────────────────────
    public function ventasView(int $id): void
    {
        $postulanteId = $this->requireAuth();
        $basePath     = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
        $userName     = $_SESSION['user_name'] ?? 'Usuario';
        $isAdmin      = ($_SESSION['user_rol'] ?? '') === 'ADMIN';

        $sesion = $this->repo->getSesionById($id);
        if (!$sesion || $sesion['estado'] !== 'PENDIENTE_VENTA') {
            header('Location: ' . APP_BASE_PATH . '/caja');
            exit;
        }

        $db = \Database::getConnection();

        // Obtener vendedora desde sesion_participante
        $stmtVend = $db->prepare(
            "SELECT sp.postulante_id, p.nombres AS vendedora_nombre
             FROM sesion_participante sp
             INNER JOIN postulante p ON p.id_postulante = sp.postulante_id
             WHERE sp.sesion_id = :sid AND sp.rol_participacion = 'VENDEDORA'
             LIMIT 1"
        );
        $stmtVend->execute(['sid' => $id]);
        $vendRow = $stmtVend->fetch();

        $vendedorId       = (int)($vendRow['postulante_id']    ?? 0);
        $vendedora_nombre = $vendRow['vendedora_nombre']        ?? '';
        $cajera_id        = (int)($sesion['postulante_apertura_id'] ?? 0);
        $cajera_nombre    = $sesion['cajera_nombre']            ?? '';
        $turno_id         = (int)($sesion['turno_id']           ?? 0);

        // Control de acceso:
        // - Si hay vendedora asignada → solo ella o admin
        // - Si no hay vendedora (ej. SB7) → la cajera que abrió o admin
        if (!$isAdmin) {
            if ($vendedorId > 0 && $postulanteId !== $vendedorId) {
                $nombreVend = $vendedora_nombre ?: 'la vendedora asignada';
                $basePath   = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
                require_once __DIR__ . '/../../views/caja/ventas_acceso_denegado.php';
                exit;
            }
            if ($vendedorId === 0 && $postulanteId !== $cajera_id) {
                $nombreVend = 'la cajera del turno';
                $basePath   = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
                require_once __DIR__ . '/../../views/caja/ventas_acceso_denegado.php';
                exit;
            }
        }

        // Verificar si la cajera ya tiene encuesta de hoy registrada
        $chkAsist = $db->prepare(
            "SELECT id_asistencia FROM asistencia
             WHERE postulante_id = :pid AND fecha = CURDATE() AND turno_id = :tid
             LIMIT 1"
        );
        $chkAsist->execute(['pid' => $cajera_id, 'tid' => $turno_id]);
        // Sin vendedora no hay quien evalúe a la cajera → sin encuesta
        $surveyNeeded = $vendedorId > 0 && !$chkAsist->fetchColumn() && !$isAdmin;

        require_once __DIR__ . '/../../views/caja/ventas.php';
    }

    // ── POST /caja/api/{id}/ventas ─────────────────────────
    public function submitVentas(int $id): void
    {
        $postulanteId = $this->requireAuth();
        $data         = $this->getAllInput();
        $ventas       = round((float)($data['monto_ventas'] ?? 0), 2);

        if ($ventas < 0) $this->error('El monto de ventas no puede ser negativo', 422);

        $sesion = $this->repo->getSesionById($id);
        if (!$sesion || $sesion['estado'] !== 'PENDIENTE_VENTA') {
            $this->error('La sesión no está en estado PENDIENTE_VENTA', 409);
        }

        $this->repo->insertVenta($id, $postulanteId, $ventas);
        $this->repo->registrarRendimientoVendedora($id, $ventas);
        $cuadre = $this->repo->calcularYGuardarCuadre($id, $ventas);

        // Auto-detectar incidencia contable si |diferencia| > 10 soles
        if (abs($cuadre['diferencia'] ?? 0) > 10) {
            $tipo           = ($cuadre['diferencia'] < 0) ? 'FALTANTE' : 'SOBRANTE';
            $montoOriginal  = round(abs($cuadre['diferencia']), 2);
            $responsableId  = (int)($sesion['postulante_apertura_id'] ?? 0) ?: null;
            $this->incRepo->crear(
                $id,
                $tipo,
                $montoOriginal,
                $postulanteId,
                $responsableId,
                "Auto-detectado al cierre. Diferencia: S/ {$cuadre['diferencia']}"
            );
        }

        $this->success('Ventas registradas. Cuadre calculado.', $cuadre);
    }

    // ── GET /caja/reporte/{id} ─────────────────────────────
    public function reporte(int $id): void
    {
        $postulanteId = $this->requireAuth();
        $basePath     = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
        $userName     = $_SESSION['user_name'] ?? 'Usuario';
        $userRol      = $_SESSION['user_rol']  ?? 'STAFF';

        $sesion = $this->repo->getSesionById($id);
        if (!$sesion || !in_array($sesion['estado'], ['CERRADA','APROBADA','OBSERVADA','RECHAZADA'], true)) {
            header('Location: ' . APP_BASE_PATH . '/caja');
            exit;
        }

        // Portero: para un cuadre ya cerrado, exige confirmar contraseña antes de
        // mostrar los datos y deja registro de quién entró y cuándo. Al admin no
        // se le pregunta: ya tiene acceso total y auditable por otras vías.
        if ($userRol !== 'ADMIN' && empty($_SESSION['visita_confirmada'][$id])) {
            $sesionId       = $id;
            $origen         = 'REPORTE';
            $nombreCompleto = $this->repo->getNombreCompleto($postulanteId) ?: $userName;
            require_once __DIR__ . '/../../views/caja/confirmar_acceso.php';
            return;
        }

        $data = $this->repo->getReporte($id);
        extract($data); // $sesion, $detalle, $venta, $gastos, $rectifs

        $transferencias = $this->repo->getTransferenciasAplicadas($id);
        $retirosAplicados  = $this->repo->getRetirosAplicados($id);
        $ingresosAplicados = $this->repo->getIngresosAplicados($id);

        // Vales SoloBank disponibles para asignar retroactivamente
        require_once __DIR__ . '/../Repositories/SoloBankRepository.php';
        $sbRepo        = new SoloBankRepository();
        $soloBankVales = $sbRepo->getValesDisponibles();

        // Incidencia contable de esta sesión (si existe)
        $incidenciaContable = $this->incRepo->getBySesion($id)[0] ?? null;

        // Historial de cambios post-cierre
        $auditoria = $this->repo->getAuditoria($id);

        // Registro de accesos (empadronamiento)
        $visitas = $this->repo->getVisitas($id);

        require_once __DIR__ . '/../../views/caja/reporte.php';
    }

    // ── POST /caja/api/sesion/{id}/corregir-venta ─────────
    public function corregirVenta(int $id): void
    {
        $postulanteId = $this->requireEditableSesion($id);
        $data         = $this->getAllInput();
        $montoNuevo   = round((float)($data['monto_nuevo'] ?? -1), 2);
        $motivo       = trim($data['motivo'] ?? '');

        if ($montoNuevo < 0) $this->error('El monto no puede ser negativo', 422);

        $sesion = $this->repo->getSesionById($id);
        if (!$sesion) $this->error('Sesión no encontrada', 404);

        $this->repo->addCorreccionVenta($id, $montoNuevo, $motivo, $postulanteId);
        $this->success('Corrección registrada.');
    }

    // ── POST /caja/api/reporte/{id}/rectificar ─────────────
    public function rectificar(int $id): void
    {
        $postulanteId = $this->requireEditableSesion($id);
        $data        = $this->getAllInput();
        $monto       = round(abs((float)($data['monto'] ?? 0)), 2);
        $desc        = trim($data['descripcion'] ?? '');
        $tipoRectId  = (int)($data['tipo_rect_id'] ?? 0);

        if ($monto <= 0 || empty($desc) || !$tipoRectId) {
            $this->error('Tipo, monto y descripción son requeridos', 422);
        }

        $sesion = $this->repo->getSesionById($id);
        if (!$sesion) $this->error('Sesión no encontrada', 404);

        $this->repo->addRectificacion($id, $postulanteId, $tipoRectId, $monto, $desc);
        $this->success('Ajuste registrado. Base del siguiente turno actualizada.');
    }

    // ── POST /caja/api/sesion/{id}/pago-digital/{movId}/eliminar-admin ──
    public function eliminarPagoAdmin(int $sesionId, int $movId): void
    {
        $postulanteId = $this->requireAuth();
        if (($_SESSION['user_rol'] ?? '') !== 'ADMIN') {
            $this->error('Solo administradores pueden eliminar cobros', 403);
            return;
        }

        $d        = $this->getAllInput();
        $password = $d['password'] ?? '';
        if (empty($password)) {
            $this->validationError('Se requiere tu contraseña para confirmar');
            return;
        }

        // Verificar contraseña del admin
        require_once __DIR__ . '/../Repositories/AuthRepository.php';
        $authRepo = new AuthRepository();
        $user     = $authRepo->findByPostulanteId($postulanteId);
        if (!$user || !password_verify($password, $user['password'])) {
            $this->error('Contraseña incorrecta', 403);
            return;
        }

        // Verificar que el movimiento pertenece a esta sesión
        $mov = $this->repo->getMovimientoById($movId, $sesionId);
        if (!$mov) {
            $this->error('Cobro no encontrado en esta sesión', 404);
            return;
        }

        // Eliminar el movimiento sin restricción de estado
        $this->repo->adminDeleteMovimiento($movId);

        // Si era un vale SoloBank, liberarlo para que vuelva a estar disponible
        require_once __DIR__ . '/../Repositories/SoloBankRepository.php';
        (new SoloBankRepository())->liberarVale($movId);

        // Actualizar detalle_cuadre con la nueva diferencia
        $this->repo->recalcularDiferenciaCompleta($sesionId);

        $this->success('Cobro eliminado', [
            'modo'  => $mov['modo_desc'],
            'monto' => $mov['monto'],
        ]);
    }

    // ── POST /caja/api/rectificacion/{id}/eliminar ─────────
    public function eliminarRectificacion(int $rectId): void
    {
        $postulanteId = $this->requireAuth();
        if (($_SESSION['user_rol'] ?? '') !== 'ADMIN') {
            $this->error('Solo administradores pueden eliminar ajustes', 403);
        }

        $password = trim($this->getAllInput()['password'] ?? '');
        if (empty($password)) $this->error('La contraseña es requerida', 400);

        $result = $this->repo->deleteRectificacion($rectId, $postulanteId, $password);
        if ($result === true) {
            $this->success('Ajuste eliminado y saldo revertido.');
        } else {
            $this->error($result, 401);
        }
    }

    // ── POST /caja/api/sesion/{id}/sincronizar-base ────────
    // ── POST /caja/api/auditar-consistencia (testing) ──────
    // Compara dc.diferencia (lo que muestra /caja) contra la fórmula live
    // (lo que calculan /caja/reporte/{id} e /incidencias/{id}) en todas las
    // sesiones con cuadre, y registra cualquier desviación en log_consistencia_cuadre.
    public function auditarConsistencia(): void
    {
        $this->requireAuth();
        if (($_SESSION['user_rol'] ?? '') !== 'ADMIN') {
            $this->error('Solo administradores', 403);
            return;
        }
        $fix       = !empty($this->getAllInput()['fix']);
        $resultado = $this->repo->auditarConsistencia($fix);
        $this->success('Verificación completada', $resultado);
    }

    public function sincronizarBase(int $id): void
    {
        $this->requireAuth();
        if (($_SESSION['user_rol'] ?? '') !== 'ADMIN') {
            $this->error('Solo administradores', 403);
        }
        $sesion = $this->repo->getSesionById($id);
        if (!$sesion) $this->error('Sesión no encontrada', 404);

        $saldo = $this->repo->propagarBase($id);
        $this->success('Base del siguiente turno actualizada a S/ ' . number_format($saldo, 2));
    }

    // ── POST /caja/api/sesion/{id}/editar-fecha-turno ──────
    // Solo ADMIN: corrige la fecha de operación y/o el turno de un cuadre ya
    // creado (por ejemplo, al reabrir al día siguiente un cuadre que se eliminó
    // por error y que debía quedar registrado en el día original).
    public function editarFechaTurno(int $id): void
    {
        $this->requireAuth();
        if (($_SESSION['user_rol'] ?? '') !== 'ADMIN') {
            $this->error('Solo administradores', 403);
            return;
        }
        $data   = $this->getAllInput();
        $fecha  = trim($data['fecha_operacion'] ?? '');
        $turno  = (int)($data['turno_id'] ?? 0);

        if (!$fecha || !$turno) {
            $this->error('Fecha y turno son requeridos', 422);
            return;
        }

        $result = $this->repo->editarFechaTurno($id, $fecha, $turno);
        if ($result === true) $this->success('Cuadre actualizado');
        else $this->error($result, 422);
    }

    // ── POST /caja/api/sesion/{id}/editar-cajera ───────────
    // Solo ADMIN: corrige quién fue realmente la cajera de un cuadre ya creado
    // (por ejemplo, cuando se registró a nombre de quien lo abrió por error).
    public function editarCajeraSesion(int $id): void
    {
        $this->requireAuth();
        if (($_SESSION['user_rol'] ?? '') !== 'ADMIN') {
            $this->error('Solo administradores', 403);
            return;
        }
        $data = $this->getAllInput();
        $pid  = (int)($data['postulante_id'] ?? 0);
        if (!$pid) { $this->error('Selecciona la cajera', 422); return; }

        $result = $this->repo->editarCajera($id, $pid);
        if ($result === true) $this->success('Cajera actualizada');
        else $this->error($result, 422);
    }

    // ── Pagos digitales (cajera) ───────────────────────────
    // POST /caja/api/sesion/{id}/pago-digital
    public function addPagoDigital(int $sesionId): void
    {
        $postulanteId = $this->requireAuth();
        $data  = $this->getAllInput();
        $modo  = (int)($data['modo_id'] ?? 0);
        $monto = round((float)($data['monto'] ?? 0), 2);
        $num   = $data['numero_operacion'] ?? null;

        if (!$modo || $monto <= 0) $this->error('modo_id y monto son requeridos', 400);

        $sesion = $this->repo->getSesionById($sesionId);
        if (!$sesion || $sesion['bloqueado']) $this->error('Sesión bloqueada o no encontrada', 403);

        $id = $this->repo->addPagoDigital($sesionId, $postulanteId, $modo, $monto, $num ?: null);
        $this->success('Pago digital registrado', ['id_movimiento' => $id]);
    }

    // POST /caja/api/pago-digital/{id}/eliminar
    public function deletePagoDigitalRoute(int $movId): void
    {
        $this->requireAuth();
        $ok = $this->repo->deletePagoDigital($movId);
        $ok ? $this->success('Pago eliminado') : $this->error('Solo se pueden eliminar pagos PENDIENTE', 409);
    }

    // GET /caja/api/sesion/{id}/pagos-digitales
    public function getPagosDigitales(int $sesionId): void
    {
        $this->requireAuth();
        $pagos = $this->repo->getPagosDigitalesBySesion($sesionId);
        $this->success('OK', $pagos);
    }

    // ── Vista supervisor: PAGOS DIGITALES ──────────────────
    // GET /caja/pagos-digitales
    public function pagosDigitalesView(): void
    {
        $postulanteId = $this->requireAdmin();
        $basePath     = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
        $userName     = $_SESSION['user_name'] ?? 'Usuario';
        $userRol      = $_SESSION['user_rol']  ?? 'STAFF';

        $filtroEstado = $_GET['estado'] ?? '';
        $filtroCaja   = isset($_GET['caja'])   ? (int)$_GET['caja']   : 0;
        $filtroCajera = isset($_GET['cajera']) ? (int)$_GET['cajera'] : 0;
        $filtroModo   = $_GET['modo'] ?? '';
        $filtroMes    = $_GET['mes'] ?? date('Y-m');

        $pagos   = $this->repo->getAllPagosDigitales($filtroEstado, $filtroCaja, $filtroCajera, $filtroModo, $filtroMes);
        $cajas   = $this->repo->getCajasActivas();
        $cajeras = $this->repo->getCajerasActivas();

        require_once __DIR__ . '/../../views/caja/pagos-digitales.php';
    }

    // POST /caja/api/pago-digital/{id}/confirmar
    public function confirmarPago(int $movId): void
    {
        $postulanteId = $this->requireAdmin();
        $data   = $this->getAllInput();
        $estado = strtoupper(trim($data['estado'] ?? 'APROBADO'));

        if (!in_array($estado, ['APROBADO', 'RECHAZADO', 'OBSERVADO', 'PENDIENTE'], true)) {
            $this->error('Estado inválido', 422);
        }

        $this->repo->confirmarPagoDigital($movId, $postulanteId, $estado);
        $this->success("Pago marcado como {$estado}");
    }

    // ── Vista: AUDITORÍA unificada (solo admin) ────────────
    // GET /caja/auditoria
    public function auditoriaView(): void
    {
        $postulanteId = $this->requireAdmin();
        $basePath     = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
        $userName     = $_SESSION['user_name'] ?? 'Usuario';
        $userRol      = $_SESSION['user_rol']  ?? 'STAFF';

        $filtroCategoria = $_GET['categoria'] ?? '';
        $filtroCaja      = isset($_GET['caja'])   ? (int)$_GET['caja']   : 0;
        $filtroCajera    = isset($_GET['cajera']) ? (int)$_GET['cajera'] : 0;
        $filtroEstado    = strtoupper($_GET['estado'] ?? '');
        $filtroMes       = $_GET['mes'] ?? date('Y-m');

        $items   = $this->repo->getAuditoriaMovimientos($filtroCategoria, $filtroCaja, $filtroCajera, $filtroEstado, $filtroMes);
        $cajas   = $this->repo->getCajasActivas();
        $cajeras = $this->repo->getCajerasActivas();

        require_once __DIR__ . '/../../views/caja/auditoria.php';
    }

    // POST /caja/api/auditoria/{categoria}/{id}/revisar
    public function apiRevisarAuditoria(string $categoria, int $id): void
    {
        $postulanteId = $this->requireAdmin();
        $categoria    = strtoupper($categoria);
        $data         = $this->getAllInput();
        $accion       = strtoupper(trim($data['accion'] ?? ''));
        $observacion  = trim($data['observacion'] ?? '') ?: null;
        $password     = $data['password'] ?? '';

        if (!in_array($accion, ['APROBAR', 'RECHAZAR', 'PENDIENTE'], true)) {
            $this->error('Acción inválida', 422);
            return;
        }
        if ($accion === 'RECHAZAR' && !$observacion) {
            $this->error('Escribe el motivo del rechazo', 422);
            return;
        }
        if (in_array($accion, ['APROBAR', 'RECHAZAR'], true)) {
            if (empty($password)) {
                $this->error('Se requiere tu contraseña para confirmar', 422);
                return;
            }
            require_once __DIR__ . '/../Repositories/AuthRepository.php';
            $authRepo = new AuthRepository();
            $user     = $authRepo->findByPostulanteId($postulanteId);
            if (!$user || !password_verify($password, $user['password'])) {
                $this->error('Contraseña incorrecta', 403);
                return;
            }
        }

        $this->ejecutarRevisionAuditoria($categoria, $id, $postulanteId, $accion, $observacion);
    }

    /**
     * POST /caja/api/reporte/{categoria}/{id}/revisar — aprobar/rechazar directo desde el
     * cuadre (sin contraseña: admin y supervisor ya están autenticados en su sesión). No
     * incluye "volver a pendiente" — revertir un rechazo se hace desde /caja/auditoria.
     */
    public function apiRevisarDesdeReporte(string $categoria, int $id): void
    {
        $postulanteId = $this->requireAdminOrSupervisor();
        $categoria    = strtoupper($categoria);
        $data         = $this->getAllInput();
        $accion       = strtoupper(trim($data['accion'] ?? ''));
        $observacion  = trim($data['observacion'] ?? '') ?: null;

        if (!in_array($accion, ['APROBAR', 'RECHAZAR'], true)) {
            $this->error('Acción inválida', 422);
            return;
        }
        if ($accion === 'RECHAZAR' && !$observacion) {
            $this->error('Escribe el motivo del rechazo', 422);
            return;
        }

        $this->ejecutarRevisionAuditoria($categoria, $id, $postulanteId, $accion, $observacion);
    }

    private function ejecutarRevisionAuditoria(string $categoria, int $id, int $postulanteId, string $accion, ?string $observacion): void
    {
        if ($categoria === 'COBROS') {
            $estado = ['APROBAR' => 'APROBADO', 'RECHAZAR' => 'RECHAZADO', 'PENDIENTE' => 'PENDIENTE'][$accion];
            $ok = $this->repo->confirmarPagoDigital($id, $postulanteId, $estado, $observacion);
        } else {
            $ok = $this->repo->revisarAuditoria($categoria, $id, $postulanteId, $accion, $observacion);
        }
        if (!$ok) { $this->error('Registro no encontrado', 404); return; }

        $mensajes = ['APROBAR' => 'Aprobado', 'RECHAZAR' => 'Rechazado', 'PENDIENTE' => 'Vuelto a pendiente'];
        $this->success($mensajes[$accion]);
    }

    // ── API: catálogos ─────────────────────────────────────
    public function apiCatalogos(): void
    {
        $this->requireAuth();
        $this->success('OK', [
            'locales'      => $this->repo->getLocales(),
            'turnos'       => $this->repo->getTurnos(),
            'conceptos'    => $this->repo->getConceptosGasto(),
            'staff'        => $this->repo->getStaffActivo(),
            'tiposEgreso'  => $this->repo->getTiposEgreso(),
        ]);
    }

    public function apiCajasByLocal(int $localId): void
    {
        $this->requireAuth();
        $this->success('OK', $this->repo->getCajasByLocal($localId));
    }

    public function apiSaldoBase(int $cajaId): void
    {
        $this->requireAuth();
        $this->success('OK', ['saldo_base' => $this->repo->getSaldoBase($cajaId)]);
    }

    // ── POST /caja/api/sesion/{id}/ajuste-esperado ────────
    public function addAjusteEsperado(int $id): void
    {
        $postulanteId = $this->requireEditableSesion($id);
        $data  = $this->getAllInput();
        $tipo  = $data['tipo']   ?? 'COBRO';
        $accion = $data['accion'] ?? '';
        $desc  = trim($data['descripcion'] ?? '');
        $monto = round(abs((float)($data['monto'] ?? 0)), 2);

        $tiposValidos = ['COBRO','PERSONAL','LOCAL','COMPRA','DEPOSITO','OTRO'];
        if (!in_array($tipo, $tiposValidos) || !in_array($accion, ['AGREGAR','QUITAR']) || $monto <= 0) {
            $this->error('Tipo, acción y monto son requeridos', 422);
        }
        if ($tipo !== 'OTRO' && empty($desc) && empty($data['ref_id'])) {
            $this->error('Completa los campos requeridos', 422);
        }

        $sesion = $this->repo->getSesionById($id);
        if (!$sesion) $this->error('Sesión no encontrada', 404);

        $extra = [
            'modo_id'        => isset($data['modo_id'])        ? (int)$data['modo_id']   : null,
            'ref_id'         => isset($data['ref_id'])         ? (int)$data['ref_id']    : null,
            'ref2_id'        => isset($data['ref2_id'])        ? (int)$data['ref2_id']   : null,
            'tipo_documento' => $data['tipo_documento'] ?? null,
            'tipo_pago'      => $data['tipo_pago']      ?? null,
        ];

        $this->repo->addAjusteEsperado($id, $tipo, $accion, $desc, $monto, $postulanteId, $extra);
        $this->success('Corrección registrada.');
    }

    // ── POST /caja/api/ajuste-esperado/{id}/eliminar ───────
    public function deleteAjusteEsperado(int $ajusteId): void
    {
        $postulanteId = $this->requireAuth();
        if (($_SESSION['user_rol'] ?? '') !== 'ADMIN') $this->error('Solo administradores', 403);

        $password = trim($this->getAllInput()['password'] ?? '');
        if (empty($password)) $this->error('La contraseña es requerida', 400);

        $result = $this->repo->deleteAjusteEsperado($ajusteId, $postulanteId, $password);
        if ($result === true) $this->success('Ajuste eliminado.');
        else $this->error($result, 401);
    }

    // ── POST /caja/api/gasto/{modo}/{id}/editar ────────────
    public function editarGasto(string $modo, int $id): void
    {
        $postulanteId = $this->requireAuth();
        if (($_SESSION['user_rol'] ?? '') !== 'ADMIN') $this->error('Solo administradores', 403);

        $data     = $this->getAllInput();
        $monto    = round((float)($data['monto'] ?? 0), 2);
        $texto    = isset($data['texto']) ? trim((string)$data['texto']) : null;
        $password = trim($data['password'] ?? '');
        if (empty($password)) $this->error('La contraseña es requerida', 400);

        $result = $this->repo->editarGasto($modo, $id, $monto, $texto ?: null, $postulanteId, $password);
        if ($result === true) $this->success('Egreso actualizado.');
        else $this->error($result, 401);
    }

    // ── POST /caja/api/gasto/{modo}/{id}/eliminar ──────────
    public function eliminarGasto(string $modo, int $id): void
    {
        $postulanteId = $this->requireAuth();
        if (($_SESSION['user_rol'] ?? '') !== 'ADMIN') $this->error('Solo administradores', 403);

        $password = trim($this->getAllInput()['password'] ?? '');
        if (empty($password)) $this->error('La contraseña es requerida', 400);

        $result = $this->repo->eliminarGasto($modo, $id, $postulanteId, $password);
        if ($result === true) $this->success('Egreso eliminado.');
        else $this->error($result, 401);
    }

    // ── POST /caja/api/sesion/{id}/comentario ─────────────
    public function guardarComentario(int $id): void
    {
        $this->requireAuth();
        $comentario = trim($this->getAllInput()['comentario'] ?? '');
        if (mb_strlen($comentario) > 500) $this->error('Máximo 500 caracteres', 422);

        $db = \Database::getConnection();
        $db->prepare("UPDATE sesion_caja SET comentario_cajera = :c WHERE id_sesion = :id")
           ->execute(['c' => $comentario ?: null, 'id' => $id]);

        $this->success('Comentario guardado.');
    }

    // ── POST /caja/api/sesion/{id}/respuesta ───────────────
    public function guardarRespuesta(int $id): void
    {
        $postulanteId = $this->requireAuth();
        if (($_SESSION['user_rol'] ?? '') !== 'ADMIN') $this->error('Solo administradores', 403);

        $data     = $this->getAllInput();
        $respuesta = trim($data['respuesta'] ?? '');
        $password  = trim($data['password']  ?? '');

        if (empty($password))  $this->error('La contraseña es requerida', 400);
        if (empty($respuesta)) $this->error('La respuesta no puede estar vacía', 422);

        if (!$this->repo->verificarPasswordAdmin($postulanteId, $password)) {
            $this->error('Contraseña incorrecta', 401);
        }

        $db = \Database::getConnection();
        $db->prepare("UPDATE sesion_caja SET respuesta_admin = :r WHERE id_sesion = :id")
           ->execute(['r' => $respuesta, 'id' => $id]);

        $this->success('Respuesta guardada.');
    }

    // ── POST /caja/api/sesion/{id}/eliminar ───────────────
    public function eliminarSesion(int $id): void
    {
        $postulanteId = $this->requireAuth();
        if (($_SESSION['user_rol'] ?? '') !== 'ADMIN') {
            $this->error('Solo administradores pueden eliminar cuadres', 403);
        }

        $data     = $this->getAllInput();
        $password = trim($data['password'] ?? '');

        if (empty($password)) {
            $this->error('La contraseña es requerida', 400);
        }

        if (!$this->repo->verificarPasswordAdmin($postulanteId, $password)) {
            $this->error('Contraseña incorrecta', 401);
        }

        $sesion = $this->repo->getSesionById($id);
        if (!$sesion) $this->error('Sesión no encontrada', 404);

        $this->repo->eliminarSesion($id);
        $this->success('Cuadre eliminado correctamente');
    }

    // ── POST /caja/api/sesion/{id}/conteo ─────────────────
    public function apiConteo(int $id): void
    {
        $postulanteId = $this->requireEditableSesion($id);
        $data = $this->getAllInput();

        $exterior      = round((float)($data['exterior']       ?? 0), 2);
        $monedas       = round((float)($data['monedas']        ?? 0), 2);
        $billetesCaja  = round((float)($data['billetes_caja']  ?? 0), 2);
        $billetesFuerte= round((float)($data['billetes_fuerte']?? 0), 2);
        $agenteBcp     = round((float)($data['agente_bcp']     ?? 0), 2);

        $sesion = $this->repo->getSesionById($id);
        if (!$sesion) { $this->error('Sesión no encontrada', 404); return; }

        $this->repo->updateConteo($id, $exterior, $monedas, $billetesCaja, $billetesFuerte, $agenteBcp, $postulanteId);

        $reporte = $this->repo->getReporte($id);
        $this->success('Conteo actualizado', [
            'total_efectivo_contado' => $reporte['detalle']['total_efectivo_contado'] ?? 0,
            'diferencia'             => $reporte['detalle']['diferencia']             ?? 0,
            'resultado_cuadre'       => $reporte['detalle']['resultado_cuadre']       ?? '',
        ]);
    }

    // ── POST /caja/api/sesion/{id}/num-ops-bcp ────────────
    public function apiUpdateNumOpsBcp(int $id): void
    {
        $postulanteId = $this->requireAuth();
        if (($_SESSION['user_rol'] ?? '') !== 'ADMIN') {
            $this->error('Solo administradores pueden editar operaciones BCP', 403);
            return;
        }

        $data       = $this->getAllInput();
        $opIngresos = (int)($data['oper_ingresos_bcp'] ?? -1);
        $opSalida   = (int)($data['oper_salida_bcp']   ?? -1);
        $opOtros    = (int)($data['oper_otros_bcp']    ?? -1);
        $opSeguros  = (int)($data['oper_seguros_bcp']  ?? 0);
        $opTarjetas = (int)($data['oper_tarjetas_bcp'] ?? 0);
        if ($opIngresos < 0 || $opSalida < 0 || $opOtros < 0 || $opSeguros < 0 || $opTarjetas < 0) {
            $this->error('Valor inválido', 422);
            return;
        }

        $sesion = $this->repo->getSesionById($id);
        if (!$sesion) { $this->error('Sesión no encontrada', 404); return; }

        $this->repo->updateNumOperacionesBcp($id, $opIngresos, $opSalida, $opOtros, $postulanteId, $opSeguros, $opTarjetas);
        $this->success('Operaciones BCP actualizadas', [
            'num_operaciones_bcp' => $opIngresos + $opSalida
                + ($opSeguros  * \CajaRepository::EQUIV_SEGURO_BCP)
                + ($opTarjetas * \CajaRepository::EQUIV_TARJETA_BCP),
            'oper_ingresos_bcp'   => $opIngresos,
            'oper_salida_bcp'     => $opSalida,
            'oper_otros_bcp'      => $opOtros,
            'oper_seguros_bcp'    => $opSeguros,
            'oper_tarjetas_bcp'   => $opTarjetas,
        ]);
    }

    // ── POST /caja/api/sesion/{id}/confirmar-visita ────────
    // "Portero": confirma la contraseña de quien entra a ver un cuadre ya
    // cerrado y deja registro (empadronamiento) de quién y cuándo.
    public function confirmarVisita(int $id): void
    {
        $postulanteId = $this->requireAuth();
        $data         = $this->getAllInput();
        $password     = trim($data['password'] ?? '');
        $origen       = in_array($data['origen'] ?? '', ['REPORTE', 'INCIDENCIA'], true)
                      ? $data['origen'] : 'REPORTE';

        if (empty($password)) { $this->error('La contraseña es requerida', 400); return; }
        if (!$this->repo->verificarPasswordAdmin($postulanteId, $password)) {
            $this->error('Contraseña incorrecta', 401);
            return;
        }

        $sesion = $this->repo->getSesionById($id);
        if (!$sesion) { $this->error('Sesión no encontrada', 404); return; }

        $this->repo->registrarVisita($id, $postulanteId, $origen);
        $_SESSION['visita_confirmada'][$id] = time();
        $this->success('Acceso confirmado');
    }

    // ── Helper: transacción ────────────────────────────────
    private function db_transaction(callable $fn): void
    {
        $db = \Database::getConnection();
        $db->beginTransaction();
        try {
            $fn();
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            $this->error('Error interno: ' . $e->getMessage(), 500);
        }
    }

    // ── Transferencias de saldo entre locales ──────────────

    public function transferirIndex(): void
    {
        $this->requireAuth();
        if (($_SESSION['user_rol'] ?? '') !== 'ADMIN') {
            header('Location: ' . APP_BASE_PATH . '/caja');
            exit;
        }
        $basePath       = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
        $saldos         = $this->repo->getSaldosBaseCajas();
        $transferencias = $this->repo->getTransferencias();
        require_once __DIR__ . '/../../views/caja/transferir.php';
    }

    public function solicitarTransferencia(): void
    {
        $userId  = $this->requireAuth();
        if (($_SESSION['user_rol'] ?? '') !== 'ADMIN') {
            $this->error('Solo administradores pueden solicitar transferencias de saldo', 403);
            return;
        }
        $data    = $this->getAllInput();
        $origen  = (int)($data['caja_origen_id']  ?? 0);
        $destino = (int)($data['caja_destino_id'] ?? 0);
        $monto   = (float)($data['monto'] ?? 0);
        $notas   = trim($data['notas'] ?? '') ?: null;

        if (!$origen || !$destino || $monto <= 0) $this->error('Origen, destino y monto son requeridos', 422);
        if ($origen === $destino) $this->error('El origen y destino no pueden ser la misma caja', 422);

        $id = $this->repo->crearTransferencia($origen, $destino, $monto, $notas, $userId);
        $this->success('Solicitud creada', ['id' => $id]);
    }

    public function confirmarTransferenciaAction(int $id): void
    {
        $userId      = $this->requireAuth();
        if (($_SESSION['user_rol'] ?? '') !== 'ADMIN') {
            $this->error('Solo administradores pueden confirmar transferencias de saldo', 403);
            return;
        }
        $data        = $this->getAllInput();
        $password    = trim($data['password'] ?? '');
        $comprobante = trim($data['numero_comprobante'] ?? '');

        if (!$password || !$comprobante) $this->error('Contraseña y número de comprobante son requeridos', 422);

        $result = $this->repo->confirmarTransferencia($id, $userId, $password, $comprobante);
        if ($result === true) $this->success('Transferencia confirmada');
        else $this->error($result, 401);
    }

    public function anularTransferenciaAction(int $id): void
    {
        $userId = $this->requireAuth();
        if (($_SESSION['user_rol'] ?? '') !== 'ADMIN') {
            $this->error('Solo administradores pueden anular transferencias de saldo', 403);
            return;
        }

        $password = trim($this->getAllInput()['password'] ?? '');
        if (!$password) $this->error('La contraseña es requerida', 400);

        $result = $this->repo->anularTransferencia($id, $userId, $password);
        if ($result === true) $this->success('Transferencia anulada');
        else $this->error($result, 401);
    }

    // Detecta (y opcionalmente repara) transferencias CONFIRMADAS que quedaron
    // apuntando a un cuadre que fue eliminado (sesion_aplicada_*_id huérfano).
    public function auditarTransferenciasAction(): void
    {
        $this->requireAuth();
        if (($_SESSION['user_rol'] ?? '') !== 'ADMIN') {
            $this->error('Solo administradores', 403);
            return;
        }
        $fix       = !empty($this->getAllInput()['fix']);
        $resultado = $this->repo->auditarTransferenciasHuerfanas($fix);
        $this->success('Verificación completada', $resultado + ['fix' => $fix]);
    }
}
