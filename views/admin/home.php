<?php
if (!isset($_SESSION['user_rol'])) exit('Acceso denegado');
$nombreUsuario = $nombreUsuario ?? $_SESSION['user_name'] ?? 'Administrador';
?>

<div class="home-welcome">
    <div class="home-welcome__header">
        <h2>Bienvenido, <?= htmlspecialchars($nombreUsuario) ?></h2>
        <p>Panel de Administración &mdash; Grupo KGyR S.A.C &bull; Solo Boticas &bull; <?= date('d/m/Y') ?></p>
    </div>

    <!-- ── Reloj ──────────────────────────────────────── -->
    <div style="background:#fff;border:1px solid #e5e7eb;border-left:4px solid #0097A7;border-radius:12px;
                padding:1.1rem 1.5rem;margin-bottom:1.5rem;display:inline-flex;align-items:center;gap:1rem;
                box-shadow:0 1px 4px rgba(0,0,0,0.05);">
        <div id="adminReloj" style="font-size:2.2rem;font-weight:700;color:#1e293b;font-variant-numeric:tabular-nums;letter-spacing:.03em;">
            00:00:00
        </div>
        <div style="font-size:0.78rem;color:#64748b;"><?= date('l d/m/Y') ?></div>
    </div>

    <div class="home-cards">
        <a href="?page=postulantes" class="home-card">
            <div class="home-card__icon">👥</div>
            <div class="home-card__label">Postulantes</div>
            <div class="home-card__desc">Ver y gestionar postulaciones</div>
        </a>

        <a href="?page=status" class="home-card">
            <div class="home-card__icon">🔐</div>
            <div class="home-card__label">Accesos</div>
            <div class="home-card__desc">Habilitar o suspender usuarios</div>
        </a>

        <a href="?page=asistencias" class="home-card">
            <div class="home-card__icon">📋</div>
            <div class="home-card__label">Asistencias</div>
            <div class="home-card__desc">Control de asistencia del personal</div>
        </a>

        <a href="<?= defined('APP_BASE_PATH') ? APP_BASE_PATH : '' ?>/horario" class="home-card">
            <div class="home-card__icon">📅</div>
            <div class="home-card__label">Horarios</div>
            <div class="home-card__desc">Asignación semanal por local</div>
        </a>

        <a href="<?= defined('APP_BASE_PATH') ? APP_BASE_PATH : '' ?>/caja" class="home-card">
            <div class="home-card__icon">💰</div>
            <div class="home-card__label">Caja</div>
            <div class="home-card__desc">Gestión de cuadre de caja</div>
        </a>

        <a href="<?= defined('APP_BASE_PATH') ? APP_BASE_PATH : '' ?>/admin/reportes" class="home-card">
            <div class="home-card__icon">📊</div>
            <div class="home-card__label">Reportes</div>
            <div class="home-card__desc">Resultados de arqueo y más</div>
        </a>

        <a href="<?= defined('APP_BASE_PATH') ? APP_BASE_PATH : '' ?>/admin/database" class="home-card">
            <div class="home-card__icon">🗄️</div>
            <div class="home-card__label">Base de Datos</div>
            <div class="home-card__desc">Backup, restore y gestión de BD</div>
        </a>

        <a href="<?= defined('APP_BASE_PATH') ? APP_BASE_PATH : '' ?>/staff/info" target="_blank" class="home-card">
            <div class="home-card__icon">📋</div>
            <div class="home-card__label">Info interna</div>
            <div class="home-card__desc">Cuentas, contactos y locales</div>
        </a>
    </div>
</div>

<script>
(function() {
    const el = document.getElementById('adminReloj');
    if (!el) return;
    const tick = () => { el.textContent = new Date().toLocaleTimeString('es-PE',{hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:false}); };
    setInterval(tick, 1000); tick();
})();
</script>

