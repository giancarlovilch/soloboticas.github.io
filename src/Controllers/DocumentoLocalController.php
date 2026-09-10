<?php

require_once __DIR__ . '/../Core/Controller.php';
require_once __DIR__ . '/../Repositories/DocumentoLocalRepository.php';

/**
 * Gestión de Locales — documentación, vigencias, firma y presupuesto.
 *
 * Quién entra:
 *  - ADMIN     → todo (además arma el catálogo desde admin/dashboard?page=documentos).
 *  - SUPERVISOR→ registra/actualiza documentos de cualquier local y los firma.
 *  - STAFF     → no entra.
 */
class DocumentoLocalController extends Controller
{
    private DocumentoLocalRepository $repo;

    private const MAX_ARCHIVO   = 8 * 1024 * 1024; // 8 MB
    private const EXT_PERMITIDAS = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->repo = new DocumentoLocalRepository();
    }

    // ─────────────────────────────────────────────────────────────
    //  Accesos
    // ─────────────────────────────────────────────────────────────

    /**
     * Un endpoint /api/ debe responder JSON aunque la sesión haya caído; una vista
     * redirige al login. Sin esto, un fetch recibía un 302 con cuerpo vacío.
     */
    private function esApi(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')
            || str_contains(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '', '/api/');
    }

    /** ADMIN o SUPERVISOR. Devuelve el postulante_id de quien opera. */
    private function requireGestor(): int
    {
        if (!isset($_SESSION['user_id'])) {
            if ($this->esApi()) { $this->error('Sesión expirada.', 401); exit; }
            header('Location: ' . APP_BASE_PATH . '/login');
            exit;
        }
        if (!in_array($_SESSION['user_rol'] ?? '', ['ADMIN', 'SUPERVISOR'], true)) {
            if ($this->esApi()) { $this->error('Solo administrador o supervisor pueden gestionar la documentación.', 403); exit; }
            header('Location: ' . APP_BASE_PATH . '/staff');
            exit;
        }
        return (int)$_SESSION['user_id'];
    }

    private function requireAdmin(): int
    {
        $id = $this->requireGestor();
        if (($_SESSION['user_rol'] ?? '') !== 'ADMIN') {
            $this->error('Solo el administrador puede realizar esta acción.', 403);
            exit;
        }
        return $id;
    }

    private function esAdmin(): bool
    {
        return ($_SESSION['user_rol'] ?? '') === 'ADMIN';
    }

    // ─────────────────────────────────────────────────────────────
    //  Vistas
    // ─────────────────────────────────────────────────────────────

    /** GET /locales — portada: tarjeta por local + alertas de vencimiento. */
    public function index(): void
    {
        $this->requireGestor();

        $basePath  = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
        $userName  = $_SESSION['user_name'] ?? 'Usuario';
        $userRol   = $_SESSION['user_rol']  ?? 'STAFF';
        $esAdmin   = $this->esAdmin();

        $anio      = (int)date('Y');
        $resumen   = $this->repo->getResumenLocales($anio);
        $alertas   = $this->repo->getProximosVencimientos(60);
        $ultimos   = $this->repo->getLogs([], 12);
        $totalTipos = count($this->repo->getTipos(true));

        require_once __DIR__ . '/../../views/locales/index.php';
    }

    /** GET /locales/{id} — checklist de documentos de un local. */
    public function detalle($id): void
    {
        $this->requireGestor();

        $id    = (int)$id;
        $local = $this->repo->getLocal($id);
        if (!$local) { $this->notFound('Local no encontrado'); return; }

        $basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
        $userName = $_SESSION['user_name'] ?? 'Usuario';
        $userRol  = $_SESSION['user_rol']  ?? 'STAFF';
        $esAdmin  = $this->esAdmin();

        $filtroEstado = strtoupper($_GET['estado'] ?? '');
        $documentos   = $this->repo->getChecklist($id);

        if ($filtroEstado !== '') {
            $documentos = array_values(array_filter(
                $documentos,
                fn($d) => $d['estado_calc']['codigo'] === $filtroEstado
            ));
        }

        // Lo urgente primero, después por categoría
        usort($documentos, function ($a, $b) {
            $pa = DocumentoLocalRepository::prioridadEstado($a['estado_calc']['codigo']);
            $pb = DocumentoLocalRepository::prioridadEstado($b['estado_calc']['codigo']);
            if ($pa !== $pb) return $pa <=> $pb;
            $cmpNombre = strcmp($a['tipo_nombre'], $b['tipo_nombre']);
            // Mismo tipo y misma urgencia (p.ej. 2 termohigrómetros ambos vigentes):
            // que salgan en orden de unidad, no en el orden que devolvió la BD.
            return $cmpNombre !== 0 ? $cmpNombre : ($a['unidad'] <=> $b['unidad']);
        });

        $categorias = DocumentoLocalRepository::CATEGORIAS;
        $logs       = $this->repo->getLogs(['local_id' => $id], 40);
        $gastoAnio  = $this->repo->getPresupuesto((int)date('Y'), $id);

        require_once __DIR__ . '/../../views/locales/detalle.php';
    }

    /** GET /locales/logs — bitácora completa con filtros. */
    public function logs(): void
    {
        $this->requireGestor();

        $basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
        $userName = $_SESSION['user_name'] ?? 'Usuario';
        $userRol  = $_SESSION['user_rol']  ?? 'STAFF';

        $filtros = [
            'local_id' => isset($_GET['local']) ? (int)$_GET['local'] : 0,
            'accion'   => $_GET['accion'] ?? '',
            'desde'    => $_GET['desde']  ?? '',
            'hasta'    => $_GET['hasta']  ?? '',
        ];

        $logs    = $this->repo->getLogs($filtros, 500);
        $locales = $this->repo->getLocales();

        require_once __DIR__ . '/../../views/locales/logs.php';
    }

    /** GET /locales/presupuesto — cuánto costó la documentación. */
    public function presupuesto(): void
    {
        $this->requireGestor();

        $basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
        $userName = $_SESSION['user_name'] ?? 'Usuario';
        $userRol  = $_SESSION['user_rol']  ?? 'STAFF';

        $anio    = isset($_GET['anio'])  ? (int)$_GET['anio'] : (int)date('Y');
        $localId = isset($_GET['local']) ? (int)$_GET['local'] : 0;

        $data       = $this->repo->getPresupuesto($anio, $localId ?: null);
        $locales    = $this->repo->getLocales();
        $anios      = $this->repo->getAniosConGasto();
        $categorias = DocumentoLocalRepository::CATEGORIAS;

        require_once __DIR__ . '/../../views/locales/presupuesto.php';
    }

    // ─────────────────────────────────────────────────────────────
    //  API — documentos
    // ─────────────────────────────────────────────────────────────

    /** GET /locales/api/documento/{id} — ficha + historial de renovaciones + bitácora. */
    public function apiDocumento($id): void
    {
        $this->requireGestor();

        $id  = (int)$id;
        $doc = $this->repo->getDocumento($id);
        if (!$doc) { $this->error('Documento no encontrado', 404); return; }

        $this->success('OK', [
            'documento' => $doc,
            'historial' => $this->repo->getHistorialDocumento((int)$doc['tipo_id'], (int)$doc['local_id']),
            'logs'      => $this->repo->getLogs(['doc_id' => $id], 50),
        ]);
    }

    /** GET /locales/api/historial?tipo=&local= — periodos anteriores de un item. */
    public function apiHistorial(): void
    {
        $this->requireGestor();

        $tipoId  = (int)($_GET['tipo']  ?? 0);
        $localId = (int)($_GET['local'] ?? 0);
        $unidad  = max(1, (int)($_GET['unidad'] ?? 1));
        if ($tipoId <= 0 || $localId <= 0) { $this->error('Parámetros inválidos', 422); return; }

        $this->success('OK', [
            'historial' => $this->repo->getHistorialDocumento($tipoId, $localId, $unidad),
            'logs'      => $this->repo->getLogs(['tipo_id' => $tipoId, 'local_id' => $localId], 60),
        ]);
    }

    /**
     * POST /locales/api/documento/guardar
     * Acepta JSON o multipart (cuando viene el archivo escaneado).
     */
    public function apiGuardar(): void
    {
        $userId = $this->requireGestor();
        $data   = $this->getAllInput();

        $res = $this->repo->guardarDocumento($data, $userId);
        if (!$res['ok']) { $this->error($res['msg'], 422); return; }

        $docId = (int)$res['id'];

        // El adjunto es opcional: si falla, el documento ya quedó guardado y se avisa.
        if (!empty($_FILES['archivo']['name'])) {
            $up = $this->guardarAdjunto($_FILES['archivo'], (int)$data['local_id'], $docId, $userId);
            if ($up !== true) {
                $this->success($res['msg'] . ' El archivo NO se subió: ' . $up, ['id' => $docId, 'archivo_error' => $up]);
                return;
            }
        }

        $this->success($res['msg'], ['id' => $docId]);
    }

    /** POST /locales/api/documento/{id}/estado */
    public function apiEstado($id): void
    {
        $userId = $this->requireGestor();
        $id     = (int)$id;
        $data   = $this->getAllInput();

        $nuevo = strtoupper($data['estado'] ?? '');
        $obs   = $data['observacion'] ?? null;

        $res = $this->repo->cambiarEstado($id, $nuevo, $userId, $obs);
        if (!$res['ok']) { $this->error($res['msg'], 422); return; }

        $this->success($res['msg']);
    }

    /** POST /locales/api/documento/{id}/archivo — sube o reemplaza el adjunto. */
    public function apiSubirArchivo($id): void
    {
        $userId = $this->requireGestor();
        $id     = (int)$id;

        $doc = $this->repo->getDocumento($id);
        if (!$doc) { $this->error('Documento no encontrado', 404); return; }
        if (empty($_FILES['archivo']['name'])) { $this->error('No se recibió ningún archivo.', 422); return; }

        $res = $this->guardarAdjunto($_FILES['archivo'], (int)$doc['local_id'], $id, $userId, $doc['archivo']);
        if ($res !== true) { $this->error($res, 422); return; }

        $this->success('Archivo adjuntado.');
    }

    /** POST /locales/api/documento/{id}/quitar-archivo */
    public function apiQuitarArchivo($id): void
    {
        $userId = $this->requireGestor();
        $id     = (int)$id;

        $doc = $this->repo->getDocumento($id);
        if (!$doc)               { $this->error('Documento no encontrado', 404); return; }
        if (empty($doc['archivo'])) { $this->error('Este documento no tiene archivo adjunto.', 422); return; }

        $this->borrarArchivoFisico($doc['archivo']);
        $this->repo->guardarArchivo($id, '', '', $userId);
        $this->repo->log([
            'documento_id' => $id, 'tipo_id' => (int)$doc['tipo_id'], 'local_id' => (int)$doc['local_id'],
            'accion' => 'QUITAR_ARCHIVO', 'postulante_id' => $userId,
            'detalle' => $doc['archivo_nombre'] ?: null,
        ]);

        $this->success('Archivo eliminado.');
    }

    /** POST /locales/api/documento/{id}/eliminar — solo ADMIN. */
    public function apiEliminar($id): void
    {
        $userId = $this->requireAdmin();

        $res = $this->repo->eliminarDocumento((int)$id, $userId);
        if (!$res['ok']) { $this->error($res['msg'], 404); return; }

        if (!empty($res['archivo'])) $this->borrarArchivoFisico($res['archivo']);

        $this->success($res['msg']);
    }

    // ─────────────────────────────────────────────────────────────
    //  API — catálogo de tipos (ADMIN)
    // ─────────────────────────────────────────────────────────────

    /** POST /admin/api/documento-tipo/guardar */
    public function apiGuardarTipo(): void
    {
        $userId = $this->requireAdmin();
        $data   = $this->getAllInput();

        if (trim($data['nombre'] ?? '') === '') { $this->error('El nombre del documento es obligatorio.', 422); return; }

        $vigencia = ($data['vigencia'] ?? 'VENCE') === 'FIJO' ? 'FIJO' : 'VENCE';
        if ($vigencia === 'VENCE' && (int)($data['dias_alerta'] ?? 30) < 0) {
            $this->error('Los días de alerta no pueden ser negativos.', 422); return;
        }

        $id = $this->repo->guardarTipo($data, $userId);
        $this->success((int)($data['id'] ?? 0) > 0 ? 'Documento actualizado.' : 'Documento creado.', ['id' => $id]);
    }

    /** POST /admin/api/documento-tipo/{id}/toggle */
    public function apiToggleTipo($id): void
    {
        $userId = $this->requireAdmin();
        if (!$this->repo->toggleTipo((int)$id, $userId)) { $this->error('Tipo no encontrado', 404); return; }
        $this->success('Estado del documento actualizado.');
    }

    /** POST /admin/api/documento-tipo/{id}/eliminar */
    public function apiEliminarTipo($id): void
    {
        $userId = $this->requireAdmin();
        $res = $this->repo->eliminarTipo((int)$id, $userId);
        if ($res !== true) { $this->error($res, 422); return; }
        $this->success('Documento eliminado del catálogo.');
    }

    // ─────────────────────────────────────────────────────────────
    //  Adjuntos
    // ─────────────────────────────────────────────────────────────

    /**
     * Guarda el escaneado en public/uploads/documentos/{local}/.
     * Devuelve true o el mensaje de error.
     */
    private function guardarAdjunto(array $file, int $localId, int $docId, int $userId, ?string $anterior = null)
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return 'Error al recibir el archivo (código ' . ($file['error'] ?? '?') . ').';
        }
        if ((int)$file['size'] > self::MAX_ARCHIVO) {
            return 'El archivo supera los 8 MB.';
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, self::EXT_PERMITIDAS, true)) {
            return 'Formato no permitido. Usa PDF, JPG, PNG o WEBP.';
        }

        $dir = __DIR__ . '/../../public/uploads/documentos/' . $localId;
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            return 'No se pudo crear la carpeta de destino.';
        }

        $nombreFisico = 'doc' . $docId . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $nombreFisico)) {
            return 'No se pudo guardar el archivo en el servidor.';
        }

        if (!empty($anterior)) $this->borrarArchivoFisico($anterior);

        $rutaRelativa = 'uploads/documentos/' . $localId . '/' . $nombreFisico;
        $nombreOrig   = mb_substr(basename($file['name']), 0, 180);

        $this->repo->guardarArchivo($docId, $rutaRelativa, $nombreOrig, $userId);
        return true;
    }

    /** Borra el archivo del disco cuidando que la ruta siga dentro de uploads/documentos. */
    private function borrarArchivoFisico(string $rutaRelativa): void
    {
        if ($rutaRelativa === '' || !str_starts_with($rutaRelativa, 'uploads/documentos/')) return;
        if (str_contains($rutaRelativa, '..')) return;

        $abs = __DIR__ . '/../../public/' . $rutaRelativa;
        if (is_file($abs)) @unlink($abs);
    }
}
