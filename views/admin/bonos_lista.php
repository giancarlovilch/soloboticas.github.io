<?php
if (!isset($_SESSION['user_rol'])) exit('Acceso denegado');
extract($bonosDatos ?? []);
$tarifas  = $tarifas  ?? [];
$bonosV   = $bonosV   ?? [];
$bonosOps = $bonosOps ?? [];

// Agrupar tarifas por rol (vigencia más reciente primero)
$tarifaActual = [];
foreach ($tarifas as $t) {
    if (!isset($tarifaActual[$t['rol_codigo']])) $tarifaActual[$t['rol_codigo']] = $t;
}

// Agrupar bonos por fecha_vigencia
$agrupar = function(array $rows): array {
    $g = [];
    foreach ($rows as $r) { $g[$r['fecha_vigencia']][] = $r; }
    krsort($g);
    return $g;
};
$gruposV   = $agrupar($bonosV);
$gruposOps = $agrupar($bonosOps);
$hoy = date('Y-m-d');
?>
<style>
.bn-section  { margin-bottom:2rem; }
.bn-title    { font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#0097A7;margin-bottom:.75rem; }
.bn-card     { background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:1rem 1.25rem;margin-bottom:.75rem; }
.bn-vig-hd   { display:flex;justify-content:space-between;align-items:center;margin-bottom:.5rem; }
.bn-vig-tag  { font-size:.7rem;font-weight:700;padding:2px 9px;border-radius:20px; }
.bn-vig-act  { background:#d1fae5;color:#065f46; }
.bn-vig-past { background:#f1f5f9;color:#64748b; }
.bn-table    { width:100%;border-collapse:collapse;font-size:.8rem; }
.bn-table th { background:#f8fafc;padding:.35rem .6rem;font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#64748b;border-bottom:1.5px solid #e2e8f0; }
.bn-table td { padding:.4rem .6rem;border-bottom:1px solid #f1f5f9;vertical-align:middle; }
.bn-table tr:last-child td { border-bottom:none; }
.bn-add-form { display:flex;gap:.5rem;flex-wrap:wrap;align-items:flex-end;margin-top:.75rem;padding-top:.75rem;border-top:1px dashed #e2e8f0; }
.bn-add-form label { font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#64748b;display:block;margin-bottom:2px; }
.bn-add-form input, .bn-add-form select { padding:.38rem .65rem;border:1.5px solid #e2e8f0;border-radius:7px;font-size:.8rem;outline:none;width:100%; }
.bn-add-form input:focus, .bn-add-form select:focus { border-color:#0097A7; }
.bn-rol-grid { display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem;margin-bottom:1.25rem; }
.bn-rol-card { background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:.85rem 1rem;text-align:center; }
.bn-rol-card__num   { font-size:1.3rem;font-weight:800;color:#0097A7; }
.bn-rol-card__label { font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#64748b;margin-top:2px; }
.bn-rol-card__sub   { font-size:.68rem;color:#94a3b8;margin-top:2px; }
.bn-msg { font-size:.78rem;padding:.4rem .75rem;border-radius:7px;margin-top:.5rem;display:none; }
.bn-msg--ok  { background:#d1fae5;color:#065f46; }
.bn-msg--err { background:#fee2e2;color:#991b1b; }
</style>

<div class="postulantes-container">
    <div class="section-header">
        <div class="header-info">
            <p class="section-kicker">Economía</p>
            <h2>Tarifas y Bonos</h2>
        </div>
    </div>

    <!-- ── Tarifas base ─────────────────────────────── -->
    <div class="bn-section">
        <p class="bn-title">Tarifa base por turno</p>

        <div class="bn-rol-grid">
            <?php foreach (['CAJERA','VENDEDORA','ALMACENERA'] as $rol):
                $t = $tarifaActual[$rol] ?? null;
            ?>
            <div class="bn-rol-card">
                <div class="bn-rol-card__num">S/ <?= $t ? number_format($t['monto'],2,'.','') : '—' ?></div>
                <div class="bn-rol-card__label"><?= $rol ?></div>
                <div class="bn-rol-card__sub">Desde <?= $t ? date('d/m/Y', strtotime($t['fecha_vigencia'])) : '—' ?></div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="bn-card">
            <p style="font-size:.75rem;font-weight:600;color:#475569;margin-bottom:.5rem;">Agregar nueva tarifa</p>
            <div class="bn-add-form" id="fmTarifa">
                <div style="min-width:140px;flex:1;">
                    <label>Rol</label>
                    <select id="tfRol">
                        <option value="CAJERA">Cajera</option>
                        <option value="VENDEDORA">Vendedora</option>
                        <option value="ALMACENERA">Almacenera</option>
                    </select>
                </div>
                <div style="min-width:110px;flex:1;">
                    <label>Monto (S/)</label>
                    <input type="number" id="tfMonto" min="1" step="0.50" placeholder="30.00">
                </div>
                <div style="min-width:140px;flex:1;">
                    <label>Vigente desde</label>
                    <input type="date" id="tfFecha" value="<?= $hoy ?>">
                </div>
                <div style="display:flex;align-items:flex-end;">
                    <button class="asist-btn asist-btn--primary" onclick="addTarifa()" style="white-space:nowrap;">+ Agregar</button>
                </div>
            </div>
            <div id="msgTarifa" class="bn-msg"></div>
        </div>

        <div class="bn-card" style="padding:.75rem 1rem;">
            <p style="font-size:.72rem;font-weight:700;color:#475569;margin-bottom:.5rem;">Historial de cambios</p>
            <table class="bn-table">
                <thead><tr><th>Rol</th><th>Monto</th><th>Vigente desde</th><th>Registrado</th><th></th></tr></thead>
                <tbody id="tbTarifas">
                <?php foreach ($tarifas as $t): ?>
                <tr id="trf-<?= $t['id'] ?>">
                    <td style="font-weight:600;"><?= htmlspecialchars($t['rol_codigo']) ?></td>
                    <td style="font-weight:700;color:#0097A7;">S/ <?= number_format($t['monto'],2,'.','') ?></td>
                    <td><?= date('d/m/Y', strtotime($t['fecha_vigencia'])) ?></td>
                    <td style="font-size:.72rem;color:#94a3b8;"><?= date('d/m/Y H:i', strtotime($t['creado_en'])) ?></td>
                    <td><button onclick="delTarifa(<?= $t['id'] ?>)" style="background:none;border:none;color:#dc2626;cursor:pointer;font-size:.75rem;">✕</button></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ── Bonos por ventas ─────────────────────────── -->
    <div class="bn-section">
        <p class="bn-title">Rangos de bono — Ventas</p>
        <?php foreach ($gruposV as $vig => $rows): ?>
        <div class="bn-card">
            <div class="bn-vig-hd">
                <span style="font-size:.78rem;font-weight:700;color:#1e293b;">Vigente desde <?= date('d/m/Y', strtotime($vig)) ?></span>
                <span class="bn-vig-tag <?= $vig <= $hoy ? 'bn-vig-act' : 'bn-vig-past' ?>">
                    <?= $vig <= $hoy ? 'Activa' : 'Futura' ?>
                </span>
            </div>
            <table class="bn-table">
                <thead><tr><th>Desde</th><th>Hasta</th><th>Bono</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($rows as $b): ?>
                <tr id="bono-<?= $b['id'] ?>">
                    <td>S/ <?= number_format($b['desde'],2,'.','') ?></td>
                    <td><?= $b['hasta'] !== null ? 'S/ '.number_format($b['hasta'],2,'.','') : '<span style="color:#94a3b8;">Sin techo</span>' ?></td>
                    <td style="font-weight:700;color:#059669;">S/ <?= number_format($b['monto_bono'],2,'.','') ?></td>
                    <td><button onclick="delBono(<?= $b['id'] ?>)" style="background:none;border:none;color:#dc2626;cursor:pointer;font-size:.75rem;">✕</button></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endforeach; ?>
        <?php if (empty($gruposV)): ?>
        <p style="font-size:.8rem;color:#94a3b8;margin-bottom:.75rem;">Sin rangos configurados aún.</p>
        <?php endif; ?>
        <?php echo renderFormBono('VENTAS'); ?>
        <div id="msgBonoV" class="bn-msg"></div>
    </div>

    <!-- ── Bonos por operaciones BCP ────────────────── -->
    <div class="bn-section">
        <p class="bn-title">Rangos de bono — Operaciones BCP</p>
        <?php foreach ($gruposOps as $vig => $rows): ?>
        <div class="bn-card">
            <div class="bn-vig-hd">
                <span style="font-size:.78rem;font-weight:700;color:#1e293b;">Vigente desde <?= date('d/m/Y', strtotime($vig)) ?></span>
                <span class="bn-vig-tag <?= $vig <= $hoy ? 'bn-vig-act' : 'bn-vig-past' ?>">
                    <?= $vig <= $hoy ? 'Activa' : 'Futura' ?>
                </span>
            </div>
            <table class="bn-table">
                <thead><tr><th>Desde (ops)</th><th>Hasta (ops)</th><th>Bono</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($rows as $b): ?>
                <tr id="bono-<?= $b['id'] ?>">
                    <td><?= number_format($b['desde'],0) ?> ops</td>
                    <td><?= $b['hasta'] !== null ? number_format($b['hasta'],0).' ops' : '<span style="color:#94a3b8;">Sin techo</span>' ?></td>
                    <td style="font-weight:700;color:#059669;">S/ <?= number_format($b['monto_bono'],2,'.','') ?></td>
                    <td><button onclick="delBono(<?= $b['id'] ?>)" style="background:none;border:none;color:#dc2626;cursor:pointer;font-size:.75rem;">✕</button></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endforeach; ?>
        <?php if (empty($gruposOps)): ?>
        <p style="font-size:.8rem;color:#94a3b8;margin-bottom:.75rem;">Sin rangos configurados aún.</p>
        <?php endif; ?>
        <?php echo renderFormBono('OPERACIONES_BCP'); ?>
        <div id="msgBonoOps" class="bn-msg"></div>
    </div>
</div>

<?php
function renderFormBono(string $tipo): string {
    $idSuf = $tipo === 'VENTAS' ? 'V' : 'Ops';
    $lbl   = $tipo === 'VENTAS' ? 'ventas (S/)' : 'operaciones';
    return <<<HTML
    <div class="bn-card">
        <p style="font-size:.75rem;font-weight:600;color:#475569;margin-bottom:.5rem;">Agregar rango de bono</p>
        <div class="bn-add-form">
            <div style="min-width:110px;flex:1;">
                <label>Desde ({$lbl})</label>
                <input type="number" id="bf{$idSuf}Desde" min="0" step="1" placeholder="0">
            </div>
            <div style="min-width:110px;flex:1;">
                <label>Hasta (vacío=sin techo)</label>
                <input type="number" id="bf{$idSuf}Hasta" min="0" step="1" placeholder="Sin techo">
            </div>
            <div style="min-width:100px;flex:1;">
                <label>Bono (S/)</label>
                <input type="number" id="bf{$idSuf}Bono" min="0" step="0.50" placeholder="0.00">
            </div>
            <div style="min-width:140px;flex:1;">
                <label>Vigente desde</label>
                <input type="date" id="bf{$idSuf}Fecha" value="<?= date('Y-m-d') ?>">
            </div>
            <div style="display:flex;align-items:flex-end;">
                <button class="asist-btn asist-btn--primary" onclick="addBono('{$tipo}','{$idSuf}')" style="white-space:nowrap;">+ Agregar</button>
            </div>
        </div>
    </div>
HTML;
}
?>

<script>
const BASE_ADM = window.location.pathname.split('/admin/')[0];
const apiUrl   = (p) => `${window.location.origin}${BASE_ADM}${p}`;

function showMsg(id, msg, ok) {
    const el = document.getElementById(id);
    el.textContent = msg;
    el.className = 'bn-msg ' + (ok ? 'bn-msg--ok' : 'bn-msg--err');
    el.style.display = 'block';
    setTimeout(() => el.style.display = 'none', 3500);
}

async function addTarifa() {
    const data = {
        rol_codigo:     document.getElementById('tfRol').value,
        monto:          document.getElementById('tfMonto').value,
        fecha_vigencia: document.getElementById('tfFecha').value,
    };
    const r   = await fetch(apiUrl('/admin/api/tarifa-base/agregar'), {
        method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(data)
    });
    const res = await r.json();
    if (res.success) { showMsg('msgTarifa', res.message, true); setTimeout(() => location.reload(), 1000); }
    else showMsg('msgTarifa', res.message || 'Error.', false);
}

async function delTarifa(id) {
    if (!confirm('¿Eliminar esta tarifa?')) return;
    const r   = await fetch(apiUrl(`/admin/api/tarifa-base/${id}/eliminar`), {
        method:'POST', headers:{'Content-Type':'application/json'}
    });
    const res = await r.json();
    if (res.success) { document.getElementById(`trf-${id}`)?.remove(); showMsg('msgTarifa', res.message, true); }
    else showMsg('msgTarifa', res.message || 'Error.', false);
}

async function addBono(tipo, suf) {
    const hastaVal = document.getElementById(`bf${suf}Hasta`).value;
    const data = {
        tipo,
        desde:          document.getElementById(`bf${suf}Desde`).value,
        hasta:          hastaVal !== '' ? hastaVal : null,
        monto_bono:     document.getElementById(`bf${suf}Bono`).value,
        fecha_vigencia: document.getElementById(`bf${suf}Fecha`).value,
    };
    const r   = await fetch(apiUrl('/admin/api/bono/agregar'), {
        method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(data)
    });
    const res = await r.json();
    const msgId = tipo === 'VENTAS' ? 'msgBonoV' : 'msgBonoOps';
    if (res.success) { showMsg(msgId, res.message, true); setTimeout(() => location.reload(), 1000); }
    else showMsg(msgId, res.message || 'Error.', false);
}

async function delBono(id) {
    if (!confirm('¿Eliminar este rango?')) return;
    const r   = await fetch(apiUrl(`/admin/api/bono/${id}/eliminar`), {
        method:'POST', headers:{'Content-Type':'application/json'}
    });
    const res = await r.json();
    if (res.success) { document.getElementById(`bono-${id}`)?.remove(); }
    else alert(res.message || 'Error.');
}
</script>
