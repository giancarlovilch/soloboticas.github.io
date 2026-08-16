<?php
/** @var array $lotes */ /** @var array $mapeo */ /** @var array $terminalesSinMap */ /** @var array $cajas */
$basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
$userName = $userName ?? $_SESSION['user_name'] ?? 'Usuario';
$userRol  = $userRol  ?? $_SESSION['user_rol']  ?? 'STAFF';

$filtroDesde    = $_GET['desde']    ?? date('Y-m-d', strtotime('-30 days'));
$filtroHasta    = $_GET['hasta']    ?? date('Y-m-d');
$filtroCaja     = isset($_GET['caja']) ? (int)$_GET['caja'] : 0;
$filtroTerminal = $_GET['terminal'] ?? '';
$filtroEstado   = $_GET['estado']   ?? '';

$mapeo            = $mapeo ?? [];
$terminalesSinMap = $terminalesSinMap ?? [];
$cajas            = $cajas ?? [];
$lotes            = $lotes ?? [];

$terminalesFiltro = array_unique(array_merge(array_column($mapeo, 'terminal_id'), $terminalesSinMap));
sort($terminalesFiltro);

$sugeridoLbl = [1 => 'Mañana', 2 => 'Tarde'];
$totLotes    = count($lotes);
$totAsignado = count(array_filter($lotes, fn($l) => $l['estado'] === 'ASIGNADO'));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conciliación de tarjetas | Caja SB</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/dashboard.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/caja.css">
</head>
<body style="background:#f1f5f9;">

<header class="caja-header">
    <div class="caja-header__brand">
        <div class="caja-header__logo">SB</div>
        <div>
            <p class="caja-header__company">Grupo KGyR S.A.C</p>
            <p class="caja-header__app"><strong>Conciliación</strong> — Cobros por POS (tarjetas)</p>
        </div>
    </div>
    <div class="caja-header__right">
        <span class="caja-header__user"><?= htmlspecialchars($userName) ?> (<?= $userRol ?>)</span>
        <a href="<?= $basePath ?>/caja/auditoria" class="caja-btn-back">← Auditoría</a>
        <a href="<?= $basePath ?>/admin/dashboard" class="caja-btn-back">Dashboard</a>
    </div>
</header>

