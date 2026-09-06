<?php
$basePath   = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
$locales    = $locales    ?? [];
$tareas     = $tareas     ?? [];
$dias       = $dias       ?? [];
$turnos     = $turnos     ?? [];
$matrizValores = $matrizValores ?? [];
$mapaRegistros = $mapaRegistros ?? [];
$staffActivo   = $staffActivo   ?? [];
$ranking       = $ranking       ?? [];
$isAdmin       = $isAdmin       ?? false;
$tabInicial    = $tabInicial    ?? 'asignar';
$hoy           = $hoy           ?? date('Y-m-d');

$diasSemana = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
$iconos     = ['BARRER' => '🧹', 'TRAPEAR' => '🪣'];
$colorSlot  = function (int $i): string { return 'local-c' . (($i % 4) + 1); };

$meses    = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
[$anioF, $nmesF] = explode('-', $filtroMes);
$mesLabel     = $meses[(int)$nmesF - 1] . ' ' . $anioF;
$mesPasado    = date('Y-m', strtotime($desdeMes . ' -1 month'));
$mesSiguiente = date('Y-m', strtotime($desdeMes . ' +1 month'));
$fmtEst = fn($v) => (floor($v) == $v) ? (string)(int)$v : number_format($v, 1);

$diferencia = (float)($estrellas['diferencia'] ?? 0);
$monto      = (float)($estrellas['monto']      ?? 0);
$enContra   = $diferencia > 0;

$posLider    = $ranking[0]['pos'] ?? null;
$lideres     = $posLider !== null ? array_filter($ranking, fn($r) => $r['pos'] === $posLider) : [];
$sinLimpiar  = array_filter($ranking, fn($r) => $r['total'] === 0);

