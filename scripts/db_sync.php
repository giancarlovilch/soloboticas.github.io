<?php
/**
 * SoloBoticas - Database Sync & Migration Tool
 * 
 * Uso:
 *   php scripts/db_sync.php pull    - Trae datos de PRODUCCIÓN a LOCAL
 *   php scripts/db_sync.php push    - Aplica migraciones de LOCAL a PRODUCCIÓN
 */

// Configuración de Producción (Remoto)
$prod = [
    'host' => '26.8.10.86',
    'port' => '3306',
    'user' => 'root',
    'pass' => '12345',
    'db'   => 'sb'
];

// Configuración Local (Cargada desde el archivo de config)
$localConfig = include __DIR__ . '/../config/database.php';
$local = [
    'host' => $localConfig['host'],
    'port' => $localConfig['port'],
    'user' => 'root', // Usamos root para local por facilidad según pruebas previas
    'pass' => '',
    'db'   => $localConfig['dbname']
];

$action = $argv[1] ?? null;

if (!$action || !in_array($action, ['pull', 'push'])) {
    die("Uso: php scripts/db_sync.php [pull|push]\n");
}

if ($action === 'pull') {
    pullData($prod, $local);
} else {
    pushMigrations($local, $prod);
}

/**
 * Trae los datos de producción y los mete en la base de datos local.
 */
function pullData($from, $to) {
    echo "--- INICIANDO PULL (Producción -> Local) ---\n";
    $dumpFile = __DIR__ . '/../db/prod_backup.sql';
    
    // 1. Exportar de producción
    echo "1. Exportando datos de producción...\n";
    $cmdDump = "mysqldump -h {$from['host']} -P {$from['port']} -u {$from['user']} -p{$from['pass']} --opt --skip-lock-tables {$from['db']} > \"$dumpFile\"";
    exec($cmdDump, $output, $returnVar);
    
    if ($returnVar !== 0) {
        die("Error al exportar de producción.\n");
    }

    // 2. Importar en local
    echo "2. Importando en base de datos local...\n";
    // Eliminamos -p{$to['pass']} porque en local root no tiene password
    $cmdImport = "mysql -h {$to['host']} -P {$to['port']} -u {$to['user']} {$to['db']} < \"$dumpFile\"";
    exec($cmdImport, $output, $returnVar);

    if ($returnVar !== 0) {
        die("Error al importar en local.\n");
    }

    echo "¡PULL completado con éxito! Tu base de datos local ahora tiene los datos reales.\n";
    @unlink($dumpFile);
}

/**
 * Busca archivos SQL en db/migrations/ y los aplica en producción si no han sido aplicados.
 */
function pushMigrations($localCfg, $remoteCfg) {
    echo "--- INICIANDO PUSH DE MIGRACIONES (Local -> Producción) ---\n";
    
    $pdo = new PDO("mysql:host={$remoteCfg['host']};port={$remoteCfg['port']};dbname={$remoteCfg['db']}", $remoteCfg['user'], $remoteCfg['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Crear tabla de log si no existe
    $pdo->exec("CREATE TABLE IF NOT EXISTS _migrations_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        filename VARCHAR(255) UNIQUE,
        applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $migrationFiles = glob(__DIR__ . '/../db/migrations/*.sql');
    sort($migrationFiles); // Asegurar orden cronológico

    if (empty($migrationFiles)) {
        echo "No hay archivos de migración en db/migrations/\n";
        return;
    }

    $stmt = $pdo->prepare("SELECT id FROM _migrations_log WHERE filename = ?");
    
    foreach ($migrationFiles as $file) {
        $filename = basename($file);
        $stmt->execute([$filename]);
        
        if ($stmt->fetch()) {
            echo "[-] Saltando: $filename (ya aplicado)\n";
            continue;
        }

        echo "[+] Aplicando: $filename...\n";
        $sql = file_get_contents($file);
        
        try {
            // Ejecutamos el SQL. Nota: Si hay múltiples sentencias, PDO::exec puede fallar dependiendo de la config.
            // Usaremos el cliente mysql por comando para mayor fiabilidad con archivos grandes o múltiples sentencias.
            $cmdExec = "mysql -h {$remoteCfg['host']} -P {$remoteCfg['port']} -u {$remoteCfg['user']} -p{$remoteCfg['pass']} {$remoteCfg['db']} < \"$file\"";
            exec($cmdExec, $output, $returnVar);

            if ($returnVar === 0) {
                $pdo->prepare("INSERT INTO _migrations_log (filename) VALUES (?)")->execute([$filename]);
                echo "    OK!\n";
            } else {
                echo "    ERROR al aplicar el archivo.\n";
                break;
            }
        } catch (Exception $e) {
            echo "    ERROR: " . $e->getMessage() . "\n";
            break;
        }
    }

    echo "--- PROCESO DE MIGRACIONES FINALIZADO ---\n";
}
