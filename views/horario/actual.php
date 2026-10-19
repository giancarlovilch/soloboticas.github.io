<?php
/** @var array|null $semana */ /** @var array|null $semanaProxima */
/** @var string $basePath */ /** @var string $userName */ /** @var bool $esAdmin */

$DIAS         = ['Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sá', 'Do'];
$ROLES        = ['CAJERA' => 'Cajera', 'VENDEDORA' => 'Vendedora', 'ALMACENERA' => 'Almacenera'];
$TURNOS       = [1 => 'Mañana', 2 => 'Tarde'];
$LOCALES      = [
    2 => ['nombre' => 'Local 2', 'color' => '#0097A7'],
    3 => ['nombre' => 'Local 3', 'color' => '#5b21b6'],
    4 => ['nombre' => 'Local 4', 'color' => '#d97706'],
    5 => ['nombre' => 'Administración', 'color' => '#0f172a', 'icono' => '🗂️'],
];
$slotsConfig = $slotsConfig ?? [];
$roles       = $roles       ?? [];

$slotLabels = [
    3 => [
        'VENDEDORA' => [1 => 'sb3', 2 => 'sb5'],
        'CAJERA'    => [1 => 'sb3', 2 => 'sb7', 3 => 'sb5'],
    ],
];

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
    <title>Horario semanal | Solo Boticas</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/horario.css">
    <link rel="icon" type="image/x-icon" href="<?= $basePath ?>/assets/img/logo.ico">
    <style>
        /* Modo solo lectura por defecto */
        .hor-asiento { cursor: default !important; pointer-events: none !important; }
        .hor-asiento:hover { transform: none !important; box-shadow: none !important; }

        /* Modo cubrir: desbloquea slots elegibles */
        body.modo-cubrir .hor-asiento--cubrir-eligible {
            cursor: pointer !important;
            pointer-events: auto !important;
        }
        body.modo-cubrir .hor-asiento--cubrir-eligible:hover {
            transform: translateY(-2px) scale(1.03) !important;
            box-shadow: 0 4px 14px rgba(220,38,38,.35) !important;
        }

        /* Banner de modo cubrir activo */
        #bannerCubrir {
            display: none;
            align-items: center; justify-content: center; gap: .75rem;
            padding: .65rem 1.25rem; border-radius: 10px;
            background: #fef2f2; border: 1.5px solid #fca5a5;
            font-size: .82rem; font-weight: 600; color: #991b1b;
        }
        body.modo-cubrir #bannerCubrir { display: flex; }

    </style>
</head>
<body>

