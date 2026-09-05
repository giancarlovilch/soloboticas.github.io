<?php
/** @var array $ventas */ /** @var array $detalles */ /** @var array $vendedores */ /** @var int $miId */
$basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
$userName = $userName ?? $_SESSION['user_name'] ?? 'Usuario';
$userRol  = $userRol  ?? $_SESSION['user_rol']  ?? 'STAFF';
$esAdmin  = $userRol === 'ADMIN';

$locales = [2 => 'Local 2 (SB2)', 3 => 'Local 3 (SB3)', 4 => 'Local 4 (SB4)'];

$filtroLocal      = isset($_GET['local']) ? (int) $_GET['local'] : 0;
$filtroFecha      = $_GET['fecha'] ?? date('Y-m-d');
$filtroVendedorId = isset($_GET['vendedor']) ? (int) $_GET['vendedor'] : 0;

$activas       = array_filter($ventas, fn($v) => $v['estado'] !== 'ANULADA');
$totalDia      = array_sum(array_column($activas, 'total'));
$pendientesErp = count(array_filter($activas, fn($v) => empty($v['descargado_en'])));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial Ventas de Emergencia | Caja SB</title>
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
            <p class="caja-header__app"><strong>Historial</strong> — Ventas de Emergencia</p>
        </div>
    </div>
    <div class="caja-header__right">
        <span class="caja-header__user"><?= htmlspecialchars($userName) ?> (<?= htmlspecialchars($userRol) ?>)</span>
        <a href="<?= $basePath ?>/ventas-emergencia" class="caja-btn-back">← Registrar venta</a>
    </div>
</header>

