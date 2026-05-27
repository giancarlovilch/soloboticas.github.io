<?php
/** @var array $sesion */ /** @var array $detalle */ /** @var array|false $venta */
/** @var array $gastos */ /** @var array $rectifs */ /** @var array $digitales */
/** @var float $digital_aprobado */ /** @var string|null $vendedor */
/** @var array $correccionesVenta */ /** @var float $sumCorrDelta */
$basePath  = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
$userName  = $userName ?? $_SESSION['user_name'] ?? 'Usuario';
$f2 = fn($v) => 'S/ ' . number_format((float)$v, 2, '.', ',');

// LO QUE ES = conteo físico + ajustes de cierre (rectificaciones)
$loQueEsFisico = (float)($detalle['monto_caja_exterior']        ?? 0)
               + (float)($detalle['monto_monedas']              ?? 0)
               + (float)($detalle['monto_billetes_caja']        ?? 0)
               + (float)($detalle['monto_billetes_caja_fuerte'] ?? 0)
               + (float)($detalle['monto_agente_bcp']           ?? 0);

$sum_rectifs = array_sum(array_column($rectifs ?? [], 'monto'));
$loQueEs     = round($loQueEsFisico + $sum_rectifs, 2);

// LO QUE SE DICE = base + ventas - gastos - digitales declarados ± ajustes esperado
$saldo_ini        = (float)($sesion['saldo_inicial']         ?? 0);
$correccionesVenta = $correccionesVenta ?? [];
$sumCorrDelta      = $sumCorrDelta ?? 0.0;
$total_ventas     = round((float)($detalle['total_ventas_sistema'] ?? ($venta['monto'] ?? 0)) + $sumCorrDelta, 2);
$total_gastos     = (float)($detalle['total_gastos_sistema'] ?? 0);
$digital_aprobado = $digital_aprobado ?? 0;

// AGREGAR un cobro/egreso omitido → RESTA del saldo esperado (era un pago digital/gasto real)
// QUITAR un cobro/egreso erróneo → SUMA al saldo esperado (ese movimiento no existió)
$sum_ajustes_esp = 0.0;
foreach ($ajustesEsperado ?? [] as $aj) {
    $sum_ajustes_esp += $aj['accion'] === 'AGREGAR' ? -(float)$aj['monto'] : (float)$aj['monto'];
}

$loQueSeDice = round($saldo_ini + $total_ventas - $total_gastos - $digital_aprobado + $sum_ajustes_esp, 2);

$diferencia = round($loQueEs - $loQueSeDice, 2);
$resultado  = abs($diferencia) < 0.01 ? 'CONSISTENTE' : ($diferencia > 0 ? 'SOBRANTE' : 'FALTANTE');
$clsDif     = abs($diferencia) < 0.01 ? 'dif-ok' : ($diferencia > 0 ? 'dif-sobrante' : 'dif-faltante');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arqueo de caja | Solo Boticas</title>
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
        <a href="<?= $basePath ?>/caja" class="caja-btn-back">← Volver a caja</a>
    </div>
</header>

