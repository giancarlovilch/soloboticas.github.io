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

        /* Botón próxima semana */
        .hor-btn-siguiente {
            display: flex; align-items: center; justify-content: center; gap: .75rem;
            width: 100%; padding: 1.1rem 2rem; border-radius: 12px;
            background: linear-gradient(135deg, #0097A7, #00BCD4);
            color: #fff; font-size: 1.05rem; font-weight: 700;
            text-decoration: none; border: none; cursor: pointer;
            box-shadow: 0 4px 16px rgba(0,151,167,.35);
            transition: transform .15s, box-shadow .15s;
        }
        .hor-btn-siguiente:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 24px rgba(0,151,167,.45);
        }
        .hor-btn-siguiente__icon { font-size: 1.4rem; }
        .hor-btn-siguiente__sub  { font-size: 0.75rem; opacity: .85; font-weight: 400; margin-top: 2px; }
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
        <a href="<?= $basePath ?>/horario/informacion" class="hor-btn hor-btn--outline" style="font-size:0.78rem;">ℹ️<span class="hor-btn-txt"> Info</span></a>
        <a href="<?= $basePath ?>/horario/historial" class="hor-btn hor-btn--outline" style="font-size:0.78rem;">📂<span class="hor-btn-txt"> Historial</span></a>
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
            <span id="badgeEstado" class="hor-semana-estado estado-cerrada">🔒 Solo lectura</span>
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

        <div id="horResumen" class="hor-resumen" hidden></div>
    </div>

    <?php if ($semana): ?>
    <div style="display:flex;justify-content:center;">
        <button id="btnCubrirPuesto" class="hor-btn-cubrir" onclick="toggleModoCubrir()"
                style="width:min(480px,100%);font-size:1.17rem;padding:.6rem 1.5rem;">
            🤝 CUBRIR PUESTO
        </button>
    </div>
    <?php endif; ?>

    <div id="horMsg" class="hor-alert" hidden></div>

    <!-- Banner modo cubrir activo -->
    <div id="bannerCubrir">
        <span>🔓 Modo <strong>Cubrir Puesto</strong> activo — haz clic en el turno de tu compañera para reemplazarla</span>
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
            <span class="hor-sala__icono">🏪</span>
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
                        $rolAbrev = ['CAJERA'=>'C','VENDEDORA'=>'V','LIMPIEZA'=>'L','ALMACENERA'=>'A'][$rol] ?? mb_substr($rolDesc, 0, 1);
                    ?>
                    <div class="hor-grid__rol-label">
                        <span class="hor-rol-full"><?= $rolDesc ?></span>
                        <span class="hor-rol-short"><?= $rolAbrev ?></span>
                        <?php if ($cantidad > 1): ?><span class="hor-slot-num"><?= $n ?></span><?php endif; ?>
                        <?php if ($esOpcional): ?><span class="hor-opcional-badge">opc.</span><?php endif; ?>
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

<!-- ── Modal: cubrir / tomar puesto ─────────────────────── -->
<div id="cubrirOverlay" class="hor-picker-overlay" hidden>
    <div class="hor-picker">
        <div class="hor-picker__header">
            <h3 id="cubrirTitulo">🤝 Cubrir Puesto</h3>
            <button onclick="cerrarModalCubrir()">✕</button>
        </div>
        <p id="cubrirPreMsg" style="font-size:.82rem;color:#475569;margin:.1rem 0 .4rem;">
            Cubrirás el turno de:
        </p>
        <div id="cubrirNombre"
             style="font-size:.95rem;font-weight:700;
                    border-radius:8px;padding:.55rem .85rem;"></div>
        <p id="cubrirExtraMsg" style="font-size:.77rem;color:#64748b;line-height:1.5;margin-top:.4rem;"></p>
        <input id="cubrirPwd" type="password" class="hor-picker__select"
               placeholder="Confirma con tu contraseña"
               onkeydown="if(event.key==='Enter') confirmarCubrir()">
        <div class="hor-picker__footer">
            <button class="hor-btn" style="background:#f1f5f9;color:#64748b;border:none;"
                    onclick="cerrarModalCubrir()">Cancelar</button>
            <button id="cubrirConfirmBtn" class="hor-btn" style="background:#dc2626;color:#fff;border:none;"
                    onclick="confirmarCubrir()">Confirmar</button>
        </div>
    </div>
</div>