<header class="hor-header">
    <div class="hor-header__brand">
        <div class="hor-header__logo">SB</div>
        <div>
            <p class="hor-header__company">Grupo KGyR S.A.C</p>
            <p class="hor-header__app">Horario semanal</p>
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

    <!-- ── Navegación 3 semanas ──────────────────────────── -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">

        <a href="<?= $basePath ?>/horario/historial"
           style="display:flex;align-items:center;gap:.6rem;padding:.9rem 1.25rem;border-radius:10px;background:#f8fafc;border:1.5px solid #e2e8f0;text-decoration:none;color:#64748b;font-weight:600;font-size:0.85rem;transition:all .15s;"
           onmouseover="this.style.borderColor='#94a3b8'" onmouseout="this.style.borderColor='#e2e8f0'">
            <span style="font-size:1.1rem;">◀</span>
            <div>
                <div>Semanas anteriores</div>
                <div style="font-size:0.72rem;font-weight:400;opacity:.7;margin-top:1px;">Historial — solo lectura</div>
            </div>
        </a>

        <a href="<?= $basePath ?>/horario/siguiente" class="hor-btn-siguiente">
            <div style="flex:1;">
                <div>Próxima semana</div>
                <div class="hor-btn-siguiente__sub">
                    <?php if ($semanaProxima): ?>
                        <?= date('d/m', strtotime($semanaProxima['fecha_inicio'])) ?>
                        — <?= date('d/m/Y', strtotime($semanaProxima['fecha_fin'])) ?>
                        · Editable
                    <?php else: ?>
                        Se generará automáticamente
                    <?php endif; ?>
                </div>
            </div>
            <span style="font-size:1.3rem;">▶</span>
        </a>

    </div>

    <?php if (!$semana): ?>
    <!-- Sin semana vigente -->
    <div class="hor-empty">
        <div class="hor-empty__icon">📅</div>
        <h2>Sin horario esta semana</h2>
        <p>Aún no se ha publicado el horario para la semana en curso.</p>
    </div>

    <?php else: ?>

    <!-- ── Encabezado de semana ──────────────────────────── -->
    <div class="hor-semana-header">
        <div class="hor-semana-info">
            <h1 class="hor-semana-titulo">
                Semana <?= date('d', strtotime($semana['fecha_inicio'])) ?> –
                <?= date('d \d\e F Y', strtotime($semana['fecha_fin'])) ?>
            </h1>
            <span id="badgeEstado" class="hor-semana-estado estado-activo">📅 Activo</span>
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
    <div style="display:flex;justify-content:center;">
        <button id="btnCubrirPuesto" class="hor-btn-cubrir" onclick="toggleModoCubrir()"
                style="width:min(480px,100%);font-size:1.17rem;padding:.1rem 1.5rem;">
            ⇄ CUBRIR / CAMBIAR PUESTO
        </button>
    </div>
    <?php endif; ?>

    <div id="horMsg" class="hor-alert" hidden></div>

    <!-- Banner modo cubrir activo -->
    <div id="bannerCubrir">
        <span>🔓 Modo <strong>Cubrir Puesto</strong> activo — haz clic en cualquier turno de hoy o de días futuros para ubicar a quien quieras</span>
        <button onclick="toggleModoCubrir()"
                style="background:transparent;border:1.5px solid #fca5a5;border-radius:6px;
                       padding:2px 10px;cursor:pointer;font-size:.76rem;color:#991b1b;font-weight:700;">
            ✕ Salir
        </button>
    </div>

    <!-- ── Grillas por local (sin interacción) ────────────── -->
    <?php foreach ($LOCALES as $localId => $localInfo):
        $configLocal = $slotsConfig[$localId] ?? [];
        if (empty($configLocal)) continue;
    ?>
    <section class="hor-sala" style="--sala-color:<?= $localInfo['color'] ?>;">
        <div class="hor-sala__titulo">
            <span class="hor-sala__icono"><?= $localInfo['icono'] ?? '🏪' ?></span>
            <?= $localInfo['nombre'] ?>
        </div>

        <?php foreach ($TURNOS as $turnoId => $turnoLabel): ?>
        <div class="hor-turno-block">
            <div class="hor-turno-label">
                <?= $turnoId === 1 ? '☀️' : '🌙' ?> <?= $turnoLabel ?>
            </div>
            <div class="hor-grid">
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
                    <?php
                        $rolDesc  = htmlspecialchars($roles[$rol]['desc'] ?? $rol);
                        $rolAbrev = [
                            'CAJERA'=>'C','VENDEDORA'=>'V','LIMPIEZA'=>'L','ALMACENERA'=>'A',
                            'ABASTECIMIENTO'=>'Ab','INVENTARIO'=>'In','COMPRAS'=>'Co','AUDITORIA'=>'Au',
                        ][$rol] ?? mb_substr($rolDesc, 0, 1);
                    ?>
                    <div class="hor-grid__rol-label">
                        <span class="hor-rol-full"><?= $rolDesc ?></span>
                        <span class="hor-rol-short"><?= $rolAbrev ?></span>
                        <?php if ($cantidad > 1): $slotLabel = $slotLabels[$localId][$rol][$n] ?? $n; if ($slotLabel !== false): ?><span class="hor-slot-num"><?= $slotLabel ?></span><?php endif; endif; ?>
                    </div>
                    <?php for ($dia = 1; $dia <= 7; $dia++): ?>
                    <div class="hor-asiento hor-asiento--libre"
                         id="slot-<?= $semana['id_semana'] ?>-<?= $localId ?>-<?= $turnoId ?>-<?= $dia ?>-<?= $rol ?>-<?= $n ?>"
                         data-semana="<?= $semana['id_semana'] ?>">
                        <span class="hor-asiento__nombre">…</span>
                    </div>
                    <?php endfor; ?>
                </div>
                <?php endfor; endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </section>
    <?php endforeach; ?>

    <!-- ── Leyenda ─────────────────────────────────────────── -->
    <div class="hor-leyenda">
        <div class="hor-leyenda__item">
            <div class="hor-asiento hor-asiento--mio hor-asiento--demo"></div>
            <span>Tuyo</span>
        </div>
        <div class="hor-leyenda__item">
            <div class="hor-asiento hor-asiento--cobertura hor-asiento--demo"></div>
            <span>Cubriendo (tú)</span>
        </div>
        <div class="hor-leyenda__item">
            <div class="hor-asiento hor-asiento--intercambio hor-asiento--demo"></div>
            <span>Intercambiado (tú)</span>
        </div>
        <div class="hor-leyenda__item">
            <div class="hor-asiento hor-asiento--ocupado hor-asiento--demo"></div>
            <span>Ocupado</span>
        </div>
        <div class="hor-leyenda__item">
            <div class="hor-asiento hor-asiento--libre hor-asiento--demo"></div>
            <span>Libre</span>
        </div>
    </div>

    <?php endif; ?>

