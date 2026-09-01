<?php
$basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
$modo     = $modo ?? 'pendientes';

// Paleta decorativa 1..10 (roja→verde). Para "ánimo" se usa invertida (más fuego = peor).
$paleta = ['#ef4444','#f0653a','#f4854a','#f5a83c','#eab308','#bef264','#a3e635','#4ade80','#22c55e','#16a34a'];
$paletaInv = array_reverse($paleta);

// Cada aspecto con su propio sticker, variado y alegre.
$aspectos = [
    'puntualidad'  => ['label' => '⏰ Puntualidad',         'icono' => '⏰', 'paleta' => $paleta,
                        'hint' => '¿Llegó puntual a su turno?', 'malo' => '😞', 'bueno' => '😊'],
    'orden'        => ['label' => '🗂️ Orden',               'icono' => '🗂️', 'paleta' => $paleta,
                        'hint' => '¿Trabajó de forma ordenada y dejó su área en orden?', 'malo' => '😞', 'bueno' => '😊'],
    'higiene'      => ['label' => '🧼 Higiene',              'icono' => '🧼', 'paleta' => $paleta,
                        'hint' => '¿Mantuvo una higiene personal impecable?', 'malo' => '😞', 'bueno' => '😊'],
    'presentacion' => ['label' => '✨ Presentación personal','icono' => '✨', 'paleta' => $paleta,
                        'hint' => '¿Se presentó con uniforme e imagen impecables?', 'malo' => '😞', 'bueno' => '😊'],
    'animo'        => ['label' => '🔥 Estado de ánimo',      'icono' => '🔥', 'paleta' => $paletaInv,
                        'hint' => '', 'malo' => '😠', 'bueno' => '😊'],
    'uso_celular'  => ['label' => '📵 Alejado del celular',  'icono' => '📵', 'paleta' => $paleta,
                        'hint' => '¿Se mantuvo alejado del teléfono durante su turno?', 'malo' => '😞', 'bueno' => '😊'],
    'confianza'    => ['label' => '🛡️ Confianza / Honestidad','icono' => '🛡️', 'paleta' => $paleta,
                        'hint' => '¿Trabajó con ética, moral y valores, sin hacer trampa?', 'malo' => '😞', 'bueno' => '😊'],
];

// ── Variables modo "mis-encuestas" ────────────────────
if ($modo === 'mis-encuestas') {
    $mesesNomCompleto = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
    $mesLabel     = $mesesNomCompleto[(int)date('n', strtotime($desde)) - 1] . ' ' . date('Y', strtotime($desde));
    $mesPasado    = date('Y-m', strtotime($desde . ' -1 month'));
    $mesSiguiente = date('Y-m', strtotime($desde . ' +1 month'));
    $mesActual    = $mesActual ?? date('Y-m');
    $diasLabel    = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
    $turnoLabel   = [1 => '☀️ Mañana', 2 => '🌙 Tarde'];
    $promedios    = $promedios ?? [];
    $detalle      = $detalle   ?? [];
}

