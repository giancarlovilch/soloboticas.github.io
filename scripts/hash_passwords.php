<?php
/**
 * Script de migración: hashea las contraseñas de todos los usuarios.
 *
 * CUÁNDO EJECUTARLO:
 *   Una sola vez, después de insertar los datos de DATA.md (INTEGRANTES + ENROLAMIENTO).
 *   Las contraseñas actuales son el DNI en texto plano — este script las convierte a bcrypt.
 *
 * CÓMO EJECUTARLO:
 *   Desde la raíz del proyecto:  php scripts/hash_passwords.php
 *   O desde el navegador (solo si está en localhost):  /scripts/hash_passwords.php
 *
 * EFECTO:
 *   • Usuarios sin contraseña hasheada → se hashea su DNI actual.
 *   • Usuarios que YA tienen hash bcrypt → se omiten (idempotente).
 *   • El admin (GIANCARLOVC) también queda hasheado.
 */

define('SCRIPT_ROOT', dirname(__DIR__));
require_once SCRIPT_ROOT . '/config/database.php';
require_once SCRIPT_ROOT . '/src/Core/Database.php';

// Seguridad mínima: solo desde CLI o localhost
if (php_sapi_name() !== 'cli') {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    if (!in_array($ip, ['127.0.0.1', '::1'], true)) {
        http_response_code(403);
        exit("Acceso denegado. Este script solo puede ejecutarse desde localhost o CLI.\n");
    }
}

$db = Database::getConnection();

// Obtiene usuarios con su DNI (contraseña provisional = DNI)
$sql = "SELECT u.postulante_id, u.username, u.password, p.num_documento
        FROM usuario u
        INNER JOIN postulante p ON u.postulante_id = p.id_postulante";

$stmt = $db->query($sql);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$update = $db->prepare("UPDATE usuario SET password = :pw WHERE postulante_id = :id");

$hasheados = 0;
$omitidos  = 0;

foreach ($rows as $row) {
    // Si ya es un hash bcrypt ($2y$...) lo omitimos — script idempotente
    if (str_starts_with($row['password'], '$2y$')) {
        $omitidos++;
        echo "  [OMITIDO] {$row['username']} — ya tiene hash bcrypt\n";
        continue;
    }

    $hash = password_hash($row['password'], PASSWORD_BCRYPT);
    $update->execute(['pw' => $hash, 'id' => $row['postulante_id']]);
    $hasheados++;
    echo "  [OK] {$row['username']} (DNI: {$row['num_documento']}) — contraseña hasheada\n";
}

echo "\n--- Resumen ---\n";
echo "  Hasheados : $hasheados\n";
echo "  Omitidos  : $omitidos\n";
echo "  TOTAL     : " . ($hasheados + $omitidos) . "\n\n";
echo "Migración completada. Ya puedes usar el login con contraseñas bcrypt.\n";
