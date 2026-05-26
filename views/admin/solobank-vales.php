<?php
$basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
$userName = $_SESSION['user_name'] ?? 'Usuario';
$vales    = $vales ?? [];

$disponibles = array_values(array_filter($vales, fn($v) => $v['estado'] === 'DISPONIBLE'));
$usados      = array_values(array_filter($vales, fn($v) => $v['estado'] === 'USADO'));
$totalPendiente = array_sum(array_column($disponibles, 'total'));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vales SoloBank | SoloBoticas</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/dashboard.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/caja.css">
    <style>
        .sb-stats { display:flex;gap:1.5rem;flex-wrap:wrap }
        .sb-stat  { background:rgba(255,255,255,.15);border-radius:8px;padding:8px 16px;text-align:center;min-width:90px }
        .sb-stat strong { display:block;font-size:1.5rem;font-weight:800;line-height:1.1 }
        .sb-stat span   { font-size:.75rem;opacity:.85 }
        .sb-badge-disp { display:inline-block;padding:2px 10px;border-radius:20px;font-size:.7rem;font-weight:700;background:#d1fae5;color:#065f46 }
        .sb-badge-used { display:inline-block;padding:2px 10px;border-radius:20px;font-size:.7rem;font-weight:700;background:#dbeafe;color:#1e40af }
        .section-lbl { font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;margin:1.5rem 0 .6rem;padding-bottom:.4rem;border-bottom:2px solid #e2e8f0 }
        .mono { font-family:monospace;font-size:.72rem;color:#94a3b8 }
        .text-right { text-align:right }
    </style>
</head>
<body style="background:#f1f5f9;">

<header class="caja-header">
    <div class="caja-header__brand">
        <div class="caja-header__logo" style="background:#003da6;">SB</div>
        <div>
            <p class="caja-header__company">Grupo KGyR S.A.C</p>
            <p class="caja-header__app"><strong>Vales SoloBank</strong> — Cierres recibidos desde Python</p>
        </div>
    </div>
    <div class="caja-header__right">
        <span class="caja-header__user"><?= htmlspecialchars($userName) ?></span>
        <a href="<?= $basePath ?>/admin/dashboard" class="caja-btn-back">← Dashboard</a>
    </div>
</header>

<main class="caja-main" style="max-width:1100px;">

    <!-- Resumen -->
    <div style="background:linear-gradient(135deg,#003da6,#1a5fd4);border-radius:12px;padding:1.25rem 1.5rem;color:#fff;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
        <div>
            <h1 style="font-size:1.2rem;font-weight:700;margin:0">📋 Vales SoloBank</h1>
            <p style="margin:.2rem 0 0;opacity:.8;font-size:.82rem">Cierres enviados automáticamente al hacer cierre en Python</p>
        </div>
        <div class="sb-stats">
            <div class="sb-stat">
                <strong><?= count($disponibles) ?></strong>
                <span>Disponibles</span>
            </div>
            <div class="sb-stat">
                <strong><?= count($usados) ?></strong>
                <span>Usados</span>
            </div>
            <div class="sb-stat">
                <strong>S/ <?= number_format($totalPendiente, 2) ?></strong>
                <span>Por cuadrar</span>
            </div>
        </div>
    </div>

    <!-- Disponibles -->
    <p class="section-lbl">✅ Disponibles para cuadre (<?= count($disponibles) ?>)</p>

    <?php if (empty($disponibles)): ?>
        <p style="color:#94a3b8;font-size:.875rem;padding:.5rem 0 1rem">Sin vales disponibles por el momento.</p>
    <?php else: ?>
    <div class="caja-table-wrap" style="margin-bottom:2rem;">
        <table class="caja-table">
            <thead>
                <tr>
                    <th>Caja SB</th>
                    <th>Fecha</th>
                    <th>Turno</th>
                    <th class="text-right">Total</th>
                    <th>Pagos</th>
                    <th>Recibido</th>
                    <th>Código</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($disponibles as $v): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($v['caja']) ?></strong></td>
                    <td><?= date('d/m/Y', strtotime($v['fecha'])) ?></td>
                    <td><?= htmlspecialchars($v['turno']) ?></td>
                    <td class="text-right"><strong>S/ <?= number_format((float)$v['total'], 2) ?></strong></td>
                    <td><?= (int)$v['conteo'] ?> pagos</td>
                    <td><?= date('d/m H:i', strtotime($v['recibido_en'])) ?></td>
                    <td class="mono"><?= htmlspecialchars(substr($v['codigo'], 0, 8)) ?>…</td>
                </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
    <?php endif ?>

    <!-- Usados -->
    <p class="section-lbl">📂 Ya usados en cuadre (<?= count($usados) ?>)</p>

    <?php if (empty($usados)): ?>
        <p style="color:#94a3b8;font-size:.875rem;padding:.5rem 0 1rem">Ninguno todavía.</p>
    <?php else: ?>
    <div class="caja-table-wrap">
        <table class="caja-table">
            <thead>
                <tr>
                    <th>Caja SB</th>
                    <th>Fecha</th>
                    <th>Turno</th>
                    <th class="text-right">Total</th>
                    <th>Pagos</th>
                    <th>Caja local</th>
                    <th>Actualizado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usados as $v): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($v['caja']) ?></strong></td>
                    <td><?= date('d/m/Y', strtotime($v['fecha'])) ?></td>
                    <td><?= htmlspecialchars($v['turno']) ?></td>
                    <td class="text-right">S/ <?= number_format((float)$v['total'], 2) ?></td>
                    <td><?= (int)$v['conteo'] ?> pagos</td>
                    <td><?= htmlspecialchars($v['caja_local_desc'] ?? '—') ?></td>
                    <td><?= date('d/m H:i', strtotime($v['actualizado_en'])) ?></td>
                </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
    <?php endif ?>

</main>
</body>
</html>
