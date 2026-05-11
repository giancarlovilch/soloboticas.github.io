<?php
$basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
$meses    = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
[$anioF, $nmesF] = explode('-', $filtroMes);
$mesLabel = $meses[(int)$nmesF - 1] . ' ' . $anioF;

$estadoInfo = [
    'A TIEMPO' => ['bg'=>'#d1fae5','color'=>'#065f46','label'=>'A tiempo'],
    'TARDE'    => ['bg'=>'#fef3c7','color'=>'#92400e','label'=>'Tarde'],
    'EXTRA'    => ['bg'=>'#eff6ff','color'=>'#1e40af','label'=>'Extra'],
    'TEMPRANO' => ['bg'=>'#f0fdfe','color'=>'#0e7490','label'=>'Temprano'],
    'FALTA'    => ['bg'=>'#fee2e2','color'=>'#991b1b','label'=>'Falta'],
];

$totalManana = 0; $totalTarde = 0;
foreach ($slots as $s) { if ($s['turno_id'] == 1) $totalManana++; else $totalTarde++; }

$fechas = array_unique(array_merge(array_column($slots, 'fecha_dia'), array_keys($asistPorFecha)));
sort($fechas);

$diasLabel  = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
$turnoLabel = [1 => '☀️ Mañana', 2 => '🌙 Tarde'];

$slotsPorFechaTurno = [];
foreach ($slots as $s) { $slotsPorFechaTurno[$s['fecha_dia']][$s['turno_id']] = $s; }

$rempPorFecha = [];
foreach ($reemplazos as $r) { $rempPorFecha[$r['fecha_dia']][$r['turno_id']] = $r; }

