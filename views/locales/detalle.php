<?php
/**
 * GET /locales/{id} — checklist de documentación de un local.
 * Espera: $local, $documentos, $categorias, $logs, $gastoAnio, $esAdmin, $filtroEstado.
 */
$docTab   = 'locales';
$docTitle = 'Local ' . ($local['descripcion'] ?? '');
require __DIR__ . '/_header.php';

$hoy = date('Y-m-d');

$fmtDias = function (?int $d): string {
    if ($d === null) return 'No caduca';
    if ($d < 0)  return 'Venció hace ' . abs($d) . ' día' . (abs($d) === 1 ? '' : 's');
    if ($d === 0) return 'Vence hoy';
    return 'Faltan ' . $d . ' día' . ($d === 1 ? '' : 's');
};

// Contadores del local (sobre el set sin filtrar no los tenemos, así que se cuentan aquí)
$conteo = ['VIGENTE' => 0, 'POR_VENCER' => 0, 'VENCIDO' => 0, 'EN_REVISION' => 0,
           'PENDIENTE' => 0, 'OBSERVADO' => 0, 'NO_APLICA' => 0];
foreach ($documentos as $d) {
    $c = $d['estado_calc']['codigo'];
    $conteo[$c] = ($conteo[$c] ?? 0) + 1;
}

// Payload para el JS del modal (evita reconstruir la fila a mano)
$payload = [];
foreach ($documentos as $d) {
    $key = $d['tipo_id'] . '-' . $d['id_local'] . '-' . $d['unidad'];
    $payload[$key] = [
        'tipo_id'          => (int)$d['tipo_id'],
        'local_id'         => (int)$d['id_local'],
        'unidad'           => (int)$d['unidad'],
        'cantidad_requerida'=> (int)$d['cantidad_requerida'],
        'doc_id'           => $d['doc_id'] ? (int)$d['doc_id'] : 0,
        'tipo_nombre'      => $d['tipo_nombre'],
        'tipo_desc'        => $d['tipo_desc'],
        'vigencia'         => $d['vigencia'],
        'tipo_medicion'    => $d['tipo_medicion'],
        'unidad_etiqueta'  => $d['unidad_etiqueta'],
        'periodicidad'     => $d['periodicidad'],
        'duracion_meses'   => $d['duracion_meses'] ? (int)$d['duracion_meses'] : null,
        'costo_referencial'=> $d['costo_referencial'],
        'tipo_entidad'     => $d['tipo_entidad'],
        'estado'           => $d['estado'] ?: 'EN_REVISION',
        'estado_calc'      => $d['estado_calc']['codigo'],
        'numero_documento' => $d['numero_documento'],
        'entidad'          => $d['entidad'],
        'fecha_emision'    => $d['fecha_emision'],
        'fecha_inicio'     => $d['fecha_inicio'],
        'fecha_fin'        => $d['fecha_fin'],
        'costo'            => $d['costo'],
        'observacion'      => $d['observacion'],
        'archivo'          => $d['archivo'],
        'archivo_nombre'   => $d['archivo_nombre'],
        'versiones'        => (int)$d['versiones'],
    ];
}

$estadosFiltro = [
    ''            => 'Todos',
    'VENCIDO'     => 'Vencidos',
    'POR_VENCER'  => 'Por vencer',
    'EN_REVISION' => 'En revisión',
    'OBSERVADO'   => 'Observados',
    'PENDIENTE'   => 'Sin registrar',
    'VIGENTE'     => 'Vigentes',
    'NO_APLICA'   => 'No aplica',
];
?>

<div class="doc-head">
    <div>
        <p class="doc-kicker"><a href="<?= $basePath ?>/locales" style="color:#0097A7;text-decoration:none;">← Locales</a></p>
        <h2 class="doc-title"><?= htmlspecialchars($local['descripcion']) ?></h2>
        <p class="doc-sub">
            <?= htmlspecialchars($local['direccion'] ?: 'Sin dirección registrada') ?>
            · Gasto <?= date('Y') ?>: <strong>S/ <?= number_format($gastoAnio['total'], 2) ?></strong>
        </p>
    </div>
    <div class="doc-actions">
        <a href="<?= $basePath ?>/locales/presupuesto?local=<?= (int)$local['id_local'] ?>" class="doc-btn">💵 Presupuesto</a>
        <a href="<?= $basePath ?>/locales/logs?local=<?= (int)$local['id_local'] ?>" class="doc-btn">🕘 Bitácora</a>
    </div>
