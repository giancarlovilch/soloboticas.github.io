<?php
/** @var array $items */ /** @var array $cajas */ /** @var array $cajeras */
$basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
$userName = $userName ?? $_SESSION['user_name'] ?? 'Usuario';
$userRol  = $userRol  ?? $_SESSION['user_rol']  ?? 'STAFF';

$filtroCategoria = $_GET['categoria'] ?? '';
$filtroCaja      = isset($_GET['caja'])   ? (int)$_GET['caja']   : 0;
$filtroCajera    = isset($_GET['cajera']) ? (int)$_GET['cajera'] : 0;
$filtroEstado    = strtoupper($_GET['estado'] ?? '');
$filtroMes       = $_GET['mes'] ?? date('Y-m');
$cajas           = $cajas   ?? [];
$cajeras         = $cajeras ?? [];

$categorias = [
    'COBROS'   => 'Cobros Electrónicos',
    'OTROS'    => 'Otros pagos',
    'PERSONAL' => 'Pago de Personal',
    'LOCAL'    => 'Pago de Local',
    'COMPRAS'  => 'Pago de Compras',
    'DEPOSITO' => 'Depósito KGyR',
];

$estadoLabel = ['PENDIENTE' => 'Pendiente', 'REVISADO' => 'Revisado', 'RECHAZADO' => 'Rechazado'];
$estadoClase = ['PENDIENTE' => 'badge-pendiente', 'REVISADO' => 'badge-contratado', 'RECHAZADO' => 'badge-rechazado'];