$conAsistencia = count(array_filter($asistencias, fn($a) => $a['hora_ingreso']));
$sinRegistro   = max(0, count($slots) - $conAsistencia);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Horario mensual | Solo Boticas</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/staff.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .mh-wrap  { max-width:1040px;margin:0 auto;padding:1.25rem 1rem 3rem; }
        .mh-top   { display:flex;flex-wrap:wrap;gap:.75rem;align-items:flex-end;margin-bottom:1.25rem; }
        .mh-top label { font-size:.75rem;font-weight:700;color:#64748b;display:block;margin-bottom:.2rem; }
        .mh-top select, .mh-top input { padding:.4rem .75rem;border:1.5px solid #e2e8f0;border-radius:8px;font-size:.85rem;outline:none; }
        .mh-top select:focus, .mh-top input:focus { border-color:#0097A7; }
        .mh-top button { padding:.4rem 1.1rem;background:#0097A7;color:#fff;border:none;border-radius:8px;font-size:.82rem;font-weight:700;cursor:pointer; }

        .mh-kpis { display:grid;grid-template-columns:repeat(4,1fr);gap:.75rem;margin-bottom:1.25rem; }
        .mh-kpi  { background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:.75rem 1rem;text-align:center; }
        .mh-kpi__num   { font-size:1.5rem;font-weight:800; }
        .mh-kpi__label { font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#64748b; }

        .mh-table-wrap { overflow-x:auto; }
        .mh-table { width:100%;border-collapse:collapse;font-size:.80rem;background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.06); }
        .mh-table th { background:#f8fafc;padding:.5rem .7rem;font-size:.66rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#64748b;border-bottom:2px solid #e2e8f0;white-space:nowrap; }
        .mh-table td { padding:.5rem .7rem;border-bottom:1px solid #f1f5f9;vertical-align:middle; }
        .mh-table tr:last-child td { border-bottom:none; }
        .mh-table tr:hover td { background:#fafafa; }
        .mh-badge { display:inline-block;font-size:.68rem;font-weight:700;padding:2px 8px;border-radius:20px;white-space:nowrap; }
        .mh-time  { font-variant-numeric:tabular-nums;font-weight:600;color:#1e293b; }
        .mh-sub   { font-size:.68rem;color:#94a3b8;display:block; }
        .mh-reg-btn { background:#0097A7;color:#fff;border:none;border-radius:6px;padding:3px 10px;font-size:.72rem;font-weight:700;cursor:pointer; }
        .mh-chk   { display:flex;flex-wrap:wrap;gap:3px; }
        .mh-chk-i { width:15px;height:15px;border-radius:3px;display:flex;align-items:center;justify-content:center;font-size:.58rem;font-weight:700; }
        .mh-chk-i--ok   { background:#d1fae5;color:#059669; }
        .mh-chk-i--fail { background:#fee2e2;color:#dc2626; }

        /* Modal */
        .mh-ov { position:fixed;inset:0;background:rgba(15,23,42,.5);z-index:500;display:flex;align-items:center;justify-content:center; }
        .mh-ov[hidden] { display:none!important; }
        .mh-modal { background:#fff;border-radius:14px;padding:1.5rem;width:420px;max-width:94vw;box-shadow:0 20px 60px rgba(0,0,0,.22);max-height:90vh;overflow-y:auto; }
        .mh-modal h3 { font-size:1rem;font-weight:700;margin-bottom:.75rem;color:#1e293b; }
        .mh-modal p  { font-size:.78rem;color:#64748b;margin-bottom:.75rem;line-height:1.5; }
        .mh-fl label { font-size:.75rem;font-weight:600;color:#475569;display:block;margin-bottom:.2rem; }
        .mh-fl input, .mh-fl select { width:100%;padding:.5rem .7rem;border:1.5px solid #e2e8f0;border-radius:8px;font-size:.85rem;margin-bottom:.6rem;box-sizing:border-box;outline:none; }
        .mh-fl input:focus { border-color:#0097A7; }
        .mh-chk-form { display:flex;flex-direction:column;gap:.4rem;margin-bottom:.75rem; }
        .mh-chk-row  { display:flex;align-items:center;gap:.6rem;font-size:.82rem;color:#334155;padding:.3rem .5rem;border-radius:6px;background:#f8fafc; }
        .mh-chk-row input[type=checkbox] { width:16px;height:16px;cursor:pointer;accent-color:#0097A7; }
        .mh-chk-sep  { font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;margin:.25rem 0; }
        .mh-modal__footer { display:flex;gap:.5rem;justify-content:flex-end;margin-top:.25rem; }
        .mh-err { font-size:.75rem;color:#dc2626;margin-bottom:.5rem;display:none; }

        @media(max-width:640px){ .mh-kpis{grid-template-columns:repeat(2,1fr);} }
    </style>
</head>
<body style="background:#f1f5f9;min-height:100vh;">

<header class="staff-header">
    <div class="staff-header__brand">
        <div class="staff-header__logo">SB</div>
        <div>
            <p class="staff-header__company">Grupo KGyR S.A.C</p>
            <p class="staff-header__app">Horario de <strong><?= htmlspecialchars($nombreTrabajador) ?></strong></p>
        </div>
    </div>
    <div class="staff-header__user">
        <p class="staff-header__name"><?= htmlspecialchars($userName) ?></p>
        <a href="<?= $basePath ?>/staff" class="staff-btn-logout" style="font-size:.78rem;">← Volver</a>
    </div>
</header>

<!-- Modal revertir falta -->
<div id="mhModalRevertir" class="mh-ov" hidden>
    <div class="mh-modal" style="max-height:unset;">
        <h3>Revertir falta</h3>
        <p id="mhRevertirDesc"></p>
        <div id="mhRevertirErr" class="mh-err"></div>
        <input type="hidden" id="mhRevertirId">
        <div class="mh-fl">
            <label>Tu contraseña para confirmar</label>
            <input type="password" id="mhRevertirPwd" placeholder="Tu contraseña de acceso">
        </div>
        <div class="mh-modal__footer">
            <button onclick="cerrarModalRevertir()"
                style="background:#f1f5f9;border:none;border-radius:7px;padding:.5rem 1rem;font-size:.82rem;cursor:pointer;color:#475569;">
                Cancelar
            </button>
            <button onclick="confirmarRevertir()"
                style="background:#dc2626;border:none;border-radius:7px;padding:.5rem 1.25rem;font-size:.82rem;font-weight:700;color:#fff;cursor:pointer;">
                Revertir
            </button>
        </div>
    </div>
</div>

<!-- Modal registrar asistencia -->
<div id="mhModal" class="mh-ov" hidden>
    <div class="mh-modal">
        <h3 id="mhModalTitulo">Registrar asistencia</h3>
        <p id="mhModalDesc"></p>
        <div id="mhErr" class="mh-err"></div>
        <input type="hidden" id="mhPid">
        <input type="hidden" id="mhFecha">
        <input type="hidden" id="mhLocalId">
        <!-- Sección normal (visible si no es falta) -->
        <div id="mhSeccionHoras">
            <div class="mh-fl">
                <label>Hora de entrada *</label>
                <input type="time" id="mhIngreso">
                <label>Hora de salida <span style="color:#94a3b8;font-weight:400;">(si ya salió)</span></label>
                <input type="time" id="mhSalida">
            </div>
            <!-- Checklist APERTURA -->
            <p class="mh-chk-sep">Verificación de apertura (entrada)</p>
            <div class="mh-chk-form" id="chkApertura"></div>
            <!-- Checklist CIERRE -->
            <p class="mh-chk-sep">Verificación de cierre (salida)</p>
            <div class="mh-chk-form" id="chkCierre"></div>
        </div>

        <!-- Aviso falta -->
        <div id="mhSeccionFalta" hidden
             style="background:#fee2e2;border-radius:8px;padding:.75rem 1rem;margin-bottom:.75rem;font-size:.82rem;color:#991b1b;font-weight:600;">
            ⚠ Se registrará que este trabajador <strong>no se presentó</strong> a su turno.
        </div>

        <div class="mh-fl" style="border-top:1px solid #e2e8f0;margin-top:.25rem;padding-top:.75rem;">
            <label>Tu contraseña (para confirmar el registro) *</label>
            <input type="password" id="mhPassword" placeholder="Tu contraseña de acceso">
        </div>
        <div class="mh-modal__footer">
            <button onclick="cerrarModal()"
                style="background:#f1f5f9;border:none;border-radius:7px;padding:.5rem 1rem;font-size:.82rem;cursor:pointer;color:#475569;">
                Cancelar
            </button>
            <button onclick="confirmarRegistro()"
                style="background:#0097A7;border:none;border-radius:7px;padding:.5rem 1.25rem;font-size:.82rem;font-weight:700;color:#fff;cursor:pointer;">
                Actualizar
            </button>
        </div>
    </div>
</div>

<main class="mh-wrap">

    <!-- Filtros: trabajador + mes -->
    <form method="GET" class="mh-top">
        <div>
            <label>Ver horario de:</label>
            <select name="trabajador">
                <?php foreach ($trabajadores as $t): ?>
                    <option value="<?= $t['id'] ?>" <?= $t['id'] == $postulanteId ? 'selected' : '' ?>>
                        <?= htmlspecialchars($t['nombre']) ?><?= $t['id'] == $registradorId ? ' (Yo)' : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label>Mes:</label>
            <input type="month" name="mes" value="<?= htmlspecialchars($filtroMes) ?>">
        </div>
        <button type="submit" style="align-self:flex-end;">Ver</button>
    </form>

    <?php if ($esPropioHorario): ?>
    <div style="background:#fef9c3;border:1px solid #fbbf24;border-radius:8px;padding:.7rem 1rem;margin-bottom:1rem;font-size:.82rem;color:#92400e;">
        ⚠ Estás viendo tu propio horario. <strong>Solo un compañero puede registrar tu asistencia.</strong>
        Selecciona a alguien del listado para registrar la de ellos.
    </div>
    <?php endif; ?>

    <!-- KPIs -->
    <div class="mh-kpis">
        <div class="mh-kpi"><div class="mh-kpi__num" style="color:#0097A7;"><?= $totalManana ?></div><div class="mh-kpi__label">Turnos Mañana ☀️</div></div>
        <div class="mh-kpi"><div class="mh-kpi__num" style="color:#475569;"><?= $totalTarde ?></div><div class="mh-kpi__label">Turnos Tarde 🌙</div></div>
        <div class="mh-kpi"><div class="mh-kpi__num" style="color:#059669;"><?= $conAsistencia ?></div><div class="mh-kpi__label">Con asistencia</div></div>
        <div class="mh-kpi"><div class="mh-kpi__num" style="color:#dc2626;"><?= $sinRegistro ?></div><div class="mh-kpi__label">Sin registro</div></div>
    </div>

    <!-- Tabla -->
    <?php if (empty($fechas)): ?>
    <div style="text-align:center;padding:3rem;color:#94a3b8;">
        <div style="font-size:2.5rem;margin-bottom:.5rem;">📅</div>
        <p style="font-weight:600;">Sin actividad en <?= htmlspecialchars($mesLabel) ?></p>
    </div>
    <?php else: ?>
    <div class="mh-table-wrap">
    <table class="mh-table">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Turno</th>
                <th>Local · Rol</th>
                <th>Cobertura</th>
                <th>Entrada</th>
                <th>Salida</th>
                <th>Horas</th>
                <th>Estado</th>
                <th>Registrado por</th>
                <th>Checklist</th>
                <?php if (!$esPropioHorario): ?><th></th><?php endif; ?>
            </tr>
        </thead>
        <tbody>
        <?php
        $asistPorTurnoIdx = [];
        foreach ($asistencias as $idx => $a) {
            $t = ($idx % 2 === 0) ? 1 : 2;
            $asistPorTurnoIdx[$a['fecha']][$t] = $a;
        }

        foreach ($fechas as $fecha):
            $dow      = $diasLabel[(int)date('w', strtotime($fecha))];
            $diaN     = date('d/m', strtotime($fecha));
            $slotsDia = $slotsPorFechaTurno[$fecha] ?? [];
            $rempDia  = $rempPorFecha[$fecha]       ?? [];
            $asistDia = $asistPorFecha[$fecha]       ?? [];

            // Asociar asistencia a turno por posición
            $asistTurno = [];
            foreach ($asistDia as $i => $a) {
                $asistTurno[$i === 0 ? 1 : 2] = $a;
            }

            foreach ($slotsDia as $turnoId => $slot):
                $asist  = $asistTurno[$turnoId] ?? null;
                $remp   = $rempDia[$turnoId]    ?? null;
                $chk    = $asist ? ($checklists[$asist['id_asistencia']] ?? []) : [];
                $estado = $asist['estado'] ?? null;
                $ecfg   = $estado ? ($estadoInfo[$estado] ?? ['bg'=>'#f1f5f9','color'=>'#64748b','label'=>$estado]) : null;
                $rowBg  = $remp ? '#fff8f8' : ($slot['cubre_id'] ? '#f0fff4' : '#fff');

                // Calcular horas trabajadas
                $horas = '';
                if ($asist && $asist['hora_ingreso'] && $asist['hora_salida']) {
                    $diff = (strtotime($asist['hora_salida']) - strtotime($asist['hora_ingreso'])) / 3600;
                    $horas = number_format($diff, 1) . ' h';
                }
        ?>
            <tr style="background:<?= $rowBg ?>">
                <td style="white-space:nowrap;">
                    <strong style="color:#1e293b;"><?= $dow ?></strong>
                    <span class="mh-sub"><?= $diaN ?></span>
                </td>
                <td>
                    <span class="mh-badge" style="background:<?= $turnoId==1 ? '#fef9c3':'#e0e7ff' ?>;color:<?= $turnoId==1 ? '#92400e':'#3730a3' ?>">
                        <?= $turnoLabel[$turnoId] ?? $turnoId ?>
                    </span>
                </td>
                <td>
                    <span style="font-weight:600;color:#1e293b;"><?= htmlspecialchars($slot['local_desc']) ?></span>
                    <span class="mh-sub"><?= htmlspecialchars($slot['rol_desc']) ?></span>
                </td>
                <td style="font-size:.72rem;">
                    <?php if ($remp): ?>
                        <span style="color:#dc2626;">↩ <?= htmlspecialchars($remp['reemplazado_por']) ?></span>
                    <?php elseif ($slot['cubre_id']): ?>
                        <span style="color:#059669;">✔ <?= htmlspecialchars($slot['cubrió_a'] ?? '') ?></span>
                    <?php else: ?>
                        <span style="color:#cbd5e1;">—</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($asist && $asist['hora_ingreso']): ?>
                        <span class="mh-time"><?= date('H:i', strtotime($asist['hora_ingreso'])) ?></span>
                    <?php else: ?>
                        <span style="color:#cbd5e1;">—</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($asist && $asist['hora_salida']): ?>
                        <span class="mh-time"><?= date('H:i', strtotime($asist['hora_salida'])) ?></span>
                    <?php elseif ($asist): ?>
                        <span style="color:#f59e0b;font-size:.75rem;">En turno</span>
                    <?php else: ?>
                        <span style="color:#cbd5e1;">—</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($horas): ?>
                        <span style="font-weight:600;color:#1e293b;"><?= $horas ?></span>
                    <?php else: ?>
                        <span style="color:#cbd5e1;">—</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($ecfg): ?>
                        <span class="mh-badge" style="background:<?= $ecfg['bg'] ?>;color:<?= $ecfg['color'] ?>"><?= $ecfg['label'] ?></span>
                    <?php else: ?>
                        <span style="color:#cbd5e1;font-size:.75rem;">Sin reg.</span>
                    <?php endif; ?>
                </td>
                <td style="font-size:.72rem;">
                    <?php if ($asist && $asist['registrado_por_nombre']): ?>
                        <span style="color:#0097A7;font-weight:600;"><?= htmlspecialchars($asist['registrado_por_nombre']) ?></span>
                    <?php else: ?>
                        <span style="color:#cbd5e1;">—</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (!empty($chk)): ?>
                    <div class="mh-chk" title="<?= implode(' · ', array_map(fn($c) => ($c['cumplido']?'✓':'✗').' '.$c['descripcion'], $chk)) ?>">
                        <?php foreach ($chk as $c): ?>
                            <div class="mh-chk-i <?= $c['cumplido'] ? 'mh-chk-i--ok' : 'mh-chk-i--fail' ?>"><?= $c['cumplido'] ? '✓' : '✗' ?></div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                        <span style="color:#cbd5e1;font-size:.75rem;">—</span>
                    <?php endif; ?>
                </td>
                <?php if (!$esPropioHorario): ?>
                <td style="display:flex;gap:.3rem;flex-wrap:wrap;align-items:center;">
                    <?php $esFaltaReg = $asist && $asist['estado'] === 'FALTA'; ?>

                    <?php if (!$remp || $asist): ?>
                    <?php
                        $ingresoExist = ($asist && $asist['hora_ingreso']) ? date('H:i', strtotime($asist['hora_ingreso'])) : '';
                        $salidaExist  = ($asist && $asist['hora_salida'])  ? date('H:i', strtotime($asist['hora_salida']))  : '';
                        $chkExist     = [];
                        foreach ($chk as $c) { $chkExist[$c['checklist_id']] = (int)$c['cumplido']; }
                    ?>
                    <button class="mh-reg-btn"
                        data-pid="<?= $postulanteId ?>"
                        data-fecha="<?= $fecha ?>"
                        data-nombre="<?= htmlspecialchars($nombreTrabajador, ENT_QUOTES) ?>"
                        data-local="<?= $slot['local_id'] ?? '' ?>"
                        data-ingreso="<?= $ingresoExist ?>"
                        data-salida="<?= $salidaExist ?>"
                        data-chk='<?= htmlspecialchars(json_encode($chkExist), ENT_QUOTES) ?>'
                        onclick="abrirModalFromBtn(this, false)">
                        <?= $asist && !$esFaltaReg ? '✏ Actualizar' : 'Actualizar' ?>
                    </button>
                    <?php endif; ?>

                    <button class="mh-reg-btn" style="background:#dc2626;"
                        data-pid="<?= $postulanteId ?>"
                        data-fecha="<?= $fecha ?>"
                        data-nombre="<?= htmlspecialchars($nombreTrabajador, ENT_QUOTES) ?>"
                        data-local="<?= $slot['local_id'] ?? '' ?>"
                        data-ingreso="" data-salida="" data-chk="{}"
                        onclick="abrirModalFromBtn(this, true)">
                        Faltó
                    </button>

                    <?php if ($esFaltaReg): ?>
                    <button onclick="abrirModalRevertirFalta(<?= $asist['id_asistencia'] ?>, '<?= addslashes($nombreTrabajador) ?>')"
                        style="background:transparent;border:1px solid #fca5a5;color:#dc2626;border-radius:6px;padding:3px 9px;font-size:.72rem;font-weight:700;cursor:pointer;white-space:nowrap;">
                        ↩ Revertir
                    </button>
                    <?php endif; ?>
                </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</main>

<script>
const BASE       = '<?= $basePath ?>';
const CHECKLIST  = <?= json_encode($checklistItems ?? []) ?>;

let _esFalta = false;

function abrirModalFromBtn(btn, esFalta) {
    const chkExist = JSON.parse(btn.dataset.chk || '{}');
    abrirModal(
        parseInt(btn.dataset.pid),
        btn.dataset.fecha,
        btn.dataset.nombre,
        btn.dataset.local || null,
        esFalta,
        btn.dataset.ingreso || '',
        btn.dataset.salida  || '',
        chkExist
    );
}

function abrirModalRevertirFalta(id, nombre) {
    document.getElementById('mhRevertirId').value = id;
    document.getElementById('mhRevertirDesc').textContent =
        `Se eliminará el registro de falta de "${nombre}". El turno volverá a "Sin registro".`;
    document.getElementById('mhRevertirPwd').value = '';
    document.getElementById('mhRevertirErr').style.display = 'none';
    document.getElementById('mhModalRevertir').removeAttribute('hidden');
    setTimeout(() => document.getElementById('mhRevertirPwd').focus(), 50);
}

function cerrarModalRevertir() {
    document.getElementById('mhModalRevertir').setAttribute('hidden', '');
}

async function confirmarRevertir() {
    const id  = document.getElementById('mhRevertirId').value;
    const pwd = document.getElementById('mhRevertirPwd').value.trim();
    const err = document.getElementById('mhRevertirErr');
    if (!pwd) { err.textContent = 'Tu contraseña es requerida.'; err.style.display = 'block'; return; }
    try {
        const r   = await fetch(`${BASE}/staff/api/asistencia/${id}/revertir`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ password: pwd }),
        });
        const res = await r.json();
        if (res.success) { cerrarModalRevertir(); location.reload(); }
        else { err.textContent = res.message || 'Error.'; err.style.display = 'block'; }
    } catch { err.textContent = 'Error de conexión.'; err.style.display = 'block'; }
}

function abrirModal(pid, fecha, nombre, localId, esFalta, ingresoExist = '', salidaExist = '', chkExist = {}) {
    _esFalta = !!esFalta;
    document.getElementById('mhPid').value      = pid;
    document.getElementById('mhFecha').value    = fecha;
    document.getElementById('mhLocalId').value  = localId || '';
    document.getElementById('mhIngreso').value  = ingresoExist;
    document.getElementById('mhSalida').value   = salidaExist;
    document.getElementById('mhPassword').value = '';
    document.getElementById('mhErr').style.display = 'none';

    const d    = new Date(fecha + 'T12:00:00');
    const dias = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
    const fechaLabel = `${dias[d.getDay()]} ${d.getDate()}/${String(d.getMonth()+1).padStart(2,'0')}`;

    document.getElementById('mhModalTitulo').textContent = esFalta
        ? `Registrar falta — ${nombre}`
        : `Registrar asistencia — ${nombre}`;
    document.getElementById('mhModalDesc').textContent =
        `${fechaLabel} · Confirma con TU propia contraseña.`;

    // Mostrar/ocultar secciones
    document.getElementById('mhSeccionHoras').hidden  = !!esFalta;
    document.getElementById('mhSeccionFalta').hidden  = !esFalta;

    if (!esFalta) {
        const apertura = CHECKLIST.filter(c => c.tipo === 'APERTURA');
        const cierre   = CHECKLIST.filter(c => c.tipo === 'CIERRE');
        const hasExisting = Object.keys(chkExist).length > 0;
        const build = (items, id) => {
            document.getElementById(id).innerHTML = items.map(c => {
                // Si hay datos existentes, usar su valor; si no, marcar por defecto
                const checked = hasExisting
                    ? (chkExist[c.id_checklist] === 1)
                    : true;
                return `<label class="mh-chk-row">
                    <input type="checkbox" name="chk" data-id="${c.id_checklist}" ${checked ? 'checked' : ''}>
                    ${c.descripcion}
                </label>`;
            }).join('');
        };
        build(apertura, 'chkApertura');
        build(cierre,   'chkCierre');
    }

    document.getElementById('mhModal').removeAttribute('hidden');
    setTimeout(() => document.getElementById(_esFalta ? 'mhPassword' : 'mhIngreso').focus(), 50);
}

function cerrarModal() {
    document.getElementById('mhModal').setAttribute('hidden', '');
}

async function confirmarRegistro() {
    const pid      = parseInt(document.getElementById('mhPid').value);
    const fecha    = document.getElementById('mhFecha').value;
    const localId  = document.getElementById('mhLocalId').value;
    const ingreso  = document.getElementById('mhIngreso').value;
    const salida   = document.getElementById('mhSalida').value;
    const password = document.getElementById('mhPassword').value.trim();
    const err      = document.getElementById('mhErr');

    if (!_esFalta && !ingreso) { err.textContent = 'La hora de entrada es requerida.'; err.style.display = 'block'; return; }
    if (!password) { err.textContent = 'Tu contraseña es requerida.'; err.style.display = 'block'; return; }

    const checklist = _esFalta ? [] : Array.from(document.querySelectorAll('input[name=chk]')).map(c => ({
        checklist_id: parseInt(c.dataset.id),
        cumplido:     c.checked ? 1 : 0,
    }));

    try {
        const r   = await fetch(`${BASE}/staff/api/asistencia/registrar`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                postulante_id: pid, fecha,
                hora_ingreso: _esFalta ? null : ingreso,
                hora_salida:  _esFalta ? null : (salida || null),
                local_id: localId ? parseInt(localId) : null,
                checklist, password,
            }),
        });
        const res = await r.json();
        if (res.success) { cerrarModal(); location.reload(); }
        else { err.textContent = res.message || 'Error.'; err.style.display = 'block'; }
    } catch { err.textContent = 'Error de conexión.'; err.style.display = 'block'; }
}
</script>
</body>
</html>