$staffActivoJson = json_encode($staffActivo, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Limpieza | Solo Boticas</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/staff.css">
    <style>
        .li-wrap { width:80%;margin:0 auto;padding:.7rem 0 2rem;box-sizing:border-box; }

        .li-tabs { display:flex;gap:.4rem;margin-bottom:.7rem; }
        .li-tab  { flex:1;border:1.5px solid #e2e8f0;background:#fff;color:#64748b;border-radius:10px;
                   padding:.6rem .5rem;font-size:.8rem;font-weight:700;cursor:pointer; }
        .li-tab.active { background:#eff6ff;border-color:#bfdbfe;color:#1d4ed8; }
        .li-panel { display:none; }
        .li-panel.active { display:block; }

        /* ── Ganar estrellas ── */
        .li-badges { display:flex;flex-wrap:wrap;gap:.4rem;margin-bottom:.6rem; }
        .li-badge  { font-size:.72rem;font-weight:700;padding:.32rem .65rem;border-radius:99px;
                     background:#fef7dc;color:#8a5a00;border:1px solid rgba(234,179,8,.35);white-space:nowrap; }
        .li-badge--ghost { background:transparent;border:1px dashed #e2e8f0;color:#94a3b8; }

        .li-grid-wrap { overflow-x:auto;border:1px solid #e2e8f0;border-radius:12px;-webkit-overflow-scrolling:touch; }
        table.li-grid { border-collapse:collapse;width:100%;font-size:.72rem; }
        table.li-grid th, table.li-grid td { padding:.32rem .3rem;text-align:center;border-bottom:1px solid #eef1f5;white-space:nowrap; }
        table.li-grid thead tr:first-child th { background:#fff;font-size:.64rem;font-weight:700;color:#64748b;
            text-transform:uppercase;letter-spacing:.03em; }
        table.li-grid thead tr:last-child th { background:#fff;font-size:.68rem;font-weight:700;color:#1e293b;border-bottom:2px solid #cbd5e1; }
        table.li-grid td.fecha { font-weight:700;text-align:left;padding-left:.6rem;color:#1e293b;vertical-align:middle; }
        table.li-grid td.li-turno { font-size:.66rem;font-weight:600;color:#64748b;text-align:left;white-space:nowrap; }
        /* Línea marcada entre un día y el siguiente, para no confundir turnos de días distintos */
        tr.li-dia-inicio td { border-top:2px solid #cbd5e1; }
        tr.row-hoy td { border-top:2px solid #1d4ed8; }
        tr.row-hoy td.fecha { color:#1d4ed8; }
        .li-pill-hoy { display:inline-block;margin-left:.3rem;font-size:.6rem;font-weight:800;color:#fff;
                       background:#1d4ed8;padding:.05rem .4rem;border-radius:99px;vertical-align:middle; }

        .local-c1 { background:#eef0ff; } thead .local-c1 { color:#4338ca; }
        .local-c2 { background:#ecfdf6; } thead .local-c2 { color:#047857; }
        .local-c3 { background:#fff6ea; } thead .local-c3 { color:#b45309; }
        .local-c4 { background:#fdf2f8; } thead .local-c4 { color:#be185d; }
        td.local-c2, th.local-c2, td.local-c3, th.local-c3, td.local-c4, th.local-c4 { border-left:1px solid #e2e8f0; }

        .li-name  { display:inline-flex;align-items:center;gap:.2rem;background:#fff;border:1px solid #e2e8f0;
                    border-radius:8px;padding:.25rem .45rem;font-weight:600;color:#1e293b;font-size:.72rem;
                    max-width:80px;overflow:hidden;text-overflow:ellipsis; }
        .li-name--admin { cursor:pointer; }
        .li-name--admin:hover { border-color:#93c5fd; }
        .li-add   { width:28px;height:28px;border-radius:50%;border:1.5px dashed #1d4ed8;background:transparent;
                    color:#1d4ed8;font-size:1rem;font-weight:800;cursor:pointer;line-height:1;
                    display:inline-flex;align-items:center;justify-content:center; }
        .li-add:hover { background:#eff6ff; }

        .li-future  { margin-top:.6rem;padding:.55rem .8rem;border-radius:10px;background:#f8fafc;
                      border:1px dashed #e2e8f0;color:#64748b;font-size:.75rem;text-align:center; }
        .li-legend  { display:flex;gap:1rem;flex-wrap:wrap;margin-top:.7rem;font-size:.7rem;color:#64748b; }

        /* ── Modal ── */
        .li-ov { position:fixed;inset:0;background:rgba(15,23,42,.5);z-index:500;display:none;align-items:center;justify-content:center; }
        .li-modal { background:#fff;border-radius:14px;padding:1.4rem;width:400px;max-width:92vw;box-shadow:0 20px 60px rgba(0,0,0,.22); }
        .li-modal h3 { font-size:1rem;font-weight:800;margin:0 0 .3rem;color:#1e293b; }
        .li-modal p.li-ctx { font-size:.82rem;color:#64748b;margin-bottom:1rem; }
        .li-modal label { font-size:.75rem;font-weight:600;color:#475569;display:block;margin-bottom:.3rem; }
        .li-modal select, .li-modal input { padding:.6rem .8rem;border:1.5px solid #e2e8f0;border-radius:10px;
            font-size:.9rem;outline:none;width:100%;box-sizing:border-box;margin-bottom:.85rem; }
        .li-modal-err { font-size:.78rem;color:#dc2626;margin-bottom:.6rem;display:none;background:#fee2e2;border-radius:8px;padding:.5rem .7rem; }
        .li-modal-actions { display:flex;gap:.5rem; }
        .li-modal-actions button { flex:1;padding:.65rem;border:none;border-radius:8px;font-size:.85rem;font-weight:700;cursor:pointer; }
        .li-btn-cancel  { background:#f1f5f9;color:#475569; }
        .li-btn-confirm { background:#1d4ed8;color:#fff; }
        .li-btn-confirm:disabled { opacity:.6;cursor:not-allowed; }
        .li-btn-quitar { width:100%;padding:.5rem;border:1px solid #fecaca;border-radius:8px;background:#fff;
                         color:#dc2626;font-size:.78rem;font-weight:700;cursor:pointer;margin-bottom:.7rem; }
        .li-btn-quitar:hover { background:#fef2f2; }
        .li-btn-quitar:disabled { opacity:.6;cursor:not-allowed; }
        .li-legado-item { display:flex;align-items:center;justify-content:space-between;gap:.6rem;
                           background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:.5rem .7rem; }
        .li-legado-item__txt { font-size:.78rem;color:#1e293b; }
        .li-legado-item__txt small { display:block;color:#94a3b8;font-size:.68rem;margin-top:1px; }
        .li-legado-item__del { background:none;border:1px solid #fecaca;border-radius:6px;padding:4px 9px;
                                font-size:.7rem;font-weight:700;cursor:pointer;color:#dc2626;white-space:nowrap; }
        .li-legado-item__del:disabled { opacity:.5;cursor:not-allowed; }

        /* ── Ranking ── */
        .li-nav   { display:flex;align-items:center;gap:.5rem;margin-bottom:1.1rem;flex-wrap:wrap; }
        .li-nav a { padding:.38rem .9rem;border-radius:8px;font-size:.82rem;font-weight:600;
                    border:1.5px solid #bfdbfe;background:#eff6ff;color:#1d4ed8;text-decoration:none; }
        .li-mes   { font-size:.92rem;font-weight:700;color:#1e293b;padding:0 .5rem; }

        .li-kpis  { display:grid;grid-template-columns:1fr 1fr;gap:.6rem;margin-bottom:1.1rem; }
        .li-kpi   { border-radius:12px;padding:.8rem .9rem;border:1px solid #e2e8f0; }
        .li-kpi--up   { background:#e3f9ee;border-color:#bbf7d0; }
        .li-kpi--down { background:#fee2e2;border-color:#fecaca; }
        .li-kpi__label { font-size:.62rem;font-weight:800;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.3rem; }
        .li-kpi--up .li-kpi__label   { color:#0f9d68; }
        .li-kpi--down .li-kpi__label { color:#dc2626; }
        .li-kpi__names { font-size:.8rem;font-weight:700;color:#1e293b;line-height:1.4; }
        .li-kpi__sub   { font-size:.68rem;color:#64748b;margin-top:.2rem; }

        table.li-rank { width:100%;border-collapse:collapse;font-size:.8rem;margin-bottom:.6rem; }
        table.li-rank th { text-align:left;font-size:.64rem;text-transform:uppercase;letter-spacing:.04em;color:#64748b;
                           padding:.4rem .4rem;border-bottom:1px solid #e2e8f0; }
        table.li-rank td { padding:.5rem .4rem;border-bottom:1px solid #e2e8f0; }
        table.li-rank td.pos  { font-weight:800;color:#94a3b8;width:1.6em; }
        table.li-rank td.num  { text-align:center; }
        table.li-rank td.total{ text-align:center;font-weight:800;color:#1d4ed8; }
        tr.li-rank-first td.pos { color:#eab308; }
        tr.li-rank-cero { background:#fef2f2; }
        tr.li-rank-cero td.total { color:#dc2626; }
        .li-caption { font-size:.7rem;color:#94a3b8;margin:0 0 1.4rem; }

        /* ── Balance rojas/azules (heredado del resumen) ── */
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
            <p class="staff-header__app">🧹 Sistema de <span>Limpieza</span></p>
        </div>
    </div>
    <div class="staff-header__user">
        <p class="staff-header__name"><?= htmlspecialchars($userName) ?></p>
        <a href="<?= $basePath ?>/staff" class="staff-btn-logout" style="font-size:.78rem;">← Volver</a>
    </div>
</header>

<main class="li-wrap">

    <div class="li-tabs">
        <button class="li-tab <?= $tabInicial === 'asignar' ? 'active' : '' ?>" id="liTabAsignar" onclick="liTab('asignar')">🧹 Ganar estrellas</button>
        <button class="li-tab <?= $tabInicial === 'ranking' ? 'active' : '' ?>" id="liTabRanking" onclick="liTab('ranking')">🏆 Ranking</button>
    </div>

    <!-- ============ GANAR ESTRELLAS ============ -->
    <section class="li-panel <?= $tabInicial === 'asignar' ? 'active' : '' ?>" id="liPanelAsignar">

        <div class="li-badges">
            <?php foreach ($tareas as $t): ?>
            <span class="li-badge"><?= $iconos[$t['codigo']] ?? '🧺' ?> <?= htmlspecialchars($t['descripcion']) ?> · base <?= (int)$t['estrellas_max'] ?>⭐</span>
            <?php endforeach; ?>
            <span class="li-badge li-badge--ghost">+ más actividades, próximamente</span>
        </div>
        <p style="font-size:.68rem;color:#94a3b8;margin:0 0 .6rem;">El valor puede variar por local — revisa el número debajo del ícono de cada columna.</p>

        <?php if (empty($locales) || empty($tareas)): ?>
        <div class="er-empty">Aún no hay locales o actividades de limpieza configuradas.</div>
        <?php else: ?>
        <div class="li-grid-wrap">
            <table class="li-grid">
                <thead>
                    <tr>
                        <th colspan="2"></th>
                        <?php foreach ($locales as $i => $l): ?>
                        <th colspan="<?= count($tareas) ?>" class="<?= $colorSlot($i) ?>"><?= htmlspecialchars($l['descripcion']) ?></th>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <th><?= ucfirst($meses[(int)date('n')-1]) ?></th>
                        <th>Turno</th>
                        <?php foreach ($locales as $i => $l): foreach ($tareas as $t):
                            $valorCol = $matrizValores[$t['id_tarea']][$l['id_local']] ?? (int)$t['estrellas_max'];
                        ?>
                        <th class="<?= $colorSlot($i) ?>" title="<?= htmlspecialchars($t['descripcion']) ?> en <?= htmlspecialchars($l['descripcion']) ?>: <?= $valorCol ?>⭐">
                            <?= $iconos[$t['codigo']] ?? '🧺' ?><br><small style="font-weight:600;color:inherit;"><?= $valorCol ?>⭐</small>
                        </th>
                        <?php endforeach; endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php $numTurnos = count($turnos) ?: 1; ?>
                    <?php foreach ($dias as $idx => $d):
                        $fecha = $d['fecha'];
                        $esHoy = $fecha === $hoy;
                        $dow   = $diasSemana[(int)date('w', strtotime($fecha))];
                        $dia   = (int)date('j', strtotime($fecha));
                        $primeraFilaDelDia = ($idx % $numTurnos) === 0;
                    ?>
                    <tr class="<?= $esHoy ? 'row-hoy' : ($primeraFilaDelDia ? 'li-dia-inicio' : '') ?>">
                        <?php if ($primeraFilaDelDia): ?>
                        <td class="fecha" rowspan="<?= $numTurnos ?>"><?= $dow ?> <?= $dia ?><?php if ($esHoy): ?><span class="li-pill-hoy">HOY</span><?php endif; ?></td>
                        <?php endif; ?>
                        <td class="li-turno">T. <?= htmlspecialchars($d['turno_desc']) ?></td>
                        <?php foreach ($locales as $i => $l): foreach ($tareas as $t):
                            $clave = $l['id_local'] . '_' . $t['id_tarea'] . '_' . $d['turno_id'] . '_' . $fecha;
                            $reg   = $mapaRegistros[$clave] ?? null;
                            $ctx   = htmlspecialchars($t['descripcion'] . ' · ' . $l['descripcion'] . ' · ' . $dow . ' ' . $dia . ' · T. ' . $d['turno_desc'], ENT_QUOTES);
                        ?>
                        <td class="<?= $colorSlot($i) ?>">
                            <?php if ($reg): ?>
                                <?php
                                $puedeReasignar = $isAdmin && !$reg['legado'] && $reg['id'] !== null;
                                $puedeVerLegado = $isAdmin && $reg['legado'];
                                $votosJson = $puedeVerLegado
                                    ? json_encode($reg['votos'], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG)
                                    : '[]';
                                ?>
                                <span class="li-name <?= ($puedeReasignar || $puedeVerLegado) ? 'li-name--admin' : '' ?>"
                                      title="<?= $reg['legado'] ? htmlspecialchars($reg['nombre']) . ' — ya registrado antes del cambio de sistema' : htmlspecialchars($reg['nombre']) ?>"
                                      <?php if ($puedeReasignar): ?>
                                      onclick="liAbrirModal({tareaId:<?= (int)$t['id_tarea'] ?>, localId:<?= (int)$l['id_local'] ?>, turnoId:<?= (int)$d['turno_id'] ?>, fecha:'<?= $fecha ?>', ctx:'<?= $ctx ?>', actualId:<?= (int)$reg['id'] ?>}, this)"
                                      <?php elseif ($puedeVerLegado): ?>
                                      onclick="liAbrirModalLegado(JSON.parse('<?= $votosJson ?>'), {tareaId:<?= (int)$t['id_tarea'] ?>, localId:<?= (int)$l['id_local'] ?>, turnoId:<?= (int)$d['turno_id'] ?>, fecha:'<?= $fecha ?>', ctx:'<?= $ctx ?>'}, this)"
                                      <?php endif; ?>
                                ><?= htmlspecialchars($reg['legado'] ? $reg['nombre'] : explode(' ', $reg['nombre'])[0]) ?><?= $reg['legado'] ? ' •' : '' ?></span>
                            <?php else: ?>
                                <button class="li-add" title="Registrar quién limpió: <?= $ctx ?>"
                                    onclick="liAbrirModal({tareaId:<?= (int)$t['id_tarea'] ?>, localId:<?= (int)$l['id_local'] ?>, turnoId:<?= (int)$d['turno_id'] ?>, fecha:'<?= $fecha ?>', ctx:'<?= $ctx ?>', actualId:null}, this)">+</button>
                            <?php endif; ?>
                        </td>
                        <?php endforeach; endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="li-future">Los días después de hoy se habilitan cuando lleguen.</div>

        <div class="li-legend">
            <span>🔒 Ya asignado — solo un admin lo cambia</span>
            <span>➕ Libre — tócalo para decir quién limpió</span>
            <span>• Ya quedó registrado antes del cambio de sistema — no se puede reasignar</span>
        </div>
        <?php endif; ?>
    </section>

    <!-- ============ RANKING ============ -->
    <section class="li-panel <?= $tabInicial === 'ranking' ? 'active' : '' ?>" id="liPanelRanking">

        <div class="li-nav">
            <a href="?tab=ranking&mes=<?= $mesPasado ?>">← Mes anterior</a>
            <span class="li-mes"><?= $mesLabel ?></span>
            <?php if ($filtroMes < $mesActual): ?>
            <a href="?tab=ranking&mes=<?= $mesSiguiente ?>">Mes siguiente →</a>
            <?php endif; ?>
        </div>

        <?php if (!empty($lideres)): ?>
        <div class="li-kpis">
            <div class="li-kpi li-kpi--up">
                <div class="li-kpi__label">🏆 Liderando</div>
                <div class="li-kpi__names"><?= htmlspecialchars(implode(' · ', array_column($lideres, 'nombre'))) ?></div>
                <div class="li-kpi__sub"><?= (int)$ranking[0]['total'] ?> limpieza<?= $ranking[0]['total'] == 1 ? '' : 's' ?><?= count($lideres) > 1 ? ' — empate' : '' ?></div>
            </div>
            <?php if (!empty($sinLimpiar)): ?>
            <div class="li-kpi li-kpi--down">
                <div class="li-kpi__label">⚠️ Sin limpiar este mes</div>
                <div class="li-kpi__names"><?= htmlspecialchars(implode(' · ', array_column($sinLimpiar, 'nombre'))) ?></div>
                <div class="li-kpi__sub">0 limpiezas en <?= $mesLabel ?></div>
            </div>
            <?php else: ?>
            <div class="li-kpi li-kpi--up">
                <div class="li-kpi__label">✅ Todos participaron</div>
                <div class="li-kpi__names">Nadie está en cero este mes</div>
            </div>
            <?php endif; ?>
        </div>

        <div class="li-grid-wrap">
            <table class="li-rank">
                <thead>
                    <tr>
                        <th>#</th><th>Trabajador</th>
                        <?php foreach ($tareas as $t): ?><th><?= $iconos[$t['codigo']] ?? '🧺' ?></th><?php endforeach; ?>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ranking as $r): ?>
                    <tr class="<?= $r['pos'] === 1 ? 'li-rank-first' : '' ?> <?= $r['total'] === 0 ? 'li-rank-cero' : '' ?>">
                        <td class="pos"><?= (int)$r['pos'] ?></td>
                        <td><?= htmlspecialchars($r['nombre']) ?></td>
                        <?php foreach ($tareas as $t): ?><td class="num"><?= (int)($r['tareas'][$t['codigo']] ?? 0) ?></td><?php endforeach; ?>
                        <td class="total"><?= $r['total'] === 0 ? '⚠️ 0' : (int)$r['total'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p class="li-caption">Ordenado de mayor a menor. Nadie califica a nadie: es el conteo de lo que cada quien se anotó.</p>
        <?php else: ?>
        <div class="er-empty">Nadie ha registrado limpieza en <?= $mesLabel ?> todavía.</div>
        <?php endif; ?>

        <!-- ── Balance rojas/azules del mes (heredado del resumen) ── -->
        <p class="er-sec-title">⚖️ Tu balance del mes</p>
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

        <p class="er-sec-title">🧹 Actividades reconocidas</p>
        <?php if (empty($detalleTareas)): ?>
        <div class="er-empty">Aún no tienes actividades reconocidas en <?= $mesLabel ?>.</div>
        <?php else: ?>
        <div class="er-list" style="margin-top:.6rem;">
            <?php foreach ($detalleTareas as $d):
                $dow = $diasSemana[(int)date('w', strtotime($d['fecha']))];
            ?>
            <div class="er-item">
                <div>
                    <div class="er-item__main"><?= htmlspecialchars($d['tarea']) ?></div>
                    <div class="er-item__sub"><?= $dow ?> <?= date('d/m', strtotime($d['fecha'])) ?> · <?= htmlspecialchars($d['local_desc']) ?><?= !empty($d['turno_desc']) ? ' · ' . htmlspecialchars($d['turno_desc']) : '' ?></div>
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

        <p class="er-sec-title">📅 Turnos asistidos</p>
        <?php if (empty($detalleTurnos)): ?>
        <div class="er-empty">Sin turnos registrados en <?= $mesLabel ?>.</div>
        <?php else: ?>
        <div class="er-list">
            <?php foreach ($detalleTurnos as $t):
                $dow = $diasSemana[(int)date('w', strtotime($t['fecha']))];
            ?>
            <div class="er-item">
                <div>
                    <div class="er-item__main"><?= $dow ?> <?= date('d/m', strtotime($t['fecha'])) ?></div>
                    <div class="er-item__sub"><?= htmlspecialchars($t['local_desc'] ?? '—') ?></div>
                </div>
                <div class="er-item__badge" style="color:#dc2626;">+<?= (int)$t['tasa_roja'] ?> 🔴</div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </section>

</main>

<!-- Modal de asignación -->
<div id="liOverlay" class="li-ov">
    <div class="li-modal">
        <h3 id="liModalTitulo">¿Quién hizo esta limpieza?</h3>
        <p class="li-ctx" id="liModalCtx"></p>
        <label>¿Quién limpió?</label>
        <select id="liSelectAsignarA"></select>
        <label>Tu contraseña para confirmar *</label>
        <input type="password" id="liPassword" placeholder="Tu contraseña de acceso">
        <div id="liModalErr" class="li-modal-err"></div>
        <button class="li-btn-quitar" id="liBtnQuitar" onclick="liQuitar()" style="display:none;">🗑️ Quitar asignación (dejar libre)</button>
        <div class="li-modal-actions">
            <button class="li-btn-cancel" onclick="liCerrarModal()">Cancelar</button>
            <button class="li-btn-confirm" id="liBtnConfirmar" onclick="liConfirmar()">Confirmar</button>
        </div>
    </div>
</div>

<!-- Modal de registros del sistema anterior (voto entre compañeros) -->
<div id="liOverlayLegado" class="li-ov">
    <div class="li-modal">
        <h3>Registros del sistema anterior</h3>
        <p class="li-ctx" id="liLegadoCtx"></p>
        <p style="font-size:.76rem;color:#64748b;margin:0 0 .8rem;">
            Esto viene del sistema viejo de votos entre compañeros. Si alguien marcó una limpieza que no pasó, elimínala aquí.
        </p>
        <div id="liLegadoLista" style="display:flex;flex-direction:column;gap:.5rem;margin-bottom:.9rem;"></div>
        <label>Tu contraseña para confirmar *</label>
        <input type="password" id="liLegadoPassword" placeholder="Tu contraseña de acceso">
        <div id="liLegadoErr" class="li-modal-err"></div>
        <div class="li-modal-actions">
            <button class="li-btn-cancel" onclick="liCerrarModalLegado()">Cerrar</button>
        </div>
    </div>
</div>

<script src="<?= $basePath ?>/assets/js/session-guard.js"></script>
<script>
const BASE = '<?= $basePath ?>';
const IS_ADMIN = <?= $isAdmin ? 'true' : 'false' ?>;
const MI_ID = <?= (int)$postulanteId ?>;
const STAFF_ACTIVO = <?= $staffActivoJson ?: '[]' ?>;

function liTab(which) {
    const isAsignar = which === 'asignar';
    document.getElementById('liTabAsignar').classList.toggle('active', isAsignar);
    document.getElementById('liTabRanking').classList.toggle('active', !isAsignar);
    document.getElementById('liPanelAsignar').classList.toggle('active', isAsignar);
    document.getElementById('liPanelRanking').classList.toggle('active', !isAsignar);
    const url = new URL(window.location);
    url.searchParams.set('tab', which);
    history.replaceState(null, '', url);
}

let liCtxActual = null;
let liCeldaActual = null;

function liAbrirModal(ctx, celda) {
    liCtxActual = ctx;
    liCeldaActual = celda;
    document.getElementById('liModalCtx').textContent = ctx.ctx;
    document.getElementById('liPassword').value = '';
    document.getElementById('liModalErr').style.display = 'none';

    const esReasignacion = ctx.actualId !== null;
    document.getElementById('liModalTitulo').textContent = esReasignacion ? 'Reasignar esta limpieza' : '¿Quién hizo esta limpieza?';
    document.getElementById('liBtnQuitar').style.display = esReasignacion ? 'block' : 'none';

    const preseleccion = esReasignacion ? ctx.actualId : MI_ID;
    const sel = document.getElementById('liSelectAsignarA');
    sel.innerHTML = STAFF_ACTIVO.map(s => `<option value="${s.id}" ${s.id == preseleccion ? 'selected' : ''}>${s.nombre}</option>`).join('');

    document.getElementById('liOverlay').style.display = 'flex';
    setTimeout(() => document.getElementById('liPassword').focus(), 80);
}

function liCerrarModal() {
    document.getElementById('liOverlay').style.display = 'none';
}

function liShowErr(msg) {
    const el = document.getElementById('liModalErr');
    el.textContent = msg; el.style.display = 'block';
}

async function liConfirmar() {
    const password = document.getElementById('liPassword').value.trim();
    if (!password) { liShowErr('Tu contraseña es requerida.'); return; }

    const body = {
        tarea_id: liCtxActual.tareaId, local_id: liCtxActual.localId, turno_id: liCtxActual.turnoId,
        fecha: liCtxActual.fecha, password,
        postulante_id: document.getElementById('liSelectAsignarA').value,
    };

    const btn = document.getElementById('liBtnConfirmar');
    btn.disabled = true; btn.textContent = 'Guardando…';

    try {
        const r = await fetch(`${BASE}/staff/api/limpieza/asignar`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body),
        });
        const res = await r.json();
        if (res.success) {
            const nombre = (res.data.nombre || '').split(' ')[0];
            if (IS_ADMIN) {
                liCeldaActual.outerHTML = `<span class="li-name li-name--admin" title="${res.data.nombre}"
                    onclick='liAbrirModal({tareaId:${liCtxActual.tareaId}, localId:${liCtxActual.localId}, turnoId:${liCtxActual.turnoId}, fecha:"${liCtxActual.fecha}", ctx:"${liCtxActual.ctx}", actualId:${res.data.postulante_id}}, this)'>${nombre}</span>`;
            } else {
                liCeldaActual.outerHTML = `<span class="li-name" title="${res.data.nombre}">${nombre}</span>`;
            }
            liCerrarModal();
        } else {
            liShowErr(res.message || 'Error al registrar.');
        }
    } catch {
        liShowErr('Error de conexión.');
    } finally {
        btn.disabled = false; btn.textContent = 'Confirmar';
    }
}

// ── Registros del sistema anterior (votos entre compañeros) ──
let liLegadoCeldaActual = null;
let liLegadoCtxActual = null;

function liAbrirModalLegado(votos, ctx, celda) {
    liLegadoCeldaActual = celda;
    liLegadoCtxActual = ctx;
    document.getElementById('liLegadoCtx').textContent = ctx.ctx;
    document.getElementById('liLegadoPassword').value = '';
    document.getElementById('liLegadoErr').style.display = 'none';
    liRenderLegadoLista(votos);
    document.getElementById('liOverlayLegado').style.display = 'flex';
}

function liRenderLegadoLista(votos) {
    const cont = document.getElementById('liLegadoLista');
    cont.innerHTML = votos.map(v => `
        <div class="li-legado-item" data-voto-id="${v.id_voto}">
            <div class="li-legado-item__txt">
                ${v.calificacion} ⭐ para ${v.nombre}
                <small>Registrado por ${v.votante_nombre}</small>
            </div>
            <button class="li-legado-item__del" onclick="liEliminarLegado(${v.id_voto}, this)">Eliminar</button>
        </div>
    `).join('');
}

function liCerrarModalLegado() {
    document.getElementById('liOverlayLegado').style.display = 'none';
}

async function liEliminarLegado(votoId, btn) {
    const password = document.getElementById('liLegadoPassword').value.trim();
    const err = document.getElementById('liLegadoErr');
    err.style.display = 'none';
    if (!password) { err.textContent = 'Tu contraseña es requerida.'; err.style.display = 'block'; return; }
    if (!confirm('¿Eliminar este registro? Esa estrella se le quitará al trabajador.')) return;

    btn.disabled = true; btn.textContent = 'Eliminando…';
    try {
        const r = await fetch(`${BASE}/staff/api/limpieza/eliminar-legado`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ voto_id: votoId, password }),
        });
        const res = await r.json();
        if (res.success) {
            const fila = document.querySelector(`#liLegadoLista [data-voto-id="${votoId}"]`);
            fila?.remove();
            if (!document.querySelector('#liLegadoLista [data-voto-id]')) {
                const c = liLegadoCtxActual;
                liLegadoCeldaActual.outerHTML = `<button class="li-add" title="Registrar quién limpió: ${c.ctx}"
                    onclick='liAbrirModal({tareaId:${c.tareaId}, localId:${c.localId}, turnoId:${c.turnoId}, fecha:"${c.fecha}", ctx:"${c.ctx}", actualId:null}, this)'>+</button>`;
                liCerrarModalLegado();
            }
        } else {
            err.textContent = res.message || 'Error al eliminar.';
            err.style.display = 'block';
            btn.disabled = false; btn.textContent = 'Eliminar';
        }
    } catch {
        err.textContent = 'Error de conexión.';
        err.style.display = 'block';
        btn.disabled = false; btn.textContent = 'Eliminar';
    }
}

async function liQuitar() {
    const password = document.getElementById('liPassword').value.trim();
    if (!password) { liShowErr('Tu contraseña es requerida.'); return; }
    if (!confirm(`¿Quitar esta asignación? El casillero quedará libre.\n\n${liCtxActual.ctx}`)) return;

    const btnQuitar = document.getElementById('liBtnQuitar');
    btnQuitar.disabled = true; btnQuitar.textContent = 'Quitando…';

    try {
        const r = await fetch(`${BASE}/staff/api/limpieza/quitar`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                tarea_id: liCtxActual.tareaId, local_id: liCtxActual.localId, turno_id: liCtxActual.turnoId,
                fecha: liCtxActual.fecha, password,
            }),
        });
        const res = await r.json();
        if (res.success) {
            liCeldaActual.outerHTML = `<button class="li-add" title="Registrar quién limpió: ${liCtxActual.ctx}"
                onclick='liAbrirModal({tareaId:${liCtxActual.tareaId}, localId:${liCtxActual.localId}, turnoId:${liCtxActual.turnoId}, fecha:"${liCtxActual.fecha}", ctx:"${liCtxActual.ctx}", actualId:null}, this)'>+</button>`;
            liCerrarModal();
        } else {
            liShowErr(res.message || 'Error al quitar.');
        }
    } catch {
        liShowErr('Error de conexión.');
    } finally {
        btnQuitar.disabled = false; btnQuitar.textContent = '🗑️ Quitar asignación (dejar libre)';
    }
}
</script>
</body>
</html>
