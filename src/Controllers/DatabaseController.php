<?php

require_once __DIR__ . '/../Core/Controller.php';
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';

class DatabaseController extends Controller
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private function middlewareAdmin(): void
    {
        if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] !== 'ADMIN') {
            header('Location: ' . APP_BASE_PATH . '/login');
            exit;
        }
    }

    /**
     * Vista principal de gestión de base de datos
     */
    public function index(): void
    {
        $this->middlewareAdmin();
        
        $migrationsDir = __DIR__ . '/../../db/migrations/';
        $files = glob($migrationsDir . '*.sql');
        sort($files);
        
        $migrations = [];
        foreach ($files as $file) {
            $migrations[] = basename($file);
        }
        
        require_once __DIR__ . '/../../views/admin/database.php';
    }

    /**
     * Sincroniza la DB local usando el archivo sb.sql descargado manualmente
     */
    public function syncFromDownload(): void
    {
        $this->middlewareAdmin();
        $file = __DIR__ . '/../../db/download/sb.sql';

        if (!file_exists($file)) {
            $this->error("No se encontró el archivo db/download/sb.sql. Por favor, colócalo allí manualmente.");
        }

        // 1. Limpiar la base de datos local y re-crearla con el charset correcto
        $localConfig = include __DIR__ . '/../../config/database.php';
        $dbName = $localConfig['dbname'];
        $user = 'root';
        $pass = '';

        try {
            $pdo = new PDO("mysql:host=localhost", $user, $pass);
            $pdo->exec("DROP DATABASE IF EXISTS `$dbName` ");
            $pdo->exec("CREATE DATABASE `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            // 2. Importar usando el comando mysql para asegurar UTF8
            $command = "mysql -h localhost -u $user --default-character-set=utf8mb4 $dbName < " . escapeshellarg($file) . " 2>&1";
            $output = [];
            $returnVar = 0;
            exec($command, $output, $returnVar);

            if ($returnVar === 0) {
                header('Location: ' . APP_BASE_PATH . '/admin/database?applied=1&sync=1');
            } else {
                $errorMsg = implode("\n", $output);
                header('Location: ' . APP_BASE_PATH . '/admin/database?error=' . urlencode("Error al importar sb.sql: " . $errorMsg));
            }
        } catch (Exception $e) {
            $this->error("Error crítico: " . $e->getMessage());
        }
    }

    /**
     * Sube un archivo SQL y REINICIA la base de datos local con ese contenido
     */
    public function uploadMigration(): void
    {
        $this->middlewareAdmin();

        if (!isset($_FILES['sql_file']) || $_FILES['sql_file']['error'] !== UPLOAD_ERR_OK) {
            $this->error("Error al subir el archivo.");
        }

        $file = $_FILES['sql_file'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);

        if (strtolower($ext) !== 'sql') {
            $this->error("Solo se permiten archivos .sql");
        }

        // Guardamos en migrations para tener historial
        $cleanName = preg_replace('/[^a-zA-Z0-9_]/', '', pathinfo($file['name'], PATHINFO_FILENAME));
        $newName = date('Ymd_His') . '_' . $cleanName . '.sql';
        $dest = __DIR__ . '/../../db/migrations/' . $newName;

        if (move_uploaded_file($file['tmp_name'], $dest)) {
            // LÓGICA DE REINICIO TOTAL (Igual que Cargar sb.sql)
            $localConfig = include __DIR__ . '/../../config/database.php';
            $dbName = $localConfig['dbname'];
            $user = 'root';
            $pass = '';

            try {
                $pdo = new PDO("mysql:host=localhost", $user, $pass);
                $pdo->exec("DROP DATABASE IF EXISTS `$dbName` ");
                $pdo->exec("CREATE DATABASE `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                
                $result = $this->applySqlFile($dest);
                
                if ($result === true) {
                    header('Location: ' . APP_BASE_PATH . '/admin/database?uploaded=1&reset=1');
                } else {
                    header('Location: ' . APP_BASE_PATH . '/admin/database?uploaded=1&error=' . urlencode("Archivo guardado pero error al importar: " . $result));
                }
            } catch (Exception $e) {
                $this->error("Error al reiniciar base de datos: " . $e->getMessage());
            }
        } else {
            $this->error("No se pudo guardar el archivo.");
        }
    }

    /**
     * Aplica un archivo SQL específico a la base de datos local (full reset)
     */
    public function applyToLocal(): void
    {
        $this->middlewareAdmin();
        $filename = $_GET['file'] ?? '';

        if (empty($filename)) {
            $this->error("No se proporcionó el nombre del archivo.");
        }

        $path = __DIR__ . '/../../db/migrations/' . basename($filename);

        if (!file_exists($path)) {
            $this->error("El archivo no existe.");
        }

        $localConfig = include __DIR__ . '/../../config/database.php';
        $dbName = $localConfig['dbname'];
        $user = 'root';
        $pass = '';

        try {
            $pdo = new PDO("mysql:host=localhost", $user, $pass);
            $pdo->exec("DROP DATABASE IF EXISTS `$dbName`");
            $pdo->exec("CREATE DATABASE `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        } catch (Exception $e) {
            header('Location: ' . APP_BASE_PATH . '/admin/database?error=' . urlencode("Error al reiniciar DB: " . $e->getMessage()));
            exit;
        }

        $result = $this->applySqlFile($path);

        if ($result === true) {
            header('Location: ' . APP_BASE_PATH . '/admin/database?applied=1');
        } else {
            header('Location: ' . APP_BASE_PATH . '/admin/database?error=' . urlencode($result));
        }
    }

    /**
     * Lógica interna para ejecutar un archivo SQL en la DB local
     */
    private function applySqlFile(string $path): bool|string
    {
        $localConfig = include __DIR__ . '/../../config/database.php';
        $user = 'root';
        $pass = ''; // Local root no pass
        $dbName = $localConfig['dbname'];

        // Redirigimos 2>&1 para capturar errores de stderr en el output
        $command = "mysql -h localhost -u $user --default-character-set=utf8mb4 $dbName < " . escapeshellarg($path) . " 2>&1";
        
        $output = [];
        $returnVar = 0;
        exec($command, $output, $returnVar);

        if ($returnVar === 0) {
            return true;
        } else {
            // Si hay error, devolvemos el output que ahora incluye el mensaje de error de MySQL
            return !empty($output) ? implode("\n", $output) : "Error desconocido (Exit Code: $returnVar). Verifique que el cliente 'mysql' esté en el PATH.";
        }
    }

    /**
     * Genera y descarga un volcado completo de la base de datos local
     */
    public function downloadFull(): void
    {
        $this->middlewareAdmin();

        $localConfig = include __DIR__ . '/../../config/database.php';
        $dbName = $localConfig['dbname'];
        $user = 'root';
        $pass = ''; 
        
        $fileName = 'sb_actualizado_' . date('Ymd_His') . '.sql';
        $tempFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $fileName;

        $command = "mysqldump -h localhost -u $user --opt --skip-lock-tables --no-tablespaces --default-character-set=utf8mb4 $dbName > \"$tempFile\"";
        
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            die("Error al generar el volcado de la base de datos.");
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($fileName) . '"');
        header('Content-Length: ' . filesize($tempFile));
        readfile($tempFile);
        unlink($tempFile);
        exit;
    }
}