<main class="ve-main">

    <div style="display:flex;gap:.6rem;flex-wrap:wrap;">
        <div class="ve-stat">
            <span class="ve-stat__label">Total del día</span>
            <span class="ve-stat__valor" style="color:#059669;">S/ <?= number_format($totalDia, 2) ?></span>
        </div>
        <div class="ve-stat">
            <span class="ve-stat__label">Tickets</span>
            <span class="ve-stat__valor"><?= count($activas) ?></span>
        </div>
        <div class="ve-stat">
            <span class="ve-stat__label">Pendientes de pasar al ERP</span>
            <span class="ve-stat__valor" style="color:#d97706;"><?= $pendientesErp ?></span>
        </div>
    </div>

    <form method="get" class="ve-filtros">
        <div class="caja-field">
            <label>Fecha</label>
            <input type="date" name="fecha" class="caja-input" value="<?= htmlspecialchars($filtroFecha) ?>">
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
            <label>Vendedor</label>
            <select name="vendedor" class="caja-input">
                <option value="0">Todos</option>
                <?php foreach ($vendedores as $vd): ?>
                    <option value="<?= $vd['id_postulante'] ?>" <?= $filtroVendedorId === (int) $vd['id_postulante'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($vd['nombres']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="caja-btn caja-btn--primary">Filtrar</button>
    </form>

    <?php if (empty($ventas)): ?>
        <div class="ve-cart-empty">No hay ventas de emergencia registradas para este filtro.</div>
    <?php else: ?>
        <div class="ve-table-wrap">
            <table class="ve-table">
                <thead>
                    <tr>
                        <th>Ticket</th>
                        <th>Hora</th>
                        <th>Local</th>
                        <th>Vendedor</th>
                        <th class="ve-col-num">Total</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ventas as $v):
                        $anulada     = $v['estado'] === 'ANULADA';
                        $puedeAnular = !$anulada && ($esAdmin || (int) $v['postulante_vendedor_id'] === (int) $miId);
                    ?>
                        <tr class="ve-fila-ticket <?= $anulada ? 've-fila-ticket--anulada' : '' ?>" data-toggle="det-<?= $v['id'] ?>">
                            <td>#<?= $v['id'] ?></td>
                            <td><?= date('d/m H:i', strtotime($v['creado_en'])) ?></td>
                            <td><?= htmlspecialchars($v['local_nombre']) ?></td>
                            <td><?= htmlspecialchars($v['vendedor_nombre']) ?></td>
                            <td class="ve-col-num" style="font-weight:700;">S/ <?= number_format($v['total'], 2) ?></td>
                            <td>
                                <?php if ($anulada): ?>
                                    <span class="ve-pill ve-pill--anulada">ANULADA</span>
                                <?php elseif ($v['descargado_en']): ?>
                                    <span class="ve-pill ve-pill--ok">DESCARGADO</span>
                                <?php else: ?>
                                    <span class="ve-pill ve-pill--pendiente">PENDIENTE</span>
                                <?php endif; ?>
                            </td>
                            <td class="ve-col-num">
                                <button type="button" class="ve-link-detalle" data-toggle-btn="det-<?= $v['id'] ?>">Ver detalle ▾</button>
                            </td>
                        </tr>
                        <tr id="det-<?= $v['id'] ?>" class="ve-fila-detalle" hidden>
                            <td colspan="7">
                                <table class="ve-table ve-table--detalle">
                                    <thead>
                                        <tr><th>Código</th><th>Producto</th><th class="ve-col-num">Cant.</th><th class="ve-col-num">P. Unit</th><th class="ve-col-num">Subtotal</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($detalles[$v['id']] ?? [] as $d): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($d['cod_producto']) ?></td>
                                                <td><?= htmlspecialchars($d['nombre_producto']) ?></td>
                                                <td class="ve-col-num"><?= rtrim(rtrim(number_format($d['cantidad'], 2), '0'), '.') ?></td>
                                                <td class="ve-col-num">S/ <?= number_format($d['precio_venta'], 2) ?></td>
                                                <td class="ve-col-num">S/ <?= number_format($d['subtotal'], 2) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <div class="ve-fila-detalle__acciones">
                                    <a href="<?= $basePath ?>/ventas-emergencia/<?= $v['id'] ?>/imprimir" target="_blank" class="caja-btn caja-btn--outline">
                                        🖨️ Imprimir
                                    </a>
                                    <?php if (!$anulada): ?>
                                        <button type="button" class="caja-btn caja-btn--outline ve-toggle-descarga" data-id="<?= $v['id'] ?>">
                                            <?= $v['descargado_en'] ? 'Marcar como pendiente' : 'Marcar como descargado en ERP' ?>
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($puedeAnular): ?>
                                        <button type="button" class="caja-btn ve-anular" data-id="<?= $v['id'] ?>"
                                                style="background:var(--cj-red-bg);color:var(--cj-red);border:1px solid #fecaca;">
                                            🚫 Anular
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

</main>

<script>
const BASE = '<?= $basePath ?>';

document.querySelectorAll('[data-toggle-btn]').forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        const row = document.getElementById(btn.dataset.toggleBtn);
        row.hidden = !row.hidden;
        btn.textContent = row.hidden ? 'Ver detalle ▾' : 'Ocultar ▴';
    });
});
document.querySelectorAll('.ve-fila-ticket').forEach(tr => {
    tr.addEventListener('click', () => {
        const row = document.getElementById(tr.dataset.toggle);
        const btn = tr.querySelector('[data-toggle-btn]');
        row.hidden = !row.hidden;
        if (btn) btn.textContent = row.hidden ? 'Ver detalle ▾' : 'Ocultar ▴';
    });
});

document.querySelectorAll('.ve-toggle-descarga').forEach(btn => {
    btn.addEventListener('click', async (e) => {
        e.stopPropagation();
        const id = btn.dataset.id;
        btn.disabled = true;
        try {
            const r = await fetch(`${BASE}/ventas-emergencia/api/${id}/marcar-descargada`, { method: 'POST' });
            const res = await r.json();
            if (res.success) window.location.reload();
            else { alert(res.message || 'Error al actualizar'); btn.disabled = false; }
        } catch (err) { alert('Error de red: ' + err.message); btn.disabled = false; }
    });
});

document.querySelectorAll('.ve-anular').forEach(btn => {
    btn.addEventListener('click', async (e) => {
        e.stopPropagation();
        if (!confirm('¿Anular este ticket? Quedará marcado como ANULADO pero no se borra.')) return;
        const id = btn.dataset.id;
        btn.disabled = true;
        try {
            const r = await fetch(`${BASE}/ventas-emergencia/api/${id}/anular`, { method: 'POST' });
            const res = await r.json();
            if (res.success) window.location.reload();
            else { alert(res.message || 'Error al anular'); btn.disabled = false; }
        } catch (err) { alert('Error de red: ' + err.message); btn.disabled = false; }
    });
});
</script>
</body>
</html>
