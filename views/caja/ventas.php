<?php
/** @var array $sesion */
$basePath         = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
$userName         = $userName         ?? $_SESSION['user_name'] ?? 'Usuario';
$cajera_id        = $cajera_id        ?? 0;
$cajera_nombre    = $cajera_nombre    ?? '';
$vendedora_nombre = $vendedora_nombre ?? '';
$vendedoraUsuarioErp = $vendedoraUsuarioErp ?? null;
$localCodigo      = $localCodigo      ?? null;
$isAdmin          = $isAdmin          ?? false;

// Hay integración ERP si el local está mapeado a SOFTPHARMA y hay vendedora asignada.
$tieneErp = $localCodigo !== null && $vendedora_nombre !== '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Venta pendiente | Caja SB</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/caja.css">
</head>
<body>

<header class="caja-header">
    <div class="caja-header__brand">
        <div class="caja-header__logo">SB</div>
        <div>
            <p class="caja-header__company">Grupo KGyR S.A.C</p>
            <p class="caja-header__app">Venta pendiente del turno</p>
        </div>
    </div>
    <div class="caja-header__right">
        <a href="<?= $basePath ?>/caja" class="caja-btn-back">← Volver</a>
    </div>
</header>

<main class="caja-main caja-main--narrow">

    <section class="caja-card caja-card--info">
        <p class="caja-kicker">Sesión pendiente de venta</p>
        <h2 class="caja-card__title" style="margin-bottom:0.5rem;">
            <?= htmlspecialchars($sesion['caja_desc']) ?> — <?= htmlspecialchars($sesion['local_desc']) ?>
        </h2>
        <div class="caja-info-row">
            <span><?= htmlspecialchars($sesion['turno_desc']) ?></span>
            <span><?= date('d/m/Y', strtotime($sesion['fecha_operacion'])) ?></span>
        </div>
        <div class="caja-info-row" style="margin-top:.35rem;">
            <span>Cajera: <strong><?= htmlspecialchars($cajera_nombre) ?></strong></span>
            <?php if ($vendedora_nombre): ?>
            <span>Vendedora: <strong><?= htmlspecialchars($vendedora_nombre) ?></strong></span>
            <?php endif; ?>
        </div>
    </section>

    <?php if (!$tieneErp): ?>
    <!-- Local sin integración ERP, o sesión sin vendedora asignada -->
    <section class="caja-card">
        <h2 class="caja-card__title">Cerrar cuadre</h2>
        <p class="caja-card__desc">
            <?= $localCodigo === null
                ? 'Este local todavía no está conectado al sistema de ventas (ERP). Cierra el cuadre directamente.'
                : 'Esta sesión no tiene una vendedora asignada. Cierra el cuadre directamente.' ?>
        </p>
        <div id="ventasMsg" class="caja-alert" hidden></div>
        <div class="caja-actions" style="margin-top:1.5rem;">
            <button class="caja-btn caja-btn--primary" onclick="cerrarSinVentas()" id="btnCerrarSinVentas">
                Cerrar cuadre →
            </button>
        </div>
    </section>

    <?php else: ?>
    <!-- Integración ERP: esperar el cierre automático de SOFTPHARMA -->
    <section class="caja-card" id="cardEstado">
        <h2 class="caja-card__title">Venta del ERP (SOFTPHARMA)</h2>
        <p class="caja-card__desc">
            No hace falta que <strong><?= htmlspecialchars($vendedora_nombre) ?></strong> ingrese nada.
            En cuanto cierre su caja en el sistema de ventas, se detecta automáticamente.
        </p>

        <?php if (!$vendedoraUsuarioErp): ?>
        <div class="caja-alert caja-alert--error" style="display:block;">
            Falta emparejar a <?= htmlspecialchars($vendedora_nombre) ?> con su usuario SOFTPHARMA.
            Ve a <strong>Admin → Usuarios SOFTPHARMA</strong> y asígnalo antes de continuar.
        </div>
        <?php else: ?>

        <div id="estadoBox" style="padding:1rem 1.1rem;border-radius:10px;background:#f8fafc;border:1.5px solid #e2e8f0;margin:1rem 0;">
            <p id="estadoTexto" style="margin:0;font-size:.9rem;color:#475569;">Buscando el cierre en SOFTPHARMA…</p>
        </div>

        <div id="ventasMsg" class="caja-alert" hidden></div>

        <div class="caja-actions" style="margin-top:1rem;gap:.6rem;flex-wrap:wrap;">
            <button class="caja-btn caja-btn--primary" onclick="actualizarVenta()" id="btnActualizar">
                ↻ Actualizar
            </button>
            <button class="caja-btn caja-btn--secondary" onclick="cerrarSinVentas()" id="btnCerrarSinVentas2">
                Cerrar sin ventas
            </button>
        </div>
        <p style="font-size:.72rem;color:#94a3b8;margin-top:.5rem;">
            "Cerrar sin ventas" deja el cuadre cerrado sin monto de ventas, como una incidencia de
            faltante — el admin asigna el cierre de SOFTPHARMA desde ahí apenas aparezca
            (igual que ya hace con Culqi o los vales de Yape).
        </p>
        <?php endif; ?>
    </section>
    <?php endif; ?>