</div>

<div class="doc-kpis">
    <div class="doc-kpi doc-kpi--vig"><div class="doc-kpi__num"><?= $conteo['VIGENTE'] ?></div><div class="doc-kpi__lbl">Vigentes</div></div>
    <div class="doc-kpi doc-kpi--pv"><div class="doc-kpi__num"><?= $conteo['POR_VENCER'] ?></div><div class="doc-kpi__lbl">Por vencer</div></div>
    <div class="doc-kpi doc-kpi--ven"><div class="doc-kpi__num"><?= $conteo['VENCIDO'] ?></div><div class="doc-kpi__lbl">Vencidos</div></div>
    <div class="doc-kpi"><div class="doc-kpi__num"><?= $conteo['EN_REVISION'] ?></div><div class="doc-kpi__lbl">En revisión</div></div>
    <div class="doc-kpi"><div class="doc-kpi__num"><?= $conteo['PENDIENTE'] + $conteo['OBSERVADO'] ?></div><div class="doc-kpi__lbl">Pendientes</div></div>
</div>

<div class="doc-panel">
    <p class="doc-panel__title">
        <span>Documentos del local</span>
        <span style="display:flex;gap:.3rem;flex-wrap:wrap;">
            <?php foreach ($estadosFiltro as $k => $lbl): ?>
                <a href="?<?= $k === '' ? '' : 'estado=' . $k ?>"
                   class="doc-btn doc-btn--sm <?= $filtroEstado === $k ? 'doc-btn--primary' : '' ?>"><?= $lbl ?></a>
            <?php endforeach; ?>
        </span>
    </p>

    <?php if (empty($documentos)): ?>
        <div class="doc-empty">
            <span class="doc-empty__ico">📂</span>
            <?= $filtroEstado !== '' ? 'Ningún documento en ese estado.' : 'Este local no tiene documentos asignados en el catálogo.' ?>
        </div>
    <?php else: ?>
    <div class="doc-table__scroll">
        <table class="doc-table">
            <thead>
                <tr>
                    <th>Documento</th>
                    <th>N.º / Entidad</th>
                    <th>Vigencia</th>
                    <th>Estado</th>
                    <th>Firma</th>
                    <th class="doc-num">Costo</th>
                    <th>Archivo</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($documentos as $d):
                $ec    = $d['estado_calc'];
                $key   = $d['tipo_id'] . '-' . $d['id_local'] . '-' . $d['unidad'];
                $esFijo = $d['vigencia'] === 'FIJO';
                $esPeriodico = $d['vigencia'] === 'PERIODICO';
                $esInventario = $d['tipo_medicion'] === 'INVENTARIO';
                $dias  = $ec['dias'];
            ?>
                <tr id="row-<?= $key ?>">
                    <td>
                        <div style="font-weight:700;color:#1e293b;">
                            <?= htmlspecialchars($d['tipo_nombre']) ?>
                            <?php if ($esInventario): ?>
                                <span class="doc-chip" style="font-weight:700;"><?= (int)$d['unidad'] ?> de <?= (int)$d['cantidad_requerida'] ?></span>
                            <?php endif; ?>
                        </div>
                        <div style="display:flex;gap:.3rem;align-items:center;margin-top:3px;flex-wrap:wrap;">
                            <span class="doc-cat"><?= htmlspecialchars($categorias[$d['categoria']] ?? $d['categoria']) ?></span>
                            <?php if ($esFijo): ?><span class="doc-chip">No caduca</span><?php endif; ?>
                            <?php if ($esPeriodico): ?><span class="doc-chip">🔁 Recurrente</span><?php endif; ?>
                            <?php if (!$d['obligatorio']): ?><span class="doc-chip">Opcional</span><?php endif; ?>
                            <?php if ((int)$d['versiones'] > 1): ?>
                                <span class="doc-chip"><?= (int)$d['versiones'] ?> versiones</span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <?php if (!empty($d['numero_documento'])): ?>
                            <div style="font-weight:600;"><?= htmlspecialchars($d['numero_documento']) ?></div>
                        <?php else: ?>
                            <span class="doc-muted">—</span>
                        <?php endif; ?>
                        <span class="doc-dias"><?= htmlspecialchars($d['entidad'] ?: ($d['tipo_entidad'] ?: '')) ?></span>
                    </td>
                    <td>
                        <?php if ($esFijo): ?>
                            <span class="doc-muted">Permanente</span>
                            <?php if (!empty($d['fecha_emision'])): ?>
                                <span class="doc-dias">Emitido <?= date('d/m/Y', strtotime($d['fecha_emision'])) ?></span>
                            <?php endif; ?>
                        <?php elseif ($esPeriodico): ?>
                            <span class="doc-muted"><?= htmlspecialchars(DocumentoLocalRepository::PERIODICIDADES[$d['periodicidad']] ?? $d['periodicidad']) ?></span>
                            <span class="doc-dias <?= ($dias !== null && $dias < 0) ? 'doc-dias--rojo' : (($dias !== null && $dias <= (int)$d['dias_alerta']) ? 'doc-dias--ambar' : '') ?>">
                                <?= $ec['codigo'] === 'VIGENTE' ? 'Cumplido este periodo' : $fmtDias($dias) ?>
                            </span>
                            <?php if (!empty($d['ultimo_cumplido']['fecha'])): ?>
                                <span class="doc-dias">Último: <?= date('d/m/Y', strtotime($d['ultimo_cumplido']['fecha'])) ?><?= $d['ultimo_cumplido']['quien'] ? ' — ' . htmlspecialchars(trim($d['ultimo_cumplido']['quien'])) : '' ?></span>
                            <?php endif; ?>
                        <?php elseif (!empty($d['fecha_fin'])): ?>
                            <?= $d['fecha_inicio'] ? date('d/m/y', strtotime($d['fecha_inicio'])) . ' → ' : '' ?>
                            <strong><?= date('d/m/Y', strtotime($d['fecha_fin'])) ?></strong>
                            <span class="doc-dias <?= ($dias !== null && $dias < 0) ? 'doc-dias--rojo' : (($dias !== null && $dias <= (int)$d['dias_alerta']) ? 'doc-dias--ambar' : '') ?>">
                                <?= $fmtDias($dias) ?>
                            </span>
                        <?php else: ?>
                            <span class="doc-muted">Sin registrar</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="doc-badge <?= $ec['clase'] ?>"><?= $ec['label'] ?></span>
                        <?php if ($ec['codigo'] === 'EN_REVISION' && !empty(trim($d['revisado_nombre'] ?? ''))): ?>
                            <span class="doc-dias">por <?= htmlspecialchars(trim($d['revisado_nombre'])) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($d['observacion'])): ?>
                            <span class="doc-dias" title="<?= htmlspecialchars($d['observacion']) ?>">
                                💬 <?= htmlspecialchars(mb_strimwidth($d['observacion'], 0, 40, '…')) ?>
                            </span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:.74rem;">
                        <?php if (!empty($d['firmado_en'])): ?>
                            <span style="color:#059669;font-weight:700;">✔ <?= htmlspecialchars(trim($d['firmado_nombre'] ?? '')) ?></span>
                            <span class="doc-dias"><?= date('d/m/Y', strtotime($d['firmado_en'])) ?></span>
                        <?php elseif (!$d['requiere_firma']): ?>
                            <span class="doc-muted">No requiere</span>
                        <?php else: ?>
                            <span class="doc-muted">Sin firmar</span>
                        <?php endif; ?>
                    </td>
                    <td class="doc-num" style="font-weight:700;">
                        <?= $d['costo'] !== null ? 'S/ ' . number_format((float)$d['costo'], 2) : '<span class="doc-muted">—</span>' ?>
                    </td>
                    <td>
                        <?php if (!empty($d['archivo'])): ?>
                            <a href="<?= $basePath ?>/<?= htmlspecialchars($d['archivo']) ?>" target="_blank"
                               class="doc-btn doc-btn--sm" title="<?= htmlspecialchars($d['archivo_nombre'] ?? '') ?>">📎 Ver</a>
                        <?php else: ?>
                            <span class="doc-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="doc-actions">
                            <button class="doc-btn doc-btn--sm" onclick="docEditar('<?= $key ?>')">
                                <?= $d['doc_id'] ? '✎ Editar' : '+ Registrar' ?>
                            </button>

                            <?php if ($d['doc_id'] && $ec['codigo'] !== 'NO_APLICA'): ?>
                                <?php if ($d['estado'] !== 'VIGENTE'): ?>
                                    <button class="doc-btn doc-btn--sm doc-btn--ok"
                                            onclick="docFirmar(<?= (int)$d['doc_id'] ?>, '<?= htmlspecialchars($d['tipo_nombre'], ENT_QUOTES) ?>')">
                                        <?= $esPeriodico ? '✔ Confirmar' : '✔ Firmar' ?>
                                    </button>
                                <?php else: ?>
                                    <button class="doc-btn doc-btn--sm doc-btn--warn"
                                            onclick="docObservar(<?= (int)$d['doc_id'] ?>, '<?= htmlspecialchars($d['tipo_nombre'], ENT_QUOTES) ?>')">⚑ Observar</button>
                                <?php endif; ?>
                                <?php if (!$esPeriodico): ?>
                                    <button class="doc-btn doc-btn--sm" onclick="docRenovar('<?= $key ?>')">↻ Renovar</button>
                                <?php endif; ?>
                            <?php endif; ?>

                            <button class="doc-btn doc-btn--sm" onclick="docHistorial(<?= (int)$d['tipo_id'] ?>, <?= (int)$d['id_local'] ?>, <?= (int)$d['unidad'] ?>)"
                                    title="Historial y bitácora">🕘</button>

                            <?php if ($esAdmin && $d['doc_id']): ?>
                                <button class="doc-btn doc-btn--sm doc-btn--danger"
                                        onclick="docEliminar(<?= (int)$d['doc_id'] ?>)" title="Eliminar registro">✕</button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- ── Bitácora del local ────────────────────────────────────── -->
