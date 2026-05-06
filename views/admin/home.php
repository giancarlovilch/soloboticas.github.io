<?php
if (!isset($_SESSION['user_rol'])) exit('Acceso denegado');
$nombreUsuario = $nombreUsuario ?? $_SESSION['user_name'] ?? 'Administrador';
?>

<div class="home-welcome">
    <div class="home-welcome__header">
        <h2>Bienvenido, <?= htmlspecialchars($nombreUsuario) ?></h2>
        <p>Panel de Administración &mdash; Grupo KGyR S.A.C &bull; Solo Boticas &bull; <?= date('d/m/Y') ?></p>
    </div>

    <!-- ── Reloj + Asistencia ──────────────────────────── -->
    <div class="admin-asist-card">
        <div class="admin-asist-card__clock-col">
            <div class="admin-asist-card__clock" id="adminReloj">00:00:00</div>
            <p class="admin-asist-card__estado" id="adminAsistEstado">Cargando...</p>
            <p class="admin-asist-card__horas"  id="adminAsistHoras"></p>
        </div>
        <div class="admin-asist-card__actions">
            <select id="adminLocalSelect" class="admin-asist-card__local">
                <option value="">— Local —</option>
            </select>
            <div class="admin-asist-card__btns">
                <button id="adminBtnEntrada" class="admin-asist-card__btn admin-btn-entrada"
                        onclick="adminAbrirModal('ENTRADA')" disabled>
                    Marcar Entrada
                </button>
                <button id="adminBtnSalida" class="admin-asist-card__btn admin-btn-salida"
                        onclick="adminAbrirModal('SALIDA')" disabled>
                    Marcar Salida
                </button>
            </div>
            <div id="adminAsistMsg" style="font-size:0.72rem; margin-top:0.4rem; color:#6b7280;"></div>
        </div>
    </div>

    <!-- Modal admin asistencia -->
    <div id="adminMarcarModal" class="admin-asist-modal-overlay" hidden>
        <div class="admin-asist-modal">
            <div class="admin-asist-modal__header">
                <h3 id="adminModalTitulo">Confirmar Entrada</h3>
                <button onclick="adminCerrarModal()" style="background:none;border:none;font-size:1.1rem;cursor:pointer;color:#64748b;">✕</button>
            </div>
            <div style="text-align:center; padding:1rem 1.5rem 0;">
                <p style="font-size:0.72rem;color:#64748b;margin-bottom:0.25rem;">Tu hora registrada será:</p>
                <div id="adminModalHora" style="font-size:2.5rem;font-weight:700;color:#1e293b;font-variant-numeric:tabular-nums;">--:--:--</div>
            </div>
            <div id="adminChecklistWrap" style="padding:1rem 1.5rem 0;">
                <p style="font-size:0.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:0.6rem;">Declara lo siguiente:</p>
                <div id="adminModalChecklist" style="display:flex;flex-direction:column;gap:0.5rem;"></div>
            </div>
            <div style="padding:1rem 1.5rem 0;">
                <label style="display:block;font-size:0.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.05em;margin-bottom:0.4rem;">Confirma con tu contraseña</label>
                <input type="password" id="adminModalPassword"
                       style="width:100%;padding:0.65rem 0.9rem;border:1.5px solid #e2e8f0;border-radius:8px;font-size:0.9rem;outline:none;"
                       placeholder="Tu contraseña">
            </div>
            <div id="adminModalError" style="margin:0.5rem 1.5rem 0;padding:0.5rem 0.75rem;border-radius:8px;background:#fee2e2;color:#991b1b;font-size:0.82rem;display:none;"></div>
            <div style="display:flex;justify-content:flex-end;gap:0.5rem;padding:1.25rem 1.5rem;border-top:1px solid #e2e8f0;margin-top:1rem;">
                <button onclick="adminCerrarModal()" style="padding:0.6rem 1.2rem;border:1px solid #e2e8f0;border-radius:8px;background:#fff;color:#64748b;font-size:0.85rem;font-weight:500;cursor:pointer;">Cancelar</button>
                <button id="adminBtnConfirmar" onclick="adminConfirmar()"
                        style="padding:0.6rem 1.4rem;border:none;border-radius:8px;font-size:0.85rem;font-weight:700;cursor:pointer;color:#fff;background:#0097A7;">
                    Confirmar
                </button>
            </div>
        </div>
    </div>

    <div class="home-cards">
        <a href="?page=postulantes" class="home-card">
            <div class="home-card__icon">👥</div>
            <div class="home-card__label">Postulantes</div>
            <div class="home-card__desc">Ver y gestionar postulaciones</div>
        </a>

        <a href="?page=status" class="home-card">
            <div class="home-card__icon">🔐</div>
            <div class="home-card__label">Accesos</div>
            <div class="home-card__desc">Habilitar o suspender usuarios</div>
        </a>

        <a href="?page=asistencias" class="home-card">
            <div class="home-card__icon">📋</div>
            <div class="home-card__label">Asistencias</div>
            <div class="home-card__desc">Control de asistencia del personal</div>
        </a>

        <a href="<?= defined('APP_BASE_PATH') ? APP_BASE_PATH : '' ?>/horario" class="home-card">
            <div class="home-card__icon">📅</div>
            <div class="home-card__label">Horarios</div>
            <div class="home-card__desc">Asignación semanal por local</div>
        </a>

        <a href="<?= defined('APP_BASE_PATH') ? APP_BASE_PATH : '' ?>/caja" class="home-card">
            <div class="home-card__icon">💰</div>
            <div class="home-card__label">Caja</div>
            <div class="home-card__desc">Gestión de cuadre de caja</div>
        </a>

        <a href="<?= defined('APP_BASE_PATH') ? APP_BASE_PATH : '' ?>/admin/reportes" class="home-card">
            <div class="home-card__icon">📊</div>
            <div class="home-card__label">Reportes</div>
            <div class="home-card__desc">Resultados de arqueo y más</div>
        </a>
    </div>
