/* ═══════════════════════════════════════════════════════════
   Gestión de Locales — documentos de un local
   Depende de DOC_BASE, DOC_LOCAL y DOCS (inyectados en la vista).
   ═══════════════════════════════════════════════════════════ */

const docApi = (p) => `${DOC_BASE}${p}`;

function docMsg(id, texto, ok) {
    const el = document.getElementById(id);
    if (!el) return;
    el.textContent = texto;
    el.className = 'doc-msg ' + (ok ? 'doc-msg--ok' : 'doc-msg--err');
    el.style.display = 'block';
}

function docLimpiarMsg(id) {
    const el = document.getElementById(id);
    if (el) el.style.display = 'none';
}

function docCerrar(id) {
    document.getElementById(id).hidden = true;
}

function docAbrir(id) {
    document.getElementById(id).hidden = false;
}

document.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape') return;
    ['ovDoc', 'ovEstado', 'ovHist'].forEach((id) => {
        const el = document.getElementById(id);
        if (el) el.hidden = true;
    });
});

// ─────────────────────────────────────────────────────────────
//  Registrar / editar / renovar
// ─────────────────────────────────────────────────────────────

/** Suma meses a una fecha ISO respetando fin de mes. */
function docSumarMeses(iso, meses) {
    const [a, m, d] = iso.split('-').map(Number);
    const f = new Date(a, m - 1 + meses, d);
    // Si el día se desbordó (31 ene + 1 mes), retrocedemos al último del mes
    if (f.getDate() !== d) f.setDate(0);
    return f.toISOString().slice(0, 10);
}

function docAutoFin() {
    const t = DOCS[document.getElementById('fdTipo').dataset.key];
    if (!t || t.vigencia === 'FIJO' || !t.duracion_meses) return;

    const ini = document.getElementById('fdInicio').value;
    const fin = document.getElementById('fdFin');
    if (ini && !fin.value) fin.value = docSumarMeses(ini, t.duracion_meses);
}

function docPrepararModal(key, renovar) {
    const t = DOCS[key];
    if (!t) return;

    docLimpiarMsg('mdMsg');

    const tipoInput = document.getElementById('fdTipo');
    tipoInput.value = t.tipo_id;
    tipoInput.dataset.key = key;
    document.getElementById('fdLocal').value = t.local_id;
    document.getElementById('fdUnidad').value = t.unidad || 1;
    document.getElementById('fdRenovar').value = renovar ? '1' : '0';
    document.getElementById('fdDocId').value = renovar ? '' : (t.doc_id || '');

    const esNuevo = renovar || !t.doc_id;
    const esPeriodico = t.vigencia === 'PERIODICO';
    const unidadTag = (t.tipo_medicion === 'INVENTARIO')
        ? ` — ${t.unidad_etiqueta || 'unidad'} ${t.unidad} de ${t.cantidad_requerida}` : '';

    document.getElementById('mdTitulo').textContent = esPeriodico
        ? (t.doc_id ? 'Editar cumplimiento' : 'Confirmar cumplimiento')
        : (renovar ? 'Renovar documento' : (t.doc_id ? 'Editar documento' : 'Registrar documento'));
    document.getElementById('mdSub').textContent =
        t.tipo_nombre + unidadTag + (t.tipo_desc ? ' — ' + t.tipo_desc : '');

    // Datos: en renovación (o periódico nuevo) arrancamos en blanco salvo la entidad
    document.getElementById('fdNumero').value  = esNuevo ? '' : (t.numero_documento || '');
    document.getElementById('fdEntidad').value = renovar ? (t.entidad || t.tipo_entidad || '')
                                                         : (t.entidad || (t.doc_id ? '' : (t.tipo_entidad || '')));
    document.getElementById('fdEmision').value = esNuevo ? '' : (t.fecha_emision || '');
    document.getElementById('fdInicio').value  = esNuevo ? '' : (t.fecha_inicio || '');
    document.getElementById('fdFin').value     = esNuevo ? '' : (t.fecha_fin || '');
    document.getElementById('fdCosto').value   = esNuevo ? '' : (t.costo ?? '');
    document.getElementById('fdObs').value     = esNuevo ? '' : (t.observacion || '');
    document.getElementById('fdEstado').value  = esNuevo ? 'EN_REVISION' : (t.estado || 'EN_REVISION');
    document.getElementById('fdArchivo').value = '';

    document.getElementById('fdArchivoActual').textContent =
        (!esNuevo && t.archivo_nombre) ? `Actual: ${t.archivo_nombre} (subir otro lo reemplaza)` : '';

    // Documento fijo: no se piden fechas de vigencia. Periódico: la fecha la pone
    // el servidor sola (siempre HOY), así que ni se muestra el campo.
    const fijo = t.vigencia === 'FIJO';
    document.getElementById('wrapInicio').style.display = esPeriodico ? 'none' : '';
    document.getElementById('wrapFin').style.display = (fijo || esPeriodico) ? 'none' : '';
    document.getElementById('fdHintFin').textContent =
        (!fijo && !esPeriodico && t.duracion_meses) ? `Se autocompleta a ${t.duracion_meses} mes(es) desde la fecha de inicio.` : '';
    document.getElementById('fdHintCosto').textContent =
        t.costo_referencial ? `Referencia: S/ ${Number(t.costo_referencial).toFixed(2)}` : '';

    docAbrir('ovDoc');
    document.getElementById('fdNumero').focus();
}

