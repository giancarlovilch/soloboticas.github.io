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
$actTasaAzulVotoHistorial = $actTasaAzulVotoHistorial ?? [];
$actTasaAzulVotoVigente   = $actTasaAzulVotoVigente   ?? 0;
$actTrabajadorId      = $actTrabajadorId      ?? 0;
$actTrabajadorNombre  = $actTrabajadorNombre  ?? null;
$actDetalleEstrellas  = $actDetalleEstrellas  ?? null;
$actDetalleTareas     = $actDetalleTareas     ?? [];
$actDetalleVotos      = $actDetalleVotos      ?? [];
$actDetalleTurnos     = $actDetalleTurnos     ?? [];

$meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
[$anioF, $nmesF] = explode('-', $actMes);
$mesLabel = $meses[(int)$nmesF - 1] . ' ' . $anioF;
$fmtEst = fn($v) => (floor($v) == $v) ? (string)(int)$v : number_format($v, 1);
$hoy = date('Y-m-d');
$diasLabel  = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
$turnoLabel = [1 => '☀️ Mañana', 2 => '🌙 Tarde'];
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

/* Reporte detallado por trabajador */
.rep-select-wrap { display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;margin-bottom:1rem; }
.rep-select { padding:.55rem .8rem;border:1.5px solid #fbcfe8;border-radius:8px;font-size:.85rem;font-weight:600;color:#1e293b;background:#fff0f6;min-width:240px;outline:none; }
.rep-kpis { display:grid;grid-template-columns:repeat(2,1fr);gap:.65rem;margin-bottom:1rem;max-width:420px; }
.rep-kpi   { background:#fff;border:1.5px solid #e2e8f0;border-radius:12px;padding:.85rem 1rem;text-align:center; }
.rep-kpi__num   { font-size:1.4rem;font-weight:800; }
.rep-kpi__label { font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#64748b;margin-top:2px; }
.rep-monto { border-radius:12px;padding:1rem 1.2rem;margin-bottom:1.25rem;text-align:center;max-width:420px; }
.rep-monto--contra { background:#fee2e2;border:1.5px solid #fecaca; }
.rep-monto--favor  { background:#dbeafe;border:1.5px solid #bfdbfe; }
.rep-monto__label { font-size:.68rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.25rem; }
.rep-monto--contra .rep-monto__label { color:#991b1b; }
.rep-monto--favor  .rep-monto__label { color:#1d4ed8; }
.rep-monto__num { font-size:1.5rem;font-weight:800; }
.rep-monto--contra .rep-monto__num { color:#dc2626; }
.rep-monto--favor  .rep-monto__num { color:#1d4ed8; }
.rep-sub-title { font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#64748b;margin:1.25rem 0 .6rem; }
.rep-list { display:flex;flex-direction:column;gap:.5rem; }
.rep-item { background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:.65rem .85rem;display:flex;align-items:center;justify-content:space-between;gap:.5rem; }
.rep-item__main  { font-size:.82rem;font-weight:700;color:#1e293b; }
.rep-item__sub   { font-size:.7rem;color:#94a3b8;margin-top:1px; }
.rep-item__badge { font-size:.72rem;font-weight:800;color:#1d4ed8;white-space:nowrap; }
.rep-item__estado--sancionado { background:#fee2e2;color:#991b1b;font-size:.66rem;font-weight:700;padding:2px 8px;border-radius:20px;white-space:nowrap; }
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
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($actTasaRojaHistorial as $tr): ?>
            <tr id="tr-<?= (int)$tr['id'] ?>">
                <td style="font-weight:700;color:#dc2626;">🔴 <?= (int)$tr['monto'] ?></td>
                <td><?= date('d/m/Y', strtotime($tr['fecha_vigencia'])) ?></td>
                <td style="font-size:.72rem;color:#94a3b8;"><?= date('d/m/Y H:i', strtotime($tr['creado_en'])) ?></td>
                <td>
                    <button onclick="trEliminar(<?= (int)$tr['id'] ?>)"
                            style="background:none;border:1px solid #fecaca;border-radius:6px;padding:4px 9px;font-size:.68rem;font-weight:700;cursor:pointer;color:#dc2626;">
                        Eliminar
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- ── Estrellas azules por voto emitido ─────────────────── -->
    <p class="eco-sec-title">🔵 Estrellas azules por voto emitido</p>
    <div class="act-kpi-box">
        <div>
            <div class="act-kpi-box__num" style="color:#1d4ed8;"><?= $fmtEst($actTasaAzulVotoVigente) ?></div>
            <div class="act-kpi-box__label">Tasa vigente hoy</div>
        </div>
        <p style="font-size:.75rem;color:#64748b;margin:0;flex:1;min-width:200px;">
            Cada trabajador suma esta cantidad de estrellas azules automáticamente por cada voto que emite
            calificando a un compañero (independiente de la calificación que le dé). Al guardar una nueva
            tasa con una fecha de inicio, todo el sistema se recalcula solo.
        </p>
    </div>

    <div class="act-card">
        <p style="font-size:.75rem;font-weight:600;color:#475569;margin-bottom:.5rem;">Registrar nueva tasa</p>
        <div class="act-add-form">
            <div style="min-width:120px;">
                <label>Estrellas azules por voto</label>
                <input type="number" id="taNuevoMonto" min="0" max="10" step="0.5" placeholder="2">
            </div>
            <div style="min-width:160px;">
                <label>Vigente desde</label>
                <input type="date" id="taNuevaFecha" value="<?= $hoy ?>">
            </div>
            <button onclick="taAgregar()"
                    style="background:#db2777;color:#fff;border:none;border-radius:8px;padding:.5rem 1.1rem;font-size:.8rem;font-weight:700;cursor:pointer;white-space:nowrap;">
                + Guardar tasa
            </button>
        </div>
        <div id="taMsg" class="act-msg"></div>
    </div>

    <?php if (!empty($actTasaAzulVotoHistorial)): ?>
    <div class="eco-table-wrap" style="margin-top:.75rem;">
        <table class="eco-table">
            <thead>
                <tr>
                    <th>Estrellas azules / voto</th>
                    <th>Vigente desde</th>
                    <th>Registrado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($actTasaAzulVotoHistorial as $ta): ?>
            <tr id="ta-<?= (int)$ta['id'] ?>">
                <td style="font-weight:700;color:#1d4ed8;">🔵 <?= $fmtEst($ta['monto']) ?></td>
                <td><?= date('d/m/Y', strtotime($ta['fecha_vigencia'])) ?></td>
                <td style="font-size:.72rem;color:#94a3b8;"><?= date('d/m/Y H:i', strtotime($ta['creado_en'])) ?></td>
                <td>
                    <button onclick="taEliminar(<?= (int)$ta['id'] ?>)"
                            style="background:none;border:1px solid #bfdbfe;border-radius:6px;padding:4px 9px;font-size:.68rem;font-weight:700;cursor:pointer;color:#1d4ed8;">
                        Eliminar
                    </button>
                </td>
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

    <!-- ── Reporte detallado por trabajador (al final: es lo más largo) ── -->
    <p class="eco-sec-title">🧑 Reporte detallado por trabajador — <?= $mesLabel ?></p>
    <form method="GET" class="rep-select-wrap">
        <input type="hidden" name="page" value="actividades">
        <input type="hidden" name="mes" value="<?= htmlspecialchars($actMes) ?>">
        <select name="trabajador" class="rep-select" onchange="this.form.submit()">
            <option value="0">— Selecciona un trabajador —</option>
            <?php foreach ($actEstrellas as $e): ?>
            <option value="<?= (int)$e['id'] ?>" <?= $actTrabajadorId === (int)$e['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($e['nombre']) ?>
            </option>
            <?php endforeach; ?>
        </select>
    </form>

    <?php if ($actTrabajadorId === 0): ?>
    <div class="eco-empty">Elige un trabajador de la lista para ver su detalle de estrellas del mes.</div>
    <?php elseif ($actDetalleEstrellas === null): ?>
    <div class="eco-empty">No se encontró a ese trabajador en <?= $mesLabel ?>.</div>
    <?php else:
        $repDif      = (float)($actDetalleEstrellas['diferencia'] ?? 0);
        $repMonto    = (float)($actDetalleEstrellas['monto']      ?? 0);
        $repEnContra = $repDif > 0;
    ?>
    <div class="rep-kpis">
        <div class="rep-kpi">
            <div class="rep-kpi__num" style="color:#dc2626;">🔴 <?= (int)$actDetalleEstrellas['rojas'] ?></div>
            <div class="rep-kpi__label">Estrellas rojas</div>
        </div>
        <div class="rep-kpi">
            <div class="rep-kpi__num" style="color:#1d4ed8;">🔵 <?= $fmtEst($actDetalleEstrellas['azules']) ?></div>
            <div class="rep-kpi__label">Estrellas azules</div>
        </div>
    </div>

    <div class="rep-monto <?= $repEnContra ? 'rep-monto--contra' : 'rep-monto--favor' ?>">
        <div class="rep-monto__label"><?= $repEnContra ? '⚠️ Diferencia en contra' : '✅ Diferencia a favor' ?></div>
        <div class="rep-monto__num"><?= $fmtEst(abs($repDif)) ?> ⭐ = S/ <?= number_format(abs($repMonto), 2) ?></div>
    </div>

    <?php if (!empty($actDetalleEstrellas['azules_ajustes'])): ?>
    <div class="eco-empty" style="background:#fef2f2;border-color:#fecaca;color:#991b1b;text-align:left;margin-bottom:1.25rem;max-width:420px;">
        ⚖️ Ajustes por sanciones este mes: <strong><?= $actDetalleEstrellas['azules_ajustes'] > 0 ? '+' : '' ?><?= (int)$actDetalleEstrellas['azules_ajustes'] ?> ⭐</strong>
    </div>
    <?php endif; ?>

    <?php if (!empty($actDetalleEstrellas['votos_emitidos'])): ?>
    <div class="eco-empty" style="background:#eff6ff;border-color:#bfdbfe;color:#1d4ed8;text-align:left;margin-bottom:1.25rem;max-width:420px;">
        🗳️ Por calificar a compañeros: <strong>+<?= $fmtEst($actDetalleEstrellas['azules_votos']) ?> ⭐</strong>
        (<?= (int)$actDetalleEstrellas['votos_emitidos'] ?> voto<?= $actDetalleEstrellas['votos_emitidos'] == 1 ? '' : 's' ?>)
    </div>
    <?php endif; ?>

    <p class="rep-sub-title">🧹 Actividades reconocidas</p>
    <?php if (empty($actDetalleTareas)): ?>
    <div class="eco-empty">Sin actividades reconocidas en <?= $mesLabel ?>.</div>
    <?php else: ?>
    <div class="rep-list">
        <?php foreach ($actDetalleTareas as $d):
            $dow = $diasLabel[(int)date('w', strtotime($d['fecha']))];
        ?>
        <div class="rep-item">
            <div>
                <div class="rep-item__main"><?= htmlspecialchars($d['tarea']) ?></div>
                <div class="rep-item__sub"><?= $dow ?> <?= date('d/m', strtotime($d['fecha'])) ?> · <?= htmlspecialchars($d['local_desc']) ?> · <?= htmlspecialchars($d['turno_desc']) ?></div>
            </div>
            <?php if ($d['sancionado']): ?>
            <div style="text-align:right;">
                <div class="rep-item__badge" style="color:#dc2626;">0 ⭐</div>
                <span class="rep-item__estado--sancionado">🚫 Sancionado</span>
            </div>
            <?php else: ?>
            <div class="rep-item__badge">+<?= $fmtEst($d['azules']) ?> ⭐</div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <p class="rep-sub-title">🗳️ Votos que dio</p>
    <?php if (empty($actDetalleVotos)): ?>
    <div class="eco-empty">No calificó a ningún compañero en <?= $mesLabel ?>.</div>
    <?php else: ?>
    <div class="rep-list">
        <?php foreach ($actDetalleVotos as $v):
            $dow = $diasLabel[(int)date('w', strtotime($v['fecha']))];
        ?>
        <div class="rep-item">
            <div>
                <div class="rep-item__main">Calificó a <?= htmlspecialchars($v['beneficiario_nombre']) ?></div>
                <div class="rep-item__sub"><?= htmlspecialchars($v['tarea']) ?> · <?= $dow ?> <?= date('d/m', strtotime($v['fecha'])) ?> · <?= htmlspecialchars($v['local_desc']) ?> · <?= htmlspecialchars($v['turno_desc']) ?></div>
            </div>
            <div class="rep-item__badge">+<?= $fmtEst($v['azul_ganado']) ?> ⭐</div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <p class="rep-sub-title">📅 Turnos asistidos</p>
    <?php if (empty($actDetalleTurnos)): ?>
    <div class="eco-empty">Sin turnos registrados en <?= $mesLabel ?>.</div>
    <?php else: ?>
    <div class="rep-list">
        <?php foreach ($actDetalleTurnos as $t):
            $dow = $diasLabel[(int)date('w', strtotime($t['fecha']))];
        ?>
        <div class="rep-item">
            <div>
                <div class="rep-item__main"><?= $dow ?> <?= date('d/m', strtotime($t['fecha'])) ?></div>
                <div class="rep-item__sub"><?= htmlspecialchars($t['local_desc'] ?? '—') ?> · <?= $turnoLabel[$t['turno_id']] ?? 'Turno' ?></div>
            </div>
            <div class="rep-item__badge" style="color:#dc2626;">+<?= (int)$t['tasa_roja'] ?> 🔴</div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php endif; ?>

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

async function trEliminar(id) {
    if (!confirm('¿Eliminar esta tasa de estrellas rojas?')) return;
    const r   = await fetch(actApiUrl(`/admin/api/tasa-roja/${id}/eliminar`), { method: 'POST' });
    const res = await r.json();
    if (res.success) document.getElementById(`tr-${id}`)?.remove();
    else actShowMsg('trMsg', res.message || 'Error.', false);
}

// ── Tasa de estrellas azules por voto ────────────────────
async function taAgregar() {
    const data = {
        monto:          document.getElementById('taNuevoMonto').value,
        fecha_vigencia: document.getElementById('taNuevaFecha').value,
    };
    if (data.monto === '' || !data.fecha_vigencia) {
        actShowMsg('taMsg', 'Completa la cantidad de estrellas y la fecha.', false);
        return;
    }
    const r   = await fetch(actApiUrl('/admin/api/tasa-azul-voto/agregar'), {
        method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data),
    });
    const res = await r.json();
    if (res.success) { actShowMsg('taMsg', 'Tasa guardada.', true); setTimeout(() => location.reload(), 900); }
    else actShowMsg('taMsg', res.message || 'Error.', false);
}

async function taEliminar(id) {
    if (!confirm('¿Eliminar esta tasa de estrellas azules por voto?')) return;
    const r   = await fetch(actApiUrl(`/admin/api/tasa-azul-voto/${id}/eliminar`), { method: 'POST' });
    const res = await r.json();
    if (res.success) document.getElementById(`ta-${id}`)?.remove();
    else actShowMsg('taMsg', res.message || 'Error.', false);
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
