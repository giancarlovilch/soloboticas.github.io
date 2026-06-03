<?php
$basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
/** @var array $semanas */ /** @var array|null $semana */ /** @var string $userName */

$DIAS   = ['Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sá', 'Do'];
$ROLES  = ['CAJERA' => 'Cajera', 'VENDEDORA' => 'Vendedora', 'ALMACENERA' => 'Almacenera'];
$TURNOS = [1 => 'Mañana', 2 => 'Tarde'];
$LOCALES = [
    2 => ['nombre' => 'Local 2', 'color' => '#0097A7'],
    3 => ['nombre' => 'Local 3', 'color' => '#5b21b6'],
    4 => ['nombre' => 'Local 4', 'color' => '#d97706'],
];
$slotsConfig = $slotsConfig ?? [];
$roles       = $roles       ?? [];

$fechasSemana = [];
if ($semana) {
    $inicio = new DateTime($semana['fecha_inicio']);
    for ($d = 0; $d < 7; $d++) {
        $dia = clone $inicio;
        $dia->modify("+{$d} days");
        $fechasSemana[$d + 1] = $dia->format('Y-m-d');
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de horarios | Solo Boticas</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/horario.css">
    <link rel="icon" type="image/x-icon" href="<?= $basePath ?>/assets/img/logo.ico">
    <style>
        /* Modo solo lectura */
        .hor-asiento { cursor: default !important; pointer-events: none !important; }
        .hor-asiento:hover { transform: none !important; box-shadow: none !important; }
        /* Admin: botones siempre visibles */
        .hor-asiento__btn-picker  { opacity: 1 !important; }
        .hor-asiento__btn-liberar { opacity: 1 !important; }

    </style>
</head>
<body>

<header class="hor-header">
    <div class="hor-header__brand">
        <div class="hor-header__logo">SB</div>
        <div>
            <p class="hor-header__company">Grupo KGyR S.A.C</p>
            <p class="hor-header__app">Historial de horarios</p>
        </div>
    </div>
    <div class="hor-header__right">
        <span class="hor-header__user"><?= htmlspecialchars($userName) ?></span>
        <a href="<?= $basePath ?>/horario/asistencia" class="hor-btn hor-btn--outline" style="font-size:0.78rem;">📊<span class="hor-btn-txt"> Métricas</span></a>
        <a href="<?= $basePath ?>/horario/log" class="hor-btn hor-btn--outline" style="font-size:0.78rem;">📋<span class="hor-btn-txt"> Logs</span></a>
        <a href="<?= $basePath ?>/<?= $esAdmin ? 'admin/dashboard' : 'staff' ?>" class="hor-btn-back">←<span class="hor-btn-txt"> Volver</span></a>
    </div>
</header>

<main class="hor-main">

    <?php if (empty($semanas)): ?>
    <div class="hor-empty">
        <div class="hor-empty__icon">📂</div>
        <h2>Sin semanas anteriores</h2>
        <p>Aquí aparecerán los horarios de semanas pasadas.</p>
    </div>
    <?php else: ?>

    <!-- ── Navegación ─────────────────────────────────────── -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">

        <div class="hor-btn-pagina-actual">
            <span style="font-size:1.3rem;">◀</span>
            <div style="flex:1;">
                <div>Semanas anteriores</div>
                <div class="hor-btn-pagina-actual__sub">Página actual</div>
            </div>
        </div>

        <a href="<?= $basePath ?>/horario" class="hor-btn-siguiente">
            <div style="flex:1;">
                <div>Semana actual</div>
                <div class="hor-btn-siguiente__sub">Solo lectura</div>
            </div>
            <span style="font-size:1.3rem;">▶</span>
        </a>

    </div>

    <!-- ── Encabezado de semana ──────────────────────────── -->
    <div class="hor-semana-header">
        <div class="hor-semana-info">
            <?php if ($semana): ?>
            <h1 class="hor-semana-titulo">
                Semana <?= date('d', strtotime($semana['fecha_inicio'])) ?> –
                <?= date('d \d\e F Y', strtotime($semana['fecha_fin'])) ?>
            </h1>
            <span class="hor-semana-estado estado-cerrada">🔒 Lectura</span>
            <?php endif; ?>
        </div>

        <!-- Filtro por trabajador -->
        <div style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;">
            <label style="font-size:.68rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.07em;white-space:nowrap;">
                Resaltar
            </label>
            <select id="filtroTrabajador"
                    style="padding:.35rem .7rem;border:1.5px solid #e2e8f0;border-radius:8px;font-size:.8rem;outline:none;min-width:160px;cursor:pointer;background:#fff;color:#1e293b;transition:border-color .15s;"
                    onchange="aplicarFiltro(this.value)">
                <option value="">— Todos —</option>
            </select>
            <button onclick="aplicarFiltro('')" id="btnLimpiarFiltro" hidden
                    style="padding:.35rem .75rem;border:1.5px solid #e2e8f0;border-radius:8px;font-size:.75rem;background:#f1f5f9;cursor:pointer;color:#475569;line-height:1;transition:all .15s;">
                ✕
            </button>
        </div>
    </div>

    <div id="horResumen" class="hor-resumen" hidden></div>

    <?php if ($semana): ?>

    <!-- ── Grillas por local (solo lectura) ───────────────── -->
    <?php foreach ($LOCALES as $localId => $localInfo):
        $configLocal = $slotsConfig[$localId] ?? [];
        if (empty($configLocal)) continue;
    ?>
    <section class="hor-sala" style="--sala-color:<?= $localInfo['color'] ?>;">
        <div class="hor-sala__titulo">
            <span class="hor-sala__icono">🏪</span>
            <?= $localInfo['nombre'] ?>
        </div>

        <?php foreach ($TURNOS as $turnoId => $turnoLabel): ?>
        <div class="hor-turno-block">
            <div class="hor-turno-label">
                <?= $turnoId === 1 ? '☀️' : '🌙' ?> <?= $turnoLabel ?>
            </div>
            <div class="hor-grid" data-local="<?= $localId ?>" data-turno="<?= $turnoId ?>">
                <div class="hor-grid__header">
                    <div class="hor-grid__rol-col"></div>
                    <?php foreach ($DIAS as $i => $dia): ?>
                    <div class="hor-grid__dia-header">
                        <span class="hor-dia-nombre"><?= $dia ?></span>
                        <span class="hor-dia-fecha"><?= date('d/m', strtotime($fechasSemana[$i+1])) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php foreach ($configLocal as $rol => $cantidad):
                    for ($n = 1; $n <= $cantidad; $n++):
                        $esOpcional = $roles[$rol]['opcional'] ?? false;
                        $rolColor   = $roles[$rol]['color']    ?? '#e2e8f0';
                ?>
                <div class="hor-grid__row <?= $esOpcional ? 'hor-grid__row--opcional' : '' ?>"
                     style="--rol-color:<?= $rolColor ?>;">
                    <div class="hor-grid__rol-label">
                        <?= htmlspecialchars($roles[$rol]['desc'] ?? $rol) ?>
                        <?php if ($cantidad > 1): ?>
                            <span class="hor-slot-num"><?= $n ?></span>
                        <?php endif; ?>
                        <?php if ($esOpcional): ?>
                            <span class="hor-opcional-badge">opc.</span>
                        <?php endif; ?>
                    </div>
                    <?php for ($dia = 1; $dia <= 7; $dia++): ?>
                    <div class="hor-asiento hor-asiento--libre"
                         id="slot-<?= $semana['id_semana'] ?>-<?= $localId ?>-<?= $turnoId ?>-<?= $dia ?>-<?= $rol ?>-<?= $n ?>"
                         data-semana="<?= $semana['id_semana'] ?>">
                        <span class="hor-asiento__nombre">…</span>
                    </div>
                    <?php endfor; ?>
                </div>
                <?php
                    endfor;
                endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </section>
    <?php endforeach; ?>

    <?php endif; ?>
    <?php endif; ?>

</main>

<script>
const BASE      = '<?= $basePath ?>';
const SEMANA_ID = <?= isset($semana) && $semana ? $semana['id_semana'] : 'null' ?>;
const MI_ID     = <?= $postulanteId ?? (int)($_SESSION['user_id'] ?? 0) ?>;
const ES_ADMIN  = <?= $esAdmin ? 'true' : 'false' ?>;

let _slots    = [];
let _filtroId = 0;

document.addEventListener('DOMContentLoaded', () => {
    if (SEMANA_ID) cargarSlotsHistorial();
});

async function cargarSlotsHistorial() {
    try {
        const r   = await fetch(`${BASE}/horario/api/semana/${SEMANA_ID}`);
        const res = await r.json();
        if (!res.success) return;
        _slots = res.data || [];
        poblarFiltro(_slots);
        renderSlotsHistorial(_slots);
    } catch {}
}

function poblarFiltro(slots) {
    const sel = document.getElementById('filtroTrabajador');
    if (!sel) return;
    const vistos = new Set();
    const opts = [{ id: 0, nombre: '— Todos —' }];
    slots.forEach(s => {
        if (s.postulante_id && !vistos.has(s.postulante_id)) {
            vistos.add(s.postulante_id);
            opts.push({ id: parseInt(s.postulante_id), nombre: s.trabajador_nombre || '—' });
        }
    });
    opts.sort((a, b) => a.nombre.localeCompare(b.nombre));
    sel.innerHTML = opts.map(o => `<option value="${o.id}">${o.nombre}</option>`).join('');
}

function aplicarFiltro(val) {
    _filtroId = parseInt(val) || 0;
    const btn = document.getElementById('btnLimpiarFiltro');
    if (btn) btn.hidden = !_filtroId;
    const sel = document.getElementById('filtroTrabajador');
    if (sel) sel.style.borderColor = _filtroId ? '#2563eb' : '#e2e8f0';
    renderSlotsHistorial(_slots);
}

function renderSlotsHistorial(slots) {
    document.querySelectorAll('.hor-asiento[data-semana]').forEach(el => {
        el.className = 'hor-asiento hor-asiento--libre';
        el.querySelector('.hor-asiento__nombre').textContent = '—';
    });

    const libres = {};
    slots.forEach(s => {
        const id = `slot-${s.semana_id}-${s.local_id}-${s.turno_id}-${s.dia_semana}-${s.rol_puesto}-${s.slot_num}`;
        const el = document.getElementById(id);
        if (!el) return;
        el.dataset.slotid = s.id_slot;

        if (s.postulante_id) {
            const pid    = parseInt(s.postulante_id);
            const esMio  = pid === MI_ID;
            const esFilt = _filtroId && pid === _filtroId && !esMio;
            const cls    = esMio ? 'hor-asiento--mio' : (esFilt ? 'hor-asiento--filtrado' : 'hor-asiento--ocupado');
            el.className = 'hor-asiento ' + cls;
            el.title     = s.trabajador_nombre || '—';
            el.querySelector('.hor-asiento__nombre').textContent = s.trabajador_nombre || '—';
            if (ES_ADMIN) {
                if (parseInt(s.encuestado) === 1) {
                    agregarBtnEncuestadoH(el);
                } else {
                    agregarBtnLiberarH(el, s.trabajador_nombre || '—');
                }
            }
        } else {
            el.className = 'hor-asiento hor-asiento--libre';
            el.querySelector('.hor-asiento__nombre').textContent = '—';
            if (ES_ADMIN) agregarBtnPickerH(el);
            if (s.rol_puesto !== 'ALMACENERA' && s.rol_puesto !== 'LIMPIEZA') {
                libres[s.local_id] = (libres[s.local_id] || 0) + 1;
            }
        }
    });

    const resumen = document.getElementById('horResumen');
    if (resumen) {
        const nombres = { 2: 'SB2', 3: 'SB3', 4: 'SB4' };
        const partes  = Object.entries(nombres).map(([lid, nombre]) => {
            const n     = libres[lid] ?? 0;
            const badge = n > 0
                ? `<span class="hor-resumen__badge hor-resumen__badge--ok">${n} libre${n !== 1 ? 's' : ''}</span>`
                : `<span class="hor-resumen__badge hor-resumen__badge--lleno">completo</span>`;
            return `<span class="hor-resumen__item">${nombre} ${badge}</span>`;
        });
        resumen.innerHTML = `<span class="hor-resumen__label">Semana anterior</span>${partes.join('')}`;
        resumen.hidden = false;
    }
}

// ── Admin: picker y liberar ────────────────────────────
let _histTrabajadores = [];
let _histPickerEl     = null;
let _histLiberarEl    = null;

async function cargarTrabajadoresH() {
    if (!ES_ADMIN || _histTrabajadores.length) return;
    try {
        const r = await fetch(`${BASE}/horario/api/trabajadores`);
        const res = await r.json();
        if (!res.success) return;
        _histTrabajadores = res.data || [];
        const sel = document.getElementById('histPickerSelect');
        if (!sel) return;
        _histTrabajadores.forEach(t => {
            const o = document.createElement('option');
            o.value = t.id; o.textContent = t.nombre;
            sel.appendChild(o);
        });
    } catch {}
}

function agregarBtnPickerH(el) {
    if (el.querySelector('.hor-asiento__btn-picker')) return;
    const btn = document.createElement('button');
    btn.className = 'hor-asiento__btn-picker'; btn.title = 'Asignar'; btn.textContent = '👤';
    btn.addEventListener('click', e => { e.stopPropagation(); _histPickerEl = el; cargarTrabajadoresH(); document.getElementById('histPickerSelect').value=''; document.getElementById('histPickerOverlay').hidden=false; });
    el.appendChild(btn);
}

function agregarBtnLiberarH(el, nombre) {
    if (el.querySelector('.hor-asiento__btn-liberar')) return;
    const btn = document.createElement('button');
    btn.className = 'hor-asiento__btn-liberar'; btn.title = `Quitar a ${nombre}`; btn.textContent = '✕';
    btn.addEventListener('click', e => {
        e.stopPropagation(); _histLiberarEl = el;
        document.getElementById('histLiberarNombre').textContent = `Quitar a ${nombre} de este turno.`;
        document.getElementById('histLiberarPwd').value = '';
        document.getElementById('histLiberarOverlay').hidden = false;
        setTimeout(() => document.getElementById('histLiberarPwd').focus(), 50);
    });
    el.appendChild(btn);
}

function agregarBtnEncuestadoH(el) {
    if (el.querySelector('.hor-asiento__btn-encuestado')) return;
    const btn = document.createElement('button');
    btn.className = 'hor-asiento__btn-encuestado'; btn.title = 'Ya fue encuestado — no se puede quitar'; btn.textContent = '📋';
    el.appendChild(btn);
}

async function histPickerAsignar() {
    const targetId = document.getElementById('histPickerSelect').value;
    if (!targetId) return;
    const el = _histPickerEl;
    document.getElementById('histPickerOverlay').hidden = true; _histPickerEl = null;
    el.classList.add('hor-asiento--loading');
    try {
        const r = await fetch(`${BASE}/horario/api/slot/asignar`, {
            method:'POST', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ slot_id: parseInt(el.dataset.slotid), semana_id: SEMANA_ID, target_id: parseInt(targetId) }),
        });
        const res = await r.json();
        await cargarSlotsHistorial();
        if (!res.success) alert(res.message || 'Error al asignar.');
    } catch { el.classList.remove('hor-asiento--loading'); }
}

