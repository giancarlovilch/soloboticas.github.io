<?php
/**
 * Verifica que dc.diferencia (lo que muestra /caja) coincida con la fórmula live
 * (lo que calculan /caja/reporte/{id} e /incidencias/{id}) en todas las sesiones cerradas.
 *
 * Uso: php scripts/check_consistencia.php
 *
 * Si hay diferencias, corregirlas con:
 *   php scripts/check_consistencia.php --fix
 */

$fix = in_array('--fix', $argv ?? []);

require_once __DIR__ . '/../src/Core/Database.php';
require_once __DIR__ . '/../src/Repositories/CajaRepository.php';

$db   = Database::getConnection();
$repo = new CajaRepository();

$sesiones = $db->query("
    SELECT sc.id_sesion, sc.estado, sc.saldo_inicial,
           p.nombres cajera,
           sc.fecha_operacion,
           dc.total_efectivo_contado,
           dc.total_ventas_sistema,
           dc.total_gastos_sistema,
           dc.diferencia AS dif_guardada
    FROM sesion_caja sc
    INNER JOIN detalle_cuadre dc ON dc.sesion_id = sc.id_sesion
    INNER JOIN postulante p ON sc.postulante_apertura_id = p.id_postulante
    WHERE sc.estado IN ('CERRADA','OBSERVADA','RECHAZADA')
    ORDER BY sc.id_sesion ASC
")->fetchAll();

$ok   = 0;
$fail = [];

foreach ($sesiones as $s) {
    $sid = (int)$s['id_sesion'];

    // LO QUE ES
    $rectStmt = $db->prepare("SELECT COALESCE(SUM(monto),0) FROM rectificacion_cuadre WHERE sesion_id=:sid");
    $rectStmt->execute(['sid' => $sid]);
    $loQueEs = round((float)$s['total_efectivo_contado'] + (float)$rectStmt->fetchColumn(), 2);

    // LO QUE SE DICE
    $cvStmt = $db->prepare("SELECT COALESCE(SUM(monto_nuevo - monto_anterior),0) FROM correccion_venta WHERE sesion_id=:sid");
    $cvStmt->execute(['sid' => $sid]);

    $digStmt = $db->prepare("SELECT COALESCE(SUM(monto),0) FROM movimiento_sesion WHERE sesion_id=:sid AND tipo_movimiento_id=1 AND estado IN ('PENDIENTE','APROBADO')");
    $digStmt->execute(['sid' => $sid]);

    $ajStmt = $db->prepare("SELECT COALESCE(SUM(CASE WHEN accion='AGREGAR' THEN -monto ELSE monto END),0) FROM ajuste_esperado WHERE sesion_id=:sid");
    $ajStmt->execute(['sid' => $sid]);

    $trStmt = $db->prepare("SELECT COALESCE(SUM(CASE WHEN sesion_aplicada_origen_id=:s1 THEN -monto WHEN sesion_aplicada_destino_id=:s2 THEN monto ELSE 0 END),0) FROM transferencia_saldo WHERE sesion_aplicada_origen_id=:s3 OR sesion_aplicada_destino_id=:s4");
    $trStmt->execute(['s1' => $sid, 's2' => $sid, 's3' => $sid, 's4' => $sid]);

    $rtStmt = $db->prepare("SELECT COALESCE(SUM(monto),0) FROM retiro_kgyr WHERE sesion_aplicada_id=:sid");
    $rtStmt->execute(['sid' => $sid]);

    $igStmt = $db->prepare("SELECT COALESCE(SUM(monto),0) FROM ingreso_kgyr WHERE sesion_aplicada_id=:sid");
    $igStmt->execute(['sid' => $sid]);

    $loQueSeDice = round(
        (float)$s['saldo_inicial']
        + (float)$s['total_ventas_sistema']
        + (float)$cvStmt->fetchColumn()
        - (float)$s['total_gastos_sistema']
        - (float)$digStmt->fetchColumn()
        + (float)$ajStmt->fetchColumn()
        + (float)$trStmt->fetchColumn()
        - (float)$rtStmt->fetchColumn()
        + (float)$igStmt->fetchColumn(),
        2
    );

    $difLive     = round($loQueEs - $loQueSeDice, 2);
    $difGuardada = round((float)$s['dif_guardada'], 2);

    if (abs($difLive - $difGuardada) >= 0.01) {
        $fail[] = [
            'id'           => $sid,
            'cajera'       => substr($s['cajera'], 0, 18),
            'fecha'        => $s['fecha_operacion'],
            'dif_guardada' => $difGuardada,
            'dif_live'     => $difLive,
            'delta'        => round($difLive - $difGuardada, 2),
        ];
        if ($fix) {
            $repo->recalcularDiferenciaCompleta($sid);
        }
    } else {
        $ok++;
    }
}

echo "=== CHECK DE CONSISTENCIA: dc.diferencia vs fórmula live ===\n";
echo "Sesiones revisadas : " . count($sesiones) . "\n";
echo "OK (coinciden)     : $ok\n";
echo "Con diferencia     : " . count($fail) . "\n";
if ($fix && !empty($fail)) echo "Corregidas con --fix: " . count($fail) . "\n";
echo "\n";

if (!empty($fail)) {
    echo str_pad('#ID', 5) . ' ' . str_pad('Cajera', 19) . ' ' . str_pad('Fecha', 11) . ' ' .
         str_pad('Guardado', 10) . ' ' . str_pad('Live', 10) . "  Delta\n";
    echo str_repeat('-', 72) . "\n";
    foreach ($fail as $f) {
        echo sprintf(
            "#%-4d %-19s %s  %+10.2f  %+10.2f  %+.2f\n",
            $f['id'], $f['cajera'], $f['fecha'],
            $f['dif_guardada'], $f['dif_live'], $f['delta']
        );
    }
    if (!$fix) {
        echo "\nPara corregir automáticamente: php scripts/check_consistencia.php --fix\n";
    }
} else {
    echo "✓ Todos los valores son consistentes.\n";
}
