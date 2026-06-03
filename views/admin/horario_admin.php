<?php
if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] !== 'ADMIN') exit('Acceso denegado');

$fecha    = $horarioFecha    ?? date('Y-m-d');
$slots    = $horarioSlots    ?? [];
$historial = $horarioHistorial ?? [];

$turnosLabel = [1 => '☀️ Mañana', 2 => '🌙 Tarde'];
$hoyStr      = date('Y-m-d');
$fechaDt     = new DateTime($fecha, new DateTimeZone('America/Lima'));
?>

<style>
.ha-wrap   { padding: 1rem 1.25rem; display: flex; flex-direction: column; gap: 1.25rem; }
.ha-title  { font-size: .82rem; font-weight: 800; text-transform: uppercase; letter-spacing: .07em; color: #0097A7; margin-bottom: .6rem; }
.ha-form   { display: flex; align-items: center; gap: .6rem; flex-wrap: wrap; }
.ha-form input[type=date] {
    padding: .4rem .7rem; border: 1.5px solid #e2e8f0; border-radius: 8px;
    font-size: .82rem; outline: none; cursor: pointer; color: #1e293b;
}
.ha-form input[type=date]:focus { border-color: #0097A7; }
.ha-form button {
    padding: .4rem 1rem; border-radius: 8px; font-size: .78rem; font-weight: 700;
    background: #0097A7; color: #fff; border: none; cursor: pointer;
}
.ha-form button:hover { background: #007b8a; }

/* Tabla de slots */
.ha-table  { width: 100%; border-collapse: collapse; font-size: .78rem; }
.ha-table th {
    text-align: left; padding: 6px 8px;
    font-size: .68rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .06em; color: #94a3b8;
    border-bottom: 2px solid #e2e8f0;
}
.ha-table td { padding: 8px 8px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.ha-table tr:last-child td { border-bottom: none; }
.ha-table tr:hover td { background: #f8fafc; }

.ha-badge-local { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: .68rem; font-weight: 700; }
.ha-libre  { color: #94a3b8; font-style: italic; }
.ha-nombre { font-weight: 600; color: #1e293b; }

.ha-btn-quitar {
    padding: 3px 10px; border-radius: 6px; font-size: .72rem; font-weight: 700;
    background: transparent; border: 1.5px solid #fca5a5; color: #dc2626;
    cursor: pointer; white-space: nowrap; transition: background .12s;
}
.ha-btn-quitar:hover { background: #fee2e2; }

/* Modal */
.ha-modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,.5); z-index: 9000; display: flex; align-items: center; justify-content: center; }
.ha-modal-overlay[hidden] { display: none !important; }
.ha-modal {
    background: #fff; border-radius: 14px; padding: 1.5rem;
    width: 340px; max-width: 90vw;
    box-shadow: 0 20px 60px rgba(0,0,0,.22);
    display: flex; flex-direction: column; gap: .85rem;
}
.ha-modal h3  { font-size: .95rem; font-weight: 700; color: #1e293b; margin: 0; }
.ha-modal p   { font-size: .8rem; color: #64748b; line-height: 1.5; margin: 0; }
.ha-modal input {
    width: 100%; padding: .5rem .75rem; border: 1.5px solid #e2e8f0;
    border-radius: 8px; font-size: .85rem; box-sizing: border-box; outline: none;
}
.ha-modal input:focus { border-color: #dc2626; }
.ha-modal__err { font-size: .75rem; color: #dc2626; display: none; }
.ha-modal__footer { display: flex; gap: .5rem; justify-content: flex-end; }
.ha-modal__footer button {
    padding: .45rem 1rem; border-radius: 7px; font-size: .8rem; font-weight: 700;
    border: none; cursor: pointer;
}

/* Historial */
.ha-hist-table { width: 100%; border-collapse: collapse; font-size: .75rem; }
.ha-hist-table th { text-align:left; padding: 5px 7px; font-size:.66rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#94a3b8; border-bottom:2px solid #e2e8f0; }
.ha-hist-table td { padding: 6px 7px; border-bottom: 1px solid #f1f5f9; }
.ha-hist-table tr:last-child td { border-bottom: none; }
.ha-badge-tipo { display: inline-block; padding: 1px 7px; border-radius: 20px; font-size: .65rem; font-weight: 700; }
.ha-badge-COBERTURA { background: #fef3c7; color: #92400e; }
.ha-badge-CAMBIO    { background: #e0f7fa; color: #0097A7; }
.ha-revertida { color: #dc2626; font-size: .66rem; font-weight: 700; }

.ha-btn-revertir {
    padding: 2px 8px; border-radius: 5px; font-size: .69rem; font-weight: 700;
    background: transparent; border: 1px solid #fca5a5; color: #dc2626;
    cursor: pointer; white-space: nowrap;
}
.ha-btn-revertir:hover { background: #fee2e2; }
</style>

<div class="ha-wrap">

    <!-- Título -->
    <div>
        <div class="ha-title">🗓 Gestión de Turnos</div>
        <p style="font-size:.75rem;color:#64748b;margin:0;">
            Retira personal o vacía posiciones en cualquier fecha. Sin restricciones de pasado.
        </p>
    </div>

    <!-- Selector de fecha -->
    <form method="GET" class="ha-form">
        <input type="hidden" name="page" value="horario">
        <label style="font-size:.78rem;font-weight:600;color:#475569;">Fecha:</label>
        <input type="date" name="fecha" value="<?= htmlspecialchars($fecha) ?>">
        <button type="submit">Buscar</button>
        <?php if ($fecha !== $hoyStr): ?>
        <a href="?page=horario" style="font-size:.72rem;color:#94a3b8;text-decoration:none;margin-left:.25rem;">Hoy</a>
        <?php endif; ?>
    </form>

    <!-- Tabla de slots -->
    <div>
        <div class="ha-title" style="margin-bottom:.5rem;">
            Turnos del <?= $fechaDt->format('d/m/Y') ?>
            <?php if ($fecha < $hoyStr): ?>
            <span style="background:#fef3c7;color:#92400e;font-size:.65rem;padding:2px 7px;border-radius:10px;font-weight:700;margin-left:.4rem;">Pasado</span>
            <?php elseif ($fecha === $hoyStr): ?>
            <span style="background:#d1fae5;color:#065f46;font-size:.65rem;padding:2px 7px;border-radius:10px;font-weight:700;margin-left:.4rem;">Hoy</span>
            <?php else: ?>
            <span style="background:#e0f7fa;color:#0097A7;font-size:.65rem;padding:2px 7px;border-radius:10px;font-weight:700;margin-left:.4rem;">Futuro</span>
            <?php endif; ?>
        </div>

        <?php if (empty($slots)): ?>
        <p style="font-size:.78rem;color:#94a3b8;padding:.75rem 0;">Sin horario registrado para esta fecha.</p>
        <?php else: ?>
        <div style="overflow-x:auto;">
        <table class="ha-table">
            <thead>
                <tr>
                    <th>Local</th>
                    <th>Turno</th>
                    <th>Posición</th>
                    <th>Asignado a</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php
            $colores = [2 => '#0097A7', 3 => '#5b21b6', 4 => '#d97706'];
            foreach ($slots as $s):
                $esLibre = !$s['postulante_id'];
                $color   = $colores[$s['local_id']] ?? '#64748b';
            ?>
            <tr>
                <td>
                    <span class="ha-badge-local" style="background:<?= $color ?>22;color:<?= $color ?>;">
                        <?= htmlspecialchars($s['local_desc']) ?>
                    </span>
                </td>
                <td style="color:#64748b;"><?= $turnosLabel[$s['turno_id']] ?? $s['turno_id'] ?></td>
                <td style="color:#475569;">
                    <?= htmlspecialchars($s['rol_puesto']) ?>
                    <?= $s['slot_num'] > 1 ? ' #'.$s['slot_num'] : '' ?>
                </td>
                <td>
                    <?php if ($esLibre): ?>
                        <span class="ha-libre">— Libre —</span>
                    <?php else: ?>
                        <span class="ha-nombre"><?= htmlspecialchars($s['trabajador_nombre']) ?></span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (!$esLibre): ?>
                    <button class="ha-btn-quitar"
                            onclick="haAbrirQuitar(<?= $s['id_slot'] ?>, '<?= addslashes($s['trabajador_nombre']) ?>', '<?= addslashes($s['local_desc']) ?> <?= addslashes($turnosLabel[$s['turno_id']] ?? '') ?>')">
                        ✕ Quitar
                    </button>
                    <?php else: ?>
                    <span style="font-size:.7rem;color:#cbd5e1;">libre</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- Historial de solicitudes recientes -->
    <?php if (!empty($historial)): ?>
    <div>
        <div class="ha-title" style="margin-bottom:.5rem;">📋 Historial de coberturas recientes</div>
        <div style="overflow-x:auto;">
        <table class="ha-hist-table">
            <thead>
                <tr>
                    <th>Fecha turno</th>
                    <th>Local · Turno</th>
                    <th>Tipo</th>
                    <th>Quién cubrió</th>
                    <th>Reemplazó a</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($historial as $h): ?>
            <tr>
                <td><?= date('d/m/Y', strtotime($h['fecha_dia'])) ?></td>
                <td style="color:#64748b;"><?= htmlspecialchars($h['local_desc']) ?> · <?= htmlspecialchars($h['turno_desc']) ?></td>
                <td>
                    <span class="ha-badge-tipo ha-badge-<?= $h['tipo'] ?>">
                        <?= $h['tipo'] === 'COBERTURA' ? 'Cobertura' : 'Cambio' ?>
                    </span>
                    <?php if (($h['sol_estado'] ?? '') === 'REVERTIDA'): ?>
                    <br><span class="ha-revertida">revertida</span>
                    <?php endif; ?>
                </td>
                <td style="font-weight:600;"><?= htmlspecialchars($h['solicitante_nombre']) ?></td>
                <td style="color:#64748b;"><?= $h['original_nombre'] ? htmlspecialchars($h['original_nombre']) : '—' ?></td>
                <td>
                    <?php if ($h['tipo'] === 'COBERTURA' && ($h['sol_estado'] ?? 'ACTIVA') === 'ACTIVA'): ?>
                    <button class="ha-btn-revertir"
                            onclick="haAbrirRevertir(<?= $h['id_solicitud'] ?>, '<?= addslashes($h['solicitante_nombre']) ?>', '<?= addslashes($h['original_nombre'] ?? '') ?>')">
                        ↩ Revertir
                    </button>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
    <?php endif; ?>

</div>

<!-- Modal: quitar del turno -->
<div id="haModalQuitar" class="ha-modal-overlay" hidden>
    <div class="ha-modal">
        <h3>✕ Quitar del turno</h3>
        <p id="haQuitarDesc"></p>
        <input type="password" id="haQuitarPwd" placeholder="Tu contraseña de administrador"
               onkeydown="if(event.key==='Enter') haConfirmarQuitar()">
        <div id="haQuitarErr" class="ha-modal__err"></div>
        <div class="ha-modal__footer">
            <button onclick="haCerrarQuitar()" style="background:#f1f5f9;color:#64748b;">Cancelar</button>
            <button onclick="haConfirmarQuitar()" style="background:#dc2626;color:#fff;">Quitar</button>
        </div>
    </div>
</div>

<!-- Modal: revertir cobertura (admin) -->
<div id="haModalRevertir" class="ha-modal-overlay" hidden>
    <div class="ha-modal">
        <h3>↩ Revertir cobertura</h3>
        <p id="haRevertirDesc"></p>
        <input type="password" id="haRevertirPwd" placeholder="Tu contraseña de administrador"
               onkeydown="if(event.key==='Enter') haConfirmarRevertir()">
        <div id="haRevertirErr" class="ha-modal__err"></div>
        <div class="ha-modal__footer">
            <button onclick="haCerrarRevertir()" style="background:#f1f5f9;color:#64748b;">Cancelar</button>
            <button onclick="haConfirmarRevertir()" style="background:#dc2626;color:#fff;">Revertir</button>
        </div>
    </div>
</div>

<script>
let _haSlotId      = null;
let _haSolicitudId = null;

// ── Quitar del turno ──────────────────────────────────
function haAbrirQuitar(slotId, nombre, ubicacion) {
    _haSlotId = slotId;
    document.getElementById('haQuitarDesc').textContent =
        `Se quitará a "${nombre}" del turno (${ubicacion}). El slot quedará libre.`;
    document.getElementById('haQuitarPwd').value = '';
    document.getElementById('haQuitarErr').style.display = 'none';
    document.getElementById('haModalQuitar').removeAttribute('hidden');
    setTimeout(() => document.getElementById('haQuitarPwd').focus(), 50);
}

function haCerrarQuitar() {
    document.getElementById('haModalQuitar').setAttribute('hidden', '');
    _haSlotId = null;
}

async function haConfirmarQuitar() {
    const pwd    = document.getElementById('haQuitarPwd').value.trim();
    const errEl  = document.getElementById('haQuitarErr');
    if (!pwd) { errEl.textContent = 'Ingresa tu contraseña.'; errEl.style.display = 'block'; return; }

    try {
        const r   = await fetch(`${BASE}/horario/api/slot/${_haSlotId}/liberar-admin`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ password: pwd }),
        });
        const res = await r.json();
        if (res.success) { haCerrarQuitar(); location.reload(); }
        else { errEl.textContent = res.message || 'Error.'; errEl.style.display = 'block'; }
    } catch { errEl.textContent = 'Error de conexión.'; errEl.style.display = 'block'; }
}

// ── Revertir cobertura (admin) ────────────────────────
function haAbrirRevertir(solicitudId, quienCubrio, original) {
    _haSolicitudId = solicitudId;
    const desc = original
        ? `Se restaurará el turno a "${original}". "${quienCubrio}" dejará de cubrir.`
        : `El slot quedará libre (lo tomó "${quienCubrio}" cuando estaba vacío).`;
    document.getElementById('haRevertirDesc').textContent = desc;
    document.getElementById('haRevertirPwd').value = '';
    document.getElementById('haRevertirErr').style.display = 'none';
    document.getElementById('haModalRevertir').removeAttribute('hidden');
    setTimeout(() => document.getElementById('haRevertirPwd').focus(), 50);
}

function haCerrarRevertir() {
    document.getElementById('haModalRevertir').setAttribute('hidden', '');
    _haSolicitudId = null;
}

async function haConfirmarRevertir() {
    const pwd   = document.getElementById('haRevertirPwd').value.trim();
    const errEl = document.getElementById('haRevertirErr');
    if (!pwd) { errEl.textContent = 'Ingresa tu contraseña.'; errEl.style.display = 'block'; return; }

    try {
        const r   = await fetch(`${BASE}/horario/api/solicitud/${_haSolicitudId}/revertir`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ password: pwd }),
        });
        const res = await r.json();
        if (res.success) { haCerrarRevertir(); location.reload(); }
        else { errEl.textContent = res.message || 'Error.'; errEl.style.display = 'block'; }
    } catch { errEl.textContent = 'Error de conexión.'; errEl.style.display = 'block'; }
}
</script>
