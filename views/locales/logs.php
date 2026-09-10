<?php
/**
 * GET /locales/logs — bitácora completa del módulo.
 * Espera: $logs, $locales, $filtros.
 */
$docTab   = 'logs';
$docTitle = 'Bitácora de documentación';
require __DIR__ . '/_header.php';

$acciones = [
    'CREAR'           => 'Registro nuevo',
    'ACTUALIZAR'      => 'Actualización',
    'RENOVAR'         => 'Renovación',
    'FIRMAR'          => 'Firma',
    'OBSERVAR'        => 'Observación',
    'ENVIAR_REVISION' => 'Envío a revisión',
    'CAMBIO_ESTADO'   => 'Cambio de estado',
    'ADJUNTAR'        => 'Archivo adjuntado',
    'QUITAR_ARCHIVO'  => 'Archivo eliminado',
    'ELIMINAR'        => 'Registro eliminado',
    'TIPO_CREAR'      => 'Catálogo: creado',
    'TIPO_EDITAR'     => 'Catálogo: editado',
    'TIPO_ACTIVAR'    => 'Catálogo: activado',
    'TIPO_DESACTIVAR' => 'Catálogo: desactivado',
    'TIPO_ELIMINAR'   => 'Catálogo: eliminado',
];
?>

<div class="doc-head">
    <div>
        <p class="doc-kicker">Trazabilidad</p>
        <h2 class="doc-title">Bitácora de documentación</h2>
        <p class="doc-sub">Todo lo que se registró, editó, firmó u observó. <?= count($logs) ?> movimiento(s).</p>
    </div>
</div>

<div class="doc-panel" style="margin-top:0;">
    <form method="get" class="doc-form-grid" style="align-items:flex-end;">
        <div class="doc-field">
            <label>Local</label>
            <select name="local">
                <option value="">Todos</option>
                <?php foreach ($locales as $l): ?>
                    <option value="<?= (int)$l['id_local'] ?>" <?= (int)$filtros['local_id'] === (int)$l['id_local'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($l['descripcion']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="doc-field">
            <label>Acción</label>
            <select name="accion">
                <option value="">Todas</option>
                <?php foreach ($acciones as $k => $v): ?>
                    <option value="<?= $k ?>" <?= $filtros['accion'] === $k ? 'selected' : '' ?>><?= $v ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="doc-field">
            <label>Desde</label>
            <input type="date" name="desde" value="<?= htmlspecialchars($filtros['desde']) ?>">
        </div>
        <div class="doc-field">
            <label>Hasta</label>
            <input type="date" name="hasta" value="<?= htmlspecialchars($filtros['hasta']) ?>">
        </div>
        <div class="doc-field" style="display:flex;gap:.4rem;">
            <button type="submit" class="doc-btn doc-btn--primary">Filtrar</button>
            <a href="<?= $basePath ?>/locales/logs" class="doc-btn">Limpiar</a>
        </div>
    </form>
</div>

<div class="doc-panel">
    <?php if (empty($logs)): ?>
        <div class="doc-empty">
            <span class="doc-empty__ico">🕘</span>
            No hay movimientos con esos filtros.
        </div>
    <?php else: ?>
    <div class="doc-table__scroll">
        <table class="doc-table">
            <thead>
                <tr>
                    <th>Fecha</th><th>Acción</th><th>Documento</th><th>Local</th>
                    <th>Estado</th><th>Usuario</th><th>Detalle</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($logs as $l): ?>
                <tr>
                    <td class="doc-muted" style="white-space:nowrap;font-size:.74rem;">
                        <?= date('d/m/Y H:i', strtotime($l['creado_en'])) ?>
                    </td>
                    <td>
                        <span class="doc-acc doc-acc--<?= htmlspecialchars($l['accion']) ?>">
                            <?= htmlspecialchars($acciones[$l['accion']] ?? str_replace('_', ' ', $l['accion'])) ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($l['tipo_nombre'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($l['local_nombre'] ?? '—') ?></td>
                    <td style="font-size:.72rem;">
                        <?php if ($l['estado_anterior'] || $l['estado_nuevo']): ?>
                            <span class="doc-muted"><?= htmlspecialchars($l['estado_anterior'] ?? '—') ?></span>
                            → <strong><?= htmlspecialchars($l['estado_nuevo'] ?? '—') ?></strong>
                        <?php else: ?>
                            <span class="doc-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:.74rem;">
                        <strong><?= htmlspecialchars(trim($l['usuario_nombre'] ?? '') ?: 'Sistema') ?></strong>
                        <?php if (!empty($l['usuario_rol'])): ?>
                            <span class="doc-dias"><?= htmlspecialchars($l['usuario_rol']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="doc-muted" style="font-size:.74rem;max-width:280px;">
                        <?= htmlspecialchars($l['detalle'] ?? '') ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

</div><!-- /doc-wrap -->
<script src="<?= $basePath ?>/assets/js/session-guard.js"></script>
</body>
</html>
