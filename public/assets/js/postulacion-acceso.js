/**
 * Acceso a postulación — Solo Boticas
 */

function getBasePath() {
    const idx = window.location.pathname.indexOf('/postulacion/');
    return idx === -1 ? '' : window.location.pathname.substring(0, idx);
}
const BASE_PATH = getBasePath();
const buildUrl  = (p) => `${BASE_PATH}${p}`;

const form              = document.getElementById('accessForm');
const dniInput          = document.getElementById('num_documento');
const captchaInput      = document.getElementById('captcha_answer');
const captchaLabel      = document.getElementById('captchaLabel');
const birthDateContainer = document.getElementById('birthDateContainer');
const birthDateInput    = document.getElementById('fecha_nacimiento');
const messageBox        = document.getElementById('messageBox');
const submitBtn         = document.getElementById('submitBtn');
const submitBtnText     = document.getElementById('submitBtnText');

let requiresBirthValidation = false;

loadCaptcha();

form.addEventListener('submit', async (e) => {
    e.preventDefault();
    clearMessage();

    const dni     = dniInput.value.trim();
    const captcha = captchaInput.value.trim();
    const dob     = birthDateInput?.value.trim() ?? '';

    if (!dni || !captcha) { showMessage('Completa todos los campos.', 'error'); return; }

    requiresBirthValidation
        ? await validateAccess(dni, captcha, dob)
        : await checkDni(dni, captcha);
});

async function loadCaptcha() {
    try {
        const r   = await fetch(buildUrl('/captcha'), { headers: { Accept: 'application/json' } });
        const res = await r.json();
        if (res.success && captchaLabel) {
            captchaLabel.textContent = res.data.question;
        }
    } catch {
        if (captchaLabel) captchaLabel.textContent = 'Verificación: error al cargar, recarga la página.';
    }
}

async function checkDni(dni, captcha) {
    setLoading(true);
    try {
        const r   = await fetch(buildUrl('/postulantes/check-dni'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ num_documento: dni, captcha_answer: captcha }),
        });
        const res = await r.json();

        if (!res.success) {
            showMessage(res.message, 'error');
            captchaInput.value = '';
            await loadCaptcha();
            return;
        }

        if (res.data.requires_birth_validation) {
            requiresBirthValidation = true;
            birthDateContainer.style.display = 'block';
            birthDateContainer.setAttribute('aria-hidden', 'false');
            birthDateInput.required = true;
            showMessage('DNI encontrado. Ingresa tu fecha de nacimiento para ver tu solicitud.', 'info');
            submitBtnText && (submitBtnText.textContent = 'Verificar identidad');
            captchaInput.value = '';
            await loadCaptcha();
            return;
        }

        window.location.href = buildUrl(`/postulacion/formulario?dni=${encodeURIComponent(dni)}`);
    } catch {
        showMessage('Error de conexión. Intenta nuevamente.', 'error');
    } finally {
        setLoading(false);
    }
}

async function validateAccess(dni, captcha, dob) {
    if (!dob) { showMessage('Ingresa tu fecha de nacimiento.', 'error'); return; }
    setLoading(true);
    try {
        const r   = await fetch(buildUrl('/postulantes/validate-access'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ num_documento: dni, captcha_answer: captcha, fecha_nacimiento: dob }),
        });
        const res = await r.json();

        if (!res.success) {
            showMessage(res.message, 'error');
            captchaInput.value = '';
            await loadCaptcha();
            return;
        }

        window.location.href = buildUrl(`/postulacion/formulario?dni=${encodeURIComponent(dni)}`);
    } catch {
        showMessage('Error de conexión. Intenta nuevamente.', 'error');
    } finally {
        setLoading(false);
    }
}

function showMessage(msg, type = 'info') {
    if (!messageBox) return;
    messageBox.textContent = msg;
    messageBox.className   = `message-box message-${type}`;
    messageBox.style.display = 'block';
}

function clearMessage() {
    if (!messageBox) return;
    messageBox.textContent = '';
    messageBox.className   = 'message-box';
    messageBox.style.display = 'none';
}

function setLoading(on) {
    submitBtn.disabled = on;
    if (submitBtnText) {
        submitBtnText.textContent = on ? 'Validando...' : (requiresBirthValidation ? 'Verificar identidad' : 'Continuar');
    }
}
