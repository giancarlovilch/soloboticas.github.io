<?php
/** @var array $ventas */ /** @var array $detalles */ /** @var int $miId */
$basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
$userName = $userName ?? $_SESSION['user_name'] ?? 'Usuario';
$userRol  = $userRol  ?? $_SESSION['user_rol']  ?? 'STAFF';
$esAdmin  = $userRol === 'ADMIN';

$locales = [2 => 'Local 2 (SB2)', 3 => 'Local 3 (SB3)', 4 => 'Local 4 (SB4)'];

$filtroLocal = isset($_GET['local']) ? (int) $_GET['local'] : 0;
$filtroFecha = $_GET['fecha'] ?? date('Y-m-d');

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

<main class="caja-main">

    <div style="display:flex;gap:1rem;flex-wrap:wrap;">
        <div class="caja-card" style="flex:1;min-width:180px;text-align:center;">
            <p style="font-size:0.68rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.4rem;">Total del día</p>
            <p style="font-size:1.6rem;font-weight:700;color:#059669;">S/ <?= number_format($totalDia, 2) ?></p>
        </div>
        <div class="caja-card" style="flex:1;min-width:180px;text-align:center;">
            <p style="font-size:0.68rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.4rem;">Tickets</p>
            <p style="font-size:1.6rem;font-weight:700;color:#1e293b;"><?= count($activas) ?></p>
        </div>
        <div class="caja-card" style="flex:1;min-width:180px;text-align:center;">
            <p style="font-size:0.68rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.4rem;">Pendientes de pasar al ERP</p>
            <p style="font-size:1.6rem;font-weight:700;color:#d97706;"><?= $pendientesErp ?></p>
        </div>
    </div>

    <section class="caja-card">
        <form method="get" style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end;margin-bottom:1rem;">
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
            <button type="submit" class="caja-btn caja-btn--primary">Filtrar</button>
        </form>

        <?php if (empty($ventas)): ?>
            <div class="ve-cart-empty">No hay ventas de emergencia registradas para este filtro.</div>
        <?php else: ?>
            <div style="display:flex;flex-direction:column;gap:1rem;">
                <?php foreach ($ventas as $v):
                    $anulada     = $v['estado'] === 'ANULADA';
                    $puedeAnular = !$anulada && ($esAdmin || (int) $v['postulante_vendedor_id'] === (int) $miId);
                ?>
                    <div class="caja-card caja-card--info" style="padding:0;overflow:hidden;<?= $anulada ? 'opacity:.7;' : '' ?>">

                        <?php if ($anulada): ?>
                            <div class="ve-estado-bar ve-estado-bar--anulada">
                                🚫 ANULADA <?= $v['anulado_en'] ? '· ' . date('d/m/Y H:i', strtotime($v['anulado_en'])) : '' ?>
                            </div>
                        <?php elseif ($v['descargado_en']): ?>
                            <div class="ve-estado-bar ve-estado-bar--descargado">
                                ✅ DESCARGADO AL ERP · <?= date('d/m/Y H:i', strtotime($v['descargado_en'])) ?>
                            </div>
                        <?php else: ?>
                            <div class="ve-estado-bar ve-estado-bar--pendiente">
                                ⏳ PENDIENTE DE PASAR AL ERP
                            </div>
                        <?php endif; ?>

                        <div style="padding:1.5rem;">
                        <div class="caja-card__header-row">
                            <div>
                                <p class="caja-card__caja">Ticket #<?= $v['id'] ?> — <?= htmlspecialchars($v['local_nombre']) ?></p>
                                <p class="caja-card__meta">
                                    Vendedor: <?= htmlspecialchars($v['vendedor_nombre']) ?> ·
                                    <?= date('d/m/Y H:i', strtotime($v['creado_en'])) ?>
                                </p>
                            </div>
                            <div style="text-align:right;">
                                <p style="font-size:1.3rem;font-weight:700;color:var(--cj-dark);">S/ <?= number_format($v['total'], 2) ?></p>
                            </div>
                        </div>

                        <div class="caja-table-wrap">
                            <table class="caja-table">
                                <thead>
                                    <tr><th>Código</th><th>Producto</th><th>Cant.</th><th>P. Unit</th><th>Subtotal</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($detalles[$v['id']] ?? [] as $d): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($d['cod_producto']) ?></td>
                                            <td><?= htmlspecialchars($d['nombre_producto']) ?></td>
                                            <td><?= rtrim(rtrim(number_format($d['cantidad'], 2), '0'), '.') ?></td>
                                            <td>S/ <?= number_format($d['precio_venta'], 2) ?></td>
                                            <td>S/ <?= number_format($d['subtotal'], 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="caja-card__actions">
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
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

</main>

<script>
const BASE = '<?= $basePath ?>';

document.querySelectorAll('.ve-toggle-descarga').forEach(btn => {
    btn.addEventListener('click', async () => {
        const id = btn.dataset.id;
        btn.disabled = true;
        try {
            const r = await fetch(`${BASE}/ventas-emergencia/api/${id}/marcar-descargada`, { method: 'POST' });
            const res = await r.json();
            if (res.success) {
                window.location.reload();
            } else {
                alert(res.message || 'Error al actualizar');
                btn.disabled = false;
            }
        } catch (err) {
            alert('Error de red: ' + err.message);
            btn.disabled = false;
        }
    });
});

document.querySelectorAll('.ve-anular').forEach(btn => {
    btn.addEventListener('click', async () => {
        if (!confirm('¿Anular este ticket? Quedará marcado como ANULADO pero no se borra.')) return;
        const id = btn.dataset.id;
        btn.disabled = true;
        try {
            const r = await fetch(`${BASE}/ventas-emergencia/api/${id}/anular`, { method: 'POST' });
            const res = await r.json();
            if (res.success) {
                window.location.reload();
            } else {
                alert(res.message || 'Error al anular');
                btn.disabled = false;
            }
        } catch (err) {
            alert('Error de red: ' + err.message);
            btn.disabled = false;
        }
    });
});
</script>
</body>
</html>
