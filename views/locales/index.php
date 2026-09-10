<?php
/**
 * GET /locales — portada del módulo.
 * Espera: $resumen, $alertas, $ultimos, $totalTipos, $anio, $esAdmin.
 */
$docTab   = 'locales';
$docTitle = 'Gestión de Locales';
require __DIR__ . '/_header.php';

$totVencidos = 0;
$totPorVencer = 0;
$totPendientes = 0;
$totVigentes = 0;
foreach ($resumen as $r) {
    $totVencidos   += $r['VENCIDO'];
    $totPorVencer  += $r['POR_VENCER'];
    $totPendientes += $r['PENDIENTE'] + $r['OBSERVADO'];
    $totVigentes   += $r['VIGENTE'];
}
$gastoTotal = array_sum(array_column($resumen, 'gasto_anio'));

$fmtDias = function (?int $d): string {
    if ($d === null) return 'No caduca';
    if ($d < 0)  return 'Venció hace ' . abs($d) . ' día' . (abs($d) === 1 ? '' : 's');
    if ($d === 0) return 'Vence hoy';
    return 'Vence en ' . $d . ' día' . ($d === 1 ? '' : 's');
};
?>

<div class="doc-head">
    <div>
        <p class="doc-kicker">Documentación</p>
        <h2 class="doc-title">Estado de los locales</h2>
        <p class="doc-sub"><?= (int)$totalTipos ?> documento(s) en el catálogo · Gasto <?= (int)$anio ?>: S/ <?= number_format($gastoTotal, 2) ?></p>
    </div>
</div>

