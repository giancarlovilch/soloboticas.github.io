<?php

require_once __DIR__ . '/../Core/Controller.php';
require_once __DIR__ . '/../Services/PostulanteService.php';

class PostulanteController extends Controller
{
    private PostulanteService $service;

    public function __construct()
    {
        $this->service = new PostulanteService();
    }

    public function accessView(): void
    {
        require_once __DIR__ . '/../../views/postulacion/acceso.php';
    }

    public function index(): void
    {
        $postulantes = $this->service->getAll();

        $this->success('Lista de postulantes obtenida correctamente', $postulantes);
    }

    public function show(): void
    {
        // Captura el ID desde la URL (?id=X)
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->error("ID no proporcionado", 400);
            return;
        }

        $postulante = $this->service->getById((int) $id);
        if (!$postulante) {
            $this->notFound('Postulante no encontrado');
        }
        $this->success('Postulante encontrado', $postulante);
    }

    public function store(): void
    {
        $data = $this->getAllInput();
        $result = $this->service->create($data);

        if (!$result['success']) {
            $this->handleServiceError($result);
        }

        $this->created($result['message'], $result['data']);
    }

    public function update(): void
    {
        // Captura el ID desde los parámetros de la URL[cite: 15]
        $id = $_GET['id'] ?? null;
        $data = $this->getAllInput();

        $result = $this->service->update((int) $id, $data);
        if (!$result['success']) {
            $this->handleServiceError($result);
        }
        $this->success($result['message'], $result['data']);
    }

    public function destroy(): void
    {
        $id = $_GET['id'] ?? null;
        $result = $this->service->delete((int) $id);
        if (!$result['success']) {
            $this->handleServiceError($result);
        }
        $this->success($result['message'], $result['data']);
    }

    private function handleServiceError(array $result): void
    {
        $status = $result['status'] ?? 500;
        $message = $result['message'] ?? 'Ocurrió un error';
        $errors = $result['errors'] ?? null;

        switch ($status) {
            case 404:
                $this->notFound($message);
                break;
            case 422:
                $this->validationError($message, $errors ?? []);
                break;
            case 401:
                $this->unauthorized($message);
                break;
            case 403:
                $this->forbidden($message);
                break;
            case 409:
                $this->error($message, 409, $errors ?? []);
                break;
            default:
                $this->error($message, $status, $errors);
                break;
        }
    }
    public function checkDni(): void
    {
        $data = $this->getAllInput();
        $result = $this->service->checkDni($data);

        if (!$result['success']) {
            $this->handleServiceError($result);
        }

        $this->success($result['message'], $result['data']);
    }

    public function validateAccess(): void
    {
        $data = $this->getAllInput();
        $result = $this->service->validateAccess($data);

        if (!$result['success']) {
            $this->handleServiceError($result);
        }

        $this->success($result['message'], $result['data']);
    }

    public function apply(): void
    {
        $data = $this->getAllInput();
        $result = $this->service->apply($data);

        if (!$result['success']) {
            $this->handleServiceError($result);
        }

        $this->created($result['message'], $result['data']);
    }

    // El Router pasa el segmento {dni} como primer argumento posicional.
    public function getApplicationView(string $dni = ''): void
    {
        $result = $this->service->getApplicationView($dni);
        if (!$result['success']) {
            $this->handleServiceError($result);
        }
        $this->success($result['message'], $result['data']);
    }

    /**
     * POST /postulantes/foto
     * Sube la foto del postulante (multipart/form-data).
     * Body: postulante_id + foto (file)
     */
    public function uploadFoto(): void
    {
        $postulanteId = (int)($_POST['postulante_id'] ?? 0);
        if (!$postulanteId) {
            $this->error('postulante_id requerido', 400);
        }

        $file = $_FILES['foto'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            $this->error('No se recibió ningún archivo o hubo un error al subirlo', 400);
        }

        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        $mime = mime_content_type($file['tmp_name']);
        if (!in_array($mime, $allowed, true)) {
            $this->error('Solo se aceptan imágenes JPEG, PNG o WebP', 422);
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            $this->error('La imagen no puede superar los 5 MB', 422);
        }

        $uploadDir = __DIR__ . '/../../public/uploads/fotos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Redimensionar a máximo 400×400 y guardar como JPEG
        [$srcW, $srcH] = getimagesize($file['tmp_name']);
        $ratio = min(400 / $srcW, 400 / $srcH, 1);
        $newW  = (int)round($srcW * $ratio);
        $newH  = (int)round($srcH * $ratio);

        $src = match ($mime) {
            'image/png'  => imagecreatefrompng($file['tmp_name']),
            'image/webp' => imagecreatefromwebp($file['tmp_name']),
            default      => imagecreatefromjpeg($file['tmp_name']),
        };

        $dst = imagecreatetruecolor($newW, $newH);

        // Fondo blanco para PNG con transparencia
        imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $srcW, $srcH);

        $filename = $postulanteId . '_' . time() . '.jpg';
        $destPath = $uploadDir . $filename;

        imagejpeg($dst, $destPath, 80);
        imagedestroy($src);
        imagedestroy($dst);

        $fotoUrl = '/uploads/fotos/' . $filename;

        require_once __DIR__ . '/../Repositories/PostulanteRepository.php';
        $repo = new PostulanteRepository();
        $repo->updateFoto($postulanteId, $fotoUrl);

        $this->success('Foto actualizada', ['foto_url' => $fotoUrl]);
    }

    public function formView(): void
    {
        require_once __DIR__ . '/../../views/postulacion/formulario.php';
    }
}