<main class="caja-main" style="max-width:1200px;">

    <!-- ── Subir archivo ─────────────────────────────────────── -->
    <section class="caja-card">
        <h2 class="caja-card__title">Importar archivo del proveedor</h2>
        <p style="font-size:.8rem;color:#64748b;margin-bottom:.75rem;">
            Sube el <code>.txt</code> exportado (formato con columnas separadas por <code>|</code>). Se puede re-subir el mismo
            archivo o uno más reciente cuantas veces sea necesario: las transacciones ya existentes se actualizan
            (por ejemplo cuando pasan de "aprobada" a "abonada"), nunca se duplican.
        </p>
        <form id="uploadForm" style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;">
            <input type="file" id="archivoInput" accept=".txt" class="caja-input" style="max-width:340px;">
            <button type="submit" class="caja-btn caja-btn--primary">Subir e importar</button>
        </form>
        <div id="importMsg" style="margin-top:.6rem;"></div>
    </section>

    <!-- ── Mapeo terminal → caja ─────────────────────────────── -->
    <section class="caja-card">
        <h2 class="caja-card__title">Mapeo de terminales</h2>
        <p style="font-size:.8rem;color:#64748b;margin-bottom:.75rem;">
            Cada terminal físico (identificado por su código) debe asociarse a la caja donde está instalado, para
            poder resolver a qué caja pertenece cada transacción importada.
        </p>
        <table class="caja-table">
            <thead><tr><th>Terminal</th><th>Caja asignada</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($mapeo as $m): ?>
                <tr>
                    <td style="font-family:monospace;font-weight:700;"><?= htmlspecialchars($m['terminal_id']) ?></td>
                    <td>
                        <select class="caja-input" id="map_<?= htmlspecialchars($m['terminal_id']) ?>" style="max-width:220px;">
                            <?php foreach ($cajas as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= (int)$m['caja_id'] === (int)$c['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['descripcion']) ?> (<?= htmlspecialchars($c['local_desc']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <button class="btn-edit" style="font-size:.72rem;" onclick="guardarMapeo('<?= htmlspecialchars($m['terminal_id']) ?>')">Guardar</button>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php foreach ($terminalesSinMap as $tid): ?>
                <tr style="background:#fffbeb;">
                    <td style="font-family:monospace;font-weight:700;">
                        <?= htmlspecialchars($tid) ?>
                        <span style="font-size:.68rem;color:#d97706;display:block;">sin mapear</span>
                    </td>
                    <td>
                        <select class="caja-input" id="map_<?= htmlspecialchars($tid) ?>" style="max-width:220px;">
                            <option value="">— seleccionar caja —</option>
                            <?php foreach ($cajas as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['descripcion']) ?> (<?= htmlspecialchars($c['local_desc']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <button class="btn-edit" style="font-size:.72rem;" onclick="guardarMapeo('<?= htmlspecialchars($tid) ?>')">Guardar</button>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($mapeo) && empty($terminalesSinMap)): ?>
                <tr><td colspan="3" class="caja-table__empty">Aún no se ha importado ningún archivo.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </section>

    <!-- ── Resumen ────────────────────────────────────────────── -->
    <div style="display:flex;gap:1rem;flex-wrap:wrap;">
        <div class="caja-card" style="flex:1;min-width:180px;text-align:center;">
            <p style="font-size:0.68rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.4rem;">Lotes en el rango</p>
            <p style="font-size:1.6rem;font-weight:700;color:#1e293b;"><?= $totLotes ?></p>
        </div>
        <div class="caja-card" style="flex:1;min-width:180px;text-align:center;">
            <p style="font-size:0.68rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.4rem;">Asignados a un cuadre</p>
            <p style="font-size:1.6rem;font-weight:700;color:#059669;"><?= $totAsignado ?></p>
        </div>
        <div class="caja-card" style="flex:1;min-width:180px;text-align:center;">
            <p style="font-size:0.68rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.4rem;">Disponibles</p>
            <p style="font-size:1.6rem;font-weight:700;color:#d97706;"><?= $totLotes - $totAsignado ?></p>
        </div>
    </div>

    <!-- ── Filtros ─────────────────────────────────────────────── -->
    <section class="caja-card">
        <form method="GET" style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;">
            <input type="date" name="desde" class="caja-input" style="max-width:150px;" value="<?= htmlspecialchars($filtroDesde) ?>" onchange="this.form.submit()">
            <input type="date" name="hasta" class="caja-input" style="max-width:150px;" value="<?= htmlspecialchars($filtroHasta) ?>" onchange="this.form.submit()">
            <select name="caja" class="caja-input" style="max-width:190px;" onchange="this.form.submit()">
                <option value="0">— Todas las cajas —</option>
                <?php foreach ($cajas as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $filtroCaja == $c['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['descripcion']) ?> (<?= htmlspecialchars($c['local_desc']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="terminal" class="caja-input" style="max-width:160px;" onchange="this.form.submit()">
                <option value="">— Todos los terminales —</option>
                <?php foreach ($terminalesFiltro as $tid): ?>
                    <option value="<?= htmlspecialchars($tid) ?>" <?= $filtroTerminal === $tid ? 'selected' : '' ?>><?= htmlspecialchars($tid) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="estado" class="caja-input" style="max-width:160px;" onchange="this.form.submit()">
                <option value="">— Todos los estados —</option>
                <option value="DISPONIBLE" <?= $filtroEstado === 'DISPONIBLE' ? 'selected' : '' ?>>Disponible</option>
                <option value="ASIGNADO" <?= $filtroEstado === 'ASIGNADO' ? 'selected' : '' ?>>Asignado</option>
            </select>
        </form>
    </section>

    <!-- ── Tabla de lotes ─────────────────────────────────────── -->
    <section class="caja-card" style="padding:1rem;">
        <div id="lotesMsg" class="caja-alert" hidden></div>
        <table class="caja-table" style="width:100%;table-layout:fixed;">
            <colgroup>
                <col style="width:85px;">
                <col style="width:150px;">
                <col style="width:75px;">
                <col style="width:90px;">
                <col style="width:60px;">
                <col style="width:95px;">
                <col style="width:90px;">
                <col style="width:130px;">
                <col style="width:110px;">
            </colgroup>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Terminal → Caja</th>
                    <th>N° Lote</th>
                    <th>Sugerido</th>
                    <th class="text-center">Txns</th>
                    <th class="text-right">Monto</th>
                    <th class="text-right">Comisión</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center">Detalle</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($lotes as $l): ?>
                <tr>
                    <td style="font-size:.8rem;"><?= date('d/m/Y', strtotime($l['fecha'])) ?></td>
                    <td style="font-size:.8rem;">
                        <span style="font-family:monospace;"><?= htmlspecialchars($l['terminal_id']) ?></span>
                        <?php if ($l['caja_desc']): ?>
                            <span style="color:#94a3b8;display:block;font-size:.72rem;"><?= htmlspecialchars($l['caja_desc']) ?> (<?= htmlspecialchars($l['local_desc']) ?>)</span>
                        <?php else: ?>
                            <span style="color:#dc2626;display:block;font-size:.72rem;">sin mapear</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:.8rem;font-family:monospace;"><?= htmlspecialchars($l['numero_lote']) ?></td>
                    <td style="font-size:.72rem;color:#94a3b8;"><?= $sugeridoLbl[$l['orden_dia']] ?? 'Último cuadre' ?></td>
                    <td class="text-center" style="font-size:.8rem;"><?= (int)$l['cantidad_transacciones'] ?></td>
                    <td class="text-right" style="font-size:.85rem;font-weight:700;">S/ <?= number_format($l['monto_total'], 2) ?></td>
                    <td class="text-right" style="font-size:.78rem;color:#64748b;">S/ <?= number_format($l['comision_total'], 2) ?></td>
                    <td class="text-center">
                        <?php if ($l['estado'] === 'ASIGNADO'): ?>
                            <span class="badge badge-contratado">Asignado</span>
                            <a href="<?= $basePath ?>/caja/reporte/<?= $l['sesion_id'] ?>" target="_blank" style="display:block;font-size:.7rem;margin-top:2px;">Ver cuadre →</a>
                        <?php else: ?>
                            <span class="badge badge-pendiente">Disponible</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <button class="btn-edit" style="font-size:.68rem;padding:3px 8px;" onclick="verTransacciones(<?= $l['id'] ?>, this)">Ver transacciones</button>
                    </td>
                </tr>
                <tr id="det-<?= $l['id'] ?>" style="display:none;">
                    <td colspan="9" style="background:#f8fafc;"><div id="det-body-<?= $l['id'] ?>" style="padding:.5rem;"></div></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($lotes)): ?>
                <tr><td colspan="9" class="caja-table__empty">No hay lotes importados con estos filtros.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </section>

</main>

<script>
const BASE = '<?= $basePath ?>';

function mostrarMsg(id, texto, tipo) {
    const msg = document.getElementById(id);
    msg.textContent = texto;
    msg.className = (id === 'importMsg' ? '' : 'caja-alert ') + (tipo === 'ok' ? 'caja-alert--ok' : 'caja-alert--error');
    msg.hidden = false;
    msg.style.display = 'block';
}

document.getElementById('uploadForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const input = document.getElementById('archivoInput');
    if (!input.files.length) { mostrarMsg('importMsg', 'Selecciona un archivo primero.', 'error'); return; }

    const fd = new FormData();
    fd.append('archivo', input.files[0]);

    try {
        const r = await fetch(`${BASE}/caja/api/conciliacion/importar`, { method: 'POST', body: fd });
        const res = await r.json();
        if (res.success) {
            const d = res.data;
            let texto = `✓ ${d.nuevos} nuevas, ${d.actualizados} actualizadas, ${d.lotes_afectados} lote(s) afectado(s).`;
            if (d.sin_terminal_mapeado && d.sin_terminal_mapeado.length) {
                texto += ` ⚠ Terminales sin mapear: ${d.sin_terminal_mapeado.join(', ')}.`;
            }
            mostrarMsg('importMsg', texto, 'ok');
            setTimeout(() => location.reload(), 1600);
        } else {
            mostrarMsg('importMsg', res.message || 'Error al importar.', 'error');
        }
    } catch {
        mostrarMsg('importMsg', 'Error de conexión.', 'error');
    }
});

async function guardarMapeo(terminalId) {
    const sel = document.getElementById(`map_${terminalId}`);
    const cajaId = sel.value;
    if (!cajaId) { alert('Selecciona una caja.'); return; }
    try {
        const r = await fetch(`${BASE}/caja/api/conciliacion/mapeo`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ terminal_id: terminalId, caja_id: cajaId }),
        });
        const res = await r.json();
        if (res.success) location.reload();
        else alert(res.message || 'Error al guardar el mapeo.');
    } catch {
        alert('Error de conexión.');
    }
}

