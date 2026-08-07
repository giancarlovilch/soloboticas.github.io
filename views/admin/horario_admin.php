<?php
if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] !== 'ADMIN') exit('Acceso denegado');

$fechaDesde = $horarioFechaDesde ?? $horarioFecha ?? date('Y-m-d');
$fechaHasta = $horarioFechaHasta ?? $horarioFecha ?? date('Y-m-d');
$slots      = $horarioSlots      ?? [];
$historial  = $horarioHistorial  ?? [];
$locales    = $horarioLocales    ?? [];
$roles      = $horarioRoles      ?? [];
$trabajadores = $horarioTrabajadores ?? [];

$fLocal  = $horarioFiltroLocal ?? 0;
$fTurno  = $horarioFiltroTurno ?? 0;
$fRol    = $horarioFiltroRol   ?? 0;
$fEstado = $horarioFiltroEst   ?? '';
$fTrab   = $horarioFiltroPost  ?? 0;

$turnosLabel = [1 => '☀️ Mañana', 2 => '🌙 Tarde'];
$hoyStr      = date('Y-m-d');
$esRangoUnico = ($fechaDesde === $fechaHasta);

$totalSlots   = count($slots);
$totalOcupado = count(array_filter($slots, fn($s) => !empty($s['postulante_id'])));
$totalLibre   = $totalSlots - $totalOcupado;
?>

