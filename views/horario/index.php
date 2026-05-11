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
];
// $slotsConfig y $roles vienen del controller (desde BD)
$slotsConfig = $slotsConfig ?? [];
$roles       = $roles       ?? [];

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

// Calcular si la semana está editable
$editable = false;
if ($semana) {
    $hoy = new DateTime('now', new DateTimeZone('America/Lima'));
    $fin = new DateTime($semana['fecha_fin'] . ' 23:59:59');
    $editable = ($semana['estado'] === 'ABIERTA' && $hoy <= $fin) || $esAdmin;
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
        <a href="<?= $basePath ?>/horario/historial" class="hor-btn hor-btn--outline" style="font-size:0.78rem;">📂 Historial</a>
        <a href="<?= $basePath ?>/horario" class="hor-btn-back">← Semana actual</a>
    </div>
</header>

<main class="hor-main">

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
            <span class="hor-semana-estado <?= $semana['estado'] === 'ABIERTA' ? 'estado-abierta' : 'estado-cerrada' ?>">
                <?= $semana['estado'] === 'ABIERTA' ? '🟢 Abierta' : '🔒 Cerrada' ?>
            </span>
            <?php if ($editable && !$esAdmin): ?>
                <span class="hor-cierre-info">
                    Cierra el domingo <?= date('d/m', strtotime($semana['fecha_fin'])) ?> a medianoche
                </span>
            <?php endif; ?>
        </div>

        <!-- Navegación entre semanas -->
        <?php if (count($semanas) > 1): ?>
        <div class="hor-semana-nav">
            <?php foreach ($semanas as $s): ?>
                <a href="?semana=<?= $s['id_semana'] ?>"
                   class="hor-semana-pill <?= $s['id_semana'] == $semana['id_semana'] ? 'active' : '' ?>">
                    <?= date('d/m', strtotime($s['fecha_inicio'])) ?> –
                    <?= date('d/m', strtotime($s['fecha_fin'])) ?>
                </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <a href="<?= $basePath ?>/horario/historial" class="hor-btn hor-btn--outline">
            📂 Historial
        </a>
    </div>

    <!-- ── Resumen de disponibles ──────────────────────────── -->
    <div id="horResumen" class="hor-resumen" hidden></div>

    <div id="horMsg" class="hor-alert" hidden></div>

    <!-- ── Grillas por local ──────────────────────────────── -->
    <?php foreach ($LOCALES as $localId => $localInfo):
        $configLocal = $slotsConfig[$localId] ?? [];
        if (empty($configLocal)) continue;
    ?>
    <section class="hor-sala" style="--sala-color:<?= $localInfo['color'] ?>;">
        <div class="hor-sala__titulo">
            <span class="hor-sala__icono">🏪</span>
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
                    <div class="hor-grid__rol-label">
                        <?= htmlspecialchars($roles[$rol]['desc'] ?? $rol) ?>
                        <?php if ($cantidad > 1): ?>
                            <span class="hor-slot-num"><?= $n ?></span>
                        <?php endif; ?>
                        <?php if ($esOpcional): ?>
                            <span class="hor-opcional-badge">opc.</span>
                        <?php endif; ?>
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
                         onclick="clickAsiento(this)"
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
            <span>Libre — clic para tomar</span>
        </div>
        <div class="hor-leyenda__item">
            <div class="hor-asiento hor-asiento--mio hor-asiento--demo"></div>
            <span>Tuyo — clic para liberar</span>
        </div>
        <div class="hor-leyenda__item">
            <div class="hor-asiento hor-asiento--ocupado hor-asiento--demo"></div>
            <span>Ocupado por otro</span>
        </div>
    </div>

    <?php endif; ?>

</main>

<?php if ($esAdmin): ?>
<!-- ── Admin: picker de trabajador ──────────────────────── -->
<div id="adminPickerOverlay" class="hor-picker-overlay" hidden>
    <div class="hor-picker">
        <div class="hor-picker__header">
            <h3 id="pickerTitulo">Asignar turno</h3>
            <button onclick="cerrarPicker()">✕</button>
        </div>
        <select id="pickerSelect" class="hor-picker__select">
            <option value="">— Seleccionar trabajador —</option>
        </select>
        <div class="hor-picker__footer">
            <button class="hor-btn" style="background:#f1f5f9;color:#64748b;border:none;" onclick="cerrarPicker()">Cancelar</button>
            <button class="hor-btn hor-btn--primary" onclick="pickerAsignar()">Asignar</button>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
const BASE          = '<?= $basePath ?>';
const MI_ID         = <?= $postulanteId ?>;
const ES_ADMIN      = <?= $esAdmin ? 'true' : 'false' ?>;
const SEMANA_ID     = <?= $semana ? $semana['id_semana'] : 'null' ?>;
const EDITABLE      = <?= $editable ? 'true' : 'false' ?>;
</script>
<script src="<?= $basePath ?>/assets/js/session-guard.js"></script>
<script src="<?= $basePath ?>/assets/js/horario.js"></script>
</body>
</html>
