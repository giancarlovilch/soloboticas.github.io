<?php
// Variables inyectadas por CajaController — el IDE no las ve, pero existen en runtime
/** @var array $sesion */
/** @var array $locales */
/** @var array $turnos */
/** @var array $conceptos */
/** @var array $staff */
/** @var array $tiposEgreso */
/** @var array $modos */
/** @var array $detalle */
/** @var array $gastos */
$basePath         = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
$userName         = $userName         ?? $_SESSION['user_name'] ?? 'Usuario';
$locales          = $locales          ?? [];
$turnos           = $turnos           ?? [];
$esAdminCaja      = $esAdminCaja      ?? true;
$horarioCajeraMap = $horarioCajeraMap ?? [];
$conceptos = $conceptos ?? [];
$staff     = $staff     ?? [];
$detalle   = $detalle   ?? [];
$gastos    = $gastos    ?? [];
$esEdicion = isset($sesion);
$sesionId  = $sesion['id_sesion'] ?? 0;
$hoy       = date('Y-m-d');

// Pre-cargar activos si es edición
$act = [
    'caja_exterior' => $detalle['monto_caja_exterior']         ?? '',
    'monedas'       => $detalle['monto_monedas']                ?? '',
    'billetes'      => $detalle['monto_billetes_caja']          ?? '',
    'caja_fuerte'   => $detalle['monto_billetes_caja_fuerte']   ?? '',
    'agente_bcp'    => $detalle['monto_agente_bcp']             ?? '',
    'yape_plin'     => $detalle['monto_yape_plin']              ?? '',
    'visas'         => $detalle['monto_visas']                  ?? '',
    'bcp'           => $detalle['monto_bcp']                    ?? '',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $esEdicion ? 'Arqueo de turno' : 'Apertura de turno' ?> | Caja SB</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/caja.css">
    <link rel="icon" type="image/x-icon" href="<?= $basePath ?>/assets/img/logo.ico">
    <style>
        /* ── Encuesta de apertura ── */
        .sv-block { background:#f8fafc;border-radius:10px;padding:.7rem .85rem;margin-bottom:.65rem;border:1px solid #e8edf2; }
        .sv-block__hd { font-size:.67rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#64748b;margin-bottom:.55rem; }
        .sv-rg { display:flex;gap:.3rem;flex-wrap:wrap; }
        .sv-row2 { display:grid;grid-template-columns:1fr 1fr;gap:.5rem; }
        .sv-field { margin-bottom:.6rem; }
        .sv-field__label { font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#64748b;display:block;margin-bottom:.3rem; }
        .sv-rb { padding:.38rem .75rem;border:1.5px solid #e2e8f0;border-radius:8px;font-size:.78rem;font-weight:600;cursor:pointer;background:#fff;color:#475569;transition:all .13s;line-height:1.3; }
        .sv-rb small { display:block;font-size:.62rem;font-weight:400;color:#94a3b8; }
        .sv-rb[data-color="blue"].active   { border-color:#3b82f6;background:#dbeafe;color:#1e40af; }
        .sv-rb[data-color="green"].active  { border-color:#10b981;background:#d1fae5;color:#065f46; }
        .sv-rb[data-color="amber"].active  { border-color:#f59e0b;background:#fef3c7;color:#92400e; }
        .sv-rb[data-color="orange"].active { border-color:#f97316;background:#ffedd5;color:#9a3412; }
        .sv-rb[data-color="red"].active    { border-color:#ef4444;background:#fee2e2;color:#991b1b; }
    </style>
</head>
<body>

<header class="caja-header">
    <div class="caja-header__brand">
        <div class="caja-header__logo">SB</div>
        <div>
            <p class="caja-header__company">Grupo KGyR S.A.C</p>
            <p class="caja-header__app"><?= $esEdicion ? 'Arqueo de turno #' . $sesionId : 'Apertura de turno de caja' ?></p>
        </div>
    </div>
    <div class="caja-header__right">
        <a href="<?= $basePath ?>/caja" class="caja-btn-back">← Volver a caja</a>
    </div>
</header>

<main class="caja-main">

    <div id="sesionMsg" class="caja-alert" hidden></div>

    <!-- ── Config de sesión (solo nueva) ─────────────────── -->
    <?php if (!$esEdicion): ?>
    <section class="caja-card">
        <h2 class="caja-card__title">1. Datos del turno</h2>
        <div class="caja-form-grid">
            <?php if (!$esAdminCaja && empty($locales)): ?>
            <div style="grid-column:1/-1;padding:.75rem 1rem;background:#fef2f2;border:1px solid #fca5a5;border-radius:8px;font-size:.82rem;color:#991b1b;font-weight:600;">
                ⚠️ No tienes ningún turno asignado como cajera en el horario de hoy.
                <a href="<?= $basePath ?>/horario" style="color:#dc2626;margin-left:.5rem;">→ Ver horario</a>
            </div>
            <?php else: ?>

            <div class="caja-field">
                <label>Local <span class="req">*</span></label>
                <select id="localId" class="caja-input" onchange="cargarCajas(this.value); filtrarTurnos(this.value);">
                    <option value="">— Selecciona local —</option>
                    <?php foreach ($locales as $l): ?>
                        <option value="<?= $l['id'] ?>"><?= htmlspecialchars($l['descripcion']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="caja-field">
                <label>Caja <span class="req">*</span></label>
                <select id="cajaId" class="caja-input">
                    <option value="">— Primero selecciona local —</option>
                </select>
            </div>
            <div class="caja-field">
                <label>Turno <span class="req">*</span></label>
                <select id="turnoId" class="caja-input" onchange="cargarStaffHorario()">
                    <option value="">— Primero selecciona local —</option>
                </select>
            </div>
            <?php endif; ?>
            <div class="caja-field">
                <label>Personal del turno <span class="req">*</span></label>
                <select id="vendedorId" class="caja-input" disabled>
                    <option value="">— Selecciona local y turno primero —</option>
                </select>
                <small id="horarioHint" style="font-size:.72rem;color:#94a3b8;margin-top:.25rem;display:block;">
                    Solo aparece el personal asignado en el horario de hoy para ese local y turno.
                </small>
            </div>
        </div>
        <div class="caja-base-info" style="font-size:0.82rem;color:#64748b;margin:.5rem 0;">
            Fecha de operación: <strong><?= date('d/m/Y') ?></strong>
        </div>
        <div class="caja-base-info" id="baseInfo" hidden>
            <p>Base del día anterior: <strong id="baseAmount">S/ 0.00</strong></p>
        </div>
        <button class="caja-btn caja-btn--primary" onclick="mostrarEncuesta()" id="btnCrear">
            Continuar →
        </button>
    </section>

    <!-- ── Paso 2: Encuesta de apertura para la vendedora ── -->
    <section class="caja-card" id="surveySection" hidden>
        <h2 class="caja-card__title">2. Evaluación de apertura — <span id="surveyVendNombre"></span></h2>
        <p class="caja-card__desc">Completa la ficha de la vendedora antes de abrir el turno.</p>

        <div class="sv-block">
            <div class="sv-block__hd">⏰ Puntualidad al ingreso</div>
            <div class="sv-rg">
                <button type="button" class="sv-rb" data-color="blue"   data-field="llegada_puntualidad" data-val="MUY_TEMPRANO" onclick="pickSurveyRadio(this)">Muy anticipada <small>+10 min antes</small></button>
                <button type="button" class="sv-rb" data-color="green"  data-field="llegada_puntualidad" data-val="TEMPRANO"     onclick="pickSurveyRadio(this)">Con anticipación <small>menos de 10 min</small></button>
                <button type="button" class="sv-rb" data-color="orange" data-field="llegada_puntualidad" data-val="TARDE"        onclick="pickSurveyRadio(this)">Retraso leve <small>menos de 10 min</small></button>
                <button type="button" class="sv-rb" data-color="red"    data-field="llegada_puntualidad" data-val="MUY_TARDE"    onclick="pickSurveyRadio(this)">Retraso considerable <small>+10 min tarde</small></button>
            </div>
        </div>

        <div class="sv-block">
            <div class="sv-block__hd">🏪 Estado del área al ingreso</div>
            <div class="sv-row2">
                <div class="sv-field">
                    <span class="sv-field__label">¿Área ordenada?</span>
                    <div class="sv-rg">
                        <button type="button" class="sv-rb" data-color="green" data-field="area_ordenada_ingreso" data-val="1" onclick="pickSurveyRadio(this)">Sí</button>
                        <button type="button" class="sv-rb" data-color="red"   data-field="area_ordenada_ingreso" data-val="0" onclick="pickSurveyRadio(this)">No</button>
                    </div>
                </div>
                <div class="sv-field">
                    <span class="sv-field__label">¿Área limpia?</span>
                    <div class="sv-rg">
                        <button type="button" class="sv-rb" data-color="green" data-field="area_limpia_ingreso" data-val="1" onclick="pickSurveyRadio(this)">Sí</button>
                        <button type="button" class="sv-rb" data-color="red"   data-field="area_limpia_ingreso" data-val="0" onclick="pickSurveyRadio(this)">No</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="sv-block">
            <div class="sv-block__hd">👕 Presentación personal</div>
            <div class="sv-field">
                <span class="sv-field__label">Higiene personal</span>
                <div class="sv-rg">
                    <button type="button" class="sv-rb" data-color="red"   data-field="aseo_personal" data-val="DEFICIENTE" onclick="pickSurveyRadio(this)">Deficiente</button>
                    <button type="button" class="sv-rb" data-color="amber" data-field="aseo_personal" data-val="ACEPTABLE"  onclick="pickSurveyRadio(this)">Aceptable</button>
                    <button type="button" class="sv-rb" data-color="green" data-field="aseo_personal" data-val="OPTIMO"     onclick="pickSurveyRadio(this)">Óptimo</button>
                </div>
            </div>
            <div class="sv-field">
                <span class="sv-field__label">Uniforme e indumentaria</span>
                <div class="sv-rg">
                    <button type="button" class="sv-rb" data-color="red"   data-field="vestimenta" data-val="DESCUIDADO"  onclick="pickSurveyRadio(this)">Descuidado</button>
                    <button type="button" class="sv-rb" data-color="amber" data-field="vestimenta" data-val="PRESENTABLE" onclick="pickSurveyRadio(this)">Presentable</button>
                    <button type="button" class="sv-rb" data-color="green" data-field="vestimenta" data-val="IMPECABLE"   onclick="pickSurveyRadio(this)">Impecable</button>
                </div>
            </div>
            <div class="sv-row2">
                <div class="sv-field">
                    <span class="sv-field__label">Estado de uñas</span>
                    <div class="sv-rg">
                        <button type="button" class="sv-rb" data-color="red"   data-field="unas" data-val="DESCUIDADAS" onclick="pickSurveyRadio(this)">Descuidadas</button>
                        <button type="button" class="sv-rb" data-color="amber" data-field="unas" data-val="ACEPTABLES"  onclick="pickSurveyRadio(this)">Aceptables</button>
                        <button type="button" class="sv-rb" data-color="green" data-field="unas" data-val="CUIDADAS"    onclick="pickSurveyRadio(this)">Cuidadas</button>
                    </div>
                </div>
                <div class="sv-field">
                    <span class="sv-field__label">Presentación del cabello</span>
                    <div class="sv-rg">
                        <button type="button" class="sv-rb" data-color="red"   data-field="cabello" data-val="SUELTO"   onclick="pickSurveyRadio(this)">Suelto</button>
                        <button type="button" class="sv-rb" data-color="green" data-field="cabello" data-val="RECOGIDO" onclick="pickSurveyRadio(this)">Recogido</button>
                        <button type="button" class="sv-rb" data-color="green" data-field="cabello" data-val="MONO"     onclick="pickSurveyRadio(this)">Con moño</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="caja-field" style="max-width:320px;margin-top:.5rem;">
            <label>Tu contraseña (cajera) <span class="req">*</span></label>
            <input type="password" id="surveyPassword" class="caja-input" placeholder="Confirma con tu contraseña"
                   onkeydown="if(event.key==='Enter') abrirTurno()">
        </div>

        <button class="caja-btn caja-btn--primary" onclick="abrirTurno()" id="btnAbrirTurno" style="margin-top:.75rem;">
            Abrir turno →
        </button>
    </section>
    <?php else: ?>
    <section class="caja-card caja-card--info">
        <div class="caja-info-row">
            <span><strong><?= htmlspecialchars($sesion['caja_desc']) ?></strong> — <?= htmlspecialchars($sesion['local_desc']) ?></span>
            <span><?= htmlspecialchars($sesion['turno_desc']) ?> · <?= date('d/m/Y', strtotime($sesion['fecha_operacion'])) ?></span>
            <span>Base: <strong>S/ <?= number_format($sesion['saldo_inicial'], 2) ?></strong></span>
        </div>
    </section>

    <?php foreach ($transferenciasPendientes ?? [] as $tp): ?>
    <div class="caja-alert caja-alert--info" style="margin-top:.6rem;">
        <?php if ($tp['lado'] === 'ORIGEN'): ?>
            Transferencia de <strong>S/ <?= number_format((float)$tp['monto'], 2) ?></strong> enviada a
            <strong><?= htmlspecialchars($tp['caja_destino_desc']) ?></strong>
        <?php else: ?>
            Transferencia de <strong>S/ <?= number_format((float)$tp['monto'], 2) ?></strong> recibida de
            <strong><?= htmlspecialchars($tp['caja_origen_desc']) ?></strong>
        <?php endif; ?>
        (confirmada el <?= date('d/m/Y H:i', strtotime($tp['confirmed_at'])) ?>) — está <strong>por aplicar</strong>:
        se reflejará en el cuadre de cierre de esta sesión.
    </div>
    <?php endforeach; ?>

    <?php foreach ($retirosPendientes ?? [] as $rp): ?>
    <div class="caja-alert caja-alert--info" style="margin-top:.6rem;">
        Se retiraron <strong>S/ <?= number_format((float)$rp['monto'], 2) ?></strong> de esta caja para depósito
        a Grupo KGyR (<?= htmlspecialchars($rp['banco']) ?>), registrado el
        <?= date('d/m/Y H:i', strtotime($rp['registrado_en'])) ?> por
        <strong><?= htmlspecialchars($rp['registrado_por_nombre']) ?></strong>
        — se reflejará en el cuadre de cierre de esta sesión.
    </div>
    <?php endforeach; ?>

    <input type="hidden" id="sesionId" value="<?= $sesionId ?>">
    <?php endif; ?>

    <!-- ── Activos ─────────────────────────────────────────── -->
    <section class="caja-card" id="formActivos" <?= !$esEdicion ? 'hidden' : '' ?>>
        <h2 class="caja-card__title">2. Arqueo de efectivo</h2>
        <p class="caja-card__desc">Registra el conteo físico de dinero y el saldo del agente BCP al cierre del turno.</p>

        <div class="caja-activos-grid">
            <div class="caja-activo-item">
                <label>Caja</label>
                <div class="caja-input-money">
                    <span>S/</span>
                    <input type="number" id="act_caja_exterior" class="caja-input caja-input--money"
                           min="0" step="0.01" value="<?= $act['caja_exterior'] ?>" oninput="recalcular()">
                </div>
            </div>
            <div class="caja-activo-item">
                <label>Monedas</label>
                <div class="caja-input-money">
                    <span>S/</span>
                    <input type="number" id="act_monedas" class="caja-input caja-input--money"
                           min="0" step="0.01" value="<?= $act['monedas'] ?>" oninput="recalcular()">
                </div>
            </div>
            <div class="caja-activo-item">
                <label>Billetes</label>
                <div class="caja-input-money">
                    <span>S/</span>
                    <input type="number" id="act_billetes" class="caja-input caja-input--money"
                           min="0" step="0.01" value="<?= $act['billetes'] ?>" oninput="recalcular()">
                </div>
            </div>
            <div class="caja-activo-item">
                <label>Caja Fuerte</label>
                <div class="caja-input-money">
                    <span>S/</span>
                    <input type="number" id="act_caja_fuerte" class="caja-input caja-input--money"
                           min="0" step="0.01" value="<?= $act['caja_fuerte'] ?>" oninput="recalcular()">
                </div>
            </div>
            <div class="caja-activo-item caja-activo-item--bcp">
                <label>Saldo Agente BCP</label>
                <div class="caja-input-money">
                    <span>S/</span>
                    <input type="number" id="act_agente_bcp" class="caja-input caja-input--money"
                           min="0" step="0.01" value="<?= $act['agente_bcp'] ?>" oninput="recalcular()">
                </div>
            </div>
        </div>

        <div class="caja-subtotal caja-subtotal--total">
            <span>Total arqueado (efectivo + Agente BCP):</span>
            <strong id="totalContado">S/ 0.00</strong>
        </div>

        <!-- Rendimiento BCP -->
        <div style="margin-top:1.25rem;padding-top:1rem;border-top:1px dashed #e2e8f0;">
            <p style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#64748b;margin-bottom:.5rem;">
                Rendimiento Agente BCP
            </p>
            <div style="display:flex;align-items:center;gap:.75rem;">
                <label style="font-size:0.82rem;color:#334155;font-weight:500;white-space:nowrap;">N° de operaciones realizadas</label>
                <input type="number" id="act_num_ops_bcp" class="caja-input caja-input--money"
                       min="0" step="1" placeholder="Ej: 5" required
                       value="<?= isset($detalle['num_operaciones_bcp']) ? (int)$detalle['num_operaciones_bcp'] : '' ?>"
                       style="max-width:100px;">
            </div>
        </div>
    </section>

    <!-- ── Pagos digitales ─────────────────────────────────── -->
    <section class="caja-card" id="formDigitales" <?= !$esEdicion ? 'hidden' : '' ?>>
        <div class="caja-card__header-row">
            <div>
                <h2 class="caja-card__title">3. Cobros electrónicos del turno</h2>
                <p class="caja-card__desc">
                    Registra cada Yape, Plin, Visa o transferencia recibida <strong>individualmente</strong>.
                    El supervisor los verificará desde <strong>Cobros Electrónicos</strong>.
                    Todos los cobros declarados se descuentan del efectivo esperado en el arqueo.
                </p>
            </div>
        </div>

        <!-- Formulario rápido para agregar -->
        <div class="caja-digital-form" id="digitalFormAdd">
            <select id="digitalModo" class="caja-input" style="max-width:140px;">
                <?php foreach ($modos ?? [] as $m): ?>
                    <option value="<?= $m['id_modo'] ?>"><?= htmlspecialchars($m['descripcion']) ?></option>
                <?php endforeach; ?>
            </select>
            <div class="caja-input-money" style="flex:1;max-width:160px;">
                <span>S/</span>
                <input type="number" id="digitalMonto" class="caja-input caja-input--money" min="0.01" step="0.01" placeholder="0.00">
            </div>
            <input type="text" id="digitalNumOp" class="caja-input" placeholder="N° operación (opc.)" style="flex:1;">
            <button class="caja-btn caja-btn--outline" onclick="agregarPagoDigital()">+ Agregar</button>
        </div>

        <div id="digitalesMsg" class="caja-alert" style="margin:.5rem 0;" hidden></div>

        <div class="caja-table-wrap" style="margin-top:.75rem;" id="digitalesTableWrap">
            <table class="caja-table" id="tablaDigitales">
                <thead>
                    <tr>
                        <th>Modo</th>
                        <th>N° Operación</th>
                        <th class="text-right">Monto</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Quitar</th>
                    </tr>
                </thead>
                <tbody id="tbodyDigitales">
                    <tr><td colspan="5" style="text-align:center;color:#94a3b8;padding:1rem;">Sin pagos digitales registrados.</td></tr>
                </tbody>
            </table>
        </div>

        <div class="caja-subtotal" style="margin-top:.75rem;">
            <span>Total digital registrado (pendiente de aprobación):</span>
            <strong id="totalDigital">S/ 0.00</strong>
        </div>

        <!-- SoloBank -->
        <div style="margin-top:1rem;padding-top:.75rem;border-top:1px solid #e2e8f0;">
            <p style="font-size:.8rem;font-weight:700;color:#003da6;margin-bottom:.5rem;text-transform:uppercase;letter-spacing:.4px;">
                📋 Vale SoloBank
            </p>
            <div id="sbFormWrap" style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;">
                <select id="sbSelect" class="caja-input" style="flex:1;min-width:220px;" onchange="sbSelectChanged()">
                    <option value="">— Seleccionar vale —</option>
                    <?php foreach ($soloBankVales ?? [] as $v):
                        $label = htmlspecialchars($v['caja']) . ' · '
                               . date('d/m', strtotime($v['fecha'])) . ' ' . htmlspecialchars($v['turno'])
                               . ' · S/ ' . number_format((float)$v['total'], 2)
                               . ' (' . $v['conteo'] . ' pagos)';
                    ?>
                        <option value="<?= htmlspecialchars($v['codigo']) ?>"
                                data-monto="<?= (float)$v['total'] ?>">
                            <?= $label ?>
                        </option>
                    <?php endforeach ?>
                </select>
                <span id="sbMonto" style="font-weight:700;color:#003da6;min-width:80px;"></span>
                <button class="caja-btn caja-btn--outline" onclick="agregarVale()" id="sbBtn" disabled>
                    + Agregar vale
                </button>
            </div>
            <div id="sbMsg" class="caja-alert" style="margin:.4rem 0;" hidden></div>
        </div>
    </section>

    <!-- ── Gastos ─────────────────────────────────────────── -->
    <section class="caja-card" id="formGastos" <?= !$esEdicion ? 'hidden' : '' ?>>
        <div class="caja-card__header-row">
            <div>
                <h2 class="caja-card__title">4. Egresos del turno</h2>
                <p class="caja-card__desc">Registra todos los egresos: pagos a personal, gastos del local y otros conceptos.</p>
            </div>
        </div>

        <!-- Egresos ya guardados (solo lectura) -->
        <table class="caja-table" id="gastosDisplayTable" style="margin-bottom:.5rem;<?= empty($gastos) ? 'display:none;' : '' ?>">
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Detalle</th>
                    <th class="text-right">Monto</th>
                    <th class="text-center" style="width:36px;"></th>
                </tr>
            </thead>
            <tbody id="gastosDisplayBody">
            <?php foreach ($gastos ?? [] as $idx => $g):
                $tipoPagoLabel = ['MES_ACTUAL'=>'Pago Mes Actual','MES_PASADO'=>'Pago Mes Pasado','PAGO_EXTRA'=>'Pago Extra'];
                $modo = $g['modo_ref'] ?? '';
                if ($modo === 'PERSONAL')
                    $det = htmlspecialchars($g['descripcion']) . ' · ' . ($tipoPagoLabel[$g['tipo_pago']??'']??'');
                elseif ($modo === 'LOCAL')
                    $det = htmlspecialchars($g['descripcion']) . ($g['concepto_desc'] ? ' / '.htmlspecialchars($g['concepto_desc']) : '');
                else
                    $det = htmlspecialchars($g['descripcion'] ?: $g['comprobante'] ?: '—');
            ?>
            <tr data-gasto-idx="<?= $idx ?>">
                <td><span class="caja-gasto-badge caja-gasto-badge--<?= $g['tipo_css'] ?? 'otro' ?>"><?= htmlspecialchars($g['etiqueta'] ?? '') ?></span></td>
                <td style="font-size:.83rem;color:#475569;"><?= $det ?></td>
                <td class="text-right" style="font-weight:700;">S/ <?= number_format((float)$g['monto'],2) ?></td>
                <td class="text-center">
                    <button type="button" style="background:none;border:none;cursor:pointer;color:#94a3b8;font-size:.9rem;padding:.1rem .3rem;border-radius:4px;"
                            onmouseover="this.style.color='#dc2626'"
                            onmouseout="this.style.color='#94a3b8'"
                            onclick="eliminarGastoGuardado(<?= $idx ?>, this)">✕</button>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Contenedor oculto para collectGastos() — mantiene los datos del batch -->
        <div id="gastosContainer" hidden>
            <?php foreach ($gastos ?? [] as $idx => $g):
                $modo = $g['modo_ref'] ?? '';
            ?>
            <div class="caja-gasto-row" data-saved-idx="<?= $idx ?>">
                <select class="caja-input caja-gasto__tipo" onchange="tipoChanged(this)">
                    <?php foreach ($tiposEgreso ?? [] as $te): ?>
                        <option value="<?= $te['id_tipo_egreso'] ?>"
                                data-modo="<?= $te['modo_ref'] ?>"
                                <?= (int)($g['tipo_egreso_id'] ?? 0) === (int)$te['id_tipo_egreso'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($te['etiqueta']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="caja-gasto__middle">
                    <?php if ($modo === 'PERSONAL'): ?>
                        <select class="caja-input caja-gasto__staff" style="flex:1">
                            <option value="<?= $g['ref_id'] ?>" selected><?= htmlspecialchars($g['descripcion']) ?></option>
                        </select>
                        <select class="caja-input caja-gasto__tipopago" style="max-width:165px">
                            <option value="MES_ACTUAL" <?= ($g['tipo_pago']??'MES_ACTUAL')==='MES_ACTUAL'?'selected':'' ?>>Pago Mes Actual</option>
                            <option value="MES_PASADO" <?= ($g['tipo_pago']??'')==='MES_PASADO'?'selected':'' ?>>Pago Mes Pasado</option>
                            <option value="PAGO_EXTRA" <?= ($g['tipo_pago']??'')==='PAGO_EXTRA' ?'selected':'' ?>>Pago Extra</option>
                        </select>
                    <?php elseif ($modo === 'LOCAL'): ?>
                        <select class="caja-input caja-gasto__local" style="max-width:120px">
                            <option value="<?= $g['ref_id'] ?>" selected><?= htmlspecialchars($g['descripcion']) ?></option>
                        </select>
                        <select class="caja-input caja-gasto__concepto" style="flex:1">
                            <?php if (!empty($g['concepto_id'])): ?>
                                <option value="<?= $g['concepto_id'] ?>" selected><?= htmlspecialchars($g['concepto_desc']??'') ?></option>
                            <?php else: ?><option value="">— Concepto —</option><?php endif; ?>
                        </select>
                        <input type="text" class="caja-input caja-gasto__comp" style="max-width:110px"
                               placeholder="N° comprobante" value="<?= htmlspecialchars($g['comprobante']??'') ?>">
                    <?php elseif ($modo === 'DEPOSITO'): ?>
                        <input type="text" class="caja-input caja-gasto__comp" style="flex:1"
                               placeholder="N° comprobante" value="<?= htmlspecialchars($g['comprobante']??'') ?>">
                    <?php elseif ($modo === 'LIBRE'): ?>
                        <input type="text" class="caja-input caja-gasto__desc" style="flex:1"
                               value="<?= htmlspecialchars($g['descripcion']??'') ?>" placeholder="Descripción del pago">
                    <?php elseif ($modo === 'FACTURA'): ?>
                        <select class="caja-input caja-gasto__tipodoc" style="max-width:140px">
                            <option value="BOLETA"        <?= ($g['tipo_documento']??'')==='BOLETA'        ?'selected':'' ?>>Boleta</option>
                            <option value="FACTURA"       <?= ($g['tipo_documento']??'')==='FACTURA'       ?'selected':'' ?>>Factura</option>
                            <option value="NOTA_DE_VENTA" <?= ($g['tipo_documento']??'')==='NOTA_DE_VENTA' ?'selected':'' ?>>Nota de venta</option>
                        </select>
                        <input type="text" class="caja-input caja-gasto__comp" style="flex:1"
                               placeholder="N° comprobante" value="<?= htmlspecialchars($g['comprobante']??'') ?>">
                    <?php endif; ?>
                </div>
                <div class="caja-input-money caja-gasto__monto">
                    <span>S/</span>
                    <input type="number" class="caja-input caja-input--money" value="<?= $g['monto'] ?>" min="0" step="0.01">
                </div>
                <button type="button" class="caja-gasto__remove">✕</button>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Formulario para agregar nuevo egreso -->
        <div id="gastoFormWrap" style="background:#f8fafc;border:1.5px solid #e2e8f0;
             border-radius:10px;padding:.85rem 1rem;margin-bottom:.5rem;">
            <p style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;
                      color:#64748b;margin:0 0 .65rem;">Nuevo egreso</p>
            <div style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:flex-start;">
                <select id="gastoTipoNew" class="caja-input" style="max-width:170px;" onchange="gastoTipoChanged()">
                    <?php foreach ($tiposEgreso ?? [] as $te): ?>
                    <option value="<?= $te['id_tipo_egreso'] ?>" data-modo="<?= $te['modo_ref'] ?>"
                            <?= $te['modo_ref'] === 'PERSONAL' ? 'selected' : '' ?>>
                        <?= htmlspecialchars($te['etiqueta']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <div id="gastoMiddleNew" style="display:flex;gap:.5rem;flex:1;flex-wrap:wrap;">
                    <!-- dinámico según tipo -->
                </div>
                <div class="caja-input-money" style="max-width:130px;">
                    <span>S/</span>
                    <input type="number" id="gastoMontoNew" class="caja-input caja-input--money"
                           min="0.01" step="0.01" placeholder="0.00"
                           oninput="if(this.value<0)this.value=''">
                </div>
                <button type="button" class="caja-btn caja-btn--outline" onclick="confirmarGasto()">
                    Agregar ✓
                </button>
            </div>
            <div id="gastoNewMsg" style="display:none;font-size:.78rem;margin-top:.4rem;color:#dc2626;"></div>
        </div>

        <div class="caja-subtotal">
            <span>Total gastos:</span>
            <strong id="totalGastos">S/ 0.00</strong>
        </div>
    </section>

    <!-- ── Comentarios del turno ────────────────────────────── -->
    <section class="caja-card" id="formComentario" <?= !$esEdicion ? 'hidden' : '' ?>>
        <h2 class="caja-card__title">5. Comentarios del turno</h2>
        <p class="caja-card__desc">¿Tuviste algún problema o algo que reportar? Escríbelo aquí antes de cerrar.</p>

        <textarea id="comentarioCajera" class="caja-input"
                  style="width:100%;resize:vertical;min-height:90px;font-family:inherit;box-sizing:border-box;"
                  maxlength="500"
                  placeholder="Describe cualquier incidencia del turno..."
                  oninput="actualizarContador(this)"><?= htmlspecialchars($sesion['comentario_cajera'] ?? '') ?></textarea>
        <div style="text-align:right;font-size:0.73rem;color:#94a3b8;margin-top:.2rem;">
            <span id="charCount"><?= mb_strlen($sesion['comentario_cajera'] ?? '') ?></span>/500
        </div>

        <div style="margin-top:.6rem;display:flex;gap:.75rem;align-items:center;">
            <button class="caja-btn caja-btn--outline" onclick="guardarComentario(<?= $sesionId ?>)">
                Guardar comentario
            </button>
            <span id="comentarioMsg" style="font-size:.82rem;display:none;color:#059669;">✓ Guardado</span>
        </div>

        <?php if (!empty($sesion['respuesta_admin'])): ?>
        <div style="margin-top:1rem;padding:.85rem 1rem;background:#eff6ff;border-left:4px solid #3b82f6;border-radius:0 8px 8px 0;">
            <p style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#3b82f6;margin:0 0 .35rem;">
                Respuesta del administrador
            </p>
            <p style="color:#1e293b;font-size:.86rem;margin:0;white-space:pre-wrap;"><?= htmlspecialchars($sesion['respuesta_admin']) ?></p>
        </div>
        <?php endif; ?>
    </section>

    <!-- ── Acciones ───────────────────────────────────────── -->
    <div class="caja-actions" id="formActions" <?= !$esEdicion ? 'hidden' : '' ?>>
        <button class="caja-btn caja-btn--secondary" onclick="guardarSesion(false)" id="btnGuardar">
            Guardar avance
        </button>
        <button class="caja-btn caja-btn--primary" onclick="confirmarCierre()" id="btnCerrar">
            Cerrar turno y registrar ventas →
        </button>
    </div>

</main>

<!-- Datos PHP para el JS -->
<script>
const BASE = '<?= $basePath ?>';
const ES_EDICION = <?= $esEdicion ? 'true' : 'false' ?>;
const SESION_ID  = <?= $sesionId ?: 'null' ?>;
const CONCEPTOS    = <?= json_encode($conceptos ?? []) ?>;
const STAFF        = <?= json_encode($staff ?? []) ?>;
const TIPOS_EGRESO    = <?= json_encode($tiposEgreso ?? []) ?>;
const MODOS           = <?= json_encode($modos ?? []) ?>;
const LOCALES         = <?= json_encode($locales ?? []) ?>;
const SOLOBANK_VALES  = <?= json_encode($soloBankVales ?? []) ?>;
</script>
<script>
function actualizarContador(el) {
    const cnt = document.getElementById('charCount');
    if (cnt) cnt.textContent = el.value.length;
}

async function guardarComentario(sesionId) {
    const comentario = document.getElementById('comentarioCajera')?.value ?? '';
    const msgEl      = document.getElementById('comentarioMsg');
    try {
        const r   = await fetch(`${BASE}/caja/api/sesion/${sesionId}/comentario`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body:   JSON.stringify({ comentario }),
        });
        const res = await r.json();
        if (res.success) {
            msgEl.style.display = 'inline';
            setTimeout(() => msgEl.style.display = 'none', 3000);
        }
    } catch {}
}
</script>
<script src="<?= $basePath ?>/assets/js/session-guard.js"></script>
<script src="<?= $basePath ?>/assets/js/caja.js"></script>
<script>
// ── Filtro de turnos según horario (solo cajera no-admin) ──
const ES_ADMIN_CAJA  = <?= $esAdminCaja ? 'true' : 'false' ?>;
const HORARIO_MAP    = <?= json_encode($horarioCajeraMap) ?>;
const TODOS_TURNOS   = <?= json_encode(array_values($turnos)) ?>;

function filtrarTurnos(localId) {
    const sel = document.getElementById('turnoId');
    if (!sel) return;
    sel.innerHTML = '';

    let turnosValidos = TODOS_TURNOS;

    if (!ES_ADMIN_CAJA && localId) {
        const ids = (HORARIO_MAP[localId] || []).map(Number);
        turnosValidos = TODOS_TURNOS.filter(t => ids.includes(parseInt(t.id)));
    }

    if (turnosValidos.length === 0) {
        sel.innerHTML = '<option value="">Sin turno asignado en este local hoy</option>';
    } else {
        sel.innerHTML = '<option value="">— Selecciona turno —</option>';
        turnosValidos.forEach(t => {
            const o = document.createElement('option');
            o.value = t.id; o.textContent = t.descripcion;
            sel.appendChild(o);
        });
    }

    // Resetear horario staff al cambiar local
    cargarStaffHorario();
}

// Si solo hay 1 local disponible, preseleccionarlo automáticamente
document.addEventListener('DOMContentLoaded', () => {
    const selLocal = document.getElementById('localId');
    if (selLocal && selLocal.options.length === 2) { // "— elige —" + 1 local
        selLocal.selectedIndex = 1;
        cargarCajas(selLocal.value);
        filtrarTurnos(selLocal.value);
    }
    // Inicializar visibilidad vendedora si ya hay caja seleccionada
    const cajaSelInit = document.getElementById('cajaId');
    if (cajaSelInit) actualizarCampoVendedora(cajaSelInit);
});
</script>
</body>
</html>
