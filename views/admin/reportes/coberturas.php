<?php
$basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
$meses    = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
[$anioF, $nmesF] = explode('-', $filtroMes);
$mesLabel = $meses[(int)$nmesF - 1] . ' ' . $anioF;

$roles = [
    'CAJERA'     => 'Cajera',
    'VENDEDORA'  => 'Vendedora',
    'ALMACENERA' => 'Almacenera',
    'LIMPIEZA'   => 'Limpieza',
];
$turnoLabel = [1 => '☀️ Mañana', 2 => '🌙 Tarde'];
$diasLabel  = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coberturas de Horario | Solo Boticas</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/caja.css">
    <style>
        .cb-badge { display:inline-block;font-size:.72rem;font-weight:700;padding:2px 9px;border-radius:20px; }
        .cb-dado  { background:#d1fae5;color:#065f46; }
        .cb-recib { background:#fee2e2;color:#991b1b; }
        .cb-activ { background:#d1fae5;color:#065f46; }
        .cb-revert{ background:#fee2e2;color:#991b1b; }
        @media print { .no-print{display:none!important;} body{background:#fff;} }
    </style>
</head>
<body style="background:#f1f5f9;min-height:100vh;">

<header class="caja-header">
    <div class="caja-header__brand">
        <div class="caja-header__logo">SB</div>
        <div>
            <p class="caja-header__company">Grupo KGyR S.A.C</p>
            <p class="caja-header__app">Reporte — <strong>Coberturas de Horario</strong></p>
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
                <label style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#64748b;">Local</label>
                <select name="local" class="caja-input" style="max-width:170px;">
                    <option value="0">Todos los locales</option>
                    <?php foreach ($locales as $l): ?>
                        <option value="<?= $l['id'] ?>" <?= $filtroLocal == $l['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($l['descripcion']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="caja-btn caja-btn--primary">Filtrar</button>
        </form>
    </section>

    <!-- KPIs rápidos -->
    <?php
    $totalDadas    = array_sum($coberturas);
    $totalRecibidas= array_sum($reemplazos);
    $totalEventos  = count($detalle);
    $activas       = count(array_filter($detalle, fn($d) => ($d['estado'] ?? 'ACTIVA') === 'ACTIVA'));
    ?>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:.65rem;margin-bottom:1.25rem;">
        <?php foreach ([
            ['Coberturas dadas',    $totalDadas,     '#059669'],
            ['Veces reemplazado',   $totalRecibidas, '#dc2626'],
            ['Total eventos',       $totalEventos,   '#0097A7'],
            ['Activas',             $activas,        '#7c3aed'],
        ] as [$lbl, $val, $col]): ?>
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:.65rem;text-align:center;">
            <div style="font-size:1.4rem;font-weight:800;color:<?= $col ?>;"><?= $val ?></div>
            <div style="font-size:.63rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#64748b;"><?= $lbl ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Resumen por trabajador -->
    <section class="caja-card" style="padding:1rem;">
        <h2 class="caja-card__title" style="margin-bottom:.75rem;">
            Resumen del mes · <span style="color:#94a3b8;font-weight:400;font-size:.85em;"><?= htmlspecialchars($mesLabel) ?></span>
        </h2>
        <div class="caja-table-wrap">
            <table class="caja-table">
                <thead>
                    <tr>
                        <th>Trabajador/a</th>
                        <th class="text-center">Coberturas dadas<br><small style="font-weight:400;color:#94a3b8;">cubrió a un compañero</small></th>
                        <th class="text-center">Veces reemplazado<br><small style="font-weight:400;color:#94a3b8;">un compañero lo cubrió</small></th>
                        <th class="text-center">Balance</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $hayResumen = false;
                foreach ($trabajadores as $t):
                    $pid = $t['id_postulante'];
                    $s3  = (int)($coberturas[$pid] ?? 0);
                    $s2  = (int)($reemplazos[$pid] ?? 0);
                    if ($s3 === 0 && $s2 === 0) continue;
                    $hayResumen  = true;
                    $balance     = $s3 - $s2;
                ?>
                <tr>
                    <td style="font-weight:600;color:#1e293b;"><?= htmlspecialchars($t['nombres']) ?></td>
                    <td class="text-center">
                        <?php if ($s3 > 0): ?>
                            <span class="cb-badge cb-dado"><?= $s3 ?> vez<?= $s3 > 1 ? 'es' : '' ?></span>
                        <?php else: ?>
                            <span style="color:#cbd5e1;">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <?php if ($s2 > 0): ?>
                            <span class="cb-badge cb-recib"><?= $s2 ?> vez<?= $s2 > 1 ? 'es' : '' ?></span>
                        <?php else: ?>
                            <span style="color:#cbd5e1;">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center" style="font-weight:700;font-size:.82rem;color:<?= $balance > 0 ? '#059669' : ($balance < 0 ? '#dc2626' : '#94a3b8') ?>">
                        <?= $balance > 0 ? "+{$balance}" : ($balance < 0 ? $balance : '—') ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (!$hayResumen): ?>
                    <tr><td colspan="4" class="caja-table__empty">Sin coberturas registradas en <?= htmlspecialchars($mesLabel) ?>.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Detalle de eventos -->
    <?php if (!empty($detalle)): ?>
    <section class="caja-card" style="padding:1rem;">
        <h2 class="caja-card__title" style="margin-bottom:.75rem;">
            Detalle de eventos
            <span style="font-size:.75rem;font-weight:400;color:#94a3b8;margin-left:.5rem;"><?= count($detalle) ?> registro<?= count($detalle) !== 1 ? 's' : '' ?></span>
        </h2>
        <div class="caja-table-wrap">
            <table class="caja-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Turno · Local · Rol</th>
                        <th>Quién cubrió</th>
                        <th>A quién reemplazó</th>
                        <th>Comentario</th>
                        <th class="text-center">Estado</th>
                        <th style="font-size:.68rem;color:#94a3b8;">Registrado</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($detalle as $d):
                    $dow = $diasLabel[(int)date('w', strtotime($d['fecha_dia']))];
                ?>
                    <tr style="<?= ($d['estado'] ?? '') === 'REVERTIDA' ? 'opacity:.55;' : '' ?>">
                        <td style="white-space:nowrap;">
                            <strong><?= $dow ?></strong>
                            <span style="display:block;font-size:.7rem;color:#64748b;"><?= date('d/m/Y', strtotime($d['fecha_dia'])) ?></span>
                        </td>
                        <td style="font-size:.78rem;">
                            <?= $turnoLabel[$d['turno_desc']] ?? htmlspecialchars($d['turno_desc']) ?>
                            <span style="display:block;color:#94a3b8;font-size:.7rem;">
                                <?= htmlspecialchars($d['local_desc']) ?> · <?= $roles[$d['rol_puesto']] ?? htmlspecialchars($d['rol_puesto']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="cb-badge cb-dado"><?= htmlspecialchars($d['quien_cubrió']) ?></span>
                        </td>
                        <td style="color:#64748b;font-size:.82rem;">
                            <?= $d['a_quien_reemplazó'] ? htmlspecialchars($d['a_quien_reemplazó']) : '<span style="color:#cbd5e1">—</span>' ?>
                        </td>
                        <td style="font-size:.76rem;color:#475569;max-width:180px;">
                            <?= $d['notas'] ? htmlspecialchars($d['notas']) : '<span style="color:#cbd5e1">—</span>' ?>
                        </td>
                        <td class="text-center">
                            <?php if (($d['estado'] ?? 'ACTIVA') === 'REVERTIDA'): ?>
                                <span class="cb-badge cb-revert" style="font-size:.65rem;">Revertida</span>
                            <?php else: ?>
                                <span class="cb-badge cb-activ" style="font-size:.65rem;">Activa</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:.68rem;color:#94a3b8;white-space:nowrap;">
                            <?= date('d/m H:i', strtotime($d['fecha_solicitud'])) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
    <?php endif; ?>

</main>
</body>
</html>
