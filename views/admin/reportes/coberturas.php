<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Coberturas | Solo Boticas</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/caja.css">
    <style>
        .cob-badge--3 { background:#d1fae5; color:#065f46; }
        .cob-badge--2 { background:#fee2e2; color:#991b1b; }
        .cob-badge--1 { background:#f1f5f9; color:#475569; }
    </style>
</head>
<body style="background:#f1f5f9;min-height:100vh;">
<?php
$basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
$f = fn($v) => htmlspecialchars($v ?? '—');
$roles = ['CAJERA'=>'Cajera','VENDEDORA'=>'Vendedora','ALMACENERA'=>'Almacenera'];
?>

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
        <a href="<?= $basePath ?>/admin/reportes" class="caja-btn-back">← Reportes</a>
    </div>
</header>

<main class="caja-main" style="max-width:1100px;">

    <!-- ── Filtros ─── -->
    <section class="caja-card no-print">
        <form method="GET" style="display:flex;gap:.6rem;flex-wrap:wrap;align-items:flex-end;">
            <div style="display:flex;flex-direction:column;gap:.25rem;">
                <label style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#64748b;">Mes</label>
                <input type="month" name="mes" class="caja-input" value="<?= htmlspecialchars($filtroMes) ?>" style="max-width:150px;">
            </div>
            <div style="display:flex;flex-direction:column;gap:.25rem;">
                <label style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#64748b;">Local</label>
                <select name="local" class="caja-input" style="max-width:170px;">
                    <option value="0">Todos</option>
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

    <!-- ── Resumen por trabajador ─── -->
    <section class="caja-card" style="padding:1rem;">
        <h2 class="caja-card__title" style="margin-bottom:.75rem;">Resumen del mes</h2>
        <p style="font-size:.75rem;color:#94a3b8;margin-bottom:.75rem;">
            Status <strong>3</strong> = cubrió a alguien &nbsp;·&nbsp;
            Status <strong>2</strong> = fue reemplazado &nbsp;·&nbsp;
            Status <strong>1</strong> = sin eventos de cobertura
        </p>
        <div class="caja-table-wrap">
            <table class="caja-table">
                <thead>
                    <tr>
                        <th>Trabajador/a</th>
                        <th class="text-center">Coberturas dadas<br><small style="font-weight:400;color:#94a3b8;">Status 3</small></th>
                        <th class="text-center">Veces reemplazado<br><small style="font-weight:400;color:#94a3b8;">Status 2</small></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($trabajadores as $t):
                    $s3 = (int)($coberturas[$t['id_postulante']] ?? 0);
                    $s2 = (int)($reemplazos[$t['id_postulante']] ?? 0);
                    if ($s3 === 0 && $s2 === 0) continue;
                ?>
                    <tr>
                        <td style="font-weight:600;"><?= htmlspecialchars($t['nombres']) ?></td>
                        <td class="text-center">
                            <?php if ($s3 > 0): ?>
                                <span class="badge cob-badge--3" style="font-size:.78rem;font-weight:700;">
                                    <?= $s3 ?> vez<?= $s3 > 1 ? 'es' : '' ?>
                                </span>
                            <?php else: ?>
                                <span style="color:#cbd5e1;">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($s2 > 0): ?>
                                <span class="badge cob-badge--2" style="font-size:.78rem;font-weight:700;">
                                    <?= $s2 ?> vez<?= $s2 > 1 ? 'es' : '' ?>
                                </span>
                            <?php else: ?>
                                <span style="color:#cbd5e1;">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty(array_filter($trabajadores, fn($t) => isset($coberturas[$t['id_postulante']]) || isset($reemplazos[$t['id_postulante']])))): ?>
                    <tr><td colspan="3" class="caja-table__empty">Sin coberturas registradas en este período.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- ── Detalle de eventos ─── -->
    <?php if (!empty($detalle)): ?>
    <section class="caja-card" style="padding:1rem;">
        <h2 class="caja-card__title" style="margin-bottom:.75rem;">Detalle de coberturas (<?= count($detalle) ?>)</h2>
        <div class="caja-table-wrap">
            <table class="caja-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Local · Turno · Rol</th>
                        <th>Quién cubrió</th>
                        <th>A quién reemplazó</th>
                        <th>Comentario</th>
                        <th class="text-center">Estado</th>
                        <th style="font-size:.68rem;color:#94a3b8;">Registrado</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($detalle as $d): ?>
                    <tr>
                        <td style="white-space:nowrap;"><?= date('d/m/Y', strtotime($d['fecha_dia'])) ?></td>
                        <td style="font-size:.8rem;">
                            <?= htmlspecialchars($d['local_desc']) ?> ·
                            <?= htmlspecialchars($d['turno_desc']) ?> ·
                            <?= $roles[$d['rol_puesto']] ?? htmlspecialchars($d['rol_puesto']) ?>
                        </td>
                        <td>
                            <span class="badge cob-badge--3" style="font-size:.75rem;">
                                <?= htmlspecialchars($d['quien_cubrió']) ?>
                            </span>
                        </td>
                        <td style="color:#64748b;font-size:.83rem;">
                            <?= $d['a_quien_reemplazó'] ? htmlspecialchars($d['a_quien_reemplazó']) : '<span style="color:#cbd5e1">—</span>' ?>
                        </td>
                        <td style="font-size:.78rem;color:#475569;max-width:200px;">
                            <?= $d['notas'] ? htmlspecialchars($d['notas']) : '<span style="color:#cbd5e1">—</span>' ?>
                        </td>
                        <td class="text-center">
                            <?php if (($d['estado'] ?? 'ACTIVA') === 'REVERTIDA'): ?>
                                <span style="background:#fee2e2;color:#991b1b;font-size:.68rem;font-weight:700;padding:2px 7px;border-radius:5px;">Revertida</span>
                            <?php else: ?>
                                <span style="background:#d1fae5;color:#065f46;font-size:.68rem;font-weight:700;padding:2px 7px;border-radius:5px;">Activa</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:.7rem;color:#94a3b8;white-space:nowrap;">
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
