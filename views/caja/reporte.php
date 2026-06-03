<?php
/** @var array $sesion */ /** @var array $detalle */ /** @var array|false $venta */
/** @var array $gastos */ /** @var array $rectifs */ /** @var array $digitales */
/** @var float $digital_aprobado */ /** @var string|null $vendedor */
/** @var array $correccionesVenta */ /** @var float $sumCorrDelta */
$basePath  = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
$userName  = $userName ?? $_SESSION['user_name'] ?? 'Usuario';
$userRol   = $userRol  ?? $_SESSION['user_rol']  ?? 'STAFF';
$esAdmin   = $userRol === 'ADMIN';
$f2 = fn($v) => 'S/ ' . number_format((float)$v, 2, '.', ',');

$loQueEsFisico = (float)($detalle['monto_caja_exterior']        ?? 0)
               + (float)($detalle['monto_monedas']              ?? 0)
               + (float)($detalle['monto_billetes_caja']        ?? 0)
               + (float)($detalle['monto_billetes_caja_fuerte'] ?? 0)
               + (float)($detalle['monto_agente_bcp']           ?? 0);

$sum_rectifs = array_sum(array_column($rectifs ?? [], 'monto'));
$loQueEs     = round($loQueEsFisico + $sum_rectifs, 2);

$saldo_ini        = (float)($sesion['saldo_inicial'] ?? 0);
$correccionesVenta = $correccionesVenta ?? [];
$sumCorrDelta      = $sumCorrDelta ?? 0.0;
$total_ventas     = round((float)($detalle['total_ventas_sistema'] ?? ($venta['monto'] ?? 0)) + $sumCorrDelta, 2);
$total_gastos     = (float)($detalle['total_gastos_sistema'] ?? 0);
$digital_aprobado = $digital_aprobado ?? 0;

$sum_ajustes_esp = 0.0;
foreach ($ajustesEsperado ?? [] as $aj) {
    $sum_ajustes_esp += $aj['accion'] === 'AGREGAR' ? -(float)$aj['monto'] : (float)$aj['monto'];
}

$loQueSeDice = round($saldo_ini + $total_ventas - $total_gastos - $digital_aprobado + $sum_ajustes_esp, 2);
$diferencia  = round($loQueEs - $loQueSeDice, 2);
$resultado   = abs($diferencia) < 0.01 ? 'CONSISTENTE' : ($diferencia > 0 ? 'SOBRANTE' : 'FALTANTE');
$clsDif      = abs($diferencia) < 0.01 ? 'dif-ok' : ($diferencia > 0 ? 'dif-sobrante' : 'dif-faltante');

$totalCorrecciones = count($rectifs ?? []) + count($ajustesEsperado ?? []) + count($correccionesVenta ?? []);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arqueo #<?= $sesion['id_sesion'] ?> | Solo Boticas</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/caja.css">
</head>
<body>

<header class="caja-header">
    <div class="caja-header__brand">
        <div class="caja-header__logo">SB</div>
        <div>
            <p class="caja-header__company">Grupo KGyR S.A.C</p>
            <p class="caja-header__app">Arqueo de caja</p>
        </div>
    </div>
    <div class="caja-header__right">
        <?php if ($esAdmin): ?>
        <button onclick="abrirModalEliminar()"
                style="background:transparent;border:1px solid #fca5a5;color:#ef4444;border-radius:6px;padding:5px 13px;font-size:0.75rem;font-weight:600;cursor:pointer;">
            🗑 Eliminar arqueo
        </button>
        <?php endif; ?>
        <a href="<?= $basePath ?>/caja" class="caja-btn-back">← Volver a caja</a>
    </div>
</header>

