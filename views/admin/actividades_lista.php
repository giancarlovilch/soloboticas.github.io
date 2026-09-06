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
$actLocalesLimpieza   = $actLocalesLimpieza   ?? [];
$actMatrizValores     = $actMatrizValores     ?? [];
$actValoresLocalHistorial = $actValoresLocalHistorial ?? [];
$actVotosLegado       = $actVotosLegado       ?? [];
$actTrabajadorId      = $actTrabajadorId      ?? 0;
$actTrabajadorNombre  = $actTrabajadorNombre  ?? null;
$actDetalleEstrellas  = $actDetalleEstrellas  ?? null;
$actDetalleTareas     = $actDetalleTareas     ?? [];
$actDetalleTurnos     = $actDetalleTurnos     ?? [];

$meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
[$anioF, $nmesF] = explode('-', $actMes);
$mesLabel = $meses[(int)$nmesF - 1] . ' ' . $anioF;
$fmtEst = fn($v) => (floor($v) == $v) ? (string)(int)$v : number_format($v, 1);
$hoy = date('Y-m-d');
$diasLabel  = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
$turnoLabel = [1 => '☀️ Mañana', 2 => '🌙 Tarde'];

$actTabInicial = in_array($_GET['tab'] ?? '', ['resumen', 'actividades', 'legado', 'reporte'], true) ? $_GET['tab'] : 'resumen';
?>
<style>
.eco-wrap { max-width:1000px;margin:0 auto;padding:.5rem 0 3rem; }
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
.act-add-form input, .act-add-form select { padding:.4rem .65rem;border:1.5px solid #fbcfe8;border-radius:7px;font-size:.8rem;width:100%;box-sizing:border-box; }
.act-msg { font-size:.78rem;padding:.4rem .75rem;border-radius:7px;margin-top:.6rem;display:none; }
@media(max-width:600px){ .act-kpi-box{flex-direction:column;align-items:flex-start;gap:.4rem;} }

/* Pestañas */
.act-tabs  { display:flex;gap:.5rem;margin-bottom:1.25rem;flex-wrap:wrap; }
.act-tab   { border:1.5px solid #fbcfe8;background:#fff;color:#9d174d;border-radius:10px;padding:.55rem 1.1rem;font-size:.8rem;font-weight:700;cursor:pointer; }
.act-tab.active { background:#fdf2f8;border-color:#f472b6;color:#be185d; }
.act-panel { display:none; }
.act-panel.active { display:block; }

/* Matriz de valores por local */
.vl-base { color:#94a3b8;font-size:.68rem;font-weight:500; }

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

    <div class="act-tabs">
        <button class="act-tab <?= $actTabInicial === 'resumen' ? 'active' : '' ?>" data-tab="resumen" onclick="actTabIr('resumen')">📊 Tasas y resumen</button>
        <button class="act-tab <?= $actTabInicial === 'actividades' ? 'active' : '' ?>" data-tab="actividades" onclick="actTabIr('actividades')">🧹 Actividades y valores</button>
        <button class="act-tab <?= $actTabInicial === 'legado' ? 'active' : '' ?>" data-tab="legado" onclick="actTabIr('legado')">🗳️ Sistema anterior<?= !empty($actVotosLegado) ? ' (' . count($actVotosLegado) . ')' : '' ?></button>
        <button class="act-tab <?= $actTabInicial === 'reporte' ? 'active' : '' ?>" data-tab="reporte" onclick="actTabIr('reporte')">🧑 Reporte por trabajador</button>
    </div>

    <!-- ============================================================ -->
    <!-- TAB 1: Tasas y resumen                                        -->
    <!-- ============================================================ -->
    <section class="act-panel <?= $actTabInicial === 'resumen' ? 'active' : '' ?>" id="actPanel-resumen">

        <!-- Navegación mensual (solo afecta al resumen de estrellas de esta pestaña) -->
        <div class="eco-nav">
            <form method="GET" style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;">
                <input type="hidden" name="page" value="actividades">
                <input type="hidden" name="tab" id="actTabHiddenResumen" value="resumen">
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
    </section>

    <!-- ============================================================ -->
    <!-- TAB 2: Actividades y valores por local                        -->
    <!-- ============================================================ -->
    <section class="act-panel <?= $actTabInicial === 'actividades' ? 'active' : '' ?>" id="actPanel-actividades">

        <!-- ── Catálogo de actividades de limpieza ───────────────── -->
        <p class="eco-sec-title">🧹 Catálogo de actividades — valor base en estrellas</p>
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

        <!-- ── Valor por local ────────────────────────────────────── -->
        <p class="eco-sec-title">💰 Valor por local</p>
        <p style="font-size:.75rem;color:#64748b;margin:0 0 .75rem;">
            Por defecto una actividad vale lo mismo en cualquier local (su "Estrellas máx." de arriba). Si un local
            debe valer distinto — por ejemplo, el doble por ser más grande — regístralo aquí: solo afecta a ese
            local, los demás se quedan con el valor base.
        </p>
        <?php if (empty($actLocalesLimpieza) || empty($actTareasLimpieza)): ?>
        <div class="eco-empty">Aún no hay locales o actividades configuradas.</div>
        <?php else: ?>
        <div class="eco-table-wrap">
            <table class="eco-table">
                <thead>
                    <tr>
                        <th>Actividad</th>
                        <?php foreach ($actLocalesLimpieza as $l): ?>
                        <th class="text-center"><?= htmlspecialchars($l['descripcion']) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($actTareasLimpieza as $t): ?>
                <tr>
                    <td style="font-weight:600;">
                        <?= htmlspecialchars($t['descripcion']) ?>
                        <span class="vl-base">(base <?= (int)$t['estrellas_max'] ?>⭐)</span>
                    </td>
                    <?php foreach ($actLocalesLimpieza as $l):
                        $valor = $actMatrizValores[$t['id_tarea']][$l['id_local']] ?? (int)$t['estrellas_max'];
                        $esBase = $valor === (int)$t['estrellas_max'];
                    ?>
                    <td class="text-center">
                        <span class="eco-badge" style="background:<?= $esBase ? '#f1f5f9' : '#fce7f3' ?>;color:<?= $esBase ? '#64748b' : '#be185d' ?>;">
                            <?= $valor ?> ⭐
                        </span>
                    </td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="act-card">
            <p style="font-size:.75rem;font-weight:600;color:#475569;margin-bottom:.5rem;">Registrar un valor especial</p>
            <div class="act-add-form">
                <div style="min-width:150px;">
                    <label>Actividad</label>
                    <select id="vlNuevaTarea">
                        <?php foreach ($actTareasLimpieza as $t): ?>
                        <option value="<?= (int)$t['id_tarea'] ?>"><?= htmlspecialchars($t['descripcion']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="min-width:150px;">
                    <label>Local</label>
                    <select id="vlNuevoLocal">
                        <?php foreach ($actLocalesLimpieza as $l): ?>
                        <option value="<?= (int)$l['id_local'] ?>"><?= htmlspecialchars($l['descripcion']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="min-width:100px;">
                    <label>Estrellas</label>
                    <input type="number" id="vlNuevoValor" min="0" max="50" placeholder="10">
                </div>
                <div style="min-width:160px;">
                    <label>Vigente desde</label>
                    <input type="date" id="vlNuevaFecha" value="<?= $hoy ?>">
                </div>
                <button onclick="vlAgregar()"
                        style="background:#db2777;color:#fff;border:none;border-radius:8px;padding:.5rem 1.1rem;font-size:.8rem;font-weight:700;cursor:pointer;white-space:nowrap;">
                    + Guardar valor
                </button>
            </div>
            <div id="vlMsg" class="act-msg"></div>
        </div>

        <?php if (!empty($actValoresLocalHistorial)): ?>
        <div class="eco-table-wrap" style="margin-top:.75rem;">
            <table class="eco-table">
                <thead>
                    <tr><th>Actividad</th><th>Local</th><th class="text-center">Estrellas</th><th>Vigente desde</th><th>Registrado</th><th></th></tr>
                </thead>
                <tbody>
                <?php foreach ($actValoresLocalHistorial as $v): ?>
                <tr id="vl-<?= (int)$v['id'] ?>">
                    <td><?= htmlspecialchars($v['tarea']) ?></td>
                    <td><?= htmlspecialchars($v['local_desc']) ?></td>
                    <td class="text-center" style="font-weight:700;color:#be185d;"><?= (int)$v['estrellas'] ?> ⭐</td>
                    <td><?= date('d/m/Y', strtotime($v['fecha_vigencia'])) ?></td>
                    <td style="font-size:.72rem;color:#94a3b8;"><?= date('d/m/Y H:i', strtotime($v['creado_en'])) ?></td>
                    <td>
                        <button onclick="vlEliminar(<?= (int)$v['id'] ?>)"
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
        <?php endif; ?>
    </section>

    <!-- ============================================================ -->
    <!-- TAB: Sistema anterior (votos entre compañeros)                -->
    <!-- ============================================================ -->
    <section class="act-panel <?= $actTabInicial === 'legado' ? 'active' : '' ?>" id="actPanel-legado">

        <div class="eco-nav">
            <form method="GET" style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;">
                <input type="hidden" name="page" value="actividades">
                <input type="hidden" name="tab" id="actTabHiddenLegado" value="legado">
                <input type="month" name="mes" value="<?= htmlspecialchars($actMes) ?>"
                       max="<?= $actMesActual ?>" onchange="this.form.submit()"
                       style="padding:.35rem .7rem;border-radius:8px;border:1.5px solid #fbcfe8;
                              background:#fff0f6;color:#9d174d;font-size:.85rem;font-weight:600;
                              cursor:pointer;outline:none;">
            </form>
        </div>

        <p class="eco-sec-title">🗳️ Votos del sistema anterior — <?= $mesLabel ?></p>
        <p style="font-size:.75rem;color:#64748b;margin:0 0 .9rem;">
            Esto es lo que quedó del sistema viejo, donde un compañero calificaba a otro (antes de la autoasignación
            en <code>/staff/limpieza</code>). Si alguien marcó una limpieza que en realidad no pasó, elimínala aquí —
            se le quita esa estrella automáticamente del balance del mes.
        </p>

        <?php if (empty($actVotosLegado)): ?>
        <div class="eco-empty">No hay votos del sistema anterior en <?= $mesLabel ?>.</div>
        <?php else: ?>
        <div class="eco-table-wrap">
            <table class="eco-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Local</th>
                        <th>Turno</th>
                        <th>Actividad</th>
                        <th>Trabajador</th>
                        <th class="text-center">⭐</th>
                        <th>Registrado por</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($actVotosLegado as $v): ?>
                <tr id="vlg-<?= (int)$v['id_voto'] ?>">
                    <td><?= date('d/m/Y', strtotime($v['fecha'])) ?></td>
                    <td><?= htmlspecialchars($v['local_desc']) ?></td>
                    <td><?= htmlspecialchars($v['turno_desc']) ?></td>
                    <td><?= htmlspecialchars($v['tarea_desc']) ?></td>
                    <td style="font-weight:600;"><?= htmlspecialchars($v['nombre']) ?></td>
                    <td class="text-center" style="font-weight:700;color:#1d4ed8;"><?= (int)$v['calificacion'] ?></td>
                    <td style="color:#64748b;"><?= htmlspecialchars($v['votante_nombre']) ?></td>
                    <td>
                        <button onclick="vlgEliminar(<?= (int)$v['id_voto'] ?>)"
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
    </section>

    <!-- ============================================================ -->
    <!-- TAB 3: Reporte detallado por trabajador                       -->
    <!-- ============================================================ -->
    <section class="act-panel <?= $actTabInicial === 'reporte' ? 'active' : '' ?>" id="actPanel-reporte">

        <p class="eco-sec-title">🧑 Reporte detallado por trabajador — <?= $mesLabel ?></p>
        <form method="GET" class="rep-select-wrap">
            <input type="hidden" name="page" value="actividades">
            <input type="hidden" name="tab" id="actTabHiddenReporte" value="reporte">
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
                    <div class="rep-item__sub"><?= $dow ?> <?= date('d/m', strtotime($d['fecha'])) ?> · <?= htmlspecialchars($d['local_desc']) ?><?= !empty($d['turno_desc']) ? ' · ' . htmlspecialchars($d['turno_desc']) : '' ?></div>
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
    </section>

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

// ── Pestañas ──────────────────────────────────────────────
function actTabIr(which) {
    document.querySelectorAll('.act-tab').forEach(b => b.classList.toggle('active', b.dataset.tab === which));
    document.querySelectorAll('.act-panel').forEach(p => p.classList.toggle('active', p.id === `actPanel-${which}`));
    document.querySelectorAll('input[type=hidden][name=tab]').forEach(i => i.value = which);
    const url = new URL(window.location);
    url.searchParams.set('tab', which);
    history.replaceState(null, '', url);
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

// ── Valor por local ───────────────────────────────────────
async function vlAgregar() {
    const data = {
        tarea_id:       document.getElementById('vlNuevaTarea').value,
        local_id:       document.getElementById('vlNuevoLocal').value,
        estrellas:      document.getElementById('vlNuevoValor').value,
        fecha_vigencia: document.getElementById('vlNuevaFecha').value,
    };
    if (data.estrellas === '' || !data.fecha_vigencia) {
        actShowMsg('vlMsg', 'Completa las estrellas y la fecha.', false);
        return;
    }
    const r   = await fetch(actApiUrl('/admin/api/valor-local/agregar'), {
        method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data),
    });
    const res = await r.json();
    if (res.success) { actShowMsg('vlMsg', 'Valor guardado.', true); setTimeout(() => location.reload(), 900); }
    else actShowMsg('vlMsg', res.message || 'Error.', false);
}

async function vlEliminar(id) {
    if (!confirm('¿Eliminar este valor especial?')) return;
    const r   = await fetch(actApiUrl(`/admin/api/valor-local/${id}/eliminar`), { method: 'POST' });
    const res = await r.json();
    if (res.success) { actShowMsg('vlMsg', 'Valor eliminado.', true); setTimeout(() => location.reload(), 900); }
    else actShowMsg('vlMsg', res.message || 'Error.', false);
}

// ── Sistema anterior (votos entre compañeros) ────────────
async function vlgEliminar(id) {
    if (!confirm('¿Eliminar este registro? Esa estrella se le quitará al trabajador.')) return;
    const r   = await fetch(actApiUrl(`/admin/api/voto-legado/${id}/eliminar`), { method: 'POST' });
    const res = await r.json();
    if (res.success) document.getElementById(`vlg-${id}`)?.remove();
    else alert(res.message || 'Error al eliminar.');
}
</script>
