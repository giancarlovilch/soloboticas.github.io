/**
 * Formulario de postulación — Solo Boticas
 */

// ── Utilidades ───────────────────────────────────────────────
function getBasePath() {
    const idx = window.location.pathname.indexOf('/postulacion/');
    return idx === -1 ? '' : window.location.pathname.substring(0, idx);
}
const BASE_PATH = getBasePath();
const buildUrl  = (p) => `${BASE_PATH}${p}`;

const dni = new URLSearchParams(window.location.search).get('dni');

let mode            = 'editable';
let catalogoSkills  = [];
let catalogoNiveles = [];
let postulanteId    = null;

const form        = document.getElementById('postulacionForm');
const statusBox   = document.getElementById('statusBox');
const stageBanner = document.getElementById('stageBanner');
const submitBtn   = document.getElementById('submitBtn');
const submitText  = document.getElementById('submitText');
const submitSpinner = document.getElementById('submitSpinner');

// ── Foto preview ─────────────────────────────────────────────
const fotoInput   = document.getElementById('fotoInput');
const fotoPreview = document.getElementById('fotoPreview');

if (fotoInput) {
    fotoInput.addEventListener('change', () => {
        const file = fotoInput.files[0];
        if (!file) return;
        const url = URL.createObjectURL(file);
        fotoPreview.innerHTML = `<img src="${url}" alt="Vista previa" style="width:100%;height:100%;object-fit:cover;border-radius:8px;">`;
    });
}

// ── Inicialización ────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', async () => {
    if (!dni) {
        showStatus('No se recibió el DNI en la URL. Vuelve a la pantalla de acceso.', 'error');
        return;
    }
    await loadCatalogos();
    await loadApplicationView();
});

// ── Catálogos ─────────────────────────────────────────────────
async function loadCatalogos() {
    try {
        const r    = await fetch(buildUrl('/catalogos/postulacion'), { headers: { Accept: 'application/json' } });
        const res  = await r.json();
        if (!res.success) { showStatus('No se pudieron cargar los catálogos.', 'error'); return; }

        const d = res.data;
        catalogoSkills  = d.skills  || [];
        catalogoNiveles = d.niveles || [];

        fillSelect('genero_id',             d.generos);
        fillSelect('situacion_vivienda_id', d.situaciones_vivienda);
        fillSelect('institucion_id',        d.instituciones);
        fillSelect('tipo_estudio_id',       d.tipos_estudio);
        fillSelect('estado_id',             d.estados);
        fillSelect('turno_id',              d.turnos);
        fillSelect('puesto_id',             d.puestos);
        fillSelectMultiple('skill_id[]',    d.skills);
        fillSelectMultiple('nivel_id[]',    d.niveles);
    } catch {
        showStatus('Error al cargar los catálogos.', 'error');
    }
}

// ── Vista de postulación existente ────────────────────────────
async function loadApplicationView() {
    try {
        const r   = await fetch(buildUrl(`/postulaciones/${encodeURIComponent(dni)}`), { headers: { Accept: 'application/json' } });
        const res = await r.json();

        if (!res.success) {
            showStatus(res.message || 'No se pudo cargar la postulación.', 'error');
            return;
        }

        const data = res.data;
        mode = data.mode;
        postulanteId = data.postulante?.id_postulante ?? null;

        renderStageBanner(data.postulacion);

        if (mode === 'readonly') {
            showStatus('Tu solicitud ya fue enviada. Formulario en modo sólo lectura.', 'info');
            fillReadonlyData(data);
            disableForm();
            return;
        }

        showStatus(`Completa tus datos para el DNI ${dni}.`, 'info');
        prefillEditableData(data);

    } catch {
        showStatus('Error al cargar la información de tu postulación.', 'error');
    }
}