<div class="doc-panel">
    <p class="doc-panel__title">
        <span>Movimientos recientes de este local</span>
        <a href="<?= $basePath ?>/locales/logs?local=<?= (int)$local['id_local'] ?>" class="doc-btn doc-btn--sm">Ver todo</a>
    </p>
    <?php if (empty($logs)): ?>
        <div class="doc-empty" style="padding:1.2rem;">Sin movimientos todavía.</div>
    <?php else: ?>
    <ul class="doc-log">
        <?php foreach (array_slice($logs, 0, 15) as $l): ?>
        <li>
            <span class="doc-log__when"><?= date('d/m/Y H:i', strtotime($l['creado_en'])) ?></span>
            <span class="doc-acc doc-acc--<?= htmlspecialchars($l['accion']) ?>"><?= htmlspecialchars(str_replace('_', ' ', $l['accion'])) ?></span>
            <span class="doc-log__what">
                <span class="doc-log__who"><?= htmlspecialchars(trim($l['usuario_nombre'] ?? '') ?: 'Sistema') ?></span>
                <?php if (!empty($l['tipo_nombre'])): ?> · <?= htmlspecialchars($l['tipo_nombre']) ?><?php endif; ?>
                <?php if (!empty($l['detalle'])): ?> — <?= htmlspecialchars($l['detalle']) ?><?php endif; ?>
            </span>
        </li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>
