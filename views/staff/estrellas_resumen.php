<?php
$basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
$meses    = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
[$anioF, $nmesF] = explode('-', $filtroMes);
$mesLabel     = $meses[(int)$nmesF - 1] . ' ' . $anioF;
$mesPasado    = date('Y-m', strtotime($desde . ' -1 month'));
$mesSiguiente = date('Y-m', strtotime($desde . ' +1 month'));
$diasLabel    = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
$turnoLabel   = [1 => '☀️ Mañana', 2 => '🌙 Tarde'];
$fmtEst = fn($v) => (floor($v) == $v) ? (string)(int)$v : number_format($v, 1);

$diferencia = (float)($estrellas['diferencia'] ?? 0);
$monto      = (float)($estrellas['monto']      ?? 0);
$enContra   = $diferencia > 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumen de estrellas | Solo Boticas</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/staff.css">
    <style>
        .er-wrap  { max-width:640px;margin:0 auto;padding:1rem 1rem 3rem; }
        .er-nav   { display:flex;align-items:center;gap:.5rem;margin-bottom:1.1rem;flex-wrap:wrap; }
        .er-nav a { padding:.38rem .9rem;border-radius:8px;font-size:.82rem;font-weight:600;
                    border:1.5px solid #bfdbfe;background:#eff6ff;color:#1d4ed8;text-decoration:none; }
        .er-mes   { font-size:.92rem;font-weight:700;color:#1e293b;padding:0 .5rem; }

        .er-kpis  { display:grid;grid-template-columns:repeat(2,1fr);gap:.65rem;margin-bottom:1rem; }
        .er-kpi   { background:#fff;border:1.5px solid #e2e8f0;border-radius:12px;padding:.85rem 1rem;text-align:center; }
        .er-kpi__num   { font-size:1.4rem;font-weight:800; }
        .er-kpi__label { font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#64748b;margin-top:2px; }

        .er-monto { border-radius:12px;padding:1rem 1.2rem;margin-bottom:1.25rem;text-align:center; }
        .er-monto--contra { background:#fee2e2;border:1.5px solid #fecaca; }
        .er-monto--favor  { background:#dbeafe;border:1.5px solid #bfdbfe; }
        .er-monto__label { font-size:.68rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.25rem; }
        .er-monto--contra .er-monto__label { color:#991b1b; }
        .er-monto--favor  .er-monto__label { color:#1d4ed8; }
        .er-monto__num   { font-size:1.5rem;font-weight:800; }
        .er-monto--contra .er-monto__num { color:#dc2626; }
        .er-monto--favor  .er-monto__num { color:#1d4ed8; }
        .er-monto__sub   { font-size:.7rem;color:#64748b;margin-top:.3rem; }

        .er-sec-title { font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;
                        color:#64748b;margin:1.25rem 0 .6rem; }
        .er-list { display:flex;flex-direction:column;gap:.5rem; }
        .er-item { background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:.65rem .85rem;
                   display:flex;align-items:center;justify-content:space-between;gap:.5rem; }
        .er-item__main { font-size:.82rem;font-weight:700;color:#1e293b; }
        .er-item__sub  { font-size:.7rem;color:#94a3b8;margin-top:1px; }
        .er-item__badge { font-size:.72rem;font-weight:800;color:#1d4ed8;white-space:nowrap; }
        .er-item__estado { font-size:.66rem;font-weight:700;padding:2px 8px;border-radius:20px;white-space:nowrap; }
        .er-item__estado--sancionado { background:#fee2e2;color:#991b1b; }

        .er-empty { text-align:center;padding:1.75rem;color:#94a3b8;font-size:.82rem;
                    background:#fff;border:1px solid #e2e8f0;border-radius:10px; }
        .er-anon  { font-size:.7rem;color:#94a3b8;margin-top:.3rem; }
    </style>
</head>
<body style="background:#f8fafc;min-height:100vh;">

<header class="staff-header">
    <div class="staff-header__brand">
        <div class="staff-header__logo">SB</div>
        <div>
            <p class="staff-header__company">Grupo KGyR S.A.C</p>
            <p class="staff-header__app">📋 Resumen de <span>estrellas</span></p>
        </div>
    </div>
    <div class="staff-header__user">
        <p class="staff-header__name"><?= htmlspecialchars($userName) ?></p>
        <a href="<?= $basePath ?>/staff" class="staff-btn-logout" style="font-size:.78rem;">← Volver</a>
    </div>
</header>

<main class="er-wrap">

    <div class="er-nav">
        <a href="?mes=<?= $mesPasado ?>">← Mes anterior</a>
        <span class="er-mes"><?= $mesLabel ?></span>
        <?php if ($filtroMes < $mesActual): ?>
        <a href="?mes=<?= $mesSiguiente ?>">Mes siguiente →</a>
        <?php endif; ?>
    </div>

    <div class="er-kpis">
        <div class="er-kpi">
            <div class="er-kpi__num" style="color:#dc2626;">🔴 <?= (int)$estrellas['rojas'] ?></div>
            <div class="er-kpi__label">Estrellas rojas</div>
        </div>
        <div class="er-kpi">
            <div class="er-kpi__num" style="color:#1d4ed8;">🔵 <?= $fmtEst($estrellas['azules']) ?></div>
            <div class="er-kpi__label">Estrellas azules</div>
        </div>
    </div>

    <div class="er-monto <?= $enContra ? 'er-monto--contra' : 'er-monto--favor' ?>">
        <div class="er-monto__label"><?= $enContra ? '⚠️ Diferencia en contra' : '✅ Diferencia a favor' ?></div>
        <div class="er-monto__num">
            <?= $fmtEst(abs($diferencia)) ?> ⭐ = S/ <?= number_format(abs($monto), 2) ?>
        </div>
        <div class="er-monto__sub">
            Cada mes arranca 50 vs 50 · cada ⭐ de diferencia equivale a S/ 0.10 al cierre del mes.
        </div>
    </div>

    <?php if (!empty($estrellas['azules_ajustes'])): ?>
    <div class="er-empty" style="background:#fef2f2;border-color:#fecaca;color:#991b1b;text-align:left;margin-bottom:1.25rem;">
        ⚖️ Ajustes por sanciones este mes: <strong><?= $estrellas['azules_ajustes'] > 0 ? '+' : '' ?><?= (int)$estrellas['azules_ajustes'] ?> ⭐</strong>
    </div>
    <?php endif; ?>

    <?php if (!empty($estrellas['votos_emitidos'])): ?>
    <div class="er-empty" style="background:#eff6ff;border-color:#bfdbfe;color:#1d4ed8;text-align:left;margin-bottom:1.25rem;">
        🗳️ Por calificar a tus compañeros este mes: <strong>+<?= $fmtEst($estrellas['azules_votos']) ?> ⭐</strong>
        (<?= (int)$estrellas['votos_emitidos'] ?> voto<?= $estrellas['votos_emitidos'] == 1 ? '' : 's' ?> emitido<?= $estrellas['votos_emitidos'] == 1 ? '' : 's' ?> · se te da a ti por participar, no a quien calificas)
    </div>
    <?php endif; ?>

    <p class="er-sec-title">🧹 Actividades reconocidas</p>
    <p class="er-anon">Se muestra qué te calificaron y cuánto ganaste — sin decir quién lo hizo.</p>
    <?php if (empty($detalleTareas)): ?>
    <div class="er-empty">Aún no tienes actividades reconocidas en <?= $mesLabel ?>.</div>
    <?php else: ?>
    <div class="er-list" style="margin-top:.6rem;">
        <?php foreach ($detalleTareas as $d):
            $dow = $diasLabel[(int)date('w', strtotime($d['fecha']))];
        ?>
        <div class="er-item">
            <div>
                <div class="er-item__main"><?= htmlspecialchars($d['tarea']) ?></div>
                <div class="er-item__sub"><?= $dow ?> <?= date('d/m', strtotime($d['fecha'])) ?> · <?= htmlspecialchars($d['local_desc']) ?> · <?= htmlspecialchars($d['turno_desc']) ?></div>
            </div>
            <div style="text-align:right;">
                <?php if ($d['sancionado']): ?>
                <div class="er-item__badge" style="color:#dc2626;">0 ⭐</div>
                <span class="er-item__estado er-item__estado--sancionado">🚫 Sancionado</span>
                <?php else: ?>
                <div class="er-item__badge">+<?= $fmtEst($d['azules']) ?> ⭐</div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <p class="er-sec-title">🗳️ Votos que diste</p>
    <p class="er-anon">Cada vez que calificas a un compañero ganas ⭐ azules tú, independiente de la calificación que le des a él/ella.</p>
    <?php if (empty($detalleVotos)): ?>
    <div class="er-empty">Aún no has calificado a ningún compañero en <?= $mesLabel ?>.</div>
    <?php else: ?>
    <div class="er-list" style="margin-top:.6rem;">
        <?php foreach ($detalleVotos as $v):
            $dow = $diasLabel[(int)date('w', strtotime($v['fecha']))];
        ?>
        <div class="er-item">
            <div>
                <div class="er-item__main">Calificaste a <?= htmlspecialchars($v['beneficiario_nombre']) ?></div>
                <div class="er-item__sub"><?= htmlspecialchars($v['tarea']) ?> · <?= $dow ?> <?= date('d/m', strtotime($v['fecha'])) ?> · <?= htmlspecialchars($v['local_desc']) ?> · <?= htmlspecialchars($v['turno_desc']) ?></div>
            </div>
            <div class="er-item__badge">+<?= $fmtEst($v['azul_ganado']) ?> ⭐</div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <p class="er-sec-title">📅 Turnos asistidos</p>
    <?php if (empty($detalleTurnos)): ?>
    <div class="er-empty">Sin turnos registrados en <?= $mesLabel ?>.</div>
    <?php else: ?>
    <div class="er-list">
        <?php foreach ($detalleTurnos as $t):
            $dow = $diasLabel[(int)date('w', strtotime($t['fecha']))];
        ?>
        <div class="er-item">
            <div>
                <div class="er-item__main"><?= $dow ?> <?= date('d/m', strtotime($t['fecha'])) ?></div>
                <div class="er-item__sub"><?= htmlspecialchars($t['local_desc'] ?? '—') ?> · <?= $turnoLabel[$t['turno_id']] ?? 'Turno' ?></div>
            </div>
            <div class="er-item__badge" style="color:#dc2626;">+<?= (int)$t['tasa_roja'] ?> 🔴</div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</main>

<script src="<?= $basePath ?>/assets/js/session-guard.js"></script>
</body>
</html>
