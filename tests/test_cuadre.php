<?php
/**
 * Pruebas de integración para recalcularDiferenciaCompleta().
 * Cada escenario corre dentro de una transacción que se hace ROLLBACK
 * al finalizar, así la BD queda limpia sin importar el resultado.
 *
 * Uso:  php tests/test_cuadre.php
 */

require_once __DIR__ . '/../src/Core/Database.php';
require_once __DIR__ . '/../src/Repositories/CajaRepository.php';

// ── IDs fijos de la BD real ────────────────────────────────
const CAJA_ID       = 2;   // SB2 – Local 2
const TURNO_ID      = 1;   // Mañana
const POSTULANTE_ID = 1;   // Gian Carlo
const MODO_ID       = 2;   // Yape/Plin

// ── Colores de consola ─────────────────────────────────────
const OK  = "\033[32m✓\033[0m";
const ERR = "\033[31m✗\033[0m";

// ── Contador global ────────────────────────────────────────
$passed = 0;
$failed = 0;

// ── Helpers ───────────────────────────────────────────────

function db(): PDO { return Database::getConnection(); }

/**
 * Crea una sesion_caja (CERRADA) + detalle_cuadre con valores controlados.
 * Devuelve el id_sesion insertado.
 */
function crearSesion(float $saldoInicial, float $efectivo, float $ventas, float $gastos): int
{
    $d = db();
    $d->prepare("
        INSERT INTO sesion_caja
            (caja_id, turno_id, postulante_apertura_id, estado, saldo_inicial, fecha_operacion)
        VALUES (:cid, :tid, :pid, 'CERRADA', :sal, CURDATE())
    ")->execute([
        'cid' => CAJA_ID,
        'tid' => TURNO_ID,
        'pid' => POSTULANTE_ID,
        'sal' => $saldoInicial,
    ]);
    $sid = (int)$d->lastInsertId();

    $d->prepare("
        INSERT INTO detalle_cuadre
            (sesion_id,
             total_efectivo_contado, total_contado_general,
             total_ventas_sistema, total_gastos_sistema,
             total_esperado_sistema, diferencia, resultado_cuadre,
             saldo_proxima_efectivo, saldo_proximo_dia)
        VALUES (:sid, :efe, :efe2, :ven, :gas, 0, 0, 'CONSISTENTE', 0, 0)
    ")->execute([
        'sid'  => $sid,
        'efe'  => $efectivo,
        'efe2' => $efectivo,
        'ven'  => $ventas,
        'gas'  => $gastos,
    ]);

    return $sid;
}

/** Lee diferencia y resultado de detalle_cuadre para la sesión dada. */
function dc(int $sid): array
{
    $stmt = db()->prepare(
        "SELECT diferencia, resultado_cuadre FROM detalle_cuadre WHERE sesion_id = :sid"
    );
    $stmt->execute(['sid' => $sid]);
    return $stmt->fetch();
}

/** Ejecuta un escenario dentro de una transacción que siempre se revierte. */
function escenario(string $nombre, callable $fn): void
{
    db()->beginTransaction();
    echo "\n[\033[1m{$nombre}\033[0m]\n";
    try {
        $fn();
    } catch (Throwable $e) {
        global $failed;
        $failed++;
        echo ERR . " EXCEPCIÓN: " . $e->getMessage() . "\n";
    } finally {
        db()->rollBack();
    }
}

/** Compara diferencia esperada con la almacenada en detalle_cuadre. */
function ok(string $label, float $difEsperada, int $sid, string $resultadoEsp = ''): void
{
    global $passed, $failed;
    $row    = dc($sid);
    $difAct = round((float)$row['diferencia'], 2);
    $difExp = round($difEsperada, 2);
    $resAct = $row['resultado_cuadre'] ?? '';

    $matchDif = abs($difExp - $difAct) < 0.01;
    $matchRes = $resultadoEsp === '' || $resultadoEsp === $resAct;

    if ($matchDif && $matchRes) {
        $passed++;
        echo OK . " {$label}\n";
    } else {
        $failed++;
        $detalle = !$matchDif
            ? "diferencia: esperado={$difExp} obtenido={$difAct}"
            : "resultado: esperado={$resultadoEsp} obtenido={$resAct}";
        echo ERR . " {$label} → {$detalle}\n";
    }
}

// ─────────────────────────────────────────────────────────
//  ESCENARIOS
// ─────────────────────────────────────────────────────────

