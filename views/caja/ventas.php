<?php
/** @var array $sesion */
$basePath      = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
$userName      = $userName      ?? $_SESSION['user_name'] ?? 'Usuario';
$surveyNeeded     = $surveyNeeded     ?? false;
$cajera_id        = $cajera_id        ?? 0;
$cajera_nombre    = $cajera_nombre    ?? '';
$vendedora_nombre = $vendedora_nombre ?? '';
$turno_id         = $turno_id         ?? 0;

// Encuesta de apertura (ficha de desempeño, 1-10) — misma versión que /caja/sesion/nueva
$svAspectos = [
    'puntualidad'  => ['label' => '⏰ Puntualidad',          'icono' => '⏰', 'hint' => '¿Llegó puntual a su turno?',              'malo' => '😞', 'bueno' => '😊'],
    'orden'        => ['label' => '🗂️ Orden',                'icono' => '🗂️', 'hint' => '¿Encontró/dejó su área ordenada?',        'malo' => '😞', 'bueno' => '😊'],
    'higiene'      => ['label' => '🧼 Higiene',               'icono' => '🧼', 'hint' => '¿Higiene personal impecable?',            'malo' => '😞', 'bueno' => '😊'],
    'presentacion' => ['label' => '✨ Presentación personal', 'icono' => '✨', 'hint' => '¿Uniforme e imagen impecables?',          'malo' => '😞', 'bueno' => '😊'],
    'animo'        => ['label' => '🔥 Estado de ánimo',       'icono' => '🔥', 'hint' => '',                                        'malo' => '😠', 'bueno' => '😊'],
    'uso_celular'  => ['label' => '📵 Alejado del celular',   'icono' => '📵', 'hint' => '¿Se mantuvo alejada del teléfono?',       'malo' => '😞', 'bueno' => '😊'],
    'confianza'    => ['label' => '🛡️ Confianza / Honestidad','icono' => '🛡️', 'hint' => '¿Trabajó con ética y sin hacer trampa?', 'malo' => '😞', 'bueno' => '😊'],
];
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
    <!-- ── Encuesta obligatoria: vendedora evalúa a la cajera (ficha 1-10) ── -->
    <section class="caja-card" style="border-left:4px solid #0097A7;">
        <h2 class="caja-card__title">📋 Evaluación de apertura — <?= htmlspecialchars($cajera_nombre) ?></h2>
        <p class="caja-card__desc">Califica del 1 al 10 para poder registrar tus ventas.</p>

        <?php foreach ($svAspectos as $campo => $a):
            $esConfianza = $campo === 'confianza';
        ?>
        <div class="sv-block<?= $esConfianza ? ' sv-block--confianza' : '' ?>">
            <div class="sv-block__hd">
                <span><?= $a['label'] ?></span>
                <span class="sv-block__val" id="sv-val-<?= $campo ?>">—/10</span>
            </div>
            <?php if (!empty($a['hint'])): ?>
            <p style="font-size:.68rem;color:<?= $esConfianza ? '#6d28d9' : '#94a3b8' ?>;margin:-.2rem 0 .45rem;"><?= $a['hint'] ?></p>
            <?php endif; ?>
            <div style="display:flex;align-items:center;gap:.4rem;">
                <?php if (!empty($a['malo'])): ?><span style="font-size:1.25rem;" title="Mal ánimo"><?= $a['malo'] ?></span><?php endif; ?>
                <div class="sv-scale" data-field="<?= $campo ?>">
                    <?php for ($i = 1; $i <= 10; $i++): ?>
                    <button type="button" class="sv-coin" data-field="<?= $campo ?>" data-val="<?= $i ?>"
                            onclick="pickVentasScale(this)"><?= $a['icono'] ?></button>
                    <?php endfor; ?>
                </div>
                <?php if (!empty($a['bueno'])): ?><span style="font-size:1.25rem;" title="Buen ánimo"><?= $a['bueno'] ?></span><?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
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

