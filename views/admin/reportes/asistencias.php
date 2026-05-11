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
$diasLabel = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Asistencias | Solo Boticas</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/caja.css">
    <style>
        .chk-dot { display:inline-block;width:13px;height:13px;border-radius:3px;font-size:.55rem;font-weight:700;text-align:center;line-height:13px; }
        .chk-ok   { background:#d1fae5;color:#059669; }
        .chk-fail { background:#fee2e2;color:#dc2626; }
        @media print { .no-print { display:none!important; } body { background:#fff; } }
    </style>
</head>
<body style="background:#f1f5f9;min-height:100vh;">

<header class="caja-header">
    <div class="caja-header__brand">
        <div class="caja-header__logo">SB</div>
        <div>
            <p class="caja-header__company">Grupo KGyR S.A.C</p>
            <p class="caja-header__app">Reporte — <strong>Asistencias</strong></p>
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
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:.75rem;margin-bottom:1.25rem;">
        <?php foreach ([
            ['A tiempo', $totalATiempo, '#059669'],
            ['Tarde',    $totalTarde,   '#d97706'],
            ['Falta',    $totalFalta,   '#dc2626'],
            ['Otro',     $totalOtro,    '#475569'],
        ] as [$lbl, $val, $col]): ?>
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:.75rem;text-align:center;">
            <div style="font-size:1.5rem;font-weight:800;color:<?= $col ?>;"><?= $val ?></div>
            <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#64748b;"><?= $lbl ?></div>
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
                        <th>Local</th>
                        <th>Entrada</th>
                        <th>Salida</th>
                        <th>Horas</th>
                        <th class="text-center">Estado</th>
                        <th>Registrado por</th>
                        <th class="text-center">Checklist</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($registros)): ?>
                    <tr><td colspan="9" class="caja-table__empty">Sin registros para los filtros seleccionados.</td></tr>
                <?php endif; ?>
                <?php foreach ($registros as $r):
                    $ecfg  = $estadoInfo[$r['estado']] ?? ['bg'=>'#f1f5f9','color'=>'#64748b','label'=>$r['estado']];
                    $chk   = $checklists[$r['id_asistencia']] ?? [];
                    $horas = '';
                    if ($r['hora_ingreso'] && $r['hora_salida']) {
                        $diff = (strtotime($r['hora_salida']) - strtotime($r['hora_ingreso'])) / 3600;
                        $horas = number_format($diff, 1) . ' h';
                    }
                    $dow = $diasLabel[(int)date('w', strtotime($r['fecha']))];
                ?>
                    <tr>
                        <td style="white-space:nowrap;">
                            <strong><?= $dow ?></strong>
                            <span style="display:block;font-size:.72rem;color:#64748b;"><?= date('d/m/Y', strtotime($r['fecha'])) ?></span>
                        </td>
                        <td style="font-weight:600;"><?= htmlspecialchars($r['trabajador_nombre']) ?></td>
                        <td style="font-size:.82rem;"><?= htmlspecialchars($r['local_desc'] ?? '—') ?></td>
                        <td>
                            <?php if ($r['hora_ingreso']): ?>
                                <span style="font-variant-numeric:tabular-nums;font-weight:600;"><?= date('H:i', strtotime($r['hora_ingreso'])) ?></span>
                            <?php else: ?><span style="color:#cbd5e1;">—</span><?php endif; ?>
                        </td>
                        <td>
                            <?php if ($r['hora_salida']): ?>
                                <span style="font-variant-numeric:tabular-nums;font-weight:600;"><?= date('H:i', strtotime($r['hora_salida'])) ?></span>
                            <?php elseif ($r['hora_ingreso']): ?>
                                <span style="color:#f59e0b;font-size:.75rem;">En turno</span>
                            <?php else: ?><span style="color:#cbd5e1;">—</span><?php endif; ?>
                        </td>
                        <td style="font-variant-numeric:tabular-nums;"><?= $horas ?: '<span style="color:#cbd5e1;">—</span>' ?></td>
                        <td class="text-center">
                            <span style="font-size:.7rem;font-weight:700;padding:2px 8px;border-radius:20px;
                                background:<?= $ecfg['bg'] ?>;color:<?= $ecfg['color'] ?>">
                                <?= $ecfg['label'] ?>
                            </span>
                        </td>
                        <td style="font-size:.78rem;color:<?= $r['registrado_por_nombre'] ? '#0097A7' : '#cbd5e1' ?>;">
                            <?= $r['registrado_por_nombre'] ? htmlspecialchars($r['registrado_por_nombre']) : '—' ?>
                        </td>
                        <td class="text-center">
                            <?php if (!empty($chk)): ?>
                            <div style="display:flex;flex-wrap:wrap;gap:2px;justify-content:center;"
                                 title="<?= implode(' · ', array_map(fn($c) => ($c['cumplido']?'✓':'✗').' '.$c['descripcion'], $chk)) ?>">
                                <?php foreach ($chk as $c): ?>
                                    <span class="chk-dot <?= $c['cumplido'] ? 'chk-ok' : 'chk-fail' ?>"><?= $c['cumplido'] ? '✓' : '✗' ?></span>
                                <?php endforeach; ?>
                            </div>
                            <?php else: ?><span style="color:#cbd5e1;font-size:.75rem;">—</span><?php endif; ?>
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