</div>

<style>
.admin-asist-card {
    background:#fff; border:1px solid #e5e7eb; border-left:4px solid #0097A7;
    border-radius:12px; padding:1.25rem 1.5rem; margin-bottom:1.5rem;
    display:flex; align-items:flex-start; gap:1.5rem; flex-wrap:wrap;
    box-shadow:0 1px 4px rgba(0,0,0,0.05);
}
.admin-asist-card__clock-col { flex:0 0 auto; text-align:center; }
.admin-asist-card__clock     { font-size:2.8rem; font-weight:700; color:#1e293b; font-variant-numeric:tabular-nums; line-height:1; letter-spacing:.03em; }
.admin-asist-card__estado    { font-size:0.82rem; font-weight:600; color:#2c3e50; margin:0.4rem 0 0; }
.admin-asist-card__horas     { font-size:0.72rem; color:#64748b; margin:0; }
.admin-asist-card__actions   { flex:1; min-width:220px; display:flex; flex-direction:column; gap:0.6rem; }
.admin-asist-card__local     { padding:5px 10px; font-size:0.82rem; border:1px solid #e2e8f0; border-radius:6px; outline:none; width:100%; }
.admin-asist-card__btns      { display:flex; gap:0.5rem; }
.admin-asist-card__btn       { flex:1; padding:7px 12px; font-size:0.82rem; font-weight:600; border:none; border-radius:7px; cursor:pointer; color:#fff; transition:background .15s; white-space:nowrap; }
.admin-asist-card__btn:disabled { opacity:.5; cursor:not-allowed; }
.admin-btn-entrada           { background:#0097A7; }
.admin-btn-entrada:hover:not(:disabled) { background:#007b8a; }
.admin-btn-salida            { background:#475569; }
.admin-btn-salida:hover:not(:disabled)  { background:#334155; }

/* modal */
.admin-asist-modal-overlay { position:fixed;inset:0;background:rgba(0,0,0,0.45);display:flex;align-items:center;justify-content:center;z-index:9999;padding:1rem; }
.admin-asist-modal-overlay[hidden] { display:none; }
.admin-asist-modal { background:#fff;border-radius:14px;width:100%;max-width:440px;box-shadow:0 24px 60px rgba(0,0,0,0.2);max-height:90vh;overflow-y:auto; }
.admin-asist-modal__header { display:flex;justify-content:space-between;align-items:center;padding:1.1rem 1.4rem;border-bottom:1px solid #e2e8f0; }
.admin-asist-modal__header h3 { font-size:0.95rem;font-weight:700;color:#1e293b;margin:0; }

/* checklist en el modal */
.admin-checklist-item { display:flex;align-items:flex-start;gap:0.55rem;padding:0.45rem 0.65rem;border-radius:7px;border:1.5px solid #e2e8f0;cursor:pointer;transition:border-color .15s; }
.admin-checklist-item:hover { border-color:#0097A7; }
.admin-checklist-item input { accent-color:#0097A7;width:15px;height:15px;flex-shrink:0;margin-top:1px; }
.admin-checklist-item span  { font-size:0.83rem;color:#1e293b; }
</style>

<script>
(function() {
    const BASE = (function() {
        const p = window.location.pathname;
        const i = p.indexOf('/admin/');
        return i === -1 ? '' : p.substring(0, i);
    })();
    const u = (p) => `${BASE}${p}`;

    const estadoEl  = document.getElementById('adminAsistEstado');
    const horasEl   = document.getElementById('adminAsistHoras');
    const localSel  = document.getElementById('adminLocalSelect');
    const msgEl     = document.getElementById('adminAsistMsg');
    const relojEl   = document.getElementById('adminReloj');
    const btnEntrada= document.getElementById('adminBtnEntrada');
    const btnSalida = document.getElementById('adminBtnSalida');

    let hoyAdmin    = null;
    let tipoAdmin   = 'ENTRADA';
    let chkCache    = { APERTURA: [], CIERRE: [] };
    let modalTick   = null;

    // Reloj
    const tickReloj = () => { if (relojEl) relojEl.textContent = new Date().toLocaleTimeString('es-PE',{hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:false}); };
    setInterval(tickReloj, 1000); tickReloj();

    async function init() {
        try {
            const r   = await fetch(u('/staff/api/historial'), { headers:{Accept:'application/json'} });
            const res = await r.json();
            if (!res.success) return;
            (res.data.locales||[]).forEach(({id,descripcion}) => {
                const o=document.createElement('option'); o.value=id; o.textContent=descripcion; localSel.appendChild(o);
            });
            actualizarUI(res.data.hoy, res.data.sesiones_hoy || 0);
        } catch {}

        // Cargar checklists
        for (const tipo of ['APERTURA','CIERRE']) {
            try {
                const r2  = await fetch(u(`/staff/api/checklist?tipo=${tipo}`),{headers:{Accept:'application/json'}});
                const res2= await r2.json();
                if (res2.success) chkCache[tipo] = res2.data||[];
            } catch {}
        }
    }

    function actualizarUI(hoy, sesionesHoy = 0) {
        hoyAdmin = hoy;
        const fmt = (dt) => dt ? new Date(dt.replace(' ','T')).toLocaleTimeString('es-PE',{hour:'2-digit',minute:'2-digit',hour12:false}) : '--';

        if (!hoy) {
            if (sesionesHoy > 0) {
                estadoEl.textContent = sesionesHoy === 1
                    ? 'Ya tienes 1 asistencia marcada hoy'
                    : `Ya tienes ${sesionesHoy} asistencias marcadas hoy`;
            } else {
                estadoEl.textContent = 'Sin marcar hoy';
            }
            horasEl.textContent  = '';
            btnEntrada.disabled  = false;
            btnSalida.disabled   = true;
        } else if (hoy.hora_salida) {
            estadoEl.textContent = `✓ Jornada completa — ${hoy.estado}`;
            horasEl.textContent  = `Entrada: ${fmt(hoy.hora_ingreso)}  Salida: ${fmt(hoy.hora_salida)}`;
            btnEntrada.disabled  = true;
            btnSalida.disabled   = true;
        } else {
            estadoEl.textContent = `Entrada marcada — ${hoy.estado}`;
            horasEl.textContent  = `Entrada: ${fmt(hoy.hora_ingreso)}`;
            btnEntrada.disabled  = true;
            btnSalida.disabled   = false;
        }
    }

    window.adminAbrirModal = function(tipo) {
        tipoAdmin = tipo;
        document.getElementById('adminModalTitulo').textContent  = tipo==='ENTRADA' ? 'Confirmar Entrada' : 'Confirmar Salida';
        document.getElementById('adminBtnConfirmar').textContent = tipo==='ENTRADA' ? 'Confirmar entrada' : 'Confirmar salida';
        document.getElementById('adminBtnConfirmar').style.background = tipo==='ENTRADA' ? '#0097A7' : '#475569';
        document.getElementById('adminModalPassword').value = '';
        document.getElementById('adminModalError').style.display = 'none';

        // Reloj del modal
        clearInterval(modalTick);
        const mh = document.getElementById('adminModalHora');
        const tick = () => { mh.textContent = new Date().toLocaleTimeString('es-PE',{hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:false}); };
        tick(); modalTick = setInterval(tick, 1000);

        // Checklist
        const items = chkCache[tipo==='ENTRADA' ? 'APERTURA' : 'CIERRE'] || [];
        const cont  = document.getElementById('adminModalChecklist');
        cont.innerHTML = '';
        document.getElementById('adminChecklistWrap').style.display = items.length ? '' : 'none';
        items.forEach(item => {
            const lbl = document.createElement('label');
            lbl.className = 'admin-checklist-item';
            lbl.innerHTML = `<input type="checkbox" data-id="${item.id_checklist}" checked><span>${item.descripcion}</span>`;
            cont.appendChild(lbl);
        });

        document.getElementById('adminMarcarModal').hidden = false;
        setTimeout(() => document.getElementById('adminModalPassword').focus(), 80);
    };

    window.adminCerrarModal = function() {
        clearInterval(modalTick);
        document.getElementById('adminMarcarModal').hidden = true;
    };

    window.adminConfirmar = async function() {
        const pw  = document.getElementById('adminModalPassword').value;
        const errEl = document.getElementById('adminModalError');
        if (!pw) { errEl.textContent='Ingresa tu contraseña.'; errEl.style.display='block'; return; }

        const checklist = [];
        document.querySelectorAll('#adminModalChecklist input[type="checkbox"]').forEach(cb => {
            checklist.push({ checklist_id: parseInt(cb.dataset.id), cumplido: cb.checked, observacion: null });
        });

        const btn = document.getElementById('adminBtnConfirmar');
        btn.disabled = true; btn.textContent = 'Confirmando...';
        errEl.style.display = 'none';

        try {
            const r   = await fetch(u('/staff/asistencia/marcar'), {
                method:'POST',
                headers:{'Content-Type':'application/json',Accept:'application/json'},
                body: JSON.stringify({
                    tipo: tipoAdmin,
                    password: pw,
                    checklist,
                    local_id: tipoAdmin==='ENTRADA' && localSel.value ? parseInt(localSel.value) : null,
                }),
            });
            const res = await r.json();
            if (res.success) {
                adminCerrarModal();
                msgEl.textContent = res.message;
                msgEl.style.color = '#0ea472';
                actualizarUI(res.data?.sesion ?? null, res.data?.sesiones_hoy ?? 0);
            } else {
                errEl.textContent = res.message || 'Error.';
                errEl.style.display = 'block';
            }
        } catch {
            errEl.textContent = 'Error de conexión.';
            errEl.style.display = 'block';
        } finally {
            btn.disabled    = false;
            btn.textContent = tipoAdmin==='ENTRADA' ? 'Confirmar entrada' : 'Confirmar salida';
        }
    };

    init();
})();
</script>
