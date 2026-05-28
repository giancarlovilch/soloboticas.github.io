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
$basePath  = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
$userName  = $userName  ?? $_SESSION['user_name'] ?? 'Usuario';
$locales   = $locales   ?? [];
$turnos    = $turnos    ?? [];
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
            <div class="caja-field">
                <label>Local <span class="req">*</span></label>
                <select id="localId" class="caja-input" onchange="cargarCajas(this.value)">
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
                <select id="turnoId" class="caja-input">
                    <?php foreach ($turnos as $t): ?>
                        <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['descripcion']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="caja-field">
                <label>Vendedor/a del turno <span class="req">*</span></label>
                <select id="vendedorId" class="caja-input">
                    <option value="">— Selecciona vendedor/a —</option>
                    <?php foreach ($staff as $s): ?>
                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nombre_completo']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="caja-base-info" style="font-size:0.82rem;color:#64748b;margin:.5rem 0;">
            Fecha de operación: <strong><?= date('d/m/Y') ?></strong>
        </div>
        <div class="caja-base-info" id="baseInfo" hidden>
            <p>Base del día anterior: <strong id="baseAmount">S/ 0.00</strong></p>
        </div>
        <button class="caja-btn caja-btn--primary" onclick="crearSesion()" id="btnCrear">
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
                $tipoPagoLabel = ['ADELANTO'=>'Adelanto','PAGO_TOTAL'=>'Pago total','DESCUENTO'=>'Descuento'];
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
                        <select class="caja-input caja-gasto__tipopago" style="max-width:130px">
                            <option value="PAGO_TOTAL" <?= ($g['tipo_pago']??'PAGO_TOTAL')==='PAGO_TOTAL'?'selected':'' ?>>Pago total</option>
                            <option value="ADELANTO"   <?= ($g['tipo_pago']??'')==='ADELANTO'  ?'selected':'' ?>>Adelanto</option>
                            <option value="DESCUENTO"  <?= ($g['tipo_pago']??'')==='DESCUENTO' ?'selected':'' ?>>Descuento</option>
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
</body>
</html>