async function verTransacciones(loteId, btn) {
    const row = document.getElementById(`det-${loteId}`);
    if (row.style.display === 'table-row') { row.style.display = 'none'; return; }

    const body = document.getElementById(`det-body-${loteId}`);
    body.innerHTML = 'Cargando…';
    row.style.display = 'table-row';

    try {
        const r = await fetch(`${BASE}/caja/api/conciliacion/lote/${loteId}/transacciones`);
        const res = await r.json();
        if (!res.success) { body.innerHTML = 'Error al cargar.'; return; }

        let html = `<table class="caja-table" style="font-size:.76rem;"><thead><tr>
            <th>Hora</th><th>Marca</th><th>Últ. 4</th><th>Banco</th>
            <th class="text-right">Monto</th><th class="text-right">Comisión</th><th class="text-right">Abono</th>
            <th class="text-center">Estado</th><th class="text-center">Incluir</th></tr></thead><tbody>`;
        for (const t of res.data) {
            const estCls = t.estado === 'abonada' ? 'color:#059669;font-weight:700;'
                         : t.estado === 'rechazada' ? 'color:#dc2626;'
                         : 'color:#d97706;';
            html += `<tr>
                <td>${t.hora_transaccion}</td>
                <td>${t.marca ?? '—'} ${t.tipo_pago ?? ''}</td>
                <td>${t.ult4 ?? '—'}</td>
                <td>${t.nombre_banco ?? '—'}</td>
                <td class="text-right">S/ ${parseFloat(t.monto_venta).toFixed(2)}</td>
                <td class="text-right">S/ ${parseFloat(t.comision_total ?? 0).toFixed(2)}</td>
                <td class="text-right">S/ ${parseFloat(t.monto_abono ?? 0).toFixed(2)}</td>
                <td class="text-center" style="${estCls}">${t.estado}</td>
                <td class="text-center"><input type="checkbox" ${t.incluido == 1 ? 'checked' : ''} onchange="toggleIncluido(${t.id}, this.checked)"></td>
            </tr>`;
        }
        html += '</tbody></table>';
        body.innerHTML = html;
    } catch {
        body.innerHTML = 'Error de conexión.';
    }
}

async function toggleIncluido(id, incluido) {
    try {
        await fetch(`${BASE}/caja/api/conciliacion/transaccion/${id}/incluir`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ incluido }),
        });
    } catch {
        alert('Error de conexión.');
    }
}
</script>
</body>
</html>
