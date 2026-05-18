<?php
$opsJson     = json_encode(['labels' => $opsDateLabels,    'datasets' => $opsDatasets],    JSON_UNESCAPED_UNICODE);
$ventasJson  = json_encode(['labels' => $ventasDateLabels, 'datasets' => $ventasDatasets], JSON_UNESCAPED_UNICODE);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gráficas Operacionales | Solo Boticas</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/caja.css">
    <link rel="icon" type="image/x-icon" href="<?= $basePath ?>/assets/img/logo.ico">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        .grf-main   { max-width: 1200px; margin: 0 auto; padding: 1.5rem 1.25rem 3rem; }
        .grf-filter { display:flex; align-items:center; gap:.75rem; flex-wrap:wrap;
                      background:#fff; border:1.5px solid #e2e8f0; border-radius:12px;
                      padding:.9rem 1.25rem; margin-bottom:1.5rem; }
        .grf-filter label { font-size:.78rem; font-weight:700; color:#64748b;
                            text-transform:uppercase; letter-spacing:.05em; }
        .grf-filter input[type=date] { border:1.5px solid #e2e8f0; border-radius:8px;
                                        padding:.35rem .65rem; font-size:.85rem; color:#1e293b;
                                        outline:none; cursor:pointer; }
        .grf-filter input[type=date]:focus { border-color:#0097A7; }
        .grf-filter button { background:#0097A7; color:#fff; border:none; border-radius:8px;
                              padding:.4rem 1.1rem; font-size:.83rem; font-weight:600; cursor:pointer; }
        .grf-filter button:hover { background:#00838f; }
        .grf-kpis   { display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr));
                      gap:1rem; margin-bottom:1.5rem; }
        .grf-kpi    { background:#fff; border:1.5px solid #e2e8f0; border-radius:12px;
                      padding:1.1rem 1.25rem; }
        .grf-kpi__lbl  { font-size:.72rem; font-weight:700; text-transform:uppercase;
                          letter-spacing:.06em; color:#64748b; margin-bottom:.3rem; }
        .grf-kpi__val  { font-size:1.6rem; font-weight:800; color:#1e293b; }
        .grf-kpi__sub  { font-size:.78rem; color:#94a3b8; margin-top:.2rem; }
        .grf-chart-box { background:#fff; border:1.5px solid #e2e8f0; border-radius:14px;
                         padding:1.5rem; margin-bottom:1.5rem; }
        .grf-chart-box h2 { font-size:.95rem; font-weight:700; color:#1e293b; margin-bottom:1rem; }
        .grf-chart-wrap { position:relative; height:640px; }
        @media print { .no-print { display:none !important; } body { background:#fff; } }
    </style>
</head>
<body style="background:#f1f5f9;min-height:100vh;">

<header class="caja-header no-print">
    <div class="caja-header__brand">
        <div class="caja-header__logo">SB</div>
        <div>
            <p class="caja-header__company">Grupo KGyR S.A.C</p>
            <p class="caja-header__app">Reportes — <strong>Gráficas</strong></p>
        </div>
    </div>
    <div class="caja-header__right">
        <span class="caja-header__user"><?= htmlspecialchars($userName) ?></span>
        <button onclick="window.print()" class="caja-btn" style="font-size:.8rem;padding:.35rem .9rem;">Imprimir</button>
        <a href="<?= $basePath ?>/admin/reportes" class="caja-btn-back">← Reportes</a>
    </div>
</header>

<main class="grf-main">

    <div style="margin-bottom:1.25rem;">
        <p style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#0097A7;margin-bottom:4px;">
            Análisis visual
        </p>
        <h1 style="font-size:1.4rem;font-weight:800;color:#1e293b;">Gráficas Operacionales</h1>
    </div>

    <!-- Filtros -->
    <form method="get" class="grf-filter no-print">
        <label>Desde</label>
        <input type="date" name="desde" value="<?= htmlspecialchars($desde) ?>">
        <label>Hasta</label>
        <input type="date" name="hasta" value="<?= htmlspecialchars($hasta) ?>">
        <button type="submit">Aplicar</button>
        <a href="?" style="font-size:.82rem;color:#64748b;text-decoration:none;margin-left:.25rem;">Últimos 14 días</a>
    </form>

    <!-- KPIs -->
    <div class="grf-kpis">
        <div class="grf-kpi">
            <div class="grf-kpi__lbl">Total operaciones BCP</div>
            <div class="grf-kpi__val"><?= number_format($totalOps) ?></div>
            <div class="grf-kpi__sub">en el período seleccionado</div>
        </div>
        <div class="grf-kpi">
            <div class="grf-kpi__lbl">Promedio diario BCP</div>
            <div class="grf-kpi__val"><?= number_format($promOps) ?></div>
            <div class="grf-kpi__sub">operaciones por día</div>
        </div>
        <div class="grf-kpi">
            <div class="grf-kpi__lbl">Total ventas</div>
            <div class="grf-kpi__val">S/ <?= number_format($totalVentas, 2) ?></div>
            <div class="grf-kpi__sub">en el período seleccionado</div>
        </div>
        <div class="grf-kpi">
            <div class="grf-kpi__lbl">Promedio diario ventas</div>
            <div class="grf-kpi__val">S/ <?= number_format($promVentas, 2) ?></div>
            <div class="grf-kpi__sub">por día</div>
        </div>
    </div>

    <!-- Gráfica 1: Operaciones BCP -->
    <div class="grf-chart-box">
        <h2>Operaciones Agente BCP por día
            <span style="font-size:.78rem;font-weight:400;color:#94a3b8;margin-left:.5rem;">
                <?= htmlspecialchars($desde) ?> — <?= htmlspecialchars($hasta) ?>
            </span>
        </h2>
        <?php if (empty($opsDateLabels)): ?>
            <p style="color:#94a3b8;font-size:.85rem;text-align:center;padding:2rem 0;">Sin datos en el período seleccionado.</p>
        <?php else: ?>
            <div class="grf-chart-wrap"><canvas id="chartOps"></canvas></div>
        <?php endif; ?>
    </div>

    <!-- Gráfica 2: Ventas diarias -->
    <div class="grf-chart-box">
        <h2>Ventas diarias por caja
            <span style="font-size:.78rem;font-weight:400;color:#94a3b8;margin-left:.5rem;">
                <?= htmlspecialchars($desde) ?> — <?= htmlspecialchars($hasta) ?>
            </span>
        </h2>
        <?php if (empty($ventasDateLabels)): ?>
            <p style="color:#94a3b8;font-size:.85rem;text-align:center;padding:2rem 0;">Sin datos en el período seleccionado.</p>
        <?php else: ?>
            <div class="grf-chart-wrap"><canvas id="chartVentas"></canvas></div>
        <?php endif; ?>
    </div>

</main>

<script>
const opsData    = <?= $opsJson ?>;
const ventasData = <?= $ventasJson ?>;

function fmtFecha(str) {
    const d = new Date(str + 'T00:00:00');
    return d.getDate() + '/' + (d.getMonth() + 1);
}

// Plugin: resalta la línea más cercana al cursor, atenúa las demás
function makeHighlightPlugin(origColors) {
    return {
        id: 'lineHighlight',
        afterEvent(chart, args) {
            const type = args.event.type;
            if (type !== 'mousemove' && type !== 'mouseout') return;

            let hoveredIdx = -1;
            if (type === 'mousemove') {
                const els = chart.getElementsAtEventForMode(
                    args.event.native, 'nearest', { intersect: false }, false
                );
                hoveredIdx = els.length ? els[0].datasetIndex : -1;
            }

            let dirty = false;
            chart.data.datasets.forEach((ds, i) => {
                const dimmed   = hoveredIdx !== -1 && i !== hoveredIdx;
                const newColor = dimmed ? origColors[i] + '28' : origColors[i];
                const newWidth = dimmed ? 1 : (i === 0 ? 3 : 2);
                const newPtR   = dimmed ? 1 : 4;
                if (ds.borderColor !== newColor || ds.borderWidth !== newWidth) {
                    ds.borderColor = newColor;
                    ds.borderWidth = newWidth;
                    ds.pointRadius = newPtR;
                    dirty = true;
                }
            });
            if (dirty) chart.update('none');
        }
    };
}

function buildChart(canvasId, data, yPrefix) {
    // Todos ocultos por defecto — el usuario destilcha uno a uno
    data.datasets.forEach(ds => { ds.hidden = true; });

    const origColors = data.datasets.map(ds => ds.borderColor);
    const plugin     = makeHighlightPlugin(origColors);

    const restore = (chart) => {
        chart.data.datasets.forEach((ds, i) => {
            ds.borderColor = origColors[i];
            ds.borderWidth = i === 0 ? 3 : 2;
            ds.pointRadius = 3;
        });
        chart.update('none');
    };

    return new Chart(document.getElementById(canvasId), {
        type: 'line',
        data,
        plugins: [plugin],
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            animation: { duration: 500 },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 13, font: { size: 11.5 }, padding: 16 },
                    onHover(evt, item, legend) {
                        const chart = legend.chart;
                        const idx   = item.datasetIndex;
                        chart.data.datasets.forEach((ds, i) => {
                            const dimmed   = i !== idx;
                            ds.borderColor = dimmed ? origColors[i] + '28' : origColors[i];
                            ds.borderWidth = dimmed ? 1 : (i === 0 ? 3 : 2.5);
                            ds.pointRadius = dimmed ? 1 : 5;
                        });
                        legend.chart.canvas.style.cursor = 'pointer';
                        chart.update('none');
                    },
                    onLeave(evt, item, legend) {
                        restore(legend.chart);
                        legend.chart.canvas.style.cursor = '';
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(15,23,42,0.88)',
                    padding: 12,
                    titleFont: { size: 12, weight: '700' },
                    bodyFont: { size: 11.5 },
                    callbacks: {
                        title: (items) => {
                            const lbl = items[0]?.label ?? '';
                            const d   = new Date(lbl + 'T00:00:00');
                            return d.toLocaleDateString('es-PE', { weekday:'long', day:'numeric', month:'long' });
                        },
                        label: (item) => `  ${item.dataset.label}: ${yPrefix}${item.formattedValue}`
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        callback: (val, idx) => fmtFecha(data.labels[idx]),
                        maxTicksLimit: 31,
                        font: { size: 11 },
                        color: '#94a3b8'
                    },
                    grid: { color: '#f1f5f9' }
                },
                y: {
                    beginAtZero: true,
                    ticks: { font: { size: 11 }, color: '#94a3b8' },
                    grid: { color: '#f1f5f9' }
                }
            }
        }
    });
}

<?php if (!empty($opsDateLabels)): ?>
buildChart('chartOps', opsData, '');
<?php endif; ?>

<?php if (!empty($ventasDateLabels)): ?>
buildChart('chartVentas', ventasData, 'S/ ');
<?php endif; ?>
</script>

</body>
</html>
