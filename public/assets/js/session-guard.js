/**
 * Intercepta respuestas 401 en cualquier fetch y muestra aviso de sesión expirada.
 * Incluir DESPUÉS del bloque que define const BASE (o window.BASE).
 */
(function () {
    const _fetch = window.fetch;
    window.fetch = async function (...args) {
        const res = await _fetch(...args);
        if (res.status === 401) _mostrarExpiracion();
        return res;
    };

    function _mostrarExpiracion() {
        if (document.getElementById('sb-sesion-exp')) return;
        const base = window.BASE || '';
        const el   = document.createElement('div');
        el.id = 'sb-sesion-exp';
        el.style.cssText = 'position:fixed;inset:0;background:rgba(15,23,42,.78);z-index:99999;display:flex;align-items:center;justify-content:center;font-family:sans-serif';
        el.innerHTML = `
            <div style="background:#fff;border-radius:12px;padding:2rem 2.5rem;max-width:340px;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,.3)">
                <div style="font-size:2.2rem;margin-bottom:.6rem">⏱</div>
                <h3 style="margin:0 0 .4rem;font-size:1.05rem;color:#1e293b">Sesión expirada</h3>
                <p style="color:#64748b;font-size:.85rem;margin:0 0 1.25rem;line-height:1.5">
                    Tu sesión se cerró por inactividad.<br>
                    Serás redirigido al login en unos segundos.
                </p>
                <button onclick="window.location.href='${base}/login'"
                    style="background:#3b82f6;color:#fff;border:none;border-radius:8px;padding:.55rem 1.75rem;font-weight:600;cursor:pointer;font-size:.88rem">
                    Ir al login ahora
                </button>
            </div>`;
        document.body.appendChild(el);
        setTimeout(() => { window.location.href = base + '/login'; }, 5000);
    }
})();
