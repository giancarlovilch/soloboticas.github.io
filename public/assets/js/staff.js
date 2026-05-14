/**
 * Portal colaborador — Solo Boticas
 */

// ── Base URL ──────────────────────────────────────────
// La variable BASE y url() se definen inline en el PHP antes de este script.

// ── Elementos ─────────────────────────────────────────
const relojEl      = document.getElementById('reloj');
const todayStatus  = document.getElementById('todayStatus');
const todayTimes   = document.getElementById('todayTimes');
const horaIngresoEl= document.getElementById('horaIngreso');
const horaSalidaEl = document.getElementById('horaSalida');
const localWrap    = document.getElementById('localWrap');
const localSelect  = document.getElementById('localSelect');
const btnEntrada   = document.getElementById('btnEntrada');
const btnSalida    = document.getElementById('btnSalida');
const marcarMsg    = document.getElementById('marcarMsg');

// Modal
const modal          = document.getElementById('marcarModal');
const modalTitulo    = document.getElementById('modalTitulo');
const modalHora      = document.getElementById('modalHoraActual');
const modalChecklist = document.getElementById('modalChecklist');
const modalPassword  = document.getElementById('modalPassword');
const modalError     = document.getElementById('modalError');
const btnConfirmar   = document.getElementById('btnConfirmar');

let hoyData        = null;
let tipoActual     = 'ENTRADA';  // 'ENTRADA' | 'SALIDA'
let checklistCache = { APERTURA: [], CIERRE: [] };
let modalClockTick = null;

// ── Reloj en tiempo real ──────────────────────────────
function tickReloj() {
    if (!relojEl) return;
    relojEl.textContent = new Date().toLocaleTimeString('es-PE', {
        hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false
    });
}
setInterval(tickReloj, 1000);
tickReloj();

// ── Inicialización ────────────────────────────────────
document.addEventListener('DOMContentLoaded', async () => {
    await Promise.all([
        cargar(),
        cargarChecklist('APERTURA'),
        cargarChecklist('CIERRE'),
    ]);
});

async function cargar() {
    try {
        const r   = await fetch(url('/staff/api/historial'), { headers: { Accept: 'application/json' } });
        const res = await r.json();
        if (!res.success) { showMsg('Error al cargar datos.', 'error'); return; }

        llenarLocales(res.data.locales || []);
        renderHistorial(res.data.historial || []);
        actualizarEstadoHoy(res.data.hoy, res.data.sesiones_hoy || 0);
    } catch {
        showMsg('Error de conexión.', 'error');
    }
}

async function cargarChecklist(tipo) {
    try {
        const r   = await fetch(url(`/staff/api/checklist?tipo=${tipo}`), { headers: { Accept: 'application/json' } });
        const res = await r.json();
        if (res.success) checklistCache[tipo] = res.data || [];
    } catch { /* silencioso, el checklist es opcional */ }
}

// ── Estado del día ────────────────────────────────────
function actualizarEstadoHoy(hoy, sesionesHoy = 0) {
    hoyData = hoy;

    if (!hoy) {
        // Sin sesión abierta — ¿hubo sesiones cerradas hoy?
        if (sesionesHoy > 0) {
            const txt = sesionesHoy === 1
                ? 'Ya tienes 1 asistencia marcada hoy'
                : `Ya tienes ${sesionesHoy} asistencias marcadas hoy`;
            todayStatus.textContent = txt;
            todayStatus.className   = 'staff-today__status status-ok';
        } else {
            todayStatus.textContent = 'Sin marcar hoy';
            todayStatus.className   = 'staff-today__status';
        }
        todayTimes.hidden   = sesionesHoy === 0;
        localWrap.hidden    = false;
        btnEntrada.disabled = false;
        btnSalida.disabled  = true;
        return;
    }

    horaIngresoEl.textContent = hoy.hora_ingreso ? formatHora(hoy.hora_ingreso) : '--:--';
    horaSalidaEl.textContent  = hoy.hora_salida  ? formatHora(hoy.hora_salida)  : '--:--';
    todayTimes.hidden = false;
    localWrap.hidden  = true;

    if (hoy.hora_salida) {
        todayStatus.textContent = `✓ Jornada completa — ${estadoLabel(hoy.estado)}`;
        todayStatus.className   = 'staff-today__status status-done';
        btnEntrada.disabled     = true;
        btnSalida.disabled      = true;
    } else {
        todayStatus.textContent = `Entrada marcada — ${estadoLabel(hoy.estado)}`;
        todayStatus.className   = `staff-today__status ${hoy.estado === 'TARDE' ? 'status-late' : 'status-ok'}`;
        btnEntrada.disabled     = true;
        btnSalida.disabled      = false;
    }
}