<!-- Modal eliminar arqueo (solo ADMIN) -->
<?php if ($esAdmin): ?>
<div id="modalEliminar" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.55);z-index:500;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:14px;padding:1.75rem;width:320px;max-width:90vw;box-shadow:0 20px 60px rgba(0,0,0,.22);">
        <h3 style="font-size:1rem;font-weight:700;color:#1e293b;margin-bottom:.35rem;">Eliminar cuadre #<?= $sesion['id_sesion'] ?></h3>
        <p style="font-size:0.8rem;color:#64748b;margin-bottom:1rem;">
            Esta acción borrará el cuadre y todos sus datos. No se puede deshacer.
        </p>
        <label style="font-size:0.8rem;font-weight:600;color:#334155;display:block;margin-bottom:.35rem;">
            Confirma con tu contraseña de administrador
        </label>
        <input type="password" id="eliminarPassword"
               style="width:100%;padding:.55rem .75rem;border:1.5px solid #e2e8f0;border-radius:8px;font-size:0.85rem;margin-bottom:.75rem;box-sizing:border-box;"
               placeholder="Contraseña" autocomplete="current-password">
        <div id="eliminarMsg" style="font-size:0.78rem;color:#991b1b;margin-bottom:.5rem;display:none;"></div>
        <div style="display:flex;gap:.6rem;justify-content:flex-end;">
            <button onclick="cerrarModalEliminar()"
                    style="background:#f1f5f9;border:none;border-radius:7px;padding:.5rem 1rem;font-size:0.82rem;cursor:pointer;color:#475569;">
                Cancelar
            </button>
            <button onclick="confirmarEliminar(<?= $sesion['id_sesion'] ?>)"
                    style="background:#dc2626;border:none;border-radius:7px;padding:.5rem 1rem;font-size:0.82rem;font-weight:700;color:#fff;cursor:pointer;">
                Eliminar definitivamente
            </button>
        </div>
    </div>
</div>
<?php endif; ?>