</main>

<script>
const BASE     = '<?= $basePath ?>';
const SESION_ID = <?= (int)$sesion['id_sesion'] ?>;
const ES_ADMIN  = <?= $isAdmin ? 'true' : 'false' ?>;
const TIENE_ERP = <?= $tieneErp && $vendedoraUsuarioErp ? 'true' : 'false' ?>;

function showAlert(el, txt, type) {
    el.textContent = txt;
    el.className   = `caja-alert caja-alert--${type}`;
    el.hidden      = false;
}

async function cerrarSinVentas() {
    if (!confirm('¿Cerrar este cuadre sin asignar una venta? Si había una vendedora asignada, quedará pendiente de revisión por el admin.')) return;
    const msg = document.getElementById('ventasMsg');
    try {
        const r   = await fetch(`${BASE}/caja/api/${SESION_ID}/cerrar-sin-ventas`, { method: 'POST' });
        const res = await r.json();
        if (res.success) {
            window.location.href = `${BASE}/caja`;
        } else {
            showAlert(msg, res.message || 'Error al cerrar.', 'error');
        }
    } catch {
        showAlert(msg, 'Error de conexión.', 'error');
    }
}

async function actualizarVenta() {
    const btn   = document.getElementById('btnActualizar');
    const texto = document.getElementById('estadoTexto');
    const msg   = document.getElementById('ventasMsg');
    btn.disabled = true;

    try {
        const r   = await fetch(`${BASE}/caja/api/${SESION_ID}/actualizar-venta`, { method: 'POST' });
        const res = await r.json();

        if (!res.success) {
            showAlert(msg, res.message || 'Error al actualizar.', 'error');
            btn.disabled = false;
            return;
        }

        const estado = res.data?.estado;
        if (estado === 'esperando') {
            texto.textContent = 'Todavía no se registra el cierre en SOFTPHARMA. Vuelve a intentar en un momento.';
        } else if (estado === 'requiere_admin') {
            texto.textContent = res.message + (ES_ADMIN ? ' Usa el selector de abajo.' : '');
            if (ES_ADMIN) cargarPicker();
        } else {
            // Se asignó y se calculó el cuadre
            window.location.href = `${BASE}/caja`;
            return;
        }
    } catch {
        showAlert(msg, 'Error de conexión.', 'error');
    }
    btn.disabled = false;
}

async function cargarPicker() {
    const box = document.getElementById('pickerBox');
    if (!box) return;
    box.innerHTML = '<p style="font-size:.82rem;color:#94a3b8;">Cargando…</p>';
    try {
        const r   = await fetch(`${BASE}/caja/api/${SESION_ID}/cierres-disponibles`);
        const res = await r.json();
        const cierres = res.data?.cierres || [];
        if (!cierres.length) {
            box.innerHTML = '<p style="font-size:.82rem;color:#94a3b8;">No hay cierres disponibles sin asignar todavía.</p>';
            return;
        }
        box.innerHTML = cierres.map(c => `
            <div style="display:flex;align-items:center;justify-content:space-between;gap:.6rem;
                        padding:.6rem .8rem;border:1.5px solid #e2e8f0;border-radius:8px;margin-bottom:.5rem;">
                <div style="font-size:.82rem;">
                    <strong>${c.nombre}</strong> (${c.usuario}) — ${c.turno}<br>
                    <span style="color:#64748b;">S/ ${c.venta_neta} · cierre ${(c.cierre || '').replace('T', ' ').slice(0, 16)}</span>
                </div>
                <button class="caja-btn caja-btn--primary" style="padding:.4rem .8rem;font-size:.78rem;"
                        onclick='asignarManual(${JSON.stringify(c)})'>Asignar</button>
            </div>
        `).join('');
    } catch {
        box.innerHTML = '<p style="font-size:.82rem;color:#dc2626;">Error al cargar los cierres disponibles.</p>';
    }
}

async function asignarManual(cierre) {
    if (!confirm(`¿Asignar la venta de ${cierre.nombre} (S/ ${cierre.venta_neta}) a este cuadre?`)) return;
    try {
        const r   = await fetch(`${BASE}/caja/api/${SESION_ID}/asignar-venta-manual`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id_caja: cierre.id_caja, usuario: cierre.usuario,
                venta_neta: cierre.venta_neta, cierre: cierre.cierre,
            }),
        });
        const res = await r.json();
        if (res.success) {
            window.location.href = `${BASE}/caja`;
        } else {
            alert(res.message || 'Error al asignar.');
        }
    } catch {
        alert('Error de conexión.');
    }
}

if (TIENE_ERP) {
    actualizarVenta();
    setInterval(actualizarVenta, 30000); // reintenta solo cada 30s, sin que la cajera tenga que presionar F5
}
</script>
</body>
</html>
