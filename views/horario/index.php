<?php
$basePath     = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
$userName     = $userName  ?? $_SESSION['user_name'] ?? 'Usuario';
$userRol      = $userRol   ?? $_SESSION['user_rol']  ?? 'STAFF';
$esAdmin      = $esAdmin   ?? false;
$semana       = $semana    ?? null;
$semanas      = $semanas   ?? [];
$postulanteId = $postulanteId ?? (int)($_SESSION['user_id'] ?? 0);

$DIAS        = ['Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sá', 'Do'];
$TURNOS      = [1 => 'Mañana', 2 => 'Tarde'];
$LOCALES     = [
    2 => ['nombre' => 'Local 2', 'color' => '#0097A7'],
    3 => ['nombre' => 'Local 3', 'color' => '#5b21b6'],
    4 => ['nombre' => 'Local 4', 'color' => '#d97706'],
    5 => ['nombre' => 'Administración', 'color' => '#0f172a', 'icono' => '🗂️'],
];
// $slotsConfig y $roles vienen del controller (desde BD)
$slotsConfig = $slotsConfig ?? [];
$roles       = $roles       ?? [];

$slotLabels = [
    3 => [
        'VENDEDORA' => [1 => 'sb3', 2 => 'sb5'],
        'CAJERA'    => [1 => 'sb3', 2 => 'sb7', 3 => 'sb5'],
    ],
];

// Formatear fechas de la semana
$fechasSemana = [];
if ($semana) {
    $inicio = new DateTime($semana['fecha_inicio']);
    for ($d = 0; $d < 7; $d++) {
        $dia = clone $inicio;
        $dia->modify("+{$d} days");
        $fechasSemana[$d + 1] = $dia->format('Y-m-d'); // dia_semana => fecha
    }
}

