/**
 * Módulo de Caja — Solo Boticas
 */

// ── Utilidades ────────────────────────────────────────
const $ = (id) => document.getElementById(id);

// ── Toast central ─────────────────────────────────────
let _cjToastTimer = null;
function mostrarToastCaja(txt, tipo = 'error') {
    let toast = document.getElementById('cjToast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'cjToast';
        document.body.appendChild(toast);
    }
    toast.textContent = txt;
    toast.className   = `cj-toast cj-toast--${tipo}`;
    void toast.offsetWidth;
    toast.classList.add('cj-toast--visible');
    clearTimeout(_cjToastTimer);
    _cjToastTimer = setTimeout(() => {
        toast.classList.remove('cj-toast--visible');
    }, 2500);
}
const fmtS = (n) => 'S/ ' + (isNaN(n) ? '0.00' : Math.abs(n).toFixed(2));
const parseS = (id) => parseFloat($( id)?.value) || 0;

function showAlert(el, txt, type = 'error') {
    if (!el) return;
    el.textContent = txt;
    el.className   = `caja-alert caja-alert--${type}`;
    el.hidden      = false;
}
function hideAlert(el) { if (el) el.hidden = true; }

// ── Recalcular totales (LO QUE ES = físico + BCP agente) ─
function recalcular() {
    const ext = parseS('act_caja_exterior');
    const mon = parseS('act_monedas');
    const bil = parseS('act_billetes');
    const fut = parseS('act_caja_fuerte');
    const bcp = parseS('act_agente_bcp');

    const loQueEs = ext + mon + bil + fut + bcp;

    const elTot = $('totalContado');
    if (elTot) elTot.textContent = fmtS(loQueEs);
}

function recalcularGastos() {
    let total = 0;
    document.querySelectorAll('.caja-gasto-row').forEach(row => {
        const monInput = row.querySelector('input[type="number"]');
        total += parseFloat(monInput?.value) || 0;
    });
    const el = $('totalGastos');
    if (el) el.textContent = fmtS(total);
}

// ── Cargar cajas por local ────────────────────────────
async function cargarCajas(localId) {
    const sel = $('cajaId');
    if (!localId || !sel) return;
    sel.innerHTML = '<option value="">Cargando...</option>';

    try {
        const r   = await fetch(`${BASE}/caja/api/cajas/${localId}`);
        const res = await r.json();
        sel.innerHTML = '<option value="">— Selecciona caja —</option>';
        (res.data || []).forEach(c => {
            const o = document.createElement('option');
            o.value = c.id; o.textContent = c.descripcion;
            sel.appendChild(o);
        });
        sel.onchange = () => cargarSaldoBase(sel.value);
    } catch {
        sel.innerHTML = '<option>Error al cargar</option>';
    }
}

async function cargarSaldoBase(cajaId) {
    if (!cajaId) return;
    try {
        const r   = await fetch(`${BASE}/caja/api/saldo/${cajaId}`);
        const res = await r.json();
        const baseInfo = $('baseInfo');
        const baseAmt  = $('baseAmount');
        if (baseInfo) baseInfo.hidden = false;
        if (baseAmt)  baseAmt.textContent = fmtS(res.data?.saldo_base ?? 0);
    } catch {}
}

// ── Crear nueva sesión ────────────────────────────────
async function crearSesion() {
    const cajaId     = $('cajaId')?.value;
    const turnoId    = $('turnoId')?.value;
    const vendedorId = $('vendedorId')?.value || null;
    const btn        = $('btnCrear');
    const msg        = $('sesionMsg');

    if (!cajaId || !turnoId || !vendedorId) { showAlert(msg, 'Selecciona local, caja, turno y vendedor/a.'); return; }

    btn.disabled    = true;
    btn.textContent = 'Creando...';

    const payload = { caja_id: parseInt(cajaId), turno_id: parseInt(turnoId), vendedor_id: parseInt(vendedorId) };

    try {
        const r   = await fetch(`${BASE}/caja/api/sesion/crear`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify(payload),
        });
        const res = await r.json();
        if (res.success) {
            window.location.href = `${BASE}/caja/sesion/${res.data.id_sesion}`;
        } else {
            showAlert(msg, res.message || 'Error al crear sesión.');
            btn.disabled    = false;
            btn.textContent = 'Abrir sesión →';
        }
    } catch {
        showAlert(msg, 'Error de conexión.');
        btn.disabled    = false;
        btn.textContent = 'Abrir sesión →';
    }
}

