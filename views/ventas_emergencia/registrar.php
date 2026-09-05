<?php
$basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
$userName = $userName ?? $_SESSION['user_name'] ?? 'Usuario';
$userRol  = $userRol  ?? $_SESSION['user_rol']  ?? 'STAFF';

$locales = [
    2 => 'Local 2 (SB2)',
    3 => 'Local 3 (SB3)',
    4 => 'Local 4 (SB4)',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Venta de Emergencia | Caja SB</title>
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
            <p class="caja-header__app"><strong>Venta de Emergencia</strong> — ERP caído</p>
        </div>
    </div>
    <div class="caja-header__right">
        <span class="caja-header__user"><?= htmlspecialchars($userName) ?> (<?= htmlspecialchars($userRol) ?>)</span>
        <a href="<?= $basePath ?>/ventas-emergencia/historial" class="caja-btn-back">Historial</a>
        <a href="<?= $basePath ?>/<?= $userRol === 'ADMIN' ? 'admin/dashboard' : 'staff' ?>" class="caja-btn-back">Dashboard</a>
    </div>
</header>

<main class="ve-main">

    <div id="veAlert" class="caja-alert caja-alert--error" hidden></div>

    <input type="hidden" id="veLocal" value="2">

    <div class="ve-local-picker" id="veLocalPicker">
        <?php foreach ($locales as $id => $nombre): ?>
            <button type="button" class="ve-local-btn" data-local="<?= $id ?>"><?= htmlspecialchars($nombre) ?></button>
        <?php endforeach; ?>
    </div>

    <div class="ve-toolbar">
        <div class="caja-field ve-buscador" style="flex:1;">
            <label>Buscar producto (código o nombre)</label>
            <input type="text" id="veBuscar" class="caja-input" placeholder="Ej: eucerin, 7501234..." autocomplete="off">
            <div id="veResultados" class="ve-resultados" hidden></div>
        </div>
    </div>

    <div class="ve-table-wrap">
        <table class="ve-table">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Producto</th>
                    <th class="ve-col-num">P. Venta</th>
                    <th class="ve-col-num">Cant.</th>
                    <th class="ve-col-num">Subtotal</th>
                    <th class="ve-col-num">Stock</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="veCarrito">
                <tr id="veCartEmptyRow"><td colspan="7" class="ve-cart-empty">Aún no agregas productos.</td></tr>
            </tbody>
        </table>
    </div>

    <div class="ve-footer">
        <div class="ve-footer__actions">
            <button type="button" id="veLimpiar" class="caja-btn caja-btn--outline">Limpiar</button>
            <button type="button" id="veGuardar" class="caja-btn caja-btn--primary">Guardar venta</button>
        </div>
        <div class="ve-footer__total">
            Total <strong id="veTotal">S/ 0.00</strong>
        </div>
    </div>

</main>

<script>
const BASE = '<?= $basePath ?>';
</script>
<script src="<?= $basePath ?>/assets/js/ventas-emergencia.js"></script>
</body>
</html>
