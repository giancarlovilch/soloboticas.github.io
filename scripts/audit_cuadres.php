<?php
/**
 * Auditoría de consistencia de cuadres (solo lectura, no corrige nada).
 *
 * Busca, para todas las sesiones de caja con cuadre guardado:
 *  1. Desincronización entre detalle_cuadre y sesion_caja (el bug que se
 *     encontró en la sesión 446 — corregido ahora en updateConteo(), pero
 *     puede haber afectado sesiones anteriores).
 *  2. Inconsistencia interna: diferencia != total_efectivo_contado - total_esperado_sistema.
 *  3. Cadena de saldos rota: saldo_inicial de una sesión no coincide con el
 *     saldo_proximo_dia de la sesión anterior de la misma caja (dinero que
 *     "aparece o desaparece" entre arqueos).
 *  4. Incidencias automáticas (ABIERTO/PARCIAL) cuyo monto original ya no
 *     coincide con la diferencia_final actual de su sesión de origen — señal
 *     de que el cuadre se corrigió después de crear la incidencia.
 */

require_once __DIR__ . '/../src/Core/Database.php';
$db = Database::getConnection();

echo "=== 1) Desincronización detalle_cuadre <-> sesion_caja ===\n";
$rows = $db->query("
    SELECT sc.id_sesion, sc.caja_id, sc.fecha_operacion, sc.estado,
           dc.total_efectivo_contado, dc.total_contado_general,
           dc.total_esperado_sistema, dc.diferencia,
           sc.saldo_final_contado, sc.saldo_final_sistema, sc.diferencia_final
    FROM sesion_caja sc
    INNER JOIN detalle_cuadre dc ON dc.sesion_id = sc.id_sesion
    WHERE ABS(dc.total_efectivo_contado - dc.total_contado_general) >= 0.01
       OR ABS(dc.total_efectivo_contado - sc.saldo_final_contado)   >= 0.01
       OR ABS(dc.total_esperado_sistema  - sc.saldo_final_sistema)  >= 0.01
       OR ABS(dc.diferencia              - sc.diferencia_final)     >= 0.01
    ORDER BY sc.id_sesion
")->fetchAll();
echo count($rows) . " sesión(es) con datos desincronizados\n";
foreach ($rows as $r) {
    printf(
        "  sesion %d (caja %d, %s, %s): efectivo_contado=%.2f total_contado_general=%.2f | " .
        "esperado_sistema(dc)=%.2f saldo_final_sistema(sc)=%.2f | " .
        "diferencia(dc)=%.2f diferencia_final(sc)=%.2f | saldo_final_contado(sc)=%.2f\n",
        $r['id_sesion'], $r['caja_id'], $r['fecha_operacion'], $r['estado'],
        $r['total_efectivo_contado'], $r['total_contado_general'],
        $r['total_esperado_sistema'], $r['saldo_final_sistema'],
        $r['diferencia'], $r['diferencia_final'],
        $r['saldo_final_contado']
    );
}

echo "\n=== 2) diferencia != total_efectivo_contado - total_esperado_sistema ===\n";
$rows = $db->query("
    SELECT dc.sesion_id, sc.caja_id, sc.fecha_operacion,
           dc.total_efectivo_contado, dc.total_esperado_sistema, dc.diferencia
    FROM detalle_cuadre dc
    INNER JOIN sesion_caja sc ON sc.id_sesion = dc.sesion_id
    WHERE ABS(dc.diferencia - ROUND(dc.total_efectivo_contado - dc.total_esperado_sistema, 2)) >= 0.01
    ORDER BY dc.sesion_id
")->fetchAll();
echo count($rows) . " sesión(es) con diferencia inconsistente con su propia fórmula\n";
foreach ($rows as $r) {
    printf(
        "  sesion %d (caja %d, %s): efectivo_contado=%.2f esperado_sistema=%.2f diferencia=%.2f (esperado %.2f)\n",
        $r['sesion_id'], $r['caja_id'], $r['fecha_operacion'],
        $r['total_efectivo_contado'], $r['total_esperado_sistema'], $r['diferencia'],
        round($r['total_efectivo_contado'] - $r['total_esperado_sistema'], 2)
    );
}

echo "\n=== 3) Cadena de saldos (saldo_inicial vs saldo_proximo_dia anterior) ===\n";
$cajas = $db->query("SELECT id_caja FROM caja ORDER BY id_caja")->fetchAll(PDO::FETCH_COLUMN);
$totalRotos = 0;
foreach ($cajas as $cajaId) {
    $stmt = $db->prepare("
        SELECT sc.id_sesion, sc.estado, sc.fecha_operacion, sc.saldo_inicial,
               dc.saldo_proximo_dia
        FROM sesion_caja sc
        LEFT JOIN detalle_cuadre dc ON dc.sesion_id = sc.id_sesion
        WHERE sc.caja_id = :cid
        ORDER BY sc.id_sesion ASC
    ");
    $stmt->execute(['cid' => $cajaId]);
    $sesiones = $stmt->fetchAll();

    $ultimoSaldoProximo   = null;
    $ultimoSesionConCuadre = null;
    foreach ($sesiones as $s) {
        $esCuadrada = in_array($s['estado'], ['CERRADA','EN_REVISION','OBSERVADA','RECHAZADA','APROBADA'], true)
                      && $s['saldo_proximo_dia'] !== null;

        if ($ultimoSaldoProximo !== null) {
            $dif = round((float)$s['saldo_inicial'] - (float)$ultimoSaldoProximo, 2);
            if (abs($dif) >= 0.01) {
                $totalRotos++;
                printf(
                    "  caja %d: sesion %d (%s, %s) saldo_inicial=%.2f != saldo_proximo_dia de sesion %d (=%.2f) -> diferencia %.2f\n",
                    $cajaId, $s['id_sesion'], $s['fecha_operacion'], $s['estado'],
                    (float)$s['saldo_inicial'], $ultimoSesionConCuadre, (float)$ultimoSaldoProximo, $dif
                );
            }
        }

        if ($esCuadrada) {
            $ultimoSaldoProximo   = $s['saldo_proximo_dia'];
            $ultimoSesionConCuadre = $s['id_sesion'];
        }
    }
}
echo $totalRotos . " salto(s) de cadena encontrados\n";

echo "\n=== 4) Incidencias automáticas desactualizadas respecto al cuadre actual ===\n";
$rows = $db->query("
    SELECT ic.id_incidencia, ic.sesion_origen_id, ic.tipo, ic.monto_original,
           ic.monto_pendiente, ic.estado, sc.diferencia_final, sc.fecha_operacion, sc.caja_id
    FROM incidencia_contable ic
    JOIN sesion_caja sc ON sc.id_sesion = ic.sesion_origen_id
    WHERE ic.auto_detectado = 1
      AND ic.estado IN ('ABIERTO','PARCIAL')
      AND ABS(
            (CASE WHEN ic.tipo = 'FALTANTE' THEN -ic.monto_original ELSE ic.monto_original END)
            - sc.diferencia_final
          ) >= 0.01
    ORDER BY ic.id_incidencia
")->fetchAll();
echo count($rows) . " incidencia(s) desactualizada(s)\n";
foreach ($rows as $r) {
    printf(
        "  incidencia %d (sesion %d, caja %d, %s): tipo=%s monto_original=%.2f monto_pendiente=%.2f estado=%s | diferencia_final actual de la sesion=%.2f\n",
        $r['id_incidencia'], $r['sesion_origen_id'], $r['caja_id'], $r['fecha_operacion'],
        $r['tipo'], $r['monto_original'], $r['monto_pendiente'], $r['estado'],
        $r['diferencia_final']
    );
}