<main class="caja-main">

    <!-- ── Encabezado del reporte ─────────────────────────── -->
    <section class="caja-card caja-card--info">
        <div class="caja-info-row">
            <strong>#<?= $sesion['id_sesion'] ?> — <?= htmlspecialchars($sesion['caja_desc']) ?> · <?= htmlspecialchars($sesion['local_desc']) ?></strong>
            <span><?= htmlspecialchars($sesion['turno_desc']) ?> · <?= date('d/m/Y', strtotime($sesion['fecha_operacion'])) ?></span>
            <span>Cajera: <?= htmlspecialchars($sesion['cajera_nombre']) ?><?= $vendedor ? ' · Vendedor/a: ' . htmlspecialchars($vendedor) : '' ?></span>
            <button onclick="abrirModalEliminar()"
                    style="margin-left:auto;background:transparent;border:1px solid #fca5a5;color:#ef4444;border-radius:6px;padding:4px 12px;font-size:0.75rem;font-weight:600;cursor:pointer;">
                🗑 Eliminar arqueo
            </button>
        </div>
    </section>

    <!-- ── Modal eliminar ────────────────────────────────── -->
    <div id="modalEliminar" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.55);z-index:500;align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:14px;padding:1.75rem;width:320px;max-width:90vw;box-shadow:0 20px 60px rgba(0,0,0,.22);">
            <h3 style="font-size:1rem;font-weight:700;color:#1e293b;margin-bottom:.35rem;">Eliminar cuadre #<?= $sesion['id_sesion'] ?></h3>
            <p style="font-size:0.8rem;color:#64748b;margin-bottom:1rem;">
                Esta acción borrará el cuadre y todos sus datos (activos, gastos, pagos digitales, rectificaciones).
                No se puede deshacer.
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

    <!-- ── Resultado del arqueo ──────────────────────────── -->
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
    </section>

    <!-- ── Cálculo detallado ──────────────────────────────── -->
    <section class="caja-card">
        <h2 class="caja-card__title">Detalle del arqueo</h2>
        <div class="caja-cuadre-grid">

            <!-- SALDO ESPERADO -->
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
                    <strong style="color:var(--cj-dark)"><?= $f2($total_gastos) ?></strong>
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
                    <strong style="color:<?= $colorAj ?>;">
                        <?= $f2(abs($sum_ajustes_esp)) ?>
                    </strong>
                </div>
                <?php endif; ?>
                <div class="caja-linea caja-linea--total">
                    <span>= Efectivo esperado</span>
                    <strong><?= $f2($loQueSeDice) ?></strong>
                </div>
            </div>

            <!-- ARQUEO DE CAJA -->
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

    <!-- ── Gastos detallados ───────────────────────────────── -->
    <section class="caja-card">
        <h2 class="caja-card__title">Egresos del turno (<?= count($gastos) ?>)</h2>
        <?php if (empty($gastos)): ?>
            <p class="caja-empty">Sin gastos registrados.</p>
        <?php else: ?>
        <table class="caja-table">
            <thead><tr><th>Tipo</th><th>Detalle</th><th>Comprobante</th><th class="text-right">Monto</th></tr></thead>
            <tbody>
            <?php
            $tipoPagoLabel = ['ADELANTO' => 'Adelanto', 'PAGO_TOTAL' => 'Pago total', 'DESCUENTO' => 'Descuento'];
            $tipoDocLabel  = ['BOLETA' => 'Boleta', 'FACTURA' => 'Factura', 'NOTA_DE_VENTA' => 'Nota de venta'];
            foreach ($gastos as $g):
                $modo = $g['modo_ref'] ?? '';
                if ($modo === 'PERSONAL') {
                    $detalle = htmlspecialchars($g['descripcion']) . ' · <em>' . ($tipoPagoLabel[$g['tipo_pago'] ?? ''] ?? '') . '</em>';
                } elseif ($modo === 'LOCAL') {
                    $detalle = htmlspecialchars($g['descripcion']);
                    if (!empty($g['concepto_desc'])) $detalle .= ' · ' . htmlspecialchars($g['concepto_desc']);
                } elseif ($modo === 'FACTURA') {
                    $detalle = $tipoDocLabel[$g['tipo_documento'] ?? ''] ?? htmlspecialchars($g['descripcion']);
                } else {
                    $detalle = htmlspecialchars($g['descripcion']);
                }
            ?>
                <tr>
                    <td><span class="caja-gasto-badge caja-gasto-badge--<?= $g['tipo_css'] ?? 'otro' ?>"><?= htmlspecialchars($g['etiqueta'] ?? '') ?></span></td>
                    <td style="font-size:.83rem;"><?= $detalle ?></td>
                    <td style="font-size:.78rem;color:#64748b;"><?= htmlspecialchars($g['comprobante'] ?? '—') ?></td>
                    <td class="text-right"><?= $f2($g['monto']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </section>

    <!-- ── Pagos digitales detallados ────────────────────────── -->
    <section class="caja-card">
        <h2 class="caja-card__title">Cobros electrónicos del turno (<?= count($digitales ?? []) ?>)</h2>
        <?php if (empty($digitales)): ?>
            <p class="caja-empty">Sin pagos digitales registrados.</p>
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
                'PENDIENTE' => ['label' => 'Pendiente',  'cls' => 'background:#fef3c7;color:#92400e;'],
                'APROBADO'  => ['label' => 'Aprobado',   'cls' => 'background:#d1fae5;color:#065f46;'],
                'RECHAZADO' => ['label' => 'Rechazado',  'cls' => 'background:#fee2e2;color:#991b1b;'],
            ];
            foreach ($digitales as $dg):
                $est = $estadoDigital[$dg['estado']] ?? ['label' => $dg['estado'], 'cls' => ''];
            ?>
                <tr>
                    <td style="font-size:0.78rem;color:#64748b;">
                        <?= date('H:i', strtotime($dg['fecha_movimiento'])) ?>
                    </td>
                    <td><strong><?= htmlspecialchars($dg['modo_desc']) ?></strong></td>
                    <td style="font-size:0.78rem;"><?= htmlspecialchars($dg['numero_operacion'] ?? '—') ?></td>
                    <td class="text-right"><?= $f2($dg['monto']) ?></td>
                    <td class="text-center">
                        <span style="font-size:0.72rem;font-weight:700;border-radius:4px;padding:2px 8px;<?= $est['cls'] ?>">
                            <?= $est['label'] ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </section>

    <!-- ── Vales SoloBank ───────────────────────────────────── -->
    <?php
    $valesSB = array_values(array_filter(
        $digitales ?? [],
        fn($d) => strtolower(trim($d['modo_desc'] ?? '')) === 'solobank'
    ));
    // Mostrar solo si hay vales asignados O hay vales disponibles para asignar
    if (!empty($valesSB) || !empty($soloBankVales)):
    ?>
    <section class="caja-card">
        <div class="caja-card__header-row">
            <div>
                <h2 class="caja-card__title">Vales SoloBank (<?= count($valesSB) ?>)</h2>
                <p class="caja-card__desc">
                    Cierres recibidos desde la app SoloBank.
                    <?= empty($soloBankVales) ? '' : 'Si la cajera olvidó asignar uno, puedes agregarlo aquí.' ?>
                </p>
            </div>
            <?php if (!empty($soloBankVales)): ?>
            <button class="caja-btn caja-btn--outline" onclick="toggleSeccion('seccionSB', this)">
                Asignar vale olvidado
            </button>
            <?php endif; ?>
        </div>

        <?php if (!empty($valesSB)): ?>
        <table class="caja-table" style="margin-bottom:.5rem;">
            <thead>
                <tr>
                    <th>Código</th>
                    <th class="text-center">Estado</th>
                    <th class="text-right">Monto</th>
                </tr>
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
                    <td style="font-size:.82rem;font-weight:600;font-family:monospace;">
                        <?= htmlspecialchars($v['numero_operacion'] ?? '—') ?>
                    </td>
                    <td class="text-center">
                        <span style="font-size:.72rem;font-weight:700;padding:2px 8px;border-radius:5px;<?= $estCls ?>">
                            <?= htmlspecialchars($v['estado']) ?>
                        </span>
                    </td>
                    <td class="text-right"><?= $f2($v['monto']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php elseif (!empty($soloBankVales)): ?>
            <p class="caja-empty">Sin vales asignados en este turno.</p>
        <?php endif; ?>

        <?php if (!empty($soloBankVales)): ?>
        <div id="seccionSB" hidden>
            <div style="background:#fffbeb;border:1px solid #fcd34d;border-radius:8px;padding:.75rem 1rem;margin-bottom:.75rem;">
                <p style="font-size:.78rem;color:#92400e;margin:0;">
                    ⚠ Al asignar un vale retroactivamente el saldo esperado del arqueo se actualizará.
                    Solo hazlo si el vale corresponde realmente a este turno.
                </p>
            </div>
            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:1rem;display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;">
                <select id="sbValeCodigo" class="caja-input" style="flex:1;min-width:200px;">
                    <option value="">— Selecciona un vale disponible —</option>
                    <?php foreach ($soloBankVales as $sv): ?>
                    <option value="<?= htmlspecialchars($sv['codigo']) ?>">
                        <?= htmlspecialchars($sv['codigo']) ?>
                        · <?= htmlspecialchars($sv['caja']) ?>
                        · <?= date('d/m', strtotime($sv['fecha'])) ?>
                        · S/ <?= number_format((float)$sv['total'], 2) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <button class="caja-btn caja-btn--secondary" onclick="asignarVale(<?= $sesion['id_sesion'] ?>)">
                    Asignar
                </button>
            </div>
            <div id="sbMsg" class="caja-alert" hidden style="margin-top:.5rem;"></div>
        </div>
        <?php endif; ?>
    </section>
    <?php endif; // fin: mostrar sección SoloBank ?>

    <!-- ── Rectificaciones ────────────────────────────────── -->
    <section class="caja-card">
        <div class="caja-card__header-row">
            <div>
                <h2 class="caja-card__title">Ajustes de arqueo</h2>
                <p class="caja-card__desc">Registra diferencias justificadas. El monto ajusta la base del siguiente turno automáticamente.</p>
            </div>
            <button class="caja-btn caja-btn--outline" onclick="toggleSeccion('seccionRect', this)">
                Modificar
            </button>
        </div>
        <div id="seccionRect" hidden>

        <?php if (!empty($rectifs)): ?>
        <table class="caja-table" style="margin-bottom:1rem;">
            <thead>
                <tr><th>Tipo</th><th>Descripción</th><th>Por</th><th class="text-right">Ajuste</th><th class="text-center">Quitar</th></tr>
            </thead>
            <tbody>
            <?php foreach ($rectifs as $r): ?>
                <?php
                    $signo   = (int)($r['signo'] ?? ($r['monto'] >= 0 ? 1 : -1));
                    $esSuma  = $signo > 0;
                    $prefijo = $esSuma ? '+' : '−';
                    $color   = $esSuma ? '#065f46' : '#991b1b';
                    $bg      = $esSuma ? '#d1fae5' : '#fee2e2';
                ?>
                <tr>
                    <td><?= htmlspecialchars($r['etiqueta'] ?? $r['tipo_rectificacion']) ?></td>
                    <td><?= htmlspecialchars($r['descripcion_contexto']) ?></td>
                    <td style="font-size:.78rem;color:#64748b;"><?= htmlspecialchars($r['registrado_por']) ?></td>
                    <td class="text-right">
                        <span style="font-weight:700;color:<?= $color ?>;background:<?= $bg ?>;padding:2px 8px;border-radius:5px;font-size:.85rem;">
                            <?= $prefijo ?> <?= $f2(abs((float)$r['monto'])) ?>
                        </span>
                    </td>
                    <td class="text-center">
                        <button onclick="abrirModalEliminarRect(<?= $r['id_rectificacion'] ?>)"
                            style="background:transparent;border:1px solid #fca5a5;color:#ef4444;border-radius:5px;padding:2px 8px;font-size:.72rem;font-weight:600;cursor:pointer;">
                            ✕
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <div class="caja-rect-form" style="grid-template-columns:100px 110px 1fr 140px auto;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:1rem;">
            <span style="font-size:.82rem;font-weight:600;color:#475569;align-self:center;">Efectivo / Saldo</span>
            <select id="rectAccion" class="caja-input" onchange="rectAccionChanged(this)">
                <option value="AGREGAR">Agregar</option>
                <option value="QUITAR">Quitar</option>
            </select>
            <input type="text" id="rectDesc" class="caja-input" placeholder="Descripción breve">
            <div class="caja-input-money">
                <span id="rectSigno" style="font-weight:700;min-width:14px;text-align:center;color:#059669;">+</span>
                <input type="number" id="rectMonto" class="caja-input caja-input--money" min="0.01" step="0.01" placeholder="0.00">
            </div>
            <button class="caja-btn caja-btn--secondary" onclick="submitRectificacion(<?= $sesion['id_sesion'] ?>)">
                Aplicar
            </button>
        </div>
        <div id="rectMsg" class="caja-alert" hidden></div>
        </div><!-- /seccionRect -->
    </section>

    <!-- Modal eliminar rectificación -->
    <div id="modalEliminarRect" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.55);z-index:500;align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:14px;padding:1.75rem;width:300px;max-width:90vw;box-shadow:0 20px 60px rgba(0,0,0,.22);">
            <h3 style="font-size:1rem;font-weight:700;color:#1e293b;margin-bottom:.35rem;">Eliminar ajuste</h3>
            <p style="font-size:0.8rem;color:#64748b;margin-bottom:1rem;">El saldo del siguiente turno será revertido. Confirma con tu contraseña.</p>
            <input type="password" id="rectPassword"
                   style="width:100%;padding:.55rem .75rem;border:1.5px solid #e2e8f0;border-radius:8px;font-size:.85rem;margin-bottom:.75rem;box-sizing:border-box;"
                   placeholder="Contraseña de administrador">
            <div id="rectElimMsg" style="font-size:.78rem;color:#991b1b;margin-bottom:.5rem;display:none;"></div>
            <div style="display:flex;gap:.6rem;justify-content:flex-end;">
                <button onclick="cerrarModalEliminarRect()"
                    style="background:#f1f5f9;border:none;border-radius:7px;padding:.5rem 1rem;font-size:.82rem;cursor:pointer;color:#475569;">
                    Cancelar
                </button>
                <button id="btnConfirmarElimRect"
                    style="background:#dc2626;border:none;border-radius:7px;padding:.5rem 1rem;font-size:.82rem;font-weight:700;color:#fff;cursor:pointer;">
                    Eliminar
                </button>
            </div>
        </div>
    </div>

    <!-- ── Ajustes al saldo esperado ────────────────────── -->
    <section class="caja-card">
        <div class="caja-card__header-row">
            <div>
                <h2 class="caja-card__title">Correcciones de gastos</h2>
                <p class="caja-card__desc">
                    Registra cobros o pagos omitidos en el turno.
                    <strong>Agregar</strong> suma al gasto esperado · <strong>Quitar</strong> resta del gasto esperado.
                </p>
            </div>
            <button class="caja-btn caja-btn--outline" onclick="toggleSeccion('seccionAjuste', this)">
                Modificar
            </button>
        </div>
        <div id="seccionAjuste" hidden>

        <?php if (!empty($ajustesEsperado)):
            $tipoLabel = ['COBRO'=>'Cobro elec.','PERSONAL'=>'Personal','LOCAL'=>'Local','COMPRA'=>'Compra','OTRO'=>'Otro'];
            $tipoColor = ['COBRO'=>'#3b82f6','PERSONAL'=>'#7c3aed','LOCAL'=>'#0e7490','COMPRA'=>'#0e7490','OTRO'=>'#64748b'];
            $tipoBg    = ['COBRO'=>'#eff6ff','PERSONAL'=>'#ede9fe','LOCAL'=>'#f0fdfe','COMPRA'=>'#f0fdfe','OTRO'=>'#f1f5f9'];
        ?>
        <table class="caja-table" style="margin-bottom:1.25rem;">
            <thead>
                <tr><th>Tipo</th><th>Detalle</th><th>Acción</th><th class="text-right">Monto</th><th class="text-center">✕</th></tr>
            </thead>
            <tbody>
            <?php foreach ($ajustesEsperado as $aj):
                $esAgregar = $aj['accion'] === 'AGREGAR';
                $t = $aj['tipo'] ?? 'COBRO';

                if ($t === 'COBRO')    $detalle = htmlspecialchars($aj['modo_desc'] ?? '');
                elseif ($t === 'PERSONAL') $detalle = htmlspecialchars($aj['staff_desc'] ?? '') . ($aj['tipo_pago'] ? ' · ' . (['ADELANTO'=>'Adelanto','PAGO_TOTAL'=>'Pago total','DESCUENTO'=>'Descuento'][$aj['tipo_pago']] ?? $aj['tipo_pago']) : '');
                elseif ($t === 'LOCAL')    $detalle = htmlspecialchars($aj['local_desc'] ?? '') . ($aj['concepto_desc'] ? ' · ' . htmlspecialchars($aj['concepto_desc']) : '');
                elseif ($t === 'COMPRA')   $detalle = ['BOLETA'=>'Boleta','FACTURA'=>'Factura','NOTA_DE_VENTA'=>'Nota de venta'][$aj['tipo_documento'] ?? ''] ?? '';
                else                       $detalle = htmlspecialchars($aj['descripcion'] ?? '');

                $notaDesc = ($t !== 'OTRO' && !empty($aj['descripcion'])) ? '<br><span style="font-size:.73rem;color:#94a3b8;">' . htmlspecialchars($aj['descripcion']) . '</span>' : '';
            ?>
                <tr>
                    <td>
                        <span style="font-size:.68rem;font-weight:700;padding:2px 7px;border-radius:5px;
                            background:<?= $tipoBg[$t] ?? '#f1f5f9' ?>;color:<?= $tipoColor[$t] ?? '#64748b' ?>">
                            <?= $tipoLabel[$t] ?? $t ?>
                        </span>
                    </td>
                    <td style="font-size:.82rem;"><?= $detalle ?><?= $notaDesc ?></td>
                    <td>
                        <?php
                        // AGREGAR resta del esperado (efecto negativo = rojo)
                        // QUITAR suma al esperado (efecto positivo = verde)
                        $efectoPositivo = !$esAgregar;
                        ?>
                        <span style="font-size:.72rem;font-weight:700;padding:2px 7px;border-radius:5px;
                            background:<?= $efectoPositivo ? '#d1fae5' : '#fee2e2' ?>;
                            color:<?= $efectoPositivo ? '#065f46' : '#991b1b' ?>">
                            <?= $esAgregar ? '− Agregar' : '+ Quitar' ?>
                        </span>
                    </td>
                    <td class="text-right" style="font-weight:700;color:<?= $efectoPositivo ? '#065f46' : '#991b1b' ?>">
                        <?= $esAgregar ? '−' : '+' ?> <?= $f2($aj['monto']) ?>
                    </td>
                    <td class="text-center">
                        <button onclick="abrirModalEliminarAjuste(<?= $aj['id_ajuste'] ?>)"
                            style="background:transparent;border:1px solid #fca5a5;color:#ef4444;border-radius:5px;padding:2px 8px;font-size:.72rem;font-weight:600;cursor:pointer;">✕</button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <!-- Formulario de corrección -->
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:1rem;">
            <!-- Fila 1: tipo + acción -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;margin-bottom:.5rem;">
                <div>
                    <label style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#64748b;display:block;margin-bottom:.25rem;">Tipo de corrección</label>
                    <select id="ajTipo" class="caja-input" onchange="ajTipoChanged(this)">
                        <option value="COBRO">Cobro electrónico (Yape/POS)</option>
                        <option value="PERSONAL">Pago de Personal</option>
                        <option value="LOCAL">Pago de Local</option>
                        <option value="COMPRA">Pago de Compras</option>
                        <option value="DEPOSITO">Depósito a KGyR</option>
                        <option value="OTRO">Otros pagos</option>
                    </select>
                </div>
                <div>
                    <label style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#64748b;display:block;margin-bottom:.25rem;">Acción</label>
                    <select id="ajAccion" class="caja-input" onchange="ajAccionChanged(this)">
                        <option value="AGREGAR">Agregar</option>
                        <option value="QUITAR">Quitar</option>
                    </select>
                </div>
            </div>
            <!-- Fila 2: campos dinámicos + monto + aplicar -->
            <div style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;">
                <div id="ajMiddle" style="display:flex;gap:.5rem;align-items:center;flex:1;min-width:0;flex-wrap:wrap;"></div>
                <div class="caja-input-money" style="max-width:130px;">
                    <span id="ajSigno" style="font-weight:700;color:#059669;">+</span>
                    <input type="number" id="ajMonto" class="caja-input caja-input--money" min="0.01" step="0.01" placeholder="0.00">
                </div>
                <button class="caja-btn caja-btn--secondary" onclick="addAjusteEsperado(<?= $sesion['id_sesion'] ?>)">Aplicar</button>
            </div>
        </div>
        <div id="ajMsg" class="caja-alert" hidden style="margin-top:.5rem;"></div>
        </div><!-- /seccionAjuste -->
    </section>

    <!-- Modal eliminar ajuste esperado -->
    <div id="modalEliminarAjuste" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.55);z-index:500;align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:14px;padding:1.75rem;width:300px;max-width:90vw;box-shadow:0 20px 60px rgba(0,0,0,.22);">
            <h3 style="font-size:1rem;font-weight:700;color:#1e293b;margin-bottom:.35rem;">Eliminar ajuste</h3>
            <p style="font-size:.8rem;color:#64748b;margin-bottom:1rem;">Confirma con tu contraseña de administrador.</p>
            <input type="password" id="ajPassword"
                   style="width:100%;padding:.55rem .75rem;border:1.5px solid #e2e8f0;border-radius:8px;font-size:.85rem;margin-bottom:.75rem;box-sizing:border-box;"
                   placeholder="Contraseña">
            <div id="ajElimMsg" style="font-size:.78rem;color:#991b1b;margin-bottom:.5rem;display:none;"></div>
            <div style="display:flex;gap:.6rem;justify-content:flex-end;">
                <button onclick="cerrarModalEliminarAjuste()"
                    style="background:#f1f5f9;border:none;border-radius:7px;padding:.5rem 1rem;font-size:.82rem;cursor:pointer;color:#475569;">
                    Cancelar
                </button>
                <button id="btnConfirmarElimAjuste"
                    style="background:#dc2626;border:none;border-radius:7px;padding:.5rem 1rem;font-size:.82rem;font-weight:700;color:#fff;cursor:pointer;">
                    Eliminar
                </button>
            </div>
        </div>
    </div>

    <!-- ── Corrección de ventas ──────────────────────────── -->
    <section class="caja-card">
        <div class="caja-card__header-row">
            <div>
                <h2 class="caja-card__title">Corrección de ventas del turno</h2>
                <p class="caja-card__desc">
                    Si el total de ventas fue ingresado incorrectamente, registra el valor correcto aquí.
                    El historial queda guardado para trazabilidad.
                </p>
            </div>
            <button class="caja-btn caja-btn--outline" onclick="toggleSeccion('seccionCorrVenta', this)">
                Modificar
            </button>
        </div>

        <!-- Valor actual -->
        <div style="display:flex;gap:1rem;align-items:center;flex-wrap:wrap;margin-bottom:.25rem;">
            <span style="font-size:.82rem;color:#64748b;">Ventas del turno (valor actual):</span>
            <strong id="ventaActualLabel" style="font-size:1rem;font-variant-numeric:tabular-nums;">
                <?= $f2($total_ventas) ?>
            </strong>
            <?php if (!empty($correccionesVenta)): ?>
            <span style="font-size:.72rem;background:#fef3c7;color:#92400e;padding:2px 8px;border-radius:5px;font-weight:700;">
                ✏ <?= count($correccionesVenta) ?> corrección<?= count($correccionesVenta) !== 1 ? 'es' : '' ?>
            </span>
            <?php endif; ?>
        </div>

        <div id="seccionCorrVenta" hidden>

        <?php if (!empty($correccionesVenta)): ?>
        <table class="caja-table" style="margin-bottom:1rem;">
            <thead>
                <tr>
                    <th>Antes</th>
                    <th>Después</th>
                    <th class="text-right">Δ</th>
                    <th>Motivo</th>
                    <th>Por</th>
                    <th>Hora</th>
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

        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:1rem;">
            <p style="font-size:.75rem;font-weight:600;color:#475569;margin-bottom:.6rem;">
                Ingresa el nuevo total de ventas del turno:
            </p>
            <div style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;">
                <div class="caja-input-money" style="max-width:160px;">
                    <span style="font-weight:700;color:#1e293b;">S/</span>
                    <input type="number" id="corrVentaMonto" class="caja-input caja-input--money"
                           min="0" step="0.01" placeholder="0.00"
                           value="<?= number_format($total_ventas, 2, '.', '') ?>">
                </div>
                <input type="text" id="corrVentaMotivo" class="caja-input"
                       style="flex:1;min-width:160px;" placeholder="Motivo (opcional)" maxlength="300">
                <button class="caja-btn caja-btn--secondary" onclick="submitCorreccionVenta(<?= $sesion['id_sesion'] ?>)">
                    Aplicar corrección
                </button>
            </div>
        </div>
        <div id="corrVentaMsg" class="caja-alert" hidden style="margin-top:.5rem;"></div>
        </div><!-- /seccionCorrVenta -->
    </section>

    <!-- ── Comentario del turno ─────────────────────────── -->
    <section class="caja-card">
        <h2 class="caja-card__title">Comentario del turno</h2>

        <?php if (!empty($sesion['comentario_cajera'])): ?>
        <div style="padding:.85rem 1rem;background:#fef9c3;border-left:4px solid #ca8a04;border-radius:0 8px 8px 0;margin-bottom:1rem;">
            <p style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#92400e;margin:0 0 .35rem;">
                Comentario de la cajera
            </p>
            <p style="color:#1e293b;font-size:.86rem;margin:0;white-space:pre-wrap;"><?= htmlspecialchars($sesion['comentario_cajera']) ?></p>
        </div>

        <?php if (!empty($sesion['respuesta_admin'])): ?>
        <div style="padding:.85rem 1rem;background:#eff6ff;border-left:4px solid #3b82f6;border-radius:0 8px 8px 0;">
            <p style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#3b82f6;margin:0 0 .35rem;">
                Respuesta del administrador
            </p>
            <p style="color:#1e293b;font-size:.86rem;margin:0;white-space:pre-wrap;"><?= htmlspecialchars($sesion['respuesta_admin']) ?></p>
        </div>
        <?php else: ?>
        <div>
            <p style="font-size:.82rem;font-weight:600;color:#334155;margin:.25rem 0 .5rem;">Respuesta del administrador</p>
            <textarea id="adminRespuesta" class="caja-input"
                      style="width:100%;resize:vertical;min-height:80px;font-family:inherit;box-sizing:border-box;"
                      placeholder="Escribe la respuesta a la cajera..."></textarea>
            <div style="margin-top:.6rem;display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;">
                <input type="password" id="adminRespPassword" class="caja-input"
                       style="max-width:210px;" placeholder="Contraseña de administrador">
                <button class="caja-btn caja-btn--secondary"
                        onclick="guardarRespuesta(<?= $sesion['id_sesion'] ?>)">
                    Guardar respuesta
                </button>
                <span id="respMsg" style="font-size:.82rem;display:none;"></span>
            </div>
        </div>
        <?php endif; ?>

        <?php else: ?>
        <p style="color:#94a3b8;font-size:.85rem;margin:0;">Este turno no tiene comentarios.</p>
        <?php endif; ?>
    </section>

    <!-- ── Transferencias de saldo del día ────────────────── -->
    <?php if (!empty($transferencias)): ?>
    <section class="caja-card">
        <h2 class="caja-card__title">Transferencias de saldo del día</h2>
        <table style="width:100%;border-collapse:collapse;font-size:.82rem;">
            <thead>
                <tr>
                    <?php foreach (['Operación','Monto','Comprobante','Confirmado por','Hora'] as $th): ?>
                    <th style="background:#f8fafc;padding:.45rem .8rem;font-size:.63rem;font-weight:700;
                        text-transform:uppercase;letter-spacing:.06em;color:#64748b;
                        border-bottom:2px solid #e2e8f0;text-align:left;"><?= $th ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($transferencias as $tr):
                $esEnvio = ((int)$tr['caja_origen_id'] === (int)$sesion['caja_id']);
            ?>
            <tr>
                <td style="padding:.55rem .8rem;border-bottom:1px solid #f1f5f9;">
                    <?php if ($esEnvio): ?>
                        <span style="color:#dc2626;font-weight:600;">↑ Enviado</span>
                        <span style="font-size:.75rem;color:#64748b;display:block;">hacia <?= htmlspecialchars($tr['caja_destino_desc']) ?></span>
                    <?php else: ?>
                        <span style="color:#059669;font-weight:600;">↓ Recibido</span>
                        <span style="font-size:.75rem;color:#64748b;display:block;">desde <?= htmlspecialchars($tr['caja_origen_desc']) ?></span>
                    <?php endif; ?>
                </td>
                <td style="padding:.55rem .8rem;border-bottom:1px solid #f1f5f9;font-weight:700;
                    font-variant-numeric:tabular-nums;color:<?= $esEnvio ? '#dc2626' : '#059669' ?>;">
                    <?= $esEnvio ? '−' : '+' ?> S/ <?= number_format($tr['monto'], 2) ?>
                </td>
                <td style="padding:.55rem .8rem;border-bottom:1px solid #f1f5f9;font-size:.78rem;color:#475569;">
                    <?= htmlspecialchars($tr['numero_comprobante'] ?? '—') ?>
                </td>
                <td style="padding:.55rem .8rem;border-bottom:1px solid #f1f5f9;font-size:.75rem;">
                    <?= htmlspecialchars($tr['confirmador_nombre'] ?? '—') ?>
                </td>
                <td style="padding:.55rem .8rem;border-bottom:1px solid #f1f5f9;font-size:.72rem;color:#64748b;white-space:nowrap;">
                    <?= date('H:i', strtotime($tr['confirmed_at'])) ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
    <?php endif; ?>

