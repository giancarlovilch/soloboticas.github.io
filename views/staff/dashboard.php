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
    <title>Mi Asistencia | Solo Boticas</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/staff.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="<?= $basePath ?>/assets/img/logo.ico">
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
        <a href="<?= $basePath ?>/staff/info" target="_blank"
           title="Información de la empresa"
           style="width:26px;height:26px;border-radius:50%;border:1.5px solid rgba(255,255,255,0.35);
                  background:rgba(255,255,255,0.12);color:#fff;font-size:0.78rem;font-weight:700;
                  cursor:pointer;line-height:26px;text-align:center;flex-shrink:0;
                  text-decoration:none;display:inline-flex;align-items:center;justify-content:center;
                  transition:background .15s;">?</a>
    </div>
</header>


<main class="staff-main">

    <!-- ── Pagos PLIN ────────────────────────────────────── -->
    <section class="staff-card" style="text-align:center;background:linear-gradient(135deg,#f5f3ff 0%,#ede9fe 100%);border:1.5px solid #c4b5fd;">
        <h2 class="staff-section-title" style="color:#6d28d9;">Cobros PLIN</h2>
        <p style="font-size:0.82rem;color:#7c3aed;margin-bottom:1rem;">
            Reclama los pagos QR recibidos y asígnalos a tu caja.
        </p>
        <a href="<?= $basePath ?>/plin"
           class="staff-btn-marcar"
           style="display:inline-block;text-decoration:none;padding:.75rem 2rem;background:#6d28d9;border-color:#6d28d9;">
            Ir a PLIN →
        </a>
    </section>

    <!-- ── Reloj ───────────────────────────────────────── -->
    <section class="staff-card staff-clock-card">
        <div class="staff-clock" id="reloj">00:00:00</div>
        <div class="staff-date"><?= $diaLabel ?></div>
    </section>

    <!-- ── Economía ────────────────────────────────── -->
    <section class="staff-card" style="text-align:center;background:linear-gradient(135deg,#fff0f6 0%,#fce7f3 100%);border:1.5px solid #fbcfe8;">
        <h2 class="staff-section-title" style="color:#9d174d;">Mi economía</h2>
        <p style="font-size:0.82rem;color:#be185d;margin-bottom:1rem;">
            Consulta los pagos que has recibido por tus turnos trabajados.
        </p>
        <a href="<?= $basePath ?>/staff/economia"
           class="staff-btn-marcar"
           style="display:inline-block;text-decoration:none;padding:.75rem 2rem;background:#ec4899;border-color:#ec4899;">
            Ver mis pagos →
        </a>
    </section>

    <!-- ── Horarios semanales ────────────────────────── -->
    <section class="staff-card" style="text-align:center;">
        <h2 class="staff-section-title">Mi horario semanal</h2>
        <p style="font-size:0.82rem;color:#64748b;margin-bottom:1rem;">
            Consulta y elige tus posiciones para la próxima semana.
        </p>
        <a href="<?= $basePath ?>/horario"
           class="staff-btn-marcar staff-btn-marcar--entrada"
           style="display:inline-block;text-decoration:none;padding:.75rem 2rem;">
            Ver horarios semanales →
        </a>
    </section>

    <!-- ── Caja ──────────────────────────────────────── -->
    <section class="staff-card" style="text-align:center;">
        <h2 class="staff-section-title">Módulo de caja</h2>
        <p style="font-size:0.82rem;color:#64748b;margin-bottom:1rem;">
            Registra y gestiona el arqueo de caja de tu turno.
        </p>
        <a href="<?= $basePath ?>/caja"
           class="staff-btn-marcar staff-btn-marcar--salida"
           style="display:inline-block;text-decoration:none;padding:.75rem 2rem;">
            Ir a caja →
        </a>
    </section>

    <!-- ── Encuestas ────────────────────────────────────── -->
    <section class="staff-card" style="text-align:center;">
        <h2 class="staff-section-title">Fichas del equipo</h2>
        <p style="font-size:0.82rem;color:#64748b;margin-bottom:1rem;">
            Califica los turnos de tus compañeros y consulta tu propio record mensual.
        </p>
        <div style="display:flex;gap:.75rem;justify-content:center;flex-wrap:wrap;">
            <a href="<?= $basePath ?>/staff/mi-horario?modo=pendientes"
               class="staff-btn-marcar staff-btn-marcar--entrada"
               style="display:inline-block;text-decoration:none;padding:.75rem 1.5rem;">
                📋 Encuestas pendientes →
            </a>
            <a href="<?= $basePath ?>/staff/mi-horario?modo=mis-encuestas"
               class="staff-btn-marcar"
               style="display:inline-block;text-decoration:none;padding:.75rem 1.5rem;background:#7c3aed;">
                👤 Mis encuestas →
            </a>
        </div>
    </section>

</main>

<script>
const BASE = (function() {
    const i = window.location.pathname.indexOf('/staff');
    return i === -1 ? '' : window.location.pathname.substring(0, i);
})();
const url = (p) => `${BASE}${p}`;
</script>
<script src="<?= $basePath ?>/assets/js/session-guard.js"></script>
<script src="<?= $basePath ?>/assets/js/staff.js"></script>
</body>
</html>