async function histLiberarConfirmar() {
    const pwd = document.getElementById('histLiberarPwd').value.trim();
    if (!pwd) return;
    const el = _histLiberarEl;
    document.getElementById('histLiberarOverlay').hidden = true; _histLiberarEl = null;
    el.classList.add('hor-asiento--loading');
    try {
        const r = await fetch(`${BASE}/horario/api/slot/${el.dataset.slotid}/liberar-admin`, {
            method:'POST', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ password: pwd }),
        });
        const res = await r.json();
        await cargarSlotsHistorial();
        if (!res.success) alert(res.message || 'Error al quitar.');
    } catch { el.classList.remove('hor-asiento--loading'); }
}
</script>

<?php if ($esAdmin): ?>
<div id="histPickerOverlay" class="hor-picker-overlay" hidden>
    <div class="hor-picker">
        <div class="hor-picker__header"><h3>Asignar trabajador</h3><button onclick="document.getElementById('histPickerOverlay').hidden=true">✕</button></div>
        <select id="histPickerSelect" class="hor-picker__select"><option value="">— Seleccionar —</option></select>
        <div class="hor-picker__footer">
            <button class="hor-btn" style="background:#f1f5f9;color:#64748b;border:none;" onclick="document.getElementById('histPickerOverlay').hidden=true">Cancelar</button>
            <button class="hor-btn hor-btn--primary" onclick="histPickerAsignar()">Asignar</button>
        </div>
    </div>
</div>
<div id="histLiberarOverlay" class="hor-picker-overlay" hidden>
    <div class="hor-picker">
        <div class="hor-picker__header"><h3>Quitar del turno</h3><button onclick="document.getElementById('histLiberarOverlay').hidden=true">✕</button></div>
        <p id="histLiberarNombre" style="font-size:.85rem;color:#475569;margin:.25rem 0 .75rem;"></p>
        <input id="histLiberarPwd" type="password" class="hor-picker__select" placeholder="Tu contraseña de administrador"
               onkeydown="if(event.key==='Enter') histLiberarConfirmar()">
        <div class="hor-picker__footer">
            <button class="hor-btn" style="background:#f1f5f9;color:#64748b;border:none;" onclick="document.getElementById('histLiberarOverlay').hidden=true">Cancelar</button>
            <button class="hor-btn" style="background:#ef4444;color:#fff;border:none;" onclick="histLiberarConfirmar()">Quitar</button>
        </div>
    </div>
</div>
<?php endif; ?>
</body>
</html>
