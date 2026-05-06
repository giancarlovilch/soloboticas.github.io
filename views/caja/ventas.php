<?php
/** @var array $sesion */
$basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
$userName = $userName ?? $_SESSION['user_name'] ?? 'Usuario';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingresar ventas | Caja SB</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/caja.css">
</head>
<body>

<header class="caja-header">
    <div class="caja-header__brand">
        <div class="caja-header__logo">SB</div>
        <div>
            <p class="caja-header__company">Grupo KGyR S.A.C</p>
            <p class="caja-header__app">Registro de ventas del turno</p>
        </div>
    </div>
    <div class="caja-header__right">
        <a href="<?= $basePath ?>/caja" class="caja-btn-back">← Volver</a>
    </div>
</header>

<main class="caja-main caja-main--narrow">

    <!-- Info de la sesión (sin montos, per policy) -->
    <section class="caja-card caja-card--info">
        <p class="caja-kicker">Sesión pendiente</p>
        <h2 class="caja-card__title" style="margin-bottom:0.5rem;">
            <?= htmlspecialchars($sesion['caja_desc']) ?> — <?= htmlspecialchars($sesion['local_desc']) ?>
        </h2>
        <div class="caja-info-row">
            <span><?= htmlspecialchars($sesion['turno_desc']) ?></span>
            <span><?= date('d/m/Y', strtotime($sesion['fecha_operacion'])) ?></span>
            <span>Cajera: <?= htmlspecialchars($sesion['cajera_nombre']) ?></span>
        </div>
        <div class="caja-ventas-note">
            El detalle financiero de esta sesión será visible en el reporte
            <strong>después</strong> de ingresar las ventas.
        </div>
    </section>

    <!-- Input de ventas -->
    <section class="caja-card">
        <h2 class="caja-card__title">Total de ventas del día (ERP)</h2>
        <p class="caja-card__desc">
            Ingresa el monto total de ventas según el sistema de ventas (ERP). Este valor es la suma
            de todas las ventas realizadas durante el turno.
        </p>

        <div class="caja-ventas-input-wrap">
            <label class="caja-ventas-label">Monto total de ventas</label>
            <div class="caja-input-money caja-input-money--lg">
                <span>S/</span>
                <input type="number" id="montoVentas" class="caja-input caja-input--money"
                       min="0" step="0.01" placeholder="0.00" autofocus>
            </div>
        </div>

        <div id="ventasMsg" class="caja-alert" hidden></div>

        <div class="caja-actions" style="margin-top:1.5rem;">
            <button class="caja-btn caja-btn--primary" onclick="submitVentas(<?= $sesion['id_sesion'] ?>)" id="btnVentas">
                Confirmar ventas y calcular cuadre →
            </button>
        </div>
    </section>

</main>

<script>
const BASE = '<?= $basePath ?>';

async function submitVentas(sesionId) {
    const monto = parseFloat(document.getElementById('montoVentas').value);
    const msg   = document.getElementById('ventasMsg');
    const btn   = document.getElementById('btnVentas');

    if (isNaN(monto) || monto < 0) {
        showAlert(msg, 'Ingresa un monto válido (puede ser 0 si no hubo ventas).', 'error');
        return;
    }

    btn.disabled     = true;
    btn.textContent  = 'Procesando...';
    msg.hidden       = true;

    try {
        const r   = await fetch(`${BASE}/caja/api/${sesionId}/ventas`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ monto_ventas: monto }),
        });
        const res = await r.json();

        if (res.success) {
            window.location.href = `${BASE}/caja/reporte/${sesionId}`;
        } else {
            showAlert(msg, res.message || 'Error al procesar.', 'error');
            btn.disabled    = false;
            btn.textContent = 'Confirmar ventas y calcular cuadre →';
        }
    } catch {
        showAlert(msg, 'Error de conexión.', 'error');
        btn.disabled    = false;
        btn.textContent = 'Confirmar ventas y calcular cuadre →';
    }
}

function showAlert(el, txt, type) {
    el.textContent = txt;
    el.className   = `caja-alert caja-alert--${type}`;
    el.hidden      = false;
}
</script>
</body>
</html>
