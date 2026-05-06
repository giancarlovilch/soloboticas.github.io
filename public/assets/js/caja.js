/**
 * Módulo de Caja — Solo Boticas
 */

// ── Utilidades ────────────────────────────────────────
const $ = (id) => document.getElementById(id);
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

    if (!cajaId || !turnoId) { showAlert(msg, 'Selecciona local, caja y turno.'); return; }

    btn.disabled    = true;
    btn.textContent = 'Creando...';

    const payload = { caja_id: parseInt(cajaId), turno_id: parseInt(turnoId) };
    if (vendedorId) payload.vendedor_id = parseInt(vendedorId);

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

    div.innerHTML = `
        <select class="caja-input caja-gasto__tipo" onchange="tipoChanged(this)">
            <option value="PERSONAL">Pago personal</option>
            <option value="LOCAL">Gasto local</option>
            <option value="OTRO">Otro</option>
        </select>
        <div class="caja-gasto__ref">
            ${buildStaffSelect()}
        </div>
        <div class="caja-input-money caja-gasto__monto">
            <span>S/</span>
            <input type="number" class="caja-input caja-input--money" min="0" step="0.01" placeholder="0.00" oninput="recalcularGastos()">
        </div>
        <input type="text" class="caja-input caja-gasto__comp" placeholder="N° comprobante">
        <button type="button" class="caja-gasto__remove" onclick="this.closest('.caja-gasto-row').remove(); recalcularGastos()">✕</button>
    `;

    container.appendChild(div);
}

function buildStaffSelect() {
    const opts = (STAFF || []).map(s =>
        `<option value="${s.id}">${s.nombre_completo}</option>`
    ).join('');
    return `<select class="caja-input caja-gasto__staff"><option value="">— Personal —</option>${opts}</select>`;
}

function buildConceptoSelect() {
    const opts = (CONCEPTOS || []).map(c =>
        `<option value="${c.id}">${c.descripcion}</option>`
    ).join('');
    return `<select class="caja-input caja-gasto__concepto"><option value="">— Concepto —</option>${opts}</select>`;
}

function tipoChanged(select) {
    const row  = select.closest('.caja-gasto-row');
    const ref  = row.querySelector('.caja-gasto__ref');
    const tipo = select.value;

    if (tipo === 'PERSONAL') ref.innerHTML = buildStaffSelect();
    else if (tipo === 'LOCAL') ref.innerHTML = buildConceptoSelect();
    else ref.innerHTML = `<input type="text" class="caja-input caja-gasto__desc" placeholder="Descripción del gasto">`;
}

// ── Recolectar gastos ─────────────────────────────────
function collectGastos() {
    const gastos = [];
    document.querySelectorAll('.caja-gasto-row').forEach(row => {
        const tipo  = row.querySelector('.caja-gasto__tipo')?.value;
        const monto = parseFloat(row.querySelector('input[type="number"]')?.value) || 0;
        const comp  = row.querySelector('.caja-gasto__comp')?.value?.trim() || null;
        if (monto <= 0) return;

        const gasto = { tipo, monto, comprobante: comp };

        if (tipo === 'PERSONAL') {
            const sel = row.querySelector('.caja-gasto__staff');
            if (!sel?.value) return;
            gasto.ref_id = parseInt(sel.value);
        } else if (tipo === 'LOCAL') {
            const sel = row.querySelector('.caja-gasto__concepto');
            if (!sel?.value) return;
            gasto.ref_id = parseInt(sel.value);
        } else {
            const inp = row.querySelector('.caja-gasto__desc');
            gasto.descripcion = inp?.value?.trim() || 'Gasto';
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
            num_operaciones_bcp: parseInt($('act_num_ops_bcp')?.value || '0') || 0,
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
const MODO_LABELS = { 2:'Yape', 3:'Plin', 4:'Visa/POS', 5:'BCP', 6:'Transferencia' };

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
        const canDelete = p.estado === 'PENDIENTE';
        return `<tr>
            <td><strong>${p.modo_desc}</strong></td>
            <td>${p.numero_operacion || '—'}</td>
            <td class="text-right">S/ ${parseFloat(p.monto).toFixed(2)}</td>
            <td class="text-center"><span class="badge ${badgeCls}" style="font-size:0.68rem;">${p.estado}</span></td>
            <td class="text-center">
                ${canDelete
                    ? `<button class="caja-gasto__remove" onclick="eliminarPagoDigital(${p.id_movimiento})">✕</button>`
                    : '—'}
            </td>
        </tr>`;
    }).join('');

    $('totalDigital').textContent = `S/ ${total.toFixed(2)}`;
}

async function eliminarPagoDigital(id) {
    const msg = $('digitalesMsg');
    try {
        const r   = await fetch(`${BASE}/caja/api/pago-digital/${id}/eliminar`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
        });
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

// ── Init ──────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    recalcular();
    recalcularGastos();
    if (ES_EDICION && SESION_ID) cargarPagosDigitales();
});
