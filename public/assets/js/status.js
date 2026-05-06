/**
 * Gestión de Estados - Solo Boticas
 * Archivo: assets/js/status.js
 */

const buildAdminUrl = (path) => {
    const base = window.location.pathname.split('/admin/')[0];
    return `${window.location.origin}${base}${path}`;
};

async function cargarUsuariosStatus() {
    const tbody = document.getElementById('tbodyStatus');
    if (!tbody) return;

    const url = buildAdminUrl('/admin/postulantes'); // Reutilizamos el fetch de postulantes que ya trae 'activo' y 'username'

    try {
        const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const result = await response.json();

        if (!result.success) throw new Error(result.message);

        tbody.innerHTML = '';
        result.data.forEach(u => {
            const tr = document.createElement('tr');
            // Usamos los mismos badges que en postulantes para consistencia visual
            tr.innerHTML = `
                <td><strong>${u.num_documento}</strong></td>
                <td>${u.nombre_completo}</td>
                <td><code>${u.username || 'Sin cuenta'}</code></td>
                <td class="text-center">
                    <span class="badge ${u.activo == 1 ? 'badge-entrevista' : 'badge-rechazado'}">
                        ${u.activo == 1 ? 'CUENTA ACTIVA' : 'CUENTA INACTIVA'}
                    </span>
                </td>
                <td class="text-center">
                    <button class="btn-edit" onclick="toggleEstado(${u.id}, ${u.activo})">
                        ${u.activo == 1 ? 'BLOQUEAR ACCESO' : 'ACTIVAR ACCESO'}
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });

    } catch (error) {
        tbody.innerHTML = `<tr><td colspan="5" class="text-center" style="color:red;">Error: ${error.message}</td></tr>`;
    }
}

async function toggleEstado(id, estadoActual) {
    const nuevoEstado = estadoActual == 1 ? 0 : 1;
    const url = buildAdminUrl('/admin/usuario/estado');

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id, activo: nuevoEstado })
        });
        
        const res = await response.json();
        if (res.success) {
            cargarUsuariosStatus(); // Recarga solo la tabla para reflejar el cambio
        } else {
            alert("No se pudo actualizar: " + res.message);
        }
    } catch (e) {
        alert("Error de comunicación con el servidor.");
    }
}

function filtrarStatus() {
    const input = document.getElementById('statusSearch');
    const filter = input.value.toLowerCase();
    const rows = document.querySelectorAll('#tbodyStatus tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
}