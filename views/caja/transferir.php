<?php
$basePath       = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
$userName       = $_SESSION['user_name'] ?? 'Usuario';
$isAdmin        = ($_SESSION['user_rol'] ?? '') === 'ADMIN';
$pendientes     = array_filter($transferencias, fn($t) => $t['estado'] === 'PENDIENTE');
$historial      = array_filter($transferencias, fn($t) => $t['estado'] !== 'PENDIENTE');

// Estado visual de aplicación: una transferencia CONFIRMADA no afecta ningún cuadre
// hasta que se aplica en el cierre de la caja origen y de la caja destino.
$estadoAplicacion = function (array $t): array {
    if ($t['estado'] !== 'CONFIRMADA') {
        return ['label' => $t['estado'], 'tag' => $t['estado'] === 'ANULADA' ? 'anul' : 'pend'];
    }
    $aplicadaOrigen  = $t['sesion_aplicada_origen_id']  !== null;
    $aplicadaDestino = $t['sesion_aplicada_destino_id'] !== null;
    if ($aplicadaOrigen && $aplicadaDestino) return ['label' => 'APLICADA', 'tag' => 'conf'];
    if ($aplicadaOrigen || $aplicadaDestino) return ['label' => 'APLICADA PARCIAL', 'tag' => 'pend'];
    return ['label' => 'POR APLICAR', 'tag' => 'pend'];
};
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transferencia de Saldo | Solo Boticas</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/caja.css">
    <link rel="icon" type="image/x-icon" href="<?= $basePath ?>/assets/img/logo.ico">
    <style>
        .tr-saldo-table { width:100%;border-collapse:collapse;font-size:.82rem; }
        .tr-saldo-table th { background:#f8fafc;padding:.45rem .8rem;font-size:.63rem;font-weight:700;
            text-transform:uppercase;letter-spacing:.06em;color:#64748b;border-bottom:2px solid #e2e8f0;white-space:nowrap; }
        .tr-saldo-table td { padding:.55rem .8rem;border-bottom:1px solid #f1f5f9;vertical-align:middle; }
        .tr-saldo-table tr:last-child td { border-bottom:none; }
        .tr-tag { display:inline-block;font-size:.72rem;font-weight:700;padding:2px 9px;border-radius:12px;white-space:nowrap; }
        .tr-tag--pend { background:#fef9c3;color:#92400e; }
        .tr-tag--conf { background:#d1fae5;color:#065f46; }
        .tr-tag--anul { background:#f1f5f9;color:#64748b; }
        .tr-arrow { color:#94a3b8;font-weight:700;margin:0 .3rem; }
        .tr-monto { font-weight:700;font-size:.88rem;font-variant-numeric:tabular-nums; }
        .tr-monto--out { color:#dc2626; }
        .tr-monto--in  { color:#059669; }

        /* Overlay modal */
        .tr-ov { position:fixed;inset:0;background:rgba(15,23,42,.5);z-index:500;
            display:flex;align-items:center;justify-content:center; }
        .tr-ov[hidden] { display:none!important; }
        .tr-modal { background:#fff;border-radius:14px;padding:1.5rem;width:380px;max-width:94vw;
            box-shadow:0 20px 60px rgba(0,0,0,.22); }
        .tr-modal h3 { font-size:.95rem;font-weight:700;margin-bottom:.5rem;color:#1e293b; }
        .tr-modal p  { font-size:.78rem;color:#64748b;margin-bottom:.75rem;line-height:1.5; }
        .tr-fl label { font-size:.75rem;font-weight:600;color:#475569;display:block;margin-bottom:.2rem; }
        .tr-fl input  { width:100%;padding:.48rem .7rem;border:1.5px solid #e2e8f0;border-radius:8px;
            font-size:.85rem;margin-bottom:.6rem;box-sizing:border-box;outline:none; }
        .tr-fl input:focus { border-color:#0097A7; }
        .tr-footer { display:flex;gap:.5rem;justify-content:flex-end;margin-top:.25rem; }
        .tr-err { font-size:.75rem;color:#dc2626;margin-bottom:.5rem;display:none; }
    </style>
</head>
<body>

<!-- Modal confirmar -->
<div id="modalConfirmar" class="tr-ov" hidden>
    <div class="tr-modal">
        <h3>Confirmar transferencia</h3>
        <p id="mdConfDesc"></p>
        <div id="mdConfErr" class="tr-err"></div>
        <input type="hidden" id="mdConfId">
        <div class="tr-fl">
            <label>N° Comprobante / Referencia *</label>
            <input type="text" id="mdConfComp" placeholder="Ej: OP-0001234">
            <label>Tu contraseña *</label>
            <input type="password" id="mdConfPwd" placeholder="Contraseña de acceso">
        </div>
        <div class="tr-footer">
            <button onclick="cerrarConfirmar()"
                style="background:#f1f5f9;border:none;border-radius:7px;padding:.5rem 1rem;font-size:.82rem;cursor:pointer;color:#475569;">
                Cancelar
            </button>
            <button onclick="enviarConfirmar()"
                style="background:#059669;border:none;border-radius:7px;padding:.5rem 1.25rem;font-size:.82rem;font-weight:700;color:#fff;cursor:pointer;">
                Confirmar
            </button>
        </div>
    </div>
</div>

<!-- Modal anular -->
<div id="modalAnular" class="tr-ov" hidden>
    <div class="tr-modal">
        <h3>Anular transferencia</h3>
        <p id="mdAnulDesc"></p>
        <div id="mdAnulErr" class="tr-err"></div>
        <input type="hidden" id="mdAnulId">
        <div class="tr-fl">
            <label>Tu contraseña para confirmar *</label>
            <input type="password" id="mdAnulPwd" placeholder="Contraseña de acceso">
        </div>
        <div class="tr-footer">
            <button onclick="cerrarAnular()"
                style="background:#f1f5f9;border:none;border-radius:7px;padding:.5rem 1rem;font-size:.82rem;cursor:pointer;color:#475569;">
                Cancelar
            </button>
            <button onclick="enviarAnular()"
                style="background:#dc2626;border:none;border-radius:7px;padding:.5rem 1.25rem;font-size:.82rem;font-weight:700;color:#fff;cursor:pointer;">
                Anular
            </button>
        </div>
    </div>
</div>

<header class="caja-header">
    <div class="caja-header__brand">
        <div class="caja-header__logo">SB</div>
        <div>
            <p class="caja-header__company">Grupo KGyR S.A.C</p>
            <p class="caja-header__app">Módulo de <strong>Caja</strong> — Transferencias</p>
        </div>
    </div>
    <div class="caja-header__right">
        <span class="caja-header__user"><?= htmlspecialchars($userName) ?></span>
        <a href="<?= $basePath ?>/caja" class="caja-btn-back">← Caja</a>
    </div>
</header>

<main class="caja-main">

    <!-- Saldo base por caja -->
    <section class="caja-card">
        <h2 class="caja-card__title">Saldo base actual por caja</h2>
        <p class="caja-card__desc" style="margin-bottom:.75rem;">Saldo de apertura proyectado para la próxima sesión de cada caja.</p>
        <div class="caja-table-wrap">
            <table class="tr-saldo-table">
                <thead>
                    <tr><th>Caja</th><th>Local</th><th>Saldo base</th><th>Última sesión</th><th>Estado</th></tr>
                </thead>
                <tbody>
                <?php foreach ($saldos as $s): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($s['caja_desc']) ?></strong></td>
                    <td style="font-size:.78rem;color:#64748b;"><?= htmlspecialchars($s['local_desc']) ?></td>
                    <td>
                        <?php if ($s['id_sesion']): ?>
                            <span style="font-weight:700;color:#1e293b;font-variant-numeric:tabular-nums;">
                                S/ <?= number_format((float)$s['saldo_proximo_dia'], 2) ?>
                            </span>
                        <?php else: ?>
                            <span style="color:#94a3b8;font-size:.75rem;">Sin sesiones</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:.75rem;color:#64748b;">
                        <?= $s['fecha_operacion'] ? date('d/m/Y', strtotime($s['fecha_operacion'])) : '—' ?>
                    </td>
                    <td>
                        <?php if ($s['sesion_estado']): ?>
                            <span class="caja-estado <?= $s['sesion_estado']==='ABIERTA' ? 'estado-abierta' : 'estado-cerrada' ?>"
                                  style="font-size:.68rem;">
                                <?= $s['sesion_estado'] ?>
                            </span>
                        <?php else: ?>
                            <span style="color:#94a3b8;">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Nueva solicitud -->
    <section class="caja-card">
        <h2 class="caja-card__title">Nueva solicitud de transferencia</h2>
        <p class="caja-card__desc">El saldo de apertura de la caja origen se reducirá y el de la caja destino aumentará al confirmar.</p>
        <form id="frmSolicitar" style="display:flex;flex-wrap:wrap;gap:.65rem;align-items:flex-end;margin-top:.85rem;">
            <div>
                <label style="font-size:.72rem;font-weight:700;color:#64748b;display:block;margin-bottom:.2rem;">Caja origen</label>
                <select id="solOrigen" class="caja-input" style="min-width:160px;">
                    <option value="">— Seleccionar —</option>
                    <?php foreach ($saldos as $s): ?>
                        <option value="<?= $s['id_caja'] ?>">
                            <?= htmlspecialchars($s['caja_desc']) ?> · <?= htmlspecialchars($s['local_desc']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="align-self:flex-end;padding-bottom:.45rem;color:#94a3b8;font-weight:700;">→</div>
            <div>
                <label style="font-size:.72rem;font-weight:700;color:#64748b;display:block;margin-bottom:.2rem;">Caja destino</label>
                <select id="solDestino" class="caja-input" style="min-width:160px;">
                    <option value="">— Seleccionar —</option>
                    <?php foreach ($saldos as $s): ?>
                        <option value="<?= $s['id_caja'] ?>">
                            <?= htmlspecialchars($s['caja_desc']) ?> · <?= htmlspecialchars($s['local_desc']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="font-size:.72rem;font-weight:700;color:#64748b;display:block;margin-bottom:.2rem;">Monto S/</label>
                <input type="number" id="solMonto" class="caja-input" placeholder="0.00" min="0.01" step="0.01" style="width:110px;">
            </div>
            <div style="flex:1;min-width:140px;">
                <label style="font-size:.72rem;font-weight:700;color:#64748b;display:block;margin-bottom:.2rem;">Notas (opcional)</label>
                <input type="text" id="solNotas" class="caja-input" placeholder="Motivo de la transferencia">
            </div>
            <div id="solErr" style="width:100%;font-size:.75rem;color:#dc2626;display:none;"></div>
            <button type="submit" class="caja-btn caja-btn--primary" style="white-space:nowrap;">
                Solicitar transferencia
            </button>
        </form>
    </section>

    <!-- Pendientes -->
    <section class="caja-card">
        <h2 class="caja-card__title">
            Pendientes de confirmación
            <?php if (count($pendientes)): ?>
                <span class="caja-badge-count"><?= count($pendientes) ?></span>
            <?php endif; ?>
        </h2>
        <?php if (empty($pendientes)): ?>
            <p style="font-size:.82rem;color:#94a3b8;">No hay transferencias pendientes.</p>
        <?php else: ?>
        <div class="caja-table-wrap">
            <table class="tr-saldo-table">
                <thead>
                    <tr><th>Transferencia</th><th>Monto</th><th>Solicitado por</th><th>Fecha</th><th></th></tr>
                </thead>
                <tbody>
                <?php foreach ($pendientes as $t): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($t['caja_origen_desc']) ?></strong>
                        <span class="tr-arrow">→</span>
                        <strong><?= htmlspecialchars($t['caja_destino_desc']) ?></strong>
                        <?php if ($t['notas']): ?>
                            <span style="display:block;font-size:.72rem;color:#64748b;"><?= htmlspecialchars($t['notas']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td><span class="tr-monto tr-monto--out">S/ <?= number_format($t['monto'], 2) ?></span></td>
                    <td style="font-size:.75rem;"><?= htmlspecialchars($t['solicitante_nombre']) ?></td>
                    <td style="font-size:.72rem;color:#64748b;white-space:nowrap;"><?= date('d/m H:i', strtotime($t['created_at'])) ?></td>
                    <td style="white-space:nowrap;display:flex;gap:.3rem;">
                        <button class="caja-btn caja-btn--primary" style="padding:4px 10px;font-size:.72rem;"
                            onclick="abrirConfirmar(<?= $t['id'] ?>, '<?= addslashes($t['caja_origen_desc']) ?>', '<?= addslashes($t['caja_destino_desc']) ?>', <?= $t['monto'] ?>)">
                            Confirmar
                        </button>
                        <?php if ($isAdmin): ?>
                        <button class="caja-btn caja-btn--outline" style="padding:4px 10px;font-size:.72rem;border-color:#fca5a5;color:#dc2626;"
                            onclick="abrirAnular(<?= $t['id'] ?>, '<?= addslashes($t['caja_origen_desc']) ?>', '<?= addslashes($t['caja_destino_desc']) ?>', <?= $t['monto'] ?>, 'PENDIENTE')">
                            Anular
                        </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </section>

    <!-- Historial reciente -->
    <?php if (!empty($historial)): ?>
    <section class="caja-card">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.5rem;">
            <h2 class="caja-card__title" style="margin:0;">Historial reciente</h2>
            <?php if ($isAdmin): ?>
            <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
                <button type="button" id="btnVerificarTransf" class="caja-btn caja-btn--outline"
                        style="border-color:#7c3aed;color:#7c3aed;font-size:.75rem;padding:5px 10px;border-radius:6px;background:#fff;cursor:pointer;">
                    🔍 Verificar transferencias
                </button>
                <button type="button" id="btnRepararTransf" class="caja-btn caja-btn--outline"
                        style="border-color:#059669;color:#059669;font-size:.75rem;padding:5px 10px;border-radius:6px;background:#fff;cursor:pointer;">
                    🛠 Reparar huérfanas
                </button>
            </div>
            <?php endif; ?>
        </div>
        <p style="font-size:.78rem;color:#64748b;max-width:65ch;margin:.4rem 0 0;">
            Si una transferencia ya se aplicó a un cuadre que luego fue eliminado, el vínculo queda roto
            y el dinero deja de sumarse en cualquier cuadre. "Verificar" detecta esos casos; "Reparar"
            los vuelve a marcar pendientes para que se apliquen solos en el próximo cierre real de esa caja.
        </p>
        <div id="verifTransfResultado" style="display:none;margin:.75rem 0;padding:.65rem .9rem;border-radius:8px;font-size:.82rem;"></div>
        <div class="caja-table-wrap" style="margin-top:.75rem;">
            <table class="tr-saldo-table">
                <thead>
                    <tr><th>Transferencia</th><th>Monto</th><th>Comprobante</th><th>Estado</th>
                        <th>Cuadre origen</th><th>Cuadre destino</th><th>Procesado por</th><th>Fecha</th><th></th></tr>
                </thead>
                <tbody>
                <?php foreach ($historial as $t): ?>
                <?php
                    $cuadreCelda = function (string $prefix) use ($t, $basePath) {
                        $sid    = $t["sesion_aplicada_{$prefix}_id"];
                        $existe = $t["{$prefix}_sesion_existe"];
                        $fecha  = $t["{$prefix}_sesion_fecha"];
                        if ($sid === null) {
                            return '<span style="color:#94a3b8;font-size:.75rem;">—</span>';
                        }
                        if ($existe === null) {
                            return '<span style="background:#fee2e2;color:#991b1b;font-size:.7rem;font-weight:700;padding:2px 7px;border-radius:5px;" title="Sesion #' . (int)$sid . ' ya no existe">⚠ cuadre eliminado</span>';
                        }
                        return '<a href="' . $basePath . '/caja/reporte/' . (int)$sid . '" style="font-size:.75rem;">#' . (int)$sid . ' · ' . date('d/m/Y', strtotime($fecha)) . '</a>';
                    };
                ?>
                <tr>
                    <td>
                        <span style="font-size:.78rem;">
                            <?= htmlspecialchars($t['caja_origen_desc']) ?>
                            <span class="tr-arrow">→</span>
                            <?= htmlspecialchars($t['caja_destino_desc']) ?>
                        </span>
                    </td>
                    <td><span class="tr-monto" style="color:#1e293b;">S/ <?= number_format($t['monto'], 2) ?></span></td>
                    <td style="font-size:.75rem;color:#475569;"><?= htmlspecialchars($t['numero_comprobante'] ?? '—') ?></td>
                    <?php $estApl = $estadoAplicacion($t); ?>
                    <td>
                        <span class="tr-tag tr-tag--<?= $estApl['tag'] ?>" <?= $estApl['label']==='POR APLICAR' ? 'title="Aún no afecta ningún cuadre. Se aplicará en el siguiente cuadre de cada caja involucrada, sea hoy o mañana."' : '' ?>>
                            <?= $estApl['label'] ?>
                        </span>
                    </td>
                    <td><?= $cuadreCelda('origen') ?></td>
                    <td><?= $cuadreCelda('destino') ?></td>
                    <td style="font-size:.75rem;">
                        <?= htmlspecialchars($t['estado']==='CONFIRMADA' ? ($t['confirmador_nombre'] ?? '—') : ($t['anulador_nombre'] ?? '—')) ?>
                    </td>
                    <td style="font-size:.72rem;color:#64748b;white-space:nowrap;">
                        <?= date('d/m/Y H:i', strtotime($t['confirmed_at'] ?? $t['anulada_at'] ?? $t['created_at'])) ?>
                    </td>
                    <td>
                        <?php if ($isAdmin && $t['estado'] === 'CONFIRMADA' && $t['sesion_aplicada_origen_id'] === null && $t['sesion_aplicada_destino_id'] === null): ?>
                        <button class="caja-btn caja-btn--outline" style="padding:3px 8px;font-size:.68rem;border-color:#fca5a5;color:#dc2626;"
                            onclick="abrirAnular(<?= $t['id'] ?>, '<?= addslashes($t['caja_origen_desc']) ?>', '<?= addslashes($t['caja_destino_desc']) ?>', <?= $t['monto'] ?>, 'CONFIRMADA')">
                            Anular
                        </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
    <?php endif; ?>

</main>

<script>
const BASE = '<?= $basePath ?>';

// ── Solicitar ──
document.getElementById('frmSolicitar').onsubmit = async (e) => {
    e.preventDefault();
    const origen  = document.getElementById('solOrigen').value;
    const destino = document.getElementById('solDestino').value;
    const monto   = parseFloat(document.getElementById('solMonto').value);
    const notas   = document.getElementById('solNotas').value.trim();
    const err     = document.getElementById('solErr');
    err.style.display = 'none';

    if (!origen || !destino) { err.textContent = 'Selecciona origen y destino.'; err.style.display='block'; return; }
    if (origen === destino)  { err.textContent = 'Origen y destino deben ser distintos.'; err.style.display='block'; return; }
    if (!monto || monto <= 0){ err.textContent = 'El monto debe ser mayor a 0.'; err.style.display='block'; return; }

    const r = await fetch(`${BASE}/caja/api/transferir/solicitar`, {
        method: 'POST', headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ caja_origen_id: parseInt(origen), caja_destino_id: parseInt(destino), monto, notas: notas || null }),
    });
    const res = await r.json();
    if (res.success) location.reload();
    else { err.textContent = res.message || 'Error.'; err.style.display='block'; }
};

// ── Confirmar ──
function abrirConfirmar(id, ori, des, monto) {
    document.getElementById('mdConfId').value = id;
    document.getElementById('mdConfDesc').textContent =
        `${ori} → ${des} · S/ ${monto.toFixed(2)}. El saldo de apertura se ajustará en ambos locales.`;
    document.getElementById('mdConfComp').value = '';
    document.getElementById('mdConfPwd').value  = '';
    document.getElementById('mdConfErr').style.display = 'none';
    document.getElementById('modalConfirmar').removeAttribute('hidden');
    setTimeout(() => document.getElementById('mdConfComp').focus(), 50);
}
function cerrarConfirmar() { document.getElementById('modalConfirmar').setAttribute('hidden',''); }
async function enviarConfirmar() {
    const id   = document.getElementById('mdConfId').value;
    const comp = document.getElementById('mdConfComp').value.trim();
    const pwd  = document.getElementById('mdConfPwd').value.trim();
    const err  = document.getElementById('mdConfErr');
    if (!comp || !pwd) { err.textContent = 'Comprobante y contraseña son requeridos.'; err.style.display='block'; return; }
    const r = await fetch(`${BASE}/caja/api/transferir/${id}/confirmar`, {
        method: 'POST', headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ numero_comprobante: comp, password: pwd }),
    });
    const res = await r.json();
    if (res.success) { cerrarConfirmar(); location.reload(); }
    else { err.textContent = res.message || 'Error.'; err.style.display='block'; }
}

// ── Anular ──
function abrirAnular(id, ori, des, monto, estado) {
    document.getElementById('mdAnulId').value = id;
    const aviso = estado === 'CONFIRMADA'
        ? ' El saldo previamente ajustado será revertido.'
        : '';
    document.getElementById('mdAnulDesc').textContent =
        `Se anulará la transferencia de ${ori} → ${des} por S/ ${monto.toFixed(2)}.${aviso}`;
    document.getElementById('mdAnulPwd').value = '';
    document.getElementById('mdAnulErr').style.display = 'none';
    document.getElementById('modalAnular').removeAttribute('hidden');
    setTimeout(() => document.getElementById('mdAnulPwd').focus(), 50);
}
function cerrarAnular() { document.getElementById('modalAnular').setAttribute('hidden',''); }
async function enviarAnular() {
    const id  = document.getElementById('mdAnulId').value;
    const pwd = document.getElementById('mdAnulPwd').value.trim();
    const err = document.getElementById('mdAnulErr');
    if (!pwd) { err.textContent = 'La contraseña es requerida.'; err.style.display='block'; return; }
    const r = await fetch(`${BASE}/caja/api/transferir/${id}/anular`, {
        method: 'POST', headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ password: pwd }),
    });
    const res = await r.json();
    if (res.success) { cerrarAnular(); location.reload(); }
    else { err.textContent = res.message || 'Error.'; err.style.display='block'; }
}

// ── Verificar / reparar transferencias huérfanas ──
async function auditarTransferencias(fix) {
    const btn   = fix ? document.getElementById('btnRepararTransf') : document.getElementById('btnVerificarTransf');
    const panel = document.getElementById('verifTransfResultado');
    const original = btn.textContent;
    btn.disabled = true;
    btn.textContent = fix ? 'Reparando...' : 'Verificando...';
    try {
        const r = await fetch(`${BASE}/caja/api/transferir/auditar`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ fix: !!fix }),
        });
        const res = await r.json();
        if (!res.success) throw new Error(res.message || 'Error al verificar');

        const riesgo    = res.data.riesgo || [];
        const sinEfecto = res.data.sin_efecto || [];
        panel.style.display = 'block';

        const fmt = (arr) => arr.map(h =>
            `#${h.id} ${h.caja_origen_desc} → ${h.caja_destino_desc} · S/ ${parseFloat(h.monto).toFixed(2)}`
        ).join('<br>');

        if (riesgo.length === 0 && sinEfecto.length === 0) {
            panel.style.background = '#d1fae5';
            panel.style.color      = '#065f46';
            panel.innerHTML = '✓ No hay transferencias huérfanas. Todo está correctamente aplicado.';
        } else if (fix) {
            panel.style.background = '#d1fae5';
            panel.style.color      = '#065f46';
            let msg = riesgo.length
                ? `✓ ${riesgo.length} transferencia(s) reparada(s). Quedaron pendientes y se aplicarán en el próximo cierre de cada caja. Recargando...`
                : '✓ No había transferencias de riesgo por reparar.';
            if (sinEfecto.length) {
                msg += `<br><br>ℹ ${sinEfecto.length} transferencia(s) con ambos lados eliminados (sin efecto en los libros actuales, no se tocaron):<br>${fmt(sinEfecto)}`;
            }
            panel.innerHTML = msg;
            if (riesgo.length) setTimeout(() => location.reload(), 1200);
        } else {
            panel.style.background = riesgo.length ? '#fee2e2' : '#fef9c3';
            panel.style.color      = riesgo.length ? '#991b1b' : '#92400e';
            let msg = '';
            if (riesgo.length) {
                msg += `⚠ ${riesgo.length} transferencia(s) de RIESGO real (un lado existe, el otro se eliminó — el dinero quedó descuadrado):<br>${fmt(riesgo)}<br><br>Usa "Reparar huérfanas" para corregirlas.`;
            }
            if (sinEfecto.length) {
                msg += (riesgo.length ? '<br><br>' : '') +
                    `ℹ ${sinEfecto.length} transferencia(s) con AMBOS lados eliminados (sin efecto en los libros actuales, no requieren acción):<br>${fmt(sinEfecto)}`;
            }
            panel.innerHTML = msg;
        }
    } catch (e) {
        panel.style.display    = 'block';
        panel.style.background = '#fee2e2';
        panel.style.color      = '#991b1b';
        panel.innerHTML        = '✗ ' + e.message;
    } finally {
        btn.disabled    = false;
        btn.textContent = original;
    }
}
document.getElementById('btnVerificarTransf')?.addEventListener('click', () => auditarTransferencias(false));
document.getElementById('btnRepararTransf')?.addEventListener('click', () => {
    if (confirm('Esto liberará el lado roto SOLO de las transferencias de riesgo real (un lado existe, el otro no), para que se apliquen en el próximo cierre real de esa caja. Las que tienen ambos lados eliminados no se tocan. ¿Continuar?')) {
        auditarTransferencias(true);
    }
});
</script>
</body>
</html>
