/**
 * Funciones para la ficha de detalle unificada[cite: 15]
 */
async function toggleEstado(id, estadoActual) {
    if (!id) return;
    const nuevoEstado = estadoActual == 1 ? 0 : 1;
    
    try {
        const response = await fetch(buildAdminUrl('/admin/actualizar-estado'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id, activo: nuevoEstado })
        });
        const res = await response.json();
        if (res.success) {
            location.reload(); // Recargamos para ver los cambios reflejados
        }
    } catch (e) {
        alert("Error al cambiar estado");
    }
}

async function actualizarEtapa(id) {
    const etapaId = document.getElementById('selectEtapa').value;
    // Aquí puedes crear un nuevo endpoint o reutilizar lógica de contratación[cite: 15]
    console.log(`Actualizando postulante ${id} a etapa ${etapaId}`);
    // Implementar fetch similar a toggleEstado hacia el controlador
}