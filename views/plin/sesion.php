<?php
/** @var array $sesion */ /** @var array $pagosLibres */ /** @var array $pagosReclamados */
$basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
$userName = $userName ?? $_SESSION['user_name'] ?? 'Usuario';
$userRol  = $userRol  ?? $_SESSION['user_rol']  ?? 'STAFF';
$sesion          = $sesion          ?? [];
$pagosLibres     = $pagosLibres     ?? [];
$pagosReclamados = $pagosReclamados ?? [];
$estaAbierta     = ($sesion['estado'] ?? '') === 'ABIERTA';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sesión PLIN #<?= $sesion['id'] ?> | SoloBoticas</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/caja.css">
    <link rel="icon" type="image/x-icon" href="<?= $basePath ?>/assets/img/logo.ico">
    <style>
        .ps-header {
            background: linear-gradient(135deg, #6d28d9, #7c3aed);
            border-radius: 14px;
            padding: 1.25rem 1.5rem;
            color: #fff;
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.5rem;
        }
        .ps-header__info p { margin: 0; }
        .ps-header__title { font-size: 1.2rem; font-weight: 700; margin-bottom: .25rem; }
        .ps-header__meta  { font-size: .78rem; opacity: .8; }
        .ps-header__kpi   { text-align: right; }
        .ps-header__total { font-size: 2rem; font-weight: 800; }
        .ps-header__sub   { font-size: .75rem; opacity: .75; }
        .ps-badge-abierta { background:#d1fae5;color:#065f46;border:1px solid #6ee7b7;border-radius:20px;padding:3px 10px;font-size:.72rem;font-weight:700; }
        .ps-badge-cerrada { background:#f1f5f9;color:#64748b;border:1px solid #cbd5e1;border-radius:20px;padding:3px 10px;font-size:.72rem;font-weight:700; }

        .ps-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; }
        @media (max-width: 720px) { .ps-grid { grid-template-columns: 1fr; } }

        .ps-panel { background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:1rem; }
        .ps-panel__title { font-size:.85rem;font-weight:700;color:#475569;margin-bottom:.75rem;display:flex;justify-content:space-between;align-items:center; }

        .ps-pago {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: .7rem .9rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .6rem;
            margin-bottom: .4rem;
        }
        .ps-pago--reclamado { background: #f0fdf4; border-color: #bbf7d0; }
        .ps-pago__monto { font-size: 1rem; font-weight: 800; color: #6d28d9; white-space: nowrap; }
        .ps-pago__monto--rec { color: #16a34a; }
        .ps-pago__cliente { font-size: .82rem; font-weight: 600; color: #1e293b; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 160px; }
        .ps-pago__meta { font-size: .68rem; color: #94a3b8; }
        .ps-btn-rec {
            background: #6d28d9;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 5px 12px;
            font-size: .76rem;
            font-weight: 700;
            cursor: pointer;
            white-space: nowrap;
            transition: background .15s;
        }
        .ps-btn-rec:hover { background: #5b21b6; }
        .ps-btn-rec:disabled { background: #a78bfa; cursor: not-allowed; }
        .ps-empty { font-size: .82rem; color: #94a3b8; text-align: center; padding: 1.5rem 0; }

        #toastOk {
            position: fixed; bottom: 1.5rem; right: 1.5rem;
            background: #16a34a; color: #fff;
            border-radius: 8px; padding: .6rem 1rem;
            font-size: .82rem; font-weight: 600;
            box-shadow: 0 4px 16px rgba(0,0,0,.2);
            display: none; z-index: 9999;
        }
    </style>
</head>
<body>

<header class="caja-header">
    <div class="caja-header__brand">
        <div class="caja-header__logo" style="background:#6d28d9;">QR</div>
        <div>
            <p class="caja-header__company">Grupo KGyR S.A.C</p>
            <p class="caja-header__app">Módulo <strong>PLIN</strong> — Sesión #<?= $sesion['id'] ?></p>
        </div>
    </div>
    <div class="caja-header__right">
        <span class="caja-header__user"><?= htmlspecialchars($userName) ?></span>
        <a href="<?= $basePath ?>/plin" class="caja-btn-back">← Volver</a>
        <a href="<?= $basePath ?>/logout" class="caja-btn-back" style="border-color:#fca5a5;color:#ef4444;">Salir</a>
    </div>
</header>

<main class="caja-main" style="max-width:1100px;">

    <!-- Cabecera sesión -->
    <div class="ps-header">
        <div class="ps-header__info">
            <p class="ps-header__title">
                <?= htmlspecialchars($sesion['caja_desc']) ?> — <?= htmlspecialchars($sesion['local_desc']) ?>
                &nbsp;
                <?php if ($estaAbierta): ?>
                    <span class="ps-badge-abierta">Abierta</span>
                <?php else: ?>
                    <span class="ps-badge-cerrada">Cerrada</span>
                <?php endif; ?>
            </p>
            <p class="ps-header__meta">
                <?= htmlspecialchars($sesion['turno_desc']) ?> · <?= date('d/m/Y', strtotime($sesion['fecha'])) ?>
                · Abierta <?= date('H:i', strtotime($sesion['abierta_en'])) ?>
                <?php if ($sesion['cerrada_en']): ?>
                    · Cerrada <?= date('H:i', strtotime($sesion['cerrada_en'])) ?>
                <?php endif; ?>
            </p>
            <p class="ps-header__meta" style="margin-top:.3rem;">
                <?php if ($sesion['cajera_nombre']): ?>Cajera: <strong><?= htmlspecialchars($sesion['cajera_nombre']) ?></strong><?php endif; ?>
                <?php if ($sesion['cajera_nombre'] && $sesion['vendedora_nombre']): ?> &nbsp;·&nbsp; <?php endif; ?>
                <?php if ($sesion['vendedora_nombre']): ?>Vendedora: <strong><?= htmlspecialchars($sesion['vendedora_nombre']) ?></strong><?php endif; ?>
                <?php if (!$sesion['cajera_nombre'] && !$sesion['vendedora_nombre']): ?><span style="opacity:.6;">Sin personal asignado</span><?php endif; ?>
            </p>
        </div>
        <div class="ps-header__kpi">
            <p class="ps-header__sub">Total reclamado</p>
            <p class="ps-header__total" id="totalReclamado">S/ <?= number_format((float)$sesion['total_reclamado'], 2) ?></p>
            <p class="ps-header__sub"><span id="numPagos"><?= (int)$sesion['num_pagos'] ?></span> pago<?= (int)$sesion['num_pagos'] !== 1 ? 's' : '' ?></p>
            <?php if ($estaAbierta): ?>
            <button onclick="cerrarSesion()" id="btnCerrar"
                    class="caja-btn caja-btn--outline"
                    style="margin-top:.75rem;border-color:#fff;color:#fff;font-size:.78rem;">
                Cerrar sesión
            </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="ps-grid">

        <!-- Panel izquierdo: pagos libres -->
        <div class="ps-panel">
            <div class="ps-panel__title">
                <span>Pagos PLIN sin reclamar</span>
                <span id="cntLibres" style="background:#fef3c7;color:#92400e;border:1px solid #fbbf24;border-radius:12px;padding:2px 8px;font-size:.7rem;">
                    <?= count($pagosLibres) ?>
                </span>
            </div>
            <div id="listaLibres">
                <?php if (empty($pagosLibres)): ?>
                <p class="ps-empty">No hay pagos pendientes de reclamar.</p>
                <?php else: ?>
                <?php foreach ($pagosLibres as $p): ?>
                <div class="ps-pago" data-id="<?= $p['id'] ?>">
                    <div style="overflow:hidden;">
                        <p class="ps-pago__cliente" title="<?= htmlspecialchars($p['cliente']) ?>"><?= htmlspecialchars($p['cliente']) ?></p>
                        <p class="ps-pago__meta"><?= date('d/m H:i', strtotime($p['fecha_notif'])) ?>
                            <?php if (!empty($p['subtexto'])): ?> · <?= htmlspecialchars($p['subtexto']) ?><?php endif; ?>
                        </p>
                    </div>
                    <div style="display:flex;align-items:center;gap:.5rem;flex-shrink:0;">
                        <span class="ps-pago__monto">S/ <?= number_format((float)$p['monto'], 2) ?></span>
                        <?php if ($estaAbierta): ?>
                        <button class="ps-btn-rec" onclick="reclamar(this, <?= $p['id'] ?>, <?= $sesion['id'] ?>)">
                            Reclamar
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Panel derecho: reclamados -->
        <div class="ps-panel">
            <div class="ps-panel__title">
                <span>Pagos reclamados en esta sesión</span>
                <span id="cntReclamados" style="background:#d1fae5;color:#065f46;border:1px solid #6ee7b7;border-radius:12px;padding:2px 8px;font-size:.7rem;">
                    <?= count($pagosReclamados) ?>
                </span>
            </div>
            <div id="listaReclamados">
                <?php if (empty($pagosReclamados)): ?>
                <p class="ps-empty" id="emptyRec">Aún no hay pagos reclamados.</p>
                <?php else: ?>
                <?php foreach ($pagosReclamados as $p): ?>
                <div class="ps-pago ps-pago--reclamado">
                    <div style="overflow:hidden;">
                        <p class="ps-pago__cliente" title="<?= htmlspecialchars($p['cliente']) ?>"><?= htmlspecialchars($p['cliente']) ?></p>
                        <p class="ps-pago__meta">
                            <?= date('d/m H:i', strtotime($p['fecha_notif'])) ?>
                            <?php if (!empty($p['subtexto'])): ?> · <?= htmlspecialchars($p['subtexto']) ?><?php endif; ?>
                        </p>
                        <p class="ps-pago__meta" style="color:#16a34a;">✓ <?= date('H:i', strtotime($p['reclamado_en'])) ?></p>
                    </div>
                    <span class="ps-pago__monto ps-pago__monto--rec">S/ <?= number_format((float)$p['monto'], 2) ?></span>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>

</main>

<div id="toastOk">✓ Pago reclamado</div>

<script>
const BASE       = '<?= $basePath ?>';
const SESION_ID  = <?= (int)$sesion['id'] ?>;
const ESTA_ABIERTA = <?= $estaAbierta ? 'true' : 'false' ?>;

// Reclamar un pago
async function reclamar(btn, pagoId, sesionId) {
    btn.disabled = true;
    btn.textContent = '…';
    try {
        const res  = await fetch(`${BASE}/plin/api/sesion/${sesionId}/reclamar/${pagoId}`, { method: 'POST' });
        const json = await res.json();
        if (res.ok) {
            // Quitar tarjeta del panel izquierdo
            const card = btn.closest('.ps-pago');
            card.remove();
            actualizarCntLibres(-1);
            // Agregar al panel derecho (sencillo, el polling lo hace limpio)
            mostrarToast();
            actualizarTotales(json.data.total_reclamado, json.data.num_pagos);
            // Refrescar lista reclamados via API
            refreshReclamados();
        } else {
            alert(json.message || 'Error al reclamar');
            btn.disabled = false;
            btn.textContent = 'Reclamar';
        }
    } catch {
        alert('Error de red');
        btn.disabled = false;
        btn.textContent = 'Reclamar';
    }
}

async function refreshReclamados() {
    const res  = await fetch(`${BASE}/plin/api/sesion/${SESION_ID}/reclamados`);
    const json = await res.json();
    if (!res.ok) return;
    const lista = document.getElementById('listaReclamados');
    const pagos = json.data?.pagos ?? [];
    document.getElementById('cntReclamados').textContent = pagos.length;
    if (pagos.length === 0) {
        lista.innerHTML = '<p class="ps-empty" id="emptyRec">Aún no hay pagos reclamados.</p>';
        return;
    }
    lista.innerHTML = pagos.map(p => `
        <div class="ps-pago ps-pago--reclamado">
            <div style="overflow:hidden;">
                <p class="ps-pago__cliente" title="${esc(p.cliente)}">${esc(p.cliente)}</p>
                <p class="ps-pago__meta">${fmtFecha(p.fecha_notif)}${p.subtexto ? ' · ' + esc(p.subtexto) : ''}</p>
                <p class="ps-pago__meta" style="color:#16a34a;">✓ ${fmtHora(p.reclamado_en)}</p>
            </div>
            <span class="ps-pago__monto ps-pago__monto--rec">S/ ${parseFloat(p.monto).toFixed(2)}</span>
        </div>
    `).join('');
}

// Polling cada 12s para nuevos pagos libres
<?php if ($estaAbierta): ?>
setInterval(async () => {
    const res  = await fetch(`${BASE}/plin/api/sesion/${SESION_ID}/pagos-libres`);
    const json = await res.json();
    if (!res.ok) return;
    const pagos = json.data?.pagos ?? [];
    const lista = document.getElementById('listaLibres');

    // IDs ya mostrados
    const yaIds = new Set([...lista.querySelectorAll('[data-id]')].map(el => parseInt(el.dataset.id)));
    let nuevos = 0;
    for (const p of pagos) {
        if (yaIds.has(parseInt(p.id))) continue;
        nuevos++;
        const div = document.createElement('div');
        div.className = 'ps-pago';
        div.dataset.id = p.id;
        div.innerHTML = `
            <div style="overflow:hidden;">
                <p class="ps-pago__cliente" title="${esc(p.cliente)}">${esc(p.cliente)}</p>
                <p class="ps-pago__meta">${fmtFecha(p.fecha_notif)}${p.subtexto ? ' · ' + esc(p.subtexto) : ''}</p>
            </div>
            <div style="display:flex;align-items:center;gap:.5rem;flex-shrink:0;">
                <span class="ps-pago__monto">S/ ${parseFloat(p.monto).toFixed(2)}</span>
                <button class="ps-btn-rec" onclick="reclamar(this, ${p.id}, ${SESION_ID})">Reclamar</button>
            </div>`;
        lista.insertBefore(div, lista.firstChild);
    }
    document.getElementById('cntLibres').textContent = pagos.length;
    if (pagos.length === 0 && lista.querySelector('.ps-empty') === null) {
        lista.innerHTML = '<p class="ps-empty">No hay pagos pendientes de reclamar.</p>';
    }
}, 12000);
<?php endif; ?>

async function cerrarSesion() {
    if (!confirm('¿Cerrar esta sesión PLIN? No podrás reclamar más pagos en ella.')) return;
    const btn = document.getElementById('btnCerrar');
    btn.disabled = true; btn.textContent = 'Cerrando…';
    const res  = await fetch(`${BASE}/plin/api/sesion/${SESION_ID}/cerrar`, { method: 'POST' });
    const json = await res.json();
    if (res.ok) {
        actualizarTotales(json.data.total_reclamado, json.data.num_pagos);
        window.location.reload();
    } else {
        alert(json.message || 'Error al cerrar');
        btn.disabled = false; btn.textContent = 'Cerrar sesión';
    }
}

function actualizarTotales(total, numPagos) {
    document.getElementById('totalReclamado').textContent = 'S/ ' + parseFloat(total).toFixed(2);
    document.getElementById('numPagos').textContent = numPagos;
}
function actualizarCntLibres(delta) {
    const el = document.getElementById('cntLibres');
    el.textContent = Math.max(0, parseInt(el.textContent) + delta);
}
function mostrarToast() {
    const t = document.getElementById('toastOk');
    t.style.display = 'block';
    setTimeout(() => { t.style.display = 'none'; }, 2200);
}
function esc(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function fmtFecha(s) {
    if (!s) return '—';
    const d = new Date(s.replace(' ', 'T'));
    return (d.getDate()+'').padStart(2,'0') + '/' + ((d.getMonth()+1)+'').padStart(2,'0') + ' ' +
           (d.getHours()+'').padStart(2,'0') + ':' + (d.getMinutes()+'').padStart(2,'0');
}
function fmtHora(s) {
    if (!s) return '—';
    const d = new Date(s.replace(' ', 'T'));
    return (d.getHours()+'').padStart(2,'0') + ':' + (d.getMinutes()+'').padStart(2,'0');
}
</script>
</body>
</html>
