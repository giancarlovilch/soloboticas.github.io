<?php
if (!isset($_SESSION['user_rol'])) exit('Acceso denegado');

// Desempacar datos cargados por el controlador
extract($economiaDatos ?? []);
$ecoPagos         = $ecoPagos         ?? [];
$ecoTrabajadores  = $ecoTrabajadores  ?? [];
$ecoIngresos      = $ecoIngresos      ?? [];
$ecoTotalIngresos = $ecoTotalIngresos ?? 0.0;
$ecoTotalBonos    = $ecoTotalBonos    ?? 0.0;
$ecoMes           = $ecoMes           ?? date('Y-m');
$ecoPid           = $ecoPid           ?? 0;
$ecoTipo          = $ecoTipo          ?? '';

$meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
[$anioF, $nmesF] = explode('-', $ecoMes);
$mesLabel   = $meses[(int)$nmesF - 1] . ' ' . $anioF;
$turnoLabel = [1 => '☀️ Mañana', 2 => '🌙 Tarde'];
$diasLabel  = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];

$tipoPagoInfo = [
    'PAGO_TOTAL' => ['label' => 'Pago total',  'bg' => '#d1fae5', 'color' => '#065f46'],
    'ADELANTO'   => ['label' => 'Adelanto',    'bg' => '#fef3c7', 'color' => '#92400e'],
    'OTROS'      => ['label' => 'Otros',       'bg' => '#ede9fe', 'color' => '#5b21b6'],
];
$estadoInfo = [
    'PAGADO'                  => ['label' => 'Pagado',        'bg' => '#d1fae5', 'color' => '#065f46'],
    'CONFIRMADO_BENEFICIARIO' => ['label' => 'Confirmado',    'bg' => '#d1fae5', 'color' => '#065f46'],
    'APROBADO'                => ['label' => 'Aprobado',      'bg' => '#ede9fe', 'color' => '#5b21b6'],
    'PENDIENTE'               => ['label' => 'Pendiente',     'bg' => '#fef3c7', 'color' => '#92400e'],
    'OBSERVADO'               => ['label' => 'Observado',     'bg' => '#ffedd5', 'color' => '#9a3412'],
    'RECHAZADO'               => ['label' => 'Rechazado',     'bg' => '#fee2e2', 'color' => '#991b1b'],
    'AJUSTE_CUADRE'           => ['label' => 'Ajuste cuadre', 'bg' => '#e0f2fe', 'color' => '#0369a1'],
];

