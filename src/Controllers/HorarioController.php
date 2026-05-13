<?php

require_once __DIR__ . '/../Core/Controller.php';
require_once __DIR__ . '/../Repositories/HorarioRepository.php';
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';

class HorarioController extends Controller
{
    private HorarioRepository $repo;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->repo = new HorarioRepository();
    }

    private function requireAuth(): int
    {
        $auth = '';
        if (function_exists('getallheaders')) {
            $h    = getallheaders();
            $auth = $h['Authorization'] ?? $h['authorization'] ?? '';
        }
        if (empty($auth)) $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (str_starts_with($auth, 'Bearer ')) {
            $payload = AuthMiddleware::requireAuth();
            return (int)($payload['sub'] ?? 0);
        }

        if (!isset($_SESSION['user_id'])) {
            $isApi = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
                  || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
            if ($isApi) { $this->error('No autenticado', 401); exit; }
            header('Location: ' . APP_BASE_PATH . '/login');
            exit;
        }
        return (int)$_SESSION['user_id'];
    }

    private function isAdmin(): bool
    {
        return ($_SESSION['user_rol'] ?? '') === 'ADMIN';
    }

    // ── GET /horario ── Semana actual (read-only) ──────────
    public function index(): void
    {
        $postulanteId = $this->requireAuth();
        $basePath     = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
        $userName     = $_SESSION['user_name'] ?? 'Usuario';
        $userRol      = $_SESSION['user_rol']  ?? 'STAFF';
        $esAdmin      = $this->isAdmin();

        // Rolling: cierra semanas vencidas y garantiza W+1 y W+2
        $this->repo->cerrarSemanasVencidas();
        $this->repo->asegurarSemanasRolling();

        $semana        = $this->repo->getSemanaVigente();
        $semanaProxima = $this->repo->getSemanaProxima();
        $slotsConfig   = $this->repo->getSlotsConfig();
        $roles         = $this->repo->getRoles();

        require_once __DIR__ . '/../../views/horario/actual.php';
    }

    // ── GET /horario/siguiente ── Próxima semana (editable) ─
    public function siguiente(): void
    {
        $postulanteId = $this->requireAuth();
        $basePath     = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
        $userName     = $_SESSION['user_name'] ?? 'Usuario';
        $userRol      = $_SESSION['user_rol']  ?? 'STAFF';
        $esAdmin      = $this->isAdmin();

        $this->repo->cerrarSemanasVencidas();
        $this->repo->asegurarSemanasRolling();

        $slotsConfig = $this->repo->getSlotsConfig();
        $roles       = $this->repo->getRoles();

        // Solo semanas futuras (que aún no han empezado)
        $db = \Database::getConnection();
        $stmtF = $db->query(
            "SELECT * FROM semana WHERE fecha_inicio > CURDATE() ORDER BY fecha_inicio ASC LIMIT 1"
        );
        $semanas = $stmtF->fetchAll();

        // Si viene ?semana= y es futura, cargarla; si no, la próxima por defecto
        $semana = null;
        if (!empty($_GET['semana'])) {
            $candidata = $this->repo->getSemanaById((int)$_GET['semana']);
            if ($candidata && $candidata['fecha_inicio'] > date('Y-m-d')) {
                $semana = $candidata;
            }
        }
        if (!$semana) $semana = $this->repo->getSemanaProxima();

        $editable = false;
        if ($semana) {
            $editable = ($semana['estado'] === 'ABIERTA') || $esAdmin;
        }

        require_once __DIR__ . '/../../views/horario/index.php';
    }

    // ── GET /horario/solicitudes ───────────────────────────
    public function solicitudes(): void
    {
        $postulanteId = $this->requireAuth();
        $basePath     = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
        $userName     = $_SESSION['user_name'] ?? 'Usuario';
        $esAdmin      = $this->isAdmin();

        $fecha       = $_GET['fecha'] ?? date('Y-m-d');
        $slots       = $this->repo->getSlotsByFecha($fecha);
        $historial   = $this->repo->getSolicitudesRecientes(20);
        $roles       = $this->repo->getRoles();

        require_once __DIR__ . '/../../views/horario/solicitudes.php';
    }

    // ── POST /horario/api/solicitud/cubrir ─────────────────
    public function cubrir(): void
    {
        $postulanteId = $this->requireAuth();
        $data         = $this->getAllInput();

        $slotId    = (int)($data['slot_id']   ?? 0);
        $password  = trim($data['password']   ?? '');
        $comentario = trim($data['comentario'] ?? '');

        if (!$slotId || !$password) $this->error('slot_id y contraseña requeridos', 400);

        // Verificar contraseña
        $db   = \Database::getConnection();
        $stmt = $db->prepare("SELECT password FROM usuario WHERE postulante_id = :pid LIMIT 1");
        $stmt->execute(['pid' => $postulanteId]);
        $hash = $stmt->fetchColumn();

        if (!$hash || !password_verify($password, $hash)) {
            $this->error('Contraseña incorrecta', 401);
        }

        $result = $this->repo->cubrirSlot($slotId, $postulanteId, $comentario ?: null);
        if ($result !== 'ok') $this->error($result, 409);

        $this->success('Turno asignado correctamente');
    }

    // ── GET /horario/informacion ───────────────────────────
    public function informacion(): void
    {
        $this->requireAuth();
        $basePath  = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
        $userName  = $_SESSION['user_name'] ?? 'Usuario';
        $esAdmin   = $this->isAdmin();
        $conceptos = $this->repo->getConceptosPenalidad();

        require_once __DIR__ . '/../../views/horario/informacion.php';
    }

    // ── POST /admin/api/penalidad/{id} ────────────────────
    public function actualizarPenalidad(int $id): void
    {
        $this->requireAuth();
        if (!$this->isAdmin()) $this->error('Solo administradores', 403);
        $data  = $this->getAllInput();
        $monto = round((float)($data['monto'] ?? 0), 2);
        $db    = \Database::getConnection();
        $db->prepare("UPDATE concepto_penalidad SET monto = :m WHERE id_concepto = :id")
           ->execute(['m' => $monto, 'id' => $id]);
        $this->success('Monto actualizado');
    }

    // ── GET /horario/historial ─────────────────────────────
    public function historial(): void
    {
        $postulanteId = $this->requireAuth();
        $basePath     = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
        $userName     = $_SESSION['user_name'] ?? 'Usuario';
        $esAdmin      = $this->isAdmin();

        $semanas     = $this->repo->getSemanasHistorial(20);
        $semanaId    = isset($_GET['semana']) ? (int)$_GET['semana'] : ($semanas[0]['id_semana'] ?? null);
        $semana      = $semanaId ? $this->repo->getSemanaById($semanaId) : null;
        $slotsConfig = $this->repo->getSlotsConfig();
        $roles       = $this->repo->getRoles();

        require_once __DIR__ . '/../../views/horario/historial.php';
    }

    // ── POST /horario/api/semana/crear ─────────────────────
    public function crearSemana(): void
    {
        $this->requireAuth();
        if (!$this->isAdmin()) $this->error('Solo el administrador puede crear semanas', 403);
        $semana = $this->repo->crearProximaSemana();
        $this->success('Semana creada', $semana);
    }

    // ── POST /horario/api/semana/{id}/cerrar ───────────────
    public function cerrarSemana(int $id): void
    {
        $this->requireAuth();
        if (!$this->isAdmin()) $this->error('Solo el administrador puede cerrar semanas', 403);
        $this->repo->cerrarSemana($id);
        $this->success('Semana cerrada');
    }

    // ── GET /horario/api/semana/{id} ───────────────────────
    public function getSlots(int $semanaId): void
    {
        $this->requireAuth();
        $slots = $this->repo->getSlotsBySemana($semanaId);
        $this->success('OK', $slots);
    }

    // ── GET /horario/api/trabajadores ─────────────────────
    public function getTrabajadores(): void
    {
        $this->requireAuth();
        if (!$this->isAdmin()) $this->error('Solo administradores', 403);
        $this->success('OK', $this->repo->getTrabajadores());
    }

    // ── POST /horario/api/slot/{id}/liberar-admin ─────────
    public function liberarSlotAdmin(int $slotId): void
    {
        $postulanteId = $this->requireAuth();
        if (!$this->isAdmin()) $this->error('Solo administradores', 403);

        $password = trim($this->getAllInput()['password'] ?? '');
        if (empty($password)) $this->error('La contraseña es requerida', 400);

        $result = $this->repo->liberarSlotAdmin($slotId, $postulanteId, $password);
        if ($result === 'ok') $this->success('Trabajador removido del horario.');
        else $this->error($result, 401);
    }

    // ── POST /horario/api/solicitud/{id}/revertir ─────────
    public function revertirCobertura(int $id): void
    {
        $postulanteId = $this->requireAuth();
        if (!$this->isAdmin()) $this->error('Solo administradores pueden revertir coberturas', 403);

        $password = trim($this->getAllInput()['password'] ?? '');
        if (empty($password)) $this->error('La contraseña es requerida', 400);

        $result = $this->repo->revertirCobertura($id, $postulanteId, $password);
        if ($result === 'ok') $this->success('Cobertura revertida. Slot restaurado al trabajador original.');
        else $this->error($result, 401);
    }

    // ── POST /horario/api/slot/asignar ─────────────────────
    public function asignarSlot(): void
    {
        $postulanteId = $this->requireAuth();
        $data         = $this->getAllInput();

        $slotId    = (int)($data['slot_id']   ?? 0);
        $semanaId  = (int)($data['semana_id'] ?? 0);
        $targetId  = (int)($data['target_id'] ?? 0);

        // Admin puede asignar en nombre de otro trabajador
        if ($this->isAdmin() && $targetId > 0) {
            $postulanteId = $targetId;
        }

        if (!$slotId || !$semanaId) $this->error('slot_id y semana_id requeridos', 400);

        // Verificar semana abierta (salvo admin)
        $semana = $this->repo->getSemanaById($semanaId);
        if (!$semana) $this->error('Semana no encontrada', 404);

        $hoy = new DateTime('now', new DateTimeZone('America/Lima'));
        $fin = new DateTime($semana['fecha_fin'] . ' 23:59:59');

        // LIMPIEZA es siempre editable (cualquier día, semana abierta o cerrada)
        $slotInfo   = $this->repo->getSlotById($slotId);
        $esLimpieza = ($slotInfo['rol_puesto'] ?? '') === 'LIMPIEZA';

        if (!$esLimpieza && !$this->isAdmin() && ($semana['estado'] === 'CERRADA' || $hoy > $fin)) {
            $this->error('La semana está cerrada. Solo el administrador puede modificarla.', 403);
        }

        $result = $this->repo->asignarSlot($slotId, $semanaId, $postulanteId);
        if ($result !== 'ok') $this->error($result, 409);

        $this->success('Turno asignado');
    }

    // ── POST /horario/api/slot/liberar ─────────────────────
    public function liberarSlot(): void
    {
        $postulanteId = $this->requireAuth();
        $data         = $this->getAllInput();
        $slotId       = (int)($data['slot_id'] ?? 0);
        $semanaId     = (int)($data['semana_id'] ?? 0);

        if (!$slotId) $this->error('slot_id requerido', 400);

        $semana     = $this->repo->getSemanaById($semanaId);
        $hoy        = new DateTime('now', new DateTimeZone('America/Lima'));
        $fin        = new DateTime($semana['fecha_fin'] . ' 23:59:59');
        $slotInfo   = $this->repo->getSlotById($slotId);
        $esLimpieza = ($slotInfo['rol_puesto'] ?? '') === 'LIMPIEZA';

        if (!$esLimpieza && !$this->isAdmin() && ($semana['estado'] === 'CERRADA' || $hoy > $fin)) {
            $this->error('La semana está cerrada. Solo el administrador puede modificarla.', 403);
        }

        $result = $this->repo->liberarSlot($slotId, $postulanteId);
        if ($result !== 'ok') $this->error($result, 409);

        $this->success('Turno liberado');
    }
}