<!-- ── Modal: revertir cobertura propia ─────────────────── -->
<div id="revertirOverlay" class="hor-picker-overlay" hidden>
    <div class="hor-picker">
        <div class="hor-picker__header">
            <h3>↩ Revertir cobertura</h3>
            <button onclick="cerrarModalRevertir()">✕</button>
        </div>
        <p id="revertirMsg"
           style="font-size:.85rem;color:#475569;line-height:1.5;margin:.25rem 0;"></p>
        <div class="hor-picker__footer">
            <button class="hor-btn" style="background:#f1f5f9;color:#64748b;border:none;"
                    onclick="cerrarModalRevertir()">Cancelar</button>
            <button class="hor-btn" style="background:#dc2626;color:#fff;border:none;"
                    onclick="confirmarRevertir()">Sí, revertir</button>
        </div>
    </div>
</div>

<script>
const BASE      = '<?= $basePath ?>';
const SEMANA_ID = <?= $semana ? $semana['id_semana'] : 'null' ?>;
const MI_ID     = <?= $postulanteId ?? (int)($_SESSION['user_id'] ?? 0) ?>;
const HOY       = '<?= date('Y-m-d') ?>';

let _slots          = [];
let _filtroId       = 0;
let _modoCubrir     = false;
let _slotParaCubrir  = null;
let _slotParaRevertir = null;

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

        if (s.postulante_id) {
            const pid            = parseInt(s.postulante_id);
            const esMio          = pid === MI_ID;
            const solSol         = parseInt(s.solicitud_solicitante_id) || 0;
            const esCoberturaMia = s.solicitud_tipo === 'COBERTURA' && solSol === MI_ID && esMio;
            const esMiCambio     = s.solicitud_tipo === 'CAMBIO'    && solSol === MI_ID && esMio;
            const esFilt         = _filtroId && pid === _filtroId && !esMio;

            let cls;
            if (esCoberturaMia)      cls = 'hor-asiento--cobertura';
            else if (esMio)          cls = 'hor-asiento--mio';
            else if (esFilt)         cls = 'hor-asiento--filtrado';
            else                     cls = 'hor-asiento--ocupado';

            el.className = 'hor-asiento ' + cls;
            el.title     = s.trabajador_nombre || '—';
            el.querySelector('.hor-asiento__nombre').textContent = s.trabajador_nombre || '—';

            const esPasado = s.fecha_dia < HOY;
            if (_modoCubrir && !esPasado) {
                if (esCoberturaMia) {
                    // Slot que yo estoy cubriendo → puedo revertir
                    el.classList.add('hor-asiento--cubrir-eligible');
                    el.title   = 'Estás cubriendo este turno — clic para revertir';
                    el.onclick = () => abrirModalRevertir(s);
                } else if (esMiCambio) {
                    // Slot libre que yo tomé → puedo liberar
                    el.classList.add('hor-asiento--cubrir-eligible');
                    el.title   = 'Tomaste este turno — clic para liberar';
                    el.onclick = () => abrirModalRevertir(s);
                } else if (!esMio) {
                    // Slot de otro o libre-de-otro → puedo cubrir
                    el.classList.add('hor-asiento--cubrir-eligible');
                    el.title   = `Cubrir a ${s.trabajador_nombre || 'este compañero'}`;
                    el.onclick = () => abrirModalCubrir(s);
                }
            }
        } else {
            el.className = 'hor-asiento hor-asiento--libre';
            if (s.rol_puesto === 'LIMPIEZA') {
                el.querySelector('.hor-asiento__nombre').innerHTML =
                    '<span style="font-size:1.25em;line-height:1;letter-spacing:1px;">🧹🫧🧼</span>';
            } else {
                el.querySelector('.hor-asiento__nombre').textContent = '＋';
            }
            if (s.rol_puesto !== 'ALMACENERA' && s.rol_puesto !== 'LIMPIEZA') {
                libres[s.local_id] = (libres[s.local_id] || 0) + 1;
            }

            // Slots libres también son tomables en modo cubrir (solo hoy/futuro)
            const esPasado = s.fecha_dia < HOY;
            if (_modoCubrir && !esPasado) {
                el.classList.add('hor-asiento--cubrir-eligible');
                el.title   = 'Turno libre — clic para tomar';
                el.onclick = () => abrirModalCubrir(s);
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
        btn.textContent = _modoCubrir ? '✕ Salir del modo cubrir' : '🤝 CUBRIR PUESTO';
    }
    if (badge) {
        badge.textContent = _modoCubrir ? '🔓 Modo cubrir activo' : '🔒 Solo lectura';
        badge.className   = _modoCubrir
            ? 'hor-semana-estado estado-abierta'
            : 'hor-semana-estado estado-cerrada';
    }

    renderSlots(_slots);
}

