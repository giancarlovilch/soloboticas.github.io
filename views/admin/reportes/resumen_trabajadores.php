<?php
$basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
$meses    = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
[$anioF, $nmesF] = explode('-', $filtroMes);
$mesLabel = $meses[(int)$nmesF - 1] . ' ' . $anioF;
$f2 = fn($v) => 'S/ ' . number_format((float)$v, 2, '.', ',');

// Determina calificación dominante del mes
$califDominante = function(array $enc): ?string {
    $scores = [
        'EXCELENTE' => (int)($enc['calif_excelente'] ?? 0),
        'BUENO'     => (int)($enc['calif_bueno']     ?? 0),
        'REGULAR'   => (int)($enc['calif_regular']   ?? 0),
        'MALO'      => (int)($enc['calif_malo']       ?? 0),
    ];
    $max = max($scores);
    if ($max === 0) return null;
    return array_search($max, $scores);
};

$califStyle = [
    'EXCELENTE' => ['bg'=>'#ede9fe','color'=>'#5b21b6'],
    'BUENO'     => ['bg'=>'#d1fae5','color'=>'#065f46'],
    'REGULAR'   => ['bg'=>'#ffedd5','color'=>'#9a3412'],
    'MALO'      => ['bg'=>'#fee2e2','color'=>'#991b1b'],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumen por Trabajador | Solo Boticas</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/caja.css">
    <style>
        .rt-num  { font-variant-numeric:tabular-nums;font-weight:700; }
        .rt-zero { color:#cbd5e1; }
        .rt-badge { display:inline-block;font-size:.63rem;font-weight:700;padding:1px 6px;border-radius:10px; }
        .rt-warn  { color:#dc2626;font-weight:700; }
        @media print { .no-print{display:none!important;} body{background:#fff;} }
    </style>
</head>
<body style="background:#f1f5f9;min-height:100vh;">

<header class="caja-header">
    <div class="caja-header__brand">
        <div class="caja-header__logo">SB</div>
        <div>
            <p class="caja-header__company">Grupo KGyR S.A.C</p>
            <p class="caja-header__app">Resumen por <strong>Trabajador</strong></p>
        </div>
    </div>
    <div class="caja-header__right">
        <span class="caja-header__user"><?= htmlspecialchars($userName) ?></span>
        <button onclick="window.print()" class="caja-btn-back no-print" style="cursor:pointer;">🖨 Imprimir</button>
        <a href="<?= $basePath ?>/admin/reportes" class="caja-btn-back">← Reportes</a>
    </div>
</header>

<main class="caja-main" style="max-width:1200px;">

    <!-- Filtro -->
    <section class="caja-card no-print">
        <form method="GET" style="display:flex;gap:.6rem;align-items:flex-end;flex-wrap:wrap;">
            <div style="display:flex;flex-direction:column;gap:.25rem;">
                <label style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#64748b;">Mes</label>
                <input type="month" name="mes" class="caja-input" value="<?= htmlspecialchars($filtroMes) ?>" style="max-width:150px;">
            </div>
            <button type="submit" class="caja-btn caja-btn--primary">Ver</button>
        </form>
    </section>

    <!-- Tabla -->
    <section class="caja-card" style="padding:1rem;">
        <p style="font-size:.75rem;color:#94a3b8;margin-bottom:.75rem;">
            Resumen de <strong><?= htmlspecialchars($mesLabel) ?></strong>
        </p>
        <div class="caja-table-wrap">
        <table class="caja-table">
            <thead>
                <tr>
                    <th rowspan="2" style="vertical-align:middle;">Trabajador</th>
                    <!-- Asistencia -->
                    <th colspan="5" class="text-center"
                        style="background:#f0fdfe;color:#0e7490;border-bottom:2px solid #0e7490;">
                        Asistencia
                    </th>
                    <!-- Encuestas -->
                    <th colspan="4" class="text-center"
                        style="background:#faf5ff;color:#7c3aed;border-bottom:2px solid #7c3aed;">
                        Encuestas
                    </th>
                    <!-- Ventas -->
                    <th colspan="2" class="text-center"
                        style="background:#f0fdf4;color:#059669;border-bottom:2px solid #059669;">
                        Ventas
                    </th>
                    <!-- Limpieza -->
                    <th colspan="1" class="text-center"
                        style="background:#fffbeb;color:#d97706;border-bottom:2px solid #d97706;">
                        Limpieza
                    </th>
                    <!-- BCP -->
                    <th colspan="2" class="text-center"
                        style="background:#eff6ff;color:#1e40af;border-bottom:2px solid #1e40af;">
                        Caja / BCP
                    </th>
                </tr>
                <tr>
                    <!-- Asistencia sub -->
                    <th style="background:#f0fdfe;color:#64748b;font-size:.62rem;">Total</th>
                    <th style="background:#f0fdfe;color:#065f46;font-size:.62rem;">A tiempo</th>
                    <th style="background:#f0fdfe;color:#d97706;font-size:.62rem;">Tarde</th>
                    <th style="background:#f0fdfe;color:#1e40af;font-size:.62rem;">Extra/Temp.</th>
                    <th style="background:#fee2e2;color:#991b1b;font-size:.62rem;">Faltas</th>
                    <!-- Encuestas sub -->
                    <th style="background:#faf5ff;color:#7c3aed;font-size:.62rem;">Fichas</th>
                    <th style="background:#faf5ff;color:#7c3aed;font-size:.62rem;">Calif. dom.</th>
                    <th style="background:#faf5ff;color:#dc2626;font-size:.62rem;">Llegadas tard.</th>
                    <th style="background:#faf5ff;color:#dc2626;font-size:.62rem;">Celular freq.</th>
                    <!-- Ventas sub -->
                    <th style="background:#f0fdf4;color:#64748b;font-size:.62rem;">Turnos</th>
                    <th style="background:#f0fdf4;color:#059669;font-size:.62rem;">Prom. venta</th>
                    <!-- Limpieza sub -->
                    <th style="background:#fffbeb;color:#d97706;font-size:.62rem;">Veces</th>
                    <!-- BCP sub -->
                    <th style="background:#eff6ff;color:#64748b;font-size:.62rem;">Turnos caja</th>
                    <th style="background:#eff6ff;color:#1e40af;font-size:.62rem;">Prom. ops. BCP</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $hayDatos = false;
            foreach ($trabajadores as $t):
                $pid  = $t['id'];
                $ast  = $asistMap[$pid]    ?? null;
                $ven  = $ventasMap[$pid]   ?? null;
                $bcp  = $bcpMap[$pid]      ?? null;
                $enc  = $encuestaMap[$pid] ?? null;
                if (!$ast && !$ven && !$bcp && !$enc) continue;
                $hayDatos = true;

                $faltas         = (int)($ast['faltas'] ?? 0);
                $llegTarde      = (int)($enc['llegadas_tarde']   ?? 0);
                $celFrec        = (int)($enc['celular_frecuente'] ?? 0);
                $totalFichas    = (int)($enc['total_fichas']      ?? 0);
                $domCalif       = $enc ? $califDominante($enc) : null;
            ?>
            <tr style="<?= $faltas > 0 ? 'background:#fff8f8;' : '' ?>">
                <td style="font-weight:600;color:#1e293b;white-space:nowrap;">
                    <?= htmlspecialchars($t['nombre']) ?>
                    <?php if ($faltas > 0): ?>
                        <span class="rt-badge" style="background:#fee2e2;color:#dc2626;margin-left:4px;">
                            <?= $faltas ?> falta<?= $faltas > 1 ? 's' : '' ?>
                        </span>
                    <?php endif; ?>
                </td>

                <!-- Asistencia -->
                <td class="text-center rt-num"><?= (int)($ast['total']   ?? 0) ?: '<span class="rt-zero">0</span>' ?></td>
                <td class="text-center" style="color:#065f46;font-weight:700;"><?= (int)($ast['a_tiempo'] ?? 0) ?: '<span class="rt-zero">0</span>' ?></td>
                <td class="text-center" style="color:#d97706;font-weight:700;"><?= (int)($ast['tarde']    ?? 0) ?: '<span class="rt-zero">0</span>' ?></td>
                <td class="text-center" style="color:#1e40af;font-weight:700;"><?= (int)($ast['extra']    ?? 0) ?: '<span class="rt-zero">0</span>' ?></td>
                <td class="text-center">
                    <?php if ($faltas > 0): ?>
                        <span style="background:#fee2e2;color:#dc2626;font-weight:700;padding:2px 8px;border-radius:20px;font-size:.75rem;"><?= $faltas ?></span>
                    <?php else: ?>
                        <span class="rt-zero">0</span>
                    <?php endif; ?>
                </td>

                <!-- Encuestas -->
                <td class="text-center rt-num" style="color:#7c3aed;">
                    <?= $totalFichas ?: '<span class="rt-zero">—</span>' ?>
                </td>
                <td class="text-center">
                    <?php if ($domCalif && isset($califStyle[$domCalif])): ?>
                        <span class="rt-badge" style="background:<?= $califStyle[$domCalif]['bg'] ?>;color:<?= $califStyle[$domCalif]['color'] ?>">
                            <?= ucfirst(strtolower($domCalif)) ?>
                        </span>
                    <?php else: ?>
                        <span class="rt-zero">—</span>
                    <?php endif; ?>
                </td>
                <td class="text-center">
                    <?= $llegTarde > 0
                        ? "<span class=\"rt-warn\">{$llegTarde}</span>"
                        : '<span class="rt-zero">0</span>' ?>
                </td>
                <td class="text-center">
                    <?= $celFrec > 0
                        ? "<span class=\"rt-warn\">{$celFrec}</span>"
                        : '<span class="rt-zero">0</span>' ?>
                </td>

                <!-- Ventas -->
                <?php
                $turnosVenta = (int)($ven['turnos_venta'] ?? 0);
                $promVenta   = $turnosVenta > 0 ? (float)$ven['total_ventas'] / $turnosVenta : 0;
                ?>
                <td class="text-center rt-num"><?= $turnosVenta ?: '<span class="rt-zero">—</span>' ?></td>
                <td class="text-right rt-num" style="color:#059669;">
                    <?= $promVenta > 0 ? $f2($promVenta) : '<span class="rt-zero">—</span>' ?>
                </td>

                <!-- Limpieza -->
                <?php $limpieza = (int)($limpiezaMap[$pid] ?? 0); ?>
                <td class="text-center rt-num" style="color:#d97706;">
                    <?= $limpieza > 0 ? $limpieza : '<span class="rt-zero">—</span>' ?>
                </td>

                <!-- BCP -->
                <?php
                $turnosCaja = (int)($bcp['turnos_caja']   ?? 0);
                $totalOps   = (int)($bcp['total_ops_bcp'] ?? 0);
                $promOps    = $turnosCaja > 0 ? round($totalOps / $turnosCaja, 1) : 0;
                ?>
                <td class="text-center rt-num"><?= $turnosCaja ?: '<span class="rt-zero">—</span>' ?></td>
                <td class="text-center rt-num" style="color:#1e40af;">
                    <?= $promOps > 0 ? $promOps : '<span class="rt-zero">—</span>' ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$hayDatos): ?>
                <tr><td colspan="15" class="caja-table__empty">Sin actividad registrada en <?= htmlspecialchars($mesLabel) ?>.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        </div>
        <p style="font-size:.68rem;color:#94a3b8;margin-top:.75rem;">
            <strong>Calif. dom.</strong> = calificación de turno más frecuente del mes ·
            <strong>Llegadas tard.</strong> = veces con retraso leve o considerable ·
            <strong>Celular freq.</strong> = veces con uso frecuente del celular
        </p>
    </section>

</main>
</body>
</html>