</div>

</div><!-- /doc-wrap -->

<!-- ══ Modal: registrar / editar / renovar ══════════════════════ -->
<div class="doc-ov" id="ovDoc" hidden>
    <div class="doc-modal">
        <div class="doc-modal__hd" style="display:flex;justify-content:space-between;align-items:flex-start;">
            <div>
                <h3 id="mdTitulo">Registrar documento</h3>
                <p id="mdSub"></p>
            </div>
            <button class="doc-modal__close" onclick="docCerrar('ovDoc')">&times;</button>
        </div>
        <div class="doc-modal__bd">
            <div id="mdMsg" class="doc-msg"></div>
            <form id="fmDoc" onsubmit="return false;">
                <input type="hidden" id="fdTipo">
                <input type="hidden" id="fdLocal" value="<?= (int)$local['id_local'] ?>">
                <input type="hidden" id="fdUnidad" value="1">
                <input type="hidden" id="fdDocId">
                <input type="hidden" id="fdRenovar" value="0">

                <div class="doc-form-grid">
                    <div class="doc-field">
                        <label>N.º de documento</label>
                        <input type="text" id="fdNumero" placeholder="Ej. 001-2026-MDC">
                    </div>
                    <div class="doc-field">
                        <label>Entidad emisora</label>
                        <input type="text" id="fdEntidad" placeholder="Municipalidad, DIRIS…">
                    </div>
                    <div class="doc-field">
                        <label>Fecha de emisión</label>
                        <input type="date" id="fdEmision">
                    </div>
                    <div class="doc-field" id="wrapInicio">
                        <label>Vigente desde</label>
                        <input type="date" id="fdInicio" onchange="docAutoFin()">
                    </div>
                    <div class="doc-field" id="wrapFin">
                        <label>Vence el</label>
                        <input type="date" id="fdFin">
                        <p class="doc-hint" id="fdHintFin"></p>
                    </div>
                    <div class="doc-field">
                        <label>Costo (S/)</label>
                        <input type="number" id="fdCosto" step="0.01" min="0" placeholder="0.00">
                        <p class="doc-hint" id="fdHintCosto"></p>
                    </div>
                    <div class="doc-field">
                        <label>Estado</label>
                        <select id="fdEstado">
                            <option value="EN_REVISION">En revisión</option>
                            <option value="VIGENTE">Vigente (firmar)</option>
                            <option value="OBSERVADO">Observado</option>
                            <option value="NO_APLICA">No aplica a este local</option>
                        </select>
                    </div>
                    <div class="doc-field doc-field--full">
                        <label>Archivo escaneado (PDF, JPG, PNG · máx. 8 MB)</label>
                        <input type="file" id="fdArchivo" accept=".pdf,.jpg,.jpeg,.png,.webp">
                        <p class="doc-hint" id="fdArchivoActual"></p>
                    </div>
                    <div class="doc-field doc-field--full">
                        <label>Observación</label>
                        <textarea id="fdObs" placeholder="Detalle, condiciones, pendientes…"></textarea>
                    </div>
                </div>
            </form>
        </div>
        <div class="doc-modal__ft">
            <button class="doc-btn" onclick="docCerrar('ovDoc')">Cancelar</button>
            <button class="doc-btn doc-btn--primary" id="btnGuardar" onclick="docGuardar()">Guardar</button>
        </div>
    </div>
