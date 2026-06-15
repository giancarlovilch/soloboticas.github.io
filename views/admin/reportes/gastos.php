<?php
$basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
$f2 = fn($v) => 'S/ ' . number_format((float)$v, 2, '.', ',');

$catMeta = [
    'PERSONAL' => ['label' => 'Personal',        'color' => '#0097A7', 'bg' => '#e0f7fa', 'icon' => '👤'],
    'LOCAL'    => ['label' => 'Local',            'color' => '#7c3aed', 'bg' => '#ede9fe', 'icon' => '🏠'],
    'FACTURA'  => ['label' => 'Compras',          'color' => '#d97706', 'bg' => '#fef3c7', 'icon' => '🧾'],
    'LIBRE'    => ['label' => 'Otros gastos',     'color' => '#475569', 'bg' => '#f1f5f9', 'icon' => '📌'],
    'DEPOSITO' => ['label' => 'Depósito a KGyR',  'color' => '#059669', 'bg' => '#d1fae5', 'icon' => '🏦'],
];
$subLabel = [
    'MES_ACTUAL'    => 'Mes actual',
    'MES_PASADO'    => 'Mes pasado',
    'PAGO_EXTRA'    => 'Pago extra',
    'BOLETA'        => 'Boleta',
    'FACTURA'       => 'Factura',
    'NOTA_DE_VENTA' => 'Nota de venta',
];
$mesLabel = function(string $mes): string {
    [$y, $m] = explode('-', $mes);
    $nombres = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio',
                'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
    return ($nombres[(int)$m] ?? $m) . ' ' . $y;
};
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gastos por categoría | Solo Boticas</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/caja.css">
    <link rel="icon" type="image/x-icon" href="<?= $basePath ?>/assets/img/logo.ico">
    <style>
        .rep-filtros { display:flex;gap:.6rem;flex-wrap:wrap;align-items:flex-end;margin-bottom:1.5rem; }
        .rep-fg      { display:flex;flex-direction:column;gap:.25rem; }
        .rep-fg label{ font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#64748b; }

        .cat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: .75rem;
            margin-bottom: 1.5rem;
        }
        .cat-card {
            background: #fff;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            padding: 1rem 1.1rem;
            display: flex; flex-direction: column; gap: .4rem;
        }
        .cat-card__head { display:flex; align-items:center; gap:.5rem; }
        .cat-card__icon { font-size: 1.4rem; line-height: 1; }
        .cat-card__name { font-size: .72rem; font-weight: 700; text-transform: uppercase;
                          letter-spacing: .06em; }
        .cat-card__total{ font-size: 1.35rem; font-weight: 800; font-variant-numeric: tabular-nums; margin-top:.1rem; }
        .cat-card__subs { margin-top: .4rem; display: flex; flex-direction: column; gap: .2rem; }
        .cat-card__sub  { display: flex; justify-content: space-between; align-items: center;
                          font-size: .75rem; color: #475569; border-top: 1px solid #f1f5f9; padding-top: .2rem; }
        .cat-card__sub span:last-child { font-weight: 600; color: #1e293b; }

        .total-banner {
            background: #1e293b; color: #fff;
            border-radius: 12px; padding: 1rem 1.4rem;
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 1rem;
        }
        .total-banner__label { font-size: .72rem; font-weight: 700; text-transform: uppercase;
                               letter-spacing: .08em; opacity: .7; }
        .total-banner__val   { font-size: 1.8rem; font-weight: 800; font-variant-numeric: tabular-nums; }
        .total-banner__count { font-size: .8rem; opacity: .6; }

        .rep-table { width:100%; border-collapse:collapse; font-size:.82rem; }
        .rep-table th { background:#f8fafc; font-size:.65rem; font-weight:700;
                        text-transform:uppercase; letter-spacing:.06em; color:#64748b;
                        padding:.55rem .75rem; text-align:left; border-bottom:2px solid #e2e8f0; }
        .rep-table td { padding:.5rem .75rem; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
        .rep-table tr:last-child td { border-bottom:none; }
        .rep-table tr:hover td { background:#f8fafc; }
        .rep-table td.monto { text-align:right; font-weight:700; font-variant-numeric:tabular-nums; }

        .cat-badge {
            display: inline-block; padding: 2px 8px; border-radius: 20px;
            font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em;
            white-space: nowrap;
        }

        @media(max-width:640px) { .cat-grid { grid-template-columns:1fr 1fr; } }
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
            <p class="caja-header__app">Reporte — <strong>Gastos por categoría</strong></p>
        </div>
    </div>
    <div class="caja-header__right">
        <span class="caja-header__user"><?= htmlspecialchars($userName) ?></span>
        <button onclick="window.print()" class="caja-btn-back no-print" style="cursor:pointer;">🖨 Imprimir</button>
        <a href="<?= $basePath ?>/admin/reportes" class="caja-btn-back">← Reportes</a>
    </div>
</header>

<main class="caja-main" style="max-width:1100px;">

    <!-- Filtros -->
    <form method="get" class="rep-filtros no-print">
        <div class="rep-fg">
            <label>Mes</label>
            <input type="month" name="mes" value="<?= htmlspecialchars($mes) ?>"
                   class="caja-input" style="width:160px;">
        </div>
        <div class="rep-fg">
            <label>Local</label>
            <select name="local" class="caja-input" style="width:160px;">
                <option value="">Todos los locales</option>
                <?php foreach ($locales as $loc): ?>
                    <option value="<?= $loc['id_local'] ?>"
                        <?= $localId === $loc['id_local'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($loc['descripcion']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="caja-btn caja-btn--primary" style="height:38px;">Filtrar</button>
    </form>

    <!-- Total general -->
    <div class="total-banner">
        <div>
            <p class="total-banner__label"><?= $mesLabel($mes) ?><?= $localId ? ' · ' . htmlspecialchars($locales[array_search($localId, array_column($locales,'id_local'))]['descripcion'] ?? '') : '' ?></p>
            <p class="total-banner__val"><?= $f2($totalGeneral) ?></p>
        </div>
        <p class="total-banner__count"><?= count($rows) ?> movimiento<?= count($rows) !== 1 ? 's' : '' ?></p>
    </div>

    <!-- Cards por categoría -->
    <div class="cat-grid">
        <?php foreach ($catMeta as $catKey => $meta): ?>
            <?php if (!isset($resumen[$catKey])) continue; ?>
            <?php $cat = $resumen[$catKey]; ?>
            <div class="cat-card" style="border-left: 4px solid <?= $meta['color'] ?>;">
                <div class="cat-card__head">
                    <span class="cat-card__icon"><?= $meta['icon'] ?></span>
                    <span class="cat-card__name" style="color:<?= $meta['color'] ?>;"><?= $meta['label'] ?></span>
                </div>
                <p class="cat-card__total" style="color:<?= $meta['color'] ?>;"><?= $f2($cat['total']) ?></p>
                <?php if (!empty($cat['subs'])): ?>
                    <div class="cat-card__subs">
                        <?php foreach ($cat['subs'] as $sub => $subTotal): ?>
                            <div class="cat-card__sub">
                                <span><?= htmlspecialchars($subLabel[$sub] ?? $sub) ?></span>
                                <span><?= $f2($subTotal) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Tabla de detalle -->
    <div style="background:#fff;border:1.5px solid #e2e8f0;border-radius:12px;overflow:hidden;">
        <div style="padding:.85rem 1.1rem;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;">
            <p style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#475569;margin:0;">
                Detalle de movimientos
            </p>
            <input type="search" id="buscar" placeholder="Buscar…"
                   class="caja-input" style="width:200px;height:30px;font-size:.78rem;" oninput="filtrarTabla()">
        </div>
        <div style="overflow-x:auto;">
            <table class="rep-table" id="tablaGastos">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Local</th>
                        <th>Categoría</th>
                        <th>Concepto</th>
                        <th>Descripción / Beneficiario</th>
                        <th style="text-align:right;">Monto</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $r):
                        $meta   = $catMeta[$r['categoria']] ?? ['label' => $r['categoria'], 'color' => '#475569', 'bg' => '#f1f5f9'];
                        $subTxt = $subLabel[$r['sub_categoria'] ?? ''] ?? ($r['sub_categoria'] ?? '—');
                    ?>
                    <tr>
                        <td style="white-space:nowrap;color:#64748b;"><?= htmlspecialchars($r['fecha']) ?></td>
                        <td><?= htmlspecialchars($r['local_desc']) ?></td>
                        <td>
                            <span class="cat-badge"
                                  style="background:<?= $meta['bg'] ?>;color:<?= $meta['color'] ?>;">
                                <?= $meta['label'] ?>
                            </span>
                        </td>
                        <td style="color:#64748b;"><?= htmlspecialchars($subTxt) ?></td>
                        <td><?= htmlspecialchars($r['descripcion'] ?? '') ?></td>
                        <td class="monto"><?= $f2($r['monto']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($rows)): ?>
                    <tr>
                        <td colspan="6" style="text-align:center;color:#94a3b8;padding:2rem;">
                            Sin gastos registrados para este período.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<script>
function filtrarTabla() {
    const q = document.getElementById('buscar').value.toLowerCase();
    document.querySelectorAll('#tablaGastos tbody tr').forEach(tr => {
        tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}
</script>

</body>
</html>
