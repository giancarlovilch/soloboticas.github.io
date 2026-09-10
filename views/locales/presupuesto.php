<?php
/**
 * GET /locales/presupuesto — cuánto cuesta mantener la documentación al día.
 * Espera: $data, $locales, $anios, $categorias, $anio, $localId.
 */
$docTab   = 'presupuesto';
$docTitle = 'Presupuesto de documentación';
require __DIR__ . '/_header.php';

$total   = $data['total'];
$maxCat  = $data['por_categoria'] ? max($data['por_categoria']) : 0;
$nombreLocal = [];
foreach ($locales as $l) $nombreLocal[(int)$l['id_local']] = $l['descripcion'];
?>

<div class="doc-head">
    <div>
        <p class="doc-kicker">Costos</p>
        <h2 class="doc-title">Presupuesto de documentación <?= (int)$anio ?></h2>
        <p class="doc-sub">
            Se cuenta cada documento según su fecha de inicio de vigencia (o emisión).
            <?= count($data['filas']) ?> registro(s) con costo.
        </p>
    </div>
    <div>
        <form method="get" style="display:flex;gap:.4rem;align-items:flex-end;">
            <div class="doc-field">
                <label>Año</label>
                <select name="anio" onchange="this.form.submit()">
                    <?php foreach ($anios as $a): ?>
                        <option value="<?= $a ?>" <?= $a === $anio ? 'selected' : '' ?>><?= $a ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="doc-field">
                <label>Local</label>
                <select name="local" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    <?php foreach ($locales as $l): ?>
                        <option value="<?= (int)$l['id_local'] ?>" <?= $localId === (int)$l['id_local'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($l['descripcion']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>
</div>

<div class="doc-kpis">
    <div class="doc-kpi">
        <div class="doc-kpi__num" style="font-size:1.2rem;">S/ <?= number_format($total, 2) ?></div>
        <div class="doc-kpi__lbl">Total <?= (int)$anio ?></div>
    </div>
    <?php foreach (array_slice($data['por_local'], 0, 4, true) as $lid => $monto): ?>
    <div class="doc-kpi">
        <div class="doc-kpi__num" style="font-size:1.1rem;">S/ <?= number_format($monto, 2) ?></div>
        <div class="doc-kpi__lbl"><?= htmlspecialchars($nombreLocal[$lid] ?? ('Local ' . $lid)) ?></div>
    </div>
    <?php endforeach; ?>
</div>

<?php if (empty($data['filas'])): ?>
    <div class="doc-panel">
        <div class="doc-empty">
            <span class="doc-empty__ico">💵</span>
            No hay costos registrados en <?= (int)$anio ?>.<br>
            El costo se ingresa al registrar o renovar cada documento.
        </div>
    </div>
<?php else: ?>

<div class="doc-panel" style="margin-top:0;">
    <p class="doc-panel__title">Gasto por categoría</p>
    <table class="doc-table">
        <tbody>
        <?php foreach ($data['por_categoria'] as $cat => $monto):
            $pct = $maxCat > 0 ? round($monto / $maxCat * 100) : 0;
        ?>
            <tr>
                <td style="width:200px;font-weight:600;"><?= htmlspecialchars($categorias[$cat] ?? $cat) ?></td>
                <td>
                    <div class="doc-bar" style="height:10px;">
                        <span class="doc-bar__vig" style="width:<?= $pct ?>%"></span>
                    </div>
                </td>
                <td class="doc-num" style="width:110px;font-weight:700;">S/ <?= number_format($monto, 2) ?></td>
                <td class="doc-num doc-muted" style="width:60px;font-size:.72rem;">
                    <?= $total > 0 ? round($monto / $total * 100) : 0 ?>%
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="doc-panel">
    <p class="doc-panel__title">Detalle</p>
    <div class="doc-table__scroll">
        <table class="doc-table">
            <thead>
                <tr>
                    <th>Local</th><th>Documento</th><th>Categoría</th><th>N.º</th>
                    <th>Fecha</th><th class="doc-num">Referencia</th><th class="doc-num">Costo real</th><th class="doc-num">Dif.</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($data['filas'] as $f):
                $fecha = $f['fecha_inicio'] ?: ($f['fecha_emision'] ?: $f['creado_en']);
                $ref   = $f['costo_referencial'] !== null ? (float)$f['costo_referencial'] : null;
                $dif   = $ref !== null ? (float)$f['costo'] - $ref : null;
            ?>
                <tr>
                    <td style="font-weight:600;"><?= htmlspecialchars($f['local_nombre']) ?></td>
                    <td><?= htmlspecialchars($f['tipo_nombre']) ?></td>
                    <td><span class="doc-cat"><?= htmlspecialchars($categorias[$f['categoria']] ?? $f['categoria']) ?></span></td>
                    <td class="doc-muted"><?= htmlspecialchars($f['numero_documento'] ?: '—') ?></td>
                    <td class="doc-muted" style="font-size:.74rem;"><?= date('d/m/Y', strtotime($fecha)) ?></td>
                    <td class="doc-num doc-muted"><?= $ref !== null ? 'S/ ' . number_format($ref, 2) : '—' ?></td>
                    <td class="doc-num" style="font-weight:700;">S/ <?= number_format((float)$f['costo'], 2) ?></td>
                    <td class="doc-num" style="font-size:.74rem;font-weight:700;color:<?= ($dif ?? 0) > 0 ? '#dc2626' : '#059669' ?>;">
                        <?= $dif !== null ? (($dif > 0 ? '+' : '') . number_format($dif, 2)) : '—' ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6" class="doc-num" style="font-weight:800;border-top:1.5px solid #e2e8f0;">Total</td>
                    <td class="doc-num" style="font-weight:800;border-top:1.5px solid #e2e8f0;">S/ <?= number_format($total, 2) ?></td>
                    <td style="border-top:1.5px solid #e2e8f0;"></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<?php endif; ?>

</div><!-- /doc-wrap -->
<script src="<?= $basePath ?>/assets/js/session-guard.js"></script>
</body>
</html>
