<?php
if (!isset($_SESSION['user_rol'])) exit('Acceso denegado');

extract($actividadesDatos ?? []);
$actMes               = $actMes               ?? date('Y-m');
$actMesActual         = $actMesActual         ?? date('Y-m');
$actDesde             = $actDesde             ?? date('Y-m-01');
$actHasta             = $actHasta             ?? date('Y-m-t');
$actEstrellas         = $actEstrellas         ?? [];
$actTareasLimpieza    = $actTareasLimpieza    ?? [];
$actTasaRojaHistorial = $actTasaRojaHistorial ?? [];
$actTasaRojaVigente   = $actTasaRojaVigente   ?? 0;
$actMovimientos       = $actMovimientos       ?? [];

$meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
[$anioF, $nmesF] = explode('-', $actMes);
$mesLabel = $meses[(int)$nmesF - 1] . ' ' . $anioF;
$fmtEst = fn($v) => (floor($v) == $v) ? (string)(int)$v : number_format($v, 1);
$hoy = date('Y-m-d');
?>
<style>
.eco-wrap { max-width:900px;margin:0 auto;padding:.5rem 0 3rem; }
.eco-nav  { display:flex;align-items:center;gap:.5rem;margin-bottom:1.25rem;flex-wrap:wrap; }
.eco-sec-title  { font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#be185d;margin:1.5rem 0 .65rem; }
.eco-table-wrap { overflow-x:auto; }
.eco-table { width:100%;border-collapse:collapse;font-size:.80rem;background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.06); }
.eco-table th { background:#fff0f6;padding:.5rem .75rem;font-size:.63rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#be185d;border-bottom:2px solid #fbcfe8;white-space:nowrap; }
.eco-table td { padding:.55rem .75rem;border-bottom:1px solid #fdf2f8;vertical-align:middle; }
.eco-table tr:last-child td { border-bottom:none; }
.eco-table tr:hover td { background:#fff8fc; }
.eco-badge { display:inline-block;font-size:.67rem;font-weight:700;padding:2px 8px;border-radius:20px;white-space:nowrap; }
.eco-empty { text-align:center;padding:2rem;color:#94a3b8; }
.act-kpi-box { display:flex;align-items:center;gap:1.25rem;background:#fff;border:1.5px solid #fbcfe8;border-radius:12px;padding:1rem 1.25rem;margin-bottom:1.25rem;flex-wrap:wrap; }
.act-kpi-box__num { font-size:1.8rem;font-weight:800;color:#9d174d;line-height:1; }
.act-kpi-box__label { font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#be185d;margin-top:2px; }
.act-card { background:#fff;border:1.5px solid #fbcfe8;border-radius:10px;padding:.9rem 1rem;margin-top:.75rem; }
.act-add-form { display:flex;gap:.5rem;flex-wrap:wrap;align-items:flex-end; }
.act-add-form label { font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#64748b;display:block;margin-bottom:2px; }
.act-add-form input { padding:.4rem .65rem;border:1.5px solid #fbcfe8;border-radius:7px;font-size:.8rem;width:100%;box-sizing:border-box; }
.act-msg { font-size:.78rem;padding:.4rem .75rem;border-radius:7px;margin-top:.6rem;display:none; }
@media(max-width:600px){ .act-kpi-box{flex-direction:column;align-items:flex-start;gap:.4rem;} }
</style>

<div class="eco-wrap">

    <div class="section-header" style="margin-bottom:1rem;">
        <div class="header-info">
            <p class="section-kicker">Actividades</p>
            <h2>Estrellas del equipo y tareas de limpieza</h2>
        </div>
    </div>

    <!-- Navegación mensual (solo afecta al resumen de estrellas de abajo) -->
    <div class="eco-nav">
        <form method="GET" style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;">
            <input type="hidden" name="page" value="actividades">
            <input type="month" name="mes" value="<?= htmlspecialchars($actMes) ?>"
                   max="<?= $actMesActual ?>" onchange="this.form.submit()"
                   style="padding:.35rem .7rem;border-radius:8px;border:1.5px solid #fbcfe8;
                          background:#fff0f6;color:#9d174d;font-size:.85rem;font-weight:600;
                          cursor:pointer;outline:none;">
        </form>
    </div>

    <!-- ── Tasa de estrellas rojas por turno ─────────────────── -->
    <p class="eco-sec-title">🔴 Tasa de estrellas rojas por turno</p>
    <div class="act-kpi-box">
        <div>
            <div class="act-kpi-box__num"><?= (int)$actTasaRojaVigente ?></div>
            <div class="act-kpi-box__label">Tasa vigente hoy</div>
        </div>
        <p style="font-size:.75rem;color:#64748b;margin:0;flex:1;min-width:200px;">
            Cada trabajador suma esta cantidad de estrellas rojas automáticamente por cada turno asistido.
            Al guardar una nueva tasa con una fecha de inicio, <strong>todo el sistema se recalcula solo</strong> —
            no hace falta ningún script ni botón de "actualizar".
        </p>
    </div>

    <div class="act-card">
        <p style="font-size:.75rem;font-weight:600;color:#475569;margin-bottom:.5rem;">Registrar nueva tasa</p>
        <div class="act-add-form">
            <div style="min-width:120px;">
                <label>Estrellas rojas por turno</label>
                <input type="number" id="trNuevoMonto" min="0" max="50" placeholder="2">
            </div>
            <div style="min-width:160px;">
                <label>Vigente desde</label>
                <input type="date" id="trNuevaFecha" value="<?= $hoy ?>">
            </div>
            <button onclick="trAgregar()"
                    style="background:#db2777;color:#fff;border:none;border-radius:8px;padding:.5rem 1.1rem;font-size:.8rem;font-weight:700;cursor:pointer;white-space:nowrap;">
                + Guardar tasa
            </button>
        </div>
        <p style="font-size:.68rem;color:#94a3b8;margin:.5rem 0 0;">
            Tip: si ya llevan trabajando varios días con la tasa vieja y quieres que el cambio aplique a todo el mes
            (o desde que arrancó el sistema), pon esa fecha anterior en vez de hoy.
        </p>
        <div id="trMsg" class="act-msg"></div>
    </div>

    <?php if (!empty($actTasaRojaHistorial)): ?>
    <div class="eco-table-wrap" style="margin-top:.75rem;">
        <table class="eco-table">
            <thead>
                <tr>
                    <th>Estrellas rojas / turno</th>
                    <th>Vigente desde</th>
                    <th>Registrado</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($actTasaRojaHistorial as $tr): ?>
            <tr>
                <td style="font-weight:700;color:#dc2626;">🔴 <?= (int)$tr['monto'] ?></td>
                <td><?= date('d/m/Y', strtotime($tr['fecha_vigencia'])) ?></td>
                <td style="font-size:.72rem;color:#94a3b8;"><?= date('d/m/Y H:i', strtotime($tr['creado_en'])) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- ── Estrellas del equipo ──────────────────────────────── -->
    <p class="eco-sec-title">⭐ Estrellas del equipo — <?= $mesLabel ?></p>
    <?php if (empty($actEstrellas)): ?>
    <div class="eco-empty">Sin datos de estrellas en <?= $mesLabel ?>.</div>
    <?php else: ?>
    <div class="eco-table-wrap">
        <table class="eco-table">
            <thead>
                <tr>
                    <th>Trabajador</th>
                    <th class="text-center">Turnos</th>
                    <th class="text-center">🔴 Rojas</th>
                    <th class="text-center">🔵 Azules</th>
                    <th>Balance</th>
                    <th class="text-center">🧹 Barrió</th>
                    <th class="text-center">💧 Trapeó</th>
                    <th class="text-right">S/ mes</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($actEstrellas as $e):
                $rojas = (int)$e['rojas']; $azules = (float)$e['azules'];
                $tot   = max(1, $rojas + $azules);
                $pctAzul = round(($azules / $tot) * 100);
                $gana  = $azules >= $rojas;
                $diferencia = (float)($e['diferencia'] ?? ($rojas - $azules));
                $monto      = (float)($e['monto']      ?? round($diferencia * 0.10, 2));
            ?>
            <tr>
                <td style="font-weight:600;font-size:.82rem;color:#1e293b;"><?= htmlspecialchars($e['nombre']) ?></td>
                <td class="text-center"><?= (int)$e['turnos'] ?></td>
                <td class="text-center" style="color:#dc2626;font-weight:700;"><?= $rojas ?></td>
                <td class="text-center" style="color:#1d4ed8;font-weight:700;"><?= $fmtEst($azules) ?></td>
                <td style="min-width:120px;">
                    <div style="height:10px;border-radius:999px;overflow:hidden;background:#dc2626;display:flex;">
                        <div style="width:<?= $pctAzul ?>%;background:linear-gradient(90deg,#3b82f6,#1d4ed8);"></div>
                        <div style="flex:1;background:linear-gradient(90deg,#ef4444,#dc2626);"></div>
                    </div>
                    <span style="font-size:.65rem;font-weight:700;color:<?= $gana ? '#1d4ed8' : '#dc2626' ?>;">
                        <?= $gana ? '✅ Va ganando' : '⚠️ Riesgo de descuento' ?>
                    </span>
                </td>
                <td class="text-center"><?= (int)($e['tareas']['BARRER']  ?? 0) ?>×</td>
                <td class="text-center"><?= (int)($e['tareas']['TRAPEAR'] ?? 0) ?>×</td>
                <td class="text-right">
                    <?php if ($diferencia > 0): ?>
                    <span class="eco-badge" style="background:#fee2e2;color:#991b1b;">− S/ <?= number_format(abs($monto), 2) ?></span>
                    <?php elseif ($diferencia < 0): ?>
                    <span class="eco-badge" style="background:#dbeafe;color:#1d4ed8;">+ S/ <?= number_format(abs($monto), 2) ?></span>
                    <?php else: ?>
                    <span class="eco-badge" style="background:#f1f5f9;color:#64748b;">S/ 0.00</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p style="font-size:.68rem;color:#94a3b8;margin-top:.5rem;">
        Cada mes arranca 50 vs 50 · cada ⭐ de diferencia equivale a S/ 0.10 al cierre del mes.
    </p>
    <?php endif; ?>

    <!-- ── Detalle de movimientos (seguimiento de puntos azules) ─ -->
    <p class="eco-sec-title">🔍 Detalle de movimientos — <?= $mesLabel ?></p>
    <p style="font-size:.75rem;color:#64748b;margin:-.4rem 0 .65rem;">
        Cada voto de limpieza y cada ajuste por sanción, uno por uno. Si a alguien "le falta" una estrella
        azul frente a lo que ve reflejado, es porque uno de sus registros aparece aquí como
        <strong style="color:#991b1b;">denunciado o sancionado</strong> (vale 0 en vez del valor original).
    </p>
    <input type="text" id="movFiltroNombre" placeholder="🔎 Filtrar por nombre (quién dio o quién recibió)…"
           oninput="movFiltrar()"
           style="width:100%;box-sizing:border-box;padding:.55rem .8rem;border:1.5px solid #fbcfe8;
                  border-radius:8px;font-size:.82rem;margin-bottom:.75rem;outline:none;">
    <?php if (empty($actMovimientos)): ?>
    <div class="eco-empty">Sin movimientos de estrellas azules en <?= $mesLabel ?>.</div>
    <?php else: ?>
    <div class="eco-table-wrap">
        <table class="eco-table" id="movTabla">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Quién dio</th>
                    <th>Quién recibió</th>
                    <th>Actividad / Motivo</th>
                    <th class="text-center">🔵 Estrellas</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($actMovimientos as $m):
                $nombres = mb_strtolower(($m['votante_nombre'] ?? '') . ' ' . $m['beneficiario_nombre']);
                if ($m['tipo'] === 'ajuste') {
                    $estado = '<span class="eco-badge" style="background:#f1f5f9;color:#64748b;">⚖️ Ajuste por sanción</span>';
                } elseif ($m['sancionado']) {
                    $estado = '<span class="eco-badge" style="background:#fee2e2;color:#991b1b;">🚫 Sancionado (' . (int)$m['reportes'] . ' denuncias)</span>';
                } elseif ($m['reportes'] > 0) {
                    $estado = '<span class="eco-badge" style="background:#fef3c7;color:#92400e;">⚠️ Denunciado ' . (int)$m['reportes'] . '/2</span>';
                } else {
                    $estado = '<span class="eco-badge" style="background:#d1fae5;color:#065f46;">✅ Normal</span>';
                }
            ?>
            <tr data-nombres="<?= htmlspecialchars($nombres) ?>">
                <td style="white-space:nowrap;font-size:.75rem;color:#64748b;"><?= date('d/m/Y', strtotime($m['fecha'])) ?></td>
                <td style="font-weight:600;"><?= $m['tipo'] === 'ajuste' ? '—' : htmlspecialchars($m['votante_nombre']) ?></td>
                <td style="font-weight:600;"><?= htmlspecialchars($m['beneficiario_nombre']) ?></td>
                <td>
                    <?= htmlspecialchars($m['detalle']) ?>
                    <?php if (!empty($m['local_desc'])): ?>
                    <div style="font-size:.68rem;color:#94a3b8;"><?= htmlspecialchars($m['local_desc']) ?> · <?= htmlspecialchars($m['turno_desc']) ?></div>
                    <?php endif; ?>
                </td>
                <td class="text-center" style="font-weight:700;color:<?= $m['estrellas'] < 0 ? '#dc2626' : '#1d4ed8' ?>;">
                    <?= $m['estrellas'] > 0 ? '+' : '' ?><?= $fmtEst($m['estrellas']) ?>
                </td>
                <td><?= $estado ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p class="eco-empty" id="movVacio" style="display:none;">Ningún movimiento coincide con "<span id="movVacioTexto"></span>".</p>
    <?php endif; ?>

    <!-- ── Catálogo de actividades de limpieza ───────────────── -->
    <p class="eco-sec-title">🧹 Actividades de limpieza — valor en estrellas</p>
    <div class="eco-table-wrap">
        <table class="eco-table">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Actividad</th>
                    <th class="text-center">Estrellas máx.</th>
                    <th class="text-center">Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="tlTbody">
            <?php foreach ($actTareasLimpieza as $t): ?>
            <tr id="tl-<?= (int)$t['id_tarea'] ?>" style="<?= $t['activo'] ? '' : 'opacity:.55;' ?>">
                <td style="font-family:monospace;font-size:.72rem;color:#64748b;"><?= htmlspecialchars($t['codigo']) ?></td>
                <td>
                    <input type="text" id="tlDesc-<?= $t['id_tarea'] ?>" value="<?= htmlspecialchars($t['descripcion']) ?>"
                           style="padding:.35rem .6rem;border:1.5px solid #fbcfe8;border-radius:7px;font-size:.8rem;width:100%;box-sizing:border-box;min-width:160px;">
                </td>
                <td class="text-center">
                    <input type="number" id="tlMax-<?= $t['id_tarea'] ?>" value="<?= (int)$t['estrellas_max'] ?>" min="1" max="50"
                           style="padding:.35rem .5rem;border:1.5px solid #fbcfe8;border-radius:7px;font-size:.8rem;width:64px;text-align:center;">
                </td>
                <td class="text-center">
                    <span class="eco-badge" style="background:<?= $t['activo'] ? '#d1fae5' : '#f1f5f9' ?>;color:<?= $t['activo'] ? '#065f46' : '#94a3b8' ?>;">
                        <?= $t['activo'] ? 'Activa' : 'Inactiva' ?>
                    </span>
                </td>
                <td style="white-space:nowrap;">
                    <button onclick="tlGuardar(<?= $t['id_tarea'] ?>)"
                            style="background:#1d4ed8;color:#fff;border:none;border-radius:6px;padding:5px 10px;font-size:.7rem;font-weight:700;cursor:pointer;margin-right:.3rem;">
                        Guardar
                    </button>
                    <button onclick="tlToggle(<?= $t['id_tarea'] ?>)"
                            style="background:none;border:1px solid #e2e8f0;border-radius:6px;padding:5px 10px;font-size:.7rem;font-weight:700;cursor:pointer;color:#64748b;">
                        <?= $t['activo'] ? 'Desactivar' : 'Activar' ?>
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="act-card">
        <p style="font-size:.75rem;font-weight:600;color:#475569;margin-bottom:.5rem;">Agregar nueva actividad</p>
        <div class="act-add-form">
            <div style="min-width:120px;flex:1;">
                <label>Código</label>
                <input type="text" id="tlNuevoCodigo" placeholder="VITRINAS" style="text-transform:uppercase;">
            </div>
            <div style="min-width:180px;flex:2;">
                <label>Descripción</label>
                <input type="text" id="tlNuevoDesc" placeholder="Limpiar vitrinas">
            </div>
            <div style="min-width:100px;">
                <label>Estrellas máx.</label>
                <input type="number" id="tlNuevoMax" min="1" max="50" placeholder="5">
            </div>
            <button onclick="tlAgregar()"
                    style="background:#db2777;color:#fff;border:none;border-radius:8px;padding:.5rem 1.1rem;font-size:.8rem;font-weight:700;cursor:pointer;white-space:nowrap;">
                + Agregar
            </button>
        </div>
        <div id="tlMsg" class="act-msg"></div>
    </div>

</div>

<script>
const BASE_ACT = window.location.pathname.split('/admin/')[0];
const actApiUrl = (p) => `${window.location.origin}${BASE_ACT}${p}`;

function actShowMsg(elId, msg, ok) {
    const el = document.getElementById(elId);
    el.textContent = msg;
    el.style.background = ok ? '#d1fae5' : '#fee2e2';
    el.style.color = ok ? '#065f46' : '#991b1b';
    el.style.display = 'block';
    setTimeout(() => el.style.display = 'none', 3500);
}

// ── Tasa de estrellas rojas ──────────────────────────────
async function trAgregar() {
    const data = {
        monto:          document.getElementById('trNuevoMonto').value,
        fecha_vigencia: document.getElementById('trNuevaFecha').value,
    };
    if (data.monto === '' || !data.fecha_vigencia) {
        actShowMsg('trMsg', 'Completa la cantidad de estrellas y la fecha.', false);
        return;
    }
    const r   = await fetch(actApiUrl('/admin/api/tasa-roja/agregar'), {
        method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data),
    });
    const res = await r.json();
    if (res.success) { actShowMsg('trMsg', 'Tasa guardada.', true); setTimeout(() => location.reload(), 900); }
    else actShowMsg('trMsg', res.message || 'Error.', false);
}

// ── Filtro de nombres en el detalle de movimientos ───────
function movFiltrar() {
    const texto = document.getElementById('movFiltroNombre').value.trim().toLowerCase();
    const filas = document.querySelectorAll('#movTabla tbody tr');
    let visibles = 0;
    filas.forEach(fila => {
        const coincide = fila.dataset.nombres.includes(texto);
        fila.style.display = coincide ? '' : 'none';
        if (coincide) visibles++;
    });
    const vacio = document.getElementById('movVacio');
    if (vacio) {
        vacio.style.display = (texto && visibles === 0) ? 'block' : 'none';
        document.getElementById('movVacioTexto').textContent = texto;
    }
}

// ── Catálogo de tareas de limpieza ───────────────────────
async function tlGuardar(id) {
    const data = {
        descripcion:   document.getElementById(`tlDesc-${id}`).value.trim(),
        estrellas_max: document.getElementById(`tlMax-${id}`).value,
    };
    const r   = await fetch(actApiUrl(`/admin/api/tarea-limpieza/${id}/actualizar`), {
        method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data),
    });
    const res = await r.json();
    if (res.success) { actShowMsg('tlMsg', 'Actividad actualizada.', true); setTimeout(() => location.reload(), 900); }
    else actShowMsg('tlMsg', res.message || 'Error.', false);
}

async function tlToggle(id) {
    const r   = await fetch(actApiUrl(`/admin/api/tarea-limpieza/${id}/toggle`), { method: 'POST' });
    const res = await r.json();
    if (res.success) location.reload();
    else actShowMsg('tlMsg', res.message || 'Error.', false);
}

async function tlAgregar() {
    const data = {
        codigo:        document.getElementById('tlNuevoCodigo').value.trim(),
        descripcion:   document.getElementById('tlNuevoDesc').value.trim(),
        estrellas_max: document.getElementById('tlNuevoMax').value,
    };
    if (!data.codigo || !data.descripcion || !data.estrellas_max) {
        actShowMsg('tlMsg', 'Completa código, descripción y estrellas máximas.', false);
        return;
    }
    const r   = await fetch(actApiUrl('/admin/api/tarea-limpieza/agregar'), {
        method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data),
    });
    const res = await r.json();
    if (res.success) { actShowMsg('tlMsg', 'Actividad agregada.', true); setTimeout(() => location.reload(), 900); }
    else actShowMsg('tlMsg', res.message || 'Error.', false);
}
</script>