</main>

<script src="<?= $basePath ?>/assets/js/session-guard.js"></script>
<script>
const BASE        = '<?= $basePath ?>';
const TIPOS_RECT  = <?= json_encode($tiposRect  ?? []) ?>;
const AJ_MODOS    = <?= json_encode($modos      ?? []) ?>;
const AJ_STAFF    = <?= json_encode($staff      ?? []) ?>;
const AJ_LOCALES  = <?= json_encode($locales    ?? []) ?>;
const AJ_CONCEPTOS= <?= json_encode($conceptos  ?? []) ?>;

function toggleSeccion(id, btn) {
    const el = document.getElementById(id);
    if (!el) return;
    const abierto = !el.hidden;
    el.hidden = abierto;
    btn.textContent = abierto ? 'Modificar' : 'Cerrar';
    btn.style.background = abierto ? '' : '#e2e8f0';
}

function rectAccionChanged(sel) {
    const esAgregar = sel.value === 'AGREGAR';
    const signoEl   = document.getElementById('rectSigno');
    if (signoEl) {
        signoEl.textContent = esAgregar ? '+' : '−';
        signoEl.style.color = esAgregar ? '#059669' : '#dc2626';
    }
}

async function submitRectificacion(sesionId) {
    const accion     = document.getElementById('rectAccion')?.value;
    const tipo       = (TIPOS_RECT || []).find(t => accion === 'AGREGAR' ? t.signo > 0 : t.signo < 0);
    const tipoRectId = tipo?.id_tipo_rect;
    const desc       = document.getElementById('rectDesc').value.trim();
    const monto      = parseFloat(document.getElementById('rectMonto').value);
    const msg        = document.getElementById('rectMsg');

    if (!tipoRectId || !desc || isNaN(monto) || monto <= 0) {
        showAlert(msg, 'Completa descripción y un monto positivo.', 'error'); return;
    }

    try {
        const r   = await fetch(`${BASE}/caja/api/reporte/${sesionId}/rectificar`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ tipo_rect_id: tipoRectId, descripcion: desc, monto }),
        });
        const res = await r.json();
        if (res.success) { showAlert(msg, '✓ ' + res.message, 'ok'); setTimeout(() => location.reload(), 1200); }
        else showAlert(msg, res.message, 'error');
    } catch { showAlert(msg, 'Error de conexión.', 'error'); }
}

