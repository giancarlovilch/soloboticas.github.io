<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Arqueos | Solo Boticas</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/caja.css">
    <link rel="icon" type="image/x-icon" href="<?= $basePath ?>/assets/img/logo.ico">
    <style>
        .rep-filtros {
            display: flex; gap: .6rem; flex-wrap: wrap; align-items: flex-end;
        }
        .rep-filtro-group { display: flex; flex-direction: column; gap: .25rem; }
        .rep-filtro-group label { font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #64748b; }

        .rep-kpis { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }
        .rep-kpi  {
            background: #fff; border: 1px solid #e2e8f0; border-radius: 10px;
            padding: 1rem 1.25rem; text-align: center;
        }
        .rep-kpi__label { font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #64748b; margin-bottom: .3rem; }
        .rep-kpi__val   { font-size: 1.5rem; font-weight: 800; font-variant-numeric: tabular-nums; }
        .rep-kpi--ok    .rep-kpi__val { color: #059669; }
        .rep-kpi--warn  .rep-kpi__val { color: #d97706; }
        .rep-kpi--bad   .rep-kpi__val { color: #dc2626; }

        .badge-conforme  { background: #d1fae5; color: #065f46; }
        .badge-superavit { background: #eff6ff; color: #2563eb; }
        .badge-deficit   { background: #fee2e2; color: #991b1b; }

        @media (max-width: 640px) {
            .rep-kpis { grid-template-columns: 1fr; }
        }
        @media print {
            .caja-header, .rep-filtros, form, .no-print { display: none !important; }
            body { background: #fff; }
        }
    </style>
</head>
<body style="background:#f1f5f9;min-height:100vh;">
<?php
$f2  = fn($v) => 'S/ ' . number_format((float)$v, 2, '.', ',');
$fDif = function($v) {
    $v = (float)$v;
    if (abs($v) < 0.01) return '<span style="color:#059669;font-weight:700;">S/ 0.00</span>';
    $color = $v < 0 ? '#dc2626' : '#2563eb';
    $sign  = $v < 0 ? '−' : '+';
    return "<span style=\"color:{$color};font-weight:700;\">{$sign} S/ " . number_format(abs($v), 2, '.', ',') . "</span>";
};
?>

<header class="caja-header">
    <div class="caja-header__brand">
        <div class="caja-header__logo">SB</div>
        <div>
            <p class="caja-header__company">Grupo KGyR S.A.C</p>
            <p class="caja-header__app">Reporte — <strong>Resultado de Arqueos</strong></p>
        </div>
    </div>
    <div class="caja-header__right">
        <span class="caja-header__user"><?= htmlspecialchars($userName) ?></span>
        <button onclick="window.print()" class="caja-btn-back no-print" style="cursor:pointer;">🖨 Imprimir</button>
        <a href="<?= $basePath ?>/admin/reportes" class="caja-btn-back">← Reportes</a>
    </div>
</header>

<main class="caja-main" style="max-width:1100px;">

    <!-- ── KPIs rápidos ─── -->
    <div class="rep-kpis">
        <div class="rep-kpi rep-kpi--ok">
            <p class="rep-kpi__label">Conformes</p>
            <p class="rep-kpi__val"><?= $countConforme ?></p>
        </div>
        <div class="rep-kpi rep-kpi--warn">
            <p class="rep-kpi__label">Superávit acum.</p>
            <p class="rep-kpi__val"><?= $f2($totalSobrante) ?></p>
            <p style="font-size:0.72rem;color:#64748b;"><?= $countSobrante ?> turno<?= $countSobrante !== 1 ? 's' : '' ?></p>
        </div>
        <div class="rep-kpi rep-kpi--bad">
            <p class="rep-kpi__label">Déficit acum.</p>
            <p class="rep-kpi__val"><?= $f2($totalFaltante) ?></p>
            <p style="font-size:0.72rem;color:#64748b;"><?= $countFaltante ?> turno<?= $countFaltante !== 1 ? 's' : '' ?></p>
        </div>
    </div>

    <!-- ── Filtros ─── -->
    <section class="caja-card no-print">
        <form method="GET" class="rep-filtros">
            <div class="rep-filtro-group">
                <label>Desde</label>
                <input type="date" name="desde" class="caja-input" style="max-width:140px;"
                       value="<?= htmlspecialchars($filtroDesde) ?>">
            </div>
            <div class="rep-filtro-group">
                <label>Hasta</label>
                <input type="date" name="hasta" class="caja-input" style="max-width:140px;"
                       value="<?= htmlspecialchars($filtroHasta) ?>">
            </div>
            <div class="rep-filtro-group">
                <label>Caja</label>
                <select name="caja" class="caja-input" style="max-width:200px;">
                    <option value="0">Todas</option>
                    <?php foreach ($cajas as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $filtroCaja == $c['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['descripcion']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="rep-filtro-group">
                <label>Resultado</label>
                <select name="resultado" class="caja-input" style="max-width:140px;">
                    <option value="">Todos</option>
                    <option value="SUPERAVIT" <?= $filtroResultado === 'SUPERAVIT' ? 'selected' : '' ?>>Superávit</option>
                    <option value="DEFICIT"   <?= $filtroResultado === 'DEFICIT'   ? 'selected' : '' ?>>Déficit</option>
                </select>
            </div>
            <div class="rep-filtro-group" style="justify-content:flex-end;">
                <label>&nbsp;</label>
                <button type="submit" class="caja-btn caja-btn--primary">Filtrar</button>
            </div>
        </form>
    </section>

    <!-- ── Tabla ─── -->
    <section class="caja-card" style="padding:1rem;">
        <p style="font-size:0.75rem;color:#94a3b8;margin-bottom:.75rem;text-align:right;">
            <?= count($registros) ?> registro<?= count($registros) !== 1 ? 's' : '' ?>
        </p>
        <div class="caja-table-wrap">
            <table class="caja-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Fecha</th>
                        <th>Local / Caja</th>
                        <th>Turno</th>
                        <th>Cajera</th>
                        <th>Vendedor/a</th>
                        <th class="text-center">Resultado</th>
                        <th class="text-right">Diferencia</th>
                        <th class="text-right">Con corrección</th>
                        <th class="text-right">Base siguiente</th>
                        <th class="text-center">Comentario</th>
                        <th class="text-center no-print">Ver</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($registros)): ?>
                    <tr><td colspan="12" class="caja-table__empty">Sin registros para los filtros seleccionados.</td></tr>
                <?php endif; ?>
                <?php foreach ($registros as $r):
                    $resultado = $r['resultado_cuadre'] ?? '—';
                    $diferencia = (float)($r['diferencia'] ?? 0);
                    if ($resultado === 'CONSISTENTE') {
                        $badgeCls = 'badge-conforme'; $label = 'Conforme';
                    } elseif ($resultado === 'SOBRANTE') {
                        $badgeCls = 'badge-superavit'; $label = 'Superávit';
                    } elseif ($resultado === 'FALTANTE') {
                        $badgeCls = 'badge-deficit'; $label = 'Déficit';
                    } else {
                        $badgeCls = ''; $label = '—';
                    }
                ?>
                    <tr>
                        <td><code style="font-size:0.73rem;color:#475569;">#<?= $r['id_sesion'] ?></code></td>
                        <td>
                            <?= date('d/m/Y', strtotime($r['fecha_operacion'])) ?>
                            <span style="display:block;font-size:0.7rem;color:#94a3b8;">
                                <?= $r['fecha_apertura'] ? date('H:i', strtotime($r['fecha_apertura'])) : '' ?>
                            </span>
                        </td>
                        <td>
                            <?= htmlspecialchars($r['local_desc']) ?>
                            <span style="display:block;font-size:0.72rem;color:#94a3b8;">
                                <?= htmlspecialchars($r['caja_desc']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($r['turno_desc']) ?></td>
                        <td><?= htmlspecialchars($r['cajera_nombre']) ?></td>
                        <td><?= $r['vendedor_nombre'] ? htmlspecialchars($r['vendedor_nombre']) : '<span style="color:#cbd5e1">—</span>' ?></td>
                        <td class="text-center">
                            <?php if ($label !== '—'): ?>
                                <span class="badge <?= $badgeCls ?>" style="font-size:0.7rem;"><?= $label ?></span>
                            <?php else: ?>
                                <span style="color:#94a3b8;">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-right"><?= $fDif($diferencia) ?></td>
                        <td class="text-right">
                            <?php
                                $difCorr = $diferencia + (float)($r['sum_rectifs'] ?? 0) - (float)($r['sum_ajustes'] ?? 0);
                                if (abs($difCorr) < 0.01):
                            ?>
                                <span style="background:#d1fae5;color:#065f46;font-size:.7rem;font-weight:700;padding:2px 7px;border-radius:5px;">Resuelto</span>
                            <?php else: ?>
                                <?= $fDif($difCorr) ?>
                            <?php endif; ?>
                        </td>
                        <td class="text-right" style="font-variant-numeric:tabular-nums;">
                            <?= $r['base_siguiente'] !== null ? $f2($r['base_siguiente']) : '<span style="color:#94a3b8">—</span>' ?>
                        </td>
                        <td class="text-center">
                            <?php if ($r['por_responder']): ?>
                                <span style="background:#fef3c7;color:#92400e;font-size:0.68rem;font-weight:700;padding:2px 7px;border-radius:5px;white-space:nowrap;">
                                    Por responder
                                </span>
                            <?php elseif (!empty($r['comentario_cajera'])): ?>
                                <span style="background:#d1fae5;color:#065f46;font-size:0.68rem;font-weight:700;padding:2px 7px;border-radius:5px;">
                                    Respondido
                                </span>
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
