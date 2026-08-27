<?php
$basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
$locales  = $locales ?? [];
$tareas   = $tareas  ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ganar estrellas | Solo Boticas</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/staff.css">
    <style>
        .ge-wrap { max-width:520px;margin:0 auto;padding:1rem 1rem 3rem; }
        .ge-step { display:none; }
        .ge-step.active { display:block; }
        .ge-title { font-size:.95rem;font-weight:800;color:#1e293b;margin:0 0 .3rem; }
        .ge-sub   { font-size:.78rem;color:#64748b;margin:0 0 1rem; }
        .ge-back  { font-size:.75rem;font-weight:700;color:#1d4ed8;background:none;border:none;cursor:pointer;padding:0;margin-bottom:.75rem;display:inline-flex;align-items:center;gap:.25rem; }

        /* Progreso de pasos: da secuencia clara en celular */
        .ge-progress { display:flex;align-items:center;gap:4px;margin-bottom:.9rem; }
        .ge-progress__dot { flex:1;height:5px;border-radius:99px;background:#e2e8f0; }
        .ge-progress__dot.on { background:#1d4ed8; }
        .ge-progress__label { font-size:.65rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.5rem; }

        .ge-select { width:100%;padding:.7rem .9rem;border:1.5px solid #bfdbfe;border-radius:10px;font-size:.9rem;font-weight:600;color:#1e293b;background:#eff6ff;outline:none; }

        .ge-list { display:flex;flex-direction:column;gap:.6rem; }
        .ge-card { display:flex;align-items:center;justify-content:space-between;background:#fff;border:1.5px solid #e2e8f0;border-radius:12px;padding:.85rem 1rem;cursor:pointer;transition:all .12s; text-align:left;width:100%; }
        .ge-card:hover { border-color:#93c5fd;background:#f8fafc; }
        .ge-card__nombre { font-size:.88rem;font-weight:700;color:#1e293b; }
        .ge-card__rol    { font-size:.7rem;color:#94a3b8;margin-top:1px; }
        .ge-card__badge  { font-size:.72rem;font-weight:700;color:#1d4ed8;background:#eff6ff;border-radius:20px;padding:3px 10px;white-space:nowrap; }
        .ge-card__estado { font-size:.68rem;color:#94a3b8;margin-top:2px; }

        .ge-empty { text-align:center;padding:2.5rem 1rem;color:#94a3b8; }
        .ge-empty__icon { font-size:2.2rem;margin-bottom:.5rem; }

        .ge-warn {
            background: linear-gradient(135deg, #fde047 0%, #fb923c 45%, #ef4444 100%);
            border: 3px solid #b91c1c;
            border-radius: 16px;
            padding: 1.05rem 1.35rem;
            margin-bottom: 1.5rem;
            font-size: 1.14rem;
            line-height: 1.45;
            color: #7f1d1d;
            font-weight: 700;
            text-align: center;
            box-shadow: 0 6px 18px rgba(220,38,38,.35);
        }
        .ge-warn strong {
            color: #7f1d1d;
            background: #fff;
            padding: 2px 8px;
            border-radius: 8px;
            display: inline-block;
            margin: 2px 0;
        }
        .ge-warn__icon { font-size: 1.8em; display: block; margin-bottom: .2rem; }

        .ge-stars { display:flex;gap:.4rem;justify-content:center;margin:1.25rem 0; }
        .ge-star  { font-size:2.4rem;line-height:1;background:none;border:none;cursor:pointer;color:#e2e8f0;transition:color .1s,transform .1s; padding:0; }
        .ge-star.on { color:#facc15; }
        .ge-star:active { transform:scale(1.15); }

        .ge-resumen { background:#eff6ff;border:1.5px solid #bfdbfe;border-radius:12px;padding:1rem 1.1rem;margin-bottom:1rem;text-align:center; }
        .ge-resumen__nombre { font-size:1rem;font-weight:800;color:#1e293b; }
        .ge-resumen__tarea  { font-size:.8rem;color:#1d4ed8;font-weight:700;margin-top:2px; }
        .ge-resumen__calif  { font-size:1.6rem;font-weight:800;color:#1d4ed8;margin-top:.5rem; }

        .ge-pwd label { font-size:.75rem;font-weight:600;color:#475569;display:block;margin-bottom:.3rem; }
        .ge-pwd input { padding:.6rem .8rem;border:1.5px solid #e2e8f0;border-radius:10px;font-size:.9rem;outline:none;width:100%;box-sizing:border-box;margin-bottom:.85rem; }

        .ge-btn { display:block;width:100%;padding:.75rem;border:none;border-radius:10px;font-size:.88rem;font-weight:800;color:#fff;background:#1d4ed8;cursor:pointer; }
        .ge-btn:disabled { opacity:.5;cursor:not-allowed; }
        .ge-err { font-size:.78rem;color:#dc2626;margin-bottom:.6rem;display:none;background:#fee2e2;border-radius:8px;padding:.5rem .7rem; }

        .ge-exito { text-align:center;padding:1.5rem 1rem; }
        .ge-exito__icon { font-size:3rem;margin-bottom:.5rem; }
        .ge-exito__msg  { font-size:.92rem;font-weight:700;color:#1e293b;margin-bottom:.3rem; }
        .ge-exito__sub  { font-size:.8rem;color:#64748b;margin-bottom:1.5rem; }
        .ge-btn--ghost { background:#f1f5f9;color:#475569;margin-top:.6rem; }

        /* Auditoría del día / denuncias */
        .ge-voto { background:#fff;border:1.5px solid #e2e8f0;border-radius:12px;padding:.75rem .9rem;margin-bottom:.6rem; }
        .ge-voto__top { display:flex;align-items:center;justify-content:space-between;gap:.5rem; }
        .ge-voto__nombre { font-size:.85rem;font-weight:700;color:#1e293b; }
        .ge-voto__tarea  { font-size:.72rem;color:#64748b;margin-top:1px; }
        .ge-voto__calif  { font-size:.8rem;font-weight:800;color:#1d4ed8;white-space:nowrap; }
        .ge-voto__por    { font-size:.68rem;color:#94a3b8;margin-top:.4rem; }
        .ge-voto__bottom { display:flex;align-items:center;justify-content:space-between;gap:.5rem;margin-top:.5rem;padding-top:.5rem;border-top:1px dashed #f1f5f9; }
        .ge-voto--sancionado { opacity:.6;background:#fef2f2;border-color:#fecaca; }
        .ge-tag { font-size:.68rem;font-weight:700;padding:2px 9px;border-radius:20px;white-space:nowrap; }
        .ge-tag--gris  { background:#f1f5f9;color:#94a3b8; }
        .ge-tag--rojo  { background:#fee2e2;color:#991b1b; }
        .ge-tag--verde { background:#d1fae5;color:#065f46; }
        .ge-btn--denunciar {
            background:#dc2626;color:#fff;border:none;border-radius:8px;padding:.4rem .85rem;
            font-size:.72rem;font-weight:800;cursor:pointer;white-space:nowrap;
        }
        .ge-cta-fija { margin-top:1.1rem; }
    </style>
</head>
<body style="background:#f8fafc;min-height:100vh;">

<header class="staff-header">
    <div class="staff-header__brand">
        <div class="staff-header__logo">SB</div>
        <div>
            <p class="staff-header__company">Grupo KGyR S.A.C</p>
            <p class="staff-header__app">⭐ Ganar <span>estrellas</span></p>
        </div>
    </div>
    <div class="staff-header__user">
        <a href="<?= $basePath ?>/staff" class="staff-btn-logout" style="font-size:.78rem;">← Volver</a>
    </div>
</header>

<!-- Modal de denuncia -->
<div id="geModalDenuncia" class="mh-ov" style="position:fixed;inset:0;background:rgba(15,23,42,.5);z-index:500;display:none;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:14px;padding:1.5rem;width:400px;max-width:92vw;box-shadow:0 20px 60px rgba(0,0,0,.22);">
        <h3 style="font-size:1rem;font-weight:800;margin:0 0 .3rem;color:#991b1b;">🚩 Denunciar registro</h3>
        <p id="geDenunciaDesc" style="font-size:.8rem;color:#64748b;margin-bottom:.9rem;"></p>
        <div style="background:#fee2e2;border-radius:8px;padding:.6rem .8rem;margin-bottom:.9rem;font-size:.76rem;color:#991b1b;">
            Si 2 personas denuncian este registro, se sanciona automáticamente: −50 estrellas a quien recibió y −100 a quien las otorgó.
        </div>
        <div id="geDenunciaErr" class="ge-err"></div>
        <div class="ge-pwd">
            <label>Tu contraseña para confirmar *</label>
            <input type="password" id="geDenunciaPwd" placeholder="Tu contraseña de acceso">
        </div>
        <div style="display:flex;gap:.5rem;">
            <button onclick="geCerrarDenuncia()" style="flex:1;padding:.6rem;border:none;border-radius:8px;font-size:.82rem;font-weight:700;cursor:pointer;background:#f1f5f9;color:#475569;">Cancelar</button>
            <button onclick="geConfirmarDenuncia()" style="flex:1;padding:.6rem;border:none;border-radius:8px;font-size:.82rem;font-weight:700;cursor:pointer;background:#dc2626;color:#fff;">Denunciar</button>
        </div>
    </div>
</div>

<main class="ge-wrap">

    <div class="ge-warn">
        <span class="ge-warn__icon">🚨⭐🚨</span>
        <strong>¡Vota con honestidad!</strong><br>
        Si 2 compañeros denuncian una calificación falsa:<br>
        🙋 quien la otorgó pierde <strong>−100 ⭐</strong><br>
        🎁 quien la recibió sin merecerla pierde <strong>−50 ⭐</strong>
    </div>

    <p class="ge-progress__label" id="geProgresoLabel">Paso 1 de 4 · Local y turno</p>
    <div class="ge-progress" id="geProgreso">
        <div class="ge-progress__dot on"></div>
        <div class="ge-progress__dot"></div>
        <div class="ge-progress__dot"></div>
        <div class="ge-progress__dot"></div>
    </div>

    <!-- ── Paso 1: local + turno ─────────────────────────── -->
    <div class="ge-step active" id="geStepLocal">
        <p class="ge-title">📍 ¿En qué local y turno?</p>
        <p class="ge-sub">Puedes elegir mañana o tarde de tu local, aunque tú hayas trabajado en el otro turno — así reconoces a quien barrió en la mañana y a quien trapeó en la tarde.</p>
        <?php if (empty($locales)): ?>
        <div class="ge-empty">
            <div class="ge-empty__icon">📅</div>
            <p>No tienes turno registrado hoy. Solo puedes calificar el mismo día del turno.</p>
        </div>
        <?php else: ?>
        <select id="geSelLocal" class="ge-select" onchange="geSeleccionarLocal()">
            <option value="">Selecciona local y turno…</option>
            <?php foreach ($locales as $l): ?>
            <option value="<?= (int)$l['local_id'] ?>|<?= (int)$l['turno_id'] ?>">
                <?= htmlspecialchars($l['local_desc']) ?> — Turno <?= htmlspecialchars($l['turno_desc']) ?>
            </option>
            <?php endforeach; ?>
        </select>
        <?php endif; ?>
    </div>

    <!-- ── Paso 2: estrellas del día + denuncias ─────────── -->
    <div class="ge-step" id="geStepAuditoria">
        <button class="ge-back" onclick="geVolver('geStepLocal')">← Cambiar local/turno</button>
        <p class="ge-title">📋 Estrellas entregadas hoy</p>
        <p class="ge-sub" id="geAuditoriaSub">Revisa lo que ya se registró en este turno. Si algo es falso, repórtalo.</p>
        <div id="geListaVotos" class="ge-list"></div>
        <div class="ge-cta-fija">
            <button class="ge-btn" onclick="geIrACompanero()">⭐ Reconocer a un compañero →</button>
        </div>
    </div>

    <!-- ── Paso 3: compañero ─────────────────────────────── -->
    <div class="ge-step" id="geStepCompanero">
        <button class="ge-back" onclick="geVolver('geStepAuditoria')">← Volver a estrellas de hoy</button>
        <p class="ge-title">🙋 ¿A quién quieres reconocer?</p>
        <p class="ge-sub">Compañeros con ese turno hoy en este local.</p>
        <div id="geListaCompaneros" class="ge-list"></div>
    </div>

    <!-- ── Paso 4: tarea ─────────────────────────────────── -->
    <div class="ge-step" id="geStepTarea">
        <button class="ge-back" onclick="geVolver('geStepCompanero')">← Cambiar compañero/a</button>
        <p class="ge-title">🧹 ¿Qué actividad hizo <span id="geTareaNombre"></span>?</p>
        <p class="ge-sub">Elige la actividad que quieres reconocer.</p>
        <div class="ge-list">
            <?php foreach ($tareas as $t): ?>
            <button type="button" class="ge-card" onclick="geSeleccionarTarea(<?= (int)$t['id_tarea'] ?>, '<?= addslashes($t['descripcion']) ?>', <?= (int)$t['estrellas_max'] ?>)">
                <span class="ge-card__nombre"><?= htmlspecialchars($t['descripcion']) ?></span>
                <span class="ge-card__badge">hasta <?= (int)$t['estrellas_max'] ?> ⭐</span>
            </button>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ── Paso 5: calificación ──────────────────────────── -->
    <div class="ge-step" id="geStepCalif">
        <button class="ge-back" onclick="geVolver('geStepTarea')">← Cambiar actividad</button>
        <div class="ge-resumen">
            <div class="ge-resumen__nombre" id="geResNombre"></div>
            <div class="ge-resumen__tarea" id="geResTarea"></div>
        </div>
        <p class="ge-title" style="text-align:center;">¿Qué tan bien lo hizo?</p>
        <p class="ge-sub" style="text-align:center;">Toca las estrellas que se merece — una sola calificación tuya ya vale completo.</p>
        <div class="ge-stars" id="geStars"></div>
        <div class="ge-pwd">
            <label>Tu contraseña para confirmar *</label>
            <input type="password" id="gePassword" placeholder="Tu contraseña de acceso">
        </div>
        <div id="geErr" class="ge-err"></div>
        <button class="ge-btn" id="geBtnConfirmar" onclick="geConfirmar()" disabled>Confirmar</button>
    </div>

    <!-- ── Éxito ──────────────────────────────────────────── -->
    <div class="ge-step" id="geStepExito">
        <div class="ge-exito">
            <div class="ge-exito__icon">🎉</div>
            <p class="ge-exito__msg" id="geExitoMsg"></p>
            <p class="ge-exito__sub" id="geExitoSub"></p>
            <button class="ge-btn" onclick="geReiniciar()">Reconocer a alguien más</button>
            <button class="ge-btn ge-btn--ghost" onclick="geVerAuditoria()">Ver estrellas de hoy</button>
            <a href="<?= $basePath ?>/staff" class="ge-btn ge-btn--ghost" style="text-decoration:none;display:block;text-align:center;">Volver al inicio</a>
        </div>
    </div>

</main>

<script src="<?= $basePath ?>/assets/js/session-guard.js"></script>
<script>
const BASE = '<?= $basePath ?>';

let geLocalId = 0;
let geTurnoId = 0;
let geLocalTurnoLabel = '';
let geBeneficiarioId = 0, geBeneficiarioNombre = '';
let geTareaId = 0, geTareaNombre = '', geTareaMax = 0;
let geCalificacion = 0;
let geVotoADenunciar = 0;

const GE_PROGRESO = {
    geStepLocal:      [1, 'Local y turno'],
    geStepAuditoria:  [2, 'Estrellas de hoy'],
    geStepCompanero:  [3, 'Elegir compañero/a'],
    geStepTarea:      [4, 'Elegir actividad'],
    geStepCalif:      [4, 'Calificar'],
    geStepExito:      [4, 'Listo'],
};

function geMostrar(id) {
    document.querySelectorAll('.ge-step').forEach(s => s.classList.remove('active'));
    document.getElementById(id).classList.add('active');
    const [n, label] = GE_PROGRESO[id] || [1, ''];
    document.getElementById('geProgresoLabel').textContent = `Paso ${n} de 4 · ${label}`;
    document.querySelectorAll('#geProgreso .ge-progress__dot').forEach((d, i) => {
        d.classList.toggle('on', i < n);
    });
}

function geVolver(id) {
    // Al volver a elegir local, resetea el <select>: si no cambia el value,
    // el navegador no dispara "change" y la pantalla se queda ahí sin avanzar.
    if (id === 'geStepLocal') {
        const sel = document.getElementById('geSelLocal');
        if (sel) sel.value = '';
    }
    geMostrar(id);
}

async function geSeleccionarLocal() {
    const sel = document.getElementById('geSelLocal');
    if (!sel.value) return;
    const [lid, tid] = sel.value.split('|').map(Number);
    geLocalId = lid; geTurnoId = tid;
    geLocalTurnoLabel = sel.options[sel.selectedIndex].textContent.trim();

    document.getElementById('geAuditoriaSub').textContent = geLocalTurnoLabel;
    await geCargarAuditoria();
    geMostrar('geStepAuditoria');
}

async function geCargarAuditoria() {
    const lista = document.getElementById('geListaVotos');
    lista.innerHTML = '<p class="ge-sub">Cargando…</p>';
    try {
        const r = await fetch(`${BASE}/staff/api/estrellas/dia?local_id=${geLocalId}&turno_id=${geTurnoId}`);
        const res = await r.json();
        const votos = res.data || [];
        if (!votos.length) {
            lista.innerHTML = '<div class="ge-empty"><div class="ge-empty__icon">🌙</div><p>Aún no se han entregado estrellas hoy en este turno.</p></div>';
            return;
        }
        lista.innerHTML = votos.map(v => {
            let accion;
            if (v.sancionado) {
                accion = `<span class="ge-tag ge-tag--rojo">🚫 Sancionado</span>`;
            } else if (v.ya_reporte) {
                accion = `<span class="ge-tag ge-tag--verde">✅ Ya lo denunciaste</span>`;
            } else if (v.puede_denunciar) {
                accion = `<button class="ge-btn--denunciar" onclick="geAbrirDenuncia(${v.id_voto}, '${v.beneficiario_nombre.replace(/'/g, "\\'")}', '${v.tarea.replace(/'/g, "\\'")}')">🚩 Denunciar</button>`;
            } else {
                accion = `<span class="ge-tag ge-tag--gris">Eres parte de este registro</span>`;
            }
            const repBadge = v.reportes > 0
                ? `<span class="ge-tag ${v.sancionado ? 'ge-tag--rojo' : 'ge-tag--gris'}">Denunciado ${v.reportes}/2</span>`
                : '';
            return `
            <div class="ge-voto ${v.sancionado ? 'ge-voto--sancionado' : ''}">
                <div class="ge-voto__top">
                    <div>
                        <div class="ge-voto__nombre">${v.beneficiario_nombre}</div>
                        <div class="ge-voto__tarea">${v.tarea}</div>
                    </div>
                    <div class="ge-voto__calif">${v.sancionado ? '0' : v.calificacion} ⭐</div>
                </div>
                <div class="ge-voto__por">Otorgado por ${v.votante_nombre}</div>
                <div class="ge-voto__bottom">
                    ${repBadge}
                    ${accion}
                </div>
            </div>`;
        }).join('');
    } catch {
        lista.innerHTML = '<p class="ge-err" style="display:block;">Error de conexión.</p>';
    }
}

function geIrACompanero() {
    geCargarCompaneros();
    geMostrar('geStepCompanero');
}

async function geCargarCompaneros() {
    const lista = document.getElementById('geListaCompaneros');
    lista.innerHTML = '<p class="ge-sub">Cargando…</p>';
    try {
        const r = await fetch(`${BASE}/staff/api/estrellas/companeros?local_id=${geLocalId}&turno_id=${geTurnoId}`);
        const res = await r.json();
        const companeros = res.data || [];
        if (!companeros.length) {
            lista.innerHTML = '<div class="ge-empty"><div class="ge-empty__icon">🙈</div><p>No hay compañeros con ese turno hoy en este local.</p></div>';
            return;
        }
        lista.innerHTML = companeros.map(c => {
            const badge = c.votos_hoy > 0
                ? `<span class="ge-card__badge">🌟 ${c.votos_hoy} encuesta${c.votos_hoy === 1 ? '' : 's'}</span>`
                : `<span class="ge-card__badge" style="color:#94a3b8;background:#f1f5f9;">Sin encuestas</span>`;
            return `
            <button type="button" class="ge-card" onclick="geSeleccionarCompanero(${c.id}, '${c.nombre.replace(/'/g, "\\'")}')">
                <div>
                    <div class="ge-card__nombre">${c.nombre}</div>
                    <div class="ge-card__rol">${c.rol_desc || ''}</div>
                </div>
                ${badge}
            </button>
        `;
        }).join('');
    } catch {
        lista.innerHTML = '<p class="ge-err" style="display:block;">Error de conexión.</p>';
    }
}

function geSeleccionarCompanero(id, nombre) {
    geBeneficiarioId = id;
    geBeneficiarioNombre = nombre;
    document.getElementById('geTareaNombre').textContent = nombre;
    geMostrar('geStepTarea');
}

function geSeleccionarTarea(id, nombre, max) {
    geTareaId = id; geTareaNombre = nombre; geTareaMax = max; geCalificacion = 0;

    document.getElementById('geResNombre').textContent = geBeneficiarioNombre;
    document.getElementById('geResTarea').textContent = nombre;

    const starsEl = document.getElementById('geStars');
    starsEl.innerHTML = '';
    for (let i = 1; i <= max; i++) {
        const b = document.createElement('button');
        b.type = 'button'; b.className = 'ge-star'; b.textContent = '★';
        b.onclick = () => geElegirCalificacion(i);
        starsEl.appendChild(b);
    }
    document.getElementById('gePassword').value = '';
    document.getElementById('geErr').style.display = 'none';
    document.getElementById('geBtnConfirmar').disabled = true;

    geMostrar('geStepCalif');
}

function geElegirCalificacion(n) {
    geCalificacion = n;
    document.querySelectorAll('#geStars .ge-star').forEach((b, i) => {
        b.classList.toggle('on', i < n);
    });
    document.getElementById('geBtnConfirmar').disabled = false;
}

function geShowErr(msg) {
    const el = document.getElementById('geErr');
    el.textContent = msg; el.style.display = 'block';
}

async function geConfirmar() {
    const password = document.getElementById('gePassword').value.trim();
    if (!password) { geShowErr('Tu contraseña es requerida.'); return; }
    if (!geCalificacion) { geShowErr('Elige cuántas estrellas darle.'); return; }

    const btn = document.getElementById('geBtnConfirmar');
    btn.disabled = true; btn.textContent = 'Enviando…';

    try {
        const r = await fetch(`${BASE}/staff/api/estrellas/votar`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                beneficiario_id: geBeneficiarioId, tarea_id: geTareaId, local_id: geLocalId, turno_id: geTurnoId,
                calificacion: geCalificacion, password,
            }),
        });
        const res = await r.json();
        if (res.success) {
            document.getElementById('geExitoMsg').textContent = `¡Le diste ${geCalificacion} ⭐ a ${geBeneficiarioNombre}!`;
            document.getElementById('geExitoSub').textContent = `${geTareaNombre}: ganó ${geCalificacion} estrellas azules de inmediato.`;
            geMostrar('geStepExito');
        } else {
            geShowErr(res.message || 'Error al registrar.');
            btn.disabled = false; btn.textContent = 'Confirmar';
        }
    } catch {
        geShowErr('Error de conexión.');
        btn.disabled = false; btn.textContent = 'Confirmar';
    }
}

function geReiniciar() {
    geBeneficiarioId = 0; geTareaId = 0; geCalificacion = 0;
    document.getElementById('geBtnConfirmar').textContent = 'Confirmar';
    geIrACompanero();
}

function geVerAuditoria() {
    geCargarAuditoria();
    geMostrar('geStepAuditoria');
}

// ── Denuncias ──────────────────────────────────────────
function geAbrirDenuncia(votoId, nombre, tarea) {
    geVotoADenunciar = votoId;
    document.getElementById('geDenunciaDesc').textContent = `${nombre} — ${tarea}`;
    document.getElementById('geDenunciaPwd').value = '';
    document.getElementById('geDenunciaErr').style.display = 'none';
    document.getElementById('geModalDenuncia').style.display = 'flex';
    setTimeout(() => document.getElementById('geDenunciaPwd').focus(), 80);
}
function geCerrarDenuncia() {
    document.getElementById('geModalDenuncia').style.display = 'none';
}

async function geConfirmarDenuncia() {
    const password = document.getElementById('geDenunciaPwd').value.trim();
    const err = document.getElementById('geDenunciaErr');
    if (!password) { err.textContent = 'Tu contraseña es requerida.'; err.style.display = 'block'; return; }
    try {
        const r = await fetch(`${BASE}/staff/api/estrellas/reportar`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ voto_id: geVotoADenunciar, password }),
        });
        const res = await r.json();
        if (res.success) {
            geCerrarDenuncia();
            await geCargarAuditoria();
        } else {
            err.textContent = res.message || 'Error al denunciar.';
            err.style.display = 'block';
        }
    } catch {
        err.textContent = 'Error de conexión.';
        err.style.display = 'block';
    }
}
</script>
</body>
</html>
