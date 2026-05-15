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
    'OTROS'      => ['label' => 'Otros',      'bg' => '#ede9fe', 'color' => '#5b21b6'],
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

        @media(max-width:600px){ .eco-kpis{grid-template-columns:repeat(2,1fr);} }
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

    <!-- Navegación mensual -->
    <div class="eco-nav">
        <a href="?mes=<?= $mesPasado ?>">← Mes anterior</a>
        <span class="eco-mes"><?= $mesLabel ?></span>
        <?php if ($filtroMes < $mesActual): ?>
            <a href="?mes=<?= $mesSiguiente ?>">Mes siguiente →</a>
        <?php endif; ?>
    </div>

    <!-- KPIs -->
    <div class="eco-kpis">
        <div class="eco-kpi">
            <div class="eco-kpi__num"><?= $f2($totalIngresos) ?></div>
            <div class="eco-kpi__label">Ingresos del mes</div>
        </div>
        <div class="eco-kpi">
            <div class="eco-kpi__num"><?= $f2($totalBonos) ?></div>
            <div class="eco-kpi__label">Bonos acumulados</div>
        </div>
        <div class="eco-kpi">
            <div class="eco-kpi__num"><?= count($ingresos) ?></div>
            <div class="eco-kpi__label">Turnos trabajados</div>
        </div>
        <div class="eco-kpi">
            <div class="eco-kpi__num"><?= $f2($totalPagado) ?></div>
            <div class="eco-kpi__label">Pagos recibidos</div>
        </div>
    </div>

    <!-- ── Cuadre del mes ───────────────────────────── -->
    <?php
    $saldo = $totalIngresos - $totalPagado;
    $saldoAbs = abs($saldo);
    if ($saldo > 0.01) {
        $saldoBg = '#fef3c7'; $saldoColor = '#92400e';
        $saldoMsg = 'Saldo pendiente de pago';
        $saldoSub = 'La empresa te debe este monto por el mes de ' . $mesLabel;
    } elseif ($saldo < -0.01) {
        $saldoBg = '#ede9fe'; $saldoColor = '#5b21b6';
        $saldoMsg = 'Has recibido pagos adelantados';
        $saldoSub = 'Recibiste S/ ' . number_format($saldoAbs,2,'.',',' ) . ' más que tus ingresos del mes';
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
                <div style="font-size:.63rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#be185d;margin-bottom:.3rem;">Ingresos del mes</div>
                <div style="font-size:1.15rem;font-weight:800;color:#0097A7;font-variant-numeric:tabular-nums;"><?= $f2($totalIngresos) ?></div>
            </div>
            <div style="padding:1rem;border-right:1px solid #fdf2f8;">
                <div style="font-size:.63rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#be185d;margin-bottom:.3rem;">Pagos recibidos</div>
                <div style="font-size:1.15rem;font-weight:800;color:#9d174d;font-variant-numeric:tabular-nums;"><?= $f2($totalPagado) ?></div>
            </div>
            <div style="padding:1rem;">
                <div style="font-size:.63rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#be185d;margin-bottom:.3rem;">Saldo</div>
                <div style="font-size:1.15rem;font-weight:800;color:<?= $saldoColor ?>;font-variant-numeric:tabular-nums;">
                    <?= ($saldo >= 0 ? '+' : '−') . ' ' . $f2($saldoAbs) ?>
                </div>
            </div>
        </div>
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

    <!-- ── Tabla 2: Ingresos diarios ────────────────── -->
    <p class="eco-sec-title">Ingresos por turno trabajado</p>
    <?php if (empty($ingresos)): ?>
    <div class="eco-empty">Sin turnos registrados en <?= $mesLabel ?>.</div>
    <?php else: ?>
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
            <?php foreach ($ingresos as $ing):
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
                    <td colspan="3" class="text-right" style="font-size:.75rem;">Total · <?= $mesLabel ?></td>
                    <td class="text-right"><?= $f2(array_sum(array_column($ingresos,'base'))) ?></td>
                    <td class="text-right"><?= $f2(array_sum(array_column($ingresos,'bono_v'))) ?></td>
                    <td class="text-right"><?= $f2(array_sum(array_column($ingresos,'bono_o'))) ?></td>
                    <td class="text-right"><?= $f2($totalIngresos) ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <?php endif; ?>

</main>

<script src="<?= $basePath ?>/assets/js/session-guard.js"></script>
</body>
</html>
