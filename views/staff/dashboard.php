<?php
$basePath  = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
$userName  = $userName ?? $_SESSION['user_name'] ?? 'Colaborador';
$userRol   = $userRol  ?? $_SESSION['user_rol']  ?? 'STAFF';
$diasSemana = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
$meses      = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
$diaLabel   = $diasSemana[date('w')] . ', ' . date('d') . ' de ' . $meses[(int)date('n') - 1] . ' de ' . date('Y');
$estrellas  = $estrellas ?? ['rojas' => 0, 'azules' => 0, 'turnos' => 0];
$estRojas   = (int)$estrellas['rojas'];
$estAzules  = (float)$estrellas['azules'];
$estTotal   = max(1, $estRojas + $estAzules);
$estPctAzul = round(($estAzules / $estTotal) * 100);
$fmtEst     = fn($v) => (floor($v) == $v) ? (string)(int)$v : number_format($v, 1);
$estDiferencia = (float)($estrellas['diferencia'] ?? ($estRojas - $estAzules));
$estMonto      = (float)($estrellas['monto']      ?? round($estDiferencia * 0.10, 2));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Solo Boticas</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/staff.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="<?= $basePath ?>/assets/img/logo.ico">
    <style>
        .db-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .db-card {
            background: #fff;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            padding: 1.25rem 1.1rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: .6rem;
            text-align: center;
            box-shadow: 0 1px 4px rgba(0,0,0,.05);
            transition: box-shadow .15s, transform .15s;
        }
        .db-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.1); transform: translateY(-1px); }
        .db-card__icon { font-size: 2rem; line-height: 1; }
        .db-card__title {
            font-size: .82rem; font-weight: 700; color: #1e293b;
            text-transform: uppercase; letter-spacing: .05em;
        }
        .db-btn {
            display: block; width: 100%;
            padding: .55rem .5rem;
            border-radius: 8px;
            font-size: .75rem; font-weight: 700;
            text-decoration: none; text-align: center;
            border: none; cursor: pointer;
            transition: filter .15s;
            color: #fff;
            letter-spacing: .03em;
        }
        .db-btn:hover { filter: brightness(.9); }
        .db-btn + .db-btn { margin-top: .4rem; }

        /* Colores por módulo */
        .db-card--horario  { border-color: #bae6fd; background: linear-gradient(135deg,#f0f9ff,#e0f2fe); }
        .db-card--caja     { border-color: #bbf7d0; background: linear-gradient(135deg,#f0fdf4,#dcfce7); }
        .db-card--inc      { border-color: #fed7aa; background: linear-gradient(135deg,#fff7ed,#ffedd5); }
        .db-card--eco      { border-color: #fbcfe8; background: linear-gradient(135deg,#fff0f6,#fce7f3); }
        .db-card--enc      { border-color: #ddd6fe; background: linear-gradient(135deg,#f5f3ff,#ede9fe); }
        .db-card--plin     { border-color: #c4b5fd; background: linear-gradient(135deg,#f5f3ff,#ede9fe); }

        @media (max-width: 480px) {
            .db-grid { grid-template-columns: 1fr; }
        }

        /* Balanza de estrellas */
        .estrellas-card {
            background: #fff; border: 1.5px solid #e2e8f0; border-radius: 14px;
            padding: 1rem 1.1rem; 
        }
        .estrellas-hd {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: .6rem;
        }
        .estrellas-hd__title {
            font-size: .72rem; font-weight: 800; text-transform: uppercase;
            letter-spacing: .06em; color: #64748b;
        }
        .estrellas-monto {
            font-size: .78rem; font-weight: 800; padding: .25rem .65rem; border-radius: 20px;
            white-space: nowrap;
        }
        .estrellas-monto--contra { background: #fee2e2; color: #dc2626; }
        .estrellas-monto--favor  { background: #dbeafe; color: #1d4ed8; }
        .estrellas-monto--parejo { background: #f1f5f9; color: #64748b; }
        .estrellas-bar {
            position: relative; height: 14px; border-radius: 999px; overflow: hidden;
            background: #dc2626; display: flex;
        }
        .estrellas-bar__azul {
            background: linear-gradient(90deg,#3b82f6,#1d4ed8);
            height: 100%;
        }
        .estrellas-bar__rojo {
            background: linear-gradient(90deg,#ef4444,#dc2626);
            height: 100%; flex: 1;
        }
        .estrellas-rate {
            font-size: .68rem; color: #94a3b8; margin: -.2rem 0 .55rem; display: flex;
            align-items: center; gap: .3rem;
        }
        .estrellas-rate strong { color: #64748b; font-weight: 700; }
        .estrellas-bar-labels {
            display: flex; justify-content: space-between; margin-top: .3rem;
            font-size: .78rem; font-weight: 800;
        }
        .estrellas-bar-labels .azul { color: #1d4ed8; }
        .estrellas-bar-labels .rojo { color: #dc2626; }
        .estrellas-msg { font-size: .72rem; color: #64748b; margin-top: .55rem; }
        .estrellas-actions { display: flex; gap: .5rem; margin-top: .65rem; }
        .estrellas-cta {
            flex: 1; text-align: center; padding: .5rem .5rem;
            border-radius: 8px; background: #1d4ed8; color: #fff; text-decoration: none;
            font-size: .76rem; font-weight: 700;
        }
        .estrellas-cta--ghost {
            background: #eff6ff; color: #1d4ed8; border: 1.5px solid #bfdbfe;
        }
    </style>
</head>
<body>

<header class="staff-header">
    <div class="staff-header__brand">
        <div class="staff-header__logo">SB</div>
        <div>
            <p class="staff-header__company">Grupo KGyR S.A.C</p>
            <p class="staff-header__app">Solo Boticas <span>Colaboradores</span></p>
        </div>
    </div>
    <div class="staff-header__user">
        <div>
            <p class="staff-header__name"><?= htmlspecialchars($userName) ?></p>
            <p class="staff-header__rol"><?= htmlspecialchars($userRol) ?></p>
        </div>
        <a href="<?= $basePath ?>/logout" class="staff-btn-logout">Salir</a>
        <a href="<?= $basePath ?>/staff/info" target="_blank" title="Información de la empresa"
           style="width:26px;height:26px;border-radius:50%;border:1.5px solid rgba(255,255,255,0.35);
                  background:rgba(255,255,255,0.12);color:#fff;font-size:0.78rem;font-weight:700;
                  cursor:pointer;line-height:26px;text-align:center;flex-shrink:0;
                  text-decoration:none;display:inline-flex;align-items:center;justify-content:center;
                  transition:background .15s;">?</a>
    </div>
</header>

<main class="staff-main">

    <!-- ── Reloj ─────────────────────────────────────────── -->
    <section class="staff-card staff-clock-card">
        <div class="staff-clock" id="reloj">00:00:00</div>
        <div class="staff-date"><?= $diaLabel ?></div>

        <?php if (!empty($cumpleanhos)): ?>
        <div class="staff-bday-list">
            <?php foreach ($cumpleanhos as $b): ?>
            <div class="staff-bday-row">
                <span class="staff-bday-nombre"><?= htmlspecialchars($b['nombre']) ?></span>
                <span class="staff-bday-dias"><?= $b['dias'] === 0 ? 'Hoy' : ($b['dias'] === 1 ? 'Mañana' : $b['dias'] . 'd') ?></span>
                <span class="staff-bday-sticker"><?= $b['dias'] === 0 ? '🎂' : '' ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </section>

    <!-- ── Encuesta BCP de cajeras (solo últimos/primeros 3 días del mes) ── -->
    <?php if (!empty($mesEncuestaBcp)):
        $mesesNomEbcp = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
        [$ebcpAnio, $ebcpNmes] = explode('-', $mesEncuestaBcp);
        $ebcpMesLabel = $mesesNomEbcp[(int)$ebcpNmes - 1] . ' ' . $ebcpAnio;
    ?>
    <a href="<?= $basePath ?>/staff/encuesta-bcp<?= $yaVotoEncuestaBcp ? '/resultados' : '' ?>"
       class="staff-card" style="display:block;text-align:center;
              background:linear-gradient(135deg,#fff7ed,#ffedd5);border:1px solid #fdba74;
              color:#9a3412;font-weight:700;font-size:.88rem;text-decoration:none;padding:.75rem 1rem;">
        <?= $yaVotoEncuestaBcp
            ? '📊 Resultados de ' . htmlspecialchars($ebcpMesLabel)
            : '🔥 Encuesta de ' . htmlspecialchars($ebcpMesLabel) ?>
    </a>
    <?php endif; ?>

    <!-- ── Balanza de estrellas del mes ─────────────────── -->
    <section class="estrellas-card">
        <div class="estrellas-hd">
            <span class="estrellas-hd__title">⭐ Estrellas del mes</span>
            <?php if ($estDiferencia > 0): ?>
            <span class="estrellas-monto estrellas-monto--contra">⚠️ S/ <?= number_format(abs($estMonto), 2) ?> en contra</span>
            <?php elseif ($estDiferencia < 0): ?>
            <span class="estrellas-monto estrellas-monto--favor">✅ S/ <?= number_format(abs($estMonto), 2) ?> a favor</span>
            <?php else: ?>
            <span class="estrellas-monto estrellas-monto--parejo">S/ 0.00 · parejo</span>
            <?php endif; ?>
        </div>
        <p class="estrellas-rate">⭐ = <strong>S/ 0.10</strong> · así se calcula al cierre del mes</p>
        <div class="estrellas-bar">
            <div class="estrellas-bar__azul" style="width:<?= $estPctAzul ?>%;"></div>
            <div class="estrellas-bar__rojo"></div>
        </div>
        <div class="estrellas-bar-labels">
            <span class="azul"><?= $fmtEst($estAzules) ?></span>
            <span class="rojo"><?= $estRojas ?></span>
        </div>
        <?php if ($estAzules >= $estRojas): ?>
        <p class="estrellas-msg">✅ Vas ganando. Sigue sumando azules para cerrar el mes arriba.</p>
        <?php else: ?>
        <p class="estrellas-msg">⚠️ Las rojas van ganando. Gana azules para emparejar la balanza.</p>
        <?php endif; ?>
        <div class="estrellas-actions">
            <a href="<?= $basePath ?>/staff/estrellas" class="estrellas-cta">⭐ Ganar estrellas</a>
            <a href="<?= $basePath ?>/staff/estrellas/resumen" class="estrellas-cta estrellas-cta--ghost">📋 Resumen</a>
        </div>
    </section>

    <!-- ── Grid de módulos ──────────────────────────────── -->
    <div class="db-grid">

        <!-- Fila 1: Mi horario + Mis pagos -->
        <div class="db-card db-card--horario">
            <div class="db-card__icon">📅</div>
            <div class="db-card__title">Mi horario</div>
            <a href="<?= $basePath ?>/horario" class="db-btn" style="background:#0284c7;">
                Ver horario
            </a>
        </div>

        <div class="db-card db-card--eco">
            <div class="db-card__icon">💰</div>
            <div class="db-card__title">Mis pagos</div>
            <a href="<?= $basePath ?>/staff/economia" class="db-btn" style="background:#db2777;">
                Ver mis pagos
            </a>
        </div>

        <!-- Fila 2: Módulo de caja + Métricas -->
        <div class="db-card db-card--caja">
            <div class="db-card__icon">🏧</div>
            <div class="db-card__title">Módulo de caja</div>
            <a href="<?= $basePath ?>/caja" class="db-btn" style="background:#16a34a;">
                Ir a caja
            </a>
        </div>

        <div class="db-card" style="border-color:#99f6e4;background:linear-gradient(135deg,#f0fdfa,#ccfbf1);">
            <div class="db-card__icon">📊</div>
            <div class="db-card__title">Métricas</div>
            <a href="<?= $basePath ?>/horario/asistencia" class="db-btn" style="background:#0d9488;">
                Ver métricas
            </a>
        </div>

        <!-- Fila 3: Encuestas (ancho completo) -->
        <div class="db-card db-card--enc" style="grid-column: 1 / -1; flex-direction: row; flex-wrap: wrap; justify-content: space-between; text-align: left; gap: .75rem;">
            <div style="display:flex;align-items:center;gap:.6rem;">
                <span class="db-card__icon">📋</span>
                <span class="db-card__title">Fichas del equipo</span>
            </div>
            <div style="display:flex;gap:.5rem;flex-wrap:wrap;flex:1;justify-content:flex-end;">
                <a href="<?= $basePath ?>/staff/mi-horario?modo=pendientes"
                   class="db-btn" style="background:#7c3aed;width:auto;padding:.55rem 1.1rem;">
                    📋 Encuestas pendientes
                </a>
                <a href="<?= $basePath ?>/staff/mi-horario?modo=mis-encuestas"
                   class="db-btn" style="background:#6d28d9;width:auto;padding:.55rem 1.1rem;">
                    👤 Mis encuestas
                </a>
            </div>
        </div>


    </div>

</main>

<script>
const BASE = (function() {
    const i = window.location.pathname.indexOf('/staff');
    return i === -1 ? '' : window.location.pathname.substring(0, i);
})();
</script>
<script src="<?= $basePath ?>/assets/js/session-guard.js"></script>
<script src="<?= $basePath ?>/assets/js/staff.js"></script>
</body>
</html>