$repo = new CajaRepository();

// 1. Fórmula base
escenario('1. Fórmula base – conforme exacto', function() use ($repo) {
    // saldo=100 + ventas=200 - gastos=50 = esperado=250 | efectivo=250 → dif=0
    $sid = crearSesion(100, 250, 200, 50);
    $repo->recalcularDiferenciaCompleta($sid);
    ok('diferencia=0 CONSISTENTE', 0, $sid, 'CONSISTENTE');
});

// 2. Faltante simple
escenario('2. Faltante simple', function() use ($repo) {
    // esperado=250, efectivo=200 → dif=-50
    $sid = crearSesion(100, 200, 200, 50);
    $repo->recalcularDiferenciaCompleta($sid);
    ok('diferencia=-50 FALTANTE', -50, $sid, 'FALTANTE');
});

// 3. Sobrante simple
escenario('3. Sobrante simple', function() use ($repo) {
    // esperado=250, efectivo=300 → dif=+50
    $sid = crearSesion(100, 300, 200, 50);
    $repo->recalcularDiferenciaCompleta($sid);
    ok('diferencia=+50 SOBRANTE', 50, $sid, 'SOBRANTE');
});

// 4. Pago digital APROBADO resta de loQueSeDice
escenario('4. Pago digital APROBADO', function() use ($repo) {
    // ventas=100, efectivo=70, digital APROBADO=30 → loQueSeDice=100-30=70, dif=0
    $sid = crearSesion(0, 70, 100, 0);
    db()->prepare("
        INSERT INTO movimiento_sesion
            (sesion_id, tipo_movimiento_id, modo_id, postulante_registro_id, monto, estado)
        VALUES (:sid, 1, :mid, :pid, 30, 'APROBADO')
    ")->execute(['sid' => $sid, 'mid' => MODO_ID, 'pid' => POSTULANTE_ID]);
    $repo->recalcularDiferenciaCompleta($sid);
    ok('digital APROBADO 30 → conforme', 0, $sid, 'CONSISTENTE');
});

// 5. Pago digital PENDIENTE también resta (declarado pero no confirmado)
escenario('5. Pago digital PENDIENTE resta igual', function() use ($repo) {
    $sid = crearSesion(0, 70, 100, 0);
    db()->prepare("
        INSERT INTO movimiento_sesion
            (sesion_id, tipo_movimiento_id, modo_id, postulante_registro_id, monto, estado)
        VALUES (:sid, 1, :mid, :pid, 30, 'PENDIENTE')
    ")->execute(['sid' => $sid, 'mid' => MODO_ID, 'pid' => POSTULANTE_ID]);
    $repo->recalcularDiferenciaCompleta($sid);
    ok('digital PENDIENTE 30 → conforme', 0, $sid, 'CONSISTENTE');
});

// 6. Pago digital RECHAZADO NO debe restar
escenario('6. Pago digital RECHAZADO no cuenta', function() use ($repo) {
    // mismo caso pero RECHAZADO → loQueSeDice=100, efectivo=70 → dif=-30
    $sid = crearSesion(0, 70, 100, 0);
    db()->prepare("
        INSERT INTO movimiento_sesion
            (sesion_id, tipo_movimiento_id, modo_id, postulante_registro_id, monto, estado)
        VALUES (:sid, 1, :mid, :pid, 30, 'RECHAZADO')
    ")->execute(['sid' => $sid, 'mid' => MODO_ID, 'pid' => POSTULANTE_ID]);
    $repo->recalcularDiferenciaCompleta($sid);
    ok('digital RECHAZADO no resta → faltante -30', -30, $sid, 'FALTANTE');
});

// 7. Rectificación DINERO_ENCONTRADO suma a loQueEs
escenario('7. Rectificación DINERO_ENCONTRADO', function() use ($repo) {
    // faltante -50 → se encuentra 50 → conforme
    $sid = crearSesion(100, 200, 200, 50);
    $repo->recalcularDiferenciaCompleta($sid);
    ok('antes de rectif: faltante -50', -50, $sid);

    db()->prepare("
        INSERT INTO rectificacion_cuadre
            (sesion_id, postulante_registra_id, tipo_rectificacion, monto, descripcion_contexto, estado)
        VALUES (:sid, :pid, 'DINERO_ENCONTRADO', 50, 'Hallado en caja fuerte', 'APROBADA')
    ")->execute(['sid' => $sid, 'pid' => POSTULANTE_ID]);
    $repo->recalcularDiferenciaCompleta($sid);
    ok('después de rectif DINERO_ENCONTRADO +50 → conforme', 0, $sid, 'CONSISTENTE');
});

// 8. Rectificación DEVOLUCION_DINERO resta de loQueEs
escenario('8. Rectificación DEVOLUCION_DINERO', function() use ($repo) {
    // conforme, luego devuelven 30 → faltante -30
    $sid = crearSesion(0, 100, 100, 0);
    $repo->recalcularDiferenciaCompleta($sid);
    ok('antes de rectif: conforme', 0, $sid);

    db()->prepare("
        INSERT INTO rectificacion_cuadre
            (sesion_id, postulante_registra_id, tipo_rectificacion, monto, descripcion_contexto, estado)
        VALUES (:sid, :pid, 'DEVOLUCION_DINERO', -30, 'Devuelto a cliente', 'APROBADA')
    ")->execute(['sid' => $sid, 'pid' => POSTULANTE_ID]);
    $repo->recalcularDiferenciaCompleta($sid);
    ok('DEVOLUCION_DINERO -30 → faltante -30', -30, $sid, 'FALTANTE');
});

// 9. Ajuste esperado AGREGAR reduce loQueSeDice (disminuye el faltante)
escenario('9. Ajuste esperado AGREGAR', function() use ($repo) {
    // faltante -30, AGREGAR 30 → conforme
    $sid = crearSesion(0, 70, 100, 0);
    db()->prepare("
        INSERT INTO ajuste_esperado (sesion_id, tipo, accion, descripcion, monto, postulante_id)
        VALUES (:sid, 'COBRO', 'AGREGAR', 'Test AGREGAR', 30, :pid)
    ")->execute(['sid' => $sid, 'pid' => POSTULANTE_ID]);
    $repo->recalcularDiferenciaCompleta($sid);
    ok('AGREGAR 30 sobre faltante 30 → conforme', 0, $sid, 'CONSISTENTE');
});

// 10. Ajuste esperado QUITAR aumenta loQueSeDice (genera faltante)
escenario('10. Ajuste esperado QUITAR', function() use ($repo) {
    // conforme, QUITAR 20 → faltante -20
    $sid = crearSesion(0, 100, 100, 0);
    db()->prepare("
        INSERT INTO ajuste_esperado (sesion_id, tipo, accion, descripcion, monto, postulante_id)
        VALUES (:sid, 'COBRO', 'QUITAR', 'Test QUITAR', 20, :pid)
    ")->execute(['sid' => $sid, 'pid' => POSTULANTE_ID]);
    $repo->recalcularDiferenciaCompleta($sid);
    ok('QUITAR 20 → faltante -20', -20, $sid, 'FALTANTE');
});

// 11. Corrección de venta al alza → mayor loQueSeDice → más faltante
escenario('11. Corrección de venta al alza', function() use ($repo) {
    // ventas=100, efectivo=100 → conforme. corrección ventas 100→120 → faltante -20
    $sid = crearSesion(0, 100, 100, 0);
    db()->prepare("
        INSERT INTO correccion_venta (sesion_id, monto_anterior, monto_nuevo, motivo, usuario_id)
        VALUES (:sid, 100, 120, 'Test corrección alza', :pid)
    ")->execute(['sid' => $sid, 'pid' => POSTULANTE_ID]);
    $repo->recalcularDiferenciaCompleta($sid);
    ok('corrección ventas +20 → faltante -20', -20, $sid, 'FALTANTE');
});

// 12. Corrección de venta a la baja → menor loQueSeDice → sobrante
escenario('12. Corrección de venta a la baja', function() use ($repo) {
    // ventas=100, efectivo=100 → conforme. corrección ventas 100→80 → sobrante +20
    $sid = crearSesion(0, 100, 100, 0);
    db()->prepare("
        INSERT INTO correccion_venta (sesion_id, monto_anterior, monto_nuevo, motivo, usuario_id)
        VALUES (:sid, 100, 80, 'Test corrección baja', :pid)
    ")->execute(['sid' => $sid, 'pid' => POSTULANTE_ID]);
    $repo->recalcularDiferenciaCompleta($sid);
    ok('corrección ventas -20 → sobrante +20', 20, $sid, 'SOBRANTE');
});

// 13. Múltiples correcciones de venta acumuladas
escenario('13. Múltiples correcciones de venta acumuladas', function() use ($repo) {
    // ventas=100, efectivo=100. corrección1: 100→110 (+10). corrección2: 110→90 (-20). neto=-10 → faltante
    $sid = crearSesion(0, 100, 100, 0);
    db()->prepare("
        INSERT INTO correccion_venta (sesion_id, monto_anterior, monto_nuevo, motivo, usuario_id)
        VALUES (:sid, 100, 110, 'Corr1', :pid)
    ")->execute(['sid' => $sid, 'pid' => POSTULANTE_ID]);
    db()->prepare("
        INSERT INTO correccion_venta (sesion_id, monto_anterior, monto_nuevo, motivo, usuario_id)
        VALUES (:sid, 110, 90, 'Corr2', :pid)
    ")->execute(['sid' => $sid, 'pid' => POSTULANTE_ID]);
    $repo->recalcularDiferenciaCompleta($sid);
    ok('neto correcciones (110→90, delta=-10) → sobrante +10', 10, $sid, 'SOBRANTE');
});

// 14. Combinación: digital + corrección de venta que se compensan
escenario('14. Combinación: digital APROBADO + corrección venta se compensan', function() use ($repo) {
    // ventas=100, gastos=0, efectivo=80
    // digital APROBADO=20 → loQueSeDice=100-20=80, dif=0 (conforme)
    // corrección venta +10 → loQueSeDice=90, dif=-10
    // ajuste AGREGAR 10 → loQueSeDice=80, dif=0 (conforme de nuevo)
    $sid = crearSesion(0, 80, 100, 0);
    db()->prepare("
        INSERT INTO movimiento_sesion
            (sesion_id, tipo_movimiento_id, modo_id, postulante_registro_id, monto, estado)
        VALUES (:sid, 1, :mid, :pid, 20, 'APROBADO')
    ")->execute(['sid' => $sid, 'mid' => MODO_ID, 'pid' => POSTULANTE_ID]);
    $repo->recalcularDiferenciaCompleta($sid);
    ok('paso 1: digital -20 → conforme', 0, $sid);

    db()->prepare("
        INSERT INTO correccion_venta (sesion_id, monto_anterior, monto_nuevo, motivo, usuario_id)
        VALUES (:sid, 100, 110, 'Corrección', :pid)
    ")->execute(['sid' => $sid, 'pid' => POSTULANTE_ID]);
    $repo->recalcularDiferenciaCompleta($sid);
    ok('paso 2: corrección +10 → faltante -10', -10, $sid);

    db()->prepare("
        INSERT INTO ajuste_esperado (sesion_id, tipo, accion, descripcion, monto, postulante_id)
        VALUES (:sid, 'COBRO', 'AGREGAR', 'Compensa corrección', 10, :pid)
    ")->execute(['sid' => $sid, 'pid' => POSTULANTE_ID]);
    $repo->recalcularDiferenciaCompleta($sid);
    ok('paso 3: ajuste AGREGAR +10 → conforme otra vez', 0, $sid, 'CONSISTENTE');
});

// 15. Idempotencia: llamar dos veces da el mismo resultado
escenario('15. Idempotencia – doble llamada no cambia el resultado', function() use ($repo) {
    $sid = crearSesion(100, 200, 200, 50);
    $repo->recalcularDiferenciaCompleta($sid);
    $primera = (float)dc($sid)['diferencia'];
    $repo->recalcularDiferenciaCompleta($sid);
    $segunda = (float)dc($sid)['diferencia'];
    global $passed, $failed;
    if (abs($primera - $segunda) < 0.01) {
        $passed++;
        echo OK . " idempotente: {$primera} == {$segunda}\n";
    } else {
        $failed++;
        echo ERR . " NO idempotente: {$primera} != {$segunda}\n";
    }
});

// ─────────────────────────────────────────────────────────
//  RESUMEN
// ─────────────────────────────────────────────────────────
echo "\n";
echo str_repeat('─', 50) . "\n";
$total = $passed + $failed;
if ($failed === 0) {
    echo "\033[32mTodos los tests pasaron: {$passed}/{$total}\033[0m\n";
} else {
    echo "\033[31mFallidos: {$failed}/{$total}  |  OK: {$passed}/{$total}\033[0m\n";
}
echo str_repeat('─', 50) . "\n";