// ── Eliminar gasto guardado (borra display + hidden batch row) ──
function eliminarGastoGuardado(idx, btn) {
    btn.closest('tr').remove();
    const hidden = document.querySelector(`#gastosContainer .caja-gasto-row[data-saved-idx="${idx}"]`);
    if (hidden) hidden.remove();
    recalcularGastos();
    const tbody = document.getElementById('gastosDisplayBody');
    const table = document.getElementById('gastosDisplayTable');
    if (table && tbody && tbody.rows.length === 0) table.style.display = 'none';
}

// ── Foco al campo monto del formulario ───────────────
function agregarGasto() {
    gastoTipoChanged();
    document.getElementById('gastoMontoNew').value = '';
    document.getElementById('gastoNewMsg').style.display = 'none';
    document.getElementById('gastoMontoNew').focus();
}

// ── Actualizar campos dinámicos del form ─────────────
function gastoTipoChanged() {
    const sel    = document.getElementById('gastoTipoNew');
    const modo   = sel.options[sel.selectedIndex]?.dataset?.modo || '';
    const middle = document.getElementById('gastoMiddleNew');
    middle.innerHTML = buildMiddle(modo);
}

// ── Confirmar y agregar como solo lectura ─────────────
function confirmarGasto() {
    const sel    = document.getElementById('gastoTipoNew');
    const monto  = parseFloat(document.getElementById('gastoMontoNew').value) || 0;
    const msg    = document.getElementById('gastoNewMsg');
    const tipoId = parseInt(sel.value) || 0;
    const modo   = sel.options[sel.selectedIndex]?.dataset?.modo || '';
    const etiq   = sel.options[sel.selectedIndex]?.text || '';

    if (monto <= 0) { msg.textContent = 'Ingresa un monto válido.'; msg.style.display='block'; return; }

    // Construir fila display
    let detalle = '';
    const middle = document.getElementById('gastoMiddleNew');

    const staffSel   = middle.querySelector('.caja-gasto__staff');
    const tipoPagoSel= middle.querySelector('.caja-gasto__tipopago');
    const localSel   = middle.querySelector('.caja-gasto__local');
    const conceptoSel= middle.querySelector('.caja-gasto__concepto');
    const compInp    = middle.querySelector('.caja-gasto__comp');
    const descInp    = middle.querySelector('.caja-gasto__desc');
    const tipoDocSel = middle.querySelector('.caja-gasto__tipodoc');

    if (modo === 'PERSONAL') {
        if (!staffSel?.value) { msg.textContent = 'Selecciona el personal.'; msg.style.display='block'; return; }
        detalle = staffSel.options[staffSel.selectedIndex]?.text + ' · ' + (tipoPagoSel?.options[tipoPagoSel.selectedIndex]?.text || '');
    } else if (modo === 'LOCAL') {
        if (!localSel?.value) { msg.textContent = 'Selecciona el local.'; msg.style.display='block'; return; }
        detalle = localSel.options[localSel.selectedIndex]?.text;
        if (conceptoSel?.value) detalle += ' / ' + conceptoSel.options[conceptoSel.selectedIndex]?.text;
    } else if (modo === 'FACTURA') {
        detalle = (tipoDocSel?.options[tipoDocSel.selectedIndex]?.text || '') + (compInp?.value ? ' · ' + compInp.value : '');
    } else if (modo === 'DEPOSITO') {
        detalle = compInp?.value || '—';
    } else {
        detalle = descInp?.value || '—';
    }

    // Agregar fila visual de solo lectura
    const tbody = document.getElementById('gastosDisplayBody');
    const table = document.getElementById('gastosDisplayTable');
    const newIdx = 'new_' + Date.now();
    const tr = document.createElement('tr');
    tr.dataset.gastoIdx = newIdx;
    tr.innerHTML = `
        <td><span class="caja-gasto-badge caja-gasto-badge--otro">${etiq}</span></td>
        <td style="font-size:.83rem;color:#475569;">${detalle}</td>
        <td class="text-right" style="font-weight:700;">S/ ${monto.toFixed(2)}</td>
        <td class="text-center">
            <button type="button" style="background:none;border:none;cursor:pointer;color:#94a3b8;font-size:.9rem;padding:.1rem .3rem;border-radius:4px;"
                    onmouseover="this.style.color='#dc2626'" onmouseout="this.style.color='#94a3b8'"
                    onclick="eliminarGastoNuevo('${newIdx}', this)">✕</button>
        </td>`;
    tbody.appendChild(tr);
    if (table) table.style.display = '';

    // Agregar fila oculta para collectGastos()
    const hiddenRow = document.createElement('div');
    hiddenRow.className = 'caja-gasto-row';
    hiddenRow.dataset.newIdx = newIdx;
    hiddenRow.innerHTML = `
        <select class="caja-input caja-gasto__tipo" onchange="tipoChanged(this)">
            ${sel.innerHTML}
        </select>
        <div class="caja-gasto__middle">${middle.innerHTML}</div>
        <div class="caja-input-money caja-gasto__monto">
            <span>S/</span>
            <input type="number" class="caja-input caja-input--money" value="${monto}" min="0" step="0.01">
        </div>
        <button type="button" class="caja-gasto__remove">✕</button>`;
    // Sync tipo seleccionado
    hiddenRow.querySelector('.caja-gasto__tipo').value = sel.value;
    // innerHTML no preserva .value de selects/inputs modificados por el usuario → sync manual
    const hm = hiddenRow.querySelector('.caja-gasto__middle');
    if (staffSel)    { const el = hm.querySelector('.caja-gasto__staff');    if (el) el.value = staffSel.value; }
    if (tipoPagoSel) { const el = hm.querySelector('.caja-gasto__tipopago'); if (el) el.value = tipoPagoSel.value; }
    if (localSel)    { const el = hm.querySelector('.caja-gasto__local');    if (el) el.value = localSel.value; }
    if (conceptoSel) { const el = hm.querySelector('.caja-gasto__concepto'); if (el) el.value = conceptoSel.value; }
    if (tipoDocSel)  { const el = hm.querySelector('.caja-gasto__tipodoc');  if (el) el.value = tipoDocSel.value; }
    if (compInp)     { const el = hm.querySelector('.caja-gasto__comp');     if (el) el.value = compInp.value; }
    if (descInp)     { const el = hm.querySelector('.caja-gasto__desc');     if (el) el.value = descInp.value; }
    document.getElementById('gastosContainer').appendChild(hiddenRow);

    recalcularGastos();

    // Reset form
    document.getElementById('gastoMontoNew').value = '';
    middle.innerHTML = buildMiddle(modo);
    msg.style.display = 'none';
}

