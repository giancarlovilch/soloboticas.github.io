<?php
require_once __DIR__ . '/../src/Core/Database.php';
$db = Database::getConnection();
$db->exec("ALTER TABLE solicitud_cambio MODIFY COLUMN tipo ENUM('COBERTURA','CAMBIO','INTERCAMBIO') NOT NULL");
echo "OK — tipo ahora acepta INTERCAMBIO\n";