// Estructurar slots por [local][turno][rol][slot_num][dia_semana]
$grid = [];
if ($semana) {
    $slots = []; // Se cargarán vía JS
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Horarios Semanales | Solo Boticas</title>
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
<body<?= $esAdmin ? ' class="es-admin"' : '' ?>>

<header class="hor-header">
    <div class="hor-header__brand">
        <div class="hor-header__logo">SB</div>
        <div>
            <p class="hor-header__company">Grupo KGyR S.A.C</p>
            <p class="hor-header__app">
                Próxima semana
                <?php if ($semana): ?>
                · <?= date('d/m', strtotime($semana['fecha_inicio'])) ?> – <?= date('d/m/Y', strtotime($semana['fecha_fin'])) ?>
                <?php endif; ?>
            </p>
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

    <!-- ── Navegación ─────────────────────────────────────── -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">

        <a href="<?= $basePath ?>/horario"
           style="display:flex;align-items:center;gap:.6rem;padding:.9rem 1.25rem;border-radius:10px;background:#f8fafc;border:1.5px solid #e2e8f0;text-decoration:none;color:#64748b;font-weight:600;font-size:0.85rem;transition:all .15s;"
           onmouseover="this.style.borderColor='#94a3b8'" onmouseout="this.style.borderColor='#e2e8f0'">
            <span style="font-size:1.1rem;">◀</span>
            <div>
                <div>Semana actual</div>
                <div style="font-size:0.72rem;font-weight:400;opacity:.7;margin-top:1px;">Solo lectura</div>
            </div>
        </a>

        <div class="hor-btn-pagina-actual" style="justify-content:flex-end;">
            <div style="flex:1;text-align:right;">
                <div>Próxima semana</div>
                <div class="hor-btn-pagina-actual__sub">Página actual</div>
            </div>
            <span style="font-size:1.3rem;">▶</span>
        </div>

    </div>

    <?php if (!$semana): ?>
    <!-- Sin semana disponible -->
    <div class="hor-empty">
        <div class="hor-empty__icon">📅</div>
        <h2>Sin semana activa</h2>
        <p>No hay ninguna semana de horarios disponible.</p>
        <?php if ($esAdmin): ?>
        <button class="hor-btn hor-btn--primary" onclick="crearProximaSemana()">
            Crear próxima semana
        </button>
        <?php else: ?>
        <p style="color:#94a3b8;font-size:0.82rem;">El administrador aún no ha creado el horario de la próxima semana.</p>
        <?php endif; ?>
    </div>

    <?php else: ?>

    <!-- ── Encabezado de semana ──────────────────────────── -->
    <div class="hor-semana-header">
        <div class="hor-semana-info">
            <h1 class="hor-semana-titulo">
                Semana
                <?= date('d', strtotime($semana['fecha_inicio'])) ?> –
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

    <!-- ── Resumen de disponibles ──────────────────────────── -->
    <div id="horResumen" class="hor-resumen" hidden></div>

    <div style="display:flex;justify-content:center;">
        <button id="btnCubrirPuesto" class="hor-btn-cubrir" onclick="toggleModoCubrir()"
                style="width:min(480px,100%);font-size:1.17rem;padding:.1rem 1.5rem;">
            ⇄ CUBRIR / CAMBIAR PUESTO
        </button>
    </div>

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

    <!-- ── Grillas por local ──────────────────────────────── -->
    <?php foreach ($LOCALES as $localId => $localInfo):
        $configLocal = $slotsConfig[$localId] ?? [];
        if (empty($configLocal)) continue;
    ?>
    <section class="hor-sala" style="--sala-color:<?= $localInfo['color'] ?>;">
        <div class="hor-sala__titulo">
            <span class="hor-sala__icono"><?= $localInfo['icono'] ?? '🏪' ?></span>
            <?= $localInfo['nombre'] ?>
            <button onclick="cargarSlots()"
                    title="Actualizar turnos"
                    style="margin-left:auto;background:transparent;border:1px solid currentColor;border-radius:5px;padding:2px 8px;font-size:0.72rem;cursor:pointer;color:var(--sala-color);opacity:.7;line-height:1.4;">
                🔄 Actualizar
            </button>
        </div>

        <?php foreach ($TURNOS as $turnoId => $turnoLabel): ?>
        <div class="hor-turno-block">
            <div class="hor-turno-label">
                <?= $turnoId === 1 ? '☀️' : '🌙' ?> <?= $turnoLabel ?>
            </div>

            <!-- Grid de días (cabecera) -->
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

                <!-- Filas de slots (un rol por fila) -->
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
                        <?php if ($cantidad > 1): $slotLabel = $slotLabels[$localId][$rol][$n] ?? $n; if ($slotLabel !== false): ?>
                            <span class="hor-slot-num"><?= $slotLabel ?></span>
                        <?php endif; endif; ?>
                    </div>

                    <!-- Celdas de cada día -->
                    <?php for ($dia = 1; $dia <= 7; $dia++): ?>
                    <div class="hor-asiento"
                         id="slot-<?= $semana['id_semana'] ?>-<?= $localId ?>-<?= $turnoId ?>-<?= $dia ?>-<?= $rol ?>-<?= $n ?>"
                         data-semana="<?= $semana['id_semana'] ?>"
                         data-local="<?= $localId ?>"
                         data-turno="<?= $turnoId ?>"
                         data-dia="<?= $dia ?>"
                         data-rol="<?= $rol ?>"
                         data-slotnum="<?= $n ?>"
                         data-slotid=""
                         data-ocupado-por=""
                         title="">
                        <span class="hor-asiento__nombre">…</span>
                    </div>
                    <?php endfor; ?>

                </div>
                <?php
                    endfor;
                endforeach; ?>

            </div><!-- /.hor-grid -->
        </div><!-- /.hor-turno-block -->
        <?php endforeach; ?>

    </section>
    <?php endforeach; ?>

    <!-- ── Leyenda ────────────────────────────────────────── -->
    <div class="hor-leyenda">
        <div class="hor-leyenda__item">
            <div class="hor-asiento hor-asiento--libre hor-asiento--demo"></div>
            <span>Libre</span>
        </div>
        <div class="hor-leyenda__item">
            <div class="hor-asiento hor-asiento--mio hor-asiento--demo"></div>
            <span>Tuyo</span>
        </div>
        <div class="hor-leyenda__item">
            <div class="hor-asiento hor-asiento--ocupado hor-asiento--demo"></div>
            <span>Ocupado</span>
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
const BASE          = '<?= $basePath ?>';
const MI_ID         = <?= $postulanteId ?>;
const ES_ADMIN      = <?= $esAdmin ? 'true' : 'false' ?>;
const SEMANA_ID     = <?= $semana ? $semana['id_semana'] : 'null' ?>;
const HOY           = '<?= date('Y-m-d') ?>';
</script>
<script src="<?= $basePath ?>/assets/js/session-guard.js"></script>
<script src="<?= $basePath ?>/assets/js/horario.js"></script>
</body>
</html>
