<?php
/** @var array $sesion */
$basePath      = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
$userName      = $userName      ?? $_SESSION['user_name'] ?? 'Usuario';
$surveyNeeded     = $surveyNeeded     ?? false;
$cajera_id        = $cajera_id        ?? 0;
$cajera_nombre    = $cajera_nombre    ?? '';
$vendedora_nombre = $vendedora_nombre ?? '';
$turno_id         = $turno_id         ?? 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingresar ventas | Caja SB</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/caja.css">
</head>
<body>

<header class="caja-header">
    <div class="caja-header__brand">
        <div class="caja-header__logo">SB</div>
        <div>
            <p class="caja-header__company">Grupo KGyR S.A.C</p>
            <p class="caja-header__app">Registro de ventas del turno</p>
        </div>
    </div>
    <div class="caja-header__right">
        <a href="<?= $basePath ?>/caja" class="caja-btn-back">← Volver</a>
    </div>
</header>

<main class="caja-main caja-main--narrow">

    <!-- Info de la sesión (sin montos, per policy) -->
    <section class="caja-card caja-card--info">
        <p class="caja-kicker">Sesión pendiente</p>
        <h2 class="caja-card__title" style="margin-bottom:0.5rem;">
            <?= htmlspecialchars($sesion['caja_desc']) ?> — <?= htmlspecialchars($sesion['local_desc']) ?>
        </h2>
        <div class="caja-info-row">
            <span><?= htmlspecialchars($sesion['turno_desc']) ?></span>
            <span><?= date('d/m/Y', strtotime($sesion['fecha_operacion'])) ?></span>
        </div>
        <div class="caja-info-row" style="margin-top:.35rem;">
            <span>Cajera: <strong><?= htmlspecialchars($cajera_nombre) ?></strong></span>
            <?php if ($vendedora_nombre): ?>
            <span>Vendedora: <strong><?= htmlspecialchars($vendedora_nombre) ?></strong></span>
            <?php endif; ?>
        </div>
        <div class="caja-ventas-note">
            El detalle financiero de esta sesión será visible en el reporte
            <strong>después</strong> de ingresar las ventas.
        </div>
    </section>

    <!-- Aviso importante -->
    <section class="caja-card" style="border-left:4px solid #dc2626;background:#fff5f5;">
        <p style="color:#dc2626;font-weight:700;font-size:.95rem;margin:0 0 .35rem;">
            ⚠ IMPORTANTE — Lee esto antes de ingresar las ventas
        </p>
        <p style="color:#7f1d1d;font-size:.85rem;margin:0;line-height:1.6;">
            Está <strong>prohibido</strong> solicitar el reporte de ventas al vendedor/a sin que la cajera
            haya cerrado previamente su turno con todos los datos de caja correctamente registrados
            (arqueo de efectivo, cobros electrónicos y egresos).
        </p>
    </section>

    <?php if ($surveyNeeded): ?>
    <!-- ── Encuesta obligatoria: vendedora evalúa a la cajera ── -->
    <section class="caja-card" style="border-left:4px solid #0097A7;">
        <h2 class="caja-card__title">📋 Evaluación de apertura — <?= htmlspecialchars($cajera_nombre) ?></h2>
        <p class="caja-card__desc">Completa la evaluación de la cajera para poder registrar tus ventas.</p>

        <div class="sv-block">
            <div class="sv-block__hd">⏰ Puntualidad al ingreso</div>
            <div class="sv-rg">
                <button type="button" class="sv-rb" data-color="blue"   data-field="llegada_puntualidad" data-val="MUY_TEMPRANO" onclick="pickVentasRb(this)">Muy anticipada <small>+10 min antes</small></button>
                <button type="button" class="sv-rb" data-color="green"  data-field="llegada_puntualidad" data-val="TEMPRANO"     onclick="pickVentasRb(this)">Con anticipación <small>menos de 10 min</small></button>
                <button type="button" class="sv-rb" data-color="orange" data-field="llegada_puntualidad" data-val="TARDE"        onclick="pickVentasRb(this)">Retraso leve <small>menos de 10 min</small></button>
                <button type="button" class="sv-rb" data-color="red"    data-field="llegada_puntualidad" data-val="MUY_TARDE"    onclick="pickVentasRb(this)">Retraso considerable <small>+10 min tarde</small></button>
            </div>
        </div>
        <div class="sv-block">
            <div class="sv-block__hd">🏪 Estado del área</div>
            <div class="sv-row2">
                <div class="sv-field"><span class="sv-field__label">¿Área ordenada?</span><div class="sv-rg">
                    <button type="button" class="sv-rb" data-color="green" data-field="area_ordenada_ingreso" data-val="1" onclick="pickVentasRb(this)">Sí</button>
                    <button type="button" class="sv-rb" data-color="red"   data-field="area_ordenada_ingreso" data-val="0" onclick="pickVentasRb(this)">No</button>
                </div></div>
                <div class="sv-field"><span class="sv-field__label">¿Área limpia?</span><div class="sv-rg">
                    <button type="button" class="sv-rb" data-color="green" data-field="area_limpia_ingreso" data-val="1" onclick="pickVentasRb(this)">Sí</button>
                    <button type="button" class="sv-rb" data-color="red"   data-field="area_limpia_ingreso" data-val="0" onclick="pickVentasRb(this)">No</button>
                </div></div>
            </div>
        </div>
        <div class="sv-block">
            <div class="sv-block__hd">👕 Presentación personal</div>
            <div class="sv-field"><span class="sv-field__label">Higiene personal</span><div class="sv-rg">
                <button type="button" class="sv-rb" data-color="red"   data-field="aseo_personal" data-val="DEFICIENTE" onclick="pickVentasRb(this)">Deficiente</button>
                <button type="button" class="sv-rb" data-color="amber" data-field="aseo_personal" data-val="ACEPTABLE"  onclick="pickVentasRb(this)">Aceptable</button>
                <button type="button" class="sv-rb" data-color="green" data-field="aseo_personal" data-val="OPTIMO"     onclick="pickVentasRb(this)">Óptimo</button>
            </div></div>
            <div class="sv-field"><span class="sv-field__label">Uniforme e indumentaria</span><div class="sv-rg">
                <button type="button" class="sv-rb" data-color="red"   data-field="vestimenta" data-val="DESCUIDADO"  onclick="pickVentasRb(this)">Descuidado</button>
                <button type="button" class="sv-rb" data-color="amber" data-field="vestimenta" data-val="PRESENTABLE" onclick="pickVentasRb(this)">Presentable</button>
                <button type="button" class="sv-rb" data-color="green" data-field="vestimenta" data-val="IMPECABLE"   onclick="pickVentasRb(this)">Impecable</button>
            </div></div>
            <div class="sv-row2">
                <div class="sv-field"><span class="sv-field__label">Estado de uñas</span><div class="sv-rg">
                    <button type="button" class="sv-rb" data-color="red"   data-field="unas" data-val="DESCUIDADAS" onclick="pickVentasRb(this)">Descuidadas</button>
                    <button type="button" class="sv-rb" data-color="amber" data-field="unas" data-val="ACEPTABLES"  onclick="pickVentasRb(this)">Aceptables</button>
                    <button type="button" class="sv-rb" data-color="green" data-field="unas" data-val="CUIDADAS"    onclick="pickVentasRb(this)">Cuidadas</button>
                </div></div>
                <div class="sv-field"><span class="sv-field__label">Presentación del cabello</span><div class="sv-rg">
                    <button type="button" class="sv-rb" data-color="red"   data-field="cabello" data-val="SUELTO"   onclick="pickVentasRb(this)">Suelto</button>
                    <button type="button" class="sv-rb" data-color="green" data-field="cabello" data-val="RECOGIDO" onclick="pickVentasRb(this)">Recogido</button>
                    <button type="button" class="sv-rb" data-color="green" data-field="cabello" data-val="MONO"     onclick="pickVentasRb(this)">Con moño</button>
                </div></div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Input de ventas -->
    <section class="caja-card">
        <h2 class="caja-card__title">Total de ventas del día (ERP)</h2>
        <p class="caja-card__desc">
            Ingresa el monto total de ventas según el sistema de ventas (ERP). Este valor es la suma
            de todas las ventas realizadas durante el turno.
        </p>

        <div class="caja-ventas-input-wrap">
            <label class="caja-ventas-label">Monto total de ventas</label>
            <div class="caja-input-money caja-input-money--lg">
                <span>S/</span>
                <input type="number" id="montoVentas" class="caja-input caja-input--money"
                       min="0" step="0.01" placeholder="0.00" autofocus>
            </div>
        </div>

        <?php if ($surveyNeeded): ?>
        <div class="caja-field" style="max-width:320px;margin-top:1rem;">
            <label>Tu contraseña (vendedora) <span class="req">*</span></label>
            <input type="password" id="ventasPwd" class="caja-input" placeholder="Confirma con tu contraseña">
        </div>
        <?php endif; ?>

        <div id="ventasMsg" class="caja-alert" hidden></div>

        <div class="caja-actions" style="margin-top:1.5rem;">
            <button class="caja-btn caja-btn--primary" onclick="submitVentas(<?= $sesion['id_sesion'] ?>)" id="btnVentas">
                Confirmar ventas y calcular cuadre →
            </button>
        </div>
    </section>

