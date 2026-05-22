<?php
$basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
$userName = $_SESSION['user_name'] ?? 'Usuario';
$userRol  = $_SESSION['user_rol']  ?? 'STAFF';
$total    = $total ?? 0;
$pagos    = $pagos ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagos PLIN QR | SoloBoticas</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/dashboard.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/caja.css">
    <style>
        .bbva-header-card {
            background: linear-gradient(135deg, #004481, #1a6bbf);
            border-radius: 12px;
            padding: 1.5rem;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .bbva-total { font-size: 2.2rem; font-weight: 700; }
        .bbva-label { font-size: 0.75rem; text-transform: uppercase; letter-spacing: .06em; opacity: .8; margin-bottom: .25rem; }
        .bbva-count { font-size: 0.85rem; opacity: .75; margin-top: .25rem; }
        .raw-box {
            background: #1e293b;
            border-radius: 8px;
            padding: .75rem 1rem;
            font-family: monospace;
            font-size: .72rem;
            color: #a8ff78;
            white-space: pre-wrap;
            word-break: break-all;
            max-height: 280px;
            overflow-y: auto;
            display: none;
        }
        .btn-raw {
            font-size: .7rem;
            padding: 2px 8px;
            background: #1e293b;
            color: #a8ff78;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-raw:hover { background: #334155; }
        .sync-dot {
            display: inline-block;
            width: 8px; height: 8px;
            border-radius: 50%;
            margin-right: 4px;
        }
    </style>
</head>
<body style="background:#f1f5f9;">

<header class="caja-header">
    <div class="caja-header__brand">
        <div class="caja-header__logo" style="background:#004481;">QR</div>
        <div>
            <p class="caja-header__company">Grupo KGyR S.A.C</p>
            <p class="caja-header__app"><strong>Pagos PLIN</strong> — Cobros por QR (raw)</p>
        </div>
    </div>
    <div class="caja-header__right">
        <span class="caja-header__user"><?= htmlspecialchars($userName) ?> (<?= $userRol ?>)</span>
        <a href="<?= $basePath ?>/admin/dashboard" class="caja-btn-back">← Dashboard</a>
    </div>
</header>

<main class="caja-main" style="max-width:1100px;">

    <!-- Total acumulado -->
    <div class="bbva-header-card">
        <div>
            <p class="bbva-label">Total acumulado</p>
            <p class="bbva-total">S/ <?= number_format($total, 2) ?></p>
            <p class="bbva-count"><?= count($pagos) ?> pago<?= count($pagos) !== 1 ? 's' : '' ?> registrado<?= count($pagos) !== 1 ? 's' : '' ?> (últimos 50)</p>
        </div>
        <div style="text-align:right;">
            <p class="bbva-label">Fuente</p>
            <p style="font-size:1rem;font-weight:600;">App LectorPagosBBVA (PLIN)</p>
            <p style="font-size:.78rem;opacity:.7;margin-top:.25rem;">Notificaciones QR · Android</p>
        </div>
    </div>

    <!-- Tabla de pagos -->
    <section class="caja-card" style="padding:1rem;">
        <?php if (empty($pagos)): ?>
            <p style="text-align:center;color:#94a3b8;padding:2rem 0;">
                Aún no hay pagos registrados desde la app móvil.
            </p>
        <?php else: ?>
        <table class="caja-table" style="width:100%;table-layout:fixed;">
            <colgroup>
                <col style="width:130px;">  <!-- Fecha -->
                <col>                        <!-- Cliente -->
                <col style="width:100px;">   <!-- Monto -->
                <col style="width:220px;">   <!-- Mensaje -->
                <col style="width:80px;">    <!-- Log -->
            </colgroup>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th class="text-right">Monto</th>
                    <th>Mensaje notificación</th>
                    <th class="text-center">Log</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($pagos as $i => $p): ?>
                <tr>
                    <td>
                        <span style="font-size:.8rem;color:#1e293b;display:block;">
                            <?= date('d/m/Y', strtotime($p['fecha_notif'])) ?>
                        </span>
                        <span style="font-size:.72rem;color:#94a3b8;">
                            <?= date('H:i:s', strtotime($p['fecha_notif'])) ?>
                        </span>
                    </td>
                    <td>
                        <span style="font-size:.88rem;font-weight:600;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                              title="<?= htmlspecialchars($p['cliente']) ?>">
                            <?= htmlspecialchars($p['cliente']) ?>
                        </span>
                        <span style="font-size:.7rem;color:#94a3b8;"><?= htmlspecialchars($p['app_origen']) ?></span>
                    </td>
                    <td class="text-right">
                        <strong style="font-size:.95rem;color:#004481;font-variant-numeric:tabular-nums;">
                            S/ <?= number_format((float)$p['monto'], 2) ?>
                        </strong>
                    </td>
                    <td>
                        <span style="font-size:.78rem;color:#334155;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                              title="<?= htmlspecialchars($p['texto']) ?>">
                            <?= htmlspecialchars($p['texto']) ?>
                        </span>
                        <?php if (!empty($p['texto_big']) && $p['texto_big'] !== $p['texto']): ?>
                        <span style="font-size:.7rem;color:#64748b;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            <?= htmlspecialchars($p['texto_big']) ?>
                        </span>
                        <?php endif; ?>
                        <?php if (!empty($p['subtexto'])): ?>
                        <span style="font-size:.7rem;color:#94a3b8;">
                            <?= htmlspecialchars($p['subtexto']) ?>
                        </span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <button class="btn-raw" onclick="toggleRaw(<?= $i ?>)">JSON</button>
                    </td>
                </tr>
                <tr id="raw-row-<?= $i ?>">
                    <td colspan="5" style="padding:0 .5rem .5rem;">
                        <div class="raw-box" id="raw-<?= $i ?>">
<?php
    $rawDecoded = json_decode($p['raw'] ?? '{}', true);
    echo htmlspecialchars(json_encode($rawDecoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </section>

</main>

<script>
function toggleRaw(i) {
    const box = document.getElementById('raw-' + i);
    box.style.display = box.style.display === 'block' ? 'none' : 'block';
}
</script>
</body>
</html>