function eliminarGastoNuevo(idx, btn) {
    btn.closest('tr').remove();
    const hidden = document.querySelector(`#gastosContainer .caja-gasto-row[data-new-idx="${idx}"]`);
    if (hidden) hidden.remove();
    recalcularGastos();
    const tbody = document.getElementById('gastosDisplayBody');
    const table = document.getElementById('gastosDisplayTable');
    if (table && tbody && tbody.rows.length === 0) table.style.display = 'none';
}


function buildStaffSelect() {
    const opts = (STAFF || []).map(s => `<option value="${s.id}">${s.nombre_completo}</option>`).join('');
    return `<select class="caja-input caja-gasto__staff" style="flex:1"><option value="">— Personal —</option>${opts}</select>`;
}

function buildTipoPagoSelect() {
    return `<select class="caja-input caja-gasto__tipopago" style="max-width:165px">
        <option value="MES_ACTUAL">Pago Mes Actual</option>
        <option value="MES_PASADO">Pago Mes Pasado</option>
        <option value="PAGO_EXTRA">Pago Extra</option>
    </select>`;
}


function buildLocalSelect() {
    const opts = (LOCALES || []).map(l => `<option value="${l.id}">${l.descripcion}</option>`).join('');
    return `<select class="caja-input caja-gasto__local" style="max-width:120px"><option value="">— Local —</option>${opts}</select>`;
}