function showAlert(el, txt, type) {
    el.textContent = txt;
    el.className   = `caja-alert caja-alert--${type}`;
    el.hidden      = false;
}

// ── Eliminar rectificación ────────────────────────────
let _rectIdAEliminar = null;

function abrirModalEliminarRect(rectId) {
    _rectIdAEliminar = rectId;
    document.getElementById('rectPassword').value = '';
    document.getElementById('rectElimMsg').style.display = 'none';
    document.getElementById('modalEliminarRect').style.display = 'flex';
    setTimeout(() => document.getElementById('rectPassword').focus(), 50);
}

function cerrarModalEliminarRect() {
    document.getElementById('modalEliminarRect').style.display = 'none';
    _rectIdAEliminar = null;
}

document.getElementById('btnConfirmarElimRect')?.addEventListener('click', async function() {
    const password = document.getElementById('rectPassword').value.trim();
    const msgEl    = document.getElementById('rectElimMsg');
    if (!password) { msgEl.textContent = 'Ingresa tu contraseña.'; msgEl.style.display = 'block'; return; }

    try {
        const r   = await fetch(`${BASE}/caja/api/rectificacion/${_rectIdAEliminar}/eliminar`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ password }),
        });
        const res = await r.json();
        if (res.success) { location.reload(); }
        else { msgEl.textContent = res.message || 'Error al eliminar.'; msgEl.style.display = 'block'; }
    } catch { msgEl.textContent = 'Error de conexión.'; msgEl.style.display = 'block'; }
});