// ── Abrir modal ───────────────────────────────────────
window.abrirModal = function(tipo) {
    tipoActual = tipo;
    modalError.hidden = true;
    modalPassword.value = '';

    modalTitulo.textContent  = tipo === 'ENTRADA' ? 'Confirmar Entrada' : 'Confirmar Salida';
    btnConfirmar.textContent = tipo === 'ENTRADA' ? 'Confirmar entrada' : 'Confirmar salida';
    btnConfirmar.className   = tipo === 'ENTRADA'
        ? 'staff-modal__btn-confirm staff-modal__btn-confirm--entrada'
        : 'staff-modal__btn-confirm staff-modal__btn-confirm--salida';

    // Actualizar hora en el modal cada segundo
    clearInterval(modalClockTick);
    const tickModal = () => {
        modalHora.textContent = new Date().toLocaleTimeString('es-PE', {
            hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false
        });
    };
    tickModal();
    modalClockTick = setInterval(tickModal, 1000);

    // Construir checklist
    const claveTipo = tipo === 'ENTRADA' ? 'APERTURA' : 'CIERRE';
    const items     = checklistCache[claveTipo] || [];
    modalChecklist.innerHTML = '';

    if (items.length === 0) {
        document.getElementById('checklistWrap').hidden = true;
    } else {
        document.getElementById('checklistWrap').hidden = false;
        items.forEach(item => {
            const label = document.createElement('label');
            label.className = 'staff-checklist__item';
            label.innerHTML = `
                <input type="checkbox" class="staff-checklist__check"
                       data-id="${item.id_checklist}" checked>
                <span class="staff-checklist__desc">${escHtml(item.descripcion)}</span>`;
            modalChecklist.appendChild(label);
        });
    }

    modal.hidden = false;
    setTimeout(() => modalPassword.focus(), 100);
};

window.cerrarModal = function() {
    clearInterval(modalClockTick);
    modal.hidden = true;
    modalError.hidden = true;
};

// ── Confirmar marcaje ─────────────────────────────────
window.confirmarMarcaje = async function() {
    const pw = modalPassword.value;
    if (!pw) { showModalError('Ingresa tu contraseña para confirmar.'); return; }

    // Recolectar checklist
    const checklist = [];
    modalChecklist.querySelectorAll('.staff-checklist__check').forEach(cb => {
        checklist.push({
            checklist_id: parseInt(cb.dataset.id),
            cumplido:     cb.checked,
            observacion:  null,
        });
    });

    btnConfirmar.disabled    = true;
    btnConfirmar.textContent = 'Confirmando...';
    modalError.hidden        = true;

    const body = {
        tipo:      tipoActual,
        password:  pw,
        checklist,
        local_id:  tipoActual === 'ENTRADA' && localSelect?.value ? parseInt(localSelect.value) : null,
    };

    try {
        const r   = await fetch(url('/staff/asistencia/marcar'), {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            body:    JSON.stringify(body),
        });
        const res = await r.json();

        if (res.success) {
            cerrarModal();
            showMsg(res.message, 'ok');
            actualizarEstadoHoy(res.data?.sesion ?? null, res.data?.sesiones_hoy ?? 0);
            await cargar(); // recarga historial
        } else {
            showModalError(res.message || 'Error al registrar.');
        }
    } catch {
        showModalError('Error de conexión. Intenta de nuevo.');
    } finally {
        btnConfirmar.disabled    = false;
        btnConfirmar.textContent = tipoActual === 'ENTRADA' ? 'Confirmar entrada' : 'Confirmar salida';
    }
};

// ── Locales ───────────────────────────────────────────
function llenarLocales(locales) {
    if (!localSelect) return;
    locales.forEach(({ id, descripcion }) => {
        const o = document.createElement('option');
        o.value = id; o.textContent = descripcion;
        localSelect.appendChild(o);
    });
}

// ── Historial ─────────────────────────────────────────
function renderHistorial(registros) {
    const tbody = document.getElementById('tbodyHistorial');
    if (!tbody) return;
    if (!registros.length) {
        tbody.innerHTML = '<tr><td colspan="4" class="staff-table-empty">Sin registros aún.</td></tr>';
        return;
    }
    const puntLabel = { MUY_TEMPRANO:'+10 min antes', TEMPRANO:'Temprano', TARDE:'Tarde', MUY_TARDE:'+10 min tarde' };
    tbody.innerHTML = registros.map(r => `
        <tr>
            <td>${formatFecha(r.fecha)}</td>
            <td>${r.local_desc || '—'}</td>
            <td>${badgeEstado(r.estado)}</td>
            <td>${r.llegada_puntualidad ? (puntLabel[r.llegada_puntualidad] || r.llegada_puntualidad) : '—'}</td>
        </tr>`).join('');
}

// ── UI helpers ─────────────────────────────────────────
function showMsg(msg, type) {
    marcarMsg.textContent = msg;
    marcarMsg.className   = `staff-msg ${type}`;
    marcarMsg.hidden      = false;
}

function showModalError(msg) {
    modalError.textContent = msg;
    modalError.hidden      = false;
}

function formatHora(dt) {
    if (!dt) return '--:--';
    const d = new Date(dt.replace(' ', 'T'));
    return d.toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit', hour12: false });
}

function formatFecha(f) {
    if (!f) return '';
    const [y, m, d] = f.split('-');
    return `${d}/${m}/${y}`;
}

function estadoLabel(e) {
    const map = { 'A TIEMPO':'A tiempo', 'TARDE':'Tarde', 'FALTA':'Falta', 'EXTRA':'Extra', 'TEMPRANO':'Temprano' };
    return map[e] || e;
}

function badgeEstado(e) {
    const cls = { 'A TIEMPO':'badge-atiempo', 'TARDE':'badge-tarde', 'FALTA':'badge-falta', 'EXTRA':'badge-extra', 'TEMPRANO':'badge-temprano' }[e] || 'badge-falta';
    return `<span class="${cls}">${estadoLabel(e)}</span>`;
}

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
