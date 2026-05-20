/**
 * Horarios Semanales tipo Cine — Solo Boticas
 */

let _pickerSlotEl = null;
let TRABAJADORES  = [];
let _slots        = [];
let _filtroId     = 0;

// ── Carga inicial ──────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    if (SEMANA_ID) cargarSlots();
    if (ES_ADMIN)  cargarTrabajadores();
});

// ── Workers (solo admin) ───────────────────────────────
async function cargarTrabajadores() {
    try {
        const r   = await fetch(`${BASE}/horario/api/trabajadores`);
        const res = await r.json();
        if (!res.success) return;
        TRABAJADORES = res.data || [];
        const sel = document.getElementById('pickerSelect');
        if (!sel) return;
        TRABAJADORES.forEach(t => {
            const opt = document.createElement('option');
            opt.value       = t.id;
            opt.textContent = t.nombre;
            sel.appendChild(opt);
        });
    } catch { /* silencioso */ }
}

// ── Slots ──────────────────────────────────────────────
async function cargarSlots() {
    try {
        const r   = await fetch(`${BASE}/horario/api/semana/${SEMANA_ID}`);
        const res = await r.json();
        if (!res.success) return;
        _slots = res.data || [];
        poblarFiltro(_slots);
        renderSlots(_slots);
    } catch {
        mostrarToast('Error al cargar los horarios.', 'error');
    }
}

function poblarFiltro(slots) {
    const sel = document.getElementById('filtroTrabajador');
    if (!sel) return;
    const vistos = new Set();
    const opts = [{ id: 0, nombre: '— Todos —' }];
    slots.forEach(s => {
        if (s.postulante_id && !vistos.has(s.postulante_id)) {
            vistos.add(s.postulante_id);
            opts.push({ id: parseInt(s.postulante_id), nombre: s.trabajador_nombre || '—' });
        }
    });
    opts.sort((a, b) => a.nombre.localeCompare(b.nombre));
    sel.innerHTML = opts.map(o => `<option value="${o.id}">${o.nombre}</option>`).join('');
    if (_filtroId) sel.value = _filtroId;
}

function aplicarFiltro(val) {
    _filtroId = parseInt(val) || 0;
    const btn = document.getElementById('btnLimpiarFiltro');
    if (btn) btn.hidden = !_filtroId;
    const sel = document.getElementById('filtroTrabajador');
    if (sel) sel.style.borderColor = _filtroId ? '#2563eb' : '#e2e8f0';
    renderSlots(_slots);
}

function renderSlots(slots) {
    // Resetear todos los asientos a "libre" (bloqueado hasta que se repinte)
    document.querySelectorAll('.hor-asiento[data-semana]').forEach(el => {
        el.dataset.slotid      = '';
        el.dataset.ocupadoPor  = '';
        el.className           = 'hor-asiento hor-asiento--libre';
        el.title               = '';
        el.style.pointerEvents = 'none'; // bloqueado hasta repintar
        el.onclick             = null;
        el.querySelector('.hor-asiento__nombre').textContent = '…';
        quitarBtnPicker(el);
        quitarBtnLiberar(el);
    });

    // Pintar según los datos del servidor
    slots.forEach(s => {
        const id = `slot-${s.semana_id}-${s.local_id}-${s.turno_id}-${s.dia_semana}-${s.rol_puesto}-${s.slot_num}`;
        const el = document.getElementById(id);
        if (!el) return;

        el.dataset.slotid = s.id_slot;

        if (s.postulante_id) {
            el.dataset.ocupadoPor = s.postulante_id;
            const nombre = s.trabajador_nombre || '—';

            const nombreEl = el.querySelector('.hor-asiento__nombre');
            nombreEl.textContent = nombre;

            if (parseInt(s.postulante_id) === MI_ID) {
                // Slot propio: solo el dueño puede liberarlo
                el.className           = 'hor-asiento hor-asiento--mio';
                el.title               = `Tuyo (${nombre}) — clic para liberar`;
                el.style.pointerEvents = 'auto';
                el.onclick             = () => clickAsiento(el);
            } else {
                // Ocupado por otro (o filtrado)
                const esFiltrado = _filtroId && parseInt(s.postulante_id) === _filtroId;
                el.className           = 'hor-asiento ' + (esFiltrado ? 'hor-asiento--filtrado' : 'hor-asiento--ocupado');
                el.title               = `Ocupado por ${nombre}`;
                el.style.pointerEvents = ES_ADMIN ? 'auto' : 'none';
                el.onclick             = null;
                if (ES_ADMIN) agregarBtnLiberar(el, nombre);
            }
            quitarBtnPicker(el);
        } else {
            el.className          = 'hor-asiento hor-asiento--libre';
            el.title              = 'Libre — clic para tomar';
            el.style.pointerEvents = '';
            el.onclick            = () => clickAsiento(el);
            el.querySelector('.hor-asiento__nombre').innerHTML = el.dataset.rol === 'LIMPIEZA'
                ? '<span style="font-size:1.25em;line-height:1;letter-spacing:1px;">🧹🫧🧼</span>'
                : '＋';
            if (ES_ADMIN) agregarBtnPicker(el);
        }
    });

    actualizarResumen(slots);
}

