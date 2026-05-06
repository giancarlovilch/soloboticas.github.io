document.addEventListener('DOMContentLoaded', () => {
    const dniInput = document.getElementById('num_documento');
    const birthDateInput = document.getElementById('fecha_nacimiento');
    const birthDateContainer = document.getElementById('birthDateContainer');
    const messageBox = document.getElementById('messageBox');

    if (dniInput) {
        dniInput.addEventListener('input', () => {
            dniInput.value = dniInput.value.replace(/\D/g, '').slice(0, 8);
        });

        dniInput.addEventListener('focus', () => {
            dniInput.closest('.input-group')?.classList.add('input-focus');
        });

        dniInput.addEventListener('blur', () => {
            dniInput.closest('.input-group')?.classList.remove('input-focus');
        });
    }

    if (birthDateInput) {
        birthDateInput.addEventListener('focus', () => {
            birthDateInput.closest('.input-group')?.classList.add('input-focus');
        });

        birthDateInput.addEventListener('blur', () => {
            birthDateInput.closest('.input-group')?.classList.remove('input-focus');
        });
    }

    const observer = new MutationObserver(() => {
        if (!birthDateContainer) return;

        const inlineDisplay = birthDateContainer.style.display;

        if (inlineDisplay === 'block') {
            birthDateContainer.classList.remove('input-group-hidden');
            birthDateContainer.classList.add('input-group-visible');
            birthDateContainer.setAttribute('aria-hidden', 'false');
        }

        if (messageBox) {
            const text = messageBox.textContent.trim().toLowerCase();

            messageBox.classList.remove('is-error', 'is-success');

            if (!text) return;

            if (
                text.includes('error') ||
                text.includes('invál') ||
                text.includes('no coincide') ||
                text.includes('ocurrió')
            ) {
                messageBox.classList.add('is-error');
            } else {
                messageBox.classList.add('is-success');
            }
        }
    });

    if (birthDateContainer || messageBox) {
        observer.observe(document.body, {
            subtree: true,
            childList: true,
            attributes: true,
            attributeFilter: ['style']
        });
    }
});