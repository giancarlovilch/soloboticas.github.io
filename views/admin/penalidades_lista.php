<?php
if (!isset($_SESSION['user_rol'])) exit('Acceso denegado');
extract($penDatos ?? []);
$penTrabajadores = $penTrabajadores ?? [];
$penMesSel       = $penMesSel       ?? date('Y-m');
$penLista        = $penLista        ?? [];
$basePath        = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
?>
<style>
.pn-table    { width:100%;border-collapse:collapse;font-size:.8rem; }
.pn-table th { background:#f8fafc;padding:.4rem .6rem;font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#64748b;border-bottom:1.5px solid #e2e8f0; }
.pn-table td { padding:.4rem .6rem;border-bottom:1px solid #f1f5f9;vertical-align:middle; }
.pn-table tr:last-child td { border-bottom:none; }
.pn-input    { padding:.4rem .6rem;border:1.5px solid #e2e8f0;border-radius:7px;font-size:.82rem;width:100%;box-sizing:border-box; }
.pn-btn      { border:none;border-radius:6px;padding:.32rem .7rem;font-size:.72rem;font-weight:700;cursor:pointer;margin-right:.3rem; }
.pn-btn--save   { background:#0097A7;color:#fff; }
.pn-btn--delete { background:#fee2e2;color:#991b1b; }
.pn-btn--primary{ background:#0097A7;color:#fff;padding:.55rem 1.2rem;border-radius:8px; }
.pn-empty    { text-align:center;padding:2.5rem;color:#94a3b8; }
.pn-form-grid{ display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:.75rem;align-items:end; }
.pn-form-grid label { display:block;font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#64748b;margin-bottom:.25rem; }
</style>

<div class="postulantes-container">
    <div class="section-header">
        <div class="header-info">
            <p class="section-kicker">Personal</p>
            <h2>Penalidades / Descuentos</h2>
        </div>
    </div>

    <p style="font-size:.8rem;color:#64748b;margin-bottom:1rem;">
        Registro informativo de descuentos aplicados a un trabajador (ej. por desaprobar la encuesta BCP). No descuenta
        ni calcula nada automáticamente — solo queda registrado para que lo tengas presente al pagar. Aparece también
        en <a href="<?= $basePath ?>/admin/dashboard?page=economia">Economía</a> y en <code>/staff/economia</code> del trabajador.
    </p>

    <!-- ── Formulario: registrar nueva penalidad ── -->
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:1rem;margin-bottom:1.25rem;">
        <div class="pn-form-grid">
            <div>
                <label>Trabajador</label>
                <select id="pnTrabajador" class="pn-input">
                    <option value="">— Selecciona —</option>
                    <?php foreach ($penTrabajadores as $t): ?>
                    <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>Tipo</label>
                <input type="text" id="pnTipo" class="pn-input" value="ENCUESTA" placeholder="ENCUESTA">
            </div>
            <div>
                <label>Mes que se aplica</label>
                <input type="month" id="pnMes" class="pn-input" value="<?= htmlspecialchars($penMesSel) ?>">
            </div>
            <div>
                <label>Monto (S/)</label>
                <input type="number" id="pnMonto" class="pn-input" min="0.01" step="0.01" placeholder="0.00">
            </div>
        </div>
        <div style="margin-top:.75rem;">
            <label style="display:block;font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#64748b;margin-bottom:.25rem;">
                Descripción (máx. 50 palabras)
            </label>
            <textarea id="pnDescripcion" class="pn-input" rows="2" oninput="pnActualizarContador()"
                      placeholder="Motivo del descuento..."></textarea>
            <span id="pnContador" style="font-size:.68rem;color:#94a3b8;">0 / 50 palabras</span>
        </div>
        <div id="pnMsg" style="display:none;font-size:.8rem;padding:.5rem .8rem;border-radius:7px;margin-top:.6rem;"></div>
        <button class="pn-btn pn-btn--primary" onclick="pnCrear()" style="margin-top:.75rem;">Registrar penalidad</button>
    </div>

    <!-- ── Filtro de mes ── -->
    <form method="GET" style="display:flex;gap:.6rem;align-items:flex-end;margin-bottom:1rem;">
        <input type="hidden" name="page" value="penalidades">
        <div>
            <label style="display:block;font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#64748b;margin-bottom:.25rem;">Mes</label>
            <input type="month" name="mes" value="<?= htmlspecialchars($penMesSel) ?>" onchange="this.form.submit()" class="pn-input">
        </div>
    </form>

    <?php if (empty($penLista)): ?>
    <div class="pn-empty">No hay penalidades registradas en <?= htmlspecialchars($penMesSel) ?>.</div>
    <?php else: ?>
    <div style="overflow-x:auto;background:#fff;border-radius:10px;border:1px solid #e2e8f0;">
    <table class="pn-table">
        <thead>
            <tr>
                <th>Trabajador</th>
                <th>Tipo</th>
                <th>Estado</th>
                <th>Descripción</th>
                <th class="text-center">Monto</th>
                <th>Registrado por</th>
                <th>Fecha</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($penLista as $p): ?>
        <tr data-id="<?= $p['id_descuento'] ?>">
            <td style="font-weight:600;color:#1e293b;white-space:nowrap;"><?= htmlspecialchars($p['trabajador_nombre']) ?></td>
            <td><span style="background:#faf5ff;color:#7c3aed;font-size:.68rem;font-weight:700;padding:2px 8px;border-radius:20px;"><?= htmlspecialchars($p['tipo']) ?></span></td>
            <td><span style="background:#f1f5f9;color:#475569;font-size:.68rem;font-weight:700;padding:2px 8px;border-radius:20px;"><?= htmlspecialchars($p['estado']) ?></span></td>
            <td style="min-width:220px;"><textarea class="pn-input" rows="2" data-field="descripcion"><?= htmlspecialchars($p['descripcion']) ?></textarea></td>
            <td class="text-center"><input type="number" class="pn-input" style="width:90px;" min="0.01" step="0.01" value="<?= (float)$p['monto'] ?>" data-field="monto"></td>
            <td style="color:#64748b;white-space:nowrap;"><?= htmlspecialchars($p['registrado_por_nombre']) ?></td>
            <td style="color:#94a3b8;font-size:.72rem;white-space:nowrap;"><?= date('d/m/Y', strtotime($p['fecha'])) ?></td>
            <td style="white-space:nowrap;">
                <button class="pn-btn pn-btn--save" onclick="pnGuardar(<?= $p['id_descuento'] ?>, this)">Guardar</button>
                <button class="pn-btn pn-btn--delete" onclick="pnEliminar(<?= $p['id_descuento'] ?>, this)">Eliminar</button>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>

<script>
const PN_BASE = '<?= $basePath ?>';

function pnContarPalabras(txt) {
    const t = txt.trim();
    return t === '' ? 0 : t.split(/\s+/).length;
}
function pnActualizarContador() {
    const n = pnContarPalabras(document.getElementById('pnDescripcion').value);
    const el = document.getElementById('pnContador');
    el.textContent = `${n} / 50 palabras`;
    el.style.color = n > 50 ? '#dc2626' : '#94a3b8';
}

function pnMostrarMsg(txt, ok) {
    const el = document.getElementById('pnMsg');
    el.textContent = txt;
    el.style.background = ok ? '#d1fae5' : '#fee2e2';
    el.style.color      = ok ? '#065f46' : '#991b1b';
    el.style.display = 'block';
}

async function pnCrear() {
    const postulante_id = document.getElementById('pnTrabajador').value;
    const tipo          = document.getElementById('pnTipo').value.trim();
    const mes            = document.getElementById('pnMes').value;
    const monto          = document.getElementById('pnMonto').value;
    const descripcion    = document.getElementById('pnDescripcion').value.trim();

    if (!postulante_id) { pnMostrarMsg('Selecciona un trabajador.', false); return; }
    if (!mes) { pnMostrarMsg('Selecciona el mes.', false); return; }
    if (pnContarPalabras(descripcion) > 50) { pnMostrarMsg('La descripción no puede superar las 50 palabras.', false); return; }
    if (!monto || parseFloat(monto) <= 0) { pnMostrarMsg('Ingresa un monto válido.', false); return; }

    try {
        const r = await fetch(`${PN_BASE}/admin/api/descuento/crear`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ postulante_id, tipo, mes, monto, descripcion }),
        });
        const res = await r.json();
        pnMostrarMsg(res.message || (res.success ? 'Registrado' : 'Error'), res.success);
        if (res.success) setTimeout(() => location.reload(), 700);
    } catch { pnMostrarMsg('Error de conexión', false); }
}

async function pnGuardar(id, btn) {
    const row = btn.closest('tr');
    const descripcion = row.querySelector('[data-field="descripcion"]').value;
    const monto       = row.querySelector('[data-field="monto"]').value;
    btn.disabled = true;
    try {
        const r = await fetch(`${PN_BASE}/admin/api/descuento/${id}/editar`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ descripcion, monto }),
        });
        const res = await r.json();
        pnMostrarMsg(res.message || (res.success ? 'Guardado' : 'Error'), res.success);
    } catch { pnMostrarMsg('Error de conexión', false); }
    btn.disabled = false;
}

async function pnEliminar(id, btn) {
    if (!confirm('¿Eliminar esta penalidad? No se puede deshacer.')) return;
    btn.disabled = true;
    try {
        const r = await fetch(`${PN_BASE}/admin/api/descuento/${id}/eliminar`, { method: 'POST' });
        const res = await r.json();
        if (res.success) btn.closest('tr').remove();
        pnMostrarMsg(res.message || (res.success ? 'Eliminado' : 'Error'), res.success);
    } catch { pnMostrarMsg('Error de conexión', false); }
    btn.disabled = false;
}
</script>