// ── Botón picker (admin, slots libres) ────────────────
function agregarBtnPicker(el) {
    if (el.querySelector('.hor-asiento__btn-picker')) return;
    const btn = document.createElement('button');
    btn.className = 'hor-asiento__btn-picker';
    btn.title     = 'Asignar a trabajador';
    btn.textContent = '👤';
    btn.addEventListener('click', e => {
        e.stopPropagation();
        abrirPicker(el);
    });
    el.appendChild(btn);
}

function quitarBtnPicker(el) {
    el.querySelector('.hor-asiento__btn-picker')?.remove();
}

// ── Botón liberar (admin, slots ocupados por otro) ────
let _liberarSlotEl = null;

function agregarBtnLiberar(el, nombre) {
    if (el.querySelector('.hor-asiento__btn-liberar')) return;
    const btn = document.createElement('button');
    btn.className   = 'hor-asiento__btn-liberar';
    btn.title       = `Quitar a ${nombre}`;
    btn.textContent = '✕';
    btn.addEventListener('click', e => {
        e.stopPropagation();
        abrirModalLiberar(el, nombre);
    });
    el.appendChild(btn);
}

function quitarBtnLiberar(el) {
    el.querySelector('.hor-asiento__btn-liberar')?.remove();
}

function abrirModalLiberar(el, nombre) {
    _liberarSlotEl = el;
    const overlay = document.getElementById('liberarAdminOverlay');
    if (!overlay) return;
    document.getElementById('liberarAdminNombre').textContent = `Vas a quitar a ${nombre} de este turno.`;
    document.getElementById('liberarAdminPwd').value = '';
    overlay.hidden = false;
    setTimeout(() => document.getElementById('liberarAdminPwd').focus(), 50);
}

function cerrarModalLiberar() {
    document.getElementById('liberarAdminOverlay').hidden = true;
    _liberarSlotEl = null;
}

async function confirmarLiberarAdmin() {
    const password = document.getElementById('liberarAdminPwd').value.trim();
    if (!password) { mostrarToast('Ingresa tu contraseña', 'info'); return; }

    const el     = _liberarSlotEl;
    const slotId = el?.dataset.slotid;
    if (!slotId) return;

    cerrarModalLiberar();
    el.classList.add('hor-asiento--loading');

    try {
        const r   = await fetch(`${BASE}/horario/api/slot/${slotId}/liberar-admin`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ password }),
        });
        const res = await r.json();
        await cargarSlots();
        if (!res.success) mostrarToast(res.message || 'No se pudo quitar al trabajador.', 'error');
    } catch {
        el.classList.remove('hor-asiento--loading');
        mostrarToast('Error de conexión.', 'error');
    }
}