</main>

<!-- ── Modal: mover puesto (disponible para todo el staff, hoy y futuro) ── -->
<div id="moverOverlay" class="hor-picker-overlay" hidden>
    <div class="hor-picker">
        <div class="hor-picker__header">
            <h3>⇄ Mover puesto</h3>
            <button onclick="cerrarPickerMover()">✕</button>
        </div>
        <p id="moverActualMsg" style="font-size:.82rem;color:#475569;margin:.1rem 0 .4rem;"></p>
        <select id="moverSelect" class="hor-picker__select">
            <option value="">— Dejar libre —</option>
        </select>
        <div class="hor-picker__footer">
            <button class="hor-btn" style="background:#f1f5f9;color:#64748b;border:none;"
                    onclick="cerrarPickerMover()">Cancelar</button>
            <button class="hor-btn hor-btn--primary" onclick="confirmarMover()">Confirmar</button>
        </div>
    </div>
</div>

<script>
const BASE      = '<?= $basePath ?>';
const SEMANA_ID = <?= $semana ? $semana['id_semana'] : 'null' ?>;
const MI_ID     = <?= $postulanteId ?? (int)($_SESSION['user_id'] ?? 0) ?>;
const HOY       = '<?= date('Y-m-d') ?>';
const ES_ADMIN  = <?= $esAdmin ? 'true' : 'false' ?>;

let _slots              = [];
let _filtroId           = 0;
let _modoCubrir         = false;
let _slotParaMover      = null;

document.addEventListener('DOMContentLoaded', () => {
    if (SEMANA_ID) cargarSlots();
});

// ── Toast ──────────────────────────────────────────────
let _toastTimer = null;
function mostrarToast(txt, tipo = 'error') {
    let toast = document.getElementById('horToast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'horToast';
        document.body.appendChild(toast);
    }
    toast.textContent = txt;
    toast.className   = `hor-toast hor-toast--${tipo}`;
    void toast.offsetWidth;
    toast.classList.add('hor-toast--visible');
    clearTimeout(_toastTimer);
    _toastTimer = setTimeout(() => toast.classList.remove('hor-toast--visible'), 2400);
}

// ── Carga de slots ─────────────────────────────────────
async function cargarSlots() {
    try {
        const r   = await fetch(`${BASE}/horario/api/semana/${SEMANA_ID}`);
        const res = await r.json();
        if (!res.success) return;
        _slots = res.data || [];
        poblarFiltro(_slots);
        renderSlots(_slots);
    } catch {}
}

// ── Filtro por trabajador ──────────────────────────────
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
    renderSlots(_slots);
}

// ── Render ─────────────────────────────────────────────
function renderSlots(slots) {
    document.querySelectorAll('.hor-asiento[data-semana]').forEach(el => {
        el.className = 'hor-asiento hor-asiento--libre';
        el.querySelector('.hor-asiento__nombre').textContent = '—';
        el.onclick = null;
        el.title   = '';
    });

    const libres = {};
    slots.forEach(s => {
        const id = `slot-${s.semana_id}-${s.local_id}-${s.turno_id}-${s.dia_semana}-${s.rol_puesto}-${s.slot_num}`;
        const el = document.getElementById(id);
        if (!el) return;
        el.dataset.slotid = s.id_slot;

        if (s.postulante_id) {
            const pid              = parseInt(s.postulante_id);
            const esMio            = pid === MI_ID;
            const solSol           = parseInt(s.solicitud_solicitante_id) || 0;
            const esCoberturaMia   = s.solicitud_tipo === 'COBERTURA'    && solSol === MI_ID && esMio;
            const esMiCambio       = s.solicitud_tipo === 'CAMBIO'       && solSol === MI_ID && esMio;
            const esMiIntercambio  = s.solicitud_tipo === 'INTERCAMBIO'  && solSol === MI_ID && esMio;
            const esFilt           = _filtroId && pid === _filtroId && !esMio;

            let cls;
            if (esCoberturaMia)     cls = 'hor-asiento--cobertura';
            else if (esMiIntercambio) cls = 'hor-asiento--intercambio';
            else if (esMio)         cls = 'hor-asiento--mio';
            else if (esFilt)        cls = 'hor-asiento--filtrado';
            else                    cls = 'hor-asiento--ocupado';

            el.className = 'hor-asiento ' + cls;
            el.title     = s.trabajador_nombre || '—';
            el.querySelector('.hor-asiento__nombre').textContent = s.trabajador_nombre || '—';

            // Días pasados solo los puede tocar el admin
            const esPasado = s.fecha_dia < HOY;
            if (_modoCubrir && (!esPasado || ES_ADMIN)) {
                el.classList.add('hor-asiento--cubrir-eligible');
                el.title   = `Mover este puesto (hoy: ${s.trabajador_nombre || '—'})`;
                el.onclick = () => abrirPickerMover(s);
            }
        } else {
            el.className = 'hor-asiento hor-asiento--libre';
            if (s.rol_puesto === 'LIMPIEZA') {
                el.querySelector('.hor-asiento__nombre').innerHTML =
                    '<span style="font-size:1.25em;line-height:1;letter-spacing:1px;">🧹🫧🧼</span>';
            } else {
                el.querySelector('.hor-asiento__nombre').textContent = '＋';
            }
            if (!['ALMACENERA','LIMPIEZA','ABASTECIMIENTO','INVENTARIO','COMPRAS','AUDITORIA'].includes(s.rol_puesto)) {
                libres[s.local_id] = (libres[s.local_id] || 0) + 1;
            }

            // Slots libres: asignables en modo cubrir, a cualquiera (días pasados solo admin)
            const esPasado = s.fecha_dia < HOY;
            if (_modoCubrir && (!esPasado || ES_ADMIN)) {
                el.classList.add('hor-asiento--cubrir-eligible');
                el.title   = 'Turno libre — clic para asignar';
                el.onclick = () => abrirPickerMover(s);
            }
        }
    });

    // Resumen disponibles
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
        resumen.innerHTML = `<span class="hor-resumen__label">Disponibles esta semana</span>${partes.join('')}`;
        resumen.hidden = false;
    }
}

