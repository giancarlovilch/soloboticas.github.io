<?php
if (!isset($_SESSION['user_rol'])) exit('Acceso denegado');
$nombreUsuario = $nombreUsuario ?? $_SESSION['user_name'] ?? 'Administrador';
$homeEstrellas = $homeEstrellas ?? null;
$homeSyncLog   = $homeSyncLog   ?? [];
if ($homeEstrellas) {
    $heTot = max(1, $homeEstrellas['rojas'] + $homeEstrellas['azules']);
    $hePctAzul = round(($homeEstrellas['azules'] / $heTot) * 100);
    $heFmt = fn($v) => (floor($v) == $v) ? (string)(int)$v : number_format($v, 1);
}
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

    <?php if ($homeEstrellas): ?>
    <a href="?page=economia" style="text-decoration:none;color:inherit;display:block;">
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:1.1rem 1.5rem;
                margin-bottom:1.5rem;box-shadow:0 1px 4px rgba(0,0,0,0.05);">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.5rem;flex-wrap:wrap;gap:.5rem;">
            <span style="font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#64748b;">
                ⭐ Estrellas del equipo — este mes
            </span>
            <span style="font-size:.85rem;font-weight:800;">
                <span style="color:#dc2626;">🔴 <?= $heFmt($homeEstrellas['rojas']) ?></span>
                <span style="color:#cbd5e1;padding:0 .3rem;">vs</span>
                <span style="color:#1d4ed8;">🔵 <?= $heFmt($homeEstrellas['azules']) ?></span>
            </span>
        </div>
        <div style="height:12px;border-radius:999px;overflow:hidden;background:#dc2626;display:flex;max-width:480px;">
            <div style="width:<?= $hePctAzul ?>%;background:linear-gradient(90deg,#3b82f6,#1d4ed8);"></div>
            <div style="flex:1;background:linear-gradient(90deg,#ef4444,#dc2626);"></div>
        </div>
        <?php if (!empty($homeEstrellas['en_riesgo'])): ?>
        <p style="font-size:.72rem;color:#64748b;margin-top:.6rem;">
            ⚠️ En riesgo de descuento:
            <?php foreach ($homeEstrellas['en_riesgo'] as $i => $r): ?>
                <strong style="color:#991b1b;"><?= htmlspecialchars($r['nombre']) ?></strong><?= $i < count($homeEstrellas['en_riesgo']) - 1 ? ', ' : '' ?>
            <?php endforeach; ?>
        </p>
        <?php else: ?>
        <p style="font-size:.72rem;color:#059669;margin-top:.6rem;">✅ Nadie en riesgo de descuento este mes.</p>
        <?php endif; ?>
    </div>
    </a>
    <?php endif; ?>

    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:1.1rem 1.5rem;
                margin-bottom:1.5rem;box-shadow:0 1px 4px rgba(0,0,0,0.05);">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.7rem;flex-wrap:wrap;gap:.5rem;">
            <span style="font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#64748b;">
                📦 Sincronización de productos (Softpharma → web)
            </span>
        </div>
        <?php if (empty($homeSyncLog)): ?>
            <p style="font-size:.8rem;color:#94a3b8;">Aún no se ha recibido ninguna sincronización.</p>
        <?php else: ?>
            <div style="display:flex;flex-direction:column;gap:.4rem;max-height:340px;overflow-y:auto;">
                <?php foreach ($homeSyncLog as $log):
                    $esError = $log['estado'] === 'ERROR';
                    $segundos = round($log['duracion_ms'] / 1000, 1);
                ?>
                <div style="display:flex;align-items:center;gap:.75rem;padding:.5rem .7rem;border-radius:8px;
                            background:<?= $esError ? '#fef2f2' : '#f8fafc' ?>;font-size:.78rem;flex-wrap:wrap;">
                    <span style="font-weight:700;color:#1e293b;min-width:110px;">
                        <?= date('d/m H:i', strtotime($log['creado_en'])) ?>
                    </span>
                    <span style="color:#64748b;min-width:100px;"><?= htmlspecialchars($log['local_nombre']) ?></span>
                    <?php if ($esError): ?>
                        <span style="color:#dc2626;font-weight:700;" title="<?= htmlspecialchars($log['mensaje'] ?? '') ?>">
                            ❌ Error al sincronizar
                        </span>
                    <?php else: ?>
                        <span style="color:#1e293b;"><?= $log['total_productos'] ?> productos</span>
                        <?php if ($log['productos_nuevos'] > 0): ?>
                            <span style="background:#dbeafe;color:#1d4ed8;font-weight:700;padding:1px 8px;border-radius:20px;">
                                +<?= $log['productos_nuevos'] ?> nuevos
                            </span>
                        <?php endif; ?>
                        <span style="color:#94a3b8;margin-left:auto;">⏱️ <?= $segundos ?>s</span>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
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

        <a href="<?= defined('APP_BASE_PATH') ? APP_BASE_PATH : '' ?>/caja/auditoria" class="home-card">
            <div class="home-card__icon">🔍</div>
            <div class="home-card__label">Auditoría</div>
            <div class="home-card__desc">Verificar cobros y pagos del cuadre</div>
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

