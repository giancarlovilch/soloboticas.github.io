<?php
/** @var array $ventas */ /** @var array $vendedores */
$basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
$userName = $userName ?? $_SESSION['user_name'] ?? 'Usuario';
$userRol  = $userRol  ?? $_SESSION['user_rol']  ?? 'ADMIN';

$locales = [2 => 'Local 2 (SB2)', 3 => 'Local 3 (SB3)', 4 => 'Local 4 (SB4)'];

$filtroLocal      = isset($_GET['local']) ? (int) $_GET['local'] : 0;
$filtroVendedorId = isset($_GET['vendedor']) ? (int) $_GET['vendedor'] : 0;
$filtroEstado     = $_GET['estado'] ?? '';
$filtroDesde      = $_GET['desde'] ?? date('Y-m-01');
$filtroHasta      = $_GET['hasta'] ?? date('Y-m-d');

$activas  = array_filter($ventas, fn($v) => $v['estado'] !== 'ANULADA');
$totalGen = array_sum(array_column($activas, 'total'));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumen Ventas de Emergencia | Admin SB</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/dashboard.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/caja.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/ventas-emergencia.css">
</head>
<body>

<header class="caja-header">
    <div class="caja-header__brand">
        <div class="caja-header__logo">SB</div>
        <div>
            <p class="caja-header__company">Grupo KGyR S.A.C</p>
            <p class="caja-header__app"><strong>Resumen</strong> — Ventas de Emergencia (Admin)</p>
        </div>
    </div>
    <div class="caja-header__right">
        <span class="caja-header__user"><?= htmlspecialchars($userName) ?> (<?= htmlspecialchars($userRol) ?>)</span>
        <a href="<?= $basePath ?>/admin/dashboard" class="caja-btn-back">← Dashboard</a>
    </div>
</header>

