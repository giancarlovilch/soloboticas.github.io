<?php
/** @var array $sesiones */ /** @var array $pagosLibres */ /** @var int $totalLibres */
/** @var array $cajas */ /** @var array $turnos */ /** @var array $staff */
$basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
$userName = $userName ?? $_SESSION['user_name'] ?? 'Usuario';
$userRol  = $userRol  ?? $_SESSION['user_rol']  ?? 'STAFF';
$sesiones     = $sesiones    ?? [];
$pagosLibres  = $pagosLibres ?? [];
$totalLibres  = $totalLibres ?? 0;
$cajas        = $cajas       ?? [];
$turnos       = $turnos      ?? [];
$staff        = $staff       ?? [];

$sesionesAbiertas = array_filter($sesiones, fn($s) => $s['estado'] === 'ABIERTA');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PLIN — Cobros QR | SoloBoticas</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/caja.css">
    <link rel="icon" type="image/x-icon" href="<?= $basePath ?>/assets/img/logo.ico">
    <style>
        .plin-hero {
            background: linear-gradient(135deg, #6d28d9, #7c3aed);
            border-radius: 14px;
            padding: 1.5rem 1.75rem;
            color: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.75rem;
        }
        .plin-hero__total { font-size: 2.4rem; font-weight: 800; letter-spacing: -.02em; }
        .plin-hero__label { font-size: .72rem; text-transform: uppercase; letter-spacing: .07em; opacity: .75; margin-bottom: .2rem; }
        .plin-hero__sub   { font-size: .8rem; opacity: .7; margin-top: .3rem; }
        .plin-badge-libre {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fbbf24;
            border-radius: 20px;
            padding: 3px 10px;
            font-size: .72rem;
            font-weight: 700;
        }
        .plin-badge-abierta {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
            border-radius: 20px;
            padding: 3px 10px;
            font-size: .72rem;
            font-weight: 700;
        }
        .plin-badge-cerrada {
            background: #f1f5f9;
            color: #64748b;
            border: 1px solid #cbd5e1;
            border-radius: 20px;
            padding: 3px 10px;
            font-size: .72rem;
            font-weight: 700;
        }
        /* Modal apertura */
        .plin-overlay {
            display: none;
            position: fixed; inset: 0;
            background: rgba(0,0,0,.55);
            z-index: 900;
            align-items: center;
            justify-content: center;
        }
        .plin-overlay.active { display: flex; }
        .plin-modal {
            background: #fff;
            border-radius: 14px;
            padding: 1.75rem;
            width: min(480px, 94vw);
            box-shadow: 0 20px 60px rgba(0,0,0,.2);
        }
        .plin-modal h3 { font-size: 1.1rem; font-weight: 700; margin-bottom: 1.2rem; color: #1e293b; }
        .plin-field { margin-bottom: 1rem; }
        .plin-field label { display: block; font-size: .8rem; font-weight: 600; color: #475569; margin-bottom: .35rem; }
        .plin-field select, .plin-field input { width: 100%; }
        .plin-modal-actions { display: flex; gap: .6rem; justify-content: flex-end; margin-top: 1.4rem; }
        .plin-card-pago {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: .9rem 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .75rem;
            margin-bottom: .5rem;
            transition: box-shadow .15s;
        }
        .plin-card-pago:hover { box-shadow: 0 2px 12px rgba(0,0,0,.08); }
        .plin-card-pago__monto { font-size: 1.15rem; font-weight: 800; color: #6d28d9; white-space: nowrap; }
        .plin-card-pago__cliente { font-size: .88rem; font-weight: 600; color: #1e293b; }
        .plin-card-pago__meta { font-size: .7rem; color: #94a3b8; }
    </style>
</head>
<body>

<header class="caja-header">
    <div class="caja-header__brand">
        <div class="caja-header__logo" style="background:#6d28d9;">QR</div>
        <div>
            <p class="caja-header__company">Grupo KGyR S.A.C</p>
            <p class="caja-header__app">Módulo <strong>PLIN</strong> — Cobros por QR</p>
        </div>
    </div>
    <div class="caja-header__right">
        <span class="caja-header__user"><?= htmlspecialchars($userName) ?></span>
        <a href="<?= $basePath ?>/<?= $userRol === 'ADMIN' ? 'admin/dashboard' : 'staff' ?>" class="caja-btn-back">← Dashboard</a>
        <a href="<?= $basePath ?>/logout" class="caja-btn-back" style="border-color:#fca5a5;color:#ef4444;">Salir</a>
    </div>
</header>

<main class="caja-main" style="max-width:1100px;">

    <!-- Hero -->
    <?php
        $totalAcum = array_sum(array_column($sesiones, 'total_reclamado'));
        $numAbiertos = count($sesionesAbiertas);
    ?>
    <div class="plin-hero">
        <div>
            <p class="plin-hero__label">Total reclamado (todas las sesiones)</p>
            <p class="plin-hero__total">S/ <?= number_format($totalAcum, 2) ?></p>
            <p class="plin-hero__sub"><?= count($sesiones) ?> sesión<?= count($sesiones) !== 1 ? 'es' : '' ?> registrada<?= count($sesiones) !== 1 ? 's' : '' ?></p>
        </div>
        <div style="text-align:right;">
            <p class="plin-hero__label">Pagos sin reclamar</p>
            <p style="font-size:1.6rem;font-weight:700;"><?= $totalLibres ?></p>
            <p class="plin-hero__sub">pendientes de asignación a caja</p>
        </div>
    </div>

    <!-- Sesiones abiertas -->
    <?php if ($numAbiertos > 0): ?>
    <section class="caja-section">
        <div class="caja-section__header">
            <div>
                <p class="caja-kicker">En curso</p>
                <h2>Sesiones PLIN abiertas <span class="caja-badge-count"><?= $numAbiertos ?></span></h2>
            </div>
        </div>
        <div class="caja-cards">
            <?php foreach ($sesionesAbiertas as $s): ?>
            <div class="caja-card caja-card--action">
                <div class="caja-card__top">
                    <div>
                        <p class="caja-card__caja"><?= htmlspecialchars($s['caja_desc']) ?> — <?= htmlspecialchars($s['local_desc']) ?></p>
                        <p class="caja-card__meta"><?= htmlspecialchars($s['turno_desc']) ?> · <?= date('d/m/Y', strtotime($s['fecha'])) ?></p>
                        <?php if ($s['cajera_nombre']): ?>
                        <p class="caja-card__cajera">Cajera: <?= htmlspecialchars($s['cajera_nombre']) ?></p>
                        <?php endif; ?>
                        <?php if ($s['vendedora_nombre']): ?>
                        <p class="caja-card__cajera">Vendedora: <?= htmlspecialchars($s['vendedora_nombre']) ?></p>
                        <?php endif; ?>
                    </div>
                    <div style="text-align:right;">
                        <span class="plin-badge-abierta">Abierta</span>
                        <p style="font-size:1.1rem;font-weight:700;color:#6d28d9;margin-top:.5rem;">
                            S/ <?= number_format($s['total_reclamado'], 2) ?>
                        </p>
                        <p style="font-size:.72rem;color:#94a3b8;"><?= $s['num_pagos'] ?> pago<?= $s['num_pagos'] !== 1 ? 's' : '' ?></p>
                    </div>
                </div>
                <div class="caja-card__actions">
                    <a href="<?= $basePath ?>/plin/sesion/<?= $s['id'] ?>" class="caja-btn caja-btn--primary">
                        Continuar sesión →
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Apertura + pagos libres -->
    <section class="caja-section">
        <div class="caja-section__header">
            <div>
                <p class="caja-kicker">Nuevo registro</p>
                <h2>Abrir sesión PLIN</h2>
            </div>
            <button class="caja-btn caja-btn--primary" onclick="abrirModalSesion()">
                + Abrir sesión
            </button>
        </div>

        <?php if (!empty($pagosLibres)): ?>
        <div style="margin-top:.75rem;">
            <p style="font-size:.8rem;color:#92400e;background:#fef3c7;border:1px solid #fbbf24;border-radius:8px;padding:.6rem .9rem;margin-bottom:1rem;">
                <strong><?= $totalLibres ?> pago<?= $totalLibres !== 1 ? 's' : '' ?> sin reclamar</strong> — Abre una sesión y reclama los pagos para asignarlos a una caja.
            </p>
            <div>
                <?php foreach (array_slice($pagosLibres, 0, 10) as $p): ?>
                <div class="plin-card-pago">
                    <div>
                        <p class="plin-card-pago__cliente"><?= htmlspecialchars($p['cliente']) ?></p>
                        <p class="plin-card-pago__meta">
                            <?= date('d/m/Y H:i', strtotime($p['fecha_notif'])) ?>
                            <?php if (!empty($p['subtexto'])): ?> · <?= htmlspecialchars($p['subtexto']) ?><?php endif; ?>
                        </p>
                    </div>
                    <p class="plin-card-pago__monto">S/ <?= number_format((float)$p['monto'], 2) ?></p>
                </div>
                <?php endforeach; ?>
                <?php if ($totalLibres > 10): ?>
                <p style="font-size:.75rem;color:#94a3b8;text-align:center;margin-top:.5rem;">
                    … y <?= $totalLibres - 10 ?> más. Abre una sesión para verlos todos.
                </p>
                <?php endif; ?>
            </div>
        </div>
        <?php else: ?>
        <p style="font-size:.85rem;color:#94a3b8;margin-top:.5rem;">No hay pagos PLIN pendientes de reclamar.</p>
        <?php endif; ?>
    </section>

    <!-- Historial de sesiones -->
    <section class="caja-section">
        <h2 style="margin-bottom:.75rem;">Historial de sesiones PLIN</h2>
        <div class="caja-table-wrap">
            <table class="caja-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Fecha</th>
                        <th>Caja / Local</th>
                        <th>Turno</th>
                        <th>Cajera</th>
                        <th>Vendedora</th>
                        <th class="text-right">Total</th>
                        <th class="text-center">Pagos</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Acción</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($sesiones as $s): ?>
                <tr>
                    <td><code style="font-size:.75rem;color:#475569;">#<?= $s['id'] ?></code></td>
                    <td>
                        <?= date('d/m/Y', strtotime($s['fecha'])) ?>
                        <span style="color:#94a3b8;font-size:.72rem;display:block;">
                            <?= date('H:i', strtotime($s['abierta_en'])) ?>
                        </span>
                    </td>
                    <td>
                        <?= htmlspecialchars($s['caja_desc']) ?>
                        <span style="color:#94a3b8;font-size:.72rem;display:block;"><?= htmlspecialchars($s['local_desc']) ?></span>
                    </td>
                    <td><?= htmlspecialchars($s['turno_desc']) ?></td>
                    <td><?= $s['cajera_nombre'] ? htmlspecialchars($s['cajera_nombre']) : '<span style="color:#cbd5e1">—</span>' ?></td>
                    <td><?= $s['vendedora_nombre'] ? htmlspecialchars($s['vendedora_nombre']) : '<span style="color:#cbd5e1">—</span>' ?></td>
                    <td class="text-right">
                        <strong style="color:#6d28d9;">S/ <?= number_format((float)$s['total_reclamado'], 2) ?></strong>
                    </td>
                    <td class="text-center"><?= (int)$s['num_pagos'] ?></td>
                    <td class="text-center">
                        <?php if ($s['estado'] === 'ABIERTA'): ?>
                            <span class="plin-badge-abierta">Abierta</span>
                        <?php else: ?>
                            <span class="plin-badge-cerrada">Cerrada</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <a href="<?= $basePath ?>/plin/sesion/<?= $s['id'] ?>" class="caja-link">
                            <?= $s['estado'] === 'ABIERTA' ? 'Continuar' : 'Ver detalle' ?>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($sesiones)): ?>
                <tr><td colspan="10" class="caja-table__empty">No hay sesiones PLIN registradas.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

</main>

<!-- Modal apertura de sesión -->
<div class="plin-overlay" id="modalApertura">
    <div class="plin-modal">
        <h3>Abrir sesión PLIN</h3>
        <div class="plin-field">
            <label>Caja *</label>
            <select id="mCaja" class="caja-input">
                <option value="">— Selecciona caja —</option>
                <?php foreach ($cajas as $c): ?>
                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['descripcion']) ?> · <?= htmlspecialchars($c['local_desc']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="plin-field">
            <label>Turno *</label>
            <select id="mTurno" class="caja-input">
                <option value="">— Selecciona turno —</option>
                <?php foreach ($turnos as $t): ?>
                <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['descripcion']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="plin-field">
            <label>Fecha *</label>
            <input type="date" id="mFecha" class="caja-input" value="<?= date('Y-m-d') ?>">
        </div>
        <div class="plin-field">
            <label>Cajera (opcional)</label>
            <select id="mCajera" class="caja-input">
                <option value="">— Sin especificar —</option>
                <?php foreach ($staff as $p): ?>
                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombres']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="plin-field">
            <label>Vendedora (opcional)</label>
            <select id="mVendedora" class="caja-input">
                <option value="">— Sin especificar —</option>
                <?php foreach ($staff as $p): ?>
                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombres']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="plin-modal-actions">
            <button class="caja-btn caja-btn--outline" onclick="cerrarModalSesion()">Cancelar</button>
            <button class="caja-btn caja-btn--primary" id="btnAbrirSesion" onclick="confirmarAbrirSesion()">Abrir sesión</button>
        </div>
    </div>
</div>

<script>
const BASE = '<?= $basePath ?>';

function abrirModalSesion()  { document.getElementById('modalApertura').classList.add('active'); }
function cerrarModalSesion() { document.getElementById('modalApertura').classList.remove('active'); }

async function confirmarAbrirSesion() {
    const cajaId     = document.getElementById('mCaja').value;
    const turnoId    = document.getElementById('mTurno').value;
    const fecha      = document.getElementById('mFecha').value;
    const cajeraId   = document.getElementById('mCajera').value;
    const vendedoraId= document.getElementById('mVendedora').value;

    if (!cajaId || !turnoId || !fecha) {
        alert('Completa Caja, Turno y Fecha.');
        return;
    }

    const btn = document.getElementById('btnAbrirSesion');
    btn.disabled = true; btn.textContent = 'Abriendo…';

    try {
        const res = await fetch(`${BASE}/plin/api/sesion`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                caja_id:      parseInt(cajaId),
                turno_id:     parseInt(turnoId),
                fecha,
                cajera_id:    cajeraId   ? parseInt(cajeraId)    : null,
                vendedora_id: vendedoraId ? parseInt(vendedoraId) : null,
            }),
        });
        const json = await res.json();
        if (json.data?.id) {
            window.location.href = `${BASE}/plin/sesion/${json.data.id}`;
        } else {
            alert(json.message || 'Error al abrir sesión');
            btn.disabled = false; btn.textContent = 'Abrir sesión';
        }
    } catch (e) {
        alert('Error de red');
        btn.disabled = false; btn.textContent = 'Abrir sesión';
    }
}

// Cerrar modal con Escape
document.addEventListener('keydown', e => { if (e.key === 'Escape') cerrarModalSesion(); });
document.getElementById('modalApertura').addEventListener('click', e => {
    if (e.target === e.currentTarget) cerrarModalSesion();
});
</script>
</body>
</html>
