<?php
$basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
$meses    = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
[$anioF, $nmesF] = explode('-', $filtroMes);
$mesLabel = $meses[(int)$nmesF - 1] . ' ' . $anioF;

$estadoInfo = [
    'A TIEMPO' => ['bg'=>'#d1fae5','color'=>'#065f46','label'=>'A tiempo'],
    'TARDE'    => ['bg'=>'#fef3c7','color'=>'#92400e','label'=>'Tarde'],
    'EXTRA'    => ['bg'=>'#eff6ff','color'=>'#1e40af','label'=>'Extra'],
    'TEMPRANO' => ['bg'=>'#f0fdfe','color'=>'#0e7490','label'=>'Temprano'],
    'FALTA'    => ['bg'=>'#fee2e2','color'=>'#991b1b','label'=>'Falta'],
];

$puntInfo = [
    'MUY_TEMPRANO' => ['label'=>'Muy anticipado',      'bg'=>'#eff6ff','color'=>'#1e40af'],
    'TEMPRANO'     => ['label'=>'Con anticipación',    'bg'=>'#d1fae5','color'=>'#065f46'],
    'TARDE'        => ['label'=>'Retraso leve',        'bg'=>'#fef3c7','color'=>'#92400e'],
    'MUY_TARDE'    => ['label'=>'Retraso considerable','bg'=>'#fee2e2','color'=>'#991b1b'],
];

$califInfo = [
    'MALO'      => ['bg'=>'#fee2e2','color'=>'#991b1b'],
    'REGULAR'   => ['bg'=>'#ffedd5','color'=>'#9a3412'],
    'BUENO'     => ['bg'=>'#d1fae5','color'=>'#065f46'],
    'EXCELENTE' => ['bg'=>'#ede9fe','color'=>'#5b21b6'],
];

$celularInfo = [
    'NO_USO'   => ['label'=>'No usó',   'bg'=>'#d1fae5','color'=>'#065f46'],
    'OCASIONAL'=> ['label'=>'Ocasional','bg'=>'#fef3c7','color'=>'#92400e'],
    'FRECUENTE'=> ['label'=>'Frecuente','bg'=>'#fee2e2','color'=>'#991b1b'],
];

$diasLabel = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
$turnoLabel = [1 => '☀️ Mañana', 2 => '🌙 Tarde'];

// Calcular KPIs adicionales
$totalCompleto = 0; $totalParcial = 0; $totalSinFicha = 0;
foreach ($registros as $r) {
    if ($r['estado'] === 'FALTA') continue;
    if ($r['llegada_puntualidad'] && $r['salida_puntualidad']) $totalCompleto++;
    elseif ($r['llegada_puntualidad'] || $r['salida_puntualidad']) $totalParcial++;
    else $totalSinFicha++;
}

// Helper para badge ENUM 3 niveles
$nivel3 = fn($val, $map) => isset($map[$val])
    ? "<span style=\"font-size:.65rem;font-weight:700;padding:1px 6px;border-radius:10px;background:{$map[$val]['bg']};color:{$map[$val]['color']}\">{$map[$val]['label']}</span>"
    : '<span style="color:#cbd5e1;">—</span>';