// ── Resumen de disponibles ─────────────────────────────
function actualizarResumen(slots) {
    const el = document.getElementById('horResumen');
    if (!el) return;

    const libres = {};
    slots.forEach(s => {
        if (s.rol_puesto === 'ALMACENERA' || s.rol_puesto === 'LIMPIEZA') return;
        const lid = s.local_id;
        if (!libres[lid]) libres[lid] = 0;
        if (!s.postulante_id) libres[lid]++;
    });

    const nombres = { 2: 'SB2', 3: 'SB3', 4: 'SB4' };
    const partes  = Object.entries(nombres).map(([lid, nombre]) => {
        const n     = libres[lid] ?? 0;
        const badge = n > 0
            ? `<span class="hor-resumen__badge hor-resumen__badge--ok">${n} libre${n !== 1 ? 's' : ''}</span>`
            : `<span class="hor-resumen__badge hor-resumen__badge--lleno">completo</span>`;
        return `<span class="hor-resumen__item">${nombre} ${badge}</span>`;
    });

    el.innerHTML = `<span class="hor-resumen__label">Disponibles</span>${partes.join('')}`;
    el.hidden = false;
}

// ── Clic en asiento (comportamiento estándar) ──────────
function clickAsiento(el) {
    if (!EDITABLE) {
        mostrarToast('La semana está cerrada. Solo el administrador puede modificar.', 'info');
        return;
    }

    const slotId     = el.dataset.slotid;
    const ocupadoPor = el.dataset.ocupadoPor;

    if (!slotId) return;

    // No-admin no puede tomar slots de otros
    if (!ES_ADMIN && ocupadoPor && parseInt(ocupadoPor) !== MI_ID) return;

    // Slot libre: asignar a sí mismo | Slot mío u otro (admin): liberar
    ejecutarSlot(el, ocupadoPor ? 'liberar' : 'asignar', null);
}

// ── Ejecución de asignar/liberar ───────────────────────
async function ejecutarSlot(el, accion, targetId) {
    const slotId   = el.dataset.slotid;
    const semanaId = el.dataset.semana;

    el.classList.add('hor-asiento--loading');

    const body = { slot_id: parseInt(slotId), semana_id: parseInt(semanaId) };
    if (targetId) body.target_id = parseInt(targetId);

    try {
        const r   = await fetch(`${BASE}/horario/api/slot/${accion}`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify(body),
        });
        const res = await r.json();
        await cargarSlots();
        if (!res.success) mostrarToast(res.message || 'No se pudo completar la acción.', 'error');
    } catch {
        el.classList.remove('hor-asiento--loading');
        mostrarToast('Error de conexión.', 'error');
    }
}

// ── Admin picker ───────────────────────────────────────
function abrirPicker(el) {
    _pickerSlotEl = el;
    document.getElementById('pickerSelect').value = '';
    document.getElementById('adminPickerOverlay').hidden = false;
}

function cerrarPicker() {
    document.getElementById('adminPickerOverlay').hidden = true;
    _pickerSlotEl = null;
}

function pickerAsignar() {
    const targetId = document.getElementById('pickerSelect').value;
    if (!targetId) {
        mostrarToast('Selecciona un trabajador', 'info');
        return;
    }
    const el = _pickerSlotEl;
    cerrarPicker();
    ejecutarSlot(el, 'asignar', targetId);
}




// ── Toast central ──────────────────────────────────────
let _toastTimer = null;

function mostrarToast(txt, tipo = 'error') {
    let toast = document.getElementById('horToast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'horToast';
        document.body.appendChild(toast);
    }
    toast.textContent = txt;
    toast.className   = `hor-toast hor-toast--${tipo}`;
    void toast.offsetWidth;
    toast.classList.add('hor-toast--visible');
    clearTimeout(_toastTimer);
    _toastTimer = setTimeout(() => {
        toast.classList.remove('hor-toast--visible');
    }, 1800);
}
