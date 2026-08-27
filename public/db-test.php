<?php
// ── HERRAMIENTA TEMPORAL DE DIAGNÓSTICO ──────────────────────────
// Borra este archivo del servidor en cuanto termines de usarlo.
// Uso: https://www.soloboticas.com/public/db-test.php?key=diagnostico2026

header('Content-Type: text/plain; charset=utf-8');

if (($_GET['key'] ?? '') !== 'diagnostico2026') {
    http_response_code(403);
    exit("Acceso denegado. Agrega ?key=diagnostico2026 a la URL.\n");
}

echo "=== Test de conexión a la base de datos — Solo Boticas ===\n\n";
echo "Fecha/hora del servidor: " . date('Y-m-d H:i:s') . "\n";
echo "Versión de PHP: " . PHP_VERSION . "\n\n";

$configPath = __DIR__ . '/../config/database.php';
if (!file_exists($configPath)) {
    die("[FALLO] No se encontró config/database.php en: $configPath\n");
}
$config = require $configPath;
echo "Config leída de config/database.php:\n";
echo "  host    = {$config['host']}\n";
echo "  port    = {$config['port']}\n";
echo "  dbname  = {$config['dbname']}\n";
echo "  user    = {$config['username']}\n";
echo "  charset = {$config['charset']}\n\n";

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
    $config['host'], $config['port'], $config['dbname'], $config['charset']
);

try {
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    echo "[OK] Conexión establecida correctamente.\n\n";
} catch (PDOException $e) {
    die("[FALLO CRÍTICO] No se pudo conectar a la base de datos:\n" . $e->getMessage() . "\n");
}

// ── Motor y versión real del servidor ─────────────────────────
$version = $pdo->query('SELECT VERSION()')->fetchColumn();
$esMaria = stripos($version, 'mariadb') !== false;
echo "Motor detectado: " . ($esMaria ? "MariaDB" : "MySQL") . "\n";
echo "Versión reportada: $version\n\n";

// ── ¿Existe la collation utf8mb4_0900_ai_ci en este servidor? ──
try {
    $soportada = (bool)$pdo->query(
        "SELECT COUNT(*) FROM information_schema.COLLATIONS WHERE COLLATION_NAME = 'utf8mb4_0900_ai_ci'"
    )->fetchColumn();
    echo ($soportada ? "[OK] " : "[ALERTA] ")
        . "utf8mb4_0900_ai_ci " . ($soportada ? "SÍ está soportada por este servidor.\n" : "NO está soportada por este servidor.\n");
    if (!$soportada) {
        echo "         Esa collation es exclusiva de MySQL 8.0+. Si el archivo que importaste la usaba,\n";
        echo "         las tablas que la necesitaban probablemente NO se crearon.\n";
    }
} catch (Exception $e) {
    echo "[ALERTA] No se pudo verificar collations disponibles: " . $e->getMessage() . "\n";
}
echo "\n";

// ── Inventario de tablas ────────────────────────────────────────
$tablas = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
echo "Tablas encontradas en '{$config['dbname']}': " . count($tablas) . "\n";

if (count($tablas) === 0) {
    echo "\n[ALERTA CRÍTICA] La base de datos está VACÍA — no hay ni una sola tabla.\n";
    echo "Esto confirma que el import no llegó a crear nada (o falló desde el inicio).\n";
} else {
    $criticas = ['usuario', 'postulante', 'local', 'horario_slot', 'turno'];
    echo "\nTablas críticas para el login:\n";
    foreach ($criticas as $t) {
        echo '  ' . (in_array($t, $tablas) ? '[OK] ' : '[FALTA] ') . $t . "\n";
    }

    if (in_array('usuario', $tablas)) {
        try {
            $n = $pdo->query('SELECT COUNT(*) FROM usuario')->fetchColumn();
            echo "\nRegistros en 'usuario': $n\n";
            $col = $pdo->query(
                "SELECT TABLE_COLLATION FROM information_schema.TABLES
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'usuario'"
            )->fetchColumn();
            echo "Collation real de la tabla 'usuario': $col\n";
        } catch (Exception $e) {
            echo "[FALLO] Error al leer la tabla 'usuario': " . $e->getMessage() . "\n";
        }
    }

    // Collations mezcladas = causa clásica de "Illegal mix of collations"
    try {
        $mezcla = $pdo->query(
            "SELECT TABLE_COLLATION, COUNT(*) AS n
             FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE()
             GROUP BY TABLE_COLLATION"
        )->fetchAll();
        echo "\nCollations en uso en toda la base:\n";
        foreach ($mezcla as $m) {
            echo "  {$m['TABLE_COLLATION']}: {$m['n']} tabla(s)\n";
        }
        if (count($mezcla) > 1) {
            echo "\n[ALERTA] Hay más de una collation mezclada en la base — esto puede causar\n";
            echo "         errores 'Illegal mix of collations' en consultas que junten esas tablas.\n";
        }
    } catch (Exception $e) { /* silencioso */ }
}

echo "\n=== Fin del test ===\n";
echo "IMPORTANTE: borra este archivo (public/db-test.php) del servidor cuando termines.\n";
