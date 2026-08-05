<?php
/** @var array $items */ /** @var array $cajas */ /** @var array $cajeras */
$basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
$userName = $userName ?? $_SESSION['user_name'] ?? 'Usuario';
$userRol  = $userRol  ?? $_SESSION['user_rol']  ?? 'STAFF';

$filtroCategoria = $_GET['categoria'] ?? '';
$filtroCaja      = isset($_GET['caja'])   ? (int)$_GET['caja']   : 0;
$filtroCajera    = isset($_GET['cajera']) ? (int)$_GET['cajera'] : 0;
$filtroRevisado  = $_GET['revisado'] ?? '';
$filtroMes       = $_GET['mes'] ?? date('Y-m');
$cajas           = $cajas   ?? [];
$cajeras         = $cajeras ?? [];

$categorias = [
    'COBROS'   => 'Cobros Electrónicos',
    'OTROS'    => 'Otros pagos',
    'PERSONAL' => 'Pago de Personal',
    'LOCAL'    => 'Pago de Local',
    'COMPRAS'  => 'Pago de Compras',
    'DEPOSITO' => 'Depósito KGyR',
];

$totRevisado = count(array_filter($items, fn($i) => (int)$i['revisado'] === 1));
$totPendiente = count($items) - $totRevisado;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auditoría | Caja SB</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/dashboard.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/caja.css">
</head>
<body style="background:#f1f5f9;">

<header class="caja-header">
    <div class="caja-header__brand">
        <div class="caja-header__logo">SB</div>
        <div>
            <p class="caja-header__company">Grupo KGyR S.A.C</p>
            <p class="caja-header__app"><strong>Auditoría</strong> — Verificación de movimientos</p>
        </div>
    </div>
    <div class="caja-header__right">
        <span class="caja-header__user"><?= htmlspecialchars($userName) ?> (<?= $userRol ?>)</span>
        <a href="<?= $basePath ?>/caja" class="caja-btn-back">← Caja</a>
        <a href="<?= $basePath ?>/admin/dashboard" class="caja-btn-back">Dashboard</a>
    </div>
</header>

