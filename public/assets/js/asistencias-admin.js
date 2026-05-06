/**
 * Gestión de asistencias — Solo Boticas Admin
 */

const buildAdminUrl = (path) => {
    const base = window.location.pathname.split('/admin/')[0];
    return `${window.location.origin}${base}${path}`;
};

let catalogoLocales   = [];
let catalogoUsuarios  = [];
let editRow           = null;

// ── Init ──────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    cargarAsistencias();
});

// ── Cargar tabla ──────────────────────────────────────
async function cargarAsistencias() {
    const fecha = document.getElementById('filtroFecha')?.value ?? '';
    const pid   = document.getElementById('filtroPostulante')?.value ?? '';

    const tbody = document.getElementById('tbodyAsistencias');
    tbody.innerHTML = '<tr><td colspan="9" class="text-center">Cargando...</td></tr>';

    try {
        const qs  = new URLSearchParams({ fecha, postulante_id: pid });
        const r   = await fetch(buildAdminUrl(`/admin/api/asistencias?${qs}`), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        const res = await r.json();
        if (!res.success) throw new Error(res.message);

        const { registros, usuarios, locales } = res.data;

        // Guardar catálogos
        catalogoLocales  = locales  || [];
        catalogoUsuarios = usuarios || [];

        // Llenar filtro de trabajadores (solo primera vez)
        const filtro = document.getElementById('filtroPostulante');
        if (filtro && filtro.options.length === 1) {
            usuarios.forEach(u => {
                const o = document.createElement('option');
                o.value = u.id; o.textContent = `${u.nombre_completo} (${u.num_documento})`;
                filtro.appendChild(o);
            });
        }

        // Llenar locales en modales
        llenarLocalSelect('editLocal');
        llenarLocalSelect('addLocal');
        llenarUsuariosSelect('addPostulante');

        renderTabla(registros);
    } catch (e) {
        document.getElementById('tbodyAsistencias').innerHTML =
            `<tr><td colspan="9" class="text-center" style="color:red;">Error: ${e.message}</td></tr>`;
    }
}

function renderTabla(registros) {
    const tbody = document.getElementById('tbodyAsistencias');
    if (!registros.length) {
        tbody.innerHTML = '<tr><td colspan="9" class="text-center">No hay registros para los filtros seleccionados.</td></tr>';
        return;
    }
    tbody.innerHTML = registros.map(r => `
        <tr>
            <td>${formatFecha(r.fecha)}</td>
            <td>${r.nombre_completo}</td>
            <td><strong>${r.num_documento}</strong></td>
            <td>${r.local_desc || '—'}</td>
            <td>${r.hora_ingreso ? formatHora(r.hora_ingreso) : '—'}</td>
            <td>${r.hora_salida  ? formatHora(r.hora_salida)  : '—'}</td>
            <td class="text-center">${badgeEstado(r.estado)}</td>
            <td class="text-center">${badgeObs(r.observacion)}</td>
            <td class="text-center">
                <button class="btn-edit" onclick='abrirModalEdit(${JSON.stringify(r)})'>Editar</button>
            </td>
            <td class="text-center">
                <button class="btn-danger" onclick='abrirModalDel(${r.id_asistencia}, "${r.nombre_completo}", "${formatFecha(r.fecha)}")'>Eliminar</button>
            </td>
        </tr>`).join('');
}

// ── Modal EDITAR ──────────────────────────────────────
async function abrirModalEdit(r) {
    editRow = r;
    document.getElementById('editId').value           = r.id_asistencia;
    document.getElementById('editNombre').value       = r.nombre_completo;
    document.getElementById('editFechaLabel').value   = formatFecha(r.fecha);
    document.getElementById('editIngreso').value      = toDatetimeLocal(r.hora_ingreso);
    document.getElementById('editSalida').value       = toDatetimeLocal(r.hora_salida);
    document.getElementById('editEstado').value       = r.estado || 'A TIEMPO';
    document.getElementById('editLocal').value        = r.local_id || '';
    document.getElementById('editJustif').value       = r.justificacion || '';
    document.getElementById('editObs').value          = r.observacion || 'PENDIENTE';
    document.getElementById('editMsg').hidden         = true;

    document.getElementById('editModalTitle').textContent =
        `Editar — ${r.nombre_completo} (${formatFecha(r.fecha)})`;

    // Cargar checklist de esta asistencia
    await cargarChecklistEdit(r.id_asistencia);

    document.getElementById('editModal').hidden = false;
}

async function cargarChecklistEdit(asistenciaId) {
    const wrap = document.getElementById('editChecklistWrap');
    const cont = document.getElementById('editChecklist');
    cont.innerHTML = '<p style="color:#94a3b8;font-size:0.78rem;">Cargando...</p>';
    wrap.hidden = false;

    try {
        const r   = await fetch(buildAdminUrl(`/admin/api/asistencia/checklist?asistencia_id=${asistenciaId}`), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        const res = await r.json();
        const items = res.data || [];

        if (!items.length) {
            cont.innerHTML = '<p style="color:#94a3b8;font-size:0.78rem;">Sin checklist registrado.</p>';
            return;
        }

        cont.innerHTML = items.map(item => {
            const checked    = item.cumplido ? 'checked' : '';
            const tipoBadge  = item.tipo === 'APERTURA'
                ? '<span style="font-size:0.62rem;background:#dbeafe;color:#1e40af;padding:1px 6px;border-radius:10px;">APERTURA</span>'
                : '<span style="font-size:0.62rem;background:#fef9c3;color:#92400e;padding:1px 6px;border-radius:10px;">CIERRE</span>';
            return `
            <div style="display:flex;align-items:center;gap:.6rem;padding:.45rem .6rem;border:1px solid #e2e8f0;border-radius:7px;background:#fafafa;"
                 id="chk-row-${item.id_asistencia_checklist}">
                <input type="checkbox" data-id="${item.id_asistencia_checklist}"
                       class="chk-item" ${checked}
                       style="width:15px;height:15px;accent-color:#0097A7;cursor:pointer;"
                       onchange="guardarChecklistItem(this)">
                <span style="flex:1;font-size:0.82rem;color:#1e293b;">${escHtml(item.descripcion)}</span>
                ${tipoBadge}
                <span style="font-size:0.7rem;font-weight:600;padding:1px 7px;border-radius:10px;
                             background:${item.cumplido ? '#d1fae5' : '#fee2e2'};
                             color:${item.cumplido ? '#065f46' : '#991b1b'};"
                      id="chk-badge-${item.id_asistencia_checklist}">
                    ${item.cumplido ? '✓ Cumple' : '✗ No cumple'}
                </span>
            </div>`;
        }).join('');

    } catch {
        cont.innerHTML = '<p style="color:#dc3545;font-size:0.78rem;">Error al cargar el checklist.</p>';
    }
}

async function guardarChecklistItem(checkbox) {
    const itemId   = parseInt(checkbox.dataset.id);
    const cumplido = checkbox.checked ? 1 : 0;
    const badge    = document.getElementById(`chk-badge-${itemId}`);

    // Feedback visual inmediato
    if (badge) {
        badge.style.background = cumplido ? '#d1fae5' : '#fee2e2';
        badge.style.color      = cumplido ? '#065f46' : '#991b1b';
        badge.textContent      = cumplido ? '✓ Cumple' : '✗ No cumple';
    }

    try {
        await fetch(buildAdminUrl('/admin/asistencia/checklist/actualizar'), {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body:    JSON.stringify({ id_asistencia_checklist: itemId, cumplido }),
        });
    } catch {
        // Revertir si falla
        checkbox.checked = !checkbox.checked;
    }
}

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function cerrarModalEdit() {
    document.getElementById('editModal').hidden = true;
}

async function guardarEdit() {
    const id = parseInt(document.getElementById('editId').value);
    const data = {
        id_asistencia: id,
        hora_ingreso:  document.getElementById('editIngreso').value || null,
        hora_salida:   document.getElementById('editSalida').value  || null,
        estado:        document.getElementById('editEstado').value,
        local_id:      document.getElementById('editLocal').value   || null,
        justificacion: document.getElementById('editJustif').value  || null,
        observacion:   document.getElementById('editObs').value,
    };

    try {
        const r   = await fetch(buildAdminUrl('/admin/asistencia/actualizar'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify(data),
        });
        const res = await r.json();

        if (res.success) {
            cerrarModalEdit();
            await cargarAsistencias();
        } else {
            showMsg('editMsg', res.message || 'Error al guardar.', 'error');
        }
    } catch {
        showMsg('editMsg', 'Error de conexión.', 'error');
    }
}

// ── Modal AGREGAR ─────────────────────────────────────
function abrirModalAdd() {
    document.getElementById('addFecha').value      = new Date().toISOString().split('T')[0];
    document.getElementById('addPostulante').value = '';
    document.getElementById('addIngreso').value    = '';
    document.getElementById('addSalida').value     = '';
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
        hora_ingreso:  document.getElementById('addIngreso').value || null,
        hora_salida:   document.getElementById('addSalida').value  || null,
        estado:        document.getElementById('addEstado').value,
        local_id:      document.getElementById('addLocal').value   || null,
        justificacion: document.getElementById('addJustif').value  || null,
        observacion:   'PENDIENTE',
    };

    try {
        const r   = await fetch(buildAdminUrl('/admin/asistencia/crear'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify(data),
        });
        const res = await r.json();

        if (res.success) {
            cerrarModalAdd();
            await cargarAsistencias();
        } else {
            showMsg('addMsg', res.message || 'Error al registrar.', 'error');
        }
    } catch {
        showMsg('addMsg', 'Error de conexión.', 'error');
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
        o.value = u.id; o.textContent = `${u.nombre_completo} (${u.num_documento})`;
        sel.appendChild(o);
    });
    sel.dataset.loaded = '1';
}

