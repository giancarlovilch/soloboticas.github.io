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
        /* Modo solo lectura: sin interacción */
        .hor-asiento { cursor: default !important; pointer-events: none !important; }
        .hor-asiento:hover { transform: none !important; box-shadow: none !important; }

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
        <a href="<?= $basePath ?>/horario/solicitudes" class="hor-btn hor-btn--outline" style="font-size:0.78rem;">📋<span class="hor-btn-txt"> Solicitudes</span></a>
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
            <span class="hor-semana-estado estado-cerrada">🔒 Solo lectura</span>
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

    <div id="horMsg" class="hor-alert" hidden></div>

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
            <div class="hor-asiento hor-asiento--ocupado hor-asiento--demo"></div>
            <span>Asignado</span>
        </div>
        <div class="hor-leyenda__item">
            <div class="hor-asiento hor-asiento--libre hor-asiento--demo"></div>
            <span>Libre</span>
        </div>
    </div>

    <?php endif; ?>

</main>

<script>
const BASE      = '<?= $basePath ?>';
const SEMANA_ID = <?= $semana ? $semana['id_semana'] : 'null' ?>;
const MI_ID     = <?= $postulanteId ?? (int)($_SESSION['user_id'] ?? 0) ?>;

let _slots   = [];
let _filtroId = 0;

document.addEventListener('DOMContentLoaded', () => {
    if (SEMANA_ID) cargarSlots();
});

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
    sel.innerHTML = opts.map(o =>
        `<option value="${o.id}">${o.nombre}</option>`
    ).join('');
}

function aplicarFiltro(val) {
    _filtroId = parseInt(val) || 0;
    const btn = document.getElementById('btnLimpiarFiltro');
    if (btn) btn.hidden = !_filtroId;
    const sel = document.getElementById('filtroTrabajador');
    if (sel) sel.style.borderColor = _filtroId ? '#2563eb' : '#e2e8f0';
    renderSlots(_slots);
}

function renderSlots(slots) {
    document.querySelectorAll('.hor-asiento[data-semana]').forEach(el => {
        el.className = 'hor-asiento hor-asiento--libre';
        el.querySelector('.hor-asiento__nombre').textContent = '—';
    });

    const libres = {};
    slots.forEach(s => {
        const id = `slot-${s.semana_id}-${s.local_id}-${s.turno_id}-${s.dia_semana}-${s.rol_puesto}-${s.slot_num}`;
        const el = document.getElementById(id);
        if (!el) return;

        if (s.postulante_id) {
            const pid    = parseInt(s.postulante_id);
            const esMio  = pid === MI_ID;
            const esFilt = _filtroId && pid === _filtroId && !esMio;
            const cls    = esMio ? 'hor-asiento--mio' : (esFilt ? 'hor-asiento--filtrado' : 'hor-asiento--ocupado');
            el.className = 'hor-asiento ' + cls;
            el.title     = s.trabajador_nombre || '—';
            el.querySelector('.hor-asiento__nombre').textContent = s.trabajador_nombre || '—';
        } else {
            el.className = 'hor-asiento hor-asiento--libre';
            el.querySelector('.hor-asiento__nombre').textContent = '—';
            if (s.rol_puesto !== 'ALMACENERA') {
                const lid = s.local_id;
                libres[lid] = (libres[lid] || 0) + 1;
            }
        }
    });

    // Resumen disponibles
    const el = document.getElementById('horResumen');
    if (el) {
        const nombres = { 2: 'SB2', 3: 'SB3', 4: 'SB4' };
        const partes  = Object.entries(nombres).map(([lid, nombre]) => {
            const n     = libres[lid] ?? 0;
            const badge = n > 0
                ? `<span class="hor-resumen__badge hor-resumen__badge--ok">${n} libre${n !== 1 ? 's' : ''}</span>`
                : `<span class="hor-resumen__badge hor-resumen__badge--lleno">completo</span>`;
            return `<span class="hor-resumen__item">${nombre} ${badge}</span>`;
        });
        el.innerHTML = `<span class="hor-resumen__label">Disponibles esta semana</span>${partes.join('')}`;
        el.hidden = false;
    }
}
</script>
</body>
</html>