function buildConceptoSelect() {
    const opts = (CONCEPTOS || []).map(c => `<option value="${c.id}">${c.descripcion}</option>`).join('');
    return `<select class="caja-input caja-gasto__concepto" style="flex:1"><option value="">— Concepto —</option>${opts}</select>`;
}

function buildTipoDocSelect() {
    return `<select class="caja-input caja-gasto__tipodoc" style="max-width:140px">
        <option value="BOLETA">Boleta</option>
        <option value="FACTURA">Factura</option>
        <option value="NOTA_DE_VENTA">Nota de venta</option>
    </select>`;
}

function buildComprobanteInput(ph = 'N° comprobante') {
    return `<input type="text" class="caja-input caja-gasto__comp" style="flex:1" placeholder="${ph}">`;
}

function buildMiddle(modoRef) {
    if (modoRef === 'PERSONAL') return buildStaffSelect() + buildTipoPagoSelect();
    if (modoRef === 'LOCAL')    return buildLocalSelect() + buildConceptoSelect() + buildComprobanteInput();
    if (modoRef === 'FACTURA')  return buildTipoDocSelect() + buildComprobanteInput();
    if (modoRef === 'DEPOSITO') return buildComprobanteInput('N° comprobante');
    if (modoRef === 'LIBRE')    return `<input type="text" class="caja-input caja-gasto__desc" style="flex:1" placeholder="Descripción del pago">`;
    return '';
}

function tipoChanged(select) {
    const row     = select.closest('.caja-gasto-row');
    const middle  = row.querySelector('.caja-gasto__middle');
    const modoRef = select.options[select.selectedIndex]?.dataset?.modo || '';
    if (middle) middle.innerHTML = buildMiddle(modoRef);
}

// ── Recolectar gastos ─────────────────────────────────
function collectGastos() {
    const gastos = [];
    document.querySelectorAll('.caja-gasto-row').forEach(row => {
        const tipoSel = row.querySelector('.caja-gasto__tipo');
        const tipoId  = parseInt(tipoSel?.value) || 0;
        const modoRef = tipoSel?.options[tipoSel.selectedIndex]?.dataset?.modo || '';
        const monto   = parseFloat(row.querySelector('input[type="number"]')?.value) || 0;
        if (monto <= 0 || !tipoId) return;

        const gasto = { tipo_egreso_id: tipoId, modo_ref: modoRef, monto };

        if (modoRef === 'PERSONAL') {
            const sel = row.querySelector('.caja-gasto__staff');
            if (!sel?.value) return;
            gasto.ref_id    = parseInt(sel.value);
            gasto.tipo_pago = row.querySelector('.caja-gasto__tipopago')?.value || 'MES_ACTUAL';
        } else if (modoRef === 'LOCAL') {
            const selLocal    = row.querySelector('.caja-gasto__local');
            const selConcepto = row.querySelector('.caja-gasto__concepto');
            if (!selLocal?.value) return;
            gasto.ref_id      = parseInt(selLocal.value);
            gasto.concepto_id = selConcepto?.value ? parseInt(selConcepto.value) : null;
            gasto.comprobante = row.querySelector('.caja-gasto__comp')?.value?.trim() || null;
        } else if (modoRef === 'FACTURA') {
            gasto.tipo_documento = row.querySelector('.caja-gasto__tipodoc')?.value || 'BOLETA';
            gasto.comprobante    = row.querySelector('.caja-gasto__comp')?.value?.trim() || null;
        } else if (modoRef === 'DEPOSITO') {
            gasto.comprobante = row.querySelector('.caja-gasto__comp')?.value?.trim() || null;
        } else if (modoRef === 'LIBRE') {
            const inp = row.querySelector('.caja-gasto__desc');
            if (!inp?.value?.trim()) return;
            gasto.descripcion = inp.value.trim();
        }

        gastos.push(gasto);
    });
    return gastos;
}

