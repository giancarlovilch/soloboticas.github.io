document.addEventListener('DOMContentLoaded', async () => {
    const container = document.querySelector('.update-container');
    const postulanteId = container.dataset.id;

    if (postulanteId) {
        await cargarDatosPostulante(postulanteId);
    }
});

async function cargarDatosPostulante(id) {
    try {
        // Usamos el endpoint de búsqueda o uno específico por ID
        const response = await fetch(buildUrl(`/admin/buscar-postulante?id=${id}`));
        const result = await response.json();

        if (result.success) {
            const p = result.data[0]; // Asumiendo que devuelve un array
            document.getElementById('nombre_completo').value = p.nombre_completo;
            document.getElementById('num_documento').value = p.num_documento;
            document.getElementById('email').value = p.email;
            document.getElementById('telefono').value = p.telefono;
            document.getElementById('etapa_id').value = p.etapa_id;
            document.getElementById('activo').value = p.activo ? "1" : "0";
            
            document.getElementById('etapaBanner').textContent = `Estado Actual: ${p.etapa_nombre}`;
        }
    } catch (error) {
        console.error("Error al cargar datos", error);
    }
}