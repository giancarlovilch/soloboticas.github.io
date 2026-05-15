<?php
$basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
$modo     = $modo ?? 'pendientes';

// ── Variables modo "mis-encuestas" ────────────────────
if ($modo === 'mis-encuestas') {
    $mesesNomCompleto = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
    $mesLabel     = $mesesNomCompleto[(int)date('n', strtotime($desde)) - 1] . ' ' . date('Y', strtotime($desde));
    $mesPasado    = date('Y-m', strtotime($desde . ' -1 month'));
    $mesSiguiente = date('Y-m', strtotime($desde . ' +1 month'));
    $mesActual    = date('Y-m');
    $diasLabel    = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
    $turnoLabel   = [1 => '☀️ Mañana', 2 => '🌙 Tarde'];

    $puntInfo = [
        'MUY_TEMPRANO' => ['label'=>'+10 min antes', 'bg'=>'#eff6ff','color'=>'#1e40af'],
        'TEMPRANO'     => ['label'=>'Temprano',       'bg'=>'#f0fdfe','color'=>'#0e7490'],
        'TARDE'        => ['label'=>'Tarde',           'bg'=>'#fef3c7','color'=>'#92400e'],
        'MUY_TARDE'    => ['label'=>'+10 min tarde',   'bg'=>'#fee2e2','color'=>'#991b1b'],
    ];

    $slotsPorFechaTurno = [];
    foreach ($slots as $s) { $slotsPorFechaTurno[$s['fecha_dia']][$s['turno_id']] = $s; }

    $fechas = array_unique(array_merge(
        array_keys($slotsPorFechaTurno),
        array_keys($asistPorFecha)
    ));
    sort($fechas);

    $totalManana = 0; $totalTarde = 0;
    foreach ($slots as $s) { if ($s['turno_id'] == 1) $totalManana++; else $totalTarde++; }

    $conFicha = 0; $conFalta = 0;
    foreach ($slots as $s) {
        $a = $asistPorFecha[$s['fecha_dia']][$s['turno_id']] ?? $asistPorFecha[$s['fecha_dia']][0] ?? null;
        if ($a && $a['estado'] === 'FALTA') $conFalta++;
        elseif ($a) $conFicha++;
    }
    $sinRegistro = count($slots) - $conFicha - $conFalta;
}