/* Encuesta de cajera (vendedora la llena) — ficha de desempeño 1-10 */
const SURVEY_ASPECTOS_V = ['puntualidad','orden','higiene','presentacion','animo','uso_celular','confianza'];
const _ventasSurvey = {};
function pickVentasScale(btn) {
    const field = btn.dataset.field;
    const val = parseInt(btn.dataset.val);
    document.querySelectorAll(`.sv-coin[data-field="${field}"]`).forEach(b => {
        b.classList.toggle('on', parseInt(b.dataset.val) <= val);
    });
    _ventasSurvey[field] = val;
    document.getElementById(`sv-val-${field}`).textContent = `${val}/10`;
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
        if (SURVEY_ASPECTOS_V.some(f => !_ventasSurvey[f])) {
            showAlert(msg, 'Completa las 7 preguntas de la evaluación antes de confirmar.', 'error');
            return;
        }
        const pwd = document.getElementById('ventasPwd')?.value?.trim();
        if (!pwd) { showAlert(msg, 'Ingresa tu contraseña para confirmar.', 'error'); return; }

        btn.disabled = true; btn.textContent = 'Guardando evaluación...';
        const fecha = new Date().toLocaleDateString('en-CA');

        // 1. Ficha de asistencia de la cajera (mantiene el conteo de estrellas rojas / bonos).
        //    Puntualidad detallada ya no se pide; se deriva del puntaje (>=6 => A TIEMPO).
        const asistenciaPayload = {
            postulante_id: CAJERA_ID, fecha, turno_id: TURNO_ID_V,
            seccion: 'ENTRADA', password: pwd,
        };
        if (_ventasSurvey['puntualidad'] < 6) asistenciaPayload.llegada_puntualidad = 'TARDE';

        try {
            const r1   = await fetch(`${BASE}/staff/api/asistencia/registrar`, {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body:   JSON.stringify(asistenciaPayload),
            });
            const res1 = await r1.json();
            if (!res1.success) {
                showAlert(msg, res1.message || 'Error al guardar la asistencia.', 'error');
                btn.disabled = false; btn.textContent = 'Confirmar ventas y calcular cuadre →';
                return;
            }
        } catch {
            showAlert(msg, 'Error de conexión al guardar la asistencia.', 'error');
            btn.disabled = false; btn.textContent = 'Confirmar ventas y calcular cuadre →';
            return;
        }

        // 2. Encuesta de desempeño (nueva ficha cuantitativa, visible en
        //    /staff/mi-horario?modo=mis-encuestas de la cajera evaluada).
        const encuestaPayload = {
            evaluado_id: CAJERA_ID, fecha, turno_id: TURNO_ID_V,
            password: pwd, ..._ventasSurvey,
        };
        try {
            const rEnc   = await fetch(`${BASE}/staff/api/encuesta/registrar`, {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body:   JSON.stringify(encuestaPayload),
            });
            const resEnc = await rEnc.json();
            if (!resEnc.success) {
                showAlert(msg, resEnc.message || 'Error al guardar la encuesta.', 'error');
                btn.disabled = false; btn.textContent = 'Confirmar ventas y calcular cuadre →';
                return;
            }
        } catch {
            showAlert(msg, 'Error de conexión al guardar la encuesta.', 'error');
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
            window.location.href = `${BASE}/caja`;
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
    .sv-block { background:#f8fafc;border-radius:10px;padding:.7rem .85rem;margin-bottom:.6rem;border:1px solid #e8edf2; }
    .sv-block--confianza { background:#f5f3ff;border-color:#ddd6fe; }
    .sv-block__hd { font-size:.78rem;font-weight:800;color:#1e293b;margin-bottom:.5rem;display:flex;align-items:center;justify-content:space-between; }
    .sv-block__val { font-size:.72rem;font-weight:700;color:#64748b; }
    .sv-scale { display:flex;gap:3px;flex-wrap:wrap; }
    .sv-coin { font-size:1.35rem;line-height:1;background:none;border:none;cursor:pointer;opacity:.22;padding:2px;transition:opacity .1s,transform .1s; }
    .sv-coin.on { opacity:1; }
    .sv-coin:active { transform:scale(1.2); }
</style>
</body>
</html>
