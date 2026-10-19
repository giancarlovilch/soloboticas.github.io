/**
 * Horarios Semanales tipo Cine — Solo Boticas
 */

let TRABAJADORES = [];
let _slots        = [];
let _filtroId     = 0;
let _modoCubrir   = false;
let _slotParaMover = null;

// ── Carga inicial ──────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    if (SEMANA_ID) cargarSlots();
});

// ── Workers (para el picker de "CUBRIR / CAMBIAR PUESTO") ──
async function cargarTrabajadores() {
    if (TRABAJADORES.length) return TRABAJADORES;
    try {
        const r   = await fetch(`${BASE}/horario/api/trabajadores`);
        const res = await r.json();
        if (res.success) TRABAJADORES = res.data || [];
    } catch { /* silencioso */ }
    return TRABAJADORES;
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
    // Resetear todos los asientos (el modo lectura por defecto lo da el CSS)
    document.querySelectorAll('.hor-asiento[data-semana]').forEach(el => {
        el.dataset.slotid     = '';
        el.dataset.ocupadoPor = '';
        el.className          = 'hor-asiento hor-asiento--libre';
        el.title              = '';
        el.onclick            = null;
        el.querySelector('.hor-asiento__nombre').textContent = '…';
    });

    slots.forEach(s => {
        const id = `slot-${s.semana_id}-${s.local_id}-${s.turno_id}-${s.dia_semana}-${s.rol_puesto}-${s.slot_num}`;
        const el = document.getElementById(id);
        if (!el) return;

        el.dataset.slotid = s.id_slot;
        const esPasado = s.fecha_dia < HOY;

        if (s.postulante_id) {
            el.dataset.ocupadoPor = s.postulante_id;
            const nombre = s.trabajador_nombre || '—';
            el.querySelector('.hor-asiento__nombre').textContent = nombre;

            if (parseInt(s.postulante_id) === MI_ID) {
                el.className = 'hor-asiento hor-asiento--mio';
            } else {
                const esFiltrado = _filtroId && parseInt(s.postulante_id) === _filtroId;
                el.className = 'hor-asiento ' + (esFiltrado ? 'hor-asiento--filtrado' : 'hor-asiento--ocupado');
            }
            el.title = nombre;

            if (_modoCubrir && (!esPasado || ES_ADMIN)) {
                el.classList.add('hor-asiento--cubrir-eligible');
                el.title   = `Mover este puesto (hoy: ${nombre})`;
                el.onclick = () => abrirPickerMover(s);
            }
        } else {
            el.className = 'hor-asiento hor-asiento--libre';
            el.querySelector('.hor-asiento__nombre').innerHTML = s.rol_puesto === 'LIMPIEZA'
                ? '<span style="font-size:1.25em;line-height:1;letter-spacing:1px;">🧹🫧🧼</span>'
                : '＋';

            if (_modoCubrir && (!esPasado || ES_ADMIN)) {
                el.classList.add('hor-asiento--cubrir-eligible');
                el.title   = 'Turno libre — clic para asignar';
                el.onclick = () => abrirPickerMover(s);
            }
        }
    });

    actualizarResumen(slots);
}

// ── Modo Cubrir Puesto ─────────────────────────────────
function toggleModoCubrir() {
    _modoCubrir = !_modoCubrir;
    document.body.classList.toggle('modo-cubrir', _modoCubrir);

    const btn   = document.getElementById('btnCubrirPuesto');
    const badge = document.getElementById('badgeEstado');
    if (btn) {
        btn.classList.toggle('activo', _modoCubrir);
        btn.textContent = _modoCubrir ? '✕ Salir del modo cubrir' : '⇄ CUBRIR / CAMBIAR PUESTO';
    }
    if (badge) {
        badge.textContent = _modoCubrir ? '🔓 Modo cubrir activo' : '📅 Activo';
        badge.className   = _modoCubrir
            ? 'hor-semana-estado estado-abierta'
            : 'hor-semana-estado estado-activo';
    }

    renderSlots(_slots);
}

// ── Picker universal: mover cualquier puesto a cualquiera ──────
async function abrirPickerMover(s) {
    _slotParaMover = s;
    const trabajadores = await cargarTrabajadores();

    document.getElementById('moverActualMsg').textContent = s.postulante_id
        ? `Ahora está: ${s.trabajador_nombre || '—'}`
        : 'Este turno está libre.';

    const sel = document.getElementById('moverSelect');
    sel.innerHTML = '<option value="">— Dejar libre —</option>' +
        trabajadores
            .filter(t => parseInt(t.id) !== parseInt(s.postulante_id))
            .map(t => `<option value="${t.id}">${t.nombre}</option>`)
            .join('');
    sel.value = '';

    document.getElementById('moverOverlay').hidden = false;
}

function cerrarPickerMover() {
    document.getElementById('moverOverlay').hidden = true;
    _slotParaMover = null;
}

async function confirmarMover() {
    const s = _slotParaMover;
    const nuevoId = document.getElementById('moverSelect').value;
    cerrarPickerMover();

    try {
        const r   = await fetch(`${BASE}/horario/api/slot/mover`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({
                slot_id: parseInt(s.id_slot),
                nuevo_postulante_id: nuevoId ? parseInt(nuevoId) : null,
            }),
        });
        const res = await r.json();
        await cargarSlots();
        if (res.success) mostrarToast(nuevoId ? '¡Turno reasignado!' : 'Turno liberado.', 'ok');
        else             mostrarToast(res.message || 'No se pudo mover el turno.', 'error');
    } catch {
        mostrarToast('Error de conexión.', 'error');
    }
}

// ── Resumen de disponibles ─────────────────────────────
function actualizarResumen(slots) {
    const el = document.getElementById('horResumen');
    if (!el) return;

    const libres = {};
    slots.forEach(s => {
        if (['ALMACENERA','LIMPIEZA','ABASTECIMIENTO','INVENTARIO','COMPRAS','AUDITORIA'].includes(s.rol_puesto)) return;
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