// ── Variables modo "pendientes" ───────────────────────
if ($modo === 'pendientes') {
    $slotsData        = $slotsData        ?? [];
    $desde            = $desde            ?? date('Y-m-01');
    $hasta            = $hasta            ?? date('Y-m-d');
    $filtroTrabajador = $filtroTrabajador ?? 0;
    $soloSinCalif     = $soloSinCalif     ?? true;

    $sinFicha = count(array_filter($slotsData, fn($s) => !$s['id_asistencia']));
    $total    = count($slotsData);

    $puntInfo = [
        'MUY_TEMPRANO' => ['label'=>'+10 min antes', 'bg'=>'#eff6ff','color'=>'#1e40af'],
        'TEMPRANO'     => ['label'=>'Temprano',       'bg'=>'#f0fdfe','color'=>'#0e7490'],
        'TARDE'        => ['label'=>'Tarde',           'bg'=>'#fef3c7','color'=>'#92400e'],
        'MUY_TARDE'    => ['label'=>'+10 min tarde',   'bg'=>'#fee2e2','color'=>'#991b1b'],
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $modo === 'mis-encuestas' ? 'Mis encuestas' : 'Encuestas pendientes' ?> | Solo Boticas</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/staff.css">
    <style>
        .mh-wrap  { max-width:1060px;margin:0 auto;padding:1.25rem 1rem 3rem; }
        .mh-table-wrap { overflow-x:auto; }
        .mh-table { width:100%;border-collapse:collapse;font-size:.80rem;background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.06); }
        .mh-table th { background:#f8fafc;padding:.5rem .7rem;font-size:.66rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#64748b;border-bottom:2px solid #e2e8f0;white-space:nowrap; }
        .mh-table td { padding:.5rem .7rem;border-bottom:1px solid #f1f5f9;vertical-align:middle; }
        .mh-table tr:last-child td { border-bottom:none; }
        .mh-table tr:hover td { background:#fafafa; }
        .mh-badge { display:inline-block;font-size:.68rem;font-weight:700;padding:2px 8px;border-radius:20px;white-space:nowrap; }
        .mh-sub   { font-size:.68rem;color:#94a3b8;display:block; }

        .mh-kpis { display:grid;grid-template-columns:repeat(4,1fr);gap:.75rem;margin-bottom:1.25rem; }
        .mh-kpi  { background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:.75rem 1rem;text-align:center; }
        .mh-kpi__num   { font-size:1.5rem;font-weight:800; }
        .mh-kpi__label { font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#64748b; }

        /* Botones de acción en tabla */
        .mh-acc { display:flex;gap:.3rem;flex-wrap:wrap;align-items:center; }
        .mh-btn { border:none;border-radius:6px;padding:4px 10px;font-size:.72rem;font-weight:700;cursor:pointer;white-space:nowrap; }
        .mh-btn--entrada  { background:#0097A7;color:#fff; }
        .mh-btn--salida   { background:#7c3aed;color:#fff; }
        .mh-btn--falta    { background:#dc2626;color:#fff; }
        .mh-btn--revertir { background:transparent;border:1px solid #fca5a5 !important;color:#dc2626; }
        .mh-btn--filled   { opacity:.75; }

        /* Selector de modo */
        .mh-mode-bar { display:flex;gap:.5rem;margin-bottom:1.25rem;border-bottom:2px solid #e2e8f0;padding-bottom:.75rem; }
        .mh-mode-btn { padding:.45rem 1.1rem;border-radius:8px 8px 0 0;font-size:.82rem;font-weight:700;text-decoration:none;color:#64748b;border:1.5px solid transparent;transition:all .15s; }
        .mh-mode-btn--active { border-color:#0097A7;background:#f0fdfe;color:#0097A7; }

        /* Filtros */
        .mh-filtros { display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;margin-bottom:1rem; }
        .mh-filtros input, .mh-filtros select { padding:.4rem .7rem;border:1.5px solid #e2e8f0;border-radius:8px;font-size:.82rem;outline:none; }
        .mh-filtros input:focus, .mh-filtros select:focus { border-color:#0097A7; }

        /* Modal overlay */
        .mh-ov { position:fixed;inset:0;background:rgba(15,23,42,.5);z-index:500;display:flex;align-items:center;justify-content:center; }
        .mh-ov[hidden] { display:none!important; }
        .mh-modal { background:#fff;border-radius:14px;padding:1.5rem;width:480px;max-width:96vw;box-shadow:0 20px 60px rgba(0,0,0,.22);max-height:92vh;overflow-y:auto; }
        .mh-modal h3 { font-size:1rem;font-weight:700;margin:0 0 .25rem;color:#1e293b; }
        .mh-modal-sub { font-size:.75rem;color:#64748b;margin-bottom:1rem; }
        .mh-err { font-size:.75rem;color:#dc2626;margin-bottom:.5rem;display:none; }
        .mh-modal__footer { display:flex;gap:.5rem;justify-content:flex-end;margin-top:.75rem; }
        .mh-modal__footer button { border:none;border-radius:7px;padding:.5rem 1.1rem;font-size:.82rem;font-weight:700;cursor:pointer; }

        /* Radio button groups */
        .mh-field { margin-bottom:.7rem; }
        .mh-field__label { font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#64748b;display:block;margin-bottom:.3rem; }
        .mh-rg { display:flex;gap:.3rem;flex-wrap:wrap; }
        .mh-rb { padding:.38rem .75rem;border:1.5px solid #e2e8f0;border-radius:8px;font-size:.78rem;font-weight:600;cursor:pointer;background:#fff;color:#475569;transition:all .13s;line-height:1.3;text-align:center; }
        .mh-rb small { display:block;font-size:.62rem;font-weight:400;color:#94a3b8;margin-top:1px; }
        .mh-rb.active { border-color:#0097A7;background:#f0fdfe;color:#0e7490; }

        /* Bloques de sección */
        .mh-block { background:#f8fafc;border-radius:10px;padding:.7rem .85rem;margin-bottom:.65rem;border:1px solid #e8edf2; }
        .mh-block__hd { font-size:.67rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#64748b;margin-bottom:.55rem;display:flex;align-items:center;gap:.35rem; }
        .mh-row-2 { display:grid;grid-template-columns:1fr 1fr;gap:.5rem; }

        /* Variantes de color para botones (data-color) */
        .mh-rb[data-color="blue"]   { border-color:#bfdbfe;color:#3b82f6; }
        .mh-rb[data-color="green"]  { border-color:#a7f3d0;color:#10b981; }
        .mh-rb[data-color="amber"]  { border-color:#fde68a;color:#d97706; }
        .mh-rb[data-color="orange"] { border-color:#fed7aa;color:#f97316; }
        .mh-rb[data-color="red"]    { border-color:#fecaca;color:#ef4444; }
        .mh-rb[data-color="purple"] { border-color:#ddd6fe;color:#8b5cf6; }
        .mh-rb[data-color="blue"].active   { border-color:#3b82f6;background:#dbeafe;color:#1e40af; }
        .mh-rb[data-color="green"].active  { border-color:#10b981;background:#d1fae5;color:#065f46; }
        .mh-rb[data-color="amber"].active  { border-color:#f59e0b;background:#fef3c7;color:#92400e; }
        .mh-rb[data-color="orange"].active { border-color:#f97316;background:#ffedd5;color:#9a3412; }
        .mh-rb[data-color="red"].active    { border-color:#ef4444;background:#fee2e2;color:#991b1b; }
        .mh-rb[data-color="purple"].active { border-color:#8b5cf6;background:#ede9fe;color:#5b21b6; }

        .mh-pwd { display:flex;flex-direction:column;gap:.2rem;margin-top:.75rem;border-top:1px solid #f1f5f9;padding-top:.75rem; }
        .mh-pwd label { font-size:.75rem;font-weight:600;color:#475569; }
        .mh-pwd input { padding:.5rem .75rem;border:1.5px solid #e2e8f0;border-radius:8px;font-size:.85rem;outline:none;width:100%;box-sizing:border-box; }
        textarea.mh-textarea { width:100%;padding:.5rem .75rem;border:1.5px solid #e2e8f0;border-radius:8px;font-size:.82rem;resize:vertical;min-height:60px;outline:none;box-sizing:border-box;font-family:inherit; }

        @media(max-width:640px){ .mh-kpis{grid-template-columns:repeat(2,1fr);} }
    </style>
</head>
<body style="background:#f1f5f9;min-height:100vh;">

<header class="staff-header">
    <div class="staff-header__brand">
        <div class="staff-header__logo">SB</div>
        <div>
            <p class="staff-header__company">Grupo KGyR S.A.C</p>
            <p class="staff-header__app">
                <?= $modo === 'mis-encuestas' ? 'Mis encuestas' : 'Encuestas del equipo' ?>
            </p>
        </div>
    </div>
    <div class="staff-header__user">
        <p class="staff-header__name"><?= htmlspecialchars($userName) ?></p>
        <a href="<?= $basePath ?>/staff" class="staff-btn-logout" style="font-size:.78rem;">← Volver</a>
    </div>
</header>

<?php if ($modo !== 'mis-encuestas'): ?>
<!-- ── Modales (solo en modo pendientes) ──────────────── -->

<!-- Modal revertir falta -->
<div id="mhModalRevertir" class="mh-ov" hidden>
    <div class="mh-modal" style="max-width:360px;">
        <h3>Revertir falta</h3>
        <p id="mhRevertirDesc" class="mh-modal-sub"></p>
        <div id="mhRevertirErr" class="mh-err"></div>
        <input type="hidden" id="mhRevertirId">
        <div class="mh-pwd">
            <label>Tu contraseña para confirmar</label>
            <input type="password" id="mhRevertirPwd" placeholder="Tu contraseña de acceso">
        </div>
        <div class="mh-modal__footer">
            <button onclick="cerrarModalRevertir()" style="background:#f1f5f9;color:#475569;">Cancelar</button>
            <button onclick="confirmarRevertir()" style="background:#dc2626;color:#fff;">Revertir</button>
        </div>
    </div>
</div>

<!-- Modal falta -->
<div id="mhModalFalta" class="mh-ov" hidden>
    <div class="mh-modal" style="max-width:360px;">
        <h3>Registrar falta</h3>
        <p id="mhFaltaDesc" class="mh-modal-sub"></p>
        <div style="background:#fee2e2;border-radius:8px;padding:.7rem 1rem;margin-bottom:.75rem;font-size:.82rem;color:#991b1b;font-weight:600;">
            ⚠ Se registrará que este trabajador no se presentó a su turno.
        </div>
        <div id="mhFaltaErr" class="mh-err"></div>
        <div class="mh-pwd">
            <label>Tu contraseña *</label>
            <input type="password" id="mhFaltaPwd" placeholder="Tu contraseña de acceso">
        </div>
        <div class="mh-modal__footer">
            <button onclick="cerrarModalFalta()" style="background:#f1f5f9;color:#475569;">Cancelar</button>
            <button onclick="confirmarFalta()" style="background:#dc2626;color:#fff;">Confirmar falta</button>
        </div>
    </div>
</div>

<!-- Modal encuesta -->
<div id="mhModal" class="mh-ov" hidden>
    <div class="mh-modal">
        <h3 id="mhModalTitulo"></h3>
        <p id="mhModalDesc" class="mh-modal-sub"></p>
        <div id="mhErr" class="mh-err"></div>

        <div id="mhSecEntrada">
            <div class="mh-block">
                <div class="mh-block__hd">⏰ Puntualidad al ingreso</div>
                <div class="mh-rg">
                    <button type="button" class="mh-rb" data-color="blue"   data-field="llegada_puntualidad" data-val="MUY_TEMPRANO" onclick="pickRadio(this)">Muy anticipado <small>+10 min antes</small></button>
                    <button type="button" class="mh-rb" data-color="green"  data-field="llegada_puntualidad" data-val="TEMPRANO"     onclick="pickRadio(this)">Con anticipación <small>menos de 10 min</small></button>
                    <button type="button" class="mh-rb" data-color="orange" data-field="llegada_puntualidad" data-val="TARDE"        onclick="pickRadio(this)">Retraso leve <small>menos de 10 min</small></button>
                    <button type="button" class="mh-rb" data-color="red"    data-field="llegada_puntualidad" data-val="MUY_TARDE"    onclick="pickRadio(this)">Retraso considerable <small>+10 min tarde</small></button>
                </div>
            </div>
            <div class="mh-block">
                <div class="mh-block__hd">🏪 Estado del área al ingreso</div>
                <div class="mh-row-2">
                    <div class="mh-field">
                        <span class="mh-field__label">¿El área estaba ordenada?</span>
                        <div class="mh-rg">
                            <button type="button" class="mh-rb" data-color="green" data-field="area_ordenada_ingreso" data-val="1" onclick="pickRadio(this)">Sí</button>
                            <button type="button" class="mh-rb" data-color="red"   data-field="area_ordenada_ingreso" data-val="0" onclick="pickRadio(this)">No</button>
                        </div>
                    </div>
                    <div class="mh-field">
                        <span class="mh-field__label">¿El área estaba limpia?</span>
                        <div class="mh-rg">
                            <button type="button" class="mh-rb" data-color="green" data-field="area_limpia_ingreso" data-val="1" onclick="pickRadio(this)">Sí</button>
                            <button type="button" class="mh-rb" data-color="red"   data-field="area_limpia_ingreso" data-val="0" onclick="pickRadio(this)">No</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mh-block">
                <div class="mh-block__hd">👕 Presentación personal</div>
                <div class="mh-field"><span class="mh-field__label">Higiene personal</span><div class="mh-rg">
                    <button type="button" class="mh-rb" data-color="red"   data-field="aseo_personal" data-val="DEFICIENTE" onclick="pickRadio(this)">Deficiente</button>
                    <button type="button" class="mh-rb" data-color="amber" data-field="aseo_personal" data-val="ACEPTABLE"  onclick="pickRadio(this)">Aceptable</button>
                    <button type="button" class="mh-rb" data-color="green" data-field="aseo_personal" data-val="OPTIMO"     onclick="pickRadio(this)">Óptimo</button>
                </div></div>
                <div class="mh-field"><span class="mh-field__label">Uniforme e indumentaria</span><div class="mh-rg">
                    <button type="button" class="mh-rb" data-color="red"   data-field="vestimenta" data-val="DESCUIDADO"  onclick="pickRadio(this)">Descuidado</button>
                    <button type="button" class="mh-rb" data-color="amber" data-field="vestimenta" data-val="PRESENTABLE" onclick="pickRadio(this)">Presentable</button>
                    <button type="button" class="mh-rb" data-color="green" data-field="vestimenta" data-val="IMPECABLE"   onclick="pickRadio(this)">Impecable</button>
                </div></div>
                <div class="mh-row-2">
                    <div class="mh-field"><span class="mh-field__label">Estado de uñas</span><div class="mh-rg">
                        <button type="button" class="mh-rb" data-color="red"   data-field="unas" data-val="DESCUIDADAS" onclick="pickRadio(this)">Descuidadas</button>
                        <button type="button" class="mh-rb" data-color="amber" data-field="unas" data-val="ACEPTABLES"  onclick="pickRadio(this)">Aceptables</button>
                        <button type="button" class="mh-rb" data-color="green" data-field="unas" data-val="CUIDADAS"    onclick="pickRadio(this)">Cuidadas</button>
                    </div></div>
                    <div class="mh-field"><span class="mh-field__label">Presentación del cabello</span><div class="mh-rg">
                        <button type="button" class="mh-rb" data-color="red"   data-field="cabello" data-val="SUELTO"   onclick="pickRadio(this)">Suelto</button>
                        <button type="button" class="mh-rb" data-color="green" data-field="cabello" data-val="RECOGIDO" onclick="pickRadio(this)">Recogido</button>
                        <button type="button" class="mh-rb" data-color="green" data-field="cabello" data-val="MONO"     onclick="pickRadio(this)">Con moño</button>
                    </div></div>
                </div>
            </div>
        </div>

        <div id="mhSecSalida" hidden>
            <div class="mh-block">
                <div class="mh-block__hd">⏰ Puntualidad al retiro</div>
                <div class="mh-rg">
                    <button type="button" class="mh-rb" data-color="blue"   data-field="salida_puntualidad" data-val="MUY_TEMPRANO" onclick="pickRadio(this)">Muy anticipado <small>+10 min antes</small></button>
                    <button type="button" class="mh-rb" data-color="green"  data-field="salida_puntualidad" data-val="TEMPRANO"     onclick="pickRadio(this)">Con anticipación <small>menos de 10 min</small></button>
                    <button type="button" class="mh-rb" data-color="orange" data-field="salida_puntualidad" data-val="TARDE"        onclick="pickRadio(this)">Retraso leve <small>menos de 10 min</small></button>
                    <button type="button" class="mh-rb" data-color="red"    data-field="salida_puntualidad" data-val="MUY_TARDE"    onclick="pickRadio(this)">Retraso considerable <small>+10 min tarde</small></button>
                </div>
            </div>
            <div class="mh-block">
                <div class="mh-block__hd">🧹 Cierre del turno</div>
                <div class="mh-field"><span class="mh-field__label">Estado del área de trabajo al cierre</span><div class="mh-rg">
                    <button type="button" class="mh-rb" data-color="red"   data-field="estado_area_cierre" data-val="DESCUIDADO"  onclick="pickRadio(this)">Descuidado</button>
                    <button type="button" class="mh-rb" data-color="amber" data-field="estado_area_cierre" data-val="PRESENTABLE" onclick="pickRadio(this)">Presentable</button>
                    <button type="button" class="mh-rb" data-color="green" data-field="estado_area_cierre" data-val="IMPECABLE"   onclick="pickRadio(this)">Impecable</button>
                </div></div>
                <div class="mh-row-2">
                    <div class="mh-field"><span class="mh-field__label">¿Realizó la limpieza de su área?</span><div class="mh-rg">
                        <button type="button" class="mh-rb" data-color="green" data-field="limpieza_area_cierre" data-val="1" onclick="pickRadio(this)">Sí</button>
                        <button type="button" class="mh-rb" data-color="red"   data-field="limpieza_area_cierre" data-val="0" onclick="pickRadio(this)">No</button>
                    </div></div>
                    <div class="mh-field"><span class="mh-field__label">¿Dejó su área ordenada?</span><div class="mh-rg">
                        <button type="button" class="mh-rb" data-color="green" data-field="area_ordenada_cierre" data-val="1" onclick="pickRadio(this)">Sí</button>
                        <button type="button" class="mh-rb" data-color="red"   data-field="area_ordenada_cierre" data-val="0" onclick="pickRadio(this)">No</button>
                    </div></div>
                </div>
                <div class="mh-field"><span class="mh-field__label">¿Participó en la apertura y/o cierre del local?</span><div class="mh-rg">
                    <button type="button" class="mh-rb" data-color="green" data-field="participo_apertura_cierre" data-val="1" onclick="pickRadio(this)">Sí</button>
                    <button type="button" class="mh-rb" data-color="red"   data-field="participo_apertura_cierre" data-val="0" onclick="pickRadio(this)">No</button>
                </div></div>
            </div>
            <div class="mh-block">
                <div class="mh-block__hd">📊 Evaluación del turno</div>
                <div class="mh-field"><span class="mh-field__label">Uso del celular personal durante el turno</span><div class="mh-rg">
                    <button type="button" class="mh-rb" data-color="green"  data-field="uso_celular" data-val="NO_USO"    onclick="pickRadio(this)">No usó el celular</button>
                    <button type="button" class="mh-rb" data-color="amber"  data-field="uso_celular" data-val="OCASIONAL" onclick="pickRadio(this)">Uso ocasional</button>
                    <button type="button" class="mh-rb" data-color="red"    data-field="uso_celular" data-val="FRECUENTE" onclick="pickRadio(this)">Uso frecuente</button>
                </div></div>
                <div class="mh-field"><span class="mh-field__label">Calificación general del turno</span><div class="mh-rg">
                    <button type="button" class="mh-rb" data-color="red"    data-field="calificacion_turno" data-val="MALO"      onclick="pickRadio(this)">Malo</button>
                    <button type="button" class="mh-rb" data-color="orange" data-field="calificacion_turno" data-val="REGULAR"   onclick="pickRadio(this)">Regular</button>
                    <button type="button" class="mh-rb" data-color="green"  data-field="calificacion_turno" data-val="BUENO"     onclick="pickRadio(this)">Bueno</button>
                    <button type="button" class="mh-rb" data-color="purple" data-field="calificacion_turno" data-val="EXCELENTE" onclick="pickRadio(this)">Excelente</button>
                </div></div>
            </div>
        </div>

        <div class="mh-sep">Comentarios</div>
        <textarea id="mhComentarios" class="mh-textarea" maxlength="200" placeholder="Observaciones del turno (máx. 200 caracteres)"></textarea>
        <div class="mh-pwd">
            <label>Tu contraseña para confirmar *</label>
            <input type="password" id="mhPassword" placeholder="Tu contraseña de acceso">
        </div>
        <div class="mh-modal__footer">
            <button onclick="cerrarModal()" style="background:#f1f5f9;color:#475569;">Cancelar</button>
            <button onclick="confirmarRegistro()" style="background:#0097A7;color:#fff;">Actualizar</button>
        </div>
    </div>
</div>
<?php endif; ?>

<main class="mh-wrap">

    <!-- ── Selector de modo ──────────────────────────────── -->
    <div class="mh-mode-bar">
        <a href="?modo=pendientes"
           class="mh-mode-btn <?= $modo === 'pendientes' ? 'mh-mode-btn--active' : '' ?>">
            📋 Encuestas pendientes
        </a>
        <a href="?modo=mis-encuestas&mes=<?= $modo === 'mis-encuestas' ? htmlspecialchars($filtroMes) : date('Y-m') ?>"
           class="mh-mode-btn <?= $modo === 'mis-encuestas' ? 'mh-mode-btn--active' : '' ?>">
            👤 Mis encuestas
        </a>
    </div>

<?php if ($modo === 'pendientes'): ?>
    <!-- ══════════ MODO PENDIENTES ══════════ -->

    <!-- Filtros -->
    <form method="GET" class="mh-filtros">
        <input type="hidden" name="modo" value="pendientes">
        <input type="hidden" name="filtro" value="1">
        <input type="date" name="desde" value="<?= htmlspecialchars($desde) ?>" onchange="this.form.submit()">
        <span style="color:#94a3b8;font-size:.8rem;">hasta</span>
        <input type="date" name="hasta" value="<?= htmlspecialchars($hasta) ?>" onchange="this.form.submit()">
        <select name="trabajador" onchange="this.form.submit()" style="min-width:160px;">
            <option value="0">Todos los compañeros</option>
            <?php foreach ($trabajadores as $t): ?>
                <option value="<?= $t['id'] ?>" <?= $t['id'] == $filtroTrabajador ? 'selected' : '' ?>>
                    <?= htmlspecialchars($t['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <label style="display:flex;align-items:center;gap:.35rem;font-size:.82rem;font-weight:600;color:#475569;cursor:pointer;">
            <input type="checkbox" name="sin_calif" value="1"
                   <?= $soloSinCalif ? 'checked' : '' ?> onchange="this.form.submit()"
                   style="accent-color:#0097A7;width:15px;height:15px;">
            Solo sin calificar
        </label>
    </form>

    <p style="font-size:.75rem;color:#64748b;margin-bottom:.75rem;">
        <?= $total ?> turno<?= $total !== 1 ? 's' : '' ?>
        <?php if ($sinFicha > 0): ?>
        · <strong style="color:#dc2626;"><?= $sinFicha ?> sin ficha</strong>
        <?php endif; ?>
    </p>

    <?php if (empty($slotsData)): ?>
    <div style="text-align:center;padding:3rem;color:#94a3b8;">
        <div style="font-size:2.5rem;margin-bottom:.5rem;">✅</div>
        <p style="font-weight:600;">¡Todo calificado! No hay encuestas pendientes.</p>
    </div>
    <?php else: ?>
    <div class="mh-table-wrap">
    <table class="mh-table">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Compañero/a</th>
                <th>Turno · Local</th>
                <th>Estado</th>
                <th>Llegada</th>
                <th>Salida</th>
                <th>Reg. por</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($slotsData as $s):
            $asist    = $s['id_asistencia'] ? $s : null;
            $esFalta  = $asist && $asist['estado'] === 'FALTA';
            $llegPI   = $asist ? ($puntInfo[$asist['llegada_puntualidad'] ?? ''] ?? null) : null;
            $salidPI  = $asist ? ($puntInfo[$asist['salida_puntualidad']  ?? ''] ?? null) : null;
            $rowBg    = !$asist ? '#fafafa' : ($esFalta ? '#fff5f5' : '');

            $asistData = $asist ? [
                'llegada_puntualidad'       => $asist['llegada_puntualidad'],
                'area_ordenada_ingreso'     => $asist['area_ordenada_ingreso'],
                'area_limpia_ingreso'       => $asist['area_limpia_ingreso'],
                'aseo_personal'             => $asist['aseo_personal'],
                'vestimenta'                => $asist['vestimenta'],
                'unas'                      => $asist['unas'],
                'cabello'                   => $asist['cabello'],
                'salida_puntualidad'        => $asist['salida_puntualidad'],
                'estado_area_cierre'        => $asist['estado_area_cierre'],
                'limpieza_area_cierre'      => $asist['limpieza_area_cierre'],
                'area_ordenada_cierre'      => $asist['area_ordenada_cierre'],
                'participo_apertura_cierre' => $asist['participo_apertura_cierre'],
                'uso_celular'               => $asist['uso_celular'],
                'calificacion_turno'        => $asist['calificacion_turno'],
                'comentarios_ficha'         => $asist['comentarios_ficha'],
                'id_asistencia'             => $asist['id_asistencia'],
            ] : null;
            $asistJson = htmlspecialchars(json_encode($asistData), ENT_QUOTES);
            $diasLabel = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
            $dow  = $diasLabel[(int)date('w', strtotime($s['fecha_dia']))];
            $diaN = date('d/m', strtotime($s['fecha_dia']));
        ?>
        <tr style="background:<?= $rowBg ?>">
            <td style="white-space:nowrap;">
                <strong style="color:#1e293b;"><?= $dow ?></strong>
                <span class="mh-sub"><?= $diaN ?></span>
            </td>
            <td>
                <span style="font-weight:600;"><?= htmlspecialchars($s['trabajador_nombre']) ?></span>
                <span class="mh-sub"><?= htmlspecialchars($s['rol_desc']) ?></span>
            </td>
            <td>
                <span class="mh-badge" style="background:<?= $s['turno_id']==1?'#fef9c3':'#e0e7ff'?>;color:<?= $s['turno_id']==1?'#92400e':'#3730a3'?>">
                    <?= $s['turno_id']==1?'☀️ Mañana':'🌙 Tarde' ?>
                </span>
                <span class="mh-sub"><?= htmlspecialchars($s['local_desc']) ?></span>
            </td>
            <td>
                <?php if ($esFalta): ?>
                    <span class="mh-badge" style="background:#fee2e2;color:#991b1b;">Falta</span>
                <?php elseif ($llegPI): ?>
                    <span class="mh-badge" style="background:#d1fae5;color:#065f46;">Presente</span>
                <?php else: ?>
                    <span style="color:#cbd5e1;font-size:.75rem;">Sin reg.</span>
                <?php endif; ?>
            </td>
            <td>
                <?php if ($llegPI): ?>
                    <span class="mh-badge" style="background:<?= $llegPI['bg']?>;color:<?= $llegPI['color']?>"><?= $llegPI['label']?></span>
                <?php else: ?><span style="color:#cbd5e1;">—</span><?php endif; ?>
            </td>
            <td>
                <?php if ($salidPI): ?>
                    <span class="mh-badge" style="background:<?= $salidPI['bg']?>;color:<?= $salidPI['color']?>"><?= $salidPI['label']?></span>
                <?php else: ?><span style="color:#cbd5e1;">—</span><?php endif; ?>
            </td>
            <td style="font-size:.72rem;color:#0097A7;">
                <?= htmlspecialchars($s['registrado_por_nombre'] ?? '—') ?>
            </td>
            <td>
                <div class="mh-acc">
                    <?php if (!$esFalta): ?>
                    <button class="mh-btn mh-btn--entrada <?= $asist && $asist['llegada_puntualidad'] ? 'mh-btn--filled' : '' ?>"
                        data-pid="<?= $s['postulante_id'] ?>"
                        data-fecha="<?= $s['fecha_dia'] ?>"
                        data-turno="<?= $s['turno_id'] ?>"
                        data-nombre="<?= htmlspecialchars($s['trabajador_nombre'], ENT_QUOTES) ?>"
                        data-asist="<?= $asistJson ?>"
                        onclick="abrirModal('ENTRADA', this)">
                        <?= $asist && $asist['llegada_puntualidad'] ? '✏ Entrada' : 'Entrada' ?>
                    </button>
                    <button class="mh-btn mh-btn--salida <?= $asist && $asist['salida_puntualidad'] ? 'mh-btn--filled' : '' ?>"
                        data-pid="<?= $s['postulante_id'] ?>"
                        data-fecha="<?= $s['fecha_dia'] ?>"
                        data-turno="<?= $s['turno_id'] ?>"
                        data-nombre="<?= htmlspecialchars($s['trabajador_nombre'], ENT_QUOTES) ?>"
                        data-asist="<?= $asistJson ?>"
                        onclick="abrirModal('SALIDA', this)">
                        <?= $asist && $asist['salida_puntualidad'] ? '✏ Salida' : 'Salida' ?>
                    </button>
                    <button class="mh-btn mh-btn--falta"
                        data-pid="<?= $s['postulante_id'] ?>"
                        data-fecha="<?= $s['fecha_dia'] ?>"
                        data-turno="<?= $s['turno_id'] ?>"
                        data-nombre="<?= htmlspecialchars($s['trabajador_nombre'], ENT_QUOTES) ?>"
                        onclick="abrirModalFalta(this)">
                        Faltó
                    </button>
                    <?php else: ?>
                    <button class="mh-btn mh-btn--revertir"
                        onclick="abrirModalRevertirFalta(<?= $asist['id_asistencia'] ?>, '<?= addslashes($s['trabajador_nombre']) ?>')">
                        ↩ Revertir
                    </button>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>

<?php else: ?>
    <!-- ══════════ MODO MIS ENCUESTAS ══════════ -->

    <!-- Navegación mensual -->
    <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.75rem;flex-wrap:wrap;">
        <a href="?modo=mis-encuestas&mes=<?= $mesPasado ?>"
           style="padding:.38rem .9rem;border-radius:8px;font-size:.82rem;font-weight:600;border:1.5px solid #e2e8f0;background:#fff;color:#475569;text-decoration:none;">
            ← Mes anterior
        </a>
        <span style="font-size:.92rem;font-weight:700;color:#1e293b;padding:0 .5rem;"><?= $mesLabel ?></span>
        <?php if ($filtroMes < $mesActual): ?>
        <a href="?modo=mis-encuestas&mes=<?= $mesSiguiente ?>"
           style="padding:.38rem .9rem;border-radius:8px;font-size:.82rem;font-weight:600;border:1.5px solid #e2e8f0;background:#fff;color:#475569;text-decoration:none;">
            Mes siguiente →
        </a>
        <?php endif; ?>
    </div>

    <div style="background:#fef9c3;border:1px solid #fbbf24;border-radius:8px;padding:.7rem 1rem;margin-bottom:1rem;font-size:.82rem;color:#92400e;">
        👤 Esta es tu ficha personal. <strong>Solo un compañero puede completar tus encuestas.</strong>
    </div>

    <!-- KPIs -->
    <div class="mh-kpis">
        <div class="mh-kpi"><div class="mh-kpi__num" style="color:#0097A7;"><?= $totalManana ?></div><div class="mh-kpi__label">Turnos Mañana ☀️</div></div>
        <div class="mh-kpi"><div class="mh-kpi__num" style="color:#475569;"><?= $totalTarde ?></div><div class="mh-kpi__label">Turnos Tarde 🌙</div></div>
        <div class="mh-kpi"><div class="mh-kpi__num" style="color:#059669;"><?= $conFicha ?></div><div class="mh-kpi__label">Con ficha</div></div>
        <div class="mh-kpi"><div class="mh-kpi__num" style="color:#dc2626;"><?= $sinRegistro ?></div><div class="mh-kpi__label">Sin registro</div></div>
    </div>

    <?php if (empty($fechas)): ?>
    <div style="text-align:center;padding:3rem;color:#94a3b8;">
        <div style="font-size:2.5rem;margin-bottom:.5rem;">📅</div>
        <p style="font-weight:600;">Sin turnos asignados en <?= $mesLabel ?></p>
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
                <th>Estado</th>
                <th>Llegada</th>
                <th>Salida</th>
                <th>Reg. por</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($fechas as $fecha):
            $dow      = $diasLabel[(int)date('w', strtotime($fecha))];
            $diaN     = date('d/m', strtotime($fecha));
            $slotsDia = $slotsPorFechaTurno[$fecha] ?? [];
            $rempDia  = $rempPorFecha[$fecha]        ?? [];

            foreach ($slotsDia as $turnoId => $slot):
                $asist  = $asistPorFecha[$fecha][$turnoId]
                        ?? $asistPorFecha[$fecha][0]
                        ?? null;
                $remp   = $rempDia[$turnoId] ?? null;
                $rowBg  = $remp ? '#fff8f8' : ($slot['cubre_id'] ? '#f0fff4' : '#fff');
                $esFalta = $asist && $asist['estado'] === 'FALTA';
                $llegPI  = $asist ? ($puntInfo[$asist['llegada_puntualidad'] ?? ''] ?? null) : null;
                $salidPI = $asist ? ($puntInfo[$asist['salida_puntualidad']  ?? ''] ?? null) : null;
        ?>
            <tr style="background:<?= $rowBg ?>">
                <td style="white-space:nowrap;">
                    <strong style="color:#1e293b;"><?= $dow ?></strong>
                    <span class="mh-sub"><?= $diaN ?></span>
                </td>
                <td>
                    <span class="mh-badge" style="background:<?= $turnoId==1?'#fef9c3':'#e0e7ff'?>;color:<?= $turnoId==1?'#92400e':'#3730a3'?>">
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
                    <?php if ($esFalta): ?>
                        <span class="mh-badge" style="background:#fee2e2;color:#991b1b;">Falta</span>
                    <?php elseif ($llegPI): ?>
                        <span class="mh-badge" style="background:#d1fae5;color:#065f46;">Presente</span>
                    <?php else: ?>
                        <span style="color:#cbd5e1;font-size:.75rem;">Sin reg.</span>
                    <?php endif; ?>
                </td>
                <td><?php if ($llegPI): ?>
                    <span class="mh-badge" style="background:<?=$llegPI['bg']?>;color:<?=$llegPI['color']?>"><?=$llegPI['label']?></span>
                <?php else: ?><span style="color:#cbd5e1;">—</span><?php endif; ?></td>
                <td><?php if ($salidPI): ?>
                    <span class="mh-badge" style="background:<?=$salidPI['bg']?>;color:<?=$salidPI['color']?>"><?=$salidPI['label']?></span>
                <?php else: ?><span style="color:#cbd5e1;">—</span><?php endif; ?></td>
                <td style="font-size:.72rem;color:#0097A7;">
                    <?= htmlspecialchars($asist['registrado_por_nombre'] ?? '—') ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
<?php endif; ?>

</main>

<?php if ($modo !== 'mis-encuestas'): ?>
<script>
const BASE = '<?= $basePath ?>';

let _seccion  = 'ENTRADA';
let _pid      = 0;
let _fecha    = '';
let _turnoId  = 0;
let _radioVals = {};
let _faltaPid = 0; let _faltaFecha = ''; let _faltaTurno = 0;

function fmtFecha(f) {
    const d = new Date(f + 'T12:00:00');
    const dias = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
    return `${dias[d.getDay()]} ${d.getDate()}/${String(d.getMonth()+1).padStart(2,'0')}`;
}

function pickRadio(btn) {
    const field = btn.dataset.field;
    document.querySelectorAll(`.mh-rb[data-field="${field}"]`).forEach(b => {
        b.classList.remove('active');
        b.style.borderColor = ''; b.style.color = '';
    });
    btn.classList.add('active');
    btn.style.borderColor = ''; btn.style.color = '';
    _radioVals[field] = btn.dataset.val;
}

function preselect(field, val) {
    if (val === null || val === undefined) return;
    const sVal = String(val);
    document.querySelectorAll(`.mh-rb[data-field="${field}"]`).forEach(btn => {
        const match = btn.dataset.val === sVal;
        btn.classList.toggle('active', match);
        if (!match) { btn.style.borderColor = ''; btn.style.color = ''; }
    });
    _radioVals[field] = sVal;
}

function clearAll() {
    document.querySelectorAll('.mh-rb').forEach(b => { b.classList.remove('active'); b.style.borderColor = ''; b.style.color = ''; });
    _radioVals = {};
}

function abrirModal(seccion, btn) {
    _seccion = seccion; _pid = parseInt(btn.dataset.pid);
    _fecha = btn.dataset.fecha; _turnoId = parseInt(btn.dataset.turno);
    const exist = JSON.parse(btn.dataset.asist || 'null');
    clearAll();
    document.getElementById('mhPassword').value = '';
    document.getElementById('mhComentarios').value = '';
    document.getElementById('mhErr').style.display = 'none';
    document.getElementById('mhModalTitulo').textContent =
        (seccion === 'ENTRADA' ? 'Registrar entrada' : 'Registrar salida') + ` — ${btn.dataset.nombre}`;
    document.getElementById('mhModalDesc').textContent = `${fmtFecha(_fecha)} · Confirma con TU propia contraseña.`;
    document.getElementById('mhSecEntrada').hidden = seccion !== 'ENTRADA';
    document.getElementById('mhSecSalida').hidden  = seccion !== 'SALIDA';
    if (exist) {
        document.getElementById('mhComentarios').value = exist.comentarios_ficha || '';
        if (seccion === 'ENTRADA') {
            preselect('llegada_puntualidad',    exist.llegada_puntualidad);
            preselect('area_ordenada_ingreso',  exist.area_ordenada_ingreso);
            preselect('area_limpia_ingreso',    exist.area_limpia_ingreso);
            preselect('aseo_personal',          exist.aseo_personal);
            preselect('vestimenta',             exist.vestimenta);
            preselect('unas',                   exist.unas);
            preselect('cabello',                exist.cabello);
        } else {
            preselect('salida_puntualidad',        exist.salida_puntualidad);
            preselect('estado_area_cierre',        exist.estado_area_cierre);
            preselect('limpieza_area_cierre',      exist.limpieza_area_cierre);
            preselect('area_ordenada_cierre',      exist.area_ordenada_cierre);
            preselect('participo_apertura_cierre', exist.participo_apertura_cierre);
            preselect('uso_celular',               exist.uso_celular);
            preselect('calificacion_turno',        exist.calificacion_turno);
        }
    }
    document.getElementById('mhModal').removeAttribute('hidden');
    setTimeout(() => document.getElementById('mhPassword').focus(), 80);
}

function cerrarModal() { document.getElementById('mhModal').setAttribute('hidden', ''); }

async function confirmarRegistro() {
    const password = document.getElementById('mhPassword').value.trim();
    const err = document.getElementById('mhErr');
    if (!password) { showErr(err, 'Tu contraseña es requerida.'); return; }
    const payload = {
        postulante_id: _pid, fecha: _fecha, turno_id: _turnoId,
        seccion: _seccion, password,
        comentarios_ficha: document.getElementById('mhComentarios').value.trim() || null,
    };
    const yn = (k) => _radioVals[k] !== undefined ? parseInt(_radioVals[k]) : null;
    if (_seccion === 'ENTRADA') {
        payload.llegada_puntualidad    = _radioVals['llegada_puntualidad'] || null;
        payload.area_ordenada_ingreso  = yn('area_ordenada_ingreso');
        payload.area_limpia_ingreso    = yn('area_limpia_ingreso');
        payload.aseo_personal          = _radioVals['aseo_personal'] || null;
        payload.vestimenta             = _radioVals['vestimenta'] || null;
        payload.unas                   = _radioVals['unas'] || null;
        payload.cabello                = _radioVals['cabello'] || null;
    } else {
        payload.salida_puntualidad        = _radioVals['salida_puntualidad'] || null;
        payload.estado_area_cierre        = _radioVals['estado_area_cierre'] || null;
        payload.limpieza_area_cierre      = yn('limpieza_area_cierre');
        payload.area_ordenada_cierre      = yn('area_ordenada_cierre');
        payload.participo_apertura_cierre = yn('participo_apertura_cierre');
        payload.uso_celular               = _radioVals['uso_celular'] || null;
        payload.calificacion_turno        = _radioVals['calificacion_turno'] || null;
    }
    try {
        const r = await fetch(`${BASE}/staff/api/asistencia/registrar`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload),
        });
        const res = await r.json();
        if (res.success) { cerrarModal(); location.reload(); }
        else showErr(err, res.message || 'Error.');
    } catch { showErr(err, 'Error de conexión.'); }
}

function abrirModalFalta(btn) {
    _faltaPid = parseInt(btn.dataset.pid); _faltaFecha = btn.dataset.fecha; _faltaTurno = parseInt(btn.dataset.turno);
    document.getElementById('mhFaltaDesc').textContent = `${btn.dataset.nombre} · ${fmtFecha(_faltaFecha)}`;
    document.getElementById('mhFaltaPwd').value = '';
    document.getElementById('mhFaltaErr').style.display = 'none';
    document.getElementById('mhModalFalta').removeAttribute('hidden');
    setTimeout(() => document.getElementById('mhFaltaPwd').focus(), 50);
}
function cerrarModalFalta() { document.getElementById('mhModalFalta').setAttribute('hidden', ''); }

async function confirmarFalta() {
    const pwd = document.getElementById('mhFaltaPwd').value.trim();
    const err = document.getElementById('mhFaltaErr');
    if (!pwd) { showErr(err, 'Tu contraseña es requerida.'); return; }
    try {
        const r = await fetch(`${BASE}/staff/api/asistencia/registrar`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ postulante_id: _faltaPid, fecha: _faltaFecha, turno_id: _faltaTurno, seccion: 'FALTA', password: pwd }),
        });
        const res = await r.json();
        if (res.success) { cerrarModalFalta(); location.reload(); }
        else showErr(err, res.message || 'Error.');
    } catch { showErr(err, 'Error de conexión.'); }
}

function abrirModalRevertirFalta(id, nombre) {
    document.getElementById('mhRevertirId').value = id;
    document.getElementById('mhRevertirDesc').textContent = `Eliminar el registro de falta de "${nombre}".`;
    document.getElementById('mhRevertirPwd').value = '';
    document.getElementById('mhRevertirErr').style.display = 'none';
    document.getElementById('mhModalRevertir').removeAttribute('hidden');
    setTimeout(() => document.getElementById('mhRevertirPwd').focus(), 50);
}
function cerrarModalRevertir() { document.getElementById('mhModalRevertir').setAttribute('hidden', ''); }

async function confirmarRevertir() {
    const id  = document.getElementById('mhRevertirId').value;
    const pwd = document.getElementById('mhRevertirPwd').value.trim();
    const err = document.getElementById('mhRevertirErr');
    if (!pwd) { showErr(err, 'Tu contraseña es requerida.'); return; }
    try {
        const r = await fetch(`${BASE}/staff/api/asistencia/${id}/revertir`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ password: pwd }),
        });
        const res = await r.json();
        if (res.success) { cerrarModalRevertir(); location.reload(); }
        else showErr(err, res.message || 'Error.');
    } catch { showErr(err, 'Error de conexión.'); }
}

function showErr(el, msg) { el.textContent = msg; el.style.display = 'block'; }
</script>
<?php endif; ?>
</body>
</html>