<main class="caja-main" style="max-width:1150px;">

    <!-- ── Resumen ────────────────────────────────────────── -->
    <div style="display:flex;gap:1rem;flex-wrap:wrap;">
        <div class="caja-card" style="flex:1;min-width:180px;text-align:center;">
            <p style="font-size:0.68rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.4rem;">Revisados</p>
            <p style="font-size:1.6rem;font-weight:700;color:#059669;"><?= $totRevisado ?></p>
        </div>
        <div class="caja-card" style="flex:1;min-width:180px;text-align:center;">
            <p style="font-size:0.68rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.4rem;">Pendientes de revisar</p>
            <p style="font-size:1.6rem;font-weight:700;color:#d97706;"><?= $totPendiente ?></p>
        </div>
        <div class="caja-card" style="flex:1;min-width:180px;text-align:center;">
            <p style="font-size:0.68rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.4rem;">Total registros</p>
            <p style="font-size:1.6rem;font-weight:700;color:#1e293b;"><?= count($items) ?></p>
        </div>
    </div>

    <!-- ── Filtros ─────────────────────────────────────────── -->
    <section class="caja-card">
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;margin-bottom:.75rem;">
            <span style="font-size:0.8rem;color:#64748b;font-weight:600;">Revisado:</span>
            <?php foreach (['' => 'Todos', 'NO' => 'Pendientes', 'SI' => 'Revisados'] as $val => $lbl): ?>
                <a href="?revisado=<?= $val ?>&categoria=<?= urlencode($filtroCategoria) ?>&caja=<?= $filtroCaja ?>&cajera=<?= $filtroCajera ?>&mes=<?= htmlspecialchars($filtroMes) ?>"
                   class="caja-btn <?= $filtroRevisado === $val ? 'caja-btn--primary' : 'caja-btn--outline' ?>"
                   style="padding:4px 12px;font-size:0.78rem;">
                    <?= $lbl ?>
                </a>
            <?php endforeach; ?>
        </div>
        <form method="GET" style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;">
            <input type="hidden" name="revisado" value="<?= htmlspecialchars($filtroRevisado) ?>">
            <select name="categoria" class="caja-input" style="max-width:190px;" onchange="this.form.submit()">
                <option value="">— Todas las categorías —</option>
                <?php foreach ($categorias as $val => $lbl): ?>
                    <option value="<?= $val ?>" <?= $filtroCategoria === $val ? 'selected' : '' ?>><?= $lbl ?></option>
                <?php endforeach; ?>
            </select>
            <select name="caja" class="caja-input" style="max-width:190px;" onchange="this.form.submit()">
                <option value="0">— Todas las cajas —</option>
                <?php foreach ($cajas as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $filtroCaja == $c['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['descripcion']) ?> (<?= htmlspecialchars($c['local_desc']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="cajera" class="caja-input" style="max-width:170px;" onchange="this.form.submit()">
                <option value="0">— Todas las cajeras —</option>
                <?php foreach ($cajeras as $cj): ?>
                    <option value="<?= $cj['id'] ?>" <?= $filtroCajera == $cj['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cj['nombres']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="month" name="mes" class="caja-input" style="max-width:140px;"
                   value="<?= htmlspecialchars($filtroMes) ?>" onchange="this.form.submit()">
            <?php if ($filtroCategoria || $filtroCaja || $filtroCajera): ?>
            <a href="?revisado=<?= htmlspecialchars($filtroRevisado) ?>&mes=<?= htmlspecialchars($filtroMes) ?>" class="caja-btn caja-btn--outline"
               style="padding:4px 10px;font-size:0.78rem;">✕ Limpiar</a>
            <?php endif; ?>
        </form>
    </section>

    <!-- ── Tabla ──────────────────────────────────────────── -->
    <section class="caja-card" style="padding:1rem;">
        <div id="audMsg" class="caja-alert" hidden></div>
        <table class="caja-table" id="tablaAuditoria" style="width:100%;table-layout:fixed;">
            <colgroup>
                <col style="width:85px;">  <!-- Fecha -->
                <col style="width:150px;"> <!-- Categoría -->
                <col style="width:130px;"> <!-- Caja/Local -->
                <col>                      <!-- Cajera / Referencia -->
                <col style="width:90px;">  <!-- Monto -->
                <col style="width:100px;"> <!-- Estado -->
                <col style="width:150px;"> <!-- Acción -->
            </colgroup>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Categoría</th>
                    <th>Caja / Local</th>
                    <th>Cajera / Referencia</th>
                    <th class="text-right">Monto</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center">Acción</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $it):
                $rowId = $it['categoria'] . '-' . $it['id'];
            ?>
                <tr id="row-<?= $rowId ?>">
                    <td>
                        <span style="font-size:0.8rem;color:#1e293b;display:block;"><?= date('d/m/Y', strtotime($it['fecha_operacion'])) ?></span>
                    </td>
                    <td>
                        <span style="font-size:0.78rem;font-weight:700;display:block;"><?= htmlspecialchars($categorias[$it['categoria']] ?? $it['categoria']) ?></span>
                    </td>
                    <td>
                        <span style="font-size:0.8rem;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= htmlspecialchars($it['caja_desc']) ?>">
                            <?= htmlspecialchars($it['caja_desc']) ?>
                        </span>
                        <span style="font-size:0.72rem;color:#94a3b8;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:block;">
                            <?= htmlspecialchars($it['local_desc']) ?>
                        </span>
                    </td>
                    <td>
                        <span style="font-size:0.8rem;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            <?= htmlspecialchars($it['cajera_nombre']) ?>
                        </span>
                        <span style="font-size:0.72rem;color:#94a3b8;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:block;">
                            <?= htmlspecialchars($it['referencia'] ?? '—') ?>
                        </span>
                    </td>
                    <td class="text-right">
                        <strong style="font-size:0.88rem;font-variant-numeric:tabular-nums;">S/ <?= number_format($it['monto'], 2) ?></strong>
                    </td>
                    <td class="text-center">
                        <span style="font-size:0.7rem;color:#64748b;display:block;"><?= htmlspecialchars($it['estado']) ?></span>
                        <span class="badge <?= $it['revisado'] ? 'badge-contratado' : 'badge-pendiente' ?>" style="margin-top:2px;">
                            <?= $it['revisado'] ? 'Revisado' : 'Pendiente' ?>
                        </span>
                    </td>
                    <td class="text-center">
                        <?php if ($it['categoria'] === 'COBROS'): ?>
                            <?php if ($it['estado'] === 'PENDIENTE'): ?>
                                <button class="btn-edit" style="font-size:0.68rem;padding:3px 6px;background:#059669;display:block;width:100%;margin-bottom:3px;"
                                        onclick="confirmarCobro(<?= $it['id'] ?>,'APROBADO')">✓ Aprobar</button>
                                <button class="btn-danger" style="font-size:0.68rem;padding:3px 6px;display:block;width:100%;"
                                        onclick="confirmarCobro(<?= $it['id'] ?>,'RECHAZADO')">✗ Rechazar</button>
                            <?php else: ?>
                                <span style="color:#94a3b8;font-size:0.72rem;">— (usa Cobros Electrónicos) —</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <button class="btn-edit" style="font-size:0.68rem;padding:3px 8px;width:100%;background:<?= $it['revisado'] ? '#94a3b8' : '#059669' ?>;"
                                    onclick="marcarRevisado('<?= $it['categoria'] ?>', <?= $it['id'] ?>, <?= $it['revisado'] ? 'false' : 'true' ?>)">
                                <?= $it['revisado'] ? '↺ Desmarcar' : '✓ Marcar revisado' ?>
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($items)): ?>
                <tr><td colspan="7" class="caja-table__empty">No hay movimientos registrados con estos filtros.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </section>

</main>

<script>
const BASE = '<?= $basePath ?>';

function mostrarMsg(texto, tipo) {
    const msg = document.getElementById('audMsg');
    msg.textContent = texto;
    msg.className = 'caja-alert ' + (tipo === 'ok' ? 'caja-alert--ok' : 'caja-alert--error');
    msg.hidden = false;
}

async function marcarRevisado(categoria, id, revisado) {
    try {
        const r = await fetch(`${BASE}/caja/api/auditoria/${categoria}/${id}/revisar`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ revisado }),
        });
        const res = await r.json();
        if (res.success) {
            location.reload();
        } else {
            mostrarMsg(res.message || 'Error al actualizar.', 'error');
        }
    } catch {
        mostrarMsg('Error de conexión.', 'error');
    }
}

async function confirmarCobro(id, estado) {
    try {
        const r = await fetch(`${BASE}/caja/api/pago-digital/${id}/confirmar`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ estado }),
        });
        const res = await r.json();
        if (res.success) {
            location.reload();
        } else {
            mostrarMsg(res.message || 'Error al actualizar.', 'error');
        }
    } catch {
        mostrarMsg('Error de conexión.', 'error');
    }
}
</script>
</body>
</html>
