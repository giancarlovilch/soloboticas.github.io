<?php
$db   = Database::getConnection();
$base = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
$f2   = fn($v) => 'S/ ' . number_format((float)$v, 2, '.', ',');

$logs = [];
$tablaExiste = true;
try {
    $logs = $db->query(
        "SELECT lc.*, sc.fecha_operacion, c.descripcion AS caja_desc, l.descripcion AS local_desc,
                p.nombres AS cajera_nombre
         FROM log_consistencia_cuadre lc
         INNER JOIN sesion_caja sc ON sc.id_sesion = lc.sesion_id
         INNER JOIN caja c         ON c.id_caja    = sc.caja_id
         INNER JOIN local l        ON l.id_local   = c.local_id
         INNER JOIN postulante p   ON p.id_postulante = sc.postulante_apertura_id
         ORDER BY lc.detectado_en DESC
         LIMIT 200"
    )->fetchAll();
} catch (\PDOException $e) {
    $tablaExiste = false;
}
?>

<div style="padding:1.5rem;">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.75rem;margin-bottom:1rem;">
        <h2 style="margin:0;">Logs de consistencia de cuadres</h2>
        <button type="button" id="btnAuditarLogs" class="caja-btn caja-btn--outline"
                style="border-color:#7c3aed;color:#7c3aed;font-size:.8rem;padding:6px 12px;border-radius:6px;background:#fff;cursor:pointer;">
            🔍 Ejecutar verificación ahora
        </button>
    </div>

    <p style="font-size:.85rem;color:#64748b;max-width:60ch;">
        Compara el valor guardado en <code>detalle_cuadre.diferencia</code> (lo que muestra
        <code>/caja</code>) contra el cálculo en vivo (lo que muestran
        <code>/caja/reporte/{id}</code> e <code>/incidencias/{id}</code>) para todas las
        sesiones con cuadre. Cada desviación encontrada queda registrada abajo.
    </p>

    <div id="auditoriaResultadoLogs" style="display:none;margin:1rem 0;padding:.75rem 1rem;border-radius:8px;font-size:.85rem;"></div>

    <?php if (!$tablaExiste): ?>
        <div style="padding:1rem;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;color:#92400e;font-size:.85rem;">
            La tabla <code>log_consistencia_cuadre</code> aún no existe. Créala en phpMyAdmin para
            empezar a registrar las verificaciones.
        </div>
    <?php elseif (empty($logs)): ?>
        <div style="padding:1rem;background:#f1f5f9;border-radius:8px;color:#64748b;font-size:.85rem;">
            Sin desviaciones registradas todavía. Usa el botón de arriba o el de
            <code>/caja</code> para correr la verificación.
        </div>
    <?php else: ?>
        <table style="width:100%;border-collapse:collapse;font-size:.83rem;">
            <thead>
                <tr style="text-align:left;border-bottom:2px solid #e2e8f0;">
                    <th style="padding:6px 8px;">Sesión</th>
                    <th style="padding:6px 8px;">Cajera</th>
                    <th style="padding:6px 8px;">Caja</th>
                    <th style="padding:6px 8px;">Fecha</th>
                    <th style="padding:6px 8px;text-align:right;">Guardado</th>
                    <th style="padding:6px 8px;text-align:right;">Real</th>
                    <th style="padding:6px 8px;text-align:right;">Delta</th>
                    <th style="padding:6px 8px;">Detectado</th>
                    <th style="padding:6px 8px;">Estado</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($logs as $row): ?>
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:6px 8px;">
                        <a href="<?= $base ?>/caja/reporte/<?= (int)$row['sesion_id'] ?>">#<?= (int)$row['sesion_id'] ?></a>
                    </td>
                    <td style="padding:6px 8px;"><?= htmlspecialchars($row['cajera_nombre']) ?></td>
                    <td style="padding:6px 8px;"><?= htmlspecialchars($row['caja_desc']) ?> (<?= htmlspecialchars($row['local_desc']) ?>)</td>
                    <td style="padding:6px 8px;"><?= date('d/m/Y', strtotime($row['fecha_operacion'])) ?></td>
                    <td style="padding:6px 8px;text-align:right;"><?= $f2($row['diferencia_guardada']) ?></td>
                    <td style="padding:6px 8px;text-align:right;"><?= $f2($row['diferencia_calculada']) ?></td>
                    <td style="padding:6px 8px;text-align:right;font-weight:700;color:#991b1b;"><?= $f2($row['delta']) ?></td>
                    <td style="padding:6px 8px;white-space:nowrap;"><?= date('d/m/Y H:i', strtotime($row['detectado_en'])) ?></td>
                    <td style="padding:6px 8px;">
                        <?php if ($row['resuelto']): ?>
                            <span style="background:#d1fae5;color:#065f46;font-size:.72rem;font-weight:700;padding:2px 7px;border-radius:5px;">Corregido</span>
                        <?php else: ?>
                            <span style="background:#fee2e2;color:#991b1b;font-size:.72rem;font-weight:700;padding:2px 7px;border-radius:5px;">Pendiente</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script>
document.getElementById('btnAuditarLogs')?.addEventListener('click', async function () {
    const btn   = this;
    const panel = document.getElementById('auditoriaResultadoLogs');
    btn.disabled = true;
    btn.textContent = 'Verificando...';
    try {
        const r   = await fetch('<?= $base ?>/caja/api/auditar-consistencia', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const res = await r.json();
        if (!res.success) throw new Error(res.message || 'Error al verificar');

        const d = res.data;
        panel.style.display = 'block';
        if (d.fallidas.length === 0) {
            panel.style.background = '#d1fae5';
            panel.style.color      = '#065f46';
            panel.innerHTML = `✓ ${d.revisadas} sesiones revisadas, todas consistentes.`;
        } else {
            panel.style.background = '#fee2e2';
            panel.style.color      = '#991b1b';
            panel.innerHTML = `⚠ ${d.fallidas.length} de ${d.revisadas} sesiones desincronizadas. Recarga la página para ver el detalle en la tabla.`;
        }
    } catch (e) {
        panel.style.display    = 'block';
        panel.style.background = '#fee2e2';
        panel.style.color      = '#991b1b';
        panel.innerHTML        = '✗ ' + e.message;
    } finally {
        btn.disabled    = false;
        btn.textContent = '🔍 Ejecutar verificación ahora';
    }
});
</script>
