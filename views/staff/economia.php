<?php
$basePath  = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
$meses     = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
[$anioF, $nmesF] = explode('-', $filtroMes);
$mesLabel  = $meses[(int)$nmesF - 1] . ' ' . $anioF;
$diasLabel = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
$turnoLabel= [1 => '☀️ Mañana', 2 => '🌙 Tarde'];

$tipoPagoInfo = [
    'PAGO_TOTAL' => ['label' => 'Pago total', 'bg' => '#d1fae5', 'color' => '#065f46'],
    'ADELANTO'   => ['label' => 'Adelanto',   'bg' => '#fef3c7', 'color' => '#92400e'],
    'DESCUENTO'  => ['label' => 'Descuento',  'bg' => '#fee2e2', 'color' => '#991b1b'],
];
$estadoInfo = [
    'PAGADO'                  => ['label' => 'Pagado',     'bg' => '#d1fae5', 'color' => '#065f46'],
    'CONFIRMADO_BENEFICIARIO' => ['label' => 'Confirmado', 'bg' => '#d1fae5', 'color' => '#065f46'],
    'APROBADO'                => ['label' => 'Aprobado',   'bg' => '#ede9fe', 'color' => '#5b21b6'],
    'PENDIENTE'               => ['label' => 'Pendiente',  'bg' => '#fef3c7', 'color' => '#92400e'],
    'OBSERVADO'               => ['label' => 'Observado',  'bg' => '#ffedd5', 'color' => '#9a3412'],
    'RECHAZADO'               => ['label' => 'Rechazado',  'bg' => '#fee2e2', 'color' => '#991b1b'],
];

