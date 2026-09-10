<?php
/**
 * admin/dashboard?page=documentos — Catálogo de documentación de locales.
 * El ADMIN define aquí los "items"; el supervisor solo los llena en /locales.
 * Espera $docDatos = ['tipos', 'locales', 'categorias', 'resumen', 'alertas'].
 */
if (!isset($_SESSION['user_rol'])) exit('Acceso denegado');

$tipos      = $docDatos['tipos']      ?? [];
$localesDoc = $docDatos['locales']    ?? [];
$categorias = $docDatos['categorias'] ?? [];
$resumenDoc = $docDatos['resumen']    ?? [];
$alertasDoc = $docDatos['alertas']    ?? [];

$totVencidos  = array_sum(array_column($resumenDoc, 'VENCIDO'));
$totPorVencer = array_sum(array_column($resumenDoc, 'POR_VENCER'));
$totPend      = array_sum(array_column($resumenDoc, 'PENDIENTE'));

// Agrupar el catálogo por categoría
$porCategoria = [];
foreach ($tipos as $t) $porCategoria[$t['categoria']][] = $t;

$nombreLocal = [];
foreach ($localesDoc as $l) $nombreLocal[(int)$l['id_local']] = $l['descripcion'];

$mediciones     = DocumentoLocalRepository::TIPOS_MEDICION;
$periodicidades = DocumentoLocalRepository::PERIODICIDADES;
$diasSemanaLbl  = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];
?>

