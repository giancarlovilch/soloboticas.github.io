<?php
$basePath  = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
$userName  = $userName ?? $_SESSION['user_name'] ?? 'Colaborador';
$userRol   = $userRol  ?? $_SESSION['user_rol']  ?? 'STAFF';
$diasSemana = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
$meses      = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
$diaLabel   = $diasSemana[date('w')] . ', ' . date('d') . ' de ' . $meses[(int)date('n') - 1] . ' de ' . date('Y');
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
