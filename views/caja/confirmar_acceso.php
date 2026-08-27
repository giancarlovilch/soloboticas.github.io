<?php
/** @var int $sesionId */
/** @var string $origen  'REPORTE' | 'INCIDENCIA' */
$basePath       = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
$nombreCompleto = $nombreCompleto ?? $userName ?? $_SESSION['user_name'] ?? 'Usuario';
$origen         = in_array($origen ?? '', ['REPORTE', 'INCIDENCIA'], true) ? $origen : 'REPORTE';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar acceso | Solo Boticas</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="icon" type="image/x-icon" href="<?= $basePath ?>/assets/img/logo.ico">
    <style>
        body { margin: 0; font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; background: #f1f5f9; }
        .cca-wrap { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1rem; }
        .cca-card {
            max-width: 430px; width: 100%; background: #fff; border-radius: 14px;
            box-shadow: 0 10px 30px rgba(0,0,0,.1); padding: 1.85rem;
            border-top: 4px solid #f59e0b;
        }
        .cca-card h1 { font-size: 1.05rem; margin: 0 0 .7rem; color: #92400e; display: flex; align-items: center; gap: .4rem; }
        .cca-card p { font-size: .85rem; color: #475569; line-height: 1.55; margin: 0 0 1.1rem; }
        .cca-card input {
            width: 100%; box-sizing: border-box; padding: .65rem .75rem; border: 1.5px solid #e2e8f0;
            border-radius: 8px; font-size: .9rem; margin-bottom: .8rem; outline: none;
        }
        .cca-card input:focus { border-color: #0097A7; }
        .cca-card button {
            width: 100%; padding: .7rem; border: none; border-radius: 8px; background: #0097A7;
            color: #fff; font-weight: 700; font-size: .9rem; cursor: pointer;
        }
        .cca-card button:disabled { opacity: .6; cursor: default; }
        .cca-cancel { display: block; text-align: center; margin-top: .9rem; font-size: .8rem; color: #64748b; text-decoration: none; }
        .cca-err {
            color: #dc2626; font-size: .8rem; margin-top: .6rem; display: none;
            background: #fee2e2; border-radius: 6px; padding: .5rem .6rem;
        }
    </style>
</head>
<body>
<div class="cca-wrap">
    <div class="cca-card">
        <h1>🔒 Acceso a cuadre cerrado</h1>
        <p>
            Hola <strong><?= htmlspecialchars($nombreCompleto) ?></strong>. Este arqueo ya está cerrado.
            Antes de continuar, confirma tu contraseña: tu ingreso quedará registrado con tu nombre,
            fecha y hora en la lista de empadronamiento de accesos, visible para administración.
        </p>
        <input type="password" id="pwd" placeholder="Tu contraseña" autofocus autocomplete="current-password">
        <button id="btnConfirmar" onclick="confirmarAcceso()">Confirmar e ingresar</button>
        <div id="err" class="cca-err"></div>
        <a class="cca-cancel" href="<?= $basePath ?>/caja">← Cancelar y volver a Caja</a>
    </div>
</div>
<script>
const BASE      = "<?= $basePath ?>";
const SESION_ID = <?= (int)$sesionId ?>;
const ORIGEN    = "<?= $origen ?>";

async function confirmarAcceso() {
    const pwd = document.getElementById('pwd').value;
    const err = document.getElementById('err');
    const btn = document.getElementById('btnConfirmar');
    err.style.display = 'none';
    if (!pwd) { err.textContent = 'Ingresa tu contraseña'; err.style.display = 'block'; return; }

    btn.disabled = true; btn.textContent = 'Verificando...';
    try {
        const r = await fetch(`${BASE}/caja/api/sesion/${SESION_ID}/confirmar-visita`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ password: pwd, origen: ORIGEN }),
        });
        const res = await r.json();
        if (res.success) {
            location.reload();
        } else {
            err.textContent = res.message || 'Contraseña incorrecta';
            err.style.display = 'block';
            btn.disabled = false; btn.textContent = 'Confirmar e ingresar';
        }
    } catch (e) {
        err.textContent = 'Error de conexión';
        err.style.display = 'block';
        btn.disabled = false; btn.textContent = 'Confirmar e ingresar';
    }
}

document.getElementById('pwd').addEventListener('keydown', e => {
    if (e.key === 'Enter') confirmarAcceso();
});
</script>
</body>
</html>