function docEditar(key)  { docPrepararModal(key, false); }
function docRenovar(key) { docPrepararModal(key, true); }

async function docGuardar() {
    const btn = document.getElementById('btnGuardar');
    const fd  = new FormData();

    fd.append('tipo_id',          document.getElementById('fdTipo').value);
    fd.append('local_id',         document.getElementById('fdLocal').value);
    fd.append('unidad',           document.getElementById('fdUnidad').value || '1');
    fd.append('doc_id',           document.getElementById('fdDocId').value || '0');
    fd.append('renovar',          document.getElementById('fdRenovar').value);
    fd.append('numero_documento', document.getElementById('fdNumero').value.trim());
    fd.append('entidad',          document.getElementById('fdEntidad').value.trim());
    fd.append('fecha_emision',    document.getElementById('fdEmision').value);
    fd.append('fecha_inicio',     document.getElementById('fdInicio').value);
    fd.append('fecha_fin',        document.getElementById('fdFin').value);
    fd.append('costo',            document.getElementById('fdCosto').value);
    fd.append('observacion',      document.getElementById('fdObs').value.trim());
    fd.append('estado',           document.getElementById('fdEstado').value);

    const file = document.getElementById('fdArchivo').files[0];
    if (file) fd.append('archivo', file);

    btn.disabled = true;
    btn.textContent = 'Guardando…';
    try {
        const r   = await fetch(docApi('/locales/api/documento/guardar'), { method: 'POST', body: fd });
        const res = await r.json();
        if (res.success) {
            docMsg('mdMsg', res.message, true);
            setTimeout(() => location.reload(), 700);
        } else {
            docMsg('mdMsg', res.message || 'No se pudo guardar.', false);
        }
    } catch (e) {
        docMsg('mdMsg', 'Error de conexión.', false);
    } finally {
        btn.disabled = false;
        btn.textContent = 'Guardar';
    }
}

// ─────────────────────────────────────────────────────────────
//  Firmar / observar
// ─────────────────────────────────────────────────────────────

function docFirmar(docId, nombre) {
    docLimpiarMsg('esMsg');
    document.getElementById('esDocId').value = docId;
    document.getElementById('esNuevo').value = 'VIGENTE';
    document.getElementById('esTitulo').textContent = 'Firmar documento';
    document.getElementById('esSub').textContent = nombre;
    document.getElementById('esLabelObs').textContent = 'Comentario (opcional)';
    document.getElementById('esObs').value = '';
    document.getElementById('esNota').textContent =
        'Al firmar queda registrado tu nombre y la fecha. El documento pasa a VIGENTE hasta su fecha de vencimiento.';
    const btn = document.getElementById('btnEstado');
    btn.className = 'doc-btn doc-btn--ok';
    btn.textContent = '✔ Firmar';
    docAbrir('ovEstado');
}

function docObservar(docId, nombre) {
    docLimpiarMsg('esMsg');
    document.getElementById('esDocId').value = docId;
    document.getElementById('esNuevo').value = 'OBSERVADO';
    document.getElementById('esTitulo').textContent = 'Observar documento';
    document.getElementById('esSub').textContent = nombre;
    document.getElementById('esLabelObs').textContent = 'Motivo de la observación';
    document.getElementById('esObs').value = '';
    document.getElementById('esNota').textContent =
        'El documento sale de VIGENTE y la firma anterior queda sin efecto.';
    const btn = document.getElementById('btnEstado');
    btn.className = 'doc-btn doc-btn--warn';
    btn.textContent = '⚑ Observar';
    docAbrir('ovEstado');
}

async function docConfirmarEstado() {
    const btn   = document.getElementById('btnEstado');
    const docId = document.getElementById('esDocId').value;
    const nuevo = document.getElementById('esNuevo').value;
    const obs   = document.getElementById('esObs').value.trim();

    if (nuevo === 'OBSERVADO' && obs === '') {
        docMsg('esMsg', 'Indica el motivo de la observación.', false);
        return;
    }

    const textoOriginal = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Procesando…';
    try {
        const r = await fetch(docApi(`/locales/api/documento/${docId}/estado`), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ estado: nuevo, observacion: obs }),
        });
        const res = await r.json();
        if (res.success) {
            docMsg('esMsg', res.message, true);
            setTimeout(() => location.reload(), 700);
        } else {
            docMsg('esMsg', res.message || 'No se pudo actualizar.', false);
        }
    } catch (e) {
        docMsg('esMsg', 'Error de conexión.', false);
    } finally {
        btn.disabled = false;
        btn.textContent = textoOriginal;
    }
}