// ── Correcciones al saldo esperado ────────────────────
function ajTipoChanged(sel) {
    const middle = document.getElementById('ajMiddle');
    const tipo   = sel.value;
    let html = '';

    if (tipo === 'COBRO') {
        const opts = AJ_MODOS.map(m => `<option value="${m.id_modo}">${m.descripcion}</option>`).join('');
        html = `<select id="ajRef" class="caja-input" style="max-width:140px"><option value="">— Modo —</option>${opts}</select>
                <input type="text" id="ajDesc" class="caja-input" style="flex:1;min-width:120px" placeholder="Descripción (opc.)" maxlength="200">`;
    } else if (tipo === 'PERSONAL') {
        const opts = AJ_STAFF.map(s => `<option value="${s.id}">${s.nombre_completo}</option>`).join('');
        html = `<select id="ajRef" class="caja-input" style="flex:1"><option value="">— Personal —</option>${opts}</select>
                <select id="ajTipoPago" class="caja-input" style="max-width:130px">
                    <option value="PAGO_TOTAL">Pago total</option><option value="ADELANTO">Adelanto</option><option value="DESCUENTO">Descuento</option>
                </select>
                <input type="text" id="ajDesc" class="caja-input" style="max-width:140px" placeholder="Descripción (opc.)" maxlength="200">`;
    } else if (tipo === 'LOCAL') {
        const optsL = AJ_LOCALES.map(l => `<option value="${l.id}">${l.descripcion}</option>`).join('');
        const optsC = AJ_CONCEPTOS.map(c => `<option value="${c.id}">${c.descripcion}</option>`).join('');
        html = `<select id="ajRef" class="caja-input" style="max-width:120px"><option value="">— Local —</option>${optsL}</select>
                <select id="ajRef2" class="caja-input" style="flex:1"><option value="">— Concepto —</option>${optsC}</select>
                <input type="text" id="ajDesc" class="caja-input" style="max-width:130px" placeholder="N° comprobante" maxlength="100">`;
    } else if (tipo === 'COMPRA') {
        html = `<select id="ajTipoDoc" class="caja-input" style="max-width:150px">
                    <option value="BOLETA">Boleta</option><option value="FACTURA">Factura</option><option value="NOTA_DE_VENTA">Nota de venta</option>
                </select>
                <input type="text" id="ajDesc" class="caja-input" style="flex:1" placeholder="N° comprobante" maxlength="100">`;
    } else if (tipo === 'DEPOSITO') {
        html = `<input type="text" id="ajDesc" class="caja-input" style="flex:1" placeholder="N° comprobante o Referencia" maxlength="200">`;
    } else {
        html = `<input type="text" id="ajDesc" class="caja-input" style="flex:1" placeholder="Descripción del pago" maxlength="200">`;
    }

    middle.innerHTML = html;
}