</main>

<script>
const BASE          = '<?= $basePath ?>';
const SURVEY_NEEDED = <?= $surveyNeeded ? 'true' : 'false' ?>;
const CAJERA_ID     = <?= (int)$cajera_id ?>;
const TURNO_ID_V    = <?= (int)$turno_id ?>;

/* Encuesta de cajera (vendedora la llena) */
const _ventasSurvey = {};
function pickVentasRb(btn) {
    const field = btn.dataset.field;
    document.querySelectorAll(`.sv-rb[data-field="${field}"]`).forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    _ventasSurvey[field] = btn.dataset.val;
}

async function submitVentas(sesionId) {
    const monto = parseFloat(document.getElementById('montoVentas').value);
    const msg   = document.getElementById('ventasMsg');
    const btn   = document.getElementById('btnVentas');

    if (isNaN(monto) || monto < 0) {
        showAlert(msg, 'Ingresa un monto válido (puede ser 0 si no hubo ventas).', 'error');
        return;
    }

    // Validar encuesta si es requerida
    if (SURVEY_NEEDED) {
        const required = ['llegada_puntualidad','area_ordenada_ingreso','area_limpia_ingreso',
                          'aseo_personal','vestimenta','unas','cabello'];
        if (required.some(f => _ventasSurvey[f] === undefined || _ventasSurvey[f] === '')) {
            showAlert(msg, 'Completa la evaluación de la cajera antes de confirmar.', 'error');
            return;
        }
        const pwd = document.getElementById('ventasPwd')?.value?.trim();
        if (!pwd) { showAlert(msg, 'Ingresa tu contraseña para confirmar.', 'error'); return; }

        btn.disabled = true; btn.textContent = 'Guardando evaluación...';

        // Registrar encuesta de la cajera
        const surveyPayload = {
            postulante_id: CAJERA_ID,
            fecha:         new Date().toLocaleDateString('en-CA'),
            turno_id:      TURNO_ID_V,
            seccion:       'ENTRADA',
            password:      pwd,
            ..._ventasSurvey,
        };
        try {
            const r1   = await fetch(`${BASE}/staff/api/asistencia/registrar`, {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body:   JSON.stringify(surveyPayload),
            });
            const res1 = await r1.json();
            if (!res1.success) {
                showAlert(msg, res1.message || 'Error al guardar la evaluación.', 'error');
                btn.disabled = false; btn.textContent = 'Confirmar ventas y calcular cuadre →';
                return;
            }
        } catch {
            showAlert(msg, 'Error de conexión al guardar la evaluación.', 'error');
            btn.disabled = false; btn.textContent = 'Confirmar ventas y calcular cuadre →';
            return;
        }
    }

    btn.disabled = true; btn.textContent = 'Procesando...';

    try {
        const r   = await fetch(`${BASE}/caja/api/${sesionId}/ventas`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ monto_ventas: monto }),
        });
        const res = await r.json();
        if (res.success) {
            window.location.href = `${BASE}/caja/reporte/${sesionId}`;
        } else {
            showAlert(msg, res.message || 'Error al procesar.', 'error');
            btn.disabled = false; btn.textContent = 'Confirmar ventas y calcular cuadre →';
        }
    } catch {
        showAlert(msg, 'Error de conexión.', 'error');
        btn.disabled = false; btn.textContent = 'Confirmar ventas y calcular cuadre →';
    }
}

function showAlert(el, txt, type) {
    el.textContent = txt;
    el.className   = `caja-alert caja-alert--${type}`;
    el.hidden      = false;
}
</script>

<style>
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
</body>
</html>
