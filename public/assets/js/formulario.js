document.addEventListener('DOMContentLoaded', () => {
    const statusBox = document.getElementById('statusBox');
    const form = document.getElementById('postulacionForm');

    if (form) {
        const fields = form.querySelectorAll('input, select');

        fields.forEach(field => {
            field.addEventListener('focus', () => {
                field.closest('.input-group')?.classList.add('input-focus');
            });

            field.addEventListener('blur', () => {
                field.closest('.input-group')?.classList.remove('input-focus');
            });
        });
    }

    if (statusBox) {
        const observer = new MutationObserver(() => {
            const text = statusBox.textContent.trim().toLowerCase();

            statusBox.classList.remove('is-error', 'is-success');

            if (!text) return;

            if (
                text.includes('error') ||
                text.includes('invál') ||
                text.includes('no se pudo') ||
                text.includes('no puedes') ||
                text.includes('no se recibió')
            ) {
                statusBox.classList.add('is-error');
            } else {
                statusBox.classList.add('is-success');
            }
        });

        observer.observe(statusBox, {
            childList: true,
            subtree: true,
            characterData: true
        });
    }
});

document.addEventListener('DOMContentLoaded', () => {

    flatpickr("#fecha_nacimiento", {
        locale: "es",
        dateFormat: "d/m/Y",
        altInput: true,
        altFormat: "d/m/Y",
        maxDate: "today"
    });

    flatpickr("#fecha_inicio", {
        locale: "es",
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "d/m/Y"
    });

    flatpickr("#fecha_fin", {
        locale: "es",
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "d/m/Y"
    });

});