$mapaPresent = [
    'DEFICIENTE'  => ['bg'=>'#fee2e2','color'=>'#991b1b','label'=>'Deficiente'],
    'ACEPTABLE'   => ['bg'=>'#fef3c7','color'=>'#92400e','label'=>'Aceptable'],
    'OPTIMO'      => ['bg'=>'#d1fae5','color'=>'#065f46','label'=>'Óptimo'],
    'DESCUIDADO'  => ['bg'=>'#fee2e2','color'=>'#991b1b','label'=>'Descuidado'],
    'PRESENTABLE' => ['bg'=>'#fef3c7','color'=>'#92400e','label'=>'Presentable'],
    'IMPECABLE'   => ['bg'=>'#d1fae5','color'=>'#065f46','label'=>'Impecable'],
    'DESCUIDADAS' => ['bg'=>'#fee2e2','color'=>'#991b1b','label'=>'Descuidadas'],
    'ACEPTABLES'  => ['bg'=>'#fef3c7','color'=>'#92400e','label'=>'Aceptables'],
    'CUIDADAS'    => ['bg'=>'#d1fae5','color'=>'#065f46','label'=>'Cuidadas'],
    'SUELTO'      => ['bg'=>'#fee2e2','color'=>'#991b1b','label'=>'Suelto'],
    'RECOGIDO'    => ['bg'=>'#d1fae5','color'=>'#065f46','label'=>'Recogido'],
    'MONO'        => ['bg'=>'#d1fae5','color'=>'#065f46','label'=>'Con moño'],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asistencias y Encuestas | Solo Boticas</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/caja.css">
    <style>
        .rp-badge { display:inline-block;font-size:.65rem;font-weight:700;padding:2px 7px;border-radius:10px;white-space:nowrap; }
        .rp-dot   { display:inline-block;width:8px;height:8px;border-radius:50%;flex-shrink:0; }
        .rp-pres  { display:flex;gap:3px;flex-wrap:wrap;align-items:center; }
        .rp-sub   { font-size:.68rem;color:#94a3b8;display:block; }
        .rp-sn    { color:#cbd5e1; }
        details > summary { cursor:pointer;list-style:none;font-size:.72rem;color:#0097A7;font-weight:600; }
        details > summary::-webkit-details-marker { display:none; }
        @media print { .no-print { display:none!important; } body { background:#fff; } }
    </style>
</head>
<body style="background:#f1f5f9;min-height:100vh;">

<header class="caja-header">
    <div class="caja-header__brand">
        <div class="caja-header__logo">SB</div>
        <div>
            <p class="caja-header__company">Grupo KGyR S.A.C</p>
            <p class="caja-header__app">Reporte — <strong>Asistencias y Encuestas</strong></p>
        </div>
    </div>
    <div class="caja-header__right">
        <span class="caja-header__user"><?= htmlspecialchars($userName) ?></span>
        <button onclick="window.print()" class="caja-btn-back no-print" style="cursor:pointer;">🖨 Imprimir</button>
        <a href="<?= $basePath ?>/admin/reportes" class="caja-btn-back">← Reportes</a>
    </div>
</header>

<main class="caja-main" style="max-width:1200px;">

    <!-- Filtros -->
    <section class="caja-card no-print">
        <form method="GET" style="display:flex;gap:.6rem;flex-wrap:wrap;align-items:flex-end;">
            <div style="display:flex;flex-direction:column;gap:.25rem;">
                <label style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#64748b;">Mes</label>
                <input type="month" name="mes" class="caja-input" value="<?= htmlspecialchars($filtroMes) ?>" style="max-width:150px;">
            </div>
            <div style="display:flex;flex-direction:column;gap:.25rem;">
                <label style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#64748b;">Trabajador</label>
                <select name="trabajador" class="caja-input" style="max-width:200px;">
                    <option value="0">Todos</option>
                    <?php foreach ($trabajadores as $t): ?>
                        <option value="<?= $t['id'] ?>" <?= $filtroTrabajador == $t['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="caja-btn caja-btn--primary">Filtrar</button>
        </form>
    </section>

    <!-- KPIs -->
    <div style="display:grid;grid-template-columns:repeat(6,1fr);gap:.65rem;margin-bottom:1.25rem;">
        <?php foreach ([
            ['A tiempo',   $totalATiempo,  '#059669'],
            ['Tarde',      $totalTarde,    '#d97706'],
            ['Falta',      $totalFalta,    '#dc2626'],
            ['Otro',       $totalOtro,     '#475569'],
            ['Completo',   $totalCompleto, '#0097A7'],
            ['Parcial',    $totalParcial,  '#7c3aed'],
        ] as [$lbl, $val, $col]): ?>
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:.65rem;text-align:center;">
            <div style="font-size:1.4rem;font-weight:800;color:<?= $col ?>;"><?= $val ?></div>
            <div style="font-size:.63rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#64748b;"><?= $lbl ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Tabla -->
    <section class="caja-card" style="padding:1rem;">
        <p style="font-size:.75rem;color:#94a3b8;margin-bottom:.75rem;text-align:right;">
            <?= count($registros) ?> registro<?= count($registros) !== 1 ? 's' : '' ?> · <?= htmlspecialchars($mesLabel) ?>
        </p>
        <div class="caja-table-wrap">
            <table class="caja-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Trabajador</th>
                        <th>Turno · Local</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Llegada</th>
                        <th class="text-center">Salida</th>
                        <th>Presentación</th>
                        <th class="text-center">Calif.</th>
                        <th class="text-center">Celular</th>
                        <th>Reg. por</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($registros)): ?>
                    <tr><td colspan="10" class="caja-table__empty">Sin registros para los filtros seleccionados.</td></tr>
                <?php endif; ?>
                <?php foreach ($registros as $r):
                    $ecfg  = $estadoInfo[$r['estado']] ?? ['bg'=>'#f1f5f9','color'=>'#64748b','label'=>$r['estado']];
                    $dow   = $diasLabel[(int)date('w', strtotime($r['fecha']))];
                    $lleg  = $puntInfo[$r['llegada_puntualidad'] ?? ''] ?? null;
                    $sal   = $puntInfo[$r['salida_puntualidad']  ?? ''] ?? null;
                    $cal   = $califInfo[$r['calificacion_turno'] ?? ''] ?? null;
                    $cel   = $celularInfo[$r['uso_celular'] ?? ''] ?? null;

                    // Presentación: puntos de color por campo
                    $camposPresent = [
                        'Higiene'   => $r['aseo_personal']  ?? null,
                        'Uniforme'  => $r['vestimenta']      ?? null,
                        'Uñas'      => $r['unas']            ?? null,
                        'Cabello'   => $r['cabello']         ?? null,
                    ];
                    $colorDot = fn($v) => match(true) {
                        in_array($v, ['DEFICIENTE','DESCUIDADO','DESCUIDADAS','SUELTO']) => '#ef4444',
                        in_array($v, ['ACEPTABLE','PRESENTABLE','ACEPTABLES'])           => '#f59e0b',
                        in_array($v, ['OPTIMO','IMPECABLE','CUIDADAS','RECOGIDO','MONO'])=> '#10b981',
                        default => '#e2e8f0',
                    };
                ?>
                    <tr style="<?= $r['estado'] === 'FALTA' ? 'background:#fff5f5' : '' ?>">
                        <td style="white-space:nowrap;">
                            <strong><?= $dow ?></strong>
                            <span class="rp-sub"><?= date('d/m/Y', strtotime($r['fecha'])) ?></span>
                        </td>
                        <td style="font-weight:600;font-size:.82rem;"><?= htmlspecialchars($r['trabajador_nombre']) ?></td>
                        <td style="font-size:.78rem;">
                            <?= $turnoLabel[$r['turno_id']] ?? '—' ?>
                            <span class="rp-sub"><?= htmlspecialchars($r['local_desc'] ?? '—') ?></span>
                        </td>
                        <td class="text-center">
                            <span class="rp-badge" style="background:<?= $ecfg['bg'] ?>;color:<?= $ecfg['color'] ?>"><?= $ecfg['label'] ?></span>
                        </td>
                        <td class="text-center">
                            <?php if ($lleg): ?>
                                <span class="rp-badge" style="background:<?= $lleg['bg'] ?>;color:<?= $lleg['color'] ?>"><?= $lleg['label'] ?></span>
                                <?php if ($r['area_ordenada_ingreso'] !== null || $r['area_limpia_ingreso'] !== null): ?>
                                <div style="font-size:.62rem;color:#94a3b8;margin-top:2px;">
                                    <?= $r['area_ordenada_ingreso'] ? '✓ ord' : ($r['area_ordenada_ingreso'] === '0' ? '✗ ord' : '') ?>
                                    <?= $r['area_limpia_ingreso']   ? ' ✓ limp' : ($r['area_limpia_ingreso'] === '0' ? ' ✗ limp' : '') ?>
                                </div>
                                <?php endif; ?>
                            <?php else: ?><span class="rp-sn">—</span><?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($sal): ?>
                                <span class="rp-badge" style="background:<?= $sal['bg'] ?>;color:<?= $sal['color'] ?>"><?= $sal['label'] ?></span>
                                <?php if ($r['estado_area_cierre']): ?>
                                <div style="font-size:.62rem;color:#94a3b8;margin-top:2px;">
                                    área: <?= $mapaPresent[$r['estado_area_cierre']]['label'] ?? '—' ?>
                                </div>
                                <?php endif; ?>
                            <?php else: ?><span class="rp-sn">—</span><?php endif; ?>
                        </td>
                        <td>
                            <?php if (array_filter($camposPresent)): ?>
                            <div class="rp-pres">
                                <?php foreach ($camposPresent as $campo => $val): ?>
                                    <span class="rp-dot" style="background:<?= $colorDot($val) ?>;"
                                          title="<?= $campo ?>: <?= $mapaPresent[$val]['label'] ?? '—' ?>"></span>
                                <?php endforeach; ?>
                            </div>
                            <?php else: ?><span class="rp-sn">—</span><?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($cal): ?>
                                <span class="rp-badge" style="background:<?= $cal['bg'] ?>;color:<?= $cal['color'] ?>">
                                    <?= ucfirst(strtolower($r['calificacion_turno'])) ?>
                                </span>
                            <?php else: ?><span class="rp-sn">—</span><?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($cel): ?>
                                <span class="rp-badge" style="background:<?= $cel['bg'] ?>;color:<?= $cel['color'] ?>"><?= $cel['label'] ?></span>
                            <?php else: ?><span class="rp-sn">—</span><?php endif; ?>
                        </td>
                        <td style="font-size:.75rem;color:<?= $r['registrado_por_nombre'] ? '#0097A7' : '#cbd5e1' ?>;">
                            <?= $r['registrado_por_nombre'] ? htmlspecialchars($r['registrado_por_nombre']) : '—' ?>
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
