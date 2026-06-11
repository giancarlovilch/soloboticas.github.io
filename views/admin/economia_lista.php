<?php
if (!isset($_SESSION['user_rol'])) exit('Acceso denegado');

extract($economiaDatos ?? []);
$ecoPagos            = $ecoPagos            ?? [];
$ecoTrabajadores     = $ecoTrabajadores     ?? [];
$ecoIngresos         = $ecoIngresos         ?? [];
$ecoTotalIngresos    = $ecoTotalIngresos    ?? 0.0;
$ecoTotalBonos       = $ecoTotalBonos       ?? 0.0;
$ecoMes              = $ecoMes              ?? date('Y-m');
$ecoMesActual        = $ecoMesActual        ?? date('Y-m');
$ecoPid              = $ecoPid              ?? 0;
$ecoEstudioInfo      = $ecoEstudioInfo      ?? null;
$ecoTarifasInfo      = $ecoTarifasInfo      ?? [];
$ecoBonosVInfo       = $ecoBonosVInfo       ?? [];
$ecoBonosOInfo       = $ecoBonosOInfo       ?? [];
$ecoBonoEstudioMonto  = $ecoBonoEstudioMonto  ?? 0.0;
$ecoBonoServicioMonto = $ecoBonoServicioMonto ?? 0.0;
$ecoNombreTrabajador  = $ecoNombreTrabajador  ?? '';
$ecoSupervisorPeriodos = $ecoSupervisorPeriodos ?? [];

$meses      = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
[$anioF, $nmesF] = explode('-', $ecoMes);
$mesLabel   = $meses[(int)$nmesF - 1] . ' ' . $anioF;
$ecoDesdeM  = "{$anioF}-{$nmesF}-01";
$ecoHastaM  = date('Y-m-t', strtotime($ecoDesdeM));
$diasLabel  = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
$turnoLabel = [1 => '☀️ Mañana', 2 => '🌙 Tarde'];
$f2 = fn($v) => 'S/ ' . number_format((float)$v, 2, '.', ',');

