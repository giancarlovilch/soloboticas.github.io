/**
 * Gestión de fichas de asistencia — Solo Boticas Admin
 */

const buildAdminUrl = (path) => {
    const base = window.location.pathname.split('/admin/')[0];
    return `${window.location.origin}${base}${path}`;
};

let catalogoLocales  = [];
let catalogoUsuarios = [];
let _rbVals          = {};
let _soloSinCalif    = false;

// ── Init ──────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    cargarAsistencias();
});

// ── Toggle "Sin calificar" ────────────────────────────
function toggleSinCalif() {
    _soloSinCalif = !_soloSinCalif;
    const btn = document.getElementById('btnSinCalif');
    if (btn) {
        btn.style.background   = _soloSinCalif ? '#0097A7' : '';
        btn.style.color        = _soloSinCalif ? '#fff' : '';
        btn.style.borderColor  = _soloSinCalif ? '#0097A7' : '';
    }
    cargarAsistencias();
}

// ── Cargar tabla ──────────────────────────────────────
async function cargarAsistencias() {
    const desde = document.getElementById('filtroDesde')?.value ?? '';
    const hasta = document.getElementById('filtroHasta')?.value ?? '';
    const pid   = document.getElementById('filtroPostulante')?.value ?? '';
    const tbody = document.getElementById('tbodyAsistencias');
    tbody.innerHTML = '<tr><td colspan="9" class="text-center">Cargando...</td></tr>';

    try {
        const qs = new URLSearchParams({
            desde, hasta,
            postulante_id: pid,
            sin_calificar: _soloSinCalif ? '1' : '',
        });
        const r   = await fetch(buildAdminUrl(`/admin/api/asistencias?${qs}`), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        const res = await r.json();
        if (!res.success) throw new Error(res.message);

        const { registros, usuarios, locales } = res.data;
        catalogoLocales  = locales  || [];
        catalogoUsuarios = usuarios || [];

        const filtro = document.getElementById('filtroPostulante');
        if (filtro && filtro.options.length === 1) {
            usuarios.forEach(u => {
                const o = document.createElement('option');
                o.value = u.id;
                o.textContent = `${u.nombre_completo} (${u.num_documento})`;
                filtro.appendChild(o);
            });
        }

        llenarLocalSelect('editLocal');
        llenarLocalSelect('addLocal');
        llenarUsuariosSelect('addPostulante');

        const sinFicha = registros.filter(r => !r.id_asistencia).length;
        const info = document.getElementById('contadorInfo');
        if (info) {
            info.textContent = `${registros.length} turno${registros.length !== 1 ? 's' : ''} · ${sinFicha} sin ficha`;
        }

        renderTabla(registros);
    } catch (e) {
        document.getElementById('tbodyAsistencias').innerHTML =
            `<tr><td colspan="9" class="text-center" style="color:red;">Error: ${e.message}</td></tr>`;
    }
}

// ── Render tabla ──────────────────────────────────────
const PUNT_LABEL = {
    'MUY_TEMPRANO': ['+10 min antes', '#eff6ff', '#1e40af'],
    'TEMPRANO':     ['Temprano',      '#f0fdfe', '#0e7490'],
    'TARDE':        ['Tarde',         '#fef3c7', '#92400e'],
    'MUY_TARDE':    ['+10 min tarde', '#fee2e2', '#991b1b'],
};
const TURNO_LABEL = { '1': '☀️ Mañana', '2': '🌙 Tarde' };

function puntBadge(val) {
    if (!val || !PUNT_LABEL[val]) return '<span style="color:#cbd5e1">—</span>';
    const [label, bg, color] = PUNT_LABEL[val];
    return `<span style="font-size:.68rem;font-weight:700;padding:2px 7px;border-radius:12px;background:${bg};color:${color};">${label}</span>`;
}

function fichaEstadoBadge(r) {
    if (!r.id_asistencia) {
        return '<span style="font-size:.68rem;font-weight:700;padding:2px 7px;border-radius:12px;background:#f1f5f9;color:#64748b;">Sin ficha</span>';
    }
    if (r.estado === 'FALTA') {
        return '<span style="font-size:.68rem;font-weight:700;padding:2px 7px;border-radius:12px;background:#fee2e2;color:#991b1b;">Falta</span>';
    }
    const tieneEntrada = !!r.llegada_puntualidad;
    const tieneSalida  = !!r.salida_puntualidad;
    if (tieneEntrada && tieneSalida) {
        return '<span style="font-size:.68rem;font-weight:700;padding:2px 7px;border-radius:12px;background:#d1fae5;color:#065f46;">Completo</span>';
    }
    if (tieneEntrada || tieneSalida) {
        return '<span style="font-size:.68rem;font-weight:700;padding:2px 7px;border-radius:12px;background:#fef9c3;color:#92400e;">Parcial</span>';
    }
    return '<span style="font-size:.68rem;font-weight:700;padding:2px 7px;border-radius:12px;background:#ede9fe;color:#5b21b6;">Solo estado</span>';
}

function renderTabla(registros) {
    const tbody = document.getElementById('tbodyAsistencias');
    if (!registros.length) {
        tbody.innerHTML = '<tr><td colspan="9" class="text-center">No hay turnos para los filtros seleccionados.</td></tr>';
        return;
    }
    tbody.innerHTML = registros.map(r => {
        const rowBg = !r.id_asistencia ? '#fafafa' : (r.estado === 'FALTA' ? '#fff5f5' : '');
        const accionBtn = `<button class="btn-edit" style="font-size:.72rem;"
            onclick='abrirModalEdit(${JSON.stringify(r).replace(/'/g, "\\'")})'>${r.id_asistencia ? 'Editar' : 'Calificar'}</button>`;
        const elimBtn = r.id_asistencia
            ? `<button class="btn-danger" onclick='abrirModalDel(${r.id_asistencia}, "${escHtml(r.trabajador_nombre)}", "${formatFecha(r.fecha_dia)}")'>Eliminar</button>`
            : `<span style="color:#cbd5e1;font-size:.75rem;">—</span>`;

        return `<tr style="background:${rowBg}">
            <td>${formatFecha(r.fecha_dia)}</td>
            <td>${escHtml(r.trabajador_nombre)}</td>
            <td style="font-size:.75rem;">
                ${TURNO_LABEL[String(r.turno_id)] || '—'}
                <span style="display:block;color:#94a3b8;">${escHtml(r.local_desc)} · ${escHtml(r.rol_desc)}</span>
            </td>
            <td class="text-center">${fichaEstadoBadge(r)}</td>
            <td class="text-center">${puntBadge(r.llegada_puntualidad)}</td>
            <td class="text-center">${puntBadge(r.salida_puntualidad)}</td>
            <td style="font-size:.75rem;color:#0097A7;">${escHtml(r.registrado_por_nombre || '—')}</td>
            <td class="text-center">${accionBtn}</td>
            <td class="text-center">${elimBtn}</td>
        </tr>`;
    }).join('');
}

// ── Radio buttons ─────────────────────────────────────
function pickRb(btn) {
    const field = btn.dataset.field;
    document.querySelectorAll(`#editModal .mh-rb[data-field="${field}"]`).forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    _rbVals[field] = btn.dataset.val;
}

function preselectRb(field, val) {
    if (val === null || val === undefined) return;
    const sVal = String(val);
    document.querySelectorAll(`#editModal .mh-rb[data-field="${field}"]`).forEach(btn => {
        btn.classList.toggle('active', btn.dataset.val === sVal);
    });
    _rbVals[field] = sVal;
}

function clearRbs() {
    document.querySelectorAll('#editModal .mh-rb').forEach(b => b.classList.remove('active'));
    _rbVals = {};
}

// ── Modal EDITAR / CALIFICAR ──────────────────────────
function abrirModalEdit(r) {
    clearRbs();

    // Campos del slot (para crear si no existe ficha)
    document.getElementById('editId').value         = r.id_asistencia || 0;
    document.getElementById('editSlotPid').value    = r.postulante_id;
    document.getElementById('editSlotFecha').value  = r.fecha_dia;
    document.getElementById('editSlotTurno').value  = r.turno_id;

    document.getElementById('editNombre').value      = r.trabajador_nombre;
    const turnoStr = TURNO_LABEL[String(r.turno_id)] || '';
    document.getElementById('editFechaLabel').value  = `${formatFecha(r.fecha_dia)}${turnoStr ? ' · ' + turnoStr : ''} · ${r.local_desc}`;
    document.getElementById('editEstado').value      = r.estado || 'FALTA';
    document.getElementById('editLocal').value       = r.local_id || '';
    document.getElementById('editJustif').value      = r.justificacion || '';
    document.getElementById('editObs').value         = r.observacion || 'PENDIENTE';
    document.getElementById('editComentarios').value = r.comentarios_ficha || '';
    document.getElementById('editMsg').hidden        = true;

    document.getElementById('editModalTitle').textContent = r.id_asistencia
        ? `Editar ficha — ${r.trabajador_nombre}`
        : `Calificar turno — ${r.trabajador_nombre}`;

    preselectRb('llegada_puntualidad',        r.llegada_puntualidad);
    preselectRb('area_ordenada_ingreso',      r.area_ordenada_ingreso);
    preselectRb('area_limpia_ingreso',        r.area_limpia_ingreso);
    preselectRb('aseo_personal',              r.aseo_personal);
    preselectRb('vestimenta',                 r.vestimenta);
    preselectRb('unas',                       r.unas);
    preselectRb('cabello',                    r.cabello);
    preselectRb('salida_puntualidad',         r.salida_puntualidad);
    preselectRb('estado_area_cierre',         r.estado_area_cierre);
    preselectRb('limpieza_area_cierre',       r.limpieza_area_cierre);
    preselectRb('area_ordenada_cierre',       r.area_ordenada_cierre);
    preselectRb('participo_apertura_cierre',  r.participo_apertura_cierre);
    preselectRb('uso_celular',                r.uso_celular);
    preselectRb('calificacion_turno',         r.calificacion_turno);

    document.getElementById('editModal').hidden = false;
}

function cerrarModalEdit() {
    document.getElementById('editModal').hidden = true;
}

async function guardarEdit() {
    const id = parseInt(document.getElementById('editId').value) || 0;
    const yn = (k) => _rbVals[k] !== undefined ? parseInt(_rbVals[k]) : null;
    const sv = (k) => _rbVals[k] || null;

    const data = {
        id_asistencia:        id,
        // Contexto del slot (para upsert si id=0)
        postulante_id:        parseInt(document.getElementById('editSlotPid').value)   || null,
        fecha:                document.getElementById('editSlotFecha').value,
        turno_id:             parseInt(document.getElementById('editSlotTurno').value) || null,
        // Datos de la ficha
        estado:               document.getElementById('editEstado').value,
        local_id:             document.getElementById('editLocal').value || null,
        justificacion:        document.getElementById('editJustif').value || null,
        observacion:          document.getElementById('editObs').value,
        comentarios_ficha:    document.getElementById('editComentarios').value || null,
        llegada_puntualidad:       sv('llegada_puntualidad'),
        area_ordenada_ingreso:     yn('area_ordenada_ingreso'),
        area_limpia_ingreso:       yn('area_limpia_ingreso'),
        aseo_personal:             sv('aseo_personal'),
        vestimenta:                sv('vestimenta'),
        unas:                      sv('unas'),
        cabello:                   sv('cabello'),
        salida_puntualidad:        sv('salida_puntualidad'),
        estado_area_cierre:        sv('estado_area_cierre'),
        limpieza_area_cierre:      yn('limpieza_area_cierre'),
        area_ordenada_cierre:      yn('area_ordenada_cierre'),
        participo_apertura_cierre: yn('participo_apertura_cierre'),
        uso_celular:               sv('uso_celular'),
        calificacion_turno:        sv('calificacion_turno'),
    };

    try {
        const r   = await fetch(buildAdminUrl('/admin/asistencia/actualizar'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify(data),
        });
        const res = await r.json();
        if (res.success) { cerrarModalEdit(); await cargarAsistencias(); }
        else showMsg('editMsg', res.message || 'Error al guardar.', 'error');
    } catch {
        showMsg('editMsg', 'Error de conexión.', 'error');
    }
}

// ── Modal AGREGAR ─────────────────────────────────────
function abrirModalAdd() {
    document.getElementById('addFecha').value      = new Date().toISOString().split('T')[0];
    document.getElementById('addPostulante').value = '';
    document.getElementById('addTurno').value      = '1';
    document.getElementById('addEstado').value     = 'FALTA';
    document.getElementById('addLocal').value      = '';
    document.getElementById('addJustif').value     = '';
    document.getElementById('addMsg').hidden       = true;
    document.getElementById('addModal').hidden     = false;
}

function cerrarModalAdd() {
    document.getElementById('addModal').hidden = true;
}

async function guardarAdd() {
    const pid = document.getElementById('addPostulante').value;
    const fec = document.getElementById('addFecha').value;
    if (!pid || !fec) { showMsg('addMsg', 'Trabajador y fecha son requeridos.', 'error'); return; }

    const data = {
        postulante_id: parseInt(pid),
        fecha:         fec,
        turno_id:      parseInt(document.getElementById('addTurno').value),
        estado:        document.getElementById('addEstado').value,
        local_id:      document.getElementById('addLocal').value || null,
        justificacion: document.getElementById('addJustif').value || null,
        observacion:   'PENDIENTE',
    };

    try {
        const r   = await fetch(buildAdminUrl('/admin/asistencia/crear'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify(data),
        });
        const res = await r.json();
        if (res.success) { cerrarModalAdd(); await cargarAsistencias(); }
        else showMsg('addMsg', res.message || 'Error al registrar.', 'error');
    } catch {
        showMsg('addMsg', 'Error de conexión.', 'error');
    }
}

// ── Modal ELIMINAR ────────────────────────────────────
let _delId = null;

function abrirModalDel(id, nombre, fecha) {
    _delId = id;
    document.getElementById('delDesc').textContent =
        `Se eliminará la ficha de "${nombre}" del ${fecha}. No se puede deshacer.`;
    document.getElementById('delPassword').value = '';
    document.getElementById('delMsg').hidden     = true;
    document.getElementById('delModal').removeAttribute('hidden');
    setTimeout(() => document.getElementById('delPassword').focus(), 50);
}

function cerrarModalDel() {
    document.getElementById('delModal').setAttribute('hidden', '');
    _delId = null;
}

async function confirmarEliminar() {
    const password = document.getElementById('delPassword').value.trim();
    if (!password) { showMsg('delMsg', 'Ingresa tu contraseña.', 'error'); return; }

    try {
        const r   = await fetch(buildAdminUrl('/admin/asistencia/eliminar'), {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body:    JSON.stringify({ id_asistencia: _delId, password }),
        });
        const res = await r.json();
        if (res.success) { cerrarModalDel(); await cargarAsistencias(); }
        else showMsg('delMsg', res.message || 'No se pudo eliminar.', 'error');
    } catch {
        showMsg('delMsg', 'Error de conexión.', 'error');
    }
}

// ── Helpers ────────────────────────────────────────────
function llenarLocalSelect(selectId) {
    const sel = document.getElementById(selectId);
    if (!sel || sel.dataset.loaded) return;
    catalogoLocales.forEach(({ id, descripcion }) => {
        const o = document.createElement('option');
        o.value = id; o.textContent = descripcion;
        sel.appendChild(o);
    });
    sel.dataset.loaded = '1';
}

function llenarUsuariosSelect(selectId) {
    const sel = document.getElementById(selectId);
    if (!sel || sel.dataset.loaded) return;
    catalogoUsuarios.forEach(u => {
        const o = document.createElement('option');
        o.value = u.id;
        o.textContent = `${u.nombre_completo} (${u.num_documento})`;
        sel.appendChild(o);
    });
    sel.dataset.loaded = '1';
}

function showMsg(elId, msg, type) {
    const el = document.getElementById(elId);
    if (!el) return;
    el.textContent = msg;
    el.className   = `asist-msg asist-msg--${type}`;
    el.hidden      = false;
}

function formatFecha(f) {
    if (!f) return '';
    const [y, m, d] = f.split('-');
    return `${d}/${m}/${y}`;
}

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function badgeEstado(e) {
    const map = {
        'A TIEMPO': ['badge-contratado', 'A tiempo'],
        'TARDE':    ['badge-entrevista', 'Tarde'],
        'FALTA':    ['badge-rechazado',  'Falta'],
        'EXTRA':    ['badge-extra',      'Extra'],
        'TEMPRANO': ['badge-temprano',   'Temprano'],
    };
    const [cls, label] = map[e] || ['badge-rechazado', e || '—'];
    return `<span class="badge ${cls}">${label}</span>`;
}