// ── Guardar sesión ────────────────────────────────────
async function guardarSesion(cerrar = false) {
    const sesionId = SESION_ID;
    const msg      = $('sesionMsg');
    const btn      = cerrar ? $('btnCerrar') : $('btnGuardar');

    if (!sesionId) { showAlert(msg, 'No hay sesión activa.'); return; }

    const opsEl = $('act_num_ops_bcp');
    if (opsEl && opsEl.value === '') {
        mostrarToastCaja('Ingresa el N° de operaciones BCP\n(escribe 0 si no hubo ninguna)');
        return;
    }

    btn.disabled    = true;
    btn.textContent = 'Guardando...';
    hideAlert(msg);

    const payload = {
        sesion_id: sesionId,
        cerrar:    cerrar,
        activos: {
            caja_exterior:       parseS('act_caja_exterior'),
            monedas:             parseS('act_monedas'),
            billetes:            parseS('act_billetes'),
            caja_fuerte:         parseS('act_caja_fuerte'),
            agente_bcp:          parseS('act_agente_bcp'),
            num_operaciones_bcp: parseInt(opsEl?.value ?? '0'),
        },
        gastos: collectGastos(),
    };

    try {
        const r   = await fetch(`${BASE}/caja/api/sesion/guardar`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify(payload),
        });
        const res = await r.json();

        if (res.success) {
            if (cerrar) {
                window.location.href = `${BASE}/caja`;
            } else {
                showAlert(msg, '✓ ' + res.message, 'ok');
            }
        } else {
            showAlert(msg, res.message || 'Error al guardar.');
        }
    } catch {
        showAlert(msg, 'Error de conexión.');
    } finally {
        btn.disabled    = false;
        btn.textContent = cerrar ? 'Enviar a pendientes →' : 'Guardar borrador';
    }
}

function confirmarCierre() {
    if (confirm('¿Cerrar la sesión y enviarla a pendientes de venta? Ya no podrás editar los datos.')) {
        guardarSesion(true);
    }
}

// ── Pagos digitales ───────────────────────────────────

async function agregarPagoDigital() {
    const sesionId = SESION_ID;
    const modoId   = parseInt($('digitalModo')?.value);
    const monto    = parseFloat($('digitalMonto')?.value);
    const numOp    = $('digitalNumOp')?.value?.trim() || null;
    const msg      = $('digitalesMsg');

    if (!sesionId) { showAlert(msg, 'No hay sesión activa.'); return; }
    if (!modoId || isNaN(monto) || monto <= 0) {
        showAlert(msg, 'Selecciona el modo y un monto válido.'); return;
    }

    try {
        const r   = await fetch(`${BASE}/caja/api/sesion/${sesionId}/pago-digital`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ modo_id: modoId, monto, numero_operacion: numOp }),
        });
        const res = await r.json();
        if (res.success) {
            $('digitalMonto').value = '';
            $('digitalNumOp').value = '';
            hideAlert(msg);
            await cargarPagosDigitales();
        } else {
            showAlert(msg, res.message || 'Error al registrar.');
        }
    } catch {
        showAlert(msg, 'Error de conexión.');
    }
}

async function cargarPagosDigitales() {
    if (!SESION_ID) return;
    try {
        const r   = await fetch(`${BASE}/caja/api/sesion/${SESION_ID}/pagos-digitales`);
        const res = await r.json();
        renderPagosDigitales(res.data || []);
    } catch {}
}

function renderPagosDigitales(pagos) {
    const tbody = $('tbodyDigitales');
    if (!tbody) return;

    if (!pagos.length) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:#94a3b8;padding:1rem;">Sin pagos digitales.</td></tr>';
        $('totalDigital').textContent = 'S/ 0.00';
        return;
    }

    let total = 0;
    tbody.innerHTML = pagos.map(p => {
        total += parseFloat(p.monto);
        const badgeCls = p.estado === 'APROBADO' ? 'badge-contratado'
                       : p.estado === 'RECHAZADO' ? 'badge-rechazado' : 'badge-pendiente';
        const canDelete   = p.estado === 'PENDIENTE';
        const esSoloBank  = p.modo_desc === 'SoloBank';
        const deleteCall  = esSoloBank
            ? `eliminarPagoDigital(${p.id_movimiento}, true)`
            : `eliminarPagoDigital(${p.id_movimiento})`;
        return `<tr>
            <td><strong>${p.modo_desc}</strong></td>
            <td>${p.numero_operacion || '—'}</td>
            <td class="text-right">S/ ${parseFloat(p.monto).toFixed(2)}</td>
            <td class="text-center"><span class="badge ${badgeCls}" style="font-size:0.68rem;">${p.estado}</span></td>
            <td class="text-center">
                ${canDelete
                    ? `<button class="caja-gasto__remove" onclick="${deleteCall}">✕</button>`
                    : '—'}
            </td>
        </tr>`;
    }).join('');

    $('totalDigital').textContent = `S/ ${total.toFixed(2)}`;
}