function ajAccionChanged(sel) {
    const signoEl = document.getElementById('ajSigno');
    if (!signoEl) return;
    const esAgregar = sel.value === 'AGREGAR';
    signoEl.textContent = esAgregar ? '+' : '−';
    signoEl.style.color = esAgregar ? '#059669' : '#dc2626';
}

async function addAjusteEsperado(sesionId) {
    const tipo   = document.getElementById('ajTipo')?.value;
    const accion = document.getElementById('ajAccion')?.value;
    const monto  = parseFloat(document.getElementById('ajMonto')?.value);
    const desc   = document.getElementById('ajDesc')?.value?.trim() || '';
    const msg    = document.getElementById('ajMsg');

    if (isNaN(monto) || monto <= 0) { showAlert(msg, 'Ingresa un monto válido.', 'error'); return; }

    const payload = { tipo, accion, descripcion: desc, monto };
    if (tipo === 'COBRO')    payload.modo_id        = parseInt(document.getElementById('ajRef')?.value) || null;
    if (tipo === 'PERSONAL') { payload.ref_id = parseInt(document.getElementById('ajRef')?.value) || null; payload.tipo_pago = document.getElementById('ajTipoPago')?.value; }
    if (tipo === 'LOCAL')    { payload.ref_id = parseInt(document.getElementById('ajRef')?.value) || null; payload.ref2_id = parseInt(document.getElementById('ajRef2')?.value) || null; }
    if (tipo === 'COMPRA')   payload.tipo_documento = document.getElementById('ajTipoDoc')?.value;

    try {
        const r   = await fetch(`${BASE}/caja/api/sesion/${sesionId}/ajuste-esperado`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body:   JSON.stringify(payload),
        });
        const res = await r.json();
        if (res.success) { showAlert(msg, '✓ ' + res.message, 'ok'); setTimeout(() => location.reload(), 900); }
        else showAlert(msg, res.message, 'error');
    } catch { showAlert(msg, 'Error de conexión.', 'error'); }
}