<main class="caja-main">

    <!-- ── 1. Encabezado del turno ─────────────────────────── -->
    <section class="caja-card caja-card--info">
        <div style="display:flex;flex-wrap:wrap;gap:.5rem 1.5rem;align-items:center;">
            <div>
                <p style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin:0 0 .2rem;">Arqueo #<?= $sesion['id_sesion'] ?></p>
                <p style="font-size:1rem;font-weight:700;color:#1e293b;margin:0;">
                    <?= htmlspecialchars($sesion['caja_desc']) ?>
                    <span style="color:#94a3b8;font-weight:400;">·</span>
                    <?= htmlspecialchars($sesion['local_desc']) ?>
                </p>
            </div>
            <div style="display:flex;gap:1.5rem;flex-wrap:wrap;font-size:.82rem;color:#475569;margin-left:auto;">
                <span><strong>Turno:</strong> <?= htmlspecialchars($sesion['turno_desc']) ?></span>
                <span><strong>Fecha:</strong> <?= date('d/m/Y', strtotime($sesion['fecha_operacion'])) ?></span>
                <span><strong>Cajera:</strong> <?= htmlspecialchars($sesion['cajera_nombre']) ?></span>
                <?php if ($vendedor): ?>
                <span><strong>Vendedor/a:</strong> <?= htmlspecialchars($vendedor) ?></span>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- ── 2. Resultado del arqueo ──────────────────────────── -->
    <section class="caja-card caja-resultado <?= $clsDif ?>">
        <div class="caja-resultado__icono">
            <?= abs($diferencia) < 0.01 ? '✓' : ($diferencia > 0 ? '↑' : '↓') ?>
        </div>
        <div class="caja-resultado__info">
            <p class="caja-resultado__label">Resultado del arqueo</p>
            <h2 class="caja-resultado__estado">
                <?= $resultado === 'CONSISTENTE' ? 'CONFORME' : $resultado ?>
            </h2>
            <p class="caja-resultado__dif">
                Diferencia: <strong><?= $f2($diferencia) ?></strong>
                <?= abs($diferencia) < 0.01 ? '' : ($diferencia > 0 ? '— Superávit' : '— Déficit') ?>
            </p>
        </div>
        <?php if (!empty($incidenciaContable)): ?>
        <?php
            $icEst  = $incidenciaContable['estado'];
            $icPend = (float)$incidenciaContable['monto_pendiente'];
            if ($icEst === 'CERRADO') {
                $icBg = 'rgba(209,250,229,.95)'; $icColor = '#065f46'; $icLabel = 'Arqueo cerrado'; $icIcon = '✓';
            } elseif ($icEst === 'PARCIAL') {
                $icBg = 'rgba(254,243,199,.95)'; $icColor = '#92400e'; $icLabel = 'Caso en proceso'; $icIcon = '◑';
            } else {
                $icBg = 'rgba(255,255,255,.95)'; $icColor = '#991b1b'; $icLabel = 'Caso abierto'; $icIcon = '!';
            }
        ?>
        <div style="margin-top:1rem;">
            <a href="<?= $basePath ?>/incidencias/<?= $incidenciaContable['id_incidencia'] ?>"
               style="display:inline-flex;align-items:center;gap:.6rem;
                      padding:.75rem 1.6rem;background:<?= $icBg ?>;color:<?= $icColor ?>;
                      border:2px solid <?= $icColor ?>;border-radius:10px;
                      font-size:.95rem;font-weight:700;text-decoration:none;letter-spacing:.01em;">
                <span style="font-size:1.1rem;"><?= $icIcon ?></span>
                <?= $icLabel ?>
                <?php if ($icEst !== 'CERRADO'): ?>
                    <span style="font-weight:500;opacity:.8;font-size:.85rem;">— pendiente <?= $f2($icPend) ?></span>
                <?php endif; ?>
                <span style="opacity:.5;font-size:.8rem;">→</span>
            </a>
        </div>
        <?php endif; ?>
    </section>

    <!-- ── 3. Detalle del cálculo ────────────────────────────── -->
    <section class="caja-card">
        <h2 class="caja-card__title">Detalle del arqueo</h2>
        <div class="caja-cuadre-grid">

            <div class="caja-cuadre-col caja-cuadre-col--esperado">
                <h3 class="caja-cuadre-col__title">📋 SALDO ESPERADO</h3>
                <div class="caja-linea">
                    <span>Saldo de apertura</span>
                    <strong><?= $f2($saldo_ini) ?></strong>
                </div>
                <div class="caja-linea">
                    <span>+ Ventas del turno</span>
                    <strong><?= $f2($total_ventas) ?></strong>
                </div>
                <div class="caja-linea caja-linea--sub">
                    <span>− Egresos del turno</span>
                    <strong style="color:#dc2626;"><?= $f2($total_gastos) ?></strong>
                </div>
                <?php if ($digital_aprobado > 0): ?>
                <div class="caja-linea caja-linea--sub">
                    <span>− Cobros electrónicos</span>
                    <strong class="text-danger"><?= $f2($digital_aprobado) ?></strong>
                </div>
                <?php endif; ?>
                <?php if ($sum_ajustes_esp != 0): ?>
                <div class="caja-linea <?= $sum_ajustes_esp > 0 ? '' : 'caja-linea--sub' ?>">
                    <?php $colorAj = $sum_ajustes_esp >= 0 ? '#059669' : '#dc2626'; ?>
                    <span style="color:<?= $colorAj ?>;font-weight:600;"><?= $sum_ajustes_esp >= 0 ? '+' : '−' ?> Ajustes declarados</span>
                    <strong style="color:<?= $colorAj ?>;"><?= $f2(abs($sum_ajustes_esp)) ?></strong>
                </div>
                <?php endif; ?>
                <div class="caja-linea caja-linea--total">
                    <span>= Efectivo esperado</span>
                    <strong><?= $f2($loQueSeDice) ?></strong>
                </div>
            </div>

            <div class="caja-cuadre-col caja-cuadre-col--real">
                <h3 class="caja-cuadre-col__title">🪙 ARQUEO DE CAJA</h3>
                <div class="caja-linea">
                    <span>Caja</span>
                    <strong><?= $f2($detalle['monto_caja_exterior'] ?? 0) ?></strong>
                </div>
                <div class="caja-linea">
                    <span>+ Monedas</span>
                    <strong><?= $f2($detalle['monto_monedas'] ?? 0) ?></strong>
                </div>
                <div class="caja-linea">
                    <span>+ Billetes</span>
                    <strong><?= $f2($detalle['monto_billetes_caja'] ?? 0) ?></strong>
                </div>
                <div class="caja-linea">
                    <span>+ Caja fuerte</span>
                    <strong><?= $f2($detalle['monto_billetes_caja_fuerte'] ?? 0) ?></strong>
                </div>
                <div class="caja-linea">
                    <span>+ Saldo Agente BCP</span>
                    <strong><?= $f2($detalle['monto_agente_bcp'] ?? 0) ?></strong>
                </div>
                <?php if ($sum_rectifs != 0): ?>
                <div class="caja-linea" style="margin-top:.25rem;">
                    <span style="color:#dc2626;font-weight:600;">+ Ajuste de caja</span>
                    <strong style="color:#dc2626;"><?= $f2($sum_rectifs) ?></strong>
                </div>
                <?php endif; ?>
                <div class="caja-linea caja-linea--total">
                    <span>= Total arqueado</span>
                    <strong><?= $f2($loQueEs) ?></strong>
                </div>
            </div>

        </div>
        <div class="caja-proximo">
            <p>→ Saldo de apertura del próximo turno: <strong class="text-highlight"><?= $f2($detalle['saldo_proximo_dia'] ?? $loQueEs) ?></strong>
               <small>(total arqueado)</small></p>
            <?php if (!empty($detalle['num_operaciones_bcp'])): ?>
            <p style="margin-top:.4rem;font-size:0.82rem;color:#64748b;">
                Operaciones BCP realizadas: <strong><?= (int)$detalle['num_operaciones_bcp'] ?></strong>
            </p>
            <?php endif; ?>
        </div>
    </section>

    <!-- ── 4. Movimientos del turno ──────────────────────────── -->

    <!-- Egresos -->
    <section class="caja-card">
        <h2 class="caja-card__title">
            Egresos del turno
            <span style="font-size:.72rem;font-weight:500;color:#94a3b8;margin-left:.4rem;"><?= count($gastos) ?> registro<?= count($gastos) !== 1 ? 's' : '' ?></span>
        </h2>
        <?php if (empty($gastos)): ?>
            <p class="caja-empty">Sin egresos registrados en este turno.</p>
        <?php else: ?>
        <table class="caja-table">
            <thead><tr><th>Tipo</th><th>Detalle</th><th>Comprobante</th><th class="text-right">Monto</th></tr></thead>
            <tbody>
            <?php
            $tipoPagoLabel = ['MES_ACTUAL' => 'Pago Mes Actual', 'MES_PASADO' => 'Pago Mes Pasado', 'PAGO_EXTRA' => 'Pago Extra'];
            $tipoDocLabel  = ['BOLETA' => 'Boleta', 'FACTURA' => 'Factura', 'NOTA_DE_VENTA' => 'Nota de venta'];
            foreach ($gastos as $g):
                $modo = $g['modo_ref'] ?? '';
                if ($modo === 'PERSONAL') {
                    $detGasto = htmlspecialchars($g['descripcion']) . ' · <em>' . ($tipoPagoLabel[$g['tipo_pago'] ?? ''] ?? '') . '</em>';
                } elseif ($modo === 'LOCAL') {
                    $detGasto = htmlspecialchars($g['descripcion']);
                    if (!empty($g['concepto_desc'])) $detGasto .= ' · ' . htmlspecialchars($g['concepto_desc']);
                } elseif ($modo === 'FACTURA') {
                    $detGasto = $tipoDocLabel[$g['tipo_documento'] ?? ''] ?? htmlspecialchars($g['descripcion']);
                } else {
                    $detGasto = htmlspecialchars($g['descripcion']);
                }
            ?>
                <tr>
                    <td><span class="caja-gasto-badge caja-gasto-badge--<?= $g['tipo_css'] ?? 'otro' ?>"><?= htmlspecialchars($g['etiqueta'] ?? '') ?></span></td>
                    <td style="font-size:.83rem;"><?= $detGasto ?></td>
                    <td style="font-size:.78rem;color:#64748b;"><?= htmlspecialchars($g['comprobante'] ?? '—') ?></td>
                    <td class="text-right" style="color:#dc2626;font-weight:700;">−<?= $f2($g['monto']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </section>

    <!-- Cobros electrónicos -->
    <section class="caja-card">
        <h2 class="caja-card__title">
            Cobros electrónicos
            <span style="font-size:.72rem;font-weight:500;color:#94a3b8;margin-left:.4rem;"><?= count($digitales ?? []) ?> registro<?= count($digitales ?? []) !== 1 ? 's' : '' ?></span>
        </h2>
        <?php if (empty($digitales)): ?>
            <p class="caja-empty">Sin cobros electrónicos registrados.</p>
        <?php else: ?>
        <table class="caja-table">
            <thead>
                <tr>
                    <th>Hora</th>
                    <th>Modo</th>
                    <th>N° Operación</th>
                    <th class="text-right">Monto</th>
                    <th class="text-center">Estado</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $estadoDigital = [
                'PENDIENTE' => ['label' => 'Pendiente', 'cls' => 'background:#fef3c7;color:#92400e;'],
                'APROBADO'  => ['label' => 'Aprobado',  'cls' => 'background:#d1fae5;color:#065f46;'],
                'RECHAZADO' => ['label' => 'Rechazado', 'cls' => 'background:#fee2e2;color:#991b1b;'],
            ];
            foreach ($digitales as $dg):
                $est = $estadoDigital[$dg['estado']] ?? ['label' => $dg['estado'], 'cls' => ''];
            ?>
                <tr>
                    <td style="font-size:.78rem;color:#64748b;"><?= date('H:i', strtotime($dg['fecha_movimiento'])) ?></td>
                    <td><strong><?= htmlspecialchars($dg['modo_desc']) ?></strong></td>
                    <td style="font-size:.78rem;"><?= htmlspecialchars($dg['numero_operacion'] ?? '—') ?></td>
                    <td class="text-right"><?= $f2($dg['monto']) ?></td>
                    <td class="text-center">
                        <span style="font-size:.72rem;font-weight:700;border-radius:4px;padding:2px 8px;<?= $est['cls'] ?>">
                            <?= $est['label'] ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </section>

    <!-- Vales SoloBank -->
    <?php
    $valesSB = array_values(array_filter(
        $digitales ?? [],
        fn($d) => strtolower(trim($d['modo_desc'] ?? '')) === 'solobank'
    ));
    if (!empty($valesSB)):
    ?>
    <section class="caja-card">
        <h2 class="caja-card__title">
            Vales SoloBank
            <span style="font-size:.72rem;font-weight:500;color:#94a3b8;margin-left:.4rem;"><?= count($valesSB) ?> vale<?= count($valesSB) !== 1 ? 's' : '' ?></span>
        </h2>
        <table class="caja-table">
            <thead>
                <tr><th>Código</th><th class="text-center">Estado</th><th class="text-right">Monto</th></tr>
            </thead>
            <tbody>
            <?php foreach ($valesSB as $v):
                $estCls = match($v['estado']) {
                    'APROBADO'  => 'background:#d1fae5;color:#065f46;',
                    'RECHAZADO' => 'background:#fee2e2;color:#991b1b;',
                    default     => 'background:#fef3c7;color:#92400e;',
                };
            ?>
                <tr>
                    <td style="font-size:.82rem;font-weight:600;font-family:monospace;"><?= htmlspecialchars($v['numero_operacion'] ?? '—') ?></td>
                    <td class="text-center">
                        <span style="font-size:.72rem;font-weight:700;padding:2px 8px;border-radius:5px;<?= $estCls ?>"><?= htmlspecialchars($v['estado']) ?></span>
                    </td>
                    <td class="text-right"><?= $f2($v['monto']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
    <?php endif; ?>

    <!-- Transferencias -->
    <?php if (!empty($transferencias)): ?>
    <section class="caja-card">
        <h2 class="caja-card__title">Transferencias de saldo del día</h2>
        <table class="caja-table">
            <thead>
                <tr>
                    <th>Operación</th>
                    <th class="text-right">Monto</th>
                    <th>Comprobante</th>
                    <th>Confirmado por</th>
                    <th>Hora</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($transferencias as $tr):
                $esEnvio = ((int)$tr['caja_origen_id'] === (int)$sesion['caja_id']);
            ?>
            <tr>
                <td>
                    <?php if ($esEnvio): ?>
                        <span style="color:#dc2626;font-weight:600;">↑ Enviado</span>
                        <span style="font-size:.75rem;color:#64748b;display:block;">hacia <?= htmlspecialchars($tr['caja_destino_desc']) ?></span>
                    <?php else: ?>
                        <span style="color:#059669;font-weight:600;">↓ Recibido</span>
                        <span style="font-size:.75rem;color:#64748b;display:block;">desde <?= htmlspecialchars($tr['caja_origen_desc']) ?></span>
                    <?php endif; ?>
                </td>
                <td class="text-right" style="font-weight:700;font-variant-numeric:tabular-nums;color:<?= $esEnvio ? '#dc2626' : '#059669' ?>;">
                    <?= $esEnvio ? '−' : '+' ?> S/ <?= number_format($tr['monto'], 2) ?>
                </td>
                <td style="font-size:.78rem;color:#475569;"><?= htmlspecialchars($tr['numero_comprobante'] ?? '—') ?></td>
                <td style="font-size:.75rem;"><?= htmlspecialchars($tr['confirmador_nombre'] ?? '—') ?></td>
                <td style="font-size:.72rem;color:#64748b;white-space:nowrap;"><?= date('H:i', strtotime($tr['confirmed_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
    <?php endif; ?>

    <!-- ── 5. Correcciones post-cierre ──────────────────────── -->
    <section class="caja-card" style="border-left:3px solid <?= $totalCorrecciones > 0 ? '#f59e0b' : '#e2e8f0' ?>;">
        <h2 class="caja-card__title" style="color:<?= $totalCorrecciones > 0 ? '#92400e' : '#64748b' ?>;">
            Correcciones post-cierre
            <?php if ($totalCorrecciones > 0): ?>
            <span style="font-size:.72rem;font-weight:700;background:#fef3c7;color:#92400e;padding:2px 9px;border-radius:5px;margin-left:.5rem;">
                <?= $totalCorrecciones ?> corrección<?= $totalCorrecciones !== 1 ? 'es' : '' ?>
            </span>
            <?php else: ?>
            <span style="font-size:.72rem;font-weight:500;color:#94a3b8;margin-left:.5rem;">Sin modificaciones</span>
            <?php endif; ?>
        </h2>

        <!-- Ajustes de arqueo (rectificaciones) -->
        <div style="margin-bottom:1.5rem;">
            <p style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin:0 0 .6rem;">
                Ajustes de arqueo
            </p>
            <?php if (empty($rectifs)): ?>
                <p style="font-size:.82rem;color:#cbd5e1;margin:0;">— Sin ajustes registrados</p>
            <?php else: ?>
            <table class="caja-table">
                <thead>
                    <tr><th>Tipo</th><th>Descripción</th><th>Registrado por</th><th class="text-right">Ajuste</th></tr>
                </thead>
                <tbody>
                <?php foreach ($rectifs as $r):
                    $signo  = (int)($r['signo'] ?? ($r['monto'] >= 0 ? 1 : -1));
                    $esSuma = $signo > 0;
                    $color  = $esSuma ? '#065f46' : '#991b1b';
                    $bg     = $esSuma ? '#d1fae5' : '#fee2e2';
                ?>
                    <tr>
                        <td><?= htmlspecialchars($r['etiqueta'] ?? $r['tipo_rectificacion']) ?></td>
                        <td style="font-size:.83rem;"><?= htmlspecialchars($r['descripcion_contexto']) ?></td>
                        <td style="font-size:.78rem;color:#64748b;"><?= htmlspecialchars($r['registrado_por']) ?></td>
                        <td class="text-right">
                            <span style="font-weight:700;color:<?= $color ?>;background:<?= $bg ?>;padding:2px 8px;border-radius:5px;font-size:.85rem;">
                                <?= $esSuma ? '+' : '−' ?> <?= $f2(abs((float)$r['monto'])) ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <!-- Correcciones de gastos (ajustes esperado) -->
        <div style="margin-bottom:1.5rem;">
            <p style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin:0 0 .6rem;">
                Correcciones de gastos
            </p>
            <?php if (empty($ajustesEsperado)): ?>
                <p style="font-size:.82rem;color:#cbd5e1;margin:0;">— Sin correcciones registradas</p>
            <?php else:
                $tipoLabel = ['COBRO'=>'Cobro elec.','PERSONAL'=>'Personal','LOCAL'=>'Local','COMPRA'=>'Compra','OTRO'=>'Otro'];
                $tipoColor = ['COBRO'=>'#3b82f6','PERSONAL'=>'#7c3aed','LOCAL'=>'#0e7490','COMPRA'=>'#0e7490','OTRO'=>'#64748b'];
                $tipoBg    = ['COBRO'=>'#eff6ff','PERSONAL'=>'#ede9fe','LOCAL'=>'#f0fdfe','COMPRA'=>'#f0fdfe','OTRO'=>'#f1f5f9'];
            ?>
            <table class="caja-table">
                <thead>
                    <tr><th>Tipo</th><th>Detalle</th><th>Acción</th><th class="text-right">Efecto</th></tr>
                </thead>
                <tbody>
                <?php foreach ($ajustesEsperado as $aj):
                    $esAgregar = $aj['accion'] === 'AGREGAR';
                    $t = $aj['tipo'] ?? 'COBRO';
                    if ($t === 'COBRO')        $detAj = htmlspecialchars($aj['modo_desc'] ?? '');
                    elseif ($t === 'PERSONAL') $detAj = htmlspecialchars($aj['staff_desc'] ?? '') . ($aj['tipo_pago'] ? ' · ' . (['MES_ACTUAL'=>'Pago Mes Actual','MES_PASADO'=>'Pago Mes Pasado','PAGO_EXTRA'=>'Pago Extra'][$aj['tipo_pago']] ?? $aj['tipo_pago']) : '');
                    elseif ($t === 'LOCAL')    $detAj = htmlspecialchars($aj['local_desc'] ?? '') . ($aj['concepto_desc'] ? ' · ' . htmlspecialchars($aj['concepto_desc']) : '');
                    elseif ($t === 'COMPRA')   $detAj = ['BOLETA'=>'Boleta','FACTURA'=>'Factura','NOTA_DE_VENTA'=>'Nota de venta'][$aj['tipo_documento'] ?? ''] ?? '';
                    else                       $detAj = htmlspecialchars($aj['descripcion'] ?? '');
                    $notaDesc = ($t !== 'OTRO' && !empty($aj['descripcion'])) ? '<br><span style="font-size:.73rem;color:#94a3b8;">' . htmlspecialchars($aj['descripcion']) . '</span>' : '';
                    $efectoPos = !$esAgregar;
                ?>
                    <tr>
                        <td>
                            <span style="font-size:.68rem;font-weight:700;padding:2px 7px;border-radius:5px;
                                background:<?= $tipoBg[$t] ?? '#f1f5f9' ?>;color:<?= $tipoColor[$t] ?? '#64748b' ?>">
                                <?= $tipoLabel[$t] ?? $t ?>
                            </span>
                        </td>
                        <td style="font-size:.82rem;"><?= $detAj ?><?= $notaDesc ?></td>
                        <td>
                            <span style="font-size:.72rem;font-weight:700;padding:2px 7px;border-radius:5px;
                                background:<?= $efectoPos ? '#d1fae5' : '#fee2e2' ?>;
                                color:<?= $efectoPos ? '#065f46' : '#991b1b' ?>">
                                <?= $esAgregar ? '− Agregar' : '+ Quitar' ?>
                            </span>
                        </td>
                        <td class="text-right" style="font-weight:700;color:<?= $efectoPos ? '#065f46' : '#991b1b' ?>">
                            <?= $esAgregar ? '−' : '+' ?> <?= $f2($aj['monto']) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <!-- Corrección de ventas -->
        <div>
            <p style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin:0 0 .6rem;">
                Corrección de ventas
            </p>
            <div style="display:flex;gap:.75rem;align-items:center;flex-wrap:wrap;margin-bottom:.6rem;">
                <span style="font-size:.82rem;color:#64748b;">Valor final del turno:</span>
                <strong style="font-size:1rem;font-variant-numeric:tabular-nums;"><?= $f2($total_ventas) ?></strong>
                <?php if (!empty($correccionesVenta)): ?>
                <span style="font-size:.72rem;background:#fef3c7;color:#92400e;padding:2px 8px;border-radius:5px;font-weight:700;">
                    ✏ <?= count($correccionesVenta) ?> corrección<?= count($correccionesVenta) !== 1 ? 'es' : '' ?>
                </span>
                <?php endif; ?>
            </div>
            <?php if (empty($correccionesVenta)): ?>
                <p style="font-size:.82rem;color:#cbd5e1;margin:0;">— Sin correcciones de ventas</p>
            <?php else: ?>
            <table class="caja-table">
                <thead>
                    <tr>
                        <th>Antes</th><th>Después</th><th class="text-right">Δ</th>
                        <th>Motivo</th><th>Por</th><th>Hora</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($correccionesVenta as $cv):
                    $delta = (float)$cv['monto_nuevo'] - (float)$cv['monto_anterior'];
                    $colorDelta = abs($delta) < 0.01 ? '#64748b' : ($delta > 0 ? '#059669' : '#dc2626');
                ?>
                    <tr>
                        <td style="font-variant-numeric:tabular-nums;"><?= $f2($cv['monto_anterior']) ?></td>
                        <td style="font-variant-numeric:tabular-nums;font-weight:700;"><?= $f2($cv['monto_nuevo']) ?></td>
                        <td class="text-right" style="font-weight:700;color:<?= $colorDelta ?>;font-variant-numeric:tabular-nums;">
                            <?= $delta >= 0 ? '+' : '−' ?> <?= $f2(abs($delta)) ?>
                        </td>
                        <td style="font-size:.8rem;color:#475569;"><?= htmlspecialchars($cv['motivo'] ?? '—') ?></td>
                        <td style="font-size:.78rem;color:#64748b;"><?= htmlspecialchars($cv['registrado_por'] ?? '—') ?></td>
                        <td style="font-size:.72rem;color:#94a3b8;white-space:nowrap;"><?= date('d/m H:i', strtotime($cv['fecha_registro'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </section>

    <!-- ── 6. Comentario del turno ───────────────────────────── -->
    <section class="caja-card">
        <h2 class="caja-card__title">Comentario del turno</h2>
        <?php if (!empty($sesion['comentario_cajera'])): ?>
        <div style="padding:.85rem 1rem;background:#fef9c3;border-left:4px solid #ca8a04;border-radius:0 8px 8px 0;margin-bottom:.75rem;">
            <p style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#92400e;margin:0 0 .35rem;">Cajera</p>
            <p style="color:#1e293b;font-size:.86rem;margin:0;white-space:pre-wrap;"><?= htmlspecialchars($sesion['comentario_cajera']) ?></p>
        </div>
        <?php if (!empty($sesion['respuesta_admin'])): ?>
        <div style="padding:.85rem 1rem;background:#eff6ff;border-left:4px solid #3b82f6;border-radius:0 8px 8px 0;">
            <p style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#3b82f6;margin:0 0 .35rem;">Administrador</p>
            <p style="color:#1e293b;font-size:.86rem;margin:0;white-space:pre-wrap;"><?= htmlspecialchars($sesion['respuesta_admin']) ?></p>
        </div>
        <?php else: ?>
        <p style="font-size:.8rem;color:#94a3b8;margin:0;font-style:italic;">Sin respuesta del administrador.</p>
        <?php endif; ?>
        <?php else: ?>
        <p class="caja-empty">Este turno no tiene comentarios.</p>
        <?php endif; ?>
    </section>

    <!-- ── 7. Auditoría de cambios post-cierre ──────────────── -->
    <?php
    $tieneAuditoria = !empty($auditoria);
    $auditBorderColor = $tieneAuditoria ? '#f59e0b' : '#e2e8f0';
    $auditTitleColor  = $tieneAuditoria ? '#92400e' : '#94a3b8';
    ?>
    <section class="caja-card" style="border-left:3px solid <?= $auditBorderColor ?>;">
        <h2 class="caja-card__title" style="color:<?= $auditTitleColor ?>;">
            Auditoría de cambios
            <?php if ($tieneAuditoria): ?>
            <span style="font-size:.72rem;font-weight:700;background:#fef3c7;color:#92400e;padding:2px 9px;border-radius:5px;margin-left:.5rem;">
                <?= count($auditoria) ?> evento<?= count($auditoria) !== 1 ? 's' : '' ?>
            </span>
            <?php else: ?>
            <span style="font-size:.72rem;font-weight:500;color:#94a3b8;margin-left:.5rem;">Sin cambios registrados</span>
            <?php endif; ?>
        </h2>
        <?php if ($tieneAuditoria): ?>
        <table class="caja-table">
            <thead>
                <tr>
                    <th>Fecha</th><th>Acción</th><th>Campo</th>
                    <th>Antes</th><th>Después</th><th>Por</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($auditoria as $a):
                $accionColor = match(true) {
                    str_contains($a['accion'], 'ELIMINAD') => '#991b1b',
                    str_contains($a['accion'], 'CORREC')   => '#92400e',
                    default                                 => '#166534',
                };
            ?>
            <tr>
                <td style="font-size:.75rem;color:#64748b;white-space:nowrap;"><?= date('d/m/y H:i', strtotime($a['fecha'])) ?></td>
                <td>
                    <span style="background:<?= $accionColor ?>1a;color:<?= $accionColor ?>;
                                 padding:.15rem .5rem;border-radius:4px;font-weight:600;font-size:.72rem;">
                        <?= htmlspecialchars($a['accion']) ?>
                    </span>
                </td>
                <td style="font-size:.8rem;color:#475569;"><?= htmlspecialchars($a['campo_modificado']) ?></td>
                <td style="font-size:.8rem;color:#64748b;"><?= htmlspecialchars($a['valor_anterior']) ?></td>
                <td style="font-size:.8rem;color:#1e293b;font-weight:500;"><?= htmlspecialchars($a['valor_nuevo']) ?></td>
                <td style="font-size:.75rem;color:#64748b;"><?= htmlspecialchars($a['registrado_por'] ?? '—') ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p class="caja-empty">No se han registrado modificaciones post-cierre en este arqueo.</p>
        <?php endif; ?>
    </section>

</main>

<script src="<?= $basePath ?>/assets/js/session-guard.js"></script>
<?php if ($esAdmin): ?>
<script>
function abrirModalEliminar() {
    document.getElementById('modalEliminar').style.display = 'flex';
    document.getElementById('eliminarPassword').value = '';
    document.getElementById('eliminarMsg').style.display = 'none';
    setTimeout(() => document.getElementById('eliminarPassword').focus(), 50);
}

function cerrarModalEliminar() {
    document.getElementById('modalEliminar').style.display = 'none';
}

async function confirmarEliminar(sesionId) {
    const password = document.getElementById('eliminarPassword').value.trim();
    const msgEl    = document.getElementById('eliminarMsg');
    if (!password) { msgEl.textContent = 'Ingresa tu contraseña.'; msgEl.style.display = 'block'; return; }

    try {
        const r   = await fetch(`<?= $basePath ?>/caja/api/sesion/${sesionId}/eliminar`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ password }),
        });
        const res = await r.json();
        if (res.success) {
            window.location.href = `<?= $basePath ?>/caja`;
        } else {
            msgEl.textContent = res.message || 'Error al eliminar.';
            msgEl.style.display = 'block';
        }
    } catch {
        msgEl.textContent = 'Error de conexión.';
        msgEl.style.display = 'block';
    }
}
</script>
<?php endif; ?>
</body>
</html>
