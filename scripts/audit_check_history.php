<?php
require_once __DIR__ . '/../src/Core/Database.php';
$db = Database::getConnection();
$sessions = [264,266,268,286,292,294,298,309,311,317,319,333,335,341,342,353,361,366,369,377,382,387,389,390,393,394,395,402,412,417,423,425,436,439,445,446,455,467];
$stmt = $db->prepare("SELECT * FROM auditoria_cuadre WHERE sesion_id = :sid ORDER BY fecha ASC");
foreach ($sessions as $sid) {
    $stmt->execute(['sid' => $sid]);
    $rows = $stmt->fetchAll();
    if (count($rows) === 0) {
        echo "sesion $sid: SIN auditoria\n";
        continue;
    }
    foreach ($rows as $r) {
        echo "sesion $sid: {$r['fecha']} {$r['accion']} {$r['campo_modificado']}: {$r['valor_anterior']} -> {$r['valor_nuevo']}\n";
    }
}
