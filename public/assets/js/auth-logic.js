/**
 * Lógica de autenticación — Solo Boticas
 */

function getBasePath() {
    const path  = window.location.pathname;
    const index = path.indexOf('/login');
    return index === -1 ? '' : path.substring(0, index);
}

const BASE_PATH = getBasePath();
const buildUrl  = (path) => `${BASE_PATH}${path}`;

// ── Alerta inline (reemplaza alert()) ────────────────────
function showAlert(msg, type = 'error') {
    const el = document.getElementById('loginAlert');
    if (!el) return;
    el.textContent = msg;
    el.className   = `login-alert ${type === 'error' ? 'is-error' : 'is-success'}`;
    el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function hideAlert() {
    const el = document.getElementById('loginAlert');
    if (el) el.className = 'login-alert';
}

// ── Toggle mostrar/ocultar contraseña ────────────────────
const toggleBtn = document.getElementById('togglePassword');
const pwInput   = document.getElementById('loginPassword');

if (toggleBtn && pwInput) {
    toggleBtn.addEventListener('click', () => {
        const visible = pwInput.type === 'text';
        pwInput.type  = visible ? 'password' : 'text';
        toggleBtn.setAttribute('aria-label', visible ? 'Mostrar contraseña' : 'Ocultar contraseña');
    });
}

// ── Formulario de Login ──────────────────────────────────
const loginForm = document.getElementById('loginForm');
const loginBtn  = document.getElementById('loginBtn');
const btnText   = document.getElementById('loginBtnText');
const btnSpinner = document.getElementById('loginBtnSpinner');

function setLoading(on) {
    if (!loginBtn) return;
    loginBtn.disabled = on;
    if (btnText)    btnText.hidden = on;
    if (btnSpinner) btnSpinner.hidden = !on;
}

if (loginForm) {
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        hideAlert();

        const username = document.getElementById('loginUsername')?.value.trim();
        const password = document.getElementById('loginPassword')?.value;

        if (!username || !password) {
            showAlert('Completa todos los campos.');
            return;
        }

        setLoading(true);

        try {
            const response = await fetch(buildUrl('/login'), {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({ username, password }),
            });

            const result = await response.json();

            if (result.success) {
                localStorage.setItem('sb_token', result.data.token);
                localStorage.setItem('sb_user',  JSON.stringify(result.data));

                showAlert(`¡Bienvenido, ${result.data.nombre_completo}!`, 'success');

                setTimeout(() => {
                    window.location.href = result.data.rol === 'ADMIN'
                        ? buildUrl('/admin/dashboard')
                        : buildUrl('/staff');
                }, 600);
            } else {
                showAlert(result.message || 'Credenciales incorrectas.');
                setLoading(false);
            }
        } catch {
            showAlert('No se pudo conectar con el servidor. Intenta de nuevo.');
            setLoading(false);
        }
    });
}