// ── Variables modo "pendientes" ───────────────────────
if ($modo === 'pendientes') {
    $slotsData        = $slotsData        ?? [];
    $desde            = $desde            ?? date('Y-m-01');
    $hasta            = $hasta            ?? date('Y-m-d');
    $filtroTrabajador = $filtroTrabajador ?? 0;
    $soloSinCalif     = $soloSinCalif     ?? false;

    $sinCalificar = count(array_filter($slotsData, fn($s) => !$s['id_encuesta']));
    $total        = count($slotsData);
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
        .mh-btn { border:none;border-radius:6px;padding:5px 12px;font-size:.74rem;font-weight:700;cursor:pointer;white-space:nowrap; }
        .mh-btn--calificar { background:#0097A7;color:#fff; }
        .mh-btn--filled    { background:#7c3aed; }

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
        .mh-modal { background:#fff;border-radius:14px;padding:1.5rem;width:620px;max-width:96vw;box-shadow:0 20px 60px rgba(0,0,0,.22);max-height:92vh;overflow-y:auto; }
        .mh-modal h3 { font-size:1rem;font-weight:700;margin:0 0 .25rem;color:#1e293b; }
        .mh-modal-sub { font-size:.75rem;color:#64748b;margin-bottom:1rem; }
        .mh-err { font-size:.75rem;color:#dc2626;margin-bottom:.5rem;display:none;background:#fee2e2;border-radius:8px;padding:.5rem .7rem; }
        .mh-modal__footer { display:flex;gap:.5rem;justify-content:flex-end;margin-top:.75rem; }
        .mh-modal__footer button { border:none;border-radius:7px;padding:.5rem 1.1rem;font-size:.82rem;font-weight:700;cursor:pointer; }

        /* Bloques de aspecto (escala 1-10 tipo monedas) */
        .mh-block { background:#f8fafc;border-radius:10px;padding:.7rem .85rem;margin-bottom:.6rem;border:1px solid #e8edf2; }
        .mh-block--confianza { background:#f5f3ff;border-color:#ddd6fe; }
        .mh-block__hd { font-size:.78rem;font-weight:800;color:#1e293b;margin-bottom:.5rem;display:flex;align-items:center;justify-content:space-between; }
        .mh-block__val { font-size:.72rem;font-weight:700;color:#64748b; }
        .mh-scale { display:flex;gap:3px;flex-wrap:wrap; }
        .mh-coin { font-size:1.35rem;line-height:1;background:none;border:none;cursor:pointer;opacity:.22;padding:2px;transition:opacity .1s,transform .1s; }
        .mh-coin.on { opacity:1; }
        .mh-coin:active { transform:scale(1.2); }

        .mh-pwd { display:flex;flex-direction:column;gap:.2rem;margin-top:.75rem;border-top:1px solid #f1f5f9;padding-top:.75rem; }
        .mh-pwd label { font-size:.75rem;font-weight:600;color:#475569; }
        .mh-pwd input { padding:.5rem .75rem;border:1.5px solid #e2e8f0;border-radius:8px;font-size:.85rem;outline:none;width:100%;box-sizing:border-box; }

        /* Promedios (modo mis-encuestas) */
        .mh-prom-grid { display:grid;grid-template-columns:repeat(3,1fr);gap:.65rem;margin-bottom:1.25rem; }
        .mh-prom-card { background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:.8rem .9rem;text-align:center; }
        .mh-prom-card__num   { font-size:1.35rem;font-weight:800;color:#0097A7; }
        .mh-prom-card__label { font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#64748b;margin-top:2px; }
        .mh-prom-bar { height:6px;border-radius:99px;background:#e2e8f0;margin-top:.4rem;overflow:hidden; }
        .mh-prom-bar__fill { height:100%;background:linear-gradient(90deg,#f59e0b,#22c55e); }

        @media(max-width:640px){ .mh-kpis{grid-template-columns:repeat(2,1fr);} .mh-prom-grid{grid-template-columns:repeat(2,1fr);} }
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
<!-- ── Modal de calificación (solo en modo pendientes) ── -->
<div id="mhModal" class="mh-ov" hidden>
    <div class="mh-modal">
        <h3 id="mhModalTitulo"></h3>
        <p id="mhModalDesc" class="mh-modal-sub"></p>
        <div id="mhErr" class="mh-err"></div>

        <?php foreach ($aspectos as $campo => $a):
            $esConfianza = $campo === 'confianza';
        ?>
        <div class="mh-block<?= $esConfianza ? ' mh-block--confianza' : '' ?>">
            <div class="mh-block__hd">
                <span><?= $a['label'] ?></span>
                <span class="mh-block__val" id="val-<?= $campo ?>">—/10</span>
            </div>
            <?php if (!empty($a['hint'])): ?>
            <p style="font-size:.68rem;color:<?= $esConfianza ? '#6d28d9' : '#94a3b8' ?>;margin:-.2rem 0 .45rem;"><?= $a['hint'] ?></p>
            <?php endif; ?>
            <div style="display:flex;align-items:center;gap:.4rem;">
                <?php if (!empty($a['malo'])): ?><span style="font-size:1.25rem;" title="Mal ánimo"><?= $a['malo'] ?></span><?php endif; ?>
                <div class="mh-scale" data-field="<?= $campo ?>">
                    <?php for ($i = 1; $i <= 10; $i++): ?>
                    <button type="button" class="mh-coin" data-field="<?= $campo ?>" data-val="<?= $i ?>"
                            style="color:<?= $a['paleta'][$i-1] ?>;" onclick="pickScale(this)"><?= $a['icono'] ?></button>
                    <?php endfor; ?>
                </div>
                <?php if (!empty($a['bueno'])): ?><span style="font-size:1.25rem;" title="Buen ánimo"><?= $a['bueno'] ?></span><?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>

        <div class="mh-pwd">
            <label>Tu contraseña para confirmar *</label>
            <input type="password" id="mhPassword" placeholder="Tu contraseña de acceso">
        </div>
        <div class="mh-modal__footer">
            <button onclick="cerrarModal()" style="background:#f1f5f9;color:#475569;">Cancelar</button>
            <button onclick="confirmarRegistro()" style="background:#0097A7;color:#fff;">Guardar</button>
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
        <?php if ($sinCalificar > 0): ?>
        · <strong style="color:#dc2626;"><?= $sinCalificar ?> sin calificar por ti</strong>
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
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($slotsData as $s):
            $yaCalificado = (bool)$s['id_encuesta'];
            $encData = $yaCalificado ? array_intersect_key($s, array_flip([
                'puntualidad','orden','higiene','presentacion','animo','uso_celular','confianza',
            ])) : null;
            $encJson = htmlspecialchars(json_encode($encData), ENT_QUOTES);
            $diasLabel = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
            $dow  = $diasLabel[(int)date('w', strtotime($s['fecha_dia']))];
            $diaN = date('d/m', strtotime($s['fecha_dia']));
        ?>
        <tr>
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
                <?php if ($yaCalificado): ?>
                    <span class="mh-badge" style="background:#ede9fe;color:#5b21b6;">Calificado por ti</span>
                <?php else: ?>
                    <span style="color:#cbd5e1;font-size:.75rem;">Pendiente</span>
                <?php endif; ?>
            </td>
            <td>
                <div class="mh-acc">
                    <button class="mh-btn mh-btn--calificar <?= $yaCalificado ? 'mh-btn--filled' : '' ?>"
                        data-pid="<?= $s['postulante_id'] ?>"
                        data-fecha="<?= $s['fecha_dia'] ?>"
                        data-turno="<?= $s['turno_id'] ?>"
                        data-nombre="<?= htmlspecialchars($s['trabajador_nombre'], ENT_QUOTES) ?>"
                        data-enc="<?= $encJson ?>"
                        onclick="abrirModal(this)">
                        <?= $yaCalificado ? '✏ Editar' : 'Calificar' ?>
                    </button>
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
        👤 Esta es tu ficha personal. <strong>Solo tus compañeros pueden calificarte — nunca a ti mismo.</strong>
        No se muestra quién te calificó, solo el resultado.
    </div>

    <!-- KPI general -->
    <div class="mh-kpis">
        <div class="mh-kpi"><div class="mh-kpi__num" style="color:#0097A7;"><?= (int)($promedios['total_encuestas'] ?? 0) ?></div><div class="mh-kpi__label">Encuestas recibidas</div></div>
    </div>

    <!-- Promedios por aspecto -->
    <p style="font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#64748b;margin-bottom:.65rem;">Tus promedios (0 a 10)</p>
    <?php if (empty($promedios['total_encuestas'])): ?>
    <div style="text-align:center;padding:3rem;color:#94a3b8;">
        <div style="font-size:2.5rem;margin-bottom:.5rem;">📊</div>
        <p style="font-weight:600;">Aún no tienes encuestas en <?= $mesLabel ?></p>
    </div>
    <?php else: ?>
    <div class="mh-prom-grid">
        <?php foreach ($aspectos as $campo => $a):
            $v = $promedios[$campo] ?? null;
            $pct = $v !== null ? min(100, ($v / 10) * 100) : 0;
            $esConfianza = $campo === 'confianza';
        ?>
        <div class="mh-prom-card"<?= $esConfianza ? ' style="background:#f5f3ff;border-color:#ddd6fe;"' : '' ?>>
            <div class="mh-prom-card__num"<?= $esConfianza ? ' style="color:#6d28d9;"' : '' ?>><?= $v !== null ? number_format($v, 2) : '—' ?></div>
            <div class="mh-prom-card__label"><?= $a['label'] ?></div>
            <div class="mh-prom-bar"><div class="mh-prom-bar__fill" style="width:<?= $pct ?>%;"></div></div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Detalle anónimo -->
    <p class="mh-sub" style="font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#64748b;margin:1.5rem 0 .65rem;">Detalle recibido</p>
    <?php if (empty($detalle)): ?>
    <div style="text-align:center;padding:1.5rem;color:#94a3b8;font-size:.82rem;background:#fff;border:1px solid #e2e8f0;border-radius:10px;">
        Sin encuestas en <?= $mesLabel ?>.
    </div>
    <?php else: ?>
    <div class="mh-table-wrap">
    <table class="mh-table">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Turno</th>
                <th class="text-center">⏰ Puntual.</th>
                <th class="text-center">🗂️ Orden</th>
                <th class="text-center">🧼 Higiene</th>
                <th class="text-center">✨ Present.</th>
                <th class="text-center">🔥 Ánimo</th>
                <th class="text-center">📵 Celular</th>
                <th class="text-center">🛡️ Confianza</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($detalle as $d):
            $dow = $diasLabel[(int)date('w', strtotime($d['fecha']))];
        ?>
        <tr>
            <td style="white-space:nowrap;">
                <strong style="color:#1e293b;"><?= $dow ?></strong>
                <span class="mh-sub"><?= date('d/m', strtotime($d['fecha'])) ?></span>
            </td>
            <td><?= $turnoLabel[$d['turno_id']] ?? $d['turno_desc'] ?></td>
            <td class="text-center"><?= (int)$d['puntualidad'] ?></td>
            <td class="text-center"><?= (int)$d['orden'] ?></td>
            <td class="text-center"><?= (int)$d['higiene'] ?></td>
            <td class="text-center"><?= (int)$d['presentacion'] ?></td>
            <td class="text-center"><?= (int)$d['animo'] ?></td>
            <td class="text-center"><?= (int)$d['uso_celular'] ?></td>
            <td class="text-center"><?= (int)$d['confianza'] ?></td>
        </tr>
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
const ASPECTOS = <?= json_encode(array_keys($aspectos)) ?>;

let _pid = 0, _fecha = '', _turnoId = 0;
let _scaleVals = {};

function fmtFecha(f) {
    const d = new Date(f + 'T12:00:00');
    const dias = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
    return `${dias[d.getDay()]} ${d.getDate()}/${String(d.getMonth()+1).padStart(2,'0')}`;
}

function pickScale(btn) {
    const field = btn.dataset.field;
    const val = parseInt(btn.dataset.val);
    document.querySelectorAll(`.mh-coin[data-field="${field}"]`).forEach(b => {
        b.classList.toggle('on', parseInt(b.dataset.val) <= val);
    });
    _scaleVals[field] = val;
    document.getElementById(`val-${field}`).textContent = `${val}/10`;
}

function preselectScale(field, val) {
    if (!val) return;
    document.querySelectorAll(`.mh-coin[data-field="${field}"]`).forEach(b => {
        b.classList.toggle('on', parseInt(b.dataset.val) <= val);
    });
    _scaleVals[field] = val;
    document.getElementById(`val-${field}`).textContent = `${val}/10`;
}

function clearAll() {
    document.querySelectorAll('.mh-coin').forEach(b => b.classList.remove('on'));
    ASPECTOS.forEach(a => document.getElementById(`val-${a}`).textContent = '—/10');
    _scaleVals = {};
}

function abrirModal(btn) {
    _pid = parseInt(btn.dataset.pid);
    _fecha = btn.dataset.fecha; _turnoId = parseInt(btn.dataset.turno);
    const exist = JSON.parse(btn.dataset.enc || 'null');
    clearAll();
    document.getElementById('mhPassword').value = '';
    document.getElementById('mhErr').style.display = 'none';
    document.getElementById('mhModalTitulo').textContent = `Calificar a ${btn.dataset.nombre}`;
    document.getElementById('mhModalDesc').textContent = `${fmtFecha(_fecha)} · Confirma con TU propia contraseña.`;
    if (exist) {
        Object.keys(exist).forEach(k => preselectScale(k, exist[k]));
    }
    document.getElementById('mhModal').removeAttribute('hidden');
    setTimeout(() => document.getElementById('mhPassword').focus(), 80);
}

function cerrarModal() { document.getElementById('mhModal').setAttribute('hidden', ''); }

async function confirmarRegistro() {
    const password = document.getElementById('mhPassword').value.trim();
    const err = document.getElementById('mhErr');
    if (!password) { showErr(err, 'Tu contraseña es requerida.'); return; }
    const faltantes = ASPECTOS.filter(a => !_scaleVals[a]);
    if (faltantes.length) { showErr(err, 'Completa las 7 preguntas antes de guardar.'); return; }

    const payload = { evaluado_id: _pid, fecha: _fecha, turno_id: _turnoId, password, ..._scaleVals };
    try {
        const r = await fetch(`${BASE}/staff/api/encuesta/registrar`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload),
        });
        const res = await r.json();
        if (res.success) { cerrarModal(); location.reload(); }
        else showErr(err, res.message || 'Error.');
    } catch { showErr(err, 'Error de conexión.'); }
}

function showErr(el, msg) { el.textContent = msg; el.style.display = 'block'; }
</script>
<?php endif; ?>
</body>
</html>