// Inicializar el middle con COBRO por defecto
document.addEventListener('DOMContentLoaded', () => {
    const sel = document.getElementById('ajTipo');
    if (sel) ajTipoChanged(sel);
});

let _ajusteIdAEliminar = null;

function abrirModalEliminarAjuste(ajusteId) {
    _ajusteIdAEliminar = ajusteId;
    document.getElementById('ajPassword').value = '';
    document.getElementById('ajElimMsg').style.display = 'none';
    document.getElementById('modalEliminarAjuste').style.display = 'flex';
    setTimeout(() => document.getElementById('ajPassword').focus(), 50);
}

function cerrarModalEliminarAjuste() {
    document.getElementById('modalEliminarAjuste').style.display = 'none';
    _ajusteIdAEliminar = null;
}

document.getElementById('btnConfirmarElimAjuste')?.addEventListener('click', async function() {
    const password = document.getElementById('ajPassword').value.trim();
    const msgEl    = document.getElementById('ajElimMsg');
    if (!password) { msgEl.textContent = 'Ingresa tu contraseña.'; msgEl.style.display = 'block'; return; }
    try {
        const r   = await fetch(`${BASE}/caja/api/ajuste-esperado/${_ajusteIdAEliminar}/eliminar`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body:   JSON.stringify({ password }),
        });
        const res = await r.json();
        if (res.success) location.reload();
        else { msgEl.textContent = res.message || 'Error.'; msgEl.style.display = 'block'; }
    } catch { msgEl.textContent = 'Error de conexión.'; msgEl.style.display = 'block'; }
});

