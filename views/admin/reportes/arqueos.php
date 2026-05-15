<?php
$basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';

$f2  = fn($v) => 'S/ ' . number_format((float)$v, 2, '.', ',');
$fDif = function($v) {
    $v = (float)$v;
    if (abs($v) < 0.01) return '<span style="color:#059669;font-weight:700;">S/ 0.00</span>';
    $color = $v < 0 ? '#dc2626' : '#2563eb';
    $sign  = $v < 0 ? '−' : '+';
    return "<span style=\"color:{$color};font-weight:700;\">{$sign} S/ " . number_format(abs($v), 2, '.', ',') . "</span>";
};

$turnoLabel = [1 => '☀️ Mañana', 2 => '🌙 Tarde'];
$diasLabel  = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arqueos de Caja | Solo Boticas</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/caja.css">
    <link rel="icon" type="image/x-icon" href="<?= $basePath ?>/assets/img/logo.ico">
    <style>
        .rep-filtros { display:flex;gap:.6rem;flex-wrap:wrap;align-items:flex-end; }
        .rep-fg      { display:flex;flex-direction:column;gap:.25rem; }
        .rep-fg label{ font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#64748b; }

        .rep-kpis { display:grid;grid-template-columns:repeat(4,1fr);gap:.75rem;margin-bottom:1.25rem; }
        .rep-kpi  { background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:.85rem 1rem;text-align:center; }
        .rep-kpi__label { font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#64748b;margin-bottom:.3rem; }
        .rep-kpi__val   { font-size:1.5rem;font-weight:800;font-variant-numeric:tabular-nums; }
        .rep-kpi__sub   { font-size:.7rem;color:#64748b;margin-top:.15rem; }

        .badge-conforme  { background:#d1fae5;color:#065f46; }
        .badge-superavit { background:#eff6ff;color:#2563eb; }
        .badge-deficit   { background:#fee2e2;color:#991b1b; }

        @media(max-width:640px) { .rep-kpis { grid-template-columns:repeat(2,1fr); } }
        @media print {
            .caja-header,.rep-filtros,form,.no-print { display:none!important; }
            body { background:#fff; }
        }
    </style>
</head>
<body style="background:#f1f5f9;min-height:100vh;">

<header class="caja-header">
    <div class="caja-header__brand">
        <div class="caja-header__logo">SB</div>
        <div>
            <p class="caja-header__company">Grupo KGyR S.A.C</p>
            <p class="caja-header__app">Reporte — <strong>Arqueos de Caja</strong></p>
        </div>
    </div>
    <div class="caja-header__right">
        <span class="caja-header__user"><?= htmlspecialchars($userName) ?></span>
        <button onclick="window.print()" class="caja-btn-back no-print" style="cursor:pointer;">🖨 Imprimir</button>
        <a href="<?= $basePath ?>/admin/reportes" class="caja-btn-back">← Reportes</a>
    </div>
</header>

<main class="caja-main" style="max-width:1200px;">

    <!-- KPIs -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:1.25rem;">
        <div class="rep-kpi" style="border-left:4px solid #2563eb;">
            <p class="rep-kpi__label">Sobrante neto acumulado</p>
            <p class="rep-kpi__val" style="color:#2563eb;"><?= $f2($totalSobrante) ?></p>
            <p class="rep-kpi__sub"><?= $countSobrante ?> turno<?= $countSobrante !== 1 ? 's' : '' ?> con sobrante</p>
        </div>
        <div class="rep-kpi" style="border-left:4px solid #dc2626;">
            <p class="rep-kpi__label">Faltante neto acumulado</p>
            <p class="rep-kpi__val" style="color:#dc2626;"><?= $f2($totalFaltante) ?></p>
            <p class="rep-kpi__sub"><?= $countFaltante ?> turno<?= $countFaltante !== 1 ? 's' : '' ?> con faltante</p>
        </div>
    </div>

    <!-- Filtros -->
    <section class="caja-card no-print">
        <form method="GET" class="rep-filtros">
            <div class="rep-fg">
                <label>Desde</label>
                <input type="date" name="desde" class="caja-input" style="max-width:140px;"
                       value="<?= htmlspecialchars($filtroDesde) ?>">
            </div>
            <div class="rep-fg">
                <label>Hasta</label>
                <input type="date" name="hasta" class="caja-input" style="max-width:140px;"
                       value="<?= htmlspecialchars($filtroHasta) ?>">
            </div>
            <div class="rep-fg">
                <label>Caja</label>
                <select name="caja" class="caja-input" style="max-width:200px;">
                    <option value="0">Todas las cajas</option>
                    <?php foreach ($cajas as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $filtroCaja == $c['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['descripcion']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="rep-fg">
                <label>Resultado</label>
                <select name="resultado" class="caja-input" style="max-width:140px;">
                    <option value="">Todos</option>
                    <option value="SUPERAVIT" <?= $filtroResultado === 'SUPERAVIT' ? 'selected' : '' ?>>Superávit</option>
                    <option value="DEFICIT"   <?= $filtroResultado === 'DEFICIT'   ? 'selected' : '' ?>>Déficit</option>
                </select>
            </div>
            <div class="rep-fg" style="justify-content:flex-end;">
                <label>&nbsp;</label>
                <button type="submit" class="caja-btn caja-btn--primary">Filtrar</button>
            </div>
        </form>
    </section>

    <!-- Tabla -->
    <section class="caja-card" style="padding:1rem;">
        <p style="font-size:.75rem;color:#94a3b8;margin-bottom:.75rem;text-align:right;">
            <?= count($registros) ?> registro<?= count($registros) !== 1 ? 's' : '' ?>
            · <?= htmlspecialchars($filtroDesde) ?> — <?= htmlspecialchars($filtroHasta) ?>
        </p>
        <div class="caja-table-wrap">
            <table class="caja-table">
                <thead>
                    <tr>
                        <th>Sesión</th>
                        <th>Fecha</th>
                        <th>Local<br><small style="font-weight:400;color:#94a3b8;text-transform:none;letter-spacing:0;">P. venta</small></th>
                        <th>Turno</th>
                        <th>Respon-<br><small style="font-weight:400;color:#94a3b8;text-transform:none;letter-spacing:0;">sable</small></th>
                        <th>Vende-<br><small style="font-weight:400;color:#94a3b8;text-transform:none;letter-spacing:0;">dor/a</small></th>
                        <th class="text-center">Estado</th>
                        <th class="text-right">Resultado<br><small style="font-weight:400;color:#94a3b8;text-transform:none;letter-spacing:0;">neto</small></th>
                        <th class="text-right">Arqueo<br><small style="font-weight:400;color:#94a3b8;text-transform:none;letter-spacing:0;">bruto</small></th>
                        <th class="text-right">Fondo<br><small style="font-weight:400;color:#94a3b8;text-transform:none;letter-spacing:0;">día sig.</small></th>
                        <th class="text-center">Obser-<br><small style="font-weight:400;color:#94a3b8;text-transform:none;letter-spacing:0;">vación</small></th>
                        <th class="text-center no-print">Ver</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($registros)): ?>
                    <tr><td colspan="12" class="caja-table__empty">Sin registros para los filtros seleccionados.</td></tr>
                <?php endif; ?>
                <?php foreach ($registros as $r):
                    $diferencia = (float)($r['diferencia']     ?? 0);
                    $corrVentas = (float)($r['sum_corr_ventas'] ?? 0);
                    $difCorr    = $diferencia
                                + (float)($r['sum_rectifs'] ?? 0)
                                + (float)($r['sum_ajustes'] ?? 0)
                                - $corrVentas;
                    $dow        = $diasLabel[(int)date('w', strtotime($r['fecha_operacion']))];

                    if (abs($difCorr) < 0.01)    { $badgeCls = 'badge-conforme';  $label = 'Conforme'; }
                    elseif ($difCorr > 0.01)     { $badgeCls = 'badge-superavit'; $label = 'Superávit'; }
                    else                         { $badgeCls = 'badge-deficit';   $label = 'Déficit'; }
                ?>
                    <tr style="<?= $difCorr < -0.01 ? 'background:#fff8f8;' : '' ?>">
                        <td><code style="font-size:.72rem;color:#475569;">#<?= $r['id_sesion'] ?></code></td>
                        <td style="white-space:nowrap;">
                            <strong><?= $dow ?></strong>
                            <span style="display:block;font-size:.7rem;color:#64748b;">
                                <?= date('d/m/Y', strtotime($r['fecha_operacion'])) ?>
                            </span>
                        </td>
                        <td>
                            <span style="font-weight:600;"><?= htmlspecialchars($r['local_desc']) ?></span>
                            <span style="display:block;font-size:.7rem;color:#94a3b8;"><?= htmlspecialchars($r['caja_desc']) ?></span>
                        </td>
                        <td style="white-space:nowrap;font-size:.82rem;">
                            <?= $turnoLabel[$r['turno_id']] ?? htmlspecialchars($r['turno_desc']) ?>
                        </td>
                        <td style="font-size:.82rem;"><?= htmlspecialchars($r['cajera_nombre']) ?></td>
                        <td style="font-size:.82rem;">
                            <?= $r['vendedor_nombre'] ? htmlspecialchars($r['vendedor_nombre']) : '<span style="color:#cbd5e1">—</span>' ?>
                        </td>
                        <td class="text-center">
                            <?php if ($label !== '—'): ?>
                                <span class="badge <?= $badgeCls ?>" style="font-size:.7rem;"><?= $label ?></span>
                            <?php else: ?>
                                <span style="color:#94a3b8;">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-right">
                            <?php if (abs($difCorr) < 0.01): ?>
                                <span style="background:#d1fae5;color:#065f46;font-size:.7rem;font-weight:700;padding:2px 7px;border-radius:5px;">Resuelto</span>
                                <?php if (abs($corrVentas) > 0.01): ?>
                                    <?php $corrTxt = ($corrVentas > 0 ? '+' : '−') . ' S/ ' . number_format(abs($corrVentas), 2, '.', ','); ?>
                                    <span title="Venta corregida: <?= htmlspecialchars($corrTxt) ?>" style="font-size:.7rem;margin-left:3px;cursor:default;">✏</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <?= $fDif($difCorr) ?>
                                <?php if (abs($corrVentas) > 0.01): ?>
                                    <?php $corrTxt = ($corrVentas > 0 ? '+' : '−') . ' S/ ' . number_format(abs($corrVentas), 2, '.', ','); ?>
                                    <span title="Venta corregida: <?= htmlspecialchars($corrTxt) ?>" style="font-size:.7rem;margin-left:3px;cursor:default;">✏</span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td class="text-right" style="color:#94a3b8;"><?= $fDif($diferencia) ?></td>
                        <td class="text-right" style="font-variant-numeric:tabular-nums;">
                            <?= $r['base_siguiente'] !== null ? $f2($r['base_siguiente']) : '<span style="color:#94a3b8">—</span>' ?>
                        </td>
                        <td class="text-center">
                            <?php if ($r['por_responder']): ?>
                                <span style="background:#fef3c7;color:#92400e;font-size:.68rem;font-weight:700;padding:2px 7px;border-radius:5px;white-space:nowrap;">Por responder</span>
                            <?php elseif (!empty($r['comentario_cajera'])): ?>
                                <span style="background:#d1fae5;color:#065f46;font-size:.68rem;font-weight:700;padding:2px 7px;border-radius:5px;">Respondido</span>
                            <?php else: ?>
                                <span style="color:#cbd5e1;font-size:.8rem;">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center no-print">
                            <a href="<?= $basePath ?>/caja/reporte/<?= $r['id_sesion'] ?>"
                               class="caja-link" target="_blank">Ver</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

</main>
</body>
</html>