// ── Modo Cubrir Puesto ─────────────────────────────────
function toggleModoCubrir() {
    _modoCubrir = !_modoCubrir;
    document.body.classList.toggle('modo-cubrir', _modoCubrir);

    const btn    = document.getElementById('btnCubrirPuesto');
    const badge  = document.getElementById('badgeEstado');
    if (btn) {
        btn.classList.toggle('activo', _modoCubrir);
        btn.textContent = _modoCubrir ? '✕ Salir del modo cubrir' : '⇄ CUBRIR / CAMBIAR PUESTO';
    }
    if (badge) {
        badge.textContent = _modoCubrir ? '🔓 Modo cubrir activo' : '📅 Activo';
        badge.className   = _modoCubrir
            ? 'hor-semana-estado estado-abierta'
            : 'hor-semana-estado estado-activo';
    }

    renderSlots(_slots);
}

// ── Picker universal: mover cualquier puesto a cualquiera ──────
let _trabajadoresCache = [];

async function cargarTrabajadores() {
    if (_trabajadoresCache.length) return _trabajadoresCache;
    try {
        const r   = await fetch(`${BASE}/horario/api/trabajadores`);
        const res = await r.json();
        if (res.success) _trabajadoresCache = res.data || [];
    } catch {}
    return _trabajadoresCache;
}

async function abrirPickerMover(s) {
    _slotParaMover = s;
    const trabajadores = await cargarTrabajadores();

    document.getElementById('moverActualMsg').textContent = s.postulante_id
        ? `Ahora está: ${s.trabajador_nombre || '—'}`
        : 'Este turno está libre.';

    const sel = document.getElementById('moverSelect');
    sel.innerHTML = '<option value="">— Dejar libre —</option>' +
        trabajadores
            .filter(t => parseInt(t.id) !== parseInt(s.postulante_id))
            .map(t => `<option value="${t.id}">${t.nombre}</option>`)
            .join('');
    sel.value = '';

    document.getElementById('moverOverlay').hidden = false;
}

function cerrarPickerMover() {
    document.getElementById('moverOverlay').hidden = true;
    _slotParaMover = null;
}

async function confirmarMover() {
    const s = _slotParaMover;
    const nuevoId = document.getElementById('moverSelect').value;
    cerrarPickerMover();

    try {
        const r   = await fetch(`${BASE}/horario/api/slot/mover`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({
                slot_id: parseInt(s.id_slot),
                nuevo_postulante_id: nuevoId ? parseInt(nuevoId) : null,
            }),
        });
        const res = await r.json();
        await cargarSlots();
        if (res.success) mostrarToast(nuevoId ? '¡Turno reasignado!' : 'Turno liberado.', 'ok');
        else             mostrarToast(res.message || 'No se pudo mover el turno.', 'error');
    } catch {
        mostrarToast('Error de conexión.', 'error');
    }
}

</script>
</body>
</html>
