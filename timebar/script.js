const personas = [
    { id: 'persona1', horarios: [8, 13, 19] },  // Horarios de comidas para Persona 1
    // Añadir más personas y sus horarios aquí
];

const horas = 24; // Total de horas en un día

document.addEventListener('DOMContentLoaded', () => {
    personas.forEach(persona => {
        const personaElement = document.getElementById(persona.id);
        persona.horarios.forEach(horario => {
            const progresoElement = personaElement.querySelector('.progreso');
            const width = (100 / horas) * horario;
            progresoElement.style.width = `${width}%`;
        });
    });
});
