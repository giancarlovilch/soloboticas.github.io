<?php
// Variables inyectadas por CajaController — el IDE no las ve, pero existen en runtime
/** @var array $sesion */
/** @var array $locales */
/** @var array $turnos */
/** @var array $conceptos */
/** @var array $staff */
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
                <label>Vendedor/a del turno</label>
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
                       min="0" step="1" placeholder="0"
                       value="<?= (int)($detalle['num_operaciones_bcp'] ?? 0) ?>"
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
                <option value="2">Yape</option>
                <option value="3">Plin</option>
                <option value="4">Visa / POS</option>
                <option value="5">BCP</option>
                <option value="6">Transferencia</option>
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
    </section>

    <!-- ── Gastos ─────────────────────────────────────────── -->
    <section class="caja-card" id="formGastos" <?= !$esEdicion ? 'hidden' : '' ?>>
        <div class="caja-card__header-row">
            <div>
                <h2 class="caja-card__title">4. Egresos del turno</h2>
                <p class="caja-card__desc">Registra todos los egresos: pagos a personal, gastos del local y otros conceptos.</p>
            </div>
            <button type="button" class="caja-btn caja-btn--outline" onclick="agregarGasto()">
                + Agregar gasto
            </button>
        </div>

        <div id="gastosContainer" class="caja-gastos-list">
            <?php foreach ($gastos ?? [] as $g): ?>
            <div class="caja-gasto-row" data-tipo="<?= htmlspecialchars($g['tipo']) ?>">
                <select class="caja-input caja-gasto__tipo" onchange="tipoChanged(this)">
                    <option value="PERSONAL" <?= $g['tipo'] === 'PERSONAL' ? 'selected':'' ?>>Pago personal</option>
                    <option value="LOCAL"    <?= $g['tipo'] === 'LOCAL'    ? 'selected':'' ?>>Gasto local</option>
                    <option value="OTRO"     <?= $g['tipo'] === 'OTRO'     ? 'selected':'' ?>>Otro</option>
                </select>
                <div class="caja-gasto__ref">
                    <?php if ($g['tipo'] === 'PERSONAL'): ?>
                        <select class="caja-input caja-gasto__staff">
                            <option value="<?= $g['ref_id'] ?>" selected><?= htmlspecialchars($g['descripcion']) ?></option>
                        </select>
                    <?php elseif ($g['tipo'] === 'LOCAL'): ?>
                        <select class="caja-input caja-gasto__concepto">
                            <option value="<?= $g['ref_id'] ?>" selected><?= htmlspecialchars($g['descripcion']) ?></option>
                        </select>
                    <?php else: ?>
                        <input type="text" class="caja-input caja-gasto__desc" value="<?= htmlspecialchars($g['descripcion']) ?>" placeholder="Descripción">
                    <?php endif; ?>
                </div>
                <div class="caja-input-money caja-gasto__monto">
                    <span>S/</span>
                    <input type="number" class="caja-input caja-input--money" value="<?= $g['monto'] ?>" min="0" step="0.01" oninput="recalcularGastos()">
                </div>
                <input type="text" class="caja-input caja-gasto__comp" placeholder="N° comprobante" value="<?= htmlspecialchars($g['comprobante'] ?? '') ?>">
                <button type="button" class="caja-gasto__remove" onclick="this.closest('.caja-gasto-row').remove(); recalcularGastos()">✕</button>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="caja-subtotal">
            <span>Total gastos:</span>
            <strong id="totalGastos">S/ 0.00</strong>
        </div>
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
const CONCEPTOS  = <?= json_encode($conceptos ?? []) ?>;
const STAFF      = <?= json_encode($staff ?? []) ?>;
</script>
<script src="<?= $basePath ?>/assets/js/caja.js"></script>
</body>
</html>