function showMsg(elId, msg, type) {
    const el = document.getElementById(elId);
    if (!el) return;
    el.textContent  = msg;
    el.className    = `asist-msg asist-msg--${type}`;
    el.hidden       = false;
}

function formatFecha(f) {
    if (!f) return '';
    const [y, m, d] = f.split('-');
    return `${d}/${m}/${y}`;
}

function formatHora(dt) {
    if (!dt) return '—';
    const d = new Date(dt.replace(' ', 'T'));
    return d.toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit' });
}

function toDatetimeLocal(dt) {
    if (!dt) return '';
    // "2026-05-04 08:45:00" → "2026-05-04T08:45"
    return dt.replace(' ', 'T').substring(0, 16);
}

function badgeEstado(e) {
    const map = {
        'A TIEMPO': ['badge-contratado', 'A tiempo'],
        'TARDE':    ['badge-entrevista', 'Tarde'],
        'FALTA':    ['badge-rechazado',  'Falta'],
        'EXTRA':    ['badge-extra',      'Extra'],
        'TEMPRANO': ['badge-temprano',   'Temprano'],
    };
    const [cls, label] = map[e] || ['badge-rechazado', e];
    return `<span class="badge ${cls}">${label}</span>`;
}

function badgeObs(o) {
    if (o === 'PROCEDE')    return '<span class="badge badge-contratado">Procede</span>';
    if (o === 'NO PROCEDE') return '<span class="badge badge-rechazado">No procede</span>';
    return '<span class="badge badge-pendiente">Pendiente</span>';
}

// ── Modal ELIMINAR ────────────────────────────────────
let _delId = null;

function abrirModalDel(id, nombre, fecha) {
    _delId = id;
    document.getElementById('delDesc').textContent =
        `Se eliminará el registro de "${nombre}" del ${fecha}. Esta acción no se puede deshacer.`;
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
    if (!password) {
        showMsg('delMsg', 'Ingresa tu contraseña.', 'error'); return;
    }

    try {
        const r   = await fetch(buildAdminUrl('/admin/asistencia/eliminar'), {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body:    JSON.stringify({ id_asistencia: _delId, password }),
        });
        const res = await r.json();

        if (res.success) {
            cerrarModalDel();
            await cargarAsistencias();
        } else {
            showMsg('delMsg', res.message || 'No se pudo eliminar.', 'error');
        }
    } catch {
        showMsg('delMsg', 'Error de conexión.', 'error');
    }
}
