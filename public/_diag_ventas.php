<?php
/**
 * DIAGNÓSTICO TEMPORAL — borrar este archivo después de usarlo.
 * Reproduce la consulta de búsqueda de productos con errores visibles,
 * para encontrar por qué falla en este servidor puntual.
 */
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
header('Content-Type: text/plain; charset=utf-8');

echo "PHP version: " . PHP_VERSION . "\n";
echo "Drivers PDO disponibles: " . implode(', ', PDO::getAvailableDrivers()) . "\n\n";

require_once __DIR__ . '/../src/Core/Database.php';

try {
    $db = Database::getConnection();
    echo "Conexión a la base de datos: OK\n";
    $ver = $db->query('SELECT VERSION() AS v')->fetch();
    echo "Versión de MySQL/MariaDB: " . $ver['v'] . "\n\n";
} catch (Throwable $e) {
    echo "ERROR AL CONECTAR A LA BASE DE DATOS:\n";
    echo $e->getMessage() . "\n";
    exit;
}

try {
    $existe = $db->query("SHOW TABLES LIKE 'producto_referencia'")->fetch();
    echo "Tabla producto_referencia existe: " . ($existe ? 'SI' : 'NO') . "\n";
    if ($existe) {
        $cnt = $db->query('SELECT COUNT(*) AS c FROM producto_referencia')->fetch();
        echo "Filas en producto_referencia: " . $cnt['c'] . "\n\n";
    }
} catch (Throwable $e) {
    echo "ERROR AL REVISAR LA TABLA:\n" . $e->getMessage() . "\n\n";
}

$idLocal = (int) ($_GET['id_local'] ?? 2);
$q       = (string) ($_GET['q'] ?? 'ketot');

echo "Probando búsqueda con id_local=$idLocal, q=\"$q\"...\n\n";

try {
    $stmt = $db->prepare("
        SELECT cod_producto, nombre_producto,
               MAX(CASE WHEN id_local = {$idLocal} THEN precio_venta END) AS precio_venta,
               MAX(CASE WHEN id_local = {$idLocal}
                        THEN (CASE WHEN precio_costo > 0 THEN precio_costo ELSE precio_venta END)
                   END) AS precio_piso,
               MAX(CASE WHEN id_local = 2 THEN stock END) AS stock_2,
               MAX(CASE WHEN id_local = 3 THEN stock END) AS stock_3,
               MAX(CASE WHEN id_local = 4 THEN stock END) AS stock_4,
               MAX(CASE WHEN id_local = {$idLocal} THEN stock END) AS stock_actual
        FROM producto_referencia
        WHERE cod_producto LIKE :q1 OR nombre_producto LIKE :q2
        GROUP BY cod_producto, nombre_producto
        ORDER BY (stock_actual > 0) DESC, nombre_producto ASC
        LIMIT 20
    ");
    $like = '%' . $q . '%';
    $stmt->execute([':q1' => $like, ':q2' => $like]);
    $rows = $stmt->fetchAll();

    echo "CONSULTA OK — filas encontradas: " . count($rows) . "\n\n";
    print_r($rows);
} catch (Throwable $e) {
    echo "======= ERROR EN LA CONSULTA =======\n";
    echo "Clase: " . get_class($e) . "\n";
    echo "Mensaje: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