$totPorEstado = ['PENDIENTE' => 0, 'REVISADO' => 0, 'RECHAZADO' => 0];
foreach ($items as $i) {
    $totPorEstado[$i['estado_auditoria']] = ($totPorEstado[$i['estado_auditoria']] ?? 0) + 1;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auditoría | Caja SB</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/dashboard.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/caja.css">
    <style>
        .badge-rechazado { background:#fee2e2;color:#991b1b; }
        .aud-obs { font-size:.68rem;color:#94a3b8;margin-top:2px;display:block;max-width:100%;
                   overflow:hidden;text-overflow:ellipsis;white-space:nowrap; }

        /* Overlay modal de aprobar/rechazar */
        .aud-ov { position:fixed;inset:0;background:rgba(15,23,42,.5);z-index:500;
            display:flex;align-items:center;justify-content:center; }
        .aud-ov[hidden] { display:none!important; }
        .aud-modal { background:#fff;border-radius:14px;padding:1.5rem;width:400px;max-width:94vw;
            box-shadow:0 20px 60px rgba(0,0,0,.22); }
        .aud-modal h3 { font-size:.95rem;font-weight:700;margin-bottom:.5rem;color:#1e293b; }
        .aud-modal p  { font-size:.78rem;color:#64748b;margin-bottom:.75rem;line-height:1.5; }
        .aud-fl label { font-size:.75rem;font-weight:600;color:#475569;display:block;margin-bottom:.2rem; }
        .aud-fl input, .aud-fl textarea { width:100%;padding:.48rem .7rem;border:1.5px solid #e2e8f0;border-radius:8px;
            font-size:.85rem;margin-bottom:.6rem;box-sizing:border-box;outline:none;font-family:inherit; }
        .aud-fl textarea { resize:vertical;min-height:60px; }
        .aud-fl input:focus, .aud-fl textarea:focus { border-color:#0097A7; }
        .aud-footer { display:flex;gap:.5rem;justify-content:flex-end;margin-top:.25rem; }
        .aud-err { font-size:.75rem;color:#dc2626;margin-bottom:.5rem;display:none; }
    </style>
</head>
<body style="background:#f1f5f9;">

<header class="caja-header">
    <div class="caja-header__brand">
        <div class="caja-header__logo">SB</div>
        <div>
            <p class="caja-header__company">Grupo KGyR S.A.C</p>
            <p class="caja-header__app"><strong>Auditoría</strong> — Verificación de movimientos</p>
        </div>
    </div>
    <div class="caja-header__right">
        <span class="caja-header__user"><?= htmlspecialchars($userName) ?> (<?= $userRol ?>)</span>
        <a href="<?= $basePath ?>/caja" class="caja-btn-back">← Caja</a>
        <a href="<?= $basePath ?>/admin/dashboard" class="caja-btn-back">Dashboard</a>
    </div>
</header>

<!-- ── Modal: aprobar / rechazar ──────────────────────────── -->
<div id="audOv" class="aud-ov" hidden>
    <div class="aud-modal">
        <h3 id="audModalTitulo">Aprobar</h3>
        <p id="audModalDesc"></p>
        <div class="aud-fl" id="audFlObs" style="display:none;">
            <label>Motivo del rechazo *</label>
            <textarea id="audObs" placeholder="¿Por qué se rechaza este registro?"></textarea>
        </div>
        <div class="aud-fl">
            <label>Tu contraseña para confirmar *</label>
            <input type="password" id="audPassword" placeholder="Tu contraseña de acceso">
        </div>
        <div id="audErr" class="aud-err"></div>
        <div class="aud-footer">
            <button class="caja-btn caja-btn--outline" onclick="audCerrarModal()">Cancelar</button>
            <button class="caja-btn caja-btn--primary" id="audBtnConfirmar" onclick="audConfirmar()">Confirmar</button>
        </div>
    </div>
</div>

<main class="caja-main" style="max-width:1150px;">

    <!-- ── Resumen ────────────────────────────────────────── -->
    <div style="display:flex;gap:1rem;flex-wrap:wrap;">
        <div class="caja-card" style="flex:1;min-width:160px;text-align:center;">
            <p style="font-size:0.68rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.4rem;">Pendientes</p>
            <p style="font-size:1.6rem;font-weight:700;color:#d97706;"><?= $totPorEstado['PENDIENTE'] ?></p>
        </div>
        <div class="caja-card" style="flex:1;min-width:160px;text-align:center;">
            <p style="font-size:0.68rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.4rem;">Revisados</p>
            <p style="font-size:1.6rem;font-weight:700;color:#059669;"><?= $totPorEstado['REVISADO'] ?></p>
        </div>
        <div class="caja-card" style="flex:1;min-width:160px;text-align:center;">
            <p style="font-size:0.68rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.4rem;">Rechazados</p>
            <p style="font-size:1.6rem;font-weight:700;color:#dc2626;"><?= $totPorEstado['RECHAZADO'] ?></p>
        </div>
        <div class="caja-card" style="flex:1;min-width:160px;text-align:center;">
            <p style="font-size:0.68rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.4rem;">Total registros</p>
            <p style="font-size:1.6rem;font-weight:700;color:#1e293b;"><?= count($items) ?></p>
        </div>
    </div>

    <!-- ── Filtros ─────────────────────────────────────────── -->
    <section class="caja-card">
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;margin-bottom:.75rem;">
            <span style="font-size:0.8rem;color:#64748b;font-weight:600;">Estado:</span>
            <?php foreach (['' => 'Todos', 'PENDIENTE' => 'Pendientes', 'REVISADO' => 'Revisados', 'RECHAZADO' => 'Rechazados'] as $val => $lbl): ?>
                <a href="?estado=<?= $val ?>&categoria=<?= urlencode($filtroCategoria) ?>&caja=<?= $filtroCaja ?>&cajera=<?= $filtroCajera ?>&mes=<?= htmlspecialchars($filtroMes) ?>"
                   class="caja-btn <?= $filtroEstado === $val ? 'caja-btn--primary' : 'caja-btn--outline' ?>"
                   style="padding:4px 12px;font-size:0.78rem;">
                    <?= $lbl ?>
                </a>
            <?php endforeach; ?>
        </div>
        <form method="GET" style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;">
            <input type="hidden" name="estado" value="<?= htmlspecialchars($filtroEstado) ?>">
            <select name="categoria" class="caja-input" style="max-width:190px;" onchange="this.form.submit()">
                <option value="">— Todas las categorías —</option>
                <?php foreach ($categorias as $val => $lbl): ?>
                    <option value="<?= $val ?>" <?= $filtroCategoria === $val ? 'selected' : '' ?>><?= $lbl ?></option>
                <?php endforeach; ?>
            </select>
            <select name="caja" class="caja-input" style="max-width:190px;" onchange="this.form.submit()">
                <option value="0">— Todas las cajas —</option>
                <?php foreach ($cajas as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $filtroCaja == $c['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['descripcion']) ?> (<?= htmlspecialchars($c['local_desc']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="cajera" class="caja-input" style="max-width:170px;" onchange="this.form.submit()">
                <option value="0">— Todas las cajeras —</option>
                <?php foreach ($cajeras as $cj): ?>
                    <option value="<?= $cj['id'] ?>" <?= $filtroCajera == $cj['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cj['nombres']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="month" name="mes" class="caja-input" style="max-width:140px;"
                   value="<?= htmlspecialchars($filtroMes) ?>" onchange="this.form.submit()">
            <?php if ($filtroCategoria || $filtroCaja || $filtroCajera): ?>
            <a href="?estado=<?= htmlspecialchars($filtroEstado) ?>&mes=<?= htmlspecialchars($filtroMes) ?>" class="caja-btn caja-btn--outline"
               style="padding:4px 10px;font-size:0.78rem;">✕ Limpiar</a>
            <?php endif; ?>
        </form>
    </section>

    <!-- ── Tabla ──────────────────────────────────────────── -->
    <section class="caja-card" style="padding:1rem;">
        <div id="audMsg" class="caja-alert" hidden></div>
        <table class="caja-table" id="tablaAuditoria" style="width:100%;table-layout:fixed;">
            <colgroup>
                <col style="width:85px;">  <!-- Fecha -->
                <col style="width:150px;"> <!-- Categoría -->
                <col style="width:130px;"> <!-- Caja/Local -->
                <col>                      <!-- Cajera / Referencia -->
                <col style="width:90px;">  <!-- Monto -->
                <col style="width:110px;"> <!-- Estado -->
                <col style="width:150px;"> <!-- Acción -->
            </colgroup>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Categoría</th>
                    <th>Caja / Local</th>
                    <th>Cajera / Referencia</th>
                    <th class="text-right">Monto</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center">Acción</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $it):
                $rowId = $it['categoria'] . '-' . $it['id'];
                $ea    = $it['estado_auditoria'];
            ?>
                <tr id="row-<?= $rowId ?>">
                    <td>
                        <span style="font-size:0.8rem;color:#1e293b;display:block;"><?= date('d/m/Y', strtotime($it['fecha_operacion'])) ?></span>
                    </td>
                    <td>
                        <span style="font-size:0.78rem;font-weight:700;display:block;"><?= htmlspecialchars($categorias[$it['categoria']] ?? $it['categoria']) ?></span>
                    </td>
                    <td>
                        <span style="font-size:0.8rem;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= htmlspecialchars($it['caja_desc']) ?>">
                            <?= htmlspecialchars($it['caja_desc']) ?>
                        </span>
                        <span style="font-size:0.72rem;color:#94a3b8;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:block;">
                            <?= htmlspecialchars($it['local_desc']) ?>
                        </span>
                        <a href="<?= $basePath ?>/caja/reporte/<?= $it['id_sesion'] ?>" target="_blank" style="font-size:0.7rem;color:#0e7490;display:block;">Ver cuadre →</a>
                    </td>
                    <td>
                        <span style="font-size:0.8rem;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            <?= htmlspecialchars($it['cajera_nombre']) ?>
                        </span>
                        <span style="font-size:0.72rem;color:#94a3b8;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:block;">
                            <?= htmlspecialchars($it['referencia'] ?? '—') ?>
                        </span>
                    </td>
                    <td class="text-right">
                        <strong style="font-size:0.88rem;font-variant-numeric:tabular-nums;">S/ <?= number_format($it['monto'], 2) ?></strong>
                    </td>
                    <td class="text-center">
                        <span class="badge <?= $estadoClase[$ea] ?>" style="margin-top:2px;">
                            <?= $estadoLabel[$ea] ?>
                        </span>
                        <span style="font-size:0.66rem;color:#94a3b8;display:block;margin-top:2px;"><?= htmlspecialchars($it['estado_original']) ?></span>
                        <?php if ($ea === 'RECHAZADO' && !empty($it['observacion_revision'])): ?>
                        <span class="aud-obs" title="<?= htmlspecialchars($it['observacion_revision']) ?>">💬 <?= htmlspecialchars($it['observacion_revision']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <?php if ($ea === 'PENDIENTE'): ?>
                            <button class="btn-edit" style="font-size:0.68rem;padding:3px 6px;background:#059669;display:block;width:100%;margin-bottom:3px;"
                                    onclick="audAbrirModal('<?= $it['categoria'] ?>', <?= $it['id'] ?>, 'APROBAR')">✓ Aprobar</button>
                            <button class="btn-danger" style="font-size:0.68rem;padding:3px 6px;display:block;width:100%;"
                                    onclick="audAbrirModal('<?= $it['categoria'] ?>', <?= $it['id'] ?>, 'RECHAZAR')">✗ Rechazar</button>
                        <?php else: ?>
                            <button class="btn-edit" style="font-size:0.68rem;padding:3px 6px;background:#64748b;display:block;width:100%;"
                                    onclick="audVolverPendiente('<?= $it['categoria'] ?>', <?= $it['id'] ?>)">↺ Volver a pendiente</button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($items)): ?>
                <tr><td colspan="7" class="caja-table__empty">No hay movimientos registrados con estos filtros.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </section>

</main>

<script>
const BASE = '<?= $basePath ?>';
let audCategoria = '', audId = 0, audAccion = '';

function mostrarMsg(texto, tipo) {
    const msg = document.getElementById('audMsg');
    msg.textContent = texto;
    msg.className = 'caja-alert ' + (tipo === 'ok' ? 'caja-alert--ok' : 'caja-alert--error');
    msg.hidden = false;
}

function audAbrirModal(categoria, id, accion) {
    audCategoria = categoria; audId = id; audAccion = accion;
    const esRechazo = accion === 'RECHAZAR';
    document.getElementById('audModalTitulo').textContent = esRechazo ? 'Rechazar registro' : 'Aprobar registro';
    document.getElementById('audModalDesc').textContent = esRechazo
        ? 'Escribe el motivo del rechazo y confirma con tu contraseña.'
        : 'Confirma con tu contraseña para aprobar este registro.';
    document.getElementById('audFlObs').style.display = esRechazo ? 'block' : 'none';
    document.getElementById('audObs').value = '';
    document.getElementById('audPassword').value = '';
    document.getElementById('audErr').style.display = 'none';
    document.getElementById('audBtnConfirmar').textContent = 'Confirmar';
    document.getElementById('audBtnConfirmar').disabled = false;
    document.getElementById('audOv').hidden = false;
    setTimeout(() => document.getElementById(esRechazo ? 'audObs' : 'audPassword').focus(), 80);
}

function audCerrarModal() {
    document.getElementById('audOv').hidden = true;
}

function audShowErr(msg) {
    const el = document.getElementById('audErr');
    el.textContent = msg; el.style.display = 'block';
}

async function audEnviar(categoria, id, accion, observacion, password) {
    try {
        const r = await fetch(`${BASE}/caja/api/auditoria/${categoria}/${id}/revisar`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ accion, observacion, password }),
        });
        return await r.json();
    } catch {
        return { success: false, message: 'Error de conexión.' };
    }
}

async function audConfirmar() {
    const password = document.getElementById('audPassword').value.trim();
    const observacion = document.getElementById('audObs').value.trim();
    if (!password) { audShowErr('Tu contraseña es requerida.'); return; }
    if (audAccion === 'RECHAZAR' && !observacion) { audShowErr('Escribe el motivo del rechazo.'); return; }

    const btn = document.getElementById('audBtnConfirmar');
    btn.disabled = true; btn.textContent = 'Enviando…';

    const res = await audEnviar(audCategoria, audId, audAccion, observacion, password);
    if (res.success) {
        audCerrarModal();
        location.reload();
    } else {
        audShowErr(res.message || 'Error al actualizar.');
        btn.disabled = false; btn.textContent = 'Confirmar';
    }
}

async function audVolverPendiente(categoria, id) {
    if (!confirm('¿Volver este registro a pendiente?')) return;
    const res = await audEnviar(categoria, id, 'PENDIENTE', null, null);
    if (res.success) {
        location.reload();
    } else {
        mostrarMsg(res.message || 'Error al actualizar.', 'error');
    }
}
</script>
</body>
</html>