// ── Respuesta del administrador ───────────────────────
async function guardarRespuesta(sesionId) {
    const respuesta = document.getElementById('adminRespuesta')?.value?.trim();
    const password  = document.getElementById('adminRespPassword')?.value?.trim();
    const msgEl     = document.getElementById('respMsg');

    if (!respuesta) { msgEl.textContent = 'Escribe la respuesta.'; msgEl.style.color = '#dc2626'; msgEl.style.display = 'inline'; return; }
    if (!password)  { msgEl.textContent = 'Ingresa tu contraseña.'; msgEl.style.color = '#dc2626'; msgEl.style.display = 'inline'; return; }

    try {
        const r   = await fetch(`${BASE}/caja/api/sesion/${sesionId}/respuesta`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body:   JSON.stringify({ respuesta, password }),
        });
        const res = await r.json();
        if (res.success) {
            msgEl.textContent = '✓ Respuesta guardada.'; msgEl.style.color = '#059669'; msgEl.style.display = 'inline';
            setTimeout(() => location.reload(), 1200);
        } else {
            msgEl.textContent = res.message || 'Error.'; msgEl.style.color = '#dc2626'; msgEl.style.display = 'inline';
        }
    } catch { msgEl.textContent = 'Error de conexión.'; msgEl.style.color = '#dc2626'; msgEl.style.display = 'inline'; }
}

// ── Corrección de ventas ─────────────────────────────
async function submitCorreccionVenta(sesionId) {
    const monto  = parseFloat(document.getElementById('corrVentaMonto')?.value);
    const motivo = document.getElementById('corrVentaMotivo')?.value?.trim() || '';
    const msg    = document.getElementById('corrVentaMsg');

    if (isNaN(monto) || monto < 0) { showAlert(msg, 'Ingresa un monto válido (≥ 0).', 'error'); return; }

    try {
        const r   = await fetch(`${BASE}/caja/api/sesion/${sesionId}/corregir-venta`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body:   JSON.stringify({ monto_nuevo: monto, motivo }),
        });
        const res = await r.json();
        if (res.success) { showAlert(msg, '✓ ' + res.message, 'ok'); setTimeout(() => location.reload(), 900); }
        else showAlert(msg, res.message, 'error');
    } catch { showAlert(msg, 'Error de conexión.', 'error'); }
}

// ── Asignar vale SoloBank olvidado ────────────────────
async function asignarVale(sesionId) {
    const codigo = document.getElementById('sbValeCodigo')?.value?.trim();
    const msg    = document.getElementById('sbMsg');
    if (!codigo) { showAlert(msg, 'Selecciona un vale de la lista.', 'error'); return; }

    try {
        const r   = await fetch(`${BASE}/caja/api/sesion/${sesionId}/solobank`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ codigo }),
        });
        const res = await r.json();
        if (res.success) {
            const monto = parseFloat(res.data?.monto ?? 0).toFixed(2);
            showAlert(msg, `✓ Vale asignado correctamente · S/ ${monto}`, 'ok');
            setTimeout(() => location.reload(), 1200);
        } else {
            showAlert(msg, res.message || 'Error al asignar el vale.', 'error');
        }
    } catch {
        showAlert(msg, 'Error de conexión.', 'error');
    }
}

// ── Eliminar cuadre ───────────────────────────────────
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

    if (!password) {
        msgEl.textContent = 'Ingresa tu contraseña.';
        msgEl.style.display = 'block'; return;
    }

    try {
        const r   = await fetch(`${BASE}/caja/api/sesion/${sesionId}/eliminar`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ password }),
        });
        const res = await r.json();
        if (res.success) {
            window.location.href = `${BASE}/caja`;
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
</body>
</html>