// ─────────────────────────────────────────────────────────────
//  Eliminar (solo ADMIN)
// ─────────────────────────────────────────────────────────────

async function docEliminar(docId) {
    if (!confirm('¿Eliminar este registro? Se borra también su archivo adjunto. El periodo anterior (si existe) vuelve a quedar como vigente.')) return;

    const r   = await fetch(docApi(`/locales/api/documento/${docId}/eliminar`), {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
    });
    const res = await r.json();
    if (res.success) location.reload();
    else alert(res.message || 'No se pudo eliminar.');
}

// ─────────────────────────────────────────────────────────────
//  Historial
// ─────────────────────────────────────────────────────────────

const docFecha = (f) => (f ? new Date(f + 'T00:00:00').toLocaleDateString('es-PE') : '—');
const docFechaHora = (f) => (f ? new Date(f.replace(' ', 'T')).toLocaleString('es-PE', {
    day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit',
}) : '—');

const docEsc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => (
    { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]
));

async function docHistorial(tipoId, localId, unidad) {
    document.getElementById('hsBody').innerHTML = 'Cargando…';
    document.getElementById('hsSub').textContent = '';
    docAbrir('ovHist');

    const r   = await fetch(docApi(`/locales/api/historial?tipo=${tipoId}&local=${localId}&unidad=${unidad || 1}`));
    const res = await r.json();
    if (!res.success) {
        document.getElementById('hsBody').innerHTML = '<p class="doc-muted">No se pudo cargar el historial.</p>';
        return;
    }

    const { historial, logs } = res.data;
    document.getElementById('hsSub').textContent = `${historial.length} periodo(s) registrado(s)`;

    let html = '';

    if (historial.length === 0) {
        html += '<p class="doc-muted">Todavía no hay ningún periodo registrado.</p>';
    } else {
        html += '<p class="doc-panel__title" style="margin-top:0;">Periodos de vigencia</p>';
        html += '<div class="doc-table__scroll"><table class="doc-table"><thead><tr>' +
                '<th>Vigencia</th><th>N.º</th><th>Estado</th><th>Firmó</th><th class="doc-num">Costo</th><th></th>' +
                '</tr></thead><tbody>';
        historial.forEach((h) => {
            const archivo = h.archivo
                ? `<a href="${DOC_BASE}/${docEsc(h.archivo)}" target="_blank" class="doc-btn doc-btn--sm">📎</a>`
                : '<span class="doc-muted">—</span>';
            html += `<tr>
                <td>${h.fecha_inicio ? docFecha(h.fecha_inicio) : '—'} → ${h.fecha_fin ? docFecha(h.fecha_fin) : 'No caduca'}
                    ${Number(h.es_actual) === 1 ? '<span class="doc-chip doc-chip--vig">Actual</span>' : ''}</td>
                <td>${docEsc(h.numero_documento || '—')}</td>
                <td><span class="doc-badge ${h.estado_calc.clase}">${h.estado_calc.label}</span></td>
                <td style="font-size:.74rem;">${docEsc((h.firmado_nombre || '').trim() || '—')}</td>
                <td class="doc-num">${h.costo !== null ? 'S/ ' + Number(h.costo).toFixed(2) : '—'}</td>
                <td>${archivo}</td>
            </tr>`;
        });
        html += '</tbody></table></div>';
    }

    html += '<p class="doc-panel__title" style="margin-top:1.2rem;">Bitácora</p>';
    if (logs.length === 0) {
        html += '<p class="doc-muted">Sin movimientos.</p>';
    } else {
        html += '<ul class="doc-log">';
        logs.forEach((l) => {
            html += `<li>
                <span class="doc-log__when">${docFechaHora(l.creado_en)}</span>
                <span class="doc-acc doc-acc--${docEsc(l.accion)}">${docEsc(l.accion.replace(/_/g, ' '))}</span>
                <span class="doc-log__what">
                    <span class="doc-log__who">${docEsc((l.usuario_nombre || '').trim() || 'Sistema')}</span>
                    ${l.usuario_rol ? ' (' + docEsc(l.usuario_rol) + ')' : ''}
                    ${l.detalle ? ' — ' + docEsc(l.detalle) : ''}
                </span>
            </li>`;
        });
        html += '</ul>';
    }

    document.getElementById('hsBody').innerHTML = html;
}
