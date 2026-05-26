<?php
$basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
$userName = $_SESSION['user_name'] ?? 'Usuario';
$vales    = $vales ?? [];

$disponibles = array_filter($vales, fn($v) => $v['estado'] === 'DISPONIBLE');
$usados      = array_filter($vales, fn($v) => $v['estado'] === 'USADO');
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
        .sb-header { background:linear-gradient(135deg,#003da6,#1a5fd4);border-radius:12px;padding:1.25rem 1.5rem;color:#fff;display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem }
        .sb-header h1 { font-size:1.3rem;font-weight:700;margin:0 }
        .sb-stats { display:flex;gap:1.5rem;font-size:.85rem }
        .sb-stat { text-align:center }
        .sb-stat strong { display:block;font-size:1.6rem;font-weight:800 }
        .sb-badge { display:inline-block;padding:2px 10px;border-radius:20px;font-size:.72rem;font-weight:700 }
        .sb-disponible { background:#d1fae5;color:#065f46 }
        .sb-usado      { background:#dbeafe;color:#1e40af }
        table { width:100%;border-collapse:collapse;font-size:.85rem }
        th { background:#f8fafc;padding:8px 12px;text-align:left;font-size:.75rem;text-transform:uppercase;letter-spacing:.5px;color:#64748b;border-bottom:2px solid #e2e8f0 }
        td { padding:8px 12px;border-bottom:1px solid #f1f5f9;vertical-align:middle }
        tr:last-child td { border-bottom:none }
        tr:hover td { background:#f8fafc }
        .mono { font-family:monospace;font-size:.75rem;color:#94a3b8 }
        .section-title { font-size:.9rem;font-weight:700;color:#1e293b;margin:1.5rem 0 .75rem;padding-bottom:.4rem;border-bottom:2px solid #e2e8f0 }
    </style>
</head>
<body>
<div class="dashboard-layout">
    <?php include __DIR__ . '/../partials/sidebar.php' ?>
    <main class="dashboard-main">
        <div class="dashboard-content">

            <div class="sb-header">
                <div>
                    <h1>📋 Vales SoloBank</h1>
                    <p style="margin:.25rem 0 0;opacity:.8;font-size:.85rem">Cierres recibidos desde el sistema de cobros</p>
                </div>
                <div class="sb-stats">
                    <div class="sb-stat">
                        <strong><?= count($disponibles) ?></strong>
                        Disponibles
                    </div>
                    <div class="sb-stat">
                        <strong><?= count($usados) ?></strong>
                        Usados
                    </div>
                    <div class="sb-stat">
                        <strong>S/ <?= number_format(array_sum(array_column($disponibles, 'total')), 2) ?></strong>
                        Por cuadrar
                    </div>
                </div>
            </div>

            <!-- Disponibles -->
            <p class="section-title">✅ Disponibles para cuadre</p>
            <?php if (empty($disponibles)): ?>
                <p style="color:#94a3b8;font-size:.875rem;padding:.5rem 0">Sin vales disponibles</p>
            <?php else: ?>
            <div class="caja-table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Caja</th><th>Fecha</th><th>Turno</th>
                            <th class="text-right">Total</th><th>Pagos</th>
                            <th>Recibido</th><th>Código</th>
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
                            <td class="mono"><?= substr($v['codigo'], 0, 8) ?>…</td>
                        </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
            <?php endif ?>

            <!-- Usados -->
            <p class="section-title">📂 Vales ya usados en cuadre</p>
            <?php if (empty($usados)): ?>
                <p style="color:#94a3b8;font-size:.875rem;padding:.5rem 0">Ninguno aún</p>
            <?php else: ?>
            <div class="caja-table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Caja SB</th><th>Fecha</th><th>Turno</th>
                            <th class="text-right">Total</th><th>Pagos</th>
                            <th>Caja local</th><th>Actualizado</th>
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

        </div>
    </main>
</div>
</body>
</html>