<main class="caja-main" style="max-width:1300px;">

    <div style="display:flex;gap:1rem;flex-wrap:wrap;">
        <div class="caja-card" style="flex:1;min-width:180px;text-align:center;">
            <p style="font-size:0.68rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.4rem;">Total (rango filtrado)</p>
            <p style="font-size:1.6rem;font-weight:700;color:#059669;">S/ <?= number_format($totalGen, 2) ?></p>
        </div>
        <div class="caja-card" style="flex:1;min-width:180px;text-align:center;">
            <p style="font-size:0.68rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.4rem;">Tickets activos</p>
            <p style="font-size:1.6rem;font-weight:700;color:#1e293b;"><?= count($activas) ?></p>
        </div>
        <div class="caja-card" style="flex:1;min-width:180px;text-align:center;">
            <p style="font-size:0.68rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.4rem;">Anulados</p>
            <p style="font-size:1.6rem;font-weight:700;color:#dc2626;"><?= count($ventas) - count($activas) ?></p>
        </div>
    </div>

    <section class="caja-card">
        <form method="get" style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end;margin-bottom:1rem;">
            <div class="caja-field">
                <label>Desde</label>
                <input type="date" name="desde" class="caja-input" value="<?= htmlspecialchars($filtroDesde) ?>">
            </div>
            <div class="caja-field">
                <label>Hasta</label>
                <input type="date" name="hasta" class="caja-input" value="<?= htmlspecialchars($filtroHasta) ?>">
            </div>
            <div class="caja-field">
                <label>Local</label>
                <select name="local" class="caja-input">
                    <option value="0">Todos</option>
                    <?php foreach ($locales as $id => $nombre): ?>
                        <option value="<?= $id ?>" <?= $filtroLocal === $id ? 'selected' : '' ?>><?= htmlspecialchars($nombre) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="caja-field">
                <label>Trabajador</label>
                <select name="vendedor" class="caja-input">
                    <option value="0">Todos</option>
                    <?php foreach ($vendedores as $vd): ?>
                        <option value="<?= $vd['id_postulante'] ?>" <?= $filtroVendedorId === (int) $vd['id_postulante'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($vd['nombres']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="caja-field">
                <label>Estado</label>
                <select name="estado" class="caja-input">
                    <option value="">Todos</option>
                    <option value="REGISTRADA" <?= $filtroEstado === 'REGISTRADA' ? 'selected' : '' ?>>Registradas</option>
                    <option value="ANULADA" <?= $filtroEstado === 'ANULADA' ? 'selected' : '' ?>>Anuladas</option>
                </select>
            </div>
            <button type="submit" class="caja-btn caja-btn--primary">Filtrar</button>
        </form>

        <?php if (empty($ventas)): ?>
            <div class="ve-cart-empty">No hay ventas de emergencia para este filtro.</div>
        <?php else: ?>
            <div class="ve-table-wrap">
                <table class="ve-table">
                    <thead>
                        <tr>
                            <th>Ticket</th>
                            <th>Fecha</th>
                            <th>Local</th>
                            <th>Vendedor</th>
                            <th class="ve-col-num">Total</th>
                            <th>Estado</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ventas as $v): $anulada = $v['estado'] === 'ANULADA'; ?>
                            <tr style="<?= $anulada ? 'opacity:.6;' : '' ?>">
                                <td>#<?= $v['id'] ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($v['creado_en'])) ?></td>
                                <td><?= htmlspecialchars($v['local_nombre']) ?></td>
                                <td><?= htmlspecialchars($v['vendedor_nombre']) ?></td>
                                <td class="ve-col-num">S/ <?= number_format($v['total'], 2) ?></td>
                                <td>
                                    <?php if ($anulada): ?>
                                        <span class="badge-pendiente-erp" style="background:#1e1e1e;color:#f87171;">ANULADA</span>
                                    <?php elseif ($v['descargado_en']): ?>
                                        <span class="badge-descargado">DESCARGADO</span>
                                    <?php else: ?>
                                        <span class="badge-pendiente-erp">PENDIENTE</span>
                                    <?php endif; ?>
                                </td>
                                <td style="white-space:nowrap;display:flex;gap:.35rem;">
                                    <a href="<?= $basePath ?>/ventas-emergencia/<?= $v['id'] ?>/imprimir" target="_blank"
                                       class="caja-btn caja-btn--outline" style="padding:.3rem .6rem;font-size:0.72rem;">🖨️</a>
                                    <?php if ($anulada): ?>
                                        <button type="button" class="caja-btn ve-reactivar" data-id="<?= $v['id'] ?>"
                                                style="padding:.3rem .6rem;font-size:0.72rem;background:var(--cj-ok-bg);color:#065f46;">
                                            Reactivar
                                        </button>
                                    <?php endif; ?>
                                    <button type="button" class="caja-btn ve-eliminar" data-id="<?= $v['id'] ?>"
                                            style="padding:.3rem .6rem;font-size:0.72rem;background:var(--cj-red-bg);color:var(--cj-red);">
                                        Eliminar
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

</main>

<script>
const BASE = '<?= $basePath ?>';

document.querySelectorAll('.ve-reactivar').forEach(btn => {
    btn.addEventListener('click', async () => {
        const id = btn.dataset.id;
        btn.disabled = true;
        try {
            const r = await fetch(`${BASE}/admin/ventas-emergencia/api/${id}/reactivar`, { method: 'POST' });
            const res = await r.json();
            if (res.success) window.location.reload();
            else { alert(res.message || 'Error'); btn.disabled = false; }
        } catch (err) { alert('Error de red: ' + err.message); btn.disabled = false; }
    });
});

document.querySelectorAll('.ve-eliminar').forEach(btn => {
    btn.addEventListener('click', async () => {
        if (!confirm('Esto BORRA el ticket definitivamente (no queda registro). ¿Seguro?')) return;
        const id = btn.dataset.id;
        btn.disabled = true;
        try {
            const r = await fetch(`${BASE}/admin/ventas-emergencia/api/${id}/eliminar`, { method: 'POST' });
            const res = await r.json();
            if (res.success) window.location.reload();
            else { alert(res.message || 'Error'); btn.disabled = false; }
        } catch (err) { alert('Error de red: ' + err.message); btn.disabled = false; }
    });
});
</script>
</body>
</html>
