<?php
require_once __DIR__ . '/../src/Core/Database.php';
$db = Database::getConnection();

$sid = 446;

// detalle_cuadre
$dc = $db->query("SELECT * FROM detalle_cuadre WHERE sesion_id = $sid")->fetch();
echo "=== detalle_cuadre sesion $sid ===\n";
echo "total_efectivo_contado  : {$dc['total_efectivo_contado']}\n";
echo "total_esperado_sistema  : {$dc['total_esperado_sistema']}\n";
echo "diferencia              : {$dc['diferencia']}\n";
echo "total_contado_general   : {$dc['total_contado_general']}\n\n";

// sesion_caja
$sc = $db->query("SELECT saldo_final_contado, saldo_final_sistema, diferencia_final FROM sesion_caja WHERE id_sesion = $sid")->fetch();
echo "=== sesion_caja $sid ===\n";
echo "saldo_final_contado  : {$sc['saldo_final_contado']}\n";
echo "saldo_final_sistema  : {$sc['saldo_final_sistema']}\n";
echo "diferencia_final     : {$sc['diferencia_final']}\n\n";

// rectifs (movimientos_incidencia tipo PAGO que ajustan diferencia)
$rectifs = $db->query("SELECT * FROM movimiento_incidencia_contable WHERE sesion_id = $sid")->fetchAll();
$sumR = array_sum(array_column($rectifs, 'monto'));
echo "=== rectifs (" . count($rectifs) . ") sumR=$sumR ===\n";
foreach ($rectifs as $r) echo "  #{$r['id_movimiento']} {$r['tipo']} monto={$r['monto']}\n";
echo "\n";

// ajustes esperados
$ajustes = $db->query("SELECT * FROM ajuste_esperado WHERE sesion_id = $sid")->fetchAll();
$sumA = 0;
foreach ($ajustes as $aj)
    $sumA += $aj['accion'] === 'AGREGAR' ? (float)$aj['monto'] : -(float)$aj['monto'];
echo "=== ajustes_esperado (" . count($ajustes) . ") sumA=$sumA ===\n";
foreach ($ajustes as $a) echo "  #{$a['id_ajuste']} {$a['accion']} {$a['tipo']} monto={$a['monto']}\n";
echo "\n";

// correccion_venta
$corrRows = $db->query("SELECT * FROM correccion_venta WHERE sesion_id = $sid")->fetchAll();
$corrDelta = 0;
foreach ($corrRows as $c) $corrDelta += (float)$c['monto_nuevo'] - (float)$c['monto_anterior'];
echo "=== correcciones_venta (" . count($corrRows) . ") corrDelta=$corrDelta ===\n\n";

// Formula final
$dif = abs(round(
    ((float)$dc['total_efectivo_contado'] - (float)$dc['total_esperado_sistema'])
    + $sumR + $sumA - $corrDelta, 2
));
echo "difEfectiva calculada = $dif\n";
echo "Umbral = 10.00 → " . ($dif <= 10 ? "PUEDE CERRAR" : "BLOQUEADA") . "\n";

// incidencia 939
$inc = $db->query("SELECT id_incidencia, tipo, monto_pendiente, estado FROM incidencia_contable WHERE id_incidencia = 939")->fetch();
echo "\n=== incidencia 939 ===\n";
echo "tipo={$inc['tipo']} monto_pendiente={$inc['monto_pendiente']} estado={$inc['estado']}\n";