</div>

<!-- ══ Modal: firmar / observar ═════════════════════════════════ -->
<div class="doc-ov" id="ovEstado" hidden>
    <div class="doc-modal" style="width:440px;">
        <div class="doc-modal__hd" style="display:flex;justify-content:space-between;align-items:flex-start;">
            <div>
                <h3 id="esTitulo">Firmar documento</h3>
                <p id="esSub"></p>
            </div>
            <button class="doc-modal__close" onclick="docCerrar('ovEstado')">&times;</button>
        </div>
        <div class="doc-modal__bd">
            <div id="esMsg" class="doc-msg"></div>
            <input type="hidden" id="esDocId">
            <input type="hidden" id="esNuevo">
            <div class="doc-field">
                <label id="esLabelObs">Comentario (opcional)</label>
                <textarea id="esObs" placeholder="Motivo, detalle de la revisión…"></textarea>
            </div>
            <p class="doc-hint" id="esNota"></p>
        </div>
        <div class="doc-modal__ft">
            <button class="doc-btn" onclick="docCerrar('ovEstado')">Cancelar</button>
            <button class="doc-btn doc-btn--ok" id="btnEstado" onclick="docConfirmarEstado()">Confirmar</button>
        </div>
    </div>
</div>

<!-- ══ Modal: historial ═════════════════════════════════════════ -->
<div class="doc-ov" id="ovHist" hidden>
    <div class="doc-modal">
        <div class="doc-modal__hd" style="display:flex;justify-content:space-between;align-items:flex-start;">
            <div><h3>Historial del documento</h3><p id="hsSub"></p></div>
            <button class="doc-modal__close" onclick="docCerrar('ovHist')">&times;</button>
        </div>
        <div class="doc-modal__bd" id="hsBody">Cargando…</div>
        <div class="doc-modal__ft"><button class="doc-btn" onclick="docCerrar('ovHist')">Cerrar</button></div>
    </div>
</div>

<script>
const DOC_BASE  = '<?= $basePath ?>';
const DOC_LOCAL = <?= (int)$local['id_local'] ?>;
const DOCS      = <?= json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<script src="<?= $basePath ?>/assets/js/locales.js"></script>
<script src="<?= $basePath ?>/assets/js/session-guard.js"></script>
</body>
</html>