async function recargarValesSelect() {
    const sel = $('sbSelect');
    if (!sel) return;
    try {
        const r    = await fetch(`${BASE}/api/solobank/vales/disponibles`);
        const res  = await r.json();
        const vales = res.data?.vales ?? [];
        // Conservar solo el placeholder y reconstruir opciones
        sel.innerHTML = '<option value="">— Seleccionar vale —</option>';
        vales.forEach(v => {
            const d     = new Date(v.fecha + 'T00:00:00');
            const label = `${v.caja} · ${String(d.getDate()).padStart(2,'0')}/${String(d.getMonth()+1).padStart(2,'0')} ${v.turno} · S/ ${parseFloat(v.total).toFixed(2)} (${v.conteo} pagos)`;
            const opt   = document.createElement('option');
            opt.value            = v.codigo;
            opt.dataset.monto    = v.total;
            opt.textContent      = label;
            sel.appendChild(opt);
        });
        sel.value = '';
        sbSelectChanged();
    } catch { /* silencioso */ }
}

async function eliminarPagoDigital(id, esSoloBank = false) {
    const msg = $('digitalesMsg');
    const url = esSoloBank
        ? `${BASE}/caja/api/solobank-mov/${id}/quitar`
        : `${BASE}/caja/api/pago-digital/${id}/eliminar`;
    try {
        const r   = await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json' } });
        const res = await r.json();
        if (res.success) {
            hideAlert(msg);
            await cargarPagosDigitales();
            if (esSoloBank) await recargarValesSelect();
        } else {
            showAlert(msg, res.message || 'No se pudo eliminar.');
        }
    } catch {
        showAlert(msg, 'Error de conexión.');
    }
}

// ── SoloBank vales ────────────────────────────────────

function sbSelectChanged() {
    const sel   = $('sbSelect');
    const btn   = $('sbBtn');
    const monto = $('sbMonto');
    if (!sel || !sel.value) {
        if (btn)   btn.disabled = true;
        if (monto) monto.textContent = '';
        return;
    }
    const opt = sel.options[sel.selectedIndex];
    const m   = parseFloat(opt.dataset.monto || 0);
    if (monto) monto.textContent = `S/ ${m.toFixed(2)}`;
    if (btn)   btn.disabled = false;
}

async function agregarVale() {
    const sel    = $('sbSelect');
    const msg    = $('sbMsg');
    const codigo = sel?.value?.trim();
    if (!codigo || !SESION_ID) return;

    try {
        const r   = await fetch(`${BASE}/caja/api/sesion/${SESION_ID}/solobank`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ codigo }),
        });
        const res = await r.json();
        if (res.success) {
            // Quitar vale del select
            sel.remove(sel.selectedIndex);
            sel.value = '';
            sbSelectChanged();
            if (msg) { msg.hidden = false; msg.className = 'caja-alert caja-alert--success'; msg.textContent = 'Vale SoloBank agregado.'; }
            await cargarPagosDigitales();
        } else {
            if (msg) { msg.hidden = false; msg.className = 'caja-alert caja-alert--error'; msg.textContent = res.message || 'Error al agregar vale.'; }
        }
    } catch {
        if (msg) { msg.hidden = false; msg.className = 'caja-alert caja-alert--error'; msg.textContent = 'Error de conexión.'; }
    }
}

// ── Init ──────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    recalcular();
    recalcularGastos();
    if (document.getElementById('gastoTipoNew')) gastoTipoChanged();
    if (ES_EDICION && SESION_ID) cargarPagosDigitales();
});
