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

// ── Agregar fila de gasto ─────────────────────────────
function agregarGasto() {
    const container = $('gastosContainer');
    const div = document.createElement('div');
    div.className = 'caja-gasto-row';

    const tipoOpts = (TIPOS_EGRESO || []).map((t, i) =>
        `<option value="${t.id_tipo_egreso}" data-modo="${t.modo_ref}"${i === 0 ? ' selected' : ''}>${t.etiqueta}</option>`
    ).join('');

    const defaultModo = TIPOS_EGRESO[0]?.modo_ref || 'PERSONAL';

    div.innerHTML = `
        <select class="caja-input caja-gasto__tipo" onchange="tipoChanged(this)">
            ${tipoOpts}
        </select>
        <div class="caja-gasto__middle">
            ${buildMiddle(defaultModo)}
        </div>
        <div class="caja-input-money caja-gasto__monto">
            <span>S/</span>
            <input type="number" class="caja-input caja-input--money" min="0" step="0.01" placeholder="0.00" oninput="recalcularGastos()">
        </div>
        <button type="button" class="caja-gasto__remove" onclick="this.closest('.caja-gasto-row').remove(); recalcularGastos()">✕</button>
    `;

    container.appendChild(div);
}

function buildStaffSelect() {
    const opts = (STAFF || []).map(s => `<option value="${s.id}">${s.nombre_completo}</option>`).join('');
    return `<select class="caja-input caja-gasto__staff" style="flex:1"><option value="">— Personal —</option>${opts}</select>`;
}

function buildTipoPagoSelect() {
    return `<select class="caja-input caja-gasto__tipopago" style="max-width:130px">
        <option value="PAGO_TOTAL">Pago total</option>
        <option value="ADELANTO">Adelanto</option>
        <option value="DESCUENTO">Descuento</option>
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
            gasto.tipo_pago = row.querySelector('.caja-gasto__tipopago')?.value || 'PAGO_TOTAL';
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
    if (ES_EDICION && SESION_ID) cargarPagosDigitales();
});