// ── Envío del formulario ──────────────────────────────────────
form.addEventListener('submit', async (e) => {
    e.preventDefault();

    if (mode === 'readonly') {
        showStatus('La solicitud ya fue enviada y no puede modificarse.', 'error');
        return;
    }

    const experiencias = collectExperiencias();
    const skills       = collectSkills();

    if (skills === null) return; // duplicados detectados

    const body = {
        nombres:              getValue('nombres'),
        apellidos:            getValue('apellidos'),
        genero_id:            toInt(getValue('genero_id')),
        fecha_nacimiento:     getValue('fecha_nacimiento') || null,
        email:                getValue('email') || null,
        telefono:             getValue('telefono') || null,
        situacion_vivienda_id: toInt(getValue('situacion_vivienda_id')),
        num_documento:        dni,
        institucion_id:       toInt(getValue('institucion_id')),
        tipo_estudio_id:      toInt(getValue('tipo_estudio_id')),
        estado_id:            toInt(getValue('estado_id')),
        fecha_inicio:         getValue('fecha_inicio') || null,
        fecha_fin:            getValue('fecha_fin')    || null,
        turno_id:             toInt(getValue('turno_id')),
        puesto_id:            toInt(getValue('puesto_id')),
        experiencias,
        skills,
    };

    setLoading(true);

    try {
        const r   = await fetch(buildUrl('/postulaciones'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify(body),
        });
        const res = await r.json();

        if (!res.success) {
            showStatus(res.message || 'No se pudo enviar la postulación.', 'error');
            return;
        }

        postulanteId = res.data?.postulante?.id_postulante ?? null;
        mode = 'readonly';
        disableForm();

        // Si hay foto seleccionada, subirla ahora
        if (postulanteId && fotoInput?.files[0]) {
            await uploadFoto(postulanteId, fotoInput.files[0]);
        }

        startSuccessCountdown();

    } catch {
        showStatus('Error al enviar la postulación. Intenta nuevamente.', 'error');
    } finally {
        setLoading(false);
    }
});

async function uploadFoto(id, file) {
    const fd = new FormData();
    fd.append('postulante_id', id);
    fd.append('foto', file);

    try {
        await fetch(buildUrl('/postulantes/foto'), { method: 'POST', body: fd });
    } catch {
        // La foto es opcional; un fallo no cancela la postulación
    }
}

// ── Dinámicos: skills y experiencias ────────────────────────
document.getElementById('addSkillBtn')?.addEventListener('click', () => {
    const container = document.getElementById('skillsContainer');
    const div = document.createElement('div');
    div.className = 'skill-item dynamic-card';
    div.innerHTML = `
        <div class="form-grid form-grid-2">
            <div class="input-group">
                <label>Habilidad</label>
                <select name="skill_id[]"><option value="">Seleccione</option></select>
            </div>
            <div class="input-group">
                <label>Nivel</label>
                <select name="nivel_id[]"><option value="">Seleccione</option></select>
            </div>
        </div>
        <button type="button" class="btn-remove-card" onclick="this.closest('.dynamic-card').remove()">✕</button>`;
    fillSingleSelect(div.querySelector('select[name="skill_id[]"]'), catalogoSkills);
    fillSingleSelect(div.querySelector('select[name="nivel_id[]"]'), catalogoNiveles);
    container.appendChild(div);
});

document.getElementById('addExperienciaBtn')?.addEventListener('click', () => {
    const container = document.getElementById('experienciasContainer');
    const div = document.createElement('div');
    div.className = 'experiencia-item dynamic-card';
    div.innerHTML = `
        <button type="button" class="btn-remove-card" onclick="this.closest('.dynamic-card').remove()">✕</button>
        <div class="form-grid form-grid-2">
            <div class="input-group"><label>Empresa</label><input type="text" name="empresa[]"></div>
            <div class="input-group"><label>Cargo</label><input type="text" name="cargo[]"></div>
            <div class="input-group"><label>Fecha inicio</label><input type="date" name="exp_fecha_inicio[]"></div>
            <div class="input-group"><label>Fecha fin</label><input type="date" name="exp_fecha_fin[]"></div>
        </div>`;
    container.appendChild(div);
});

