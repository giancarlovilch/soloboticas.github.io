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

        require_once __DIR__ . '/../../views/caja/sesion.php';
    }

    // ── POST /caja/api/sesion/crear ────────────────────────
    public function crearSesion(): void
    {
        $postulanteId = $this->requireAuth();
        $data = $this->getAllInput();

        if (empty($data['caja_id']) || empty($data['turno_id'])) {
            $this->error('Caja y turno son requeridos', 400);
        }

        // Bloquear solo si ya hay una sesión ACTIVA (abierta o pendiente de venta).
        // Se permiten múltiples cuadres por día siempre que el anterior esté cerrado.
        $db  = \Database::getConnection();
        $chk = $db->prepare(
            "SELECT id_sesion, estado FROM sesion_caja
             WHERE caja_id = :cid AND turno_id = :tid
               AND fecha_operacion = CURDATE()
               AND estado IN ('ABIERTA','PENDIENTE_VENTA')
             LIMIT 1"
        );
        $chk->execute(['cid' => (int)$data['caja_id'], 'tid' => (int)$data['turno_id']]);
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
        $isAdminCrear = ($_SESSION['user_rol'] ?? '') === 'ADMIN';
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
                   AND hs.fecha_dia     = CURDATE()
                   AND hs.postulante_id = :vid
                   AND rh.codigo        = 'VENDEDORA'
                 LIMIT 1"
            );
            $chkHorario->execute([
                'caja_id' => (int)$data['caja_id'],
                'tid'     => (int)$data['turno_id'],
                'vid'     => $vendedorId,
            ]);
            if (!$chkHorario->fetch()) {
                $this->error(
                    'Esta persona no está asignada en el horario para este local y turno hoy. '
                    . 'Corrija el horario primero en /horario.',
                    409
                );
            }
        }

        $data['postulante_id'] = $postulanteId;
        $data['vendedor_id']   = $vendedorId;
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

        $data = $this->repo->getReporte($id);
        extract($data); // $sesion, $detalle, $venta, $gastos, $rectifs

        $transferencias = $this->repo->getTransferenciasAplicadas($id);

        // Vales SoloBank disponibles para asignar retroactivamente
        require_once __DIR__ . '/../Repositories/SoloBankRepository.php';
        $sbRepo        = new SoloBankRepository();
        $soloBankVales = $sbRepo->getValesDisponibles();

        // Incidencia contable de esta sesión (si existe)
        $incidenciaContable = $this->incRepo->getBySesion($id)[0] ?? null;

        // Historial de cambios post-cierre
        $auditoria = $this->repo->getAuditoria($id);

        require_once __DIR__ . '/../../views/caja/reporte.php';
    }

    // ── POST /caja/api/sesion/{id}/corregir-venta ─────────
    public function corregirVenta(int $id): void
    {
        $postulanteId = $this->requireAuth();
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
        $postulanteId = $this->requireAuth();
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
        $this->repo->recalcularDiferencia($sesionId);

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
        $postulanteId = $this->requireAuth();
        $basePath     = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
        $userName     = $_SESSION['user_name'] ?? 'Usuario';
        $userRol      = $_SESSION['user_rol']  ?? 'STAFF';

        $filtroEstado = $_GET['estado'] ?? '';
        $filtroLocal  = isset($_GET['local']) ? (int)$_GET['local'] : 0;
        $filtroCaja   = isset($_GET['caja'])  ? (int)$_GET['caja']  : 0;
        $pagos        = $this->repo->getAllPagosDigitales($filtroEstado, $filtroLocal, $filtroCaja);
        $locales      = $this->repo->getLocales();
        $cajas        = $filtroLocal > 0 ? $this->repo->getCajasByLocal($filtroLocal) : [];

        require_once __DIR__ . '/../../views/caja/pagos-digitales.php';
    }

    // POST /caja/api/pago-digital/{id}/confirmar
    public function confirmarPago(int $movId): void
    {
        $postulanteId = $this->requireAuth();
        $data   = $this->getAllInput();
        $estado = strtoupper(trim($data['estado'] ?? 'APROBADO'));

        if (!in_array($estado, ['APROBADO', 'RECHAZADO', 'OBSERVADO', 'PENDIENTE'], true)) {
            $this->error('Estado inválido', 422);
        }

        $this->repo->confirmarPagoDigital($movId, $postulanteId, $estado);
        $this->success("Pago marcado como {$estado}");
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
        $postulanteId = $this->requireAuth();
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
        $postulanteId = $this->requireAuth();
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
        $basePath       = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
        $saldos         = $this->repo->getSaldosBaseCajas();
        $transferencias = $this->repo->getTransferencias();
        require_once __DIR__ . '/../../views/caja/transferir.php';
    }

    public function solicitarTransferencia(): void
    {
        $userId  = $this->requireAuth();
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
}
