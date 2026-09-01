<?php
$basePath    = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
$resultados  = $resultados  ?? [];
$comentarios = $comentarios ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados de <?= htmlspecialchars($mesLabel) ?> | Solo Boticas</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/staff.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/caja.css">
    <style>
        .ebr-wrap { max-width: 900px; margin: 0 auto; padding: 1.25rem 1rem 3rem; }
        .ebr-table-wrap { overflow-x: auto; background:#fff; border-radius:10px; box-shadow:0 1px 4px rgba(0,0,0,.06); }
        .ebr-table { width: 100%; border-collapse: collapse; font-size: .8rem; }
        .ebr-table th { background:#f8fafc; padding:.5rem .7rem; font-size:.66rem; font-weight:700; text-transform:uppercase;
            letter-spacing:.05em; color:#64748b; border-bottom:2px solid #e2e8f0; }
        .ebr-table td { padding:.5rem .7rem; border-bottom:1px solid #f1f5f9; }
        .ebr-table tr:last-child td { border-bottom:none; }
        .ebr-num { font-weight:700; font-variant-numeric: tabular-nums; }
        .ebr-com-list { display:flex; flex-direction:column; gap:.5rem; }
        .ebr-com { background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:.6rem .8rem;
            font-size:.8rem; color:#334155; white-space:pre-wrap; }
    </style>
</head>
<body style="background:#f1f5f9;min-height:100vh;">

<header class="staff-header">
    <div class="staff-header__brand">
        <div class="staff-header__logo">SB</div>
        <div>
            <p class="staff-header__company">Grupo KGyR S.A.C</p>
            <p class="staff-header__app">Resultados de <?= htmlspecialchars($mesLabel) ?></p>
        </div>
    </div>
    <div class="staff-header__user">
        <a href="<?= $basePath ?>/staff" class="staff-btn-logout" style="font-size:.78rem;">← Volver</a>
    </div>
</header>

<main class="ebr-wrap">
    <p style="font-size:.8rem;color:#64748b;margin-bottom:1rem;">
        Promedio (1-10) de la encuesta anónima sobre el manejo del agente BCP. Cuanto más alto, más señales de que
        podría estar pasando.
    </p>

    <?php if (empty($resultados)): ?>
    <div style="text-align:center;padding:3rem;color:#94a3b8;">
        <div style="font-size:2.5rem;margin-bottom:.5rem;">🔥</div>
        <p style="font-weight:600;">Aún no hay votos registrados este mes.</p>
    </div>
    <?php else: ?>
    <div class="ebr-table-wrap">
    <table class="ebr-table">
        <thead>
            <tr>
                <th>Cajera</th>
                <th class="text-center">Votos</th>
                <th class="text-center">🔥 Tarjeta propia/empresa</th>
                <th class="text-center">🔥 Fraccionamiento</th>
                <th class="text-center">🔥 Irregularidad (síntesis)</th>
                <th class="text-center">🔥 Apropiación de sobrantes</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($resultados as $r): ?>
            <tr>
                <td style="font-weight:600;color:#1e293b;"><?= htmlspecialchars($r['nombres']) ?></td>
                <td class="text-center" style="color:#94a3b8;"><?= (int)$r['total_votos'] ?></td>
                <td class="text-center ebr-num"><?= number_format((float)$r['prom_tarjeta_propia'], 2) ?></td>
                <td class="text-center ebr-num"><?= number_format((float)$r['prom_fraccionamiento'], 2) ?></td>
                <td class="text-center ebr-num"><?= number_format((float)$r['prom_irregularidad'], 2) ?></td>
                <td class="text-center ebr-num"><?= number_format((float)$r['prom_apropiacion_sobrante'], 2) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>

    <?php if (!empty($comentarios)): ?>
    <p style="font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#64748b;margin:1.5rem 0 .65rem;">
        💬 Comentarios anónimos
    </p>
    <div class="ebr-com-list">
        <?php foreach ($comentarios as $c): ?>
        <div class="ebr-com"><?= nl2br(htmlspecialchars($c['comentario'])) ?></div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</main>
</body>
</html>