// ── Modal: cubrir / tomar ──────────────────────────────
function abrirModalCubrir(s) {
    _slotParaCubrir = s;
    const tieneOcupante = !!s.postulante_id;
    const nombre = document.getElementById('cubrirNombre');

    if (tieneOcupante) {
        document.getElementById('cubrirTitulo').textContent    = '🤝 Cubrir Puesto';
        document.getElementById('cubrirPreMsg').textContent    = 'Cubrirás el turno de:';
        nombre.textContent   = s.trabajador_nombre || '—';
        nombre.style.color   = '#991b1b';
        nombre.style.background = '#fee2e2';
        nombre.style.border  = '1px solid #fca5a5';
        document.getElementById('cubrirExtraMsg').textContent  =
            'Tu nombre reemplazará al suyo. Puedes revertirlo haciendo clic en tu nombre (rojo).';
        document.getElementById('cubrirConfirmBtn').textContent = 'Confirmar cobertura';
    } else {
        document.getElementById('cubrirTitulo').textContent    = '✅ Tomar Turno Libre';
        document.getElementById('cubrirPreMsg').textContent    = 'Tomarás este turno:';
        nombre.textContent   = '— Libre —';
        nombre.style.color   = '#065f46';
        nombre.style.background = '#d1fae5';
        nombre.style.border  = '1px solid #a7f3d0';
        document.getElementById('cubrirExtraMsg').textContent  =
            'Tu nombre aparecerá en el horario. Puedes liberarlo haciendo clic en tu nombre después.';
        document.getElementById('cubrirConfirmBtn').textContent = 'Confirmar';
    }

    document.getElementById('cubrirPwd').value = '';
    document.getElementById('cubrirOverlay').hidden = false;
    setTimeout(() => document.getElementById('cubrirPwd').focus(), 60);
}

function cerrarModalCubrir() {
    document.getElementById('cubrirOverlay').hidden = true;
    _slotParaCubrir = null;
}

async function confirmarCubrir() {
    const password = document.getElementById('cubrirPwd').value.trim();
    if (!password) { mostrarToast('Ingresa tu contraseña', 'info'); return; }

    const s = _slotParaCubrir;
    cerrarModalCubrir();

    try {
        const r   = await fetch(`${BASE}/horario/api/solicitud/cubrir`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ slot_id: parseInt(s.id_slot), password }),
        });
        const res = await r.json();
        await cargarSlots();
        if (res.success) mostrarToast('¡Turno cubierto correctamente!', 'ok');
        else             mostrarToast(res.message || 'No se pudo cubrir el turno.', 'error');
    } catch {
        mostrarToast('Error de conexión.', 'error');
    }
}

// ── Modal: revertir (cobertura o turno libre tomado) ──
function abrirModalRevertir(s) {
    _slotParaRevertir = s;
    let msg;
    if (s.solicitud_tipo === 'COBERTURA') {
        const orig = s.original_nombre || 'tu compañera';
        msg = `¿Quieres dejar de cubrir este turno? ${orig} recuperará su puesto.`;
    } else {
        msg = '¿Quieres liberar este turno? Quedará disponible para otras compañeras.';
    }
    document.getElementById('revertirMsg').textContent = msg;
    document.getElementById('revertirOverlay').hidden = false;
}

function cerrarModalRevertir() {
    document.getElementById('revertirOverlay').hidden = true;
    _slotParaRevertir = null;
}

async function confirmarRevertir() {
    const s           = _slotParaRevertir;
    const solicitudId = s.id_solicitud_activa;
    cerrarModalRevertir();

    try {
        const r   = await fetch(`${BASE}/horario/api/solicitud/${solicitudId}/revertir-propia`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
        });
        const res = await r.json();
        await cargarSlots();
        if (res.success) mostrarToast('Cobertura revertida.', 'ok');
        else             mostrarToast(res.message || 'No se pudo revertir.', 'error');
    } catch {
        mostrarToast('Error de conexión.', 'error');
    }
}
</script>
</body>
</html>