// KPIs
$totalPagado     = 0;
$trabajadoresPagados = [];
foreach ($ecoPagos as $p) {
    if (in_array($p['estado'], ['PAGADO','CONFIRMADO_BENEFICIARIO','APROBADO','AJUSTE_CUADRE'])) {
        $totalPagado += (float)$p['monto'];
    }
    $trabajadoresPagados[$p['beneficiario_nombre']] = true;
}
$totalAdelantos = count(array_filter($ecoPagos, fn($p) => $p['tipo_pago'] === 'ADELANTO'));
?>
<style>
.eco-admin-filtros { display:flex;gap:.5rem;flex-wrap:wrap;align-items:flex-end;margin-bottom:1rem; }
.eco-admin-filtros select,
.eco-admin-filtros input { padding:.4rem .7rem;border:1.5px solid #fbcfe8;border-radius:8px;font-size:.82rem;outline:none;background:#fff; }
.eco-admin-filtros select:focus,
.eco-admin-filtros input:focus { border-color:#ec4899; }

.eco-kpis { display:grid;grid-template-columns:repeat(4,1fr);gap:.65rem;margin-bottom:1.25rem; }
.eco-kpi  { background:#fff0f6;border:1px solid #fbcfe8;border-radius:10px;padding:.65rem 1rem;text-align:center; }
.eco-kpi__num   { font-size:1.35rem;font-weight:800;color:#9d174d; }
.eco-kpi__label { font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#be185d; }

.eco-badge { display:inline-block;font-size:.67rem;font-weight:700;padding:2px 8px;border-radius:20px;white-space:nowrap; }
.eco-sub   { font-size:.68rem;color:#94a3b8;display:block; }
.eco-monto { font-size:.95rem;font-weight:800;color:#9d174d;font-variant-numeric:tabular-nums; }
.eco-base  { font-weight:600;color:#0097A7;font-variant-numeric:tabular-nums; }
.eco-bono  { font-weight:700;color:#059669;font-variant-numeric:tabular-nums; }
.eco-zero  { color:#cbd5e1; }
.eco-sec-title { font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;
                 color:#be185d;margin:1.5rem 0 .65rem; }
.eco-table-wrap { overflow-x:auto; }
.eco-table { width:100%;border-collapse:collapse;font-size:.80rem;background:#fff;
             border-radius:10px;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.06); }
.eco-table th { background:#fff0f6;padding:.5rem .75rem;font-size:.63rem;font-weight:700;
                text-transform:uppercase;letter-spacing:.06em;color:#be185d;
                border-bottom:2px solid #fbcfe8;white-space:nowrap; }
.eco-table td { padding:.5rem .75rem;border-bottom:1px solid #fdf2f8;vertical-align:middle; }
.eco-table tr:last-child td { border-bottom:none; }
.eco-table tfoot td { background:#fff0f6;font-weight:700;color:#9d174d;border-top:2px solid #fbcfe8; }

@media(max-width:700px){ .eco-kpis{grid-template-columns:repeat(2,1fr);} }
</style>

<div class="postulantes-container">
    <div class="section-header" style="flex-wrap:wrap;gap:.75rem;">
        <div class="header-info">
            <p class="section-kicker">Operaciones</p>
            <h2 style="color:#9d174d;">💰 Economía — Pagos de Personal</h2>
        </div>
    </div>

    <!-- Filtros -->
    <form method="GET" class="eco-admin-filtros">
        <input type="hidden" name="page" value="economia">
        <input type="month" name="mes" value="<?= htmlspecialchars($ecoMes) ?>"
               onchange="this.form.submit()" style="max-width:145px;">
        <select name="trabajador" onchange="this.form.submit()" style="min-width:170px;">
            <option value="0">Todos los trabajadores</option>
            <?php foreach ($ecoTrabajadores as $t): ?>
                <option value="<?= $t['id'] ?>" <?= $ecoPid == $t['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($t['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select name="tipo" onchange="this.form.submit()" style="max-width:145px;">
            <option value="">Todos los tipos</option>
            <option value="PAGO_TOTAL" <?= $ecoTipo === 'PAGO_TOTAL' ? 'selected' : '' ?>>Pago total</option>
            <option value="ADELANTO"   <?= $ecoTipo === 'ADELANTO'   ? 'selected' : '' ?>>Adelanto</option>
            <option value="OTROS"      <?= $ecoTipo === 'OTROS'      ? 'selected' : '' ?>>Otros</option>
        </select>
    </form>

    <!-- KPIs -->
    <div class="eco-kpis">
        <div class="eco-kpi">
            <div class="eco-kpi__num">S/ <?= number_format($ecoTotalIngresos, 2, '.', ',') ?></div>
            <div class="eco-kpi__label">Ingresos del mes</div>
        </div>
        <div class="eco-kpi">
            <div class="eco-kpi__num">S/ <?= number_format($ecoTotalBonos, 2, '.', ',') ?></div>
            <div class="eco-kpi__label">Bonos acumulados</div>
        </div>
        <div class="eco-kpi">
            <div class="eco-kpi__num">S/ <?= number_format($totalPagado, 2, '.', ',') ?></div>
            <div class="eco-kpi__label">Total pagado</div>
        </div>
        <div class="eco-kpi">
            <div class="eco-kpi__num"><?= count($ecoIngresos) ?></div>
            <div class="eco-kpi__label">Turnos trabajados</div>
        </div>
    </div>

    <!-- Tabla pagos -->
    <p class="eco-sec-title">Pagos recibidos</p>
    <div class="table-wrapper">
        <table class="fl-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Beneficiario</th>
                    <th>Turno · Local</th>
                    <th>Tipo</th>
                    <th class="text-center">Estado</th>
                    <th>Pagado por</th>
                    <th class="text-right">Monto</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($ecoPagos)): ?>
                <tr><td colspan="7" class="text-center" style="padding:2rem;color:#94a3b8;">
                    Sin pagos registrados en <?= htmlspecialchars($mesLabel) ?>.
                </td></tr>
            <?php endif; ?>
            <?php foreach ($ecoPagos as $p):
                $dow   = $diasLabel[(int)date('w', strtotime($p['fecha_operacion']))];
                $tInfo = $tipoPagoInfo[$p['tipo_pago']] ?? ['label'=>$p['tipo_pago'],'bg'=>'#f1f5f9','color'=>'#475569'];
                $eInfo = $estadoInfo[$p['estado']]      ?? ['label'=>$p['estado'],   'bg'=>'#f1f5f9','color'=>'#475569'];
            ?>
            <tr>
                <td style="white-space:nowrap;">
                    <strong><?= $dow ?></strong>
                    <span class="eco-sub"><?= date('d/m/Y', strtotime($p['fecha_operacion'])) ?></span>
                </td>
                <td style="font-weight:600;color:#1e293b;"><?= htmlspecialchars($p['beneficiario_nombre']) ?></td>
                <td style="font-size:.78rem;">
                    <?= $turnoLabel[$p['turno_id']] ?? '—' ?>
                    <span class="eco-sub"><?= htmlspecialchars($p['local_desc']) ?></span>
                </td>
                <td>
                    <span class="eco-badge" style="background:<?= $tInfo['bg'] ?>;color:<?= $tInfo['color'] ?>">
                        <?= $tInfo['label'] ?>
                    </span>
                    <?php if ($p['numero_operacion']): ?>
                        <span class="eco-sub">Op. <?= htmlspecialchars($p['numero_operacion']) ?></span>
                    <?php endif; ?>
                </td>
                <td class="text-center">
                    <span class="eco-badge" style="background:<?= $eInfo['bg'] ?>;color:<?= $eInfo['color'] ?>">
                        <?= $eInfo['label'] ?>
                    </span>
                </td>
                <td style="font-size:.78rem;color:#475569;"><?= htmlspecialchars($p['emisor_nombre']) ?></td>
                <td class="text-right">
                    <span class="eco-monto">S/ <?= number_format((float)$p['monto'], 2, '.', ',') ?></span>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (!empty($ecoPagos)): ?>
            <tr style="background:#fff0f6;font-weight:700;">
                <td colspan="6" style="text-align:right;font-size:.8rem;color:#9d174d;padding:.5rem .75rem;">
                    Total del mes · <?= htmlspecialchars($mesLabel) ?>
                </td>
                <td class="text-right" style="color:#9d174d;font-size:1rem;font-variant-numeric:tabular-nums;">
                    S/ <?= number_format($totalPagado, 2, '.', ',') ?>
                </td>
            </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Tabla ingresos diarios -->
    <p class="eco-sec-title">Ingresos por turno trabajado</p>
    <?php if (empty($ecoIngresos)): ?>
        <p style="font-size:.82rem;color:#94a3b8;margin-bottom:1rem;">Sin turnos trabajados registrados en <?= htmlspecialchars($mesLabel) ?>.</p>
    <?php else: ?>
    <div class="eco-table-wrap">
        <table class="eco-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Trabajador</th>
                    <th>Turno · Local</th>
                    <th>Rol</th>
                    <th class="text-right">Base</th>
                    <th class="text-right">Bono ventas</th>
                    <th class="text-right">Bono ops.</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($ecoIngresos as $ing):
                $dow = $diasLabel[(int)date('w', strtotime($ing['fecha_dia']))];
                $f2  = fn($v) => 'S/ ' . number_format((float)$v, 2, '.', ',');
            ?>
            <tr>
                <td style="white-space:nowrap;">
                    <strong style="color:#1e293b;"><?= $dow ?></strong>
                    <span class="eco-sub"><?= date('d/m/Y', strtotime($ing['fecha_dia'])) ?></span>
                </td>
                <td style="font-weight:600;font-size:.82rem;"><?= htmlspecialchars($ing['trabajador_nombre']) ?></td>
                <td style="font-size:.78rem;">
                    <?= $turnoLabel[$ing['turno_id']] ?? '—' ?>
                    <span class="eco-sub"><?= htmlspecialchars($ing['local_desc']) ?></span>
                </td>
                <td style="font-size:.78rem;color:#475569;font-weight:600;"><?= htmlspecialchars($ing['rol_desc']) ?></td>
                <td class="text-right"><span class="eco-base"><?= $f2($ing['base']) ?></span></td>
                <td class="text-right">
                    <?= $ing['bono_v'] > 0 ? '<span class="eco-bono">'.$f2($ing['bono_v']).'</span>' : '<span class="eco-zero">—</span>' ?>
                </td>
                <td class="text-right">
                    <?= $ing['bono_o'] > 0 ? '<span class="eco-bono">'.$f2($ing['bono_o']).'</span>' : '<span class="eco-zero">—</span>' ?>
                </td>
                <td class="text-right"><span class="eco-monto"><?= $f2($ing['total']) ?></span></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-right" style="font-size:.75rem;">Total · <?= htmlspecialchars($mesLabel) ?></td>
                    <td class="text-right"><?= 'S/ '.number_format(array_sum(array_column($ecoIngresos,'base')),2,'.',',' ) ?></td>
                    <td class="text-right"><?= 'S/ '.number_format(array_sum(array_column($ecoIngresos,'bono_v')),2,'.',',' ) ?></td>
                    <td class="text-right"><?= 'S/ '.number_format(array_sum(array_column($ecoIngresos,'bono_o')),2,'.',',' ) ?></td>
                    <td class="text-right">S/ <?= number_format($ecoTotalIngresos, 2, '.', ',') ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <?php endif; ?>

    <!-- ── Cuadre del mes ───────────────────────────── -->
    <?php if ($ecoPid > 0):
        $saldo    = $ecoTotalIngresos - $totalPagado;
        $saldoAbs = abs($saldo);
        if ($saldo > 0.01) {
            $saldoBg = '#fef3c7'; $saldoColor = '#92400e';
            $saldoMsg = 'Saldo pendiente de pago';
            $saldoSub = 'La empresa debe este monto al trabajador en ' . htmlspecialchars($mesLabel);
        } elseif ($saldo < -0.01) {
            $saldoBg = '#ede9fe'; $saldoColor = '#5b21b6';
            $saldoMsg = 'Pagos adelantados';
            $saldoSub = 'El trabajador recibió S/ ' . number_format($saldoAbs,2,'.',',' ) . ' más que sus ingresos del mes';
        } else {
            $saldoBg = '#d1fae5'; $saldoColor = '#065f46';
            $saldoMsg = 'Mes cuadrado';
            $saldoSub = 'Sin deuda pendiente entre el trabajador y la empresa';
        }
    ?>
    <p class="eco-sec-title">Cuadre del mes</p>
    <div style="background:#fff;border:1px solid #fbcfe8;border-radius:12px;overflow:hidden;margin-bottom:1rem;">
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;text-align:center;">
            <div style="padding:1rem;border-right:1px solid #fdf2f8;">
                <div style="font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#be185d;margin-bottom:.3rem;">Ingresos del mes</div>
                <div style="font-size:1.1rem;font-weight:800;color:#0097A7;font-variant-numeric:tabular-nums;">S/ <?= number_format($ecoTotalIngresos,2,'.',',' ) ?></div>
            </div>
            <div style="padding:1rem;border-right:1px solid #fdf2f8;">
                <div style="font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#be185d;margin-bottom:.3rem;">Pagos recibidos</div>
                <div style="font-size:1.1rem;font-weight:800;color:#9d174d;font-variant-numeric:tabular-nums;">S/ <?= number_format($totalPagado,2,'.',',' ) ?></div>
            </div>
            <div style="padding:1rem;">
                <div style="font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#be185d;margin-bottom:.3rem;">Saldo</div>
                <div style="font-size:1.1rem;font-weight:800;color:<?= $saldoColor ?>;font-variant-numeric:tabular-nums;">
                    <?= ($saldo >= 0 ? '+' : '−') ?> S/ <?= number_format($saldoAbs,2,'.',',' ) ?>
                </div>
            </div>
        </div>
        <div style="background:<?= $saldoBg ?>;padding:.6rem 1rem;text-align:center;border-top:1px solid #fdf2f8;">
            <span style="font-size:.8rem;font-weight:700;color:<?= $saldoColor ?>;"><?= $saldoMsg ?></span>
            <span style="font-size:.72rem;color:<?= $saldoColor ?>;opacity:.75;margin-left:.5rem;"><?= $saldoSub ?></span>
        </div>
    </div>
    <?php else: ?>
    <p style="font-size:.78rem;color:#94a3b8;margin-bottom:1rem;">Selecciona un trabajador específico para ver el cuadre del mes.</p>
    <?php endif; ?>
</div>