// ── Recolectores ─────────────────────────────────────────────
function collectSkills() {
    const ids    = [...document.getElementsByName('skill_id[]')];
    const nivels = [...document.getElementsByName('nivel_id[]')];
    const skills = [];
    const seen   = new Set();

    for (let i = 0; i < ids.length; i++) {
        const sid = ids[i].value;
        if (!sid) continue;
        if (seen.has(sid)) {
            showStatus('No puedes repetir la misma habilidad más de una vez.', 'error');
            return null;
        }
        seen.add(sid);
        skills.push({ skill_id: Number(sid), nivel_id: nivels[i]?.value ? Number(nivels[i].value) : null });
    }
    return skills;
}

function collectExperiencias() {
    const empresas = [...document.getElementsByName('empresa[]')];
    const cargos   = [...document.getElementsByName('cargo[]')];
    const inics    = [...document.getElementsByName('exp_fecha_inicio[]')];
    const fins     = [...document.getElementsByName('exp_fecha_fin[]')];
    const result   = [];
    for (let i = 0; i < empresas.length; i++) {
        if (!empresas[i].value.trim() || !inics[i]?.value) continue;
        result.push({
            empresa:     empresas[i].value.trim(),
            cargo:       cargos[i]?.value.trim() || null,
            fecha_inicio: inics[i].value,
            fecha_fin:   fins[i]?.value || null,
        });
    }
    return result;
}

// ── Rellenar datos ────────────────────────────────────────────
function prefillEditableData(data) {
    if (!data?.postulante) return;
    const p = data.postulante;
    setValue('nombres',  p.nombres);
    setValue('apellidos', p.apellidos);
    setValue('genero_id', p.genero_id);
    setValue('fecha_nacimiento', p.fecha_nacimiento);
    setValue('email',    p.email);
    setValue('telefono', p.telefono);
    setValue('situacion_vivienda_id', p.situacion_vivienda_id);

    if (p.foto_url) {
        fotoPreview.innerHTML = `<img src="${p.foto_url}" alt="Foto actual" style="width:100%;height:100%;object-fit:cover;border-radius:8px;">`;
    }
}

function fillReadonlyData(data) {
    prefillEditableData(data);

    setValue('institucion_id',  data.postulante?.institucion_id);
    setValue('tipo_estudio_id', data.postulante?.tipo_estudio_id);
    setValue('estado_id',       data.postulante?.estado_id);
    setValue('fecha_inicio',    data.postulante?.fecha_inicio);
    setValue('fecha_fin',       data.postulante?.fecha_fin);
    setValue('turno_id',        data.postulante?.turno_id);

    if (data.postulacion) setValue('puesto_id', data.postulacion.puesto_id);

    fillReadonlyExperiencias(data.experiencias || []);
    fillReadonlySkills(data.skills || []);
}

function fillReadonlyExperiencias(list) {
    const c = document.getElementById('experienciasContainer');
    if (!c) return;
    c.innerHTML = '';
    if (!list.length) { c.innerHTML = '<p class="empty-note">Sin experiencia registrada.</p>'; return; }
    list.forEach(e => {
        const div = document.createElement('div');
        div.className = 'dynamic-card';
        div.innerHTML = `
            <div class="form-grid form-grid-2">
                <div class="input-group"><label>Empresa</label><input type="text" value="${esc(e.empresa)}" disabled></div>
                <div class="input-group"><label>Cargo</label><input type="text" value="${esc(e.cargo||'')}" disabled></div>
                <div class="input-group"><label>Fecha inicio</label><input type="date" value="${e.fecha_inicio||''}" disabled></div>
                <div class="input-group"><label>Fecha fin</label><input type="date" value="${e.fecha_fin||''}" disabled></div>
            </div>`;
        c.appendChild(div);
    });
}

function fillReadonlySkills(list) {
    const c = document.getElementById('skillsContainer');
    if (!c) return;
    c.innerHTML = '';
    if (!list.length) { c.innerHTML = '<p class="empty-note">Sin habilidades registradas.</p>'; return; }
    list.forEach(s => {
        const div = document.createElement('div');
        div.className = 'dynamic-card';
        div.innerHTML = `
            <div class="form-grid form-grid-2">
                <div class="input-group"><label>Habilidad</label><input type="text" value="${esc(s.skill_descripcion||'')}" disabled></div>
                <div class="input-group"><label>Nivel</label><input type="text" value="${esc(s.nivel_descripcion||'')}" disabled></div>
            </div>`;
        c.appendChild(div);
    });
}

