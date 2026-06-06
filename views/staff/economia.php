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
        .eco-wrap  { max-width:1100px;margin:0 auto;padding:1.25rem 1rem 3rem; }

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
        .eco-table { width:100%;border-collapse:collapse;font-size:.78rem;background:#fff;
                     border-radius:10px;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.06); }
        .eco-table th { background:#fff0f6;padding:.45rem .55rem;font-size:.60rem;font-weight:700;
                        text-transform:uppercase;letter-spacing:.04em;color:#be185d;
                        border-bottom:2px solid #fbcfe8;white-space:nowrap; }
        .eco-table td { padding:.5rem .55rem;border-bottom:1px solid #fdf2f8;vertical-align:middle; }
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

            <!-- Bono Servicio -->
            <div class="eco-bono-group" style="margin-top:.5rem;">
                <div class="eco-bono-group__title">Bono por tiempo de servicio</div>
                <p style="font-size:.72rem;color:#64748b;margin-bottom:.4rem;">
                    S/ 0.20 × meses completos trabajados en la empresa, contados desde el inicio del mes consultado.
                </p>
                <?php if ($fechaIngreso ?? null): ?>
                <p style="font-size:.72rem;color:#065f46;background:#d1fae5;border-radius:6px;padding:.3rem .6rem;">
                    ✓ Desde <?= date('d/m/Y', strtotime($fechaIngreso)) ?> · <?= $mesesServicio ?> meses · S/ <?= number_format($bonoServicioMonto, 2) ?> por turno
                </p>
                <?php else: ?>
                <p style="font-size:.72rem;color:#64748b;">Fecha de ingreso no registrada — consulta al administrador.</p>
                <?php endif; ?>
            </div>

            <!-- Bono Estudios -->
            <div class="eco-bono-group" style="margin-top:.5rem;">
                <div class="eco-bono-group__title">Bono por estudios</div>
                <table class="eco-bono-table">
                    <thead>
                        <tr>
                            <th>Nivel</th>
                            <th>Estado</th>
                            <th>Bono por turno</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Universitario</td>
                            <td>En curso / Trunco</td>
                            <td class="eco-bono-monto">S/ 3.00</td>
                        </tr>
                        <tr>
                            <td>Universitario</td>
                            <td>Egreso / Titulado</td>
                            <td class="eco-bono-monto">S/ 6.00</td>
                        </tr>
                        <tr>
                            <td>Técnico</td>
                            <td>En curso / Trunco</td>
                            <td class="eco-bono-monto">S/ 2.00</td>
                        </tr>
                        <tr>
                            <td>Técnico</td>
                            <td>Egreso / Titulado</td>
                            <td class="eco-bono-monto">S/ 4.00</td>
                        </tr>
                    </tbody>
                </table>
                <?php if ($estudioInfo): ?>
                <p style="font-size:.72rem;margin-top:.4rem;color:#065f46;background:#d1fae5;border-radius:6px;padding:.3rem .6rem;">
                    ✓ Aplica a ti · <?= htmlspecialchars($estudioInfo['tipo_desc']) ?> — <?= htmlspecialchars($estudioInfo['estado_desc']) ?> · S/ <?= number_format($bonoEstudioMonto, 2) ?> por turno
                </p>
                <?php else: ?>
                <p style="font-size:.72rem;margin-top:.4rem;color:#64748b;">
                    No aplica a tu perfil actual.
                </p>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <!-- Navegación mensual -->
    <div class="eco-nav">
        <form method="get" style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;">
            <input type="month" name="mes" value="<?= htmlspecialchars($filtroMes) ?>"
                   max="<?= $mesActual ?>"
                   onchange="this.form.submit()"
                   style="padding:.35rem .7rem;border-radius:8px;border:1.5px solid #fbcfe8;
                          background:#fff0f6;color:#9d174d;font-size:.85rem;font-weight:600;
                          cursor:pointer;outline:none;">
        </form>
        <?php if ($estudioInfo): ?>
        <span style="display:inline-flex;align-items:center;gap:.3rem;padding:.3rem .75rem;
                     border-radius:20px;background:#d1fae5;border:1px solid #6ee7b7;
                     font-size:.75rem;font-weight:700;color:#065f46;">
            🎓 <?= htmlspecialchars($estudioInfo['tipo_desc']) ?> · <?= htmlspecialchars($estudioInfo['estado_desc']) ?>
        </span>
        <?php else: ?>
        <span style="display:inline-flex;align-items:center;padding:.3rem .75rem;
                     border-radius:20px;background:#f1f5f9;border:1px solid #e2e8f0;
                     font-size:.75rem;font-weight:700;color:#64748b;">
            Sin categoría
        </span>
        <?php endif; ?>
    </div>

    <!-- KPIs -->
    <?php
    $totalPagadoReal = array_sum(array_column(
        array_filter($pagos, fn($p) =>
            in_array($p['estado'], ['PAGADO','CONFIRMADO_BENEFICIARIO','APROBADO']) &&
            ($p['tipo_pago'] ?? '') !== 'DESCUENTO'
        ), 'monto'
    ));
    foreach ($descuentosAdj ?? [] as $d) {
        $totalPagadoReal += $d['accion'] === 'AGREGAR' ? (float)$d['monto'] : -(float)$d['monto'];
    }
    ?>
    <div class="eco-kpis">
        <div class="eco-kpi" style="background:#fef9c3;border-color:#fde047;">
            <div class="eco-kpi__num" style="color:#854d0e;"><?= $f2($totalIngresos) ?></div>
            <div class="eco-kpi__label" style="color:#a16207;">Ingresos del mes</div>
        </div>
        <div class="eco-kpi">
            <div class="eco-kpi__num" style="color:#059669;"><?= $f2($totalBonos) ?></div>
            <div class="eco-kpi__label">Bonos acumulados</div>
        </div>
        <div class="eco-kpi">
            <div class="eco-kpi__num"><?= count($ingresos) ?></div>
            <div class="eco-kpi__label">Turnos trabajados</div>
        </div>
        <div class="eco-kpi">
            <div class="eco-kpi__num"><?= $f2($totalPagadoReal) ?></div>
            <div class="eco-kpi__label">Pagos recibidos</div>
        </div>
    </div>

    <!-- ── Tabla 1: Pagos recibidos (pago_personal + ajuste_esperado PERSONAL) ── -->
    <?php
    $todosPagos = [];
    foreach ($pagos as $p) {
        $todosPagos[] = ['_src' => 'pago'] + $p;
    }
    foreach ($descuentosAdj ?? [] as $d) {
        $todosPagos[] = ['_src' => 'ajuste'] + $d;
    }
    usort($todosPagos, fn($a, $b) => strcmp(
        $b['_src'] === 'pago' ? ($b['fecha_operacion'] ?? $b['fecha']) : $b['fecha'],
        $a['_src'] === 'pago' ? ($a['fecha_operacion'] ?? $a['fecha']) : $a['fecha']
    ));
    ?>
    <p class="eco-sec-title">Pagos recibidos</p>
    <?php if (empty($todosPagos)): ?>
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
                    <th>Registrado por</th>
                    <th class="text-right">Monto</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($todosPagos as $row):
                if ($row['_src'] === 'pago'):
                    $fecha = $row['fecha_operacion'] ?? $row['fecha_pago'];
                    $dow   = $diasLabel[(int)date('w', strtotime($fecha))];
                    $tInfo = $tipoPagoInfo[$row['tipo_pago']] ?? ['label'=>$row['tipo_pago'],'bg'=>'#f1f5f9','color'=>'#475569'];
                    $eInfo = $estadoInfo[$row['estado']]      ?? ['label'=>$row['estado'],   'bg'=>'#f1f5f9','color'=>'#475569'];
            ?>
            <tr>
                <td style="white-space:nowrap;">
                    <strong style="color:#1e293b;"><?= $dow ?></strong>
                    <span class="eco-sub"><?= date('d/m/Y', strtotime($fecha)) ?></span>
                </td>
                <td>
                    <span style="font-size:.8rem;"><?= $turnoLabel[$row['turno_id']] ?? '—' ?></span>
                    <span class="eco-sub"><?= htmlspecialchars($row['local_desc']) ?></span>
                </td>
                <td><span class="eco-badge" style="background:<?= $tInfo['bg'] ?>;color:<?= $tInfo['color'] ?>"><?= $tInfo['label'] ?></span></td>
                <td class="text-center"><span class="eco-badge" style="background:<?= $eInfo['bg'] ?>;color:<?= $eInfo['color'] ?>"><?= $eInfo['label'] ?></span></td>
                <td style="font-size:.78rem;color:#475569;"><?= htmlspecialchars($row['emisor_nombre']) ?></td>
                <td class="text-right"><span class="eco-monto"><?= $f2($row['monto']) ?></span></td>
            </tr>
            <?php else:
                $dow = $diasLabel[(int)date('w', strtotime($row['fecha']))];
                if ($row['accion'] === 'QUITAR') {
                    $tBg = '#ffedd5'; $tColor = '#9a3412'; $tLabel = 'Revertido';
                    $montoColor = '#991b1b'; $signo = '−';
                } else {
                    $tBg = '#e0f2fe'; $tColor = '#0369a1'; $tLabel = 'Pago via cuadre';
                    $montoColor = '#0369a1'; $signo = '';
                }
            ?>
            <tr>
                <td style="white-space:nowrap;">
                    <strong style="color:#1e293b;"><?= $dow ?></strong>
                    <span class="eco-sub"><?= date('d/m/Y', strtotime($row['fecha'])) ?></span>
                </td>
                <td>
                    <span style="font-size:.8rem;">Cuadre #<?= $row['id_sesion'] ?></span>
                    <span class="eco-sub"><?= htmlspecialchars($row['local_desc']) ?></span>
                </td>
                <td><span class="eco-badge" style="background:<?= $tBg ?>;color:<?= $tColor ?>"><?= $tLabel ?></span></td>
                <td class="text-center"><span class="eco-badge" style="background:#f1f5f9;color:#475569;"><?= htmlspecialchars($row['descripcion'] ?? '') ?></span></td>
                <td style="font-size:.78rem;color:#475569;"><?= htmlspecialchars($row['admin_nombre'] ?? '') ?></td>
                <td class="text-right"><span class="eco-monto" style="color:<?= $montoColor ?>"><?= $signo ? $signo . ' ' : '' ?><?= $f2($row['monto']) ?></span></td>
            </tr>
            <?php endif; ?>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="text-right" style="font-size:.75rem;">Total pagado · <?= $mesLabel ?></td>
                    <td class="text-right"><?= $f2($totalPagadoReal) ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <?php endif; ?>

    <!-- ── Tabla 2: Ingresos por turno trabajado ────── -->
    <?php
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
                    <th class="text-right">Bono estudios</th>
                    <th class="text-right">Bono servicio</th>
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
                <td class="text-right">
                    <?= ($ing['bono_e'] ?? 0) > 0
                        ? '<span class="eco-bono">'.$f2($ing['bono_e']).'</span>'
                        : '<span class="eco-zero">—</span>' ?>
                </td>
                <td class="text-right">
                    <?= ($ing['bono_s'] ?? 0) > 0
                        ? '<span class="eco-bono">'.$f2($ing['bono_s']).'</span>'
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
                    <td class="text-right"><?= $f2(array_sum(array_column($filas,'bono_e'))) ?></td>
                    <td class="text-right"><?= $f2(array_sum(array_column($filas,'bono_s'))) ?></td>
                    <td class="text-right"><?= $f2($total) ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <?php }; ?>

    <p class="eco-sec-title">Ingresos por turno trabajado</p>
    <?php if (empty($ingresos)): ?>
    <div class="eco-empty">Sin turnos registrados en <?= $mesLabel ?>.</div>
    <?php else: ?>
    <?php $renderTablaIngresos($ingresos, $totalIngresos); ?>
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
