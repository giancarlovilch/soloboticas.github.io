<?php
$basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
$cajas    = $cajas   ?? [];
$cajeras  = $cajeras ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encuesta de <?= htmlspecialchars($mesLabel) ?> | Solo Boticas</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/staff.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/caja.css">
    <style>
        .ebcp-wrap { max-width: 640px; margin: 0 auto; padding: 1.25rem 1rem 3rem; }
        .ebcp-bloque { margin-bottom: 1.1rem; }
        .ebcp-bloque__title { font-size:.7rem; font-weight:800; text-transform:uppercase; letter-spacing:.05em;
            color:#92400e; margin-bottom:.35rem; line-height:1.3; }
        .ebcp-table-wrap { overflow-x: auto; background:#fff; border-radius:10px; box-shadow:0 1px 4px rgba(0,0,0,.06); }
        .ebcp-table { width: 100%; border-collapse: collapse; font-size: .76rem; }
        .ebcp-table th { background:#f8fafc; padding: 3px 6px; font-size:.62rem; font-weight:700; text-transform:uppercase;
            letter-spacing:.04em; color:#64748b; border-bottom:2px solid #e2e8f0; white-space:nowrap; }
        .ebcp-table td { padding: 1px 6px; border-bottom:1px solid #f1f5f9; vertical-align: middle; white-space:nowrap; }
        .ebcp-table tr:last-child td { border-bottom:none; }
        .ebcp-nombre { font-weight:600; color:#1e293b; }
        .ebcp-scale { display:flex; gap:1px; align-items:center; }
        .ebcp-siono { font-size:1.05rem; line-height:1; flex-shrink:0; }
        .ebcp-coin { font-size:.9rem; line-height:1; background:none; border:none; cursor:pointer; opacity:.2; padding:1px; }
        .ebcp-coin.on { opacity:1; }
        .ebcp-empty { text-align:center; padding:3rem; color:#94a3b8; }
    </style>
</head>
<body style="background:#f1f5f9;min-height:100vh;">

<header class="staff-header">
    <div class="staff-header__brand">
        <div class="staff-header__logo">SB</div>
        <div>
            <p class="staff-header__company">Grupo KGyR S.A.C</p>
            <p class="staff-header__app">Encuesta de <?= htmlspecialchars($mesLabel) ?></p>
        </div>
    </div>
    <div class="staff-header__user">
        <a href="<?= $basePath ?>/staff" class="staff-btn-logout" style="font-size:.78rem;">← Volver</a>
    </div>
</header>

<main class="ebcp-wrap">

    <div style="background:#fef2f2;border:1px solid #fecaca;border-left:4px solid #dc2626;border-radius:8px;padding:.7rem 1rem;margin-bottom:1rem;">
        <p style="color:#dc2626;font-weight:900;font-size:1.15rem;margin:0;">🔒 ENCUESTA ANÓNIMA</p>
        <p style="color:#991b1b;font-size:.72rem;margin:.15rem 0 0;">Tus respuestas se mantendrán confidenciales.</p>
    </div>

    <?php if (empty($cajeras)): ?>
    <div class="ebcp-empty">
        <div style="font-size:2.5rem;margin-bottom:.5rem;">🔥</div>
        <p style="font-weight:600;">No hay cajeras registradas para calificar este mes.</p>
    </div>
    <?php else: ?>

    <div id="ebcpMsg" class="caja-alert" hidden style="margin-bottom:.75rem;"></div>

    <!-- ── Bloque 1: nombres + turnos por caja ─────────────── -->
    <div class="ebcp-bloque">
        <div class="ebcp-bloque__title">👤 Cajera · turnos del mes</div>
        <div class="ebcp-table-wrap">
        <table class="ebcp-table">
            <thead>
                <tr>
                    <th>Cajera</th>
                    <?php foreach ($cajas as $c): ?>
                    <th class="text-center"><?= htmlspecialchars($c['descripcion']) ?></th>
                    <?php endforeach; ?>
                    <th class="text-center">Total</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($cajeras as $c): ?>
                <tr>
                    <td class="ebcp-nombre"><?= htmlspecialchars($c['nombre']) ?></td>
                    <?php foreach ($cajas as $caja): ?>
                    <td class="text-center" style="color:#94a3b8;"><?= (int)($c['porCaja'][$caja['id_caja']] ?? 0) ?></td>
                    <?php endforeach; ?>
                    <td class="text-center" style="font-weight:700;color:#1e293b;"><?= array_sum($c['porCaja']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>

    <!-- ── Bloque 2: nombres + pregunta 1 ──────────────────── -->
    <div class="ebcp-bloque">
        <div class="ebcp-bloque__title">🔥 <?= htmlspecialchars(VotoBcpRepository::PREGUNTAS['tarjeta_propia']) ?></div>
        <div class="ebcp-table-wrap">
        <table class="ebcp-table">
            <tbody>
            <?php foreach ($cajeras as $c): ?>
                <tr>
                    <td class="ebcp-nombre" style="width:38%;"><?= htmlspecialchars($c['nombre']) ?></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:.3rem;">
                            <span class="ebcp-siono" title="No">😇</span>
                            <div class="ebcp-scale" data-cajera="<?= $c['id'] ?>" data-preg="tarjeta_propia">
                                <?php for ($i = 1; $i <= 10; $i++): ?>
                                <button type="button" class="ebcp-coin" data-cajera="<?= $c['id'] ?>" data-preg="tarjeta_propia"
                                        data-val="<?= $i ?>" onclick="pickEbcp(this)">🔥</button>
                                <?php endfor; ?>
                            </div>
                            <span class="ebcp-siono" title="Sí">👿</span>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>

    <!-- ── Bloque 3: nombres + pregunta 2 ──────────────────── -->
    <div class="ebcp-bloque">
        <div class="ebcp-bloque__title">🔥 <?= htmlspecialchars(VotoBcpRepository::PREGUNTAS['fraccionamiento']) ?></div>
        <div class="ebcp-table-wrap">
        <table class="ebcp-table">
            <tbody>
            <?php foreach ($cajeras as $c): ?>
                <tr>
                    <td class="ebcp-nombre" style="width:38%;"><?= htmlspecialchars($c['nombre']) ?></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:.3rem;">
                            <span class="ebcp-siono" title="No">😇</span>
                            <div class="ebcp-scale" data-cajera="<?= $c['id'] ?>" data-preg="fraccionamiento">
                                <?php for ($i = 1; $i <= 10; $i++): ?>
                                <button type="button" class="ebcp-coin" data-cajera="<?= $c['id'] ?>" data-preg="fraccionamiento"
                                        data-val="<?= $i ?>" onclick="pickEbcp(this)">🔥</button>
                                <?php endfor; ?>
                            </div>
                            <span class="ebcp-siono" title="Sí">👿</span>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>

    <!-- ── Bloque 4: nombres + pregunta 3 (síntesis) ───────── -->
    <div class="ebcp-bloque">
        <div class="ebcp-bloque__title">🔥 <?= htmlspecialchars(VotoBcpRepository::PREGUNTAS['irregularidad']) ?></div>
        <div class="ebcp-table-wrap">
        <table class="ebcp-table">
            <tbody>
            <?php foreach ($cajeras as $c): ?>
                <tr>
                    <td class="ebcp-nombre" style="width:38%;"><?= htmlspecialchars($c['nombre']) ?></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:.3rem;">
                            <span class="ebcp-siono" title="No">😇</span>
                            <div class="ebcp-scale" data-cajera="<?= $c['id'] ?>" data-preg="irregularidad">
                                <?php for ($i = 1; $i <= 10; $i++): ?>
                                <button type="button" class="ebcp-coin" data-cajera="<?= $c['id'] ?>" data-preg="irregularidad"
                                        data-val="<?= $i ?>" onclick="pickEbcp(this)">🔥</button>
                                <?php endfor; ?>
                            </div>
                            <span class="ebcp-siono" title="Sí">👿</span>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>

    <!-- ── Bloque 5: nombres + pregunta 4 (sobrantes) ──────── -->
    <div class="ebcp-bloque">
        <div class="ebcp-bloque__title">🔥 <?= htmlspecialchars(VotoBcpRepository::PREGUNTAS['apropiacion_sobrante']) ?></div>
        <div class="ebcp-table-wrap">
        <table class="ebcp-table">
            <tbody>
            <?php foreach ($cajeras as $c): ?>
                <tr>
                    <td class="ebcp-nombre" style="width:38%;"><?= htmlspecialchars($c['nombre']) ?></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:.3rem;">
                            <span class="ebcp-siono" title="No">😇</span>
                            <div class="ebcp-scale" data-cajera="<?= $c['id'] ?>" data-preg="apropiacion_sobrante">
                                <?php for ($i = 1; $i <= 10; $i++): ?>
                                <button type="button" class="ebcp-coin" data-cajera="<?= $c['id'] ?>" data-preg="apropiacion_sobrante"
                                        data-val="<?= $i ?>" onclick="pickEbcp(this)">🔥</button>
                                <?php endfor; ?>
                            </div>
                            <span class="ebcp-siono" title="Sí">👿</span>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>

    <div class="caja-field" style="margin-top:1rem;">
        <label>Comentario anónimo (opcional, hasta 200 palabras)</label>
        <textarea id="ebcpComentario" class="caja-input" rows="3" maxlength="2000"
                  placeholder="Algo que quieras contarnos sobre este mes..."
                  oninput="ebcpActualizarContador()" style="resize:vertical;font-family:inherit;"></textarea>
        <span id="ebcpContador" style="font-size:.68rem;color:#94a3b8;">0 / 200 palabras</span>
    </div>

    <div class="caja-field" style="max-width:320px;margin-top:1rem;">
        <label>Tu contraseña <span class="req">*</span></label>
        <input type="password" id="ebcpPassword" class="caja-input" placeholder="Confirma con tu contraseña">
    </div>

    <button class="caja-btn caja-btn--primary" onclick="enviarEbcp()" id="ebcpBtnEnviar" style="margin-top:.75rem;width:100%;">
        Enviar encuesta →
    </button>
    <?php endif; ?>

</main>

<script>
const BASE = '<?= $basePath ?>';
const _votos = {};
const TOTAL_CAJERAS = <?= count($cajeras) ?>;

function ebcpContarPalabras(txt) {
    const t = txt.trim();
    return t === '' ? 0 : t.split(/\s+/).length;
}

function ebcpActualizarContador() {
    const n = ebcpContarPalabras(document.getElementById('ebcpComentario').value);
    const el = document.getElementById('ebcpContador');
    el.textContent = `${n} / 200 palabras`;
    el.style.color = n > 200 ? '#dc2626' : '#94a3b8';
}

function pickEbcp(btn) {
    const cajera = btn.dataset.cajera, preg = btn.dataset.preg, val = parseInt(btn.dataset.val);
    const actual = _votos[cajera]?.[preg];

    if (actual === val) {
        // Tocar de nuevo el mismo valor lo limpia (por si se marcó sin querer)
        document.querySelectorAll(`.ebcp-coin[data-cajera="${cajera}"][data-preg="${preg}"]`).forEach(b => {
            b.classList.remove('on');
        });
        if (_votos[cajera]) delete _votos[cajera][preg];
        return;
    }

    document.querySelectorAll(`.ebcp-coin[data-cajera="${cajera}"][data-preg="${preg}"]`).forEach(b => {
        b.classList.toggle('on', parseInt(b.dataset.val) <= val);
    });
    _votos[cajera] = _votos[cajera] || {};
    _votos[cajera][preg] = val;
}

async function enviarEbcp() {
    const msg = document.getElementById('ebcpMsg');
    const btn = document.getElementById('ebcpBtnEnviar');

    const PREGUNTAS = ['tarjeta_propia', 'fraccionamiento', 'irregularidad', 'apropiacion_sobrante'];
    const incompletas = Object.keys(_votos).length < TOTAL_CAJERAS
        || Object.values(_votos).some(v => PREGUNTAS.some(p => !v[p]));
    if (incompletas) {
        showAlert(msg, 'Completa las 4 preguntas para todas las cajeras antes de enviar.');
        return;
    }
    const comentario = document.getElementById('ebcpComentario').value.trim();
    if (ebcpContarPalabras(comentario) > 200) {
        showAlert(msg, 'El comentario no puede superar las 200 palabras.');
        return;
    }
    const password = document.getElementById('ebcpPassword').value.trim();
    if (!password) { showAlert(msg, 'Ingresa tu contraseña para confirmar.'); return; }

    btn.disabled = true; btn.textContent = 'Enviando...';
    try {
        const r = await fetch(`${BASE}/staff/api/encuesta-bcp/registrar`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ password, votos: _votos, comentario }),
        });
        const res = await r.json();
        if (res.success) {
            window.location.href = `${BASE}/staff/encuesta-bcp/resultados`;
        } else {
            showAlert(msg, res.message || 'Error al enviar.');
            btn.disabled = false; btn.textContent = 'Enviar encuesta →';
        }
    } catch {
        showAlert(msg, 'Error de conexión.');
        btn.disabled = false; btn.textContent = 'Enviar encuesta →';
    }
}

function showAlert(el, txt) {
    el.textContent = txt;
    el.className = 'caja-alert caja-alert--error';
    el.hidden = false;
}
</script>
</body>
</html>