<style>
.ha-wrap   { padding: 1rem 1.25rem; display: flex; flex-direction: column; gap: 1.25rem; }
.ha-title  { font-size: .82rem; font-weight: 800; text-transform: uppercase; letter-spacing: .07em; color: #0097A7; margin-bottom: .6rem; }
.ha-form   { display: flex; align-items: flex-end; gap: .6rem; flex-wrap: wrap; }
.ha-field  { display: flex; flex-direction: column; gap: .25rem; }
.ha-field label { font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #94a3b8; }
.ha-form input[type=date], .ha-form select {
    padding: .4rem .6rem; border: 1.5px solid #e2e8f0; border-radius: 8px;
    font-size: .8rem; outline: none; cursor: pointer; color: #1e293b; background: #fff;
}
.ha-form input[type=date]:focus, .ha-form select:focus { border-color: #0097A7; }
.ha-form button {
    padding: .45rem 1.1rem; border-radius: 8px; font-size: .78rem; font-weight: 700;
    background: #0097A7; color: #fff; border: none; cursor: pointer; height: fit-content;
}
.ha-form button:hover { background: #007b8a; }
.ha-clear { font-size: .72rem; color: #94a3b8; text-decoration: none; align-self: center; }

.ha-stats { display: flex; gap: .5rem; flex-wrap: wrap; margin-top: .35rem; }
.ha-stat  { font-size: .7rem; font-weight: 700; padding: 3px 10px; border-radius: 20px; }
.ha-stat--total  { background: #f1f5f9; color: #475569; }
.ha-stat--ocup   { background: #e0f7fa; color: #0097A7; }
.ha-stat--libre  { background: #f8fafc; color: #94a3b8; }

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
.ha-flag   { font-size: .68rem; margin-left: 4px; cursor: help; }
.ha-acciones { display: flex; gap: .4rem; flex-wrap: wrap; }

.ha-btn-quitar, .ha-btn-reasignar {
    padding: 3px 10px; border-radius: 6px; font-size: .72rem; font-weight: 700;
    cursor: pointer; white-space: nowrap; transition: background .12s; border: 1.5px solid;
}
.ha-btn-quitar    { background: transparent; border-color: #fca5a5; color: #dc2626; }
.ha-btn-quitar:hover    { background: #fee2e2; }
.ha-btn-reasignar { background: transparent; border-color: #93c5fd; color: #1d4ed8; }
.ha-btn-reasignar:hover { background: #dbeafe; }

/* Modal */
.ha-modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,.5); z-index: 9000; display: flex; align-items: center; justify-content: center; }
.ha-modal-overlay[hidden] { display: none !important; }
.ha-modal {
    background: #fff; border-radius: 14px; padding: 1.5rem;
    width: 360px; max-width: 90vw;
    box-shadow: 0 20px 60px rgba(0,0,0,.22);
    display: flex; flex-direction: column; gap: .85rem;
}
.ha-modal h3  { font-size: .95rem; font-weight: 700; color: #1e293b; margin: 0; }
.ha-modal p   { font-size: .8rem; color: #64748b; line-height: 1.5; margin: 0; }
.ha-modal select, .ha-modal input {
    width: 100%; padding: .5rem .75rem; border: 1.5px solid #e2e8f0;
    border-radius: 8px; font-size: .85rem; box-sizing: border-box; outline: none;
}
.ha-modal input:focus, .ha-modal select:focus { border-color: #dc2626; }
.ha-modal__warn {
    font-size: .74rem; color: #92400e; background: #fef3c7; border-radius: 8px;
    padding: .55rem .7rem; line-height: 1.45; display: none;
}
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
            Busca, filtra y corrige el horario en cualquier fecha. Puedes reasignar un turno ya
            trabajado a otra persona: el cuadre, la ficha de asistencia y el rendimiento/bono se
            actualizan juntos, sin restricciones de pasado.
        </p>
    </div>

    <!-- Filtros -->
    <form method="GET" class="ha-form">
        <input type="hidden" name="page" value="horario">

        <div class="ha-field">
            <label>Desde</label>
            <input type="date" name="fecha_desde" value="<?= htmlspecialchars($fechaDesde) ?>">
        </div>
        <div class="ha-field">
            <label>Hasta</label>
            <input type="date" name="fecha_hasta" value="<?= htmlspecialchars($fechaHasta) ?>">
        </div>

        <div class="ha-field">
            <label>Local</label>
            <select name="local">
                <option value="0">Todos</option>
                <?php foreach ($locales as $l): ?>
                <option value="<?= $l['id'] ?>" <?= $fLocal == $l['id'] ? 'selected' : '' ?>><?= htmlspecialchars($l['descripcion']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="ha-field">
            <label>Turno</label>
            <select name="turno">
                <option value="0">Todos</option>
                <?php foreach ($turnosLabel as $tid => $tlabel): ?>
                <option value="<?= $tid ?>" <?= $fTurno == $tid ? 'selected' : '' ?>><?= $tlabel ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="ha-field">
            <label>Rol</label>
            <select name="rol">
                <option value="0">Todos</option>
                <?php foreach ($roles as $r): ?>
                <option value="<?= $r['id'] ?>" <?= $fRol == $r['id'] ? 'selected' : '' ?>><?= htmlspecialchars($r['descripcion']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="ha-field">
            <label>Estado</label>
            <select name="estado">
                <option value=""        <?= $fEstado === ''        ? 'selected' : '' ?>>Todos</option>
                <option value="OCUPADO" <?= $fEstado === 'OCUPADO' ? 'selected' : '' ?>>Ocupado</option>
                <option value="LIBRE"   <?= $fEstado === 'LIBRE'   ? 'selected' : '' ?>>Libre</option>
            </select>
        </div>

        <div class="ha-field">
            <label>Trabajador</label>
            <select name="trabajador">
                <option value="0">Todos</option>
                <?php foreach ($trabajadores as $t): ?>
                <option value="<?= $t['id'] ?>" <?= $fTrab == $t['id'] ? 'selected' : '' ?>><?= htmlspecialchars($t['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit">🔍 Buscar</button>
        <a href="?page=horario" class="ha-clear">Limpiar filtros</a>
    </form>

    <!-- Tabla de slots -->
    <div>
        <div class="ha-title" style="margin-bottom:.5rem;">
            <?php if ($esRangoUnico): ?>
                Turnos del <?= (new DateTime($fechaDesde))->format('d/m/Y') ?>
                <?php if ($fechaDesde < $hoyStr): ?>
                <span style="background:#fef3c7;color:#92400e;font-size:.65rem;padding:2px 7px;border-radius:10px;font-weight:700;margin-left:.4rem;">Pasado</span>
                <?php elseif ($fechaDesde === $hoyStr): ?>
                <span style="background:#d1fae5;color:#065f46;font-size:.65rem;padding:2px 7px;border-radius:10px;font-weight:700;margin-left:.4rem;">Hoy</span>
                <?php else: ?>
                <span style="background:#e0f7fa;color:#0097A7;font-size:.65rem;padding:2px 7px;border-radius:10px;font-weight:700;margin-left:.4rem;">Futuro</span>
                <?php endif; ?>
            <?php else: ?>
                Resultados del <?= (new DateTime($fechaDesde))->format('d/m/Y') ?> al <?= (new DateTime($fechaHasta))->format('d/m/Y') ?>
            <?php endif; ?>
        </div>
        <div class="ha-stats">
            <span class="ha-stat ha-stat--total"><?= $totalSlots ?> turnos</span>
            <span class="ha-stat ha-stat--ocup"><?= $totalOcupado ?> ocupados</span>
            <span class="ha-stat ha-stat--libre"><?= $totalLibre ?> libres</span>
        </div>

        <?php if (empty($slots)): ?>
        <p style="font-size:.78rem;color:#94a3b8;padding:.75rem 0;">Sin turnos que coincidan con los filtros.</p>
        <?php else: ?>
        <div style="overflow-x:auto;margin-top:.5rem;">
        <table class="ha-table">
            <thead>
                <tr>
                    <?php if (!$esRangoUnico): ?><th>Fecha</th><?php endif; ?>
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
                <?php if (!$esRangoUnico): ?>
                <td style="color:#64748b;white-space:nowrap;"><?= date('d/m/Y', strtotime($s['fecha_dia'])) ?></td>
                <?php endif; ?>
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
                        <?php if (!empty($s['encuestado'])): ?>
                        <span class="ha-flag" title="Ya tiene ficha de asistencia (encuesta) registrada este turno">📝</span>
                        <?php endif; ?>
                        <?php if (!empty($s['cuadre_cerrado'])): ?>
                        <span class="ha-flag" title="El cuadre de caja de este turno ya está CERRADA">🔒</span>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (!$esLibre): ?>
                    <div class="ha-acciones">
                        <button class="ha-btn-reasignar"
                                onclick="haAbrirReasignar(<?= $s['id_slot'] ?>, '<?= addslashes($s['trabajador_nombre']) ?>', '<?= addslashes($s['local_desc']) ?> <?= addslashes($turnosLabel[$s['turno_id']] ?? '') ?>', <?= !empty($s['encuestado']) ? 1 : 0 ?>, <?= !empty($s['cuadre_cerrado']) ? 1 : 0 ?>, <?= (int)$s['postulante_id'] ?>)">
                            🔄 Reasignar
                        </button>
                        <button class="ha-btn-quitar"
                                onclick="haAbrirQuitar(<?= $s['id_slot'] ?>, '<?= addslashes($s['trabajador_nombre']) ?>', '<?= addslashes($s['local_desc']) ?> <?= addslashes($turnosLabel[$s['turno_id']] ?? '') ?>')">
                            ✕ Quitar
                        </button>
                    </div>
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

<!-- Modal: reasignar turno a otra persona -->
<div id="haModalReasignar" class="ha-modal-overlay" hidden>
    <div class="ha-modal">
        <h3>🔄 Reasignar turno</h3>
        <p id="haReasignarDesc"></p>
        <div id="haReasignarWarn" class="ha-modal__warn"></div>
        <select id="haReasignarSelect"></select>
        <input type="password" id="haReasignarPwd" placeholder="Tu contraseña de administrador"
               onkeydown="if(event.key==='Enter') haConfirmarReasignar()">
        <div id="haReasignarErr" class="ha-modal__err"></div>
        <div class="ha-modal__footer">
            <button onclick="haCerrarReasignar()" style="background:#f1f5f9;color:#64748b;">Cancelar</button>
            <button onclick="haConfirmarReasignar()" style="background:#1d4ed8;color:#fff;">Reasignar</button>
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
const HA_TRABAJADORES = <?= json_encode(array_values($trabajadores)) ?>;

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

// ── Reasignar turno a otra persona ────────────────────
function haAbrirReasignar(slotId, nombreActual, ubicacion, encuestado, cuadreCerrado, postulanteActualId) {
    _haSlotId = slotId;
    document.getElementById('haReasignarDesc').innerHTML =
        `Reemplazar a <b>${nombreActual}</b> en el turno (${ubicacion}).`;

    const warnEl = document.getElementById('haReasignarWarn');
    const avisos = [];
    if (encuestado)     avisos.push('Ya existe una ficha de asistencia de esta persona para este turno: se anulará automáticamente.');
    if (cuadreCerrado)  avisos.push('El cuadre de caja de este turno ya está cerrado: la participación, apertura/cierre y el rendimiento (ventas/operaciones BCP) se moverán al nuevo trabajador. Los montos de dinero del cuadre NO cambian.');
    if (avisos.length) {
        warnEl.innerHTML = '⚠️ ' + avisos.join('<br>⚠️ ');
        warnEl.style.display = 'block';
    } else {
        warnEl.style.display = 'none';
    }

    const sel = document.getElementById('haReasignarSelect');
    sel.innerHTML = '';
    HA_TRABAJADORES
        .filter(t => t.id != postulanteActualId)
        .forEach(t => {
            const opt = document.createElement('option');
            opt.value = t.id;
            opt.textContent = t.nombre;
            sel.appendChild(opt);
        });

    document.getElementById('haReasignarPwd').value = '';
    document.getElementById('haReasignarErr').style.display = 'none';
    document.getElementById('haModalReasignar').removeAttribute('hidden');
}

function haCerrarReasignar() {
    document.getElementById('haModalReasignar').setAttribute('hidden', '');
    _haSlotId = null;
}

async function haConfirmarReasignar() {
    const nuevoId = document.getElementById('haReasignarSelect').value;
    const pwd     = document.getElementById('haReasignarPwd').value.trim();
    const errEl   = document.getElementById('haReasignarErr');
    if (!nuevoId) { errEl.textContent = 'Selecciona el nuevo trabajador.'; errEl.style.display = 'block'; return; }
    if (!pwd)     { errEl.textContent = 'Ingresa tu contraseña.'; errEl.style.display = 'block'; return; }

    try {
        const r   = await fetch(`${BASE}/horario/api/slot/${_haSlotId}/reasignar`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ postulante_id: nuevoId, password: pwd }),
        });
        const res = await r.json();
        if (res.success) { haCerrarReasignar(); location.reload(); }
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
