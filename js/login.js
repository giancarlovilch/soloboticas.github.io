// Constantes para eventos
const container = document.getElementById('container');
const registerBtn = document.getElementById('register');
const loginBtn = document.getElementById('login');

// Activar y desactivar clase en el contenedor
registerBtn.addEventListener('click', () => {
    container.classList.add("active");
});

loginBtn.addEventListener('click', () => {
    container.classList.remove("active");
});

// Animación en el botón "Atrás"
const buttonUI2 = document.getElementById('button-ui-2');
const arrowSVG = document.getElementById('arrow-svg');

// Sacudida y rotación de la flecha al pasar el ratón
function openForgotPasswordModal() {
    const modal = document.getElementById("forgotPasswordModal");
    modal.style.display = "block"; // Mostrar el modal
}

function closeForgotPasswordModal() {
    const modal = document.getElementById("forgotPasswordModal");
    modal.style.display = "none"; // Ocultar el modal
}

setTimeout(() => {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        alert.style.transition = 'opacity 0.5s';
        alert.style.opacity = '0'; // Desvanecer el mensaje
        setTimeout(() => alert.remove(), 500); // Eliminar el mensaje del DOM después del desvanecimiento
    });
}, 4000);