$f2 = fn($v) => 'S/ ' . number_format((float)$v, 2, '.', ',');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Economía | Solo Boticas</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/staff.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .eco-wrap  { max-width:860px;margin:0 auto;padding:1.25rem 1rem 3rem; }

        /* Navegación mensual */
        .eco-nav   { display:flex;align-items:center;gap:.5rem;margin-bottom:1.25rem;flex-wrap:wrap; }
        .eco-nav a { padding:.38rem .9rem;border-radius:8px;font-size:.82rem;font-weight:600;
                     border:1.5px solid #fbcfe8;background:#fff0f6;color:#be185d;text-decoration:none; }
        .eco-mes   { font-size:.95rem;font-weight:700;color:#9d174d;padding:0 .5rem; }

        /* KPIs */
        .eco-kpis  { display:grid;grid-template-columns:repeat(4,1fr);gap:.65rem;margin-bottom:1.5rem; }
        .eco-kpi   { background:#fff;border:1px solid #fbcfe8;border-radius:10px;padding:.75rem 1rem;text-align:center; }
        .eco-kpi__num   { font-size:1.25rem;font-weight:800;color:#9d174d; }
        .eco-kpi__label { font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#be185d;margin-top:2px; }

        /* Secciones */
        .eco-sec-title { font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;
                         color:#be185d;margin:1.5rem 0 .65rem; }

        /* Tabla */
        .eco-table-wrap { overflow-x:auto; }
        .eco-table { width:100%;border-collapse:collapse;font-size:.80rem;background:#fff;
                     border-radius:10px;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.06); }
        .eco-table th { background:#fff0f6;padding:.5rem .75rem;font-size:.63rem;font-weight:700;
                        text-transform:uppercase;letter-spacing:.06em;color:#be185d;
                        border-bottom:2px solid #fbcfe8;white-space:nowrap; }
        .eco-table td { padding:.55rem .75rem;border-bottom:1px solid #fdf2f8;vertical-align:middle; }
        .eco-table tr:last-child td { border-bottom:none; }
        .eco-table tr:hover td { background:#fff8fc; }
        .eco-table tfoot td { background:#fff0f6;font-weight:700;font-size:.82rem;color:#9d174d;
                              border-top:2px solid #fbcfe8; }

        .eco-badge { display:inline-block;font-size:.67rem;font-weight:700;padding:2px 8px;border-radius:20px;white-space:nowrap; }
        .eco-sub   { font-size:.68rem;color:#94a3b8;display:block; }
        .eco-monto { font-size:.95rem;font-weight:800;color:#9d174d;font-variant-numeric:tabular-nums; }
        .eco-base  { font-weight:600;color:#0097A7;font-variant-numeric:tabular-nums; }
        .eco-bono  { font-weight:700;color:#059669;font-variant-numeric:tabular-nums; }
        .eco-zero  { color:#cbd5e1; }

        .eco-empty { text-align:center;padding:2rem;color:#94a3b8; }

        /* Sección bonos info */
        .eco-bonos-box  { background:#fff;border:1.5px solid #fbcfe8;border-radius:12px;
                          padding:1.1rem 1.25rem;margin-bottom:1.5rem; }
        .eco-bonos-hd   { display:flex;align-items:center;justify-content:space-between;
                          cursor:pointer;user-select:none; }
        .eco-bonos-hd h3 { font-size:.82rem;font-weight:800;text-transform:uppercase;
                           letter-spacing:.07em;color:#9d174d;margin:0; }
        .eco-bonos-hd span { font-size:.75rem;color:#be185d;transition:transform .2s; }
        .eco-bonos-body { margin-top:1rem; }
        .eco-bonos-body.hidden { display:none; }
        .eco-rol-grid   { display:grid;grid-template-columns:repeat(3,1fr);gap:.65rem;margin-bottom:1rem; }
        .eco-rol-card   { background:#fff0f6;border:1px solid #fbcfe8;border-radius:9px;
                          padding:.75rem .9rem;text-align:center; }
        .eco-rol-card__num   { font-size:1.2rem;font-weight:800;color:#9d174d; }
        .eco-rol-card__label { font-size:.62rem;font-weight:700;text-transform:uppercase;
                               letter-spacing:.06em;color:#be185d;margin-top:2px; }
        .eco-bono-group { margin-bottom:.9rem; }
        .eco-bono-group__title { font-size:.68rem;font-weight:700;text-transform:uppercase;
                                 letter-spacing:.06em;color:#64748b;margin-bottom:.4rem; }
        .eco-bono-table { width:100%;border-collapse:collapse;font-size:.78rem; }
        .eco-bono-table th { background:#f8fafc;padding:.3rem .65rem;font-size:.62rem;font-weight:700;
                             text-transform:uppercase;letter-spacing:.05em;color:#64748b;
                             border-bottom:1.5px solid #f1f5f9;text-align:left; }
        .eco-bono-table td { padding:.35rem .65rem;border-bottom:1px solid #f8fafc;vertical-align:middle; }
        .eco-bono-table tr:last-child td { border-bottom:none; }
        .eco-bono-monto { font-weight:700;color:#059669; }

        @media(max-width:600px){ .eco-kpis{grid-template-columns:repeat(2,1fr);}
                                  .eco-rol-grid{grid-template-columns:repeat(3,1fr);} }
    </style>
</head>
<body style="background:#fdf2f8;min-height:100vh;">

<header class="staff-header" style="background:linear-gradient(135deg,#be185d,#9d174d);">
    <div class="staff-header__brand">
        <div class="staff-header__logo">SB</div>
        <div>
            <p class="staff-header__company">Grupo KGyR S.A.C</p>
            <p class="staff-header__app">Mi <span>Economía</span></p>
        </div>
    </div>
    <div class="staff-header__user">
        <p class="staff-header__name"><?= htmlspecialchars($userName) ?></p>
        <a href="<?= $basePath ?>/staff" class="staff-btn-logout" style="font-size:.78rem;">← Volver</a>
    </div>
</header>

<main class="eco-wrap">

    <!-- Sección informativa: Bonos y Tarifas -->
    <div class="eco-bonos-box">
        <div class="eco-bonos-hd" onclick="toggleBonos(this)">
            <h3>Cómo se calculan tus ingresos</h3>
            <span id="bonosChevron">▼</span>
        </div>
        <div class="eco-bonos-body hidden" id="bonosBody">

            <!-- Tarifa base por rol -->
            <div class="eco-rol-grid" style="margin-top:.5rem;">
                <?php foreach (['CAJERA' => 'Cajera', 'VENDEDORA' => 'Vendedora', 'ALMACENERA' => 'Almacenera'] as $cod => $label):
                    $t = $tarifasInfo[$cod] ?? null; ?>
                <div class="eco-rol-card">
                    <div class="eco-rol-card__num">S/ <?= $t ? number_format((float)$t['monto'], 2, '.', '') : '—' ?></div>
                    <div class="eco-rol-card__label"><?= $label ?></div>
                    <div style="font-size:.62rem;color:#94a3b8;margin-top:2px;">por turno</div>
                </div>
                <?php endforeach; ?>
            </div>

            <p style="font-size:.72rem;color:#94a3b8;margin-bottom:.85rem;">
                Tarifa base por turno trabajado. Las cajeras y vendedoras pueden ganar un bono adicional según su desempeño.
            </p>

            <!-- Bono Ventas -->
            <?php if (!empty($bonosVInfo)): ?>
            <div class="eco-bono-group">
                <div class="eco-bono-group__title">Bono por ventas — Vendedora</div>
                <table class="eco-bono-table">
                    <thead>
                        <tr>
                            <th>Desde</th>
                            <th>Hasta</th>
                            <th>Bono</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bonosVInfo as $b): ?>
                        <tr>
                            <td>S/ <?= number_format((float)$b['desde'], 2, '.', ',') ?></td>
                            <td><?= $b['hasta'] !== null ? 'S/ ' . number_format((float)$b['hasta'], 2, '.', ',') : 'Sin límite' ?></td>
                            <td class="eco-bono-monto">S/ <?= number_format((float)$b['monto_bono'], 2, '.', ',') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <!-- Bono Operaciones BCP -->
            <?php if (!empty($bonosOInfo)): ?>
            <div class="eco-bono-group">
                <div class="eco-bono-group__title">Bono por operaciones BCP — Cajera</div>
                <table class="eco-bono-table">
                    <thead>
                        <tr>
                            <th>Desde</th>
                            <th>Hasta</th>
                            <th>Bono</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bonosOInfo as $b): ?>
                        <tr>
                            <td><?= (int)$b['desde'] ?> ops</td>
                            <td><?= $b['hasta'] !== null ? (int)$b['hasta'] . ' ops' : 'Sin límite' ?></td>
                            <td class="eco-bono-monto">S/ <?= number_format((float)$b['monto_bono'], 2, '.', ',') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

        </div>
    </div>

    <!-- Navegación mensual -->
    <div class="eco-nav">
        <a href="?mes=<?= $mesPasado ?>">← Mes anterior</a>
        <span class="eco-mes"><?= $mesLabel ?></span>
        <?php if ($filtroMes < $mesActual): ?>
            <a href="?mes=<?= $mesSiguiente ?>">Mes siguiente →</a>
        <?php endif; ?>
    </div>

    <!-- KPIs -->
    <?php
    $nCert   = count(array_filter($ingresos, fn($i) => $i['certificado']));
    $nNoCert = count($ingresos) - $nCert;
    ?>
    <div class="eco-kpis">
        <div class="eco-kpi">
            <div class="eco-kpi__num"><?= $f2($totalIngCert) ?></div>
            <div class="eco-kpi__label">Ingresos certificados</div>
        </div>
        <div class="eco-kpi">
            <div class="eco-kpi__num" style="color:#b45309;"><?= $f2($totalIngNoCert) ?></div>
            <div class="eco-kpi__label">Sin certificar</div>
        </div>
        <div class="eco-kpi">
            <div class="eco-kpi__num"><?= $nCert ?> / <?= count($ingresos) ?></div>
            <div class="eco-kpi__label">Turnos certificados</div>
        </div>
        <div class="eco-kpi">
            <div class="eco-kpi__num"><?= $f2($totalPagado) ?></div>
            <div class="eco-kpi__label">Pagos recibidos</div>
        </div>
    </div>

    <!-- ── Cuadre del mes ───────────────────────────── -->
    <?php
    // Pagos reales recibidos (excluye DESCUENTO, que no es dinero entregado)
    $totalPagadoReal = array_sum(array_column(
        array_filter($pagos, fn($p) =>
            in_array($p['estado'], ['PAGADO','CONFIRMADO_BENEFICIARIO','APROBADO']) &&
            ($p['tipo_pago'] ?? '') !== 'DESCUENTO'
        ), 'monto'
    ));

    // Descuentos de pago_personal (dinero descontado de sueldo en turno activo)
    $totalDescuentosPP = array_sum(array_column(
        array_filter($pagos, fn($p) =>
            in_array($p['estado'], ['PAGADO','CONFIRMADO_BENEFICIARIO','APROBADO']) &&
            ($p['tipo_pago'] ?? '') === 'DESCUENTO'
        ), 'monto'
    ));

    // Descuentos vía ajuste_esperado: AGREGAR resta, QUITAR suma (devuelve)
    $netDescuentosAdj = 0.0;
    foreach ($descuentosAdj ?? [] as $d) {
        $netDescuentosAdj += $d['accion'] === 'AGREGAR' ? -(float)$d['monto'] : (float)$d['monto'];
    }
    // netDescuentos > 0 = deducción neta, < 0 = devolución neta
    $netDescuentos = $totalDescuentosPP - $netDescuentosAdj;

    $saldo    = $totalIngCert - $totalPagadoReal - $netDescuentos;
    $saldoAbs = abs($saldo);
    if ($saldo > 0.01) {
        $saldoBg = '#fef3c7'; $saldoColor = '#92400e';
        $saldoMsg = 'Saldo contable pendiente';
        $saldoSub = 'La empresa te debe este monto por ingresos certificados de ' . $mesLabel;
    } elseif ($saldo < -0.01) {
        $saldoBg = '#ede9fe'; $saldoColor = '#5b21b6';
        $saldoMsg = 'Has recibido pagos adelantados';
        $saldoSub = 'Recibiste S/ ' . number_format($saldoAbs,2,'.',',' ) . ' más que tus ingresos certificados';
    } else {
        $saldoBg = '#d1fae5'; $saldoColor = '#065f46';
        $saldoMsg = '¡Mes cuadrado!';
        $saldoSub = 'Sin deuda pendiente entre tú y la empresa';
    }
    ?>
    <p class="eco-sec-title">Cuadre del mes</p>
    <div style="background:#fff;border:1px solid #fbcfe8;border-radius:12px;overflow:hidden;margin-bottom:1.5rem;">
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;text-align:center;">
            <div style="padding:1rem;border-right:1px solid #fdf2f8;">
                <div style="font-size:.63rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#be185d;margin-bottom:.3rem;">Ingresos certificados</div>
                <div style="font-size:1.15rem;font-weight:800;color:#0097A7;font-variant-numeric:tabular-nums;"><?= $f2($totalIngCert) ?></div>
            </div>
            <div style="padding:1rem;border-right:1px solid #fdf2f8;">
                <div style="font-size:.63rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#be185d;margin-bottom:.3rem;">Pagos recibidos</div>
                <div style="font-size:1.15rem;font-weight:800;color:#9d174d;font-variant-numeric:tabular-nums;"><?= $f2($totalPagadoReal) ?></div>
                <?php if ($netDescuentos > 0.01): ?>
                <div style="font-size:.7rem;font-weight:700;color:#991b1b;margin-top:.2rem;"><?= $f2($netDescuentos) ?> de descuento</div>
                <?php elseif ($netDescuentos < -0.01): ?>
                <div style="font-size:.7rem;font-weight:700;color:#065f46;margin-top:.2rem;"><?= $f2(abs($netDescuentos)) ?> devuelto</div>
                <?php endif; ?>
            </div>
            <div style="padding:1rem;">
                <div style="font-size:.63rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#be185d;margin-bottom:.3rem;">Saldo contable</div>
                <div style="font-size:1.15rem;font-weight:800;color:<?= $saldoColor ?>;font-variant-numeric:tabular-nums;">
                    <?= ($saldo >= 0 ? '+' : '−') . ' ' . $f2($saldoAbs) ?>
                </div>
            </div>
        </div>
        <?php if ($totalIngNoCert > 0.01): ?>
        <div style="background:#fffbeb;padding:.5rem 1rem;text-align:center;border-top:1px solid #fef3c7;">
            <span style="font-size:.75rem;font-weight:700;color:#b45309;">+ <?= $f2($totalIngNoCert) ?> sin certificar</span>
            <span style="font-size:.7rem;color:#b45309;opacity:.8;margin-left:.4rem;">· Completa las encuestas de <?= $nNoCert ?> turno<?= $nNoCert !== 1 ? 's' : '' ?> para sumar al saldo contable</span>
        </div>
        <?php endif; ?>
        <div style="background:<?= $saldoBg ?>;padding:.6rem 1rem;text-align:center;border-top:1px solid #fdf2f8;">
            <span style="font-size:.8rem;font-weight:700;color:<?= $saldoColor ?>;"><?= $saldoMsg ?></span>
            <span style="font-size:.72rem;color:<?= $saldoColor ?>;opacity:.75;margin-left:.5rem;"><?= $saldoSub ?></span>
        </div>
    </div>

    <!-- ── Tabla 1: Pagos recibidos ─────────────────── -->
    <p class="eco-sec-title">Pagos recibidos</p>
    <?php if (empty($pagos)): ?>
    <div class="eco-empty">Sin pagos registrados en <?= $mesLabel ?>.</div>
    <?php else: ?>
    <div class="eco-table-wrap">
        <table class="eco-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Turno · Local</th>
                    <th>Tipo</th>
                    <th class="text-center">Estado</th>
                    <th>Pagado por</th>
                    <th class="text-right">Monto</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($pagos as $p):
                $dow   = $diasLabel[(int)date('w', strtotime($p['fecha_operacion']))];
                $tInfo = $tipoPagoInfo[$p['tipo_pago']] ?? ['label'=>$p['tipo_pago'],'bg'=>'#f1f5f9','color'=>'#475569'];
                $eInfo = $estadoInfo[$p['estado']]      ?? ['label'=>$p['estado'],   'bg'=>'#f1f5f9','color'=>'#475569'];
            ?>
            <tr>
                <td style="white-space:nowrap;">
                    <strong style="color:#1e293b;"><?= $dow ?></strong>
                    <span class="eco-sub"><?= date('d/m/Y', strtotime($p['fecha_operacion'])) ?></span>
                </td>
                <td>
                    <span style="font-size:.8rem;"><?= $turnoLabel[$p['turno_id']] ?? '—' ?></span>
                    <span class="eco-sub"><?= htmlspecialchars($p['local_desc']) ?></span>
                </td>
                <td><span class="eco-badge" style="background:<?= $tInfo['bg'] ?>;color:<?= $tInfo['color'] ?>"><?= $tInfo['label'] ?></span></td>
                <td class="text-center"><span class="eco-badge" style="background:<?= $eInfo['bg'] ?>;color:<?= $eInfo['color'] ?>"><?= $eInfo['label'] ?></span></td>
                <td style="font-size:.78rem;color:#475569;"><?= htmlspecialchars($p['emisor_nombre']) ?></td>
                <td class="text-right"><span class="eco-monto"><?= $f2($p['monto']) ?></span></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="text-right" style="font-size:.75rem;">Total pagado · <?= $mesLabel ?></td>
                    <td class="text-right"><?= $f2($totalPagado) ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <?php endif; ?>

    <!-- ── Tabla 1b: Historial de descuentos (ajustes admin) ── -->
    <?php if (!empty($descuentosAdj ?? [])): ?>
    <p class="eco-sec-title">Historial de descuentos</p>
    <div class="eco-table-wrap">
        <table class="eco-table">
            <thead>
                <tr>
                    <th>Fecha registro</th>
                    <th>Cuadre · Local</th>
                    <th>Acción</th>
                    <th>Nota</th>
                    <th class="text-right">Monto</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($descuentosAdj as $d):
                $accion   = $d['accion'];
                $tipoPago = $d['tipo_pago'] ?? '';
                $dow      = $diasLabel[(int)date('w', strtotime($d['fecha']))];

                // Semántica por combinación accion + tipo_pago:
                // AGREGAR+DESCUENTO → descuento directo (badge rojo, sin signo)
                // QUITAR+cualquiera → pago revertido / devolución (badge naranja, con −)
                // AGREGAR+otros    → pago registrado desde cuadre (badge azul, sin signo)
                if ($accion === 'QUITAR') {
                    $badge = '<span class="eco-badge" style="background:#ffedd5;color:#9a3412;">Pago revertido</span>';
                    $signo = '−'; $colorMonto = '#991b1b';
                } elseif ($tipoPago === 'DESCUENTO') {
                    $badge = '<span class="eco-badge" style="background:#fee2e2;color:#991b1b;">Descuento aplicado</span>';
                    $signo = ''; $colorMonto = '#991b1b';
                } else {
                    $badge = '<span class="eco-badge" style="background:#e0f2fe;color:#0369a1;">Pago registrado</span>';
                    $signo = ''; $colorMonto = '#0369a1';
                }
            ?>
            <tr>
                <td style="white-space:nowrap;">
                    <strong style="color:#1e293b;"><?= $dow ?></strong>
                    <span class="eco-sub"><?= date('d/m/Y', strtotime($d['fecha'])) ?></span>
                </td>
                <td>
                    <span style="font-size:.8rem;">Cuadre #<?= $d['id_sesion'] ?></span>
                    <span class="eco-sub"><?= htmlspecialchars($d['local_desc']) ?></span>
                </td>
                <td><?= $badge ?></td>
                <td style="font-size:.78rem;color:#475569;"><?= htmlspecialchars($d['descripcion'] ?? '') ?></td>
                <td class="text-right">
                    <span class="eco-monto" style="color:<?= $colorMonto ?>">
                        <?= $signo ? $signo . ' ' : '' ?><?= $f2($d['monto']) ?>
                    </span>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- ── Tabla 2: Ingresos por turno trabajado ────── -->
    <?php
    $ingCert   = array_values(array_filter($ingresos, fn($i) =>  $i['certificado']));
    $ingNoCert = array_values(array_filter($ingresos, fn($i) => !$i['certificado']));

    $renderTablaIngresos = function(array $filas, float $total) use ($f2, $diasLabel, $turnoLabel): void { ?>
    <div class="eco-table-wrap">
        <table class="eco-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Turno · Local</th>
                    <th>Rol</th>
                    <th class="text-right">Base</th>
                    <th class="text-right">Bono ventas</th>
                    <th class="text-right">Bono ops.</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($filas as $ing):
                $dow = $diasLabel[(int)date('w', strtotime($ing['fecha_dia']))];
            ?>
            <tr>
                <td style="white-space:nowrap;">
                    <strong style="color:#1e293b;"><?= $dow ?></strong>
                    <span class="eco-sub"><?= date('d/m/Y', strtotime($ing['fecha_dia'])) ?></span>
                </td>
                <td>
                    <span style="font-size:.8rem;"><?= $turnoLabel[$ing['turno_id']] ?? '—' ?></span>
                    <span class="eco-sub"><?= htmlspecialchars($ing['local_desc']) ?></span>
                </td>
                <td style="font-size:.78rem;font-weight:600;color:#475569;"><?= htmlspecialchars($ing['rol_desc']) ?></td>
                <td class="text-right"><span class="eco-base"><?= $f2($ing['base']) ?></span></td>
                <td class="text-right">
                    <?= $ing['bono_v'] > 0
                        ? '<span class="eco-bono">'.$f2($ing['bono_v']).'</span>'
                        : '<span class="eco-zero">—</span>' ?>
                </td>
                <td class="text-right">
                    <?= $ing['bono_o'] > 0
                        ? '<span class="eco-bono">'.$f2($ing['bono_o']).'</span>'
                        : '<span class="eco-zero">—</span>' ?>
                </td>
                <td class="text-right"><span class="eco-monto"><?= $f2($ing['total']) ?></span></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-right" style="font-size:.75rem;">Subtotal</td>
                    <td class="text-right"><?= $f2(array_sum(array_column($filas,'base'))) ?></td>
                    <td class="text-right"><?= $f2(array_sum(array_column($filas,'bono_v'))) ?></td>
                    <td class="text-right"><?= $f2(array_sum(array_column($filas,'bono_o'))) ?></td>
                    <td class="text-right"><?= $f2($total) ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <?php }; ?>

    <?php if (empty($ingresos)): ?>
    <p class="eco-sec-title">Ingresos por turno trabajado</p>
    <div class="eco-empty">Sin turnos registrados en <?= $mesLabel ?>.</div>
    <?php else: ?>

    <?php if (!empty($ingCert)): ?>
    <p class="eco-sec-title" style="color:#065f46;">✓ Ingresos certificados</p>
    <?php $renderTablaIngresos($ingCert, $totalIngCert); ?>
    <?php endif; ?>

    <?php if (!empty($ingNoCert)): ?>
    <p class="eco-sec-title" style="color:#b45309;margin-top:1.25rem;">⚠ Ingresos sin certificar</p>
    <p style="font-size:.78rem;color:#92400e;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:.5rem .75rem;margin-bottom:.6rem;">
        Estos turnos no tienen encuesta completada. Pide al supervisor que la registre para que pasen a ingresos certificados.
    </p>
    <?php $renderTablaIngresos($ingNoCert, $totalIngNoCert); ?>
    <?php endif; ?>

    <?php endif; ?>

</main>

<script src="<?= $basePath ?>/assets/js/session-guard.js"></script>
<script>
function toggleBonos(hd) {
    const body    = document.getElementById('bonosBody');
    const chevron = document.getElementById('bonosChevron');
    const open    = !body.classList.contains('hidden');
    body.classList.toggle('hidden', open);
    chevron.textContent = open ? '▼' : '▲';
}
</script>
</body>
</html>