<div class="postulantes-container">
    <div class="section-header">
        <div class="header-info">
            <p class="section-kicker">Locales</p>
            <h2>Catálogo de documentación</h2>
        </div>
        <div style="display:flex;gap:.4rem;align-items:center;">
            <a href="<?= $basePath ?>/locales" class="doc-btn">🏪 Ir a Gestión de Locales</a>
            <button class="doc-btn doc-btn--primary" onclick="dtNuevo()">+ Nuevo documento</button>
        </div>
    </div>

    <p style="font-size:.8rem;color:#64748b;margin:0 0 1rem;line-height:1.55;">
        Cada documento que crees aquí aparece automáticamente en la ficha de los locales que le indiques.
        Los supervisores solo cargan los datos (fechas, número, costo, escaneado) y lo firman;
        no pueden crear ni borrar items del catálogo.
    </p>

    <!-- ── Estado general ───────────────────────────────────── -->
    <div class="doc-kpis">
        <div class="doc-kpi"><div class="doc-kpi__num"><?= count($tipos) ?></div><div class="doc-kpi__lbl">Items en catálogo</div></div>
        <div class="doc-kpi doc-kpi--ven"><div class="doc-kpi__num"><?= $totVencidos ?></div><div class="doc-kpi__lbl">Vencidos</div></div>
        <div class="doc-kpi doc-kpi--pv"><div class="doc-kpi__num"><?= $totPorVencer ?></div><div class="doc-kpi__lbl">Por vencer</div></div>
        <div class="doc-kpi"><div class="doc-kpi__num"><?= $totPend ?></div><div class="doc-kpi__lbl">Sin registrar</div></div>
    </div>

    <?php if (!empty($alertasDoc)): ?>
    <div class="doc-panel" style="margin-top:0;">
        <p class="doc-panel__title">
            <span>Atención inmediata</span>
            <a href="<?= $basePath ?>/locales" class="doc-btn doc-btn--sm">Ver todo</a>
        </p>
        <div class="doc-table__scroll">
            <table class="doc-table">
                <thead><tr><th>Local</th><th>Documento</th><th>Vence</th><th>Estado</th></tr></thead>
                <tbody>
                <?php foreach (array_slice($alertasDoc, 0, 8) as $a): $ec = $a['estado_calc']; ?>
                    <tr>
                        <td style="font-weight:700;"><?= htmlspecialchars($a['local_nombre']) ?></td>
                        <td><?= htmlspecialchars($a['tipo_nombre']) ?></td>
                        <td><?= $a['fecha_fin'] ? date('d/m/Y', strtotime($a['fecha_fin'])) : '—' ?></td>
                        <td><span class="doc-badge <?= $ec['clase'] ?>"><?= $ec['label'] ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── Catálogo ─────────────────────────────────────────── -->
    <?php if (empty($tipos)): ?>
        <div class="doc-panel">
            <div class="doc-empty">
                <span class="doc-empty__ico">📄</span>
                Aún no creaste ningún documento.<br>
                Empieza con los típicos: licencia de funcionamiento, certificado de Defensa Civil,
                contrato de alquiler, fumigación, extintores…
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($porCategoria as $cat => $lista): ?>
        <div class="doc-panel">
            <p class="doc-panel__title"><?= htmlspecialchars($categorias[$cat] ?? $cat) ?></p>
            <div class="doc-table__scroll">
                <table class="doc-table">
                    <thead>
                        <tr>
                            <th>Documento</th><th>Vigencia</th><th>Alerta</th>
                            <th class="doc-num">Costo ref.</th><th>Aplica a / cantidad</th><th>Reglas</th><th>Usos</th><th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($lista as $t): ?>
                        <tr style="<?= (int)$t['activo'] === 0 ? 'opacity:.5;' : '' ?>">
                            <td>
                                <div style="font-weight:700;color:#1e293b;"><?= htmlspecialchars($t['nombre']) ?></div>
                                <?php if ($t['tipo_medicion'] === 'INVENTARIO'): ?>
                                    <span class="doc-chip">📦 Inventario (<?= htmlspecialchars($t['unidad_etiqueta']) ?>)</span>
                                <?php endif; ?>
                                <?php if (!empty($t['descripcion'])): ?>
                                    <span class="doc-dias"><?= htmlspecialchars($t['descripcion']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($t['entidad'])): ?>
                                    <span class="doc-dias">Entidad: <?= htmlspecialchars($t['entidad']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($t['vigencia'] === 'FIJO'): ?>
                                    <span class="doc-badge doc-badge--na">No caduca</span>
                                <?php elseif ($t['vigencia'] === 'PERIODICO'): ?>
                                    <span class="doc-badge doc-badge--rev">🔁 <?= htmlspecialchars($periodicidades[$t['periodicidad']] ?? $t['periodicidad']) ?></span>
                                    <?php if ($t['periodicidad'] === 'MENSUAL'): ?>
                                        <span class="doc-dias">Día <?= (int)$t['dia_vencimiento'] ?> de cada mes</span>
                                    <?php elseif ($t['periodicidad'] === 'SEMANAL'): ?>
                                        <span class="doc-dias"><?= htmlspecialchars($diasSemanaLbl[(int)$t['dia_vencimiento']] ?? '') ?> de cada semana</span>
                                    <?php else: ?>
                                        <span class="doc-dias">Todos los días</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="doc-badge doc-badge--rev">Caduca</span>
                                    <?php if (!empty($t['duracion_meses'])): ?>
                                        <span class="doc-dias">Cada <?= (int)$t['duracion_meses'] ?> mes(es)</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td class="doc-muted" style="font-size:.74rem;">
                                <?= $t['vigencia'] === 'FIJO' ? '—' : ((int)$t['dias_alerta'] . ' días antes') ?>
                            </td>
                            <td class="doc-num">
                                <?= $t['costo_referencial'] !== null
                                    ? 'S/ ' . number_format((float)$t['costo_referencial'], 2)
                                    : '<span class="doc-muted">—</span>' ?>
                            </td>
                            <td style="font-size:.74rem;">
                                <?php if ($t['alcance'] === 'TODOS'): ?>
                                    Todos los locales
                                <?php elseif (empty($t['locales'])): ?>
                                    <span style="color:#dc2626;font-weight:700;">Ningún local asignado</span>
                                <?php else: ?>
                                    <?= htmlspecialchars(implode(', ', array_map(
                                        fn($id) => $nombreLocal[$id] ?? ('#' . $id), $t['locales']
                                    ))) ?>
                                <?php endif; ?>
                                <?php if ($t['tipo_medicion'] === 'INVENTARIO' && !empty($t['cantidades'])): ?>
                                    <div class="doc-dias">
                                        <?php $partes = []; foreach ($t['cantidades'] as $lid => $c) { $partes[] = ($nombreLocal[$lid] ?? "#$lid") . ": $c"; } ?>
                                        <?= htmlspecialchars(implode(' · ', $partes)) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display:flex;gap:.25rem;flex-wrap:wrap;">
                                    <?php if ((int)$t['obligatorio'] === 1): ?><span class="doc-chip doc-chip--ven">Obligatorio</span><?php endif; ?>
                                    <?php if ((int)$t['requiere_firma'] === 1): ?><span class="doc-chip doc-chip--rev">Requiere firma</span><?php endif; ?>
                                    <?php if ((int)$t['activo'] === 0): ?><span class="doc-chip">Inactivo</span><?php endif; ?>
                                </div>
                            </td>
                            <td class="doc-num doc-muted"><?= (int)$t['total_docs'] ?></td>
                            <td>
                                <div class="doc-actions">
                                    <button class="doc-btn doc-btn--sm" onclick="dtEditar(<?= (int)$t['id'] ?>)">✎</button>
                                    <button class="doc-btn doc-btn--sm" onclick="dtToggle(<?= (int)$t['id'] ?>)">
                                        <?= (int)$t['activo'] === 1 ? 'Desactivar' : 'Activar' ?>
                                    </button>
                                    <?php if ((int)$t['total_docs'] === 0): ?>
                                        <button class="doc-btn doc-btn--sm doc-btn--danger" onclick="dtEliminar(<?= (int)$t['id'] ?>)">✕</button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- ══ Modal: crear / editar tipo ══════════════════════════════ -->
<div class="doc-ov" id="ovTipo" hidden>
    <div class="doc-modal">
        <div class="doc-modal__hd" style="display:flex;justify-content:space-between;align-items:flex-start;">
            <div>
                <h3 id="dtTitulo">Nuevo documento</h3>
                <p>Define cómo se controla este papel en todos los locales.</p>
            </div>
            <button class="doc-modal__close" onclick="document.getElementById('ovTipo').hidden = true">&times;</button>
        </div>
        <div class="doc-modal__bd">
            <div id="dtMsg" class="doc-msg"></div>
            <input type="hidden" id="dtId">

            <div class="doc-form-grid">
                <div class="doc-field doc-field--full">
                    <label>Nombre del documento *</label>
                    <input type="text" id="dtNombre" placeholder="Licencia de funcionamiento">
                </div>
                <div class="doc-field">
                    <label>Categoría</label>
                    <select id="dtCategoria">
                        <?php foreach ($categorias as $k => $v): ?>
                            <option value="<?= $k ?>"><?= htmlspecialchars($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="doc-field">
                    <label>Entidad emisora habitual</label>
                    <input type="text" id="dtEntidad" placeholder="Municipalidad distrital">
                </div>
                <div class="doc-field doc-field--full">
                    <label>Descripción / instrucción para el supervisor</label>
                    <textarea id="dtDesc" placeholder="Qué debe adjuntar, dónde se tramita, qué mirar…"></textarea>
                </div>

                <div class="doc-field">
                    <label>¿Cómo controla su vigencia?</label>
                    <select id="dtVigencia" onchange="dtToggleVigencia()">
                        <option value="VENCE">Caduca (licencia, contrato…)</option>
                        <option value="FIJO">No caduca (permanente)</option>
                        <option value="PERIODICO">Es una obligación que se repite (revisión diaria, reporte mensual…)</option>
                    </select>
                </div>
                <div class="doc-field" id="dtWrapDuracion">
                    <label>Duración (meses)</label>
                    <input type="number" id="dtDuracion" min="1" max="240" placeholder="12">
                    <p class="doc-hint">Se usa para autocompletar la fecha de vencimiento.</p>
                </div>
                <div class="doc-field" id="dtWrapPeriodicidad" style="display:none;">
                    <label>Se repite</label>
                    <select id="dtPeriodicidad" onchange="dtTogglePeriodicidad()">
                        <?php foreach ($periodicidades as $k => $v): ?>
                            <option value="<?= $k ?>"><?= htmlspecialchars($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="doc-field" id="dtWrapDiaVenc" style="display:none;">
                    <label id="dtLblDiaVenc">Día de vencimiento</label>
                    <select id="dtDiaVencSemana" style="display:none;">
                        <?php foreach ($diasSemanaLbl as $n => $lbl): ?>
                            <option value="<?= $n ?>"><?= $lbl ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" id="dtDiaVencMes" min="1" max="31" value="15" style="display:none;">
                    <p class="doc-hint" id="dtHintDiaVenc"></p>
                </div>
                <div class="doc-field" id="dtWrapAlerta">
                    <label>Avisar días antes</label>
                    <input type="number" id="dtAlerta" min="0" max="365" value="30">
                    <p class="doc-hint">Debajo de esto se marca "por vencer" (campanita amarilla). Vencido = campanita roja.</p>
                </div>
                <div class="doc-field">
                    <label>Costo referencial (S/)</label>
                    <input type="number" id="dtCosto" step="0.01" min="0" placeholder="0.00">
                    <p class="doc-hint">Opcional — para comparar contra el gasto real.</p>
                </div>

                <div class="doc-field" id="dtWrapMedicion">
                    <label>¿Cómo se registra?</label>
                    <select id="dtMedicion" onchange="dtToggleMedicion()">
                        <?php foreach ($mediciones as $k => $v): ?>
                            <option value="<?= $k ?>"><?= htmlspecialchars($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="doc-field" id="dtWrapUnidadEtq" style="display:none;">
                    <label>¿Cómo se llama cada unidad?</label>
                    <input type="text" id="dtUnidadEtq" placeholder="termohigrómetro, extintor…">
                </div>

                <div class="doc-field doc-field--full">
                    <label>¿A qué locales aplica?</label>
                    <select id="dtAlcance" onchange="dtToggleAlcance()">
                        <option value="TODOS">Todos los locales activos</option>
                        <option value="ESPECIFICOS">Solo locales específicos</option>
                    </select>
                </div>
                <div class="doc-field doc-field--full" id="dtWrapLocales" style="display:none;">
                    <label>Locales</label>
                    <div style="display:flex;gap:.75rem;flex-wrap:wrap;padding:.4rem 0;">
                        <?php foreach ($localesDoc as $l): ?>
                            <label class="doc-check">
                                <input type="checkbox" class="dt-local" value="<?= (int)$l['id_local'] ?>">
                                <?= htmlspecialchars($l['descripcion']) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="doc-field doc-field--full" id="dtWrapCantidades" style="display:none;">
                    <label>¿Cuántas unidades necesita cada local?</label>
                    <div style="display:flex;gap:.75rem;flex-wrap:wrap;padding:.4rem 0;">
                        <?php foreach ($localesDoc as $l): ?>
                            <div style="min-width:120px;">
                                <label style="font-weight:600;font-size:.72rem;"><?= htmlspecialchars($l['descripcion']) ?></label>
                                <input type="number" class="dt-cantidad" data-local="<?= (int)$l['id_local'] ?>" min="0" max="99" value="1"
                                       style="padding:.3rem .5rem;border:1.5px solid #e2e8f0;border-radius:6px;width:100%;">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <p class="doc-hint">Pon 0 en el local que no lo necesite. Cada unidad se gestiona por separado (su propia fecha, costo y archivo).</p>
                </div>

                <div class="doc-field doc-field--full">
                    <div style="display:flex;gap:1.25rem;flex-wrap:wrap;">
                        <label class="doc-check"><input type="checkbox" id="dtObligatorio" checked> Obligatorio</label>
                        <label class="doc-check"><input type="checkbox" id="dtFirma" checked> Requiere firma de supervisor</label>
                    </div>
                </div>
                <div class="doc-field">
                    <label>Orden en la lista</label>
                    <input type="number" id="dtOrden" value="0" min="0">
                </div>
            </div>
        </div>
        <div class="doc-modal__ft">
            <button class="doc-btn" onclick="document.getElementById('ovTipo').hidden = true">Cancelar</button>
            <button class="doc-btn doc-btn--primary" id="dtBtnGuardar" onclick="dtGuardar()">Guardar</button>
        </div>
    </div>
</div>

<script>
const DT_TIPOS = <?= json_encode(array_column($tipos, null, 'id'), JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

function dtMsg(texto, ok) {
    const el = document.getElementById('dtMsg');
    el.textContent = texto;
    el.className = 'doc-msg ' + (ok ? 'doc-msg--ok' : 'doc-msg--err');
    el.style.display = 'block';
}

function dtToggleVigencia() {
    const v = document.getElementById('dtVigencia').value;
    document.getElementById('dtWrapDuracion').style.display     = (v === 'VENCE') ? '' : 'none';
    document.getElementById('dtWrapAlerta').style.display       = (v === 'FIJO')  ? 'none' : '';
    document.getElementById('dtWrapPeriodicidad').style.display = (v === 'PERIODICO') ? '' : 'none';
    document.getElementById('dtWrapDiaVenc').style.display      = (v === 'PERIODICO') ? '' : 'none';
    // Un periódico (libro de recetas, reporte mensual) es siempre "un documento":
    // no tiene sentido llevarlo como inventario de varias unidades.
    document.getElementById('dtWrapMedicion').style.display = (v === 'PERIODICO') ? 'none' : '';
    if (v === 'PERIODICO') {
        document.getElementById('dtMedicion').value = 'DOCUMENTO';
        dtToggleMedicion();
        dtTogglePeriodicidad();
    }
}

function dtTogglePeriodicidad() {
    const p = document.getElementById('dtPeriodicidad').value;
    const selSemana = document.getElementById('dtDiaVencSemana');
    const inpMes    = document.getElementById('dtDiaVencMes');
    selSemana.style.display = (p === 'SEMANAL') ? '' : 'none';
    inpMes.style.display    = (p === 'MENSUAL') ? '' : 'none';
    document.getElementById('dtWrapDiaVenc').style.display = (p === 'DIARIO') ? 'none' : '';
    const hint = document.getElementById('dtHintDiaVenc');
    hint.textContent = p === 'DIARIO'
        ? 'Se revisa todos los días; no hace falta elegir un día.'
        : p === 'SEMANAL'
            ? 'Ese día de cada semana debe estar hecho.'
            : 'Ese día de cada mes debe estar hecho (si el mes es más corto, se ajusta al último día).';
}

function dtToggleMedicion() {
    const inv = document.getElementById('dtMedicion').value === 'INVENTARIO';
    document.getElementById('dtWrapUnidadEtq').style.display  = inv ? '' : 'none';
    document.getElementById('dtWrapCantidades').style.display = inv ? '' : 'none';
}

function dtToggleAlcance() {
    const esp = document.getElementById('dtAlcance').value === 'ESPECIFICOS';
    document.getElementById('dtWrapLocales').style.display = esp ? '' : 'none';
}

function dtNuevo() {
    document.getElementById('dtMsg').style.display = 'none';
    document.getElementById('dtTitulo').textContent = 'Nuevo documento';
    document.getElementById('dtId').value = '';
    document.getElementById('dtNombre').value = '';
    document.getElementById('dtCategoria').value = 'GENERAL';
    document.getElementById('dtEntidad').value = '';
    document.getElementById('dtDesc').value = '';
    document.getElementById('dtVigencia').value = 'VENCE';
    document.getElementById('dtDuracion').value = '';
    document.getElementById('dtPeriodicidad').value = 'MENSUAL';
    document.getElementById('dtDiaVencSemana').value = 5;
    document.getElementById('dtDiaVencMes').value = 15;
    document.getElementById('dtAlerta').value = 30;
    document.getElementById('dtCosto').value = '';
    document.getElementById('dtMedicion').value = 'DOCUMENTO';
    document.getElementById('dtUnidadEtq').value = '';
    document.getElementById('dtAlcance').value = 'TODOS';
    document.getElementById('dtObligatorio').checked = true;
    document.getElementById('dtFirma').checked = true;
    document.getElementById('dtOrden').value = 0;
    document.querySelectorAll('.dt-local').forEach(c => c.checked = false);
    document.querySelectorAll('.dt-cantidad').forEach(c => c.value = 1);
    dtToggleVigencia();
    dtToggleMedicion();
    dtToggleAlcance();
    document.getElementById('ovTipo').hidden = false;
    document.getElementById('dtNombre').focus();
}

function dtEditar(id) {
    const t = DT_TIPOS[id];
    if (!t) return;
    document.getElementById('dtMsg').style.display = 'none';
    document.getElementById('dtTitulo').textContent = 'Editar documento';
    document.getElementById('dtId').value = t.id;
    document.getElementById('dtNombre').value = t.nombre || '';
    document.getElementById('dtCategoria').value = t.categoria || 'GENERAL';
    document.getElementById('dtEntidad').value = t.entidad || '';
    document.getElementById('dtDesc').value = t.descripcion || '';
    document.getElementById('dtVigencia').value = t.vigencia || 'VENCE';
    document.getElementById('dtDuracion').value = t.duracion_meses || '';
    document.getElementById('dtPeriodicidad').value = t.periodicidad || 'MENSUAL';
    document.getElementById('dtDiaVencSemana').value = t.periodicidad === 'SEMANAL' ? (t.dia_vencimiento || 5) : 5;
    document.getElementById('dtDiaVencMes').value = t.periodicidad === 'MENSUAL' ? (t.dia_vencimiento || 15) : 15;
    document.getElementById('dtAlerta').value = t.dias_alerta ?? 30;
    document.getElementById('dtCosto').value = t.costo_referencial ?? '';
    document.getElementById('dtMedicion').value = t.tipo_medicion || 'DOCUMENTO';
    document.getElementById('dtUnidadEtq').value = t.unidad_etiqueta || '';
    document.getElementById('dtAlcance').value = t.alcance || 'TODOS';
    document.getElementById('dtObligatorio').checked = Number(t.obligatorio) === 1;
    document.getElementById('dtFirma').checked = Number(t.requiere_firma) === 1;
    document.getElementById('dtOrden').value = t.orden ?? 0;

    const asignados = (t.locales || []).map(Number);
    document.querySelectorAll('.dt-local').forEach(c => { c.checked = asignados.includes(Number(c.value)); });

    const cantidades = t.cantidades || {};
    document.querySelectorAll('.dt-cantidad').forEach(c => {
        const lid = c.dataset.local;
        c.value = (lid in cantidades) ? cantidades[lid] : 1;
    });

    dtToggleVigencia();
    dtTogglePeriodicidad();
    dtToggleMedicion();
    dtToggleAlcance();
    document.getElementById('ovTipo').hidden = false;
}

async function dtGuardar() {
    const nombre = document.getElementById('dtNombre').value.trim();
    if (!nombre) { dtMsg('El nombre es obligatorio.', false); return; }

    const alcance = document.getElementById('dtAlcance').value;
    const locales = [...document.querySelectorAll('.dt-local:checked')].map(c => Number(c.value));
    if (alcance === 'ESPECIFICOS' && locales.length === 0) {
        dtMsg('Selecciona al menos un local.', false); return;
    }

    const vigencia = document.getElementById('dtVigencia').value;
    const periodicidad = document.getElementById('dtPeriodicidad').value;
    const diaVencimiento = periodicidad === 'SEMANAL'
        ? document.getElementById('dtDiaVencSemana').value
        : document.getElementById('dtDiaVencMes').value;

    const medicion = document.getElementById('dtMedicion').value;
    const cantidades = {};
    if (medicion === 'INVENTARIO') {
        document.querySelectorAll('.dt-cantidad').forEach(c => { cantidades[c.dataset.local] = c.value || 0; });
    }

    const data = {
        id:                document.getElementById('dtId').value || 0,
        nombre,
        categoria:         document.getElementById('dtCategoria').value,
        entidad:           document.getElementById('dtEntidad').value.trim(),
        descripcion:       document.getElementById('dtDesc').value.trim(),
        vigencia,
        duracion_meses:    document.getElementById('dtDuracion').value,
        periodicidad,
        dia_vencimiento:   diaVencimiento,
        dias_alerta:       document.getElementById('dtAlerta').value || 30,
        costo_referencial: document.getElementById('dtCosto').value,
        tipo_medicion:     medicion,
        unidad_etiqueta:   document.getElementById('dtUnidadEtq').value.trim(),
        cantidades,
        alcance,
        locales,
        obligatorio:       document.getElementById('dtObligatorio').checked ? 1 : 0,
        requiere_firma:    document.getElementById('dtFirma').checked ? 1 : 0,
        orden:             document.getElementById('dtOrden').value || 0,
    };

    const btn = document.getElementById('dtBtnGuardar');
    btn.disabled = true;
    btn.textContent = 'Guardando…';
    try {
        const r = await fetch(`${BASE}/admin/api/documento-tipo/guardar`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data),
        });
        const res = await r.json();
        if (res.success) { dtMsg(res.message, true); setTimeout(() => location.reload(), 700); }
        else dtMsg(res.message || 'No se pudo guardar.', false);
    } catch (e) {
        dtMsg('Error de conexión.', false);
    } finally {
        btn.disabled = false;
        btn.textContent = 'Guardar';
    }
}

async function dtToggle(id) {
    const r = await fetch(`${BASE}/admin/api/documento-tipo/${id}/toggle`, {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
    });
    const res = await r.json();
    if (res.success) location.reload();
    else alert(res.message || 'Error.');
}

async function dtEliminar(id) {
    if (!confirm('¿Eliminar este documento del catálogo? Solo se puede si nunca se usó.')) return;
    const r = await fetch(`${BASE}/admin/api/documento-tipo/${id}/eliminar`, {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
    });
    const res = await r.json();
    if (res.success) location.reload();
    else alert(res.message || 'Error.');
}
</script>
