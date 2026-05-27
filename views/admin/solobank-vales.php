<?php
$basePath    = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
$userName    = $_SESSION['user_name'] ?? 'Usuario';
$vales       = $vales ?? [];
$filtroFecha = $filtroFecha ?? '';

$disponibles    = array_values(array_filter($vales, fn($v) => $v['estado'] === 'DISPONIBLE'));
$usados         = array_values(array_filter($vales, fn($v) => $v['estado'] === 'USADO'));
$totalPendiente = array_sum(array_column($disponibles, 'total'));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SoloBank | SoloBoticas</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/dashboard.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/caja.css">
    <style>
        .sb-stats { display:flex;gap:1rem;flex-wrap:wrap }
        .sb-stat  { background:rgba(255,255,255,.15);border-radius:8px;padding:7px 14px;text-align:center;min-width:80px }
        .sb-stat strong { display:block;font-size:1.4rem;font-weight:800;line-height:1.1 }
        .sb-stat span   { font-size:.72rem;opacity:.85 }
        .badge-disp { display:inline-block;padding:2px 10px;border-radius:20px;font-size:.7rem;font-weight:700;background:#d1fae5;color:#065f46 }
        .badge-used { display:inline-block;padding:2px 10px;border-radius:20px;font-size:.7rem;font-weight:700;background:#dbeafe;color:#1e40af }
        .section-lbl { font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;margin:1.5rem 0 .6rem;padding-bottom:.4rem;border-bottom:2px solid #e2e8f0 }
        .mono { font-family:monospace;font-size:.7rem;color:#94a3b8 }
        .text-right { text-align:right }
        .btn-toggle { border:none;border-radius:5px;padding:3px 10px;font-size:.72rem;font-weight:600;cursor:pointer;transition:opacity .15s }
        .btn-toggle:hover { opacity:.8 }
        .btn-toggle.disp  { background:#fee2e2;color:#b91c1c }
        .btn-toggle.usado { background:#dcfce7;color:#15803d }
        #toast { position:fixed;bottom:1.5rem;right:1.5rem;background:#1e293b;color:#fff;padding:10px 18px;border-radius:8px;font-size:.85rem;opacity:0;transition:opacity .3s;pointer-events:none;z-index:999 }
        #toast.show { opacity:1 }
    </style>
</head>
<body style="background:#f1f5f9;">

<header class="caja-header">
    <div class="caja-header__brand">
        <div class="caja-header__logo" style="background:#003da6;">SB</div>
        <div>
            <p class="caja-header__company">Grupo KGyR S.A.C</p>
            <p class="caja-header__app"><strong>SoloBank</strong> — Vales de cierres</p>
        </div>
    </div>
    <div class="caja-header__right">
        <span class="caja-header__user"><?= htmlspecialchars($userName) ?></span>
        <a href="<?= $basePath ?>/admin/dashboard" class="caja-btn-back">← Dashboard</a>
    </div>
</header>

<main class="caja-main" style="max-width:1060px;">

    <!-- Resumen -->
    <div style="background:linear-gradient(135deg,#003da6,#1a5fd4);border-radius:12px;padding:1.1rem 1.4rem;color:#fff;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.2rem;">
        <div>
            <h1 style="font-size:1.1rem;font-weight:700;margin:0">📋 SoloBank — Vales activos</h1>
            <p style="margin:.2rem 0 0;opacity:.75;font-size:.78rem">Enviados automáticamente al hacer cierre en Python · Recarga la página para ver los nuevos</p>
        </div>
        <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
            <div class="sb-stats">
                <div class="sb-stat">
                    <strong><?= count($disponibles) ?></strong>
                    <span>Disponibles</span>
                </div>
                <div class="sb-stat">
                    <strong><?= count($usados) ?></strong>
                    <span>Usados</span>
                </div>
                <div class="sb-stat">
                    <strong>S/ <?= number_format($totalPendiente, 2) ?></strong>
                    <span>Por cuadrar</span>
                </div>
            </div>
            <form method="GET" style="display:flex;align-items:center;gap:.4rem;margin-left:.5rem">
                <input type="date" name="fecha" value="<?= htmlspecialchars($filtroFecha) ?>"
                       style="border:1px solid rgba(255,255,255,.4);background:rgba(255,255,255,.15);color:#fff;border-radius:6px;padding:4px 8px;font-size:.78rem;">
                <button type="submit" style="background:rgba(255,255,255,.2);color:#fff;border:1px solid rgba(255,255,255,.4);border-radius:6px;padding:4px 10px;font-size:.78rem;cursor:pointer;font-weight:600">Filtrar</button>
                <?php if ($filtroFecha): ?>
                    <a href="?fecha=" style="color:rgba(255,255,255,.7);font-size:.75rem;text-decoration:none">✕ Todos</a>
                <?php endif ?>
            </form>
        </div>
    </div>

    <!-- Tabla unificada -->
    <?php if (empty($vales)): ?>
        <p style="color:#94a3b8;font-size:.875rem;padding:1rem 0">Aún no se han recibido vales desde Python.</p>
    <?php else: ?>
    <div class="caja-table-wrap">
        <table class="caja-table">
            <thead>
                <tr>
                    <th>Caja SB</th>
                    <th>Fecha</th>
                    <th>Turno</th>
                    <th class="text-right">Total</th>
                    <th>Pagos</th>
                    <th>Estado</th>
                    <th>Sesión / Caja</th>
                    <th>Recibido</th>
                    <th style="text-align:center">Acción</th>
                </tr>
            </thead>
            <tbody id="tbodyVales">
            <?php foreach ($vales as $v):
                $esDis    = $v['estado'] === 'DISPONIBLE';
                $sesionId = (int)($v['sesion_id'] ?? 0);
                $movId    = (int)($v['movimiento_id'] ?? 0);
                $monto    = number_format((float)$v['total'], 2);
            ?>
                <tr id="row-<?= $v['id'] ?>">
                    <td><strong><?= htmlspecialchars($v['caja']) ?></strong></td>
                    <td><?= date('d/m/Y', strtotime($v['fecha'])) ?></td>
                    <td><?= htmlspecialchars($v['turno']) ?></td>
                    <td class="text-right"><strong>S/ <?= $monto ?></strong></td>
                    <td><?= (int)$v['conteo'] ?></td>
                    <td>
                        <span id="badge-<?= $v['id'] ?>" class="<?= $esDis ? 'badge-disp' : 'badge-used' ?>">
                            <?= $esDis ? 'DISPONIBLE' : 'USADO' ?>
                        </span>
                    </td>
                    <td>
                        <?php if (!$esDis && $sesionId): ?>
                            <a href="<?= $basePath ?>/caja/reporte/<?= $sesionId ?>"
                               target="_blank"
                               style="font-size:.78rem;font-weight:700;color:#1e40af;text-decoration:none;"
                               title="Ver arqueo de la sesión">
                                #<?= $sesionId ?> ↗
                            </a>
                            <span style="display:block;font-size:.7rem;color:#94a3b8;">
                                <?= htmlspecialchars($v['caja_local_desc'] ?? '—') ?>
                            </span>
                        <?php else: ?>
                            <span style="font-size:.78rem;color:#94a3b8;">
                                <?= htmlspecialchars($v['caja_local_desc'] ?? '—') ?>
                            </span>
                        <?php endif; ?>
                    </td>
                    <td><?= date('d/m H:i', strtotime($v['recibido_en'])) ?></td>
                    <td style="text-align:center">
                        <?php if ($esDis): ?>
                        <button id="btn-<?= $v['id'] ?>"
                                class="btn-toggle disp"
                                onclick="toggle(<?= $v['id'] ?>, false, 0, '0.00')">
                            Marcar usado
                        </button>
                        <?php else: ?>
                        <button id="btn-<?= $v['id'] ?>"
                                class="btn-toggle usado"
                                onclick="toggle(<?= $v['id'] ?>, true, <?= $sesionId ?>, '<?= $monto ?>')">
                            Liberar
                        </button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>

        <!-- Modal de confirmación para liberar -->
        <div id="modalLiberar" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.55);z-index:500;align-items:center;justify-content:center;">
            <div style="background:#fff;border-radius:14px;padding:1.75rem;width:340px;max-width:90vw;box-shadow:0 20px 60px rgba(0,0,0,.22);">
                <h3 style="font-size:1rem;font-weight:700;color:#1e293b;margin:0 0 .35rem;">Liberar vale SoloBank</h3>
                <p id="liberarMsg" style="font-size:.82rem;color:#475569;margin:0 0 1rem;line-height:1.5;"></p>
                <div style="background:#fef3c7;border-radius:8px;padding:.6rem .9rem;margin-bottom:1rem;">
                    <p style="font-size:.76rem;color:#92400e;margin:0;">
                        ⚠ Si el vale está asignado a una sesión, el cobro electrónico será eliminado del cuadre de esa cajera.
                    </p>
                </div>
                <div id="liberarErr" style="font-size:.78rem;color:#991b1b;margin-bottom:.5rem;display:none;"></div>
                <div style="display:flex;gap:.6rem;justify-content:flex-end;">
                    <button onclick="cerrarModalLiberar()"
                        style="background:#f1f5f9;border:none;border-radius:7px;padding:.5rem 1.1rem;font-size:.82rem;cursor:pointer;color:#475569;">
                        Cancelar
                    </button>
                    <button id="btnConfirmarLiberar"
                        style="background:#dc2626;border:none;border-radius:7px;padding:.5rem 1.1rem;font-size:.82rem;font-weight:700;color:#fff;cursor:pointer;">
                        Sí, liberar
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endif ?>

</main>

<div id="toast"></div>

<script>
const BASE = '<?= $basePath ?>';

// id actual pendiente de confirmar
let _liberarId = null;

function toggle(id, esLiberar, sesionId, monto) {
    if (esLiberar) {
        // Mostrar modal de confirmación antes de liberar
        _liberarId = id;
        const msg = sesionId > 0
            ? `El vale S/ ${monto} está asignado a la sesión #${sesionId}.\nAl liberarlo, se eliminará el cobro electrónico de ese cuadre.`
            : `El vale S/ ${monto} será marcado como disponible nuevamente.`;
        document.getElementById('liberarMsg').textContent = msg;
        document.getElementById('liberarErr').style.display = 'none';
        document.getElementById('modalLiberar').style.display = 'flex';
    } else {
        // Marcar como usado: sin confirmación
        ejecutarToggle(id);
    }
}

function cerrarModalLiberar() {
    document.getElementById('modalLiberar').style.display = 'none';
    _liberarId = null;
}

document.getElementById('btnConfirmarLiberar')?.addEventListener('click', async function() {
    if (!_liberarId) return;
    this.disabled = true;
    await ejecutarToggle(_liberarId);
    cerrarModalLiberar();
    this.disabled = false;
});

async function ejecutarToggle(id) {
    const btn = document.getElementById('btn-' + id);
    if (btn) btn.disabled = true;

    try {
        const r   = await fetch(`${BASE}/admin/solobank-vales/${id}/toggle`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' }
        });
        const res = await r.json();
        if (!res.success) {
            showToast(res.message || 'Error');
            if (btn) btn.disabled = false;
            return;
        }

        const nuevoEstado = res.data.nuevo_estado;
        const esDis       = nuevoEstado === 'DISPONIBLE';
        const sesIdNuevo  = res.data.sesion_id ?? 0;

        // Actualizar badge
        const badge = document.getElementById('badge-' + id);
        if (badge) {
            badge.className   = esDis ? 'badge-disp' : 'badge-used';
            badge.textContent = esDis ? 'DISPONIBLE' : 'USADO';
        }

        // Actualizar botón
        if (btn) {
            btn.className   = 'btn-toggle ' + (esDis ? 'disp' : 'usado');
            btn.textContent = esDis ? 'Marcar usado' : 'Liberar';
            btn.setAttribute('onclick', `toggle(${id}, ${!esDis}, ${sesIdNuevo}, '${btn.closest('tr')?.querySelector('.text-right strong')?.textContent?.replace('S/ ','') ?? '0.00'}')`);
            btn.disabled = false;
        }

        // Actualizar celda de sesión al liberar (quitar el link)
        if (esDis) {
            const row = document.getElementById('row-' + id);
            const sesionCell = row?.cells[6];
            if (sesionCell) {
                sesionCell.innerHTML = '<span style="font-size:.78rem;color:#94a3b8;">—</span>';
            }
        }

        showToast(esDis ? '✅ Vale liberado y cobro eliminado del cuadre' : '🔒 Vale marcado como usado');
    } catch {
        showToast('Error de conexión');
        if (btn) btn.disabled = false;
    }
}

function showToast(msg) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 3000);
}
</script>
</body>
</html>