$tipoPagoInfo = [
    'PAGO_TOTAL' => ['label' => 'Pago total', 'bg' => '#d1fae5', 'color' => '#065f46'],
    'ADELANTO'   => ['label' => 'Adelanto',   'bg' => '#fef3c7', 'color' => '#92400e'],
    'DESCUENTO'  => ['label' => 'Descuento',  'bg' => '#fee2e2', 'color' => '#991b1b'],
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

$totalPagadoReal = 0.0;
foreach ($ecoPagos as $p) {
    if (in_array($p['estado'], ['PAGADO','CONFIRMADO_BENEFICIARIO','APROBADO'])) {
        if (($p['tipo_pago'] ?? '') !== 'DESCUENTO') $totalPagadoReal += (float)$p['monto'];
    } elseif ($p['estado'] === 'AJUSTE_CUADRE') {
        $totalPagadoReal += ($p['accion'] ?? '') === 'QUITAR' ? -(float)$p['monto'] : (float)$p['monto'];
    }
}
?>
<style>
/* ── Mismo CSS que staff/economia ── */
.eco-wrap { max-width:900px;margin:0 auto;padding:.5rem 0 3rem; }
.eco-nav  { display:flex;align-items:center;gap:.5rem;margin-bottom:1.25rem;flex-wrap:wrap; }
.eco-kpis { display:grid;grid-template-columns:repeat(4,1fr);gap:.65rem;margin-bottom:1.5rem; }
.eco-kpi  { background:#fff;border:1px solid #fbcfe8;border-radius:10px;padding:.75rem 1rem;text-align:center; }
.eco-kpi__num   { font-size:1.25rem;font-weight:800;color:#9d174d; }
.eco-kpi__label { font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#be185d;margin-top:2px; }
.eco-sec-title  { font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#be185d;margin:1.5rem 0 .65rem; }
.eco-table-wrap { overflow-x:auto; }
.eco-table { width:100%;border-collapse:collapse;font-size:.80rem;background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.06); }
.eco-table th { background:#fff0f6;padding:.5rem .75rem;font-size:.63rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#be185d;border-bottom:2px solid #fbcfe8;white-space:nowrap; }
.eco-table td { padding:.55rem .75rem;border-bottom:1px solid #fdf2f8;vertical-align:middle; }
.eco-table tr:last-child td { border-bottom:none; }
.eco-table tr:hover td { background:#fff8fc; }
.eco-table tfoot td { background:#fff0f6;font-weight:700;font-size:.82rem;color:#9d174d;border-top:2px solid #fbcfe8; }
.eco-badge { display:inline-block;font-size:.67rem;font-weight:700;padding:2px 8px;border-radius:20px;white-space:nowrap; }
.eco-sub   { font-size:.68rem;color:#94a3b8;display:block; }
.eco-monto { font-size:.95rem;font-weight:800;color:#9d174d;font-variant-numeric:tabular-nums; }
.eco-base  { font-weight:600;color:#0097A7;font-variant-numeric:tabular-nums; }
.eco-bono  { font-weight:700;color:#059669;font-variant-numeric:tabular-nums; }
.eco-zero  { color:#cbd5e1; }
.eco-empty { text-align:center;padding:2rem;color:#94a3b8; }
.eco-bonos-box { background:#fff;border:1.5px solid #fbcfe8;border-radius:12px;padding:1.1rem 1.25rem;margin-bottom:1.5rem; }
.eco-bonos-hd  { display:flex;align-items:center;justify-content:space-between;cursor:pointer;user-select:none; }
.eco-bonos-hd h3 { font-size:.82rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#9d174d;margin:0; }
.eco-bonos-body { margin-top:1rem; }
.eco-bonos-body.hidden { display:none; }
.eco-rol-grid { display:grid;grid-template-columns:repeat(3,1fr);gap:.65rem;margin-bottom:1rem; }
.eco-rol-card { background:#fff0f6;border:1px solid #fbcfe8;border-radius:9px;padding:.75rem .9rem;text-align:center; }
.eco-rol-card__num   { font-size:1.2rem;font-weight:800;color:#9d174d; }
.eco-rol-card__label { font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#be185d;margin-top:2px; }
.eco-bono-group { margin-bottom:.9rem; }
.eco-bono-group__title { font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#64748b;margin-bottom:.4rem; }
.eco-bono-table { width:100%;border-collapse:collapse;font-size:.78rem; }
.eco-bono-table th { background:#f8fafc;padding:.3rem .65rem;font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#64748b;border-bottom:1.5px solid #f1f5f9;text-align:left; }
.eco-bono-table td { padding:.35rem .65rem;border-bottom:1px solid #f8fafc;vertical-align:middle; }
.eco-bono-table tr:last-child td { border-bottom:none; }
.eco-bono-monto { font-weight:700;color:#059669; }
@media(max-width:600px){ .eco-kpis{grid-template-columns:repeat(2,1fr);} }
</style>

<div class="eco-wrap">

    <!-- Sección informativa -->
    <div class="eco-bonos-box">
        <div class="eco-bonos-hd" onclick="toggleBonosAdmin(this)">
            <h3>Cómo se calculan los ingresos</h3>
            <span id="bonosAdminChevron">▼</span>
        </div>
        <div class="eco-bonos-body hidden" id="bonosAdminBody">
            <div class="eco-rol-grid" style="margin-top:.5rem;">
                <?php foreach (['CAJERA' => 'Cajera', 'VENDEDORA' => 'Vendedora', 'ALMACENERA' => 'Almacenera'] as $cod => $label):
                    $t = $ecoTarifasInfo[$cod] ?? null; ?>
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
            <?php if (!empty($ecoBonosVInfo)): ?>
            <div class="eco-bono-group">
                <div class="eco-bono-group__title">Bono por ventas — Vendedora</div>
                <table class="eco-bono-table">
                    <thead><tr><th>Desde</th><th>Hasta</th><th>Bono</th></tr></thead>
                    <tbody>
                        <?php foreach ($ecoBonosVInfo as $b): ?>
                        <tr>
                            <td>S/ <?= number_format((float)$b['desde'], 2, '.', ',') ?></td>
                            <td><?= $b['hasta'] !== null ? 'S/ '.number_format((float)$b['hasta'],2,'.',',' ) : 'Sin límite' ?></td>
                            <td class="eco-bono-monto">S/ <?= number_format((float)$b['monto_bono'], 2, '.', ',') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
            <?php if (!empty($ecoBonosOInfo)): ?>
            <div class="eco-bono-group">
                <div class="eco-bono-group__title">Bono por operaciones BCP — Cajera</div>
                <table class="eco-bono-table">
                    <thead><tr><th>Desde</th><th>Hasta</th><th>Bono</th></tr></thead>
                    <tbody>
                        <?php foreach ($ecoBonosOInfo as $b): ?>
                        <tr>
                            <td><?= (int)$b['desde'] ?> ops</td>
                            <td><?= $b['hasta'] !== null ? (int)$b['hasta'].' ops' : 'Sin límite' ?></td>
                            <td class="eco-bono-monto">S/ <?= number_format((float)$b['monto_bono'], 2, '.', ',') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
            <div class="eco-bono-group" style="margin-top:.5rem;">
                <div class="eco-bono-group__title">Bono por tiempo de servicio</div>
                <p style="font-size:.72rem;color:#64748b;margin-bottom:.4rem;">
                    S/ 0.20 × meses completos en la empresa, contados desde el inicio del mes consultado.
                </p>
                <?php if ($ecoPid && $ecoBonoServicioMonto > 0): ?>
                <p style="font-size:.72rem;color:#065f46;background:#d1fae5;border-radius:6px;padding:.3rem .6rem;">
                    ✓ <?= htmlspecialchars($ecoNombreTrabajador) ?> · S/ <?= number_format($ecoBonoServicioMonto, 2) ?> por turno
                </p>
                <?php elseif ($ecoPid): ?>
                <p style="font-size:.72rem;color:#64748b;">Fecha de ingreso no registrada para este trabajador.</p>
                <?php endif; ?>
                <?php
                $ecoSupervisorActivo = null;
                foreach ($ecoSupervisorPeriodos as $per) {
                    if ($per['fecha_desde'] <= $ecoHastaM && ($per['fecha_hasta'] === null || $per['fecha_hasta'] >= $ecoDesdeM)) {
                        $ecoSupervisorActivo = $per;
                        break;
                    }
                }
                ?>
                <?php if ($ecoPid && $ecoSupervisorActivo): ?>
                <p style="font-size:.72rem;color:#065f46;background:#d1fae5;border-radius:6px;padding:.3rem .6rem;margin-top:.4rem;">
                    ✓ Pago por supervisión · <?= htmlspecialchars($ecoNombreTrabajador) ?> · Del <?= date('d/m/Y', strtotime($ecoSupervisorActivo['fecha_desde'])) ?>
                    <?= $ecoSupervisorActivo['fecha_hasta'] ? 'al '.date('d/m/Y', strtotime($ecoSupervisorActivo['fecha_hasta'])) : '(indefinido)' ?>
                    · S/ <?= number_format((float)$ecoSupervisorActivo['monto_dia'], 2) ?> adicionales por turno trabajado
                </p>
                <?php endif; ?>
            </div>

            <div class="eco-bono-group" style="margin-top:.5rem;">
                <div class="eco-bono-group__title">Bono por estudios</div>
                <table class="eco-bono-table">
                    <thead><tr><th>Nivel</th><th>Estado</th><th>Bono por turno</th></tr></thead>
                    <tbody>
                        <tr><td>Universitario</td><td>En curso / Trunco</td><td class="eco-bono-monto">S/ 3.00</td></tr>
                        <tr><td>Universitario</td><td>Egreso / Titulado</td><td class="eco-bono-monto">S/ 6.00</td></tr>
                        <tr><td>Técnico</td><td>En curso / Trunco</td><td class="eco-bono-monto">S/ 2.00</td></tr>
                        <tr><td>Técnico</td><td>Egreso / Titulado</td><td class="eco-bono-monto">S/ 4.00</td></tr>
                    </tbody>
                </table>
                <?php if ($ecoPid && $ecoEstudioInfo): ?>
                <p style="font-size:.72rem;margin-top:.4rem;color:#065f46;background:#d1fae5;border-radius:6px;padding:.3rem .6rem;">
                    ✓ <?= htmlspecialchars($ecoNombreTrabajador) ?> · <?= htmlspecialchars($ecoEstudioInfo['tipo_desc']) ?> — <?= htmlspecialchars($ecoEstudioInfo['estado_desc']) ?> · S/ <?= number_format($ecoBonoEstudioMonto, 2) ?> por turno
                </p>
                <?php elseif ($ecoPid): ?>
                <p style="font-size:.72rem;margin-top:.4rem;color:#64748b;">No aplica al perfil de este trabajador.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Filtro: trabajador + mes + badge educativo -->
    <div class="eco-nav">
        <form method="GET" style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;">
            <input type="hidden" name="page" value="economia">
            <select name="trabajador" onchange="this.form.submit()"
                    style="padding:.35rem .7rem;border-radius:8px;border:1.5px solid #fbcfe8;
                           background:#fff0f6;color:#9d174d;font-size:.85rem;font-weight:600;outline:none;cursor:pointer;min-width:180px;">
                <option value="0">Todos los trabajadores</option>
                <?php foreach ($ecoTrabajadores as $t): ?>
                    <option value="<?= $t['id'] ?>" <?= $ecoPid == $t['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($t['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="month" name="mes" value="<?= htmlspecialchars($ecoMes) ?>"
                   max="<?= $ecoMesActual ?>" onchange="this.form.submit()"
                   style="padding:.35rem .7rem;border-radius:8px;border:1.5px solid #fbcfe8;
                          background:#fff0f6;color:#9d174d;font-size:.85rem;font-weight:600;
                          cursor:pointer;outline:none;">
        </form>
        <?php if ($ecoPid && $ecoEstudioInfo): ?>
        <span style="display:inline-flex;align-items:center;gap:.3rem;padding:.3rem .75rem;
                     border-radius:20px;background:#d1fae5;border:1px solid #6ee7b7;
                     font-size:.75rem;font-weight:700;color:#065f46;">
            🎓 <?= htmlspecialchars($ecoEstudioInfo['tipo_desc']) ?> · <?= htmlspecialchars($ecoEstudioInfo['estado_desc']) ?>
        </span>
        <?php elseif ($ecoPid): ?>
        <span style="display:inline-flex;align-items:center;padding:.3rem .75rem;
                     border-radius:20px;background:#f1f5f9;border:1px solid #e2e8f0;
                     font-size:.75rem;font-weight:700;color:#64748b;">Sin categoría</span>
        <?php endif; ?>
    </div>

    <!-- KPIs -->
    <div class="eco-kpis">
        <div class="eco-kpi" style="background:#fef9c3;border-color:#fde047;">
            <div class="eco-kpi__num" style="color:#854d0e;"><?= $f2($ecoTotalIngresos) ?></div>
            <div class="eco-kpi__label" style="color:#a16207;">Ingresos del mes</div>
        </div>
        <div class="eco-kpi">
            <div class="eco-kpi__num" style="color:#059669;"><?= $f2($ecoTotalBonos) ?></div>
            <div class="eco-kpi__label">Bonos acumulados</div>
        </div>
        <div class="eco-kpi">
            <div class="eco-kpi__num"><?= count($ecoIngresos) ?></div>
            <div class="eco-kpi__label">Turnos trabajados</div>
        </div>
        <div class="eco-kpi">
            <div class="eco-kpi__num"><?= $f2($totalPagadoReal) ?></div>
            <div class="eco-kpi__label">Pagos recibidos</div>
        </div>
    </div>

    <!-- Tabla pagos -->
    <p class="eco-sec-title">Pagos recibidos</p>
    <?php if (empty($ecoPagos)): ?>
    <div class="eco-empty">Sin pagos registrados en <?= $mesLabel ?>.</div>
    <?php else: ?>
    <div class="eco-table-wrap">
        <table class="eco-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <?php if (!$ecoPid): ?><th>Beneficiario</th><?php endif; ?>
                    <th>Turno · Local</th>
                    <th>Tipo</th>
                    <th class="text-center">Estado</th>
                    <th>Registrado por</th>
                    <th class="text-right">Monto</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($ecoPagos as $p):
                $esAjuste = ($p['estado'] === 'AJUSTE_CUADRE');
                $fecha    = $p['fecha_operacion'] ?? $p['fecha_pago'];
                $dow      = $diasLabel[(int)date('w', strtotime($fecha))];
                if (!$esAjuste):
                    $tInfo = $tipoPagoInfo[$p['tipo_pago']] ?? ['label'=>$p['tipo_pago'],'bg'=>'#f1f5f9','color'=>'#475569'];
                    $eInfo = $estadoInfo[$p['estado']]      ?? ['label'=>$p['estado'],   'bg'=>'#f1f5f9','color'=>'#475569'];
                else:
                    $esReversion = ($p['accion'] ?? '') === 'QUITAR';
                    $tBg    = $esReversion ? '#ffedd5' : '#e0f2fe';
                    $tColor = $esReversion ? '#9a3412' : '#0369a1';
                    $tLabel = $esReversion ? 'Revertido' : 'Pago via cuadre';
                    $montoColor = $esReversion ? '#991b1b' : '#0369a1';
                endif;
            ?>
            <tr>
                <td style="white-space:nowrap;">
                    <strong style="color:#1e293b;"><?= $dow ?></strong>
                    <span class="eco-sub"><?= date('d/m/Y', strtotime($fecha)) ?></span>
                </td>
                <?php if (!$ecoPid): ?>
                <td style="font-weight:600;font-size:.82rem;color:#1e293b;"><?= htmlspecialchars($p['beneficiario_nombre']) ?></td>
                <?php endif; ?>
                <td>
                    <span style="font-size:.8rem;"><?= $turnoLabel[$p['turno_id']] ?? '—' ?></span>
                    <span class="eco-sub"><?= htmlspecialchars($p['local_desc']) ?></span>
                </td>
                <?php if (!$esAjuste): ?>
                <td><span class="eco-badge" style="background:<?= $tInfo['bg'] ?>;color:<?= $tInfo['color'] ?>"><?= $tInfo['label'] ?></span></td>
                <td class="text-center"><span class="eco-badge" style="background:<?= $eInfo['bg'] ?>;color:<?= $eInfo['color'] ?>"><?= $eInfo['label'] ?></span></td>
                <td style="font-size:.78rem;color:#475569;"><?= htmlspecialchars($p['emisor_nombre']) ?></td>
                <td class="text-right"><span class="eco-monto"><?= $f2($p['monto']) ?></span></td>
                <?php else: ?>
                <td><span class="eco-badge" style="background:<?= $tBg ?>;color:<?= $tColor ?>"><?= $tLabel ?></span></td>
                <td class="text-center"><span class="eco-badge" style="background:#f1f5f9;color:#475569;"><?= htmlspecialchars($p['numero_operacion'] ?? '') ?></span></td>
                <td style="font-size:.78rem;color:#475569;"><?= htmlspecialchars($p['emisor_nombre']) ?></td>
                <td class="text-right"><span class="eco-monto" style="color:<?= $montoColor ?>"><?= $esReversion ? '− ' : '' ?><?= $f2($p['monto']) ?></span></td>
                <?php endif; ?>
            </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="<?= $ecoPid ? 5 : 6 ?>" class="text-right" style="font-size:.75rem;">Total pagado · <?= $mesLabel ?></td>
                    <td class="text-right"><?= $f2($totalPagadoReal) ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <?php endif; ?>

    <!-- Tabla ingresos -->
    <p class="eco-sec-title">Ingresos por turno trabajado</p>
    <?php if (empty($ecoIngresos)): ?>
    <div class="eco-empty">Sin turnos registrados en <?= $mesLabel ?>.</div>
    <?php else: ?>
    <div class="eco-table-wrap">
        <table class="eco-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <?php if (!$ecoPid): ?><th>Trabajador</th><?php endif; ?>
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
            <?php foreach ($ecoIngresos as $ing):
                $dow = $diasLabel[(int)date('w', strtotime($ing['fecha_dia']))];
            ?>
            <tr>
                <td style="white-space:nowrap;">
                    <strong style="color:#1e293b;"><?= $dow ?></strong>
                    <span class="eco-sub"><?= date('d/m/Y', strtotime($ing['fecha_dia'])) ?></span>
                </td>
                <?php if (!$ecoPid): ?>
                <td style="font-weight:600;font-size:.82rem;"><?= htmlspecialchars($ing['trabajador_nombre']) ?></td>
                <?php endif; ?>
                <td>
                    <span style="font-size:.8rem;"><?= $turnoLabel[$ing['turno_id']] ?? '—' ?></span>
                    <span class="eco-sub"><?= htmlspecialchars($ing['local_desc']) ?></span>
                </td>
                <td style="font-size:.78rem;font-weight:600;color:#475569;"><?= htmlspecialchars($ing['rol_desc']) ?></td>
                <td class="text-right"><span class="eco-base"><?= $f2($ing['base']) ?></span></td>
                <td class="text-right">
                    <?= $ing['bono_v'] > 0 ? '<span class="eco-bono">'.$f2($ing['bono_v']).'</span>' : '<span class="eco-zero">—</span>' ?>
                </td>
                <td class="text-right">
                    <?= $ing['bono_o'] > 0 ? '<span class="eco-bono">'.$f2($ing['bono_o']).'</span>' : '<span class="eco-zero">—</span>' ?>
                </td>
                <td class="text-right">
                    <?= ($ing['bono_e'] ?? 0) > 0 ? '<span class="eco-bono">'.$f2($ing['bono_e']).'</span>' : '<span class="eco-zero">—</span>' ?>
                </td>
                <td class="text-right">
                    <?= ($ing['bono_s'] ?? 0) > 0 ? '<span class="eco-bono">'.$f2($ing['bono_s']).'</span>' : '<span class="eco-zero">—</span>' ?>
                </td>
                <td class="text-right"><span class="eco-monto"><?= $f2($ing['total']) ?></span></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="<?= $ecoPid ? 3 : 4 ?>" class="text-right" style="font-size:.75rem;">Subtotal · <?= $mesLabel ?></td>
                    <td class="text-right"><?= $f2(array_sum(array_column($ecoIngresos,'base'))) ?></td>
                    <td class="text-right"><?= $f2(array_sum(array_column($ecoIngresos,'bono_v'))) ?></td>
                    <td class="text-right"><?= $f2(array_sum(array_column($ecoIngresos,'bono_o'))) ?></td>
                    <td class="text-right"><?= $f2(array_sum(array_column($ecoIngresos,'bono_e'))) ?></td>
                    <td class="text-right"><?= $f2(array_sum(array_column($ecoIngresos,'bono_s'))) ?></td>
                    <td class="text-right"><?= $f2($ecoTotalIngresos) ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <?php endif; ?>

</div>

<script>
function toggleBonosAdmin(hd) {
    const body    = document.getElementById('bonosAdminBody');
    const chevron = document.getElementById('bonosAdminChevron');
    const open    = !body.classList.contains('hidden');
    body.classList.toggle('hidden', open);
    chevron.textContent = open ? '▼' : '▲';
}
</script>