<?php if ($totalTipos === 0): ?>
    <div class="doc-panel">
        <div class="doc-empty">
            <span class="doc-empty__ico">📄</span>
            Todavía no hay documentos en el catálogo.<br>
            <?php if ($esAdmin): ?>
                Créalos desde
                <a href="<?= $basePath ?>/admin/dashboard?page=documentos" style="color:#0097A7;font-weight:700;">
                    Catálogo de documentos</a>
                y aparecerán automáticamente en cada local.
            <?php else: ?>
                El administrador debe crearlos primero.
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>

    <?php if ($totVencidos > 0 || $totPorVencer > 0): ?>
    <div class="doc-alert">
        <span class="doc-alert__ico">⚠️</span>
        <span class="doc-alert__txt">
            <?php if ($totVencidos > 0): ?>
                Hay <strong><?= $totVencidos ?> documento(s) vencido(s)</strong>.
            <?php endif; ?>
            <?php if ($totPorVencer > 0): ?>
                <?= $totVencidos > 0 ? ' Además, ' : '' ?><strong><?= $totPorVencer ?></strong> está(n) por vencer.
            <?php endif; ?>
            Revisa el detalle más abajo.
        </span>
    </div>
    <?php endif; ?>

    <div class="doc-kpis">
        <div class="doc-kpi doc-kpi--vig"><div class="doc-kpi__num"><?= $totVigentes ?></div><div class="doc-kpi__lbl">Vigentes</div></div>
        <div class="doc-kpi doc-kpi--pv"><div class="doc-kpi__num"><?= $totPorVencer ?></div><div class="doc-kpi__lbl">Por vencer</div></div>
        <div class="doc-kpi doc-kpi--ven"><div class="doc-kpi__num"><?= $totVencidos ?></div><div class="doc-kpi__lbl">Vencidos</div></div>
        <div class="doc-kpi"><div class="doc-kpi__num"><?= $totPendientes ?></div><div class="doc-kpi__lbl">Sin registrar / observados</div></div>
        <div class="doc-kpi"><div class="doc-kpi__num" style="font-size:1.1rem;">S/ <?= number_format($gastoTotal, 2) ?></div><div class="doc-kpi__lbl">Gasto <?= (int)$anio ?></div></div>
    </div>

    <div class="doc-locales">
        <?php foreach ($resumen as $r):
            $alerta = $r['VENCIDO'] > 0;
            $tot    = max(1, $r['total']);
            $pV  = round($r['VIGENTE']    / $tot * 100, 2);
            $pPV = round($r['POR_VENCER'] / $tot * 100, 2);
            $pVe = round($r['VENCIDO']    / $tot * 100, 2);
            $pPe = max(0, 100 - $pV - $pPV - $pVe);
            $colorPct = $r['cumplimiento'] >= 90 ? '#059669' : ($r['cumplimiento'] >= 60 ? '#d97706' : '#dc2626');
        ?>
        <a href="<?= $basePath ?>/locales/<?= (int)$r['id_local'] ?>" class="doc-local <?= $alerta ? 'doc-local--alerta' : '' ?>">
            <div class="doc-local__hd">
                <div>
                    <div class="doc-local__name"><?= htmlspecialchars($r['nombre']) ?></div>
                    <div class="doc-local__dir"><?= htmlspecialchars($r['direccion'] ?: 'Sin dirección registrada') ?></div>
                </div>
                <div class="doc-local__pct" style="color:<?= $colorPct ?>;">
                    <?= (int)$r['cumplimiento'] ?>%<small>al día</small>
                </div>
            </div>

            <div class="doc-bar">
                <span class="doc-bar__vig" style="width:<?= $pV ?>%"></span>
                <span class="doc-bar__pv"  style="width:<?= $pPV ?>%"></span>
                <span class="doc-bar__ven" style="width:<?= $pVe ?>%"></span>
                <span class="doc-bar__pen" style="width:<?= $pPe ?>%"></span>
            </div>

            <div class="doc-chips">
                <?php if ($r['VIGENTE']):     ?><span class="doc-chip doc-chip--vig"><?= $r['VIGENTE'] ?> vigentes</span><?php endif; ?>
                <?php if ($r['POR_VENCER']):  ?><span class="doc-chip doc-chip--pv"><?= $r['POR_VENCER'] ?> por vencer</span><?php endif; ?>
                <?php if ($r['VENCIDO']):     ?><span class="doc-chip doc-chip--ven"><?= $r['VENCIDO'] ?> vencidos</span><?php endif; ?>
                <?php if ($r['EN_REVISION']): ?><span class="doc-chip doc-chip--rev"><?= $r['EN_REVISION'] ?> en revisión</span><?php endif; ?>
                <?php if ($r['OBSERVADO']):   ?><span class="doc-chip doc-chip--obs"><?= $r['OBSERVADO'] ?> observados</span><?php endif; ?>
                <?php if ($r['PENDIENTE']):   ?><span class="doc-chip doc-chip--pen"><?= $r['PENDIENTE'] ?> sin registrar</span><?php endif; ?>
                <?php if ($r['total'] === 0): ?><span class="doc-chip">Sin documentos asignados</span><?php endif; ?>
            </div>

            <div class="doc-local__foot">
                <span>
                    <?php if ($r['proximo']): ?>
                        Próximo: <strong><?= htmlspecialchars($r['proximo']['nombre']) ?></strong>
                        (<?= $fmtDias((int)$r['proximo']['dias']) ?>)
                    <?php else: ?>
                        <span class="doc-muted">Sin vencimientos próximos</span>
                    <?php endif; ?>
                </span>
                <span>S/ <strong><?= number_format($r['gasto_anio'], 2) ?></strong></span>
            </div>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- ── Vencimientos próximos ─────────────────────────────── -->
    <div class="doc-panel">
        <p class="doc-panel__title">
            <span>Vencidos y por vencer (próximos 60 días)</span>
            <span class="doc-muted" style="font-weight:600;text-transform:none;letter-spacing:0;">
                <?= count($alertas) ?> documento(s)
            </span>
        </p>

        <?php if (empty($alertas)): ?>
            <div class="doc-empty" style="padding:1.5rem;">
                <span class="doc-empty__ico">✅</span>
                Nada por vencer en los próximos 60 días.
            </div>
        <?php else: ?>
        <div class="doc-table__scroll">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Local</th><th>Documento</th><th>Vence</th><th>Estado</th><th>Responsable</th><th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($alertas as $a):
                    $ec   = $a['estado_calc'];
                    $dias = $ec['dias'];
                ?>
                    <tr>
                        <td style="font-weight:700;"><?= htmlspecialchars($a['local_nombre']) ?></td>
                        <td>
                            <?= htmlspecialchars($a['tipo_nombre']) ?>
                            <?php if (!empty($a['numero_documento'])): ?>
                                <span class="doc-dias">N.º <?= htmlspecialchars($a['numero_documento']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= $a['fecha_fin'] ? date('d/m/Y', strtotime($a['fecha_fin'])) : '—' ?>
                            <span class="doc-dias <?= ($dias !== null && $dias < 0) ? 'doc-dias--rojo' : 'doc-dias--ambar' ?>">
                                <?= $fmtDias($dias) ?>
                            </span>
                        </td>
                        <td><span class="doc-badge <?= $ec['clase'] ?>"><?= $ec['label'] ?></span></td>
                        <td class="doc-muted" style="font-size:.74rem;">
                            <?= htmlspecialchars(trim($a['firmado_nombre'] ?? '') ?: (trim($a['registrado_nombre'] ?? '') ?: '—')) ?>
                        </td>
                        <td class="doc-num">
                            <a href="<?= $basePath ?>/locales/<?= (int)$a['id_local'] ?>" class="doc-btn doc-btn--sm">Ver</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- ── Últimos movimientos ───────────────────────────────── -->
    <div class="doc-panel">
        <p class="doc-panel__title">
            <span>Últimos movimientos</span>
            <a href="<?= $basePath ?>/locales/logs" class="doc-btn doc-btn--sm">Ver bitácora completa</a>
        </p>
        <?php if (empty($ultimos)): ?>
            <div class="doc-empty" style="padding:1.2rem;">Sin movimientos todavía.</div>
        <?php else: ?>
        <ul class="doc-log">
            <?php foreach ($ultimos as $l): ?>
            <li>
                <span class="doc-log__when"><?= date('d/m/Y H:i', strtotime($l['creado_en'])) ?></span>
                <span class="doc-acc doc-acc--<?= htmlspecialchars($l['accion']) ?>"><?= htmlspecialchars(str_replace('_', ' ', $l['accion'])) ?></span>
                <span class="doc-log__what">
                    <span class="doc-log__who"><?= htmlspecialchars(trim($l['usuario_nombre'] ?? '') ?: 'Sistema') ?></span>
                    <?php if (!empty($l['tipo_nombre'])): ?> · <?= htmlspecialchars($l['tipo_nombre']) ?><?php endif; ?>
                    <?php if (!empty($l['local_nombre'])): ?> · <?= htmlspecialchars($l['local_nombre']) ?><?php endif; ?>
                </span>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>

<?php endif; ?>

</div><!-- /doc-wrap -->
<script src="<?= $basePath ?>/assets/js/session-guard.js"></script>
</body>
</html>
