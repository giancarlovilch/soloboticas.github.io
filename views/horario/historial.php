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
        .hor-readonly-banner {
            display: flex; align-items: center; gap: .5rem;
            padding: .55rem 1rem; border-radius: 8px;
            background: #f8fafc; border: 1px solid #e2e8f0;
            font-size: 0.78rem; color: #64748b; font-weight: 500;
        }
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
        <a href="<?= $basePath ?>/horario" class="hor-btn-back">← Semana actual</a>
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

    <!-- ── Navegación de semanas pasadas ──────────────────── -->
    <div class="hor-semana-header">
        <div class="hor-semana-info">
            <?php if ($semana): ?>
            <h1 class="hor-semana-titulo">
                Semana <?= date('d', strtotime($semana['fecha_inicio'])) ?> –
                <?= date('d \d\e F Y', strtotime($semana['fecha_fin'])) ?>
            </h1>
            <span class="hor-semana-estado estado-cerrada">🔒 Cerrada</span>
            <?php endif; ?>
        </div>
        <div class="hor-semana-nav">
            <?php foreach ($semanas as $s): ?>
                <a href="?semana=<?= $s['id_semana'] ?>"
                   class="hor-semana-pill <?= isset($semana) && $s['id_semana'] == $semana['id_semana'] ? 'active' : '' ?>">
                    <?= date('d/m', strtotime($s['fecha_inicio'])) ?> –
                    <?= date('d/m', strtotime($s['fecha_fin'])) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="hor-readonly-banner">
        🔒 Modo solo lectura — Los horarios pasados no pueden ser modificados.
    </div>

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

document.addEventListener('DOMContentLoaded', () => {
    if (SEMANA_ID) cargarSlotsHistorial();
});

async function cargarSlotsHistorial() {
    try {
        const r   = await fetch(`${BASE}/horario/api/semana/${SEMANA_ID}`);
        const res = await r.json();
        if (!res.success) return;
        renderSlotsHistorial(res.data || []);
    } catch {}
}

function renderSlotsHistorial(slots) {
    // Resetear a vacío
    document.querySelectorAll('.hor-asiento[data-semana]').forEach(el => {
        el.className = 'hor-asiento hor-asiento--libre';
        el.querySelector('.hor-asiento__nombre').textContent = '—';
    });

    slots.forEach(s => {
        const id = `slot-${s.semana_id}-${s.local_id}-${s.turno_id}-${s.dia_semana}-${s.rol_puesto}-${s.slot_num}`;
        const el = document.getElementById(id);
        if (!el) return;

        if (s.postulante_id) {
            const nombre = s.trabajador_nombre || '—';
            el.className = 'hor-asiento hor-asiento--ocupado';
            el.title     = nombre;
            el.querySelector('.hor-asiento__nombre').textContent = nombre;
        } else {
            el.className = 'hor-asiento hor-asiento--libre';
            el.querySelector('.hor-asiento__nombre').textContent = '—';
        }
    });
}
</script>
</body>
</html>