// ── Selects ───────────────────────────────────────────────────
function fillSelect(id, items) {
    const el = document.getElementById(id);
    if (!el || !Array.isArray(items) || el.dataset.loaded) return;
    items.forEach(({ id: v, descripcion: t }) => {
        const o = document.createElement('option');
        o.value = v; o.textContent = t;
        el.appendChild(o);
    });
    el.dataset.loaded = '1';
}

function fillSelectMultiple(name, items) {
    [...document.getElementsByName(name)].forEach(el => {
        if (el.dataset.loaded) return;
        items.forEach(({ id: v, descripcion: t }) => {
            const o = document.createElement('option');
            o.value = v; o.textContent = t;
            el.appendChild(o);
        });
        el.dataset.loaded = '1';
    });
}

function fillSingleSelect(el, items) {
    el.innerHTML = '<option value="">Seleccione</option>';
    items.forEach(({ id: v, descripcion: t }) => {
        const o = document.createElement('option');
        o.value = v; o.textContent = t;
        el.appendChild(o);
    });
}

// ── Banner de etapa ───────────────────────────────────────────
function renderStageBanner(postulacion) {
    if (!stageBanner || !postulacion?.etapa_id) { stageBanner?.setAttribute('hidden', ''); return; }
    const id = Number(postulacion.etapa_id);
    const map = {
        1: ['stage-pendiente',  '⏳ Tu postulación está en revisión (Etapa: Pendiente)'],
        2: ['stage-entrevista', '🗓️  ¡Avanzaste a Entrevista! Espera ser contactado'],
        3: ['stage-rechazado',  '❌ Tu proceso de selección ha finalizado'],
    };
    if (map[id]) {
        stageBanner.className  = `stage-banner ${map[id][0]}`;
        stageBanner.textContent = map[id][1];
        stageBanner.removeAttribute('hidden');
    } else if (id >= 4) {
        stageBanner.className  = 'stage-banner stage-intranet';
        stageBanner.textContent = '🔒 Tu proceso continúa por intranet. Inicia sesión en Solo Boticas Intranet.';
        stageBanner.removeAttribute('hidden');
    }
}

// ── Deshabilitar formulario ───────────────────────────────────
function disableForm() {
    form.querySelectorAll('input, select, textarea, button').forEach(f => f.disabled = true);
    form.classList.add('is-readonly');
    document.getElementById('fotoBtnLabel')?.classList.add('disabled');
}

// ── Countdown de éxito ────────────────────────────────────────
function startSuccessCountdown() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
    let sec = 6;
    showStatus(`✅ ¡Postulación enviada! Serás redirigido en ${sec} segundos...`, 'success');
    const iv = setInterval(() => {
        sec--;
        if (sec <= 0) { clearInterval(iv); window.location.href = buildUrl('/postulacion/acceso'); return; }
        showStatus(`✅ ¡Postulación enviada! Serás redirigido en ${sec} segundos...`, 'success');
    }, 1000);
}

// ── Utilidades UI ─────────────────────────────────────────────
function showStatus(msg, type = 'info') {
    if (!statusBox) return;
    statusBox.textContent = msg;
    statusBox.className   = `status-banner status-${type}`;
    statusBox.removeAttribute('hidden');
    statusBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function setLoading(on) {
    submitBtn.disabled       = on;
    submitText.hidden        = on;
    submitSpinner.hidden     = !on;
}

function getValue(id) {
    const el = document.getElementById(id);
    return el ? el.value.trim() : '';
}

function setValue(id, val) {
    const el = document.getElementById(id);
    if (el) el.value = val ?? '';
}

function toInt(v) { return v === '' || v == null ? null : Number(v); }

function esc(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
