/**
 * Gestión de Postulantes - Solo Boticas
 * Archivo: assets/js/postulantes.js[cite: 10]
 */



const buildAdminUrl = (path) => {
    const base = window.location.pathname.split('/admin/')[0];
    return `${window.location.origin}${base}${path}`;
};

// --- FUNCIONES DE LISTADO ---
async function cargarPostulantes() {
    const tbody = document.getElementById('tbodyPostulantes');
    if (!tbody) return;
    const url = buildAdminUrl('/admin/postulantes');
    try {
        const response = await fetch(url, {
            method: 'GET',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const result = await response.json();
        if (!result.success) throw new Error(result.message);
        tbody.innerHTML = '';
        result.data.forEach(p => {
            const estadoHtml = p.activo == 1
                ? '<span class="badge badge-contratado">Activo</span>'
                : '<span class="badge badge-rechazado">Inactivo</span>';
            const nombreSafe = (p.nombre_completo || '').replace(/'/g, "\\'");
            const fotoHtml = p.foto_url
                ? `<img src="${p.foto_url}" alt="Foto" style="width:36px;height:36px;border-radius:50%;object-fit:cover;">`
                : `<span style="display:inline-block;width:36px;height:36px;border-radius:50%;background:#e5e7eb;"></span>`;
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${fotoHtml}</td>
                <td><strong>${p.num_documento}</strong></td>
                <td>${p.nombre_completo}</td>
                <td>${p.puesto_nombre || '<span style="color:#9ca3af">—</span>'}</td>
                <td><span class="badge ${getBadgeClass(p.etapa_nombre || '')}">${p.etapa_nombre || 'PENDIENTE'}</span></td>
                <td class="text-center">${estadoHtml}</td>
                <td>${p.fecha_creacion || '—'}</td>
                <td class="text-center" style="display:flex;gap:4px;justify-content:center;">
                    <button class="btn-edit" onclick="window.location.href='?page=update&id=${p.id}'">Ver/Editar</button>
                    <button class="btn-danger" onclick="abrirModalEliminar(${p.id},'${nombreSafe}')">Eliminar</button>
                </td>`;
            tbody.appendChild(tr);
        });
    } catch (error) {
        console.error("Error en carga:", error);
    }
}

// --- LÓGICA DE ACTUALIZACIÓN INTEGRAL ---

/**
 * Captura el formulario extendido y lo envía como JSON al controlador[cite: 10]
 */
// --- LÓGICA DE ACTUALIZACIÓN INTEGRAL ---
async function manejarUpdatePostulante(e) {
    e.preventDefault();
    console.log("Iniciando actualización integral..."); // Debug

    const formData = new FormData(e.target);
    const data = {
        id_postulante: formData.get('id_postulante'),
        nombres: formData.get('nombres'),
        apellidos: formData.get('apellidos'),
        genero_id: formData.get('genero_id'),
        fecha_nacimiento: formData.get('fecha_nacimiento'),
        email: formData.get('email'),
        telefono: formData.get('telefono'),
        situacion_vivienda_id: formData.get('situacion_vivienda_id'),
        distrito: formData.get('distrito'),
        direccion: formData.get('direccion'),
        etapa_id:      formData.get('etapa_id'),
        activo:        formData.get('activo'),
        rol_id:        formData.get('rol_id'),
        tipo_personal: formData.get('tipo_personal'),
        fecha_ingreso: formData.get('fecha_ingreso') || null,
        puestos: [],      // <-- NUEVA PROPIEDAD PARA MULTIFUNCIONALIDAD
        experiencias: [],
        estudios: [],
        skills: [],
        turnos: formData.getAll('turnos[]')
    };

    // 0. Recolectar Puestos Asignados (Multifuncional)[cite: 18, 19]
    const puestoSet = new Set();
    document.querySelectorAll('#contenedor-puestos .fila-dinamica').forEach(fila => {
        const selPuesto = fila.querySelector('select[name*="[id_puesto]"]');
        if (selPuesto && selPuesto.value && !puestoSet.has(selPuesto.value)) {
            data.puestos.push({ puesto_id: selPuesto.value });
            puestoSet.add(selPuesto.value);
        }
    });

    // 1. Recolectar Skills con validación de existencia[cite: 18]
    const skillSet = new Set();
    data.skills = []; // Asegurar que el array esté limpio antes de llenar

    document.querySelectorAll('#contenedor-skills .fila-dinamica').forEach(fila => {
        // Buscamos los selectores específicos por el atributo 'name'[cite: 18]
        const selSkill = fila.querySelector('select[name*="[skill_id]"]');
        const selNivel = fila.querySelector('select[name*="[nivel_id]"]');

        if (selSkill && selSkill.value && !skillSet.has(selSkill.value)) {
            data.skills.push({
                skill_id: selSkill.value,
                nivel_id: selNivel.value
            });
            skillSet.add(selSkill.value);
        }
    });

    // 2. Recolectar Experiencias
    document.querySelectorAll('#contenedor-experiencias .fila-dinamica').forEach(fila => {
        const emp = fila.querySelector('input[name*="[empresa]"]');
        if (emp && emp.value) {
            data.experiencias.push({
                empresa: emp.value,
                cargo: fila.querySelector('input[name*="[cargo]"]')?.value || '',
                fecha_inicio: fila.querySelector('input[name*="[fecha_inicio]"]')?.value || '',
                fecha_fin: fila.querySelector('input[name*="[fecha_fin]"]')?.value || null
            });
        }
    });

    // 3. Recolectar Estudios[cite: 13, 16]
    document.querySelectorAll('#contenedor-estudios .fila-dinamica').forEach(fila => {
        const inst = fila.querySelector('select[name*="[institucion_id]"]');
        if (inst && inst.value) {
            data.estudios.push({
                institucion_id: inst.value,
                tipo_id: fila.querySelector('select[name*="[tipo_id]"]')?.value || '',
                estado_id: fila.querySelector('select[name*="[estado_id]"]')?.value || '',
                fecha_inicio: fila.querySelector('input[name*="[fecha_inicio]"]')?.value || null,
                fecha_fin: fila.querySelector('input[name*="[fecha_fin]"]')?.value || null
            });
        }
    });

    try {
        const response = await fetch(buildAdminUrl('/admin/postulante/actualizar'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify(data)
        });

        const result = await response.json();
        if (result.success) {
            alert("✅ " + result.message);
            window.location.href = '?page=postulantes';
        } else {
            alert("❌ Error: " + result.message);
        }
    } catch (error) {
        console.error("Error al actualizar:", error);
        alert("Ocurrió un problema al conectar con el servidor.");
    }
}

function getBadgeClass(etapa) {
    const e = etapa.toLowerCase();
    if (e.includes('pendiente')) return 'badge-pendiente';
    if (e.includes('entrevista')) return 'badge-entrevista';
    if (e.includes('contratado')) return 'badge-contratado';
    return 'badge-rechazado';
}

document.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    const page = params.get('page');
    if (page === 'postulantes') cargarPostulantes();

    const form = document.getElementById('formUpdateCompleto');
    if (form) form.addEventListener('submit', manejarUpdatePostulante);

    // Cerrar modal con Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') cerrarModalEliminar();
    });
});

// ── Modal eliminar postulante ──────────────────────────
let _deleteId = null;

function abrirModalEliminar(id, nombre) {
    _deleteId = id;
    document.getElementById('deleteNombre').textContent = nombre;
    document.getElementById('deletePassword').value    = '';
    document.getElementById('deleteError').hidden      = true;
    document.getElementById('deleteModal').hidden      = false;
    setTimeout(() => document.getElementById('deletePassword').focus(), 50);
}

function cerrarModalEliminar() {
    document.getElementById('deleteModal').hidden = true;
    _deleteId = null;
}

async function confirmarEliminar() {
    const password = document.getElementById('deletePassword').value.trim();
    const errEl    = document.getElementById('deleteError');
    const btn      = document.getElementById('deleteConfirmBtn');

    if (!password) { showDeleteError('Ingresa tu contraseña.'); return; }

    btn.disabled     = true;
    btn.textContent  = 'Verificando...';
    errEl.hidden     = true;

    try {
        const base = window.location.pathname.split('/admin/')[0];
        const r    = await fetch(`${base}/admin/postulante/eliminar`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body:    JSON.stringify({ postulante_id: _deleteId, password }),
        });
        const res = await r.json();

        if (res.success) {
            cerrarModalEliminar();
            cargarPostulantes();
        } else {
            showDeleteError(res.message || 'Error al eliminar.');
        }
    } catch {
        showDeleteError('Error de conexión.');
    } finally {
        btn.disabled    = false;
        btn.textContent = 'Eliminar';
    }
}

function showDeleteError(msg) {
    const el = document.getElementById('deleteError');
    el.textContent = msg;
    el.hidden = false;
}