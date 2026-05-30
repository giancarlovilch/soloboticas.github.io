<?php
$basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
$userRol  = $userRol  ?? $_SESSION['user_rol']  ?? 'STAFF';
$userName = $userName ?? $_SESSION['user_name'] ?? 'Usuario';
$esAdmin  = $userRol === 'ADMIN';

$f2 = fn($v) => 'S/ ' . number_format((float)$v, 2, '.', ',');

$estadoInfo = [
    'ABIERTO' => ['label' => 'Abierto',   'bg' => '#fee2e2', 'color' => '#991b1b'],
    'PARCIAL' => ['label' => 'En proceso','bg' => '#fef3c7', 'color' => '#92400e'],
    'CERRADO' => ['label' => 'Cerrado',   'bg' => '#d1fae5', 'color' => '#065f46'],
];
$tipoInfo = [
    'FALTANTE' => ['label' => 'Faltante', 'bg' => '#fee2e2', 'color' => '#991b1b'],
    'SOBRANTE' => ['label' => 'Sobrante', 'bg' => '#dbeafe', 'color' => '#1e40af'],
];
$movTipoInfo = [
    'ABONO'        => ['label' => 'Abono',       'dot' => '#16a34a', 'bg' => '#d1fae5', 'color' => '#065f46', 'signo' => '−'],
    'CARGO'        => ['label' => 'Cargo',        'dot' => '#dc2626', 'bg' => '#fee2e2', 'color' => '#991b1b', 'signo' => '+'],
    'CONDONACION'  => ['label' => 'Condonación',  'dot' => '#7c3aed', 'bg' => '#ede9fe', 'color' => '#5b21b6', 'signo' => '−'],
    'AJUSTE_ADMIN' => ['label' => 'Ajuste admin', 'dot' => '#d97706', 'bg' => '#fef3c7', 'color' => '#92400e', 'signo' => '−'],
];

$ei = $estadoInfo[$incidencia['estado']] ?? ['label' => $incidencia['estado'], 'bg' => '#f1f5f9', 'color' => '#475569'];
$ti = $tipoInfo[$incidencia['tipo']]     ?? ['label' => $incidencia['tipo'],   'bg' => '#f1f5f9', 'color' => '#475569'];

$esCerrada  = $incidencia['estado'] === 'CERRADO';
$pendiente  = (float)$incidencia['monto_pendiente'];
$original   = (float)$incidencia['monto_original'];
$porcentaje = $original > 0 ? round(100 * (1 - $pendiente / $original)) : 100;
$cajera     = htmlspecialchars($incidencia['responsable_nombre'] ?? '');
$vendedora  = htmlspecialchars($incidencia['vendedora_nombre']   ?? '');

// Historial más reciente primero
$movimientos = array_reverse($movimientos ?? []);

// Datos del arqueo
$dc       = $detalle ?? [];
$sesionId = (int)$incidencia['sesion_origen_id'];

// Vales SoloBank asignados a esta sesión
$valesSB = array_values(array_filter(
    $digitales ?? [],
    fn($d) => strtolower(trim($d['modo_desc'] ?? '')) === 'solobank'
));

// Cálculo live del arqueo
$dcExt  = (float)($dc['monto_caja_exterior']        ?? 0);
$dcMon  = (float)($dc['monto_monedas']               ?? 0);
$dcBil  = (float)($dc['monto_billetes_caja']         ?? 0);
$dcFue  = (float)($dc['monto_billetes_caja_fuerte']  ?? 0);
$dcAge  = (float)($dc['monto_agente_bcp']            ?? 0);
$totalEsperado = (float)($dc['total_esperado_sistema'] ?? 0);
$totalContado  = (float)($dc['total_efectivo_contado'] ?? 0);
$ventaMonto    = (float)($venta['monto'] ?? 0);

// Diferencia efectiva — misma fórmula que reporte.php
// loQueEs  = físico + rectificaciones
// loQueSeDice = saldo_inicial + ventas(+correcciones) - gastos - digital + ajustes(AGREGAR=-,QUITAR=+)
$sumRectifs = array_sum(array_map(fn($r) => (float)$r['monto'], $rectifs ?? []));
$loQueEs    = round($totalContado + $sumRectifs, 2);

$saldoIni     = (float)($sesion['saldo_inicial'] ?? 0);
$totalVentas  = round((float)($dc['total_ventas_sistema'] ?? ($venta['monto'] ?? 0)) + (float)($sumCorrDelta ?? 0), 2);
$totalGastos  = (float)($dc['total_gastos_sistema'] ?? 0);
$digitalDecl  = (float)($digital_aprobado ?? 0);

$sumAjustesEsp = 0;
foreach ($ajustesEsperado ?? [] as $aj)
    $sumAjustesEsp += $aj['accion'] === 'AGREGAR' ? -(float)$aj['monto'] : (float)$aj['monto'];

// Ajustes visibles: excluye los generados automáticamente por vales de regularización
$ajustesDisplay = array_values(array_filter(
    $ajustesEsperado ?? [],
    fn($aj) => !str_starts_with($aj['descripcion'] ?? '', 'Vale regularización')
));

$loQueSeDice = round($saldoIni + $totalVentas - $totalGastos - $digitalDecl + $sumAjustesEsp, 2);
$difActual   = round($loQueEs - $loQueSeDice, 2);

// Alias para JS y calculadora
$sumAjustes = -$sumAjustesEsp; // para display en panel (signo visual)

// Monto efectivo a resolver (para sugerir en resolución)
$pendienteEfectivo = abs($difActual);

// Se puede cerrar si: sobrante, pendiente declarado ≤10, o diferencia efectiva ≤10
$puedeCerrar = $incidencia['tipo'] === 'SOBRANTE' || $pendiente <= 10 || abs($difActual) <= 10;

$difColor = abs($difActual) <= 0.01 ? '#16a34a' : ($difActual > 0 ? '#1e40af' : '#dc2626');
$difLabel = abs($difActual) <= 0.01 ? 'CUADRADO' : ($difActual > 0 ? 'SOBRANTE' : 'FALTANTE');
$difBg    = abs($difActual) <= 0.01 ? '#d1fae5'  : ($difActual > 0 ? '#dbeafe'  : '#fee2e2');
$difBd    = abs($difActual) <= 0.01 ? '#a7f3d0'  : ($difActual > 0 ? '#93c5fd'  : '#fecaca');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incidencia #<?= $incidencia['id_incidencia'] ?> | Solo Boticas</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/staff.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="<?= $basePath ?>/assets/img/logo.ico">
    <style>
        * { box-sizing: border-box; }
        body { background: #f1f5f9; font-family: 'Inter', sans-serif; margin: 0; }

        /* Header */
        .inc-header { background:#1e293b;padding:.75rem 1.25rem;display:flex;align-items:center;
                      gap:1rem;flex-wrap:wrap;justify-content:space-between; }
        .inc-header__logo  { width:32px;height:32px;background:#3b82f6;border-radius:7px;
                             display:flex;align-items:center;justify-content:center;
                             font-weight:800;color:#fff;font-size:.85rem;flex-shrink:0; }
        .inc-header__title { font-size:.85rem;font-weight:600;color:#e2e8f0; }
        .inc-header__sub   { font-size:.72rem;color:#94a3b8;margin-top:.05rem; }
        .inc-header__right { display:flex;align-items:center;gap:.6rem;flex-wrap:wrap; }

        /* Layout */
        .inc-wrap  { max-width:1280px;margin:0 auto;padding:1rem 1rem 3rem; }
        .inc-cols  { display:grid;grid-template-columns:1fr 320px;gap:1rem;align-items:start; }
        @media(max-width:900px){ .inc-cols { grid-template-columns:1fr; } }

        /* Cards */
        .card { background:#fff;border-radius:12px;border:1px solid #e2e8f0;
                box-shadow:0 1px 3px rgba(0,0,0,.05);margin-bottom:.85rem; }
        .card-head { padding:.75rem 1.1rem;border-bottom:1px solid #f1f5f9;
                     display:flex;align-items:center;justify-content:space-between; }
        .card-body { padding:.85rem 1.1rem; }
        .card-title { font-size:.72rem;font-weight:700;text-transform:uppercase;
                      letter-spacing:.07em;color:#64748b;margin:0; }

        /* Badges */
        .badge { display:inline-block;padding:.2rem .6rem;border-radius:6px;font-size:.7rem;font-weight:700; }

        /* Buttons */
        .btn { display:inline-flex;align-items:center;gap:.4rem;padding:.5rem 1.1rem;
               border-radius:8px;font-size:.82rem;font-weight:600;cursor:pointer;
               border:1.5px solid transparent;transition:all .15s;font-family:inherit; }
        .btn-primary   { background:#1e293b;color:#fff;border-color:#1e293b; }
        .btn-primary:hover { background:#334155; }
        .btn-secondary { background:#f1f5f9;color:#475569;border-color:#e2e8f0; }
        .btn-secondary:hover { background:#e2e8f0; }
        .btn-success   { background:#d1fae5;color:#065f46;border-color:#a7f3d0; }
        .btn-success:hover { background:#a7f3d0; }
        .btn-warn      { background:#fef3c7;color:#92400e;border-color:#fde68a; }
        .btn-warn:hover { background:#fde68a; }
        .btn-danger    { background:#fee2e2;color:#991b1b;border-color:#fecaca; }
        .btn-danger:hover { background:#fecaca; }
        .btn-sm { padding:.35rem .75rem;font-size:.78rem; }
        .btn:disabled { opacity:.4;cursor:not-allowed; }

        /* Form */
        .form-label { font-size:.7rem;font-weight:700;text-transform:uppercase;
                      letter-spacing:.06em;color:#64748b;display:block;margin-bottom:.3rem; }
        .form-input { width:100%;padding:.45rem .7rem;border:1.5px solid #e2e8f0;border-radius:7px;
                      font-size:.875rem;font-family:inherit;outline:none;transition:border .15s;background:#fff; }
        .form-input:focus { border-color:#3b82f6; }
        .form-row { display:grid;gap:.6rem;margin-bottom:.6rem; }
        .cols-2 { grid-template-columns:1fr 1fr; }
        .cols-3 { grid-template-columns:1fr 1fr 1fr; }
        @media(max-width:600px){ .cols-2,.cols-3 { grid-template-columns:1fr; } }

        /* Before/After table */
        .ba-table { width:100%;border-collapse:collapse;font-size:.82rem; }
        .ba-table th { text-align:left;padding:.35rem .5rem;font-size:.68rem;
                       font-weight:700;text-transform:uppercase;letter-spacing:.06em;
                       color:#94a3b8;border-bottom:1px solid #f1f5f9; }
        .ba-table td { padding:.4rem .5rem;border-bottom:1px solid #f8fafc;vertical-align:middle; }
        .ba-table tr:last-child td { border-bottom:none; }
        .ba-table .field-name { color:#475569;font-weight:600;font-size:.8rem; }
        .ba-table .val-before { color:#94a3b8; }
        .ba-table input[type=number] { width:100%;padding:.3rem .5rem;border:1.5px solid #e2e8f0;
                                        border-radius:6px;font-size:.82rem;font-family:inherit;
                                        text-align:right;outline:none; }
        .ba-table input[type=number]:focus { border-color:#3b82f6; }

        /* Total row */
        .ba-total { font-weight:800;font-size:.85rem; }
        .ba-total td { padding-top:.6rem !important;border-top:2px solid #e2e8f0 !important;
                       border-bottom:none !important; }

        /* Alerts */
        .alert { padding:.5rem .85rem;border-radius:8px;font-size:.8rem;font-weight:600; }
        .alert-ok  { background:#d1fae5;color:#065f46; }
        .alert-err { background:#fee2e2;color:#991b1b; }

        /* Back link */
        .back-link { font-size:.8rem;color:#64748b;text-decoration:none;display:inline-flex;
                     align-items:center;gap:.3rem;margin-bottom:.75rem; }
        .back-link:hover { color:#1e293b; }

        /* Sticky calculator */
        .calc-panel { position:sticky;top:1rem; }
        .calc-row   { display:flex;justify-content:space-between;align-items:center;
                      padding:.4rem 0;font-size:.85rem; }
        .calc-row + .calc-row { border-top:1px solid #f1f5f9; }
        .calc-key   { color:#64748b;font-weight:600; }
        .calc-val   { font-weight:700; }
        .calc-sep   { border:none;border-top:2px solid #e2e8f0;margin:.5rem 0; }
        .calc-big   { font-size:1.6rem;font-weight:800;letter-spacing:-.03em;text-align:center;
                      padding:.6rem 0; }

        /* Progress bar */
        .prog-track { background:#e2e8f0;border-radius:20px;height:6px;overflow:hidden;margin:.4rem 0; }
        .prog-fill  { height:100%;border-radius:20px;transition:width .25s; }

        /* Pendiente status */
        .pend-amount { font-size:2rem;font-weight:800;letter-spacing:-.03em;line-height:1; }

        /* Historial movimientos */
        .mov-scroll { max-height:260px;overflow-y:auto;padding-right:.25rem; }
        .mov-item { display:flex;align-items:flex-start;gap:.6rem;padding:.55rem 0;
                    border-bottom:1px solid #f8fafc; }
        .mov-item:last-child { border-bottom:none; }
        .mov-dot  { width:8px;height:8px;border-radius:50%;margin-top:.3rem;flex-shrink:0; }
        .mov-body { flex:1;min-width:0; }
        .mov-meta { font-size:.68rem;color:#94a3b8;margin-top:.15rem; }
        .mov-monto { font-size:.85rem;font-weight:800;white-space:nowrap;margin-left:.4rem; }

        /* Penalidad buttons */
        .penal-row { display:grid;grid-template-columns:1fr 1fr 1fr;gap:.45rem;margin-top:.8rem; }
        @media(max-width:480px){ .penal-row { grid-template-columns:1fr; } }
        .penal-btn { border:1.5px solid #e2e8f0;border-radius:8px;padding:.45rem .5rem;
                     background:#f8fafc;cursor:pointer;text-align:center;
                     transition:border-color .15s,background .15s;font-family:inherit; }
        .penal-btn:hover { border-color:#3b82f6;background:#eff6ff; }
        .penal-btn__title { font-size:.62rem;font-weight:700;text-transform:uppercase;
                            letter-spacing:.07em;color:#94a3b8;display:block;margin-bottom:.15rem; }
        .penal-btn__name  { font-size:.78rem;font-weight:700;color:#1e293b; }

        /* Existing items list */
        .item-list { list-style:none;margin:0;padding:0; }
        .item-list li { display:flex;align-items:center;justify-content:space-between;
                        padding:.4rem .5rem;border-radius:6px;font-size:.8rem;
                        margin-bottom:.25rem;background:#f8fafc; }
        .item-list li .item-desc { flex:1;color:#475569; }
        .item-list li .item-amount { font-weight:700;white-space:nowrap;margin-left:.5rem; }
        .item-list li .item-rm { background:none;border:none;cursor:pointer;color:#94a3b8;
                                  font-size:.85rem;padding:.1rem .3rem;border-radius:4px; }
        .item-list li .item-rm:hover { color:#dc2626;background:#fee2e2; }
        .item-list li .item-rm.edit:hover { color:#2563eb;background:#dbeafe; }

        /* Section divider */
        .sec-divider { border:none;border-top:1px solid #e2e8f0;margin:1rem 0; }

        /* Correction ventas */
        .corr-row { display:flex;align-items:center;gap:.4rem;margin-bottom:.35rem; }
    </style>
</head>
<body>

<header class="inc-header">
    <div style="display:flex;align-items:center;gap:.85rem;min-width:0;">
        <div class="inc-header__logo">SB</div>
        <div style="min-width:0;">
            <!-- Fila 1: número + badges -->
            <div style="display:flex;align-items:center;gap:.45rem;flex-wrap:wrap;margin-bottom:.3rem;">
                <span style="font-size:.95rem;font-weight:800;color:#f1f5f9;letter-spacing:-.01em;">
                    Incidencia #<?= $incidencia['id_incidencia'] ?>
                </span>
                <span class="badge" style="background:<?= $ti['bg'] ?>;color:<?= $ti['color'] ?>;font-size:.68rem;">
                    <?= $ti['label'] ?>
                </span>
                <span class="badge" style="background:<?= $ei['bg'] ?>;color:<?= $ei['color'] ?>;font-size:.68rem;">
                    <?= $ei['label'] ?>
                </span>
            </div>
            <!-- Fila 2: meta info separada con puntos -->
            <div style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;">
                <?php if ($incidencia['local_desc'] ?? ''): ?>
                <span style="font-size:.72rem;color:#94a3b8;"><?= htmlspecialchars($incidencia['local_desc']) ?></span>
                <?php endif; ?>
                <?php if ($incidencia['caja_desc'] ?? ''): ?>
                <span style="color:#475569;font-size:.6rem;">·</span>
                <span style="font-size:.72rem;color:#94a3b8;"><?= htmlspecialchars($incidencia['caja_desc']) ?></span>
                <?php endif; ?>
                <span style="color:#475569;font-size:.6rem;">·</span>
                <a href="<?= $basePath ?>/caja/reporte/<?= $sesionId ?>" target="_blank"
                   style="font-size:.72rem;color:#60a5fa;text-decoration:none;font-weight:600;">
                    Sesión #<?= $sesionId ?>
                </a>
                <span style="color:#475569;font-size:.6rem;">·</span>
                <span style="font-size:.72rem;color:#94a3b8;">
                    <?= date('d/m/Y', strtotime($incidencia['fecha_apertura'])) ?>
                </span>
            </div>
        </div>
    </div>
    <div class="inc-header__right">
        <?php if ($esAdmin && $esCerrada): ?>
        <button class="btn btn-warn" id="btnReabrir">Reabrir caso</button>
        <?php endif; ?>
        <?php if ($esAdmin && !$esCerrada): ?>
        <button class="btn btn-success" id="btnCerrarManual"
            <?= !$puedeCerrar ? 'disabled title="Faltante pendiente > S/ 10.00 — debe resolverse antes de cerrar"' : '' ?>>
            Cerrar caso
        </button>
        <?php endif; ?>
        <a href="<?= $basePath ?>/caja" class="btn btn-secondary">← Volver</a>
    </div>
</header>

<main>
<div class="inc-wrap">
    <div class="inc-cols">

        <!-- ═══════════════════════════════════════════════════════════ -->
        <!-- Columna izquierda: herramientas de edición del arqueo       -->
        <!-- ═══════════════════════════════════════════════════════════ -->
        <div>

            <!-- ── 1. Conteo de efectivo ──────────────────────── -->
            <div class="card">
                <div class="card-head">
                    <p class="card-title">Conteo de efectivo</p>
                    <span style="font-size:.72rem;color:#94a3b8;">
                        Total actual: <strong id="conteoTotal"><?= $f2($totalContado) ?></strong>
                    </span>
                </div>
                <div class="card-body">
                    <table class="ba-table">
                        <thead>
                            <tr>
                                <th style="width:42%">Campo</th>
                                <th>Actual</th>
                                <th>Nuevo valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $conteoFields = [
                                'exterior'        => ['Caja exterior',       $dcExt],
                                'monedas'         => ['Monedas',             $dcMon],
                                'billetes_caja'   => ['Billetes caja',       $dcBil],
                                'billetes_fuerte' => ['Billetes caja fuerte',$dcFue],
                                'agente_bcp'      => ['Agente BCP',          $dcAge],
                            ];
                            foreach ($conteoFields as $key => [$label, $val]):
                            ?>
                            <tr>
                                <td class="field-name"><?= $label ?></td>
                                <td class="val-before"><?= $f2($val) ?></td>
                                <td>
                                    <input type="number" step="0.01" min="0"
                                           id="cnt_<?= $key ?>" value="<?= number_format($val,2,'.','') ?>"
                                           oninput="recalcConteo()">
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="ba-total">
                                <td class="field-name">Total efectivo</td>
                                <td class="val-before"><?= $f2($totalContado) ?></td>
                                <td style="text-align:right;font-weight:800;" id="cnt_total_preview">
                                    <?= $f2($totalContado) ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                    <div style="display:flex;gap:.5rem;align-items:center;margin-top:.75rem;flex-wrap:wrap;">
                        <button class="btn btn-primary btn-sm" id="btnConteo" onclick="guardarConteo()">
                            Guardar conteo
                        </button>
                        <span id="conteoAlert" style="display:none;" class="alert"></span>
                    </div>

                    <?php
                    $histConteo = array_values(array_filter(
                        $auditoriaCaja ?? [],
                        fn($a) => $a['accion'] === 'CONTEO_MODIFICADO'
                    ));
                    if (!empty($histConteo)):
                    ?>
                    <hr style="border:none;border-top:1px solid #f1f5f9;margin:1rem 0 .75rem;">
                    <p class="form-label" style="margin-bottom:.5rem;">Historial de cambios</p>
                    <ul class="item-list">
                        <?php foreach (array_reverse($histConteo) as $h): ?>
                        <li style="flex-direction:column;align-items:flex-start;gap:.15rem;">
                            <div style="display:flex;justify-content:space-between;width:100%;">
                                <span style="font-size:.78rem;font-weight:600;color:#475569;">
                                    <?= htmlspecialchars($h['campo_modificado'] ?? '') ?>
                                </span>
                                <span style="font-size:.78rem;color:#64748b;">
                                    <?= htmlspecialchars($h['valor_anterior'] ?? '') ?>
                                    <span style="color:#94a3b8;">→</span>
                                    <strong style="color:#1e293b;"><?= htmlspecialchars($h['valor_nuevo'] ?? '') ?></strong>
                                </span>
                            </div>
                            <div style="font-size:.68rem;color:#94a3b8;">
                                <?= htmlspecialchars($h['registrado_por'] ?? '—') ?>
                                · <?= date('d/m/Y H:i', strtotime($h['fecha'])) ?>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>

                </div>
            </div>

            <!-- ── 2. Ventas del turno ────────────────────────── -->
            <div class="card">
                <div class="card-head">
                    <p class="card-title">Ventas del turno</p>
                </div>
                <div class="card-body">
                    <?php if (!empty($correccionesVenta)): ?>
                    <p style="font-size:.72rem;font-weight:700;text-transform:uppercase;color:#94a3b8;margin:0 0 .5rem;">Correcciones anteriores</p>
                    <ul class="item-list" style="margin-bottom:.85rem;">
                        <?php foreach ($correccionesVenta as $cv): ?>
                        <li>
                            <span class="item-desc">
                                <?= $f2($cv['monto_anterior']) ?> → <?= $f2($cv['monto_nuevo']) ?>
                                <?php if ($cv['motivo'] ?? ''): ?>
                                  <span style="color:#94a3b8;"> · <?= htmlspecialchars($cv['motivo']) ?></span>
                                <?php endif; ?>
                            </span>
                            <span class="item-amount" style="color:<?= (float)$cv['monto_nuevo']>(float)$cv['monto_anterior']?'#16a34a':'#dc2626' ?>">
                                <?= (float)$cv['monto_nuevo']>=(float)$cv['monto_anterior']?'+':'' ?><?= $f2((float)$cv['monto_nuevo']-(float)$cv['monto_anterior']) ?>
                            </span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>

                    <table class="ba-table">
                        <thead>
                            <tr>
                                <th>Campo</th>
                                <th>Actual</th>
                                <th>Nuevo valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="field-name">Monto ventas</td>
                                <td class="val-before"><?= $f2($ventaMonto) ?></td>
                                <td>
                                    <input type="number" step="0.01" min="0" id="venta_monto"
                                           value="<?= number_format($ventaMonto,2,'.','') ?>">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div style="margin-top:.6rem;">
                        <label class="form-label">Motivo de corrección</label>
                        <input type="text" class="form-input" id="venta_desc"
                               placeholder="Ej: Error en captura de caja">
                    </div>
                    <div style="display:flex;gap:.5rem;align-items:center;margin-top:.65rem;flex-wrap:wrap;">
                        <button class="btn btn-primary btn-sm" onclick="guardarVenta()">Guardar venta</button>
                        <span id="ventaAlert" style="display:none;" class="alert"></span>
                    </div>
                </div>
            </div>

            <!-- ── 3. Cobros electrónicos ─────────────────────── -->
            <?php
            $cobrosElec = array_values(array_filter(
                $digitales ?? [],
                fn($d) => strtolower(trim($d['modo_desc'] ?? '')) !== 'solobank'
            ));
            ?>
            <div class="card">
                <div class="card-head">
                    <p class="card-title">Cobros electrónicos</p>
                    <span style="font-size:.72rem;color:#94a3b8;">Yape · Plin · Visa POS · otros</span>
                </div>
                <div class="card-body">
                    <?php if (!empty($cobrosElec)): ?>
                    <ul class="item-list" style="margin-bottom:.85rem;">
                        <?php foreach ($cobrosElec as $ce): ?>
                        <li>
                            <span class="item-desc">
                                <span class="badge" style="background:#ede9fe;color:#5b21b6;font-size:.65rem;margin-right:.3rem;">
                                    <?= htmlspecialchars($ce['modo_desc']) ?>
                                </span>
                                <?php if ($ce['numero_operacion'] ?? ''): ?>
                                <span style="font-family:monospace;font-size:.78rem;color:#475569;">
                                    <?= htmlspecialchars($ce['numero_operacion']) ?>
                                </span>
                                <?php endif; ?>
                                <span style="font-size:.7rem;color:#94a3b8;margin-left:.3rem;">
                                    <?= date('d/m H:i', strtotime($ce['fecha_movimiento'])) ?>
                                </span>
                                <?php
                                $estCls = match($ce['estado']) {
                                    'APROBADO'  => 'background:#d1fae5;color:#065f46',
                                    'PENDIENTE' => 'background:#fef3c7;color:#92400e',
                                    default     => 'background:#f1f5f9;color:#475569',
                                };
                                ?>
                                <span class="badge" style="<?= $estCls ?>;font-size:.62rem;margin-left:.3rem;">
                                    <?= $ce['estado'] ?>
                                </span>
                            </span>
                            <span class="item-amount" style="color:#5b21b6;"><?= $f2($ce['monto']) ?></span>
                            <?php if ($ce['estado'] === 'PENDIENTE'): ?>
                            <button class="item-rm" onclick="eliminarCobro(<?= $ce['id_movimiento'] ?>, this)" title="Eliminar">✕</button>
                            <?php elseif ($esAdmin): ?>
                            <button class="item-rm" onclick="eliminarCobroAdmin(<?= $sesionId ?>, <?= $ce['id_movimiento'] ?>, this)" title="Eliminar (admin)">✕</button>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php else: ?>
                    <p style="font-size:.82rem;color:#94a3b8;margin-bottom:.85rem;">Sin cobros electrónicos.</p>
                    <?php endif; ?>

                    <p class="form-label" style="margin-bottom:.5rem;">Agregar cobro</p>
                    <div class="form-row cols-3" style="margin-bottom:.6rem;">
                        <div>
                            <label class="form-label">Modo *</label>
                            <select class="form-input" id="ce_modo_id">
                                <option value="">— seleccionar —</option>
                                <?php foreach ($modos ?? [] as $m): ?>
                                <option value="<?= $m['id_modo'] ?>"><?= htmlspecialchars($m['descripcion']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Monto (S/) *</label>
                            <input type="number" step="0.01" min="0.01" class="form-input"
                                   id="ce_monto" placeholder="0.00">
                        </div>
                        <div>
                            <label class="form-label">N° operación</label>
                            <input type="text" class="form-input" id="ce_num_op" placeholder="Opcional">
                        </div>
                    </div>
                    <div style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;">
                        <button class="btn btn-primary btn-sm" onclick="guardarCobro()">Agregar</button>
                        <span id="ceAlert" style="display:none;" class="alert"></span>
                    </div>
                </div>
            </div>

            <!-- ── 4. Ajustes al esperado ─────────────────────── -->
            <div class="card">
                <div class="card-head">
                    <p class="card-title">Ajustes al esperado</p>
                </div>
                <div class="card-body">
                    <?php if (!empty($ajustesDisplay)): ?>
                    <ul class="item-list" style="margin-bottom:.85rem;">
                        <?php foreach ($ajustesDisplay as $aj): ?>
                        <li>
                            <span class="item-desc">
                                <span class="badge" style="background:#f1f5f9;color:#475569;font-size:.65rem;margin-right:.3rem;"><?= $aj['accion'] ?></span>
                                <?php
                                switch ($aj['tipo']) {
                                    case 'PERSONAL':
                                        $ajLabel = 'Personal · ' . ($aj['staff_desc'] ?? '—');
                                        if ($aj['tipo_pago'] ?? '') $ajLabel .= ' (' . $aj['tipo_pago'] . ')';
                                        if ($aj['descripcion'] ?? '') $ajLabel .= ' — ' . $aj['descripcion'];
                                        break;
                                    case 'LOCAL':
                                        $ajLabel = 'Local · ' . ($aj['local_desc'] ?? '—');
                                        if ($aj['concepto_desc'] ?? '') $ajLabel .= ' / ' . $aj['concepto_desc'];
                                        if ($aj['descripcion'] ?? '') $ajLabel .= ' · ' . $aj['descripcion'];
                                        break;
                                    case 'DEPOSITO':
                                        $ajLabel = 'Depósito KGYR';
                                        if ($aj['descripcion'] ?? '') $ajLabel .= ' · ' . $aj['descripcion'];
                                        break;
                                    case 'COMPRA':
                                        $ajLabel = 'Compra · ' . ($aj['descripcion'] ?? '—');
                                        break;
                                    default:
                                        $ajLabel = ($aj['tipo'] ?? '') . ($aj['descripcion'] ? ' · ' . $aj['descripcion'] : '');
                                }
                                echo htmlspecialchars($ajLabel);
                                ?>
                            </span>
                            <span class="item-amount" style="color:<?= $aj['accion']==='AGREGAR'?'#16a34a':'#dc2626' ?>">
                                <?= $aj['accion']==='AGREGAR'?'+':'-' ?><?= $f2($aj['monto']) ?>
                            </span>
                            <?php if ($esAdmin): ?>
                            <button class="item-rm" onclick="eliminarAjuste(<?= $aj['id_ajuste'] ?>, this)" title="Eliminar">✕</button>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php else: ?>
                    <p style="font-size:.82rem;color:#94a3b8;margin-bottom:.85rem;">Sin ajustes.</p>
                    <?php endif; ?>

                    <p class="form-label" style="margin-bottom:.5rem;">Agregar ajuste</p>
                    <div class="form-row cols-3" style="margin-bottom:.6rem;">
                        <div>
                            <label class="form-label">Acción</label>
                            <select class="form-input" id="aj_accion">
                                <option value="AGREGAR">+ Agregar</option>
                                <option value="QUITAR">− Quitar</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Tipo</label>
                            <select class="form-input" id="aj_tipo" onchange="ajTipoChange()">
                                <option value="PERSONAL">Personal (staff)</option>
                                <option value="LOCAL">Local</option>
                                <option value="COMPRA">Compra</option>
                                <option value="DEPOSITO">Depósito KGYR</option>
                                <option value="OTRO">Otro</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Monto (S/)</label>
                            <input type="number" step="0.01" min="0" class="form-input" id="aj_monto" placeholder="0.00">
                        </div>
                    </div>

                    <!-- Campos dinámicos por tipo -->

                    <!-- COBRO: oculto (se maneja en sección Cobros electrónicos) -->
                    <div id="aj_bloque_cobro" style="display:none;margin-bottom:.6rem;">
                        <div class="form-row cols-2">
                            <div>
                                <label class="form-label">Modo de pago</label>
                                <select class="form-input" id="aj_modo_id">
                                    <option value="">— cualquiera —</option>
                                    <?php foreach ($modos ?? [] as $m): ?>
                                    <option value="<?= $m['id_modo'] ?>"><?= htmlspecialchars($m['descripcion']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Descripción *</label>
                                <input type="text" class="form-input" id="aj_desc_cobro" placeholder="Ej: Cobro Yape cliente X">
                            </div>
                        </div>
                    </div>

                    <!-- PERSONAL: staff + tipo de pago + nota -->
                    <div id="aj_bloque_personal" style="display:none;margin-bottom:.6rem;">
                        <div class="form-row cols-2" style="margin-bottom:.5rem;">
                            <div>
                                <label class="form-label">Personal *</label>
                                <select class="form-input" id="aj_ref_personal">
                                    <option value="">— seleccionar —</option>
                                    <?php foreach ($staff ?? [] as $s): ?>
                                    <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nombre_completo']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Tipo de pago</label>
                                <select class="form-input" id="aj_tipopago">
                                    <option value="PAGO_TOTAL">Pago total</option>
                                    <option value="ADELANTO">Adelanto</option>
                                    <option value="DESCUENTO">Descuento</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="form-label">Nota</label>
                            <input type="text" class="form-input" id="aj_desc_personal"
                                   placeholder="Ej: pago semanal, adelanto por emergencia…">
                        </div>
                    </div>

                    <!-- LOCAL: local + concepto + comprobante -->
                    <div id="aj_bloque_local" style="display:none;margin-bottom:.6rem;">
                        <div class="form-row cols-2" style="margin-bottom:.5rem;">
                            <div>
                                <label class="form-label">Local *</label>
                                <select class="form-input" id="aj_ref_local">
                                    <option value="">— seleccionar —</option>
                                    <?php foreach ($locales ?? [] as $loc): ?>
                                    <option value="<?= $loc['id'] ?>"><?= htmlspecialchars($loc['descripcion']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Concepto</label>
                                <select class="form-input" id="aj_ref2_concepto">
                                    <option value="">— seleccionar —</option>
                                    <?php foreach ($conceptos ?? [] as $con): ?>
                                    <option value="<?= $con['id'] ?>"><?= htmlspecialchars($con['descripcion']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="form-label">N° Comprobante</label>
                            <input type="text" class="form-input" id="aj_desc_local" placeholder="Opcional">
                        </div>
                    </div>

                    <!-- COMPRA: descripción + comprobante -->
                    <div id="aj_bloque_compra" style="display:none;margin-bottom:.6rem;">
                        <div class="form-row cols-2">
                            <div>
                                <label class="form-label">Descripción *</label>
                                <input type="text" class="form-input" id="aj_desc_compra" placeholder="Ej: Compra de útiles">
                            </div>
                            <div>
                                <label class="form-label">N° Comprobante</label>
                                <input type="text" class="form-input" id="aj_doc_compra" placeholder="Opcional">
                            </div>
                        </div>
                    </div>

                    <!-- DEPOSITO: banco + referencia -->
                    <div id="aj_bloque_deposito" style="display:none;margin-bottom:.6rem;">
                        <div class="form-row cols-2">
                            <div>
                                <label class="form-label">Banco *</label>
                                <select class="form-input" id="aj_banco_deposito">
                                    <option value="BCP">BCP</option>
                                    <option value="BBVA">BBVA</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">N° operación *</label>
                                <input type="text" class="form-input" id="aj_desc_deposito" placeholder="Ej: 12345678">
                            </div>
                        </div>
                    </div>

                    <!-- OTRO: descripción libre -->
                    <div id="aj_bloque_otro" style="display:none;margin-bottom:.6rem;">
                        <label class="form-label">Descripción</label>
                        <input type="text" class="form-input" id="aj_desc_otro" placeholder="Descripción libre">
                    </div>

                    <div style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;">
                        <button class="btn btn-primary btn-sm" onclick="guardarAjuste()">Agregar</button>
                        <span id="ajAlert" style="display:none;" class="alert"></span>
                    </div>
                </div>
            </div>

            <!-- ── 5. Vales SoloBank ───────────────────────────── -->
            <div class="card">
                <div class="card-head">
                    <p class="card-title">Vales SoloBank</p>
                </div>
                <div class="card-body">
                    <?php if (!empty($valesSB)): ?>
                    <ul class="item-list" style="margin-bottom:.85rem;">
                        <?php foreach ($valesSB as $v): ?>
                        <li>
                            <span class="item-desc">
                                <span style="font-family:monospace;font-size:.78rem;color:#475569;"><?= htmlspecialchars($v['numero_operacion'] ?? '—') ?></span>
                                <span style="color:#94a3b8;font-size:.72rem;margin-left:.3rem;"><?= date('d/m/Y', strtotime($v['fecha_movimiento'])) ?></span>
                            </span>
                            <span class="item-amount" style="color:#5b21b6;"><?= $f2($v['monto']) ?></span>
                            <button class="item-rm" onclick="quitarVale(<?= $v['id_movimiento'] ?>, this)" title="Quitar vale">✕</button>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php else: ?>
                    <p style="font-size:.82rem;color:#94a3b8;margin-bottom:.85rem;">Sin vales asignados.</p>
                    <?php endif; ?>

                    <?php if (!empty($valesDisponibles)): ?>
                    <p class="form-label" style="margin-bottom:.5rem;">Asignar vale disponible</p>
                    <div class="form-row cols-2">
                        <div>
                            <label class="form-label">Vale</label>
                            <select class="form-input" id="sb_codigo">
                                <option value="">— seleccionar —</option>
                                <?php foreach ($valesDisponibles as $sv): ?>
                                <option value="<?= htmlspecialchars($sv['codigo']) ?>">
                                    <?= htmlspecialchars($sv['caja']) ?> — S/ <?= number_format($sv['total'],2) ?> · <?= htmlspecialchars($sv['fecha']) ?> (<?= htmlspecialchars($sv['turno']) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div style="display:flex;align-items:flex-end;">
                            <button class="btn btn-primary btn-sm" onclick="asignarVale()" style="width:100%">Asignar</button>
                        </div>
                    </div>
                    <span id="sbAlert" style="display:none;margin-top:.4rem;" class="alert"></span>
                    <?php else: ?>
                    <p style="font-size:.78rem;color:#94a3b8;">No hay vales disponibles para asignar.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ── 6. Vales de regularización ────────────────────── -->
            <div class="card">
                <div class="card-head">
                    <p class="card-title">Vales de regularización</p>
                    <span style="font-size:.7rem;color:#94a3b8;">Regulariza cobros entre sesiones</span>
                </div>
                <div class="card-body">

                    <?php
                    $valesOrigen  = array_values(array_filter($valesRegPropios, fn($v) => $v['incidencia_origen_id'] == $incidencia['id_incidencia']));
                    $valesDestino = array_values(array_filter($valesRegPropios, fn($v) => $v['incidencia_destino_id'] == $incidencia['id_incidencia']));
                    ?>

                    <!-- Vales generados por esta incidencia -->
                    <?php if (!empty($valesOrigen)): ?>
                    <p class="form-label" style="margin-bottom:.4rem;">Generados por esta incidencia</p>
                    <ul class="item-list" style="margin-bottom:.85rem;">
                        <?php foreach ($valesOrigen as $vr): ?>
                        <li>
                            <span class="item-desc">
                                <span style="font-family:monospace;font-weight:700;color:#1e293b;font-size:.8rem;"><?= htmlspecialchars($vr['codigo']) ?></span>
                                <?php if ($vr['descripcion'] ?? ''): ?>
                                  <span style="color:#94a3b8;font-size:.72rem;margin-left:.3rem;"><?= htmlspecialchars($vr['descripcion']) ?></span>
                                <?php endif; ?>
                                <?php if ($vr['estado'] === 'USADO'): ?>
                                  <span class="badge" style="background:#d1fae5;color:#065f46;margin-left:.4rem;">Usado · sesión #<?= $vr['sesion_destino_id'] ?></span>
                                <?php elseif ($vr['estado'] === 'ANULADO'): ?>
                                  <span class="badge" style="background:#f1f5f9;color:#94a3b8;margin-left:.4rem;">Anulado</span>
                                <?php else: ?>
                                  <span class="badge" style="background:#fef3c7;color:#92400e;margin-left:.4rem;">Disponible</span>
                                <?php endif; ?>
                            </span>
                            <span class="item-amount" style="color:#5b21b6;"><?= $f2($vr['monto']) ?></span>
                            <?php if ($esAdmin && $vr['estado'] === 'DISPONIBLE'): ?>
                            <button class="item-rm edit" onclick="editarValeInline(<?= $vr['id'] ?>, <?= $vr['monto'] ?>, <?= json_encode($vr['descripcion'] ?? '') ?>, this)" title="Editar" style="margin-right:.2rem;">✎</button>
                            <button class="item-rm" onclick="anularVale(<?= $vr['id'] ?>, this)" title="Anular">✕</button>
                            <?php elseif ($esAdmin && $vr['estado'] === 'USADO'): ?>
                            <button class="item-rm" onclick="revertirVale(<?= $vr['id'] ?>, this)" title="Revertir aplicación" style="color:#d97706;">↩</button>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>

                    <!-- Vales recibidos/usados en esta incidencia -->
                    <?php if (!empty($valesDestino)): ?>
                    <p class="form-label" style="margin-bottom:.4rem;">Vales aplicados a esta incidencia</p>
                    <ul class="item-list" style="margin-bottom:.85rem;">
                        <?php foreach ($valesDestino as $vr): ?>
                        <li>
                            <span class="item-desc">
                                <span style="font-family:monospace;font-weight:700;color:#1e293b;font-size:.8rem;"><?= htmlspecialchars($vr['codigo']) ?></span>
                                <span style="color:#94a3b8;font-size:.72rem;margin-left:.3rem;">inc. #<?= $vr['incidencia_origen_id'] ?> · sesión #<?= $vr['sesion_origen_id'] ?></span>
                            </span>
                            <span class="item-amount" style="color:#16a34a;">−<?= $f2($vr['monto']) ?></span>
                            <?php if ($esAdmin): ?>
                            <button class="item-rm" onclick="revertirVale(<?= $vr['id'] ?>, this)" title="Revertir aplicación" style="color:#d97706;">↩</button>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>

                    <?php if (empty($valesOrigen) && empty($valesDestino)): ?>
                    <p style="font-size:.82rem;color:#94a3b8;margin-bottom:.85rem;">Sin vales de regularización.</p>
                    <?php endif; ?>

                    <hr class="sec-divider">

                    <!-- Generar nuevo vale (solo si hay pendiente) -->
                    <?php if (!$esCerrada): ?>
                    <p class="form-label" style="margin-bottom:.5rem;">Generar vale para otra sesión</p>
                    <div class="form-row cols-2" style="margin-bottom:.6rem;">
                        <div>
                            <label class="form-label">Monto (S/) *</label>
                            <input type="number" step="0.01" min="0.01"
                                   class="form-input" id="vr_monto"
                                   value="<?= number_format($pendienteEfectivo, 2, '.', '') ?>"
                                   placeholder="<?= $f2($pendienteEfectivo) ?>">
                        </div>
                        <div>
                            <label class="form-label">Nota (opcional)</label>
                            <input type="text" class="form-input" id="vr_desc" placeholder="Ej: cobro llegó en turno tarde">
                        </div>
                    </div>
                    <div style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;margin-bottom:1rem;">
                        <button class="btn btn-primary btn-sm" onclick="generarVale()">Generar vale</button>
                        <span id="vrGenAlert" style="display:none;" class="alert"></span>
                    </div>
                    <?php endif; ?>

                    <!-- Usar un vale de otra incidencia -->
                    <?php
                    $valesDisponiblesOtros = array_values(array_filter(
                        $valesRegDisponibles,
                        fn($v) => $v['incidencia_origen_id'] != $incidencia['id_incidencia']
                    ));
                    ?>
                    <?php if (!empty($valesDisponiblesOtros)): ?>
                    <p class="form-label" style="margin-bottom:.5rem;">Vales disponibles de otras incidencias</p>
                    <ul class="item-list" style="margin-bottom:.85rem;" id="listaValesOtros">
                        <?php foreach ($valesDisponiblesOtros as $vd): ?>
                        <li id="vd-<?= $vd['id'] ?>">
                            <span class="item-desc">
                                <span style="font-family:monospace;font-weight:700;color:#1e293b;font-size:.8rem;"><?= htmlspecialchars($vd['codigo']) ?></span>
                                <span style="color:#94a3b8;font-size:.72rem;margin-left:.3rem;">
                                    Inc.#<?= $vd['incidencia_origen_id'] ?> · <?= htmlspecialchars($vd['local_desc']) ?> · <?= date('d/m', strtotime($vd['fecha_operacion'])) ?>
                                </span>
                                <?php if ($vd['descripcion'] ?? ''): ?>
                                <span style="color:#64748b;font-size:.72rem;margin-left:.3rem;"><?= htmlspecialchars($vd['descripcion']) ?></span>
                                <?php endif; ?>
                            </span>
                            <span class="item-amount" style="color:#5b21b6;margin-right:.4rem;"><?= $f2($vd['monto']) ?></span>
                            <?php if (!$esCerrada): ?>
                            <button class="btn btn-success btn-sm" style="padding:.2rem .55rem;font-size:.75rem;margin-right:.2rem;"
                                onclick="usarValeId(<?= $vd['id'] ?>, this)">Aplicar</button>
                            <?php endif; ?>
                            <?php if ($esAdmin): ?>
                            <button class="item-rm" onclick="editarValeInline(<?= $vd['id'] ?>, <?= $vd['monto'] ?>, <?= json_encode($vd['descripcion'] ?? '') ?>, this)" title="Editar" style="margin-right:.2rem;">✎</button>
                            <button class="item-rm" onclick="anularValeOtro(<?= $vd['id'] ?>, this)" title="Anular">✕</button>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <span id="vrUsarAlert" style="display:none;margin-top:.4rem;" class="alert"></span>
                    <?php else: ?>
                    <p style="font-size:.78rem;color:#94a3b8;">No hay vales de otras incidencias disponibles.</p>
                    <span id="vrUsarAlert" style="display:none;margin-top:.4rem;" class="alert"></span>
                    <?php endif; ?>

                </div>
            </div>

            <!-- ── 7. Movimiento de incidencia ── -->
            <div class="card">
                <div class="card-head">
                    <p class="card-title">Resolución de incidencia</p>
                </div>
                <div class="card-body">
                    <?php if (!$esCerrada): ?>

                    <!-- Selector de acción -->
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.6rem;margin-bottom:1rem;">
                        <button class="penal-btn" id="btnAccionCondonar" onclick="seleccionarAccion('condonar')"
                                style="padding:.7rem .5rem;">
                            <span class="penal-btn__title">Empresa perdona</span>
                            <span class="penal-btn__name">Condonación</span>
                        </button>
                        <button class="penal-btn" id="btnAccionDescuento" onclick="seleccionarAccion('descuento')"
                                style="padding:.7rem .5rem;">
                            <span class="penal-btn__title">Descuento a trabajador</span>
                            <span class="penal-btn__name">Aplicar descuento</span>
                        </button>
                    </div>

                    <!-- Bloque condonación -->
                    <div id="bloqueCondonar" style="display:none;">
                        <div style="margin-bottom:.6rem;">
                            <label class="form-label">Monto a condonar (S/) *</label>
                            <input type="number" class="form-input" id="montoMov"
                                   min="0.01" step="0.01" placeholder="<?= number_format($pendienteEfectivo,2) ?>"
                                   oninput="calcularPreview()">
                        </div>
                        <div style="margin-bottom:.6rem;">
                            <label class="form-label">Motivo</label>
                            <textarea class="form-input" id="descMov" rows="2"
                                      placeholder="Ej: Error del sistema, monto dentro del margen aceptable…"
                                      style="resize:vertical;"></textarea>
                        </div>
                        <div id="resultPreview" style="display:none;padding:.4rem .7rem;border-radius:7px;
                             font-size:.8rem;font-weight:600;margin-bottom:.65rem;border:1px solid #e2e8f0;"></div>
                        <input type="hidden" id="tipoMov" value="CONDONACION">
                        <div style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;">
                            <button class="btn btn-warn btn-sm" id="btnGuardar" onclick="guardarMovimiento()">
                                Confirmar condonación
                            </button>
                            <span id="movAlert" style="display:none;" class="alert"></span>
                        </div>
                    </div>

                    <!-- Bloque descuento / penalidades -->
                    <div id="bloqueDescuento" style="display:none;">
                        <?php if ($cajera || $vendedora): ?>
                        <p style="font-size:.72rem;color:#64748b;margin-bottom:.6rem;">
                            Selecciona a quién se aplica el descuento. Se pre-llenará el monto pendiente.
                        </p>
                        <div class="penal-row">
                            <?php if ($cajera): ?>
                            <button class="penal-btn" onclick="aplicarPenalidad('cajera')">
                                <span class="penal-btn__title">Cajera</span>
                                <span class="penal-btn__name"><?= $cajera ?></span>
                            </button>
                            <?php endif; ?>
                            <?php if ($vendedora): ?>
                            <button class="penal-btn" onclick="aplicarPenalidad('vendedora')">
                                <span class="penal-btn__title">Vendedora</span>
                                <span class="penal-btn__name"><?= $vendedora ?></span>
                            </button>
                            <?php endif; ?>
                            <?php if ($cajera && $vendedora): ?>
                            <button class="penal-btn" onclick="aplicarPenalidad('ambos')">
                                <span class="penal-btn__title">Ambos</span>
                                <span class="penal-btn__name" style="font-size:.75rem;">50 / 50</span>
                            </button>
                            <?php endif; ?>
                        </div>
                        <div style="margin-top:.85rem;">
                            <label class="form-label">Monto (S/) *</label>
                            <input type="number" class="form-input" id="montoMovDesc"
                                   min="0.01" step="0.01" placeholder="<?= number_format($pendienteEfectivo,2) ?>"
                                   oninput="calcularPreviewDesc()">
                        </div>
                        <div style="margin:.6rem 0;">
                            <label class="form-label">Nota</label>
                            <textarea class="form-input" id="descMovDesc" rows="2"
                                      style="resize:vertical;"
                                      placeholder="Ej: Descuento de sueldo — semana del 26/05"></textarea>
                        </div>
                        <div id="resultPreviewDesc" style="display:none;padding:.4rem .7rem;border-radius:7px;
                             font-size:.8rem;font-weight:600;margin-bottom:.65rem;border:1px solid #e2e8f0;"></div>
                        <div style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;">
                            <button class="btn btn-primary btn-sm" id="btnGuardarDesc" onclick="guardarDescuento()">
                                Confirmar descuento
                            </button>
                            <span id="movAlertDesc" style="display:none;" class="alert"></span>
                        </div>
                        <?php else: ?>
                        <p style="font-size:.82rem;color:#94a3b8;">No hay cajera ni vendedora asignada a esta sesión.</p>
                        <?php endif; ?>
                    </div>

                    <?php else: ?>
                    <div style="text-align:center;padding:1.25rem 0;">
                        <p style="font-size:1.4rem;margin-bottom:.4rem;">✓</p>
                        <p style="font-weight:700;color:#065f46;margin-bottom:.2rem;">Caso cerrado</p>
                        <p style="font-size:.78rem;color:#94a3b8;">
                            <?= $incidencia['fecha_cierre'] ? 'Cerrado el ' . date('d/m/Y H:i', strtotime($incidencia['fecha_cierre'])) : '' ?>
                        </p>
                        <?php if ($esAdmin): ?>
                        <button class="btn btn-warn btn-sm" id="btnReabrir2" style="margin-top:.75rem;">Reabrir caso</button>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ── 7. Descripción del caso ────────────────────── -->
            <div class="card">
                <div class="card-head">
                    <p class="card-title">Descripción del caso</p>
                </div>
                <div class="card-body">
                    <textarea class="form-input" id="descCaso" rows="3"
                              style="resize:vertical;font-size:.85rem;"
                              placeholder="Añade contexto sobre esta incidencia..."
                              ><?= htmlspecialchars($incidencia['descripcion'] ?? '') ?></textarea>
                    <div style="display:flex;gap:.5rem;align-items:center;margin-top:.6rem;">
                        <button class="btn btn-secondary btn-sm" onclick="guardarDescripcion()">
                            Guardar descripción
                        </button>
                        <span id="descAlert" style="display:none;" class="alert"></span>
                    </div>
                </div>
            </div>

        </div><!-- /col izquierda -->

        <!-- ═══════════════════════════════════════════════════════════ -->
        <!-- Columna derecha: calculadora sticky + historial             -->
        <!-- ═══════════════════════════════════════════════════════════ -->
        <div class="calc-panel">

            <!-- ── Pendiente del caso ── -->
            <div class="card">
                <?php
                $esSobrante       = $incidencia['tipo'] === 'SOBRANTE';
                // Progreso respecto al monto original usando la diferencia efectiva actual
                $pctEfectivo      = $original > 0
                    ? min(100, max(0, round(100 * (1 - $pendienteEfectivo / $original))))
                    : 100;
                $barColor         = $pendienteEfectivo <= 0.01
                    ? '#22c55e'
                    : ($pendienteEfectivo <= 10
                        ? '#22c55e'
                        : ($pctEfectivo >= 75 ? '#f59e0b' : '#ef4444'));
                // Diferencia entre monto_pendiente y diferencia efectiva
                $hayDesfase = abs($pendiente - $pendienteEfectivo) > 0.01;
                ?>
                <div class="card-head">
                    <p class="card-title">Pendiente del caso</p>
                    <span class="badge" style="background:<?= $ti['bg'] ?>;color:<?= $ti['color'] ?>;font-size:.62rem;">
                        <?= $ti['label'] ?>
                    </span>
                </div>
                <div class="card-body" style="text-align:center;">
                    <p style="font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;
                              color:#94a3b8;margin-bottom:.15rem;">
                        <?= $esSobrante ? 'Sobrante a regularizar' : 'Por cobrar / descontar' ?>
                    </p>
                    <!-- Número principal: diferencia efectiva del arqueo -->
                    <p class="pend-amount" id="displayPendiente" style="color:<?= $difColor ?>">
                        <?= $f2($pendienteEfectivo) ?>
                    </p>
                    <div class="prog-track" style="margin:.4rem 0 .15rem;">
                        <div class="prog-fill" id="progressFill"
                             style="width:<?= $pctEfectivo ?>%;background:<?= $barColor ?>;"></div>
                    </div>
                    <p style="font-size:.68rem;color:#94a3b8;margin-bottom:.5rem;">
                        <?= $pctEfectivo ?>% resuelto · Original <?= $f2($original) ?>
                    </p>
                    <!-- Texto contextual del estado -->
                    <p id="calc_dif_eff_desc"
                       style="font-size:.72rem;font-weight:600;color:<?= $difColor ?>;
                              margin-bottom:.75rem;line-height:1.35;">
                        <?php if (abs($difActual) <= 0.01): ?>
                            ✓ El arqueo cuadra perfectamente
                        <?php elseif (abs($difActual) <= 10): ?>
                            Margen ≤ S/ 10.00 — habilitado para cerrar
                        <?php elseif ($difActual < 0): ?>
                            Faltan S/ <?= number_format(abs($difActual), 2) ?> en el arqueo
                        <?php else: ?>
                            Sobran S/ <?= number_format($difActual, 2) ?> en el arqueo
                        <?php endif; ?>
                    </p>
                    <!-- Registro histórico: solo si difiere del efectivo -->
                    <?php if ($hayDesfase): ?>
                    <div style="background:#f8fafc;border-radius:7px;padding:.45rem .7rem;
                                text-align:left;border:1px solid #e2e8f0;">
                        <p style="font-size:.6rem;font-weight:700;text-transform:uppercase;
                                  letter-spacing:.05em;color:#94a3b8;margin-bottom:.2rem;">
                            Registro en sistema de incidencias
                        </p>
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:.4rem;">
                            <span style="font-size:.9rem;font-weight:700;color:#475569;">
                                <?= $f2($pendiente) ?>
                            </span>
                            <span style="font-size:.68rem;color:#94a3b8;">
                                <?= count($movimientos) ?> movimiento<?= count($movimientos) !== 1 ? 's' : '' ?>
                            </span>
                        </div>
                        <p style="font-size:.68rem;color:#94a3b8;margin-top:.15rem;line-height:1.3;">
                            Original <?= $f2($original) ?> ·
                            descuentos/abonos <?= $f2(round($original - $pendiente, 2)) ?>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ── Arqueo del turno (desglose) ── -->
            <div class="card">
                <div class="card-head">
                    <p class="card-title">Arqueo del turno</p>
                    <a href="<?= $basePath ?>/caja/reporte/<?= $sesionId ?>" target="_blank"
                       style="font-size:.7rem;color:#60a5fa;text-decoration:none;font-weight:600;">
                        Sesión #<?= $sesionId ?> ↗
                    </a>
                </div>
                <div class="card-body" style="font-size:.82rem;padding-top:.7rem;">

                    <!-- ─ A: Efectivo físico ─ -->
                    <p style="font-size:.6rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;
                              color:#64748b;background:#f1f5f9;border-radius:5px;
                              padding:.2rem .55rem;margin-bottom:.4rem;">
                        Efectivo físico · lo que hay
                    </p>
                    <div style="margin-bottom:.7rem;">
                        <div style="display:flex;justify-content:space-between;padding:.22rem 0;">
                            <span style="color:#475569;">Conteo de caja</span>
                            <span style="font-weight:600;" id="calc_efectivo"><?= $f2($totalContado) ?></span>
                        </div>
                        <?php foreach ($rectifs ?? [] as $ri):
                            $rMonto = (float)$ri['monto'];
                            $rLabel = htmlspecialchars($ri['etiqueta'] ?? ($ri['descripcion'] ?? 'rectificación'));
                        ?>
                        <div style="display:flex;justify-content:space-between;padding:.18rem 0;
                                    border-top:1px dashed #f1f5f9;">
                            <span style="color:#64748b;font-size:.78rem;">+ Rectif.: <?= $rLabel ?></span>
                            <span style="font-weight:600;font-size:.78rem;
                                         color:<?= $rMonto >= 0 ? '#16a34a' : '#dc2626' ?>;">
                                <?= ($rMonto >= 0 ? '+' : '−') ?>S/ <?= number_format(abs($rMonto), 2) ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                        <div style="display:flex;justify-content:space-between;padding:.3rem 0;
                                    border-top:2px solid #e2e8f0;margin-top:.25rem;">
                            <span style="font-weight:700;color:#1e293b;">= Total físico</span>
                            <span style="font-weight:800;color:#1e293b;" id="calc_es"><?= $f2($loQueEs) ?></span>
                        </div>
                    </div>

                    <!-- ─ B: Efectivo esperado ─ -->
                    <p style="font-size:.6rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;
                              color:#64748b;background:#f1f5f9;border-radius:5px;
                              padding:.2rem .55rem;margin-bottom:.4rem;">
                        Efectivo esperado · lo que debería haber
                    </p>
                    <div style="margin-bottom:.7rem;">

                        <!-- Apertura -->
                        <div style="display:flex;justify-content:space-between;padding:.22rem 0;">
                            <span style="color:#475569;">(+) Apertura del turno</span>
                            <span style="font-weight:600;color:#16a34a;">+<?= $f2($saldoIni) ?></span>
                        </div>

                        <!-- Ventas -->
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;
                                    padding:.22rem 0;border-top:1px dashed #f1f5f9;">
                            <span style="color:#475569;">
                                (+) Ventas del turno
                                <?php if (abs((float)($sumCorrDelta ?? 0)) > 0.001): ?>
                                <span style="display:block;font-size:.7rem;color:#94a3b8;">
                                    base <?= $f2($ventaMonto) ?>
                                    <?= (float)$sumCorrDelta >= 0 ? '+' : '−' ?> corrección
                                    S/ <?= number_format(abs((float)$sumCorrDelta), 2) ?>
                                </span>
                                <?php endif; ?>
                            </span>
                            <span style="font-weight:600;color:#16a34a;white-space:nowrap;">
                                +<?= $f2($totalVentas) ?>
                            </span>
                        </div>

                        <!-- Egresos -->
                        <div style="display:flex;justify-content:space-between;padding:.22rem 0;
                                    border-top:1px dashed #f1f5f9;">
                            <span style="color:#475569;">(−) Egresos del turno</span>
                            <span style="font-weight:600;color:#dc2626;">−<?= $f2($totalGastos) ?></span>
                        </div>

                        <!-- Cobros digitales -->
                        <div style="border-top:1px dashed #f1f5f9;padding:.22rem 0 0;">
                            <div style="display:flex;justify-content:space-between;padding-bottom:.1rem;">
                                <span style="color:#475569;">(−) Cobros digitales</span>
                                <span style="font-weight:600;color:#dc2626;">−<?= $f2($digitalDecl) ?></span>
                            </div>
                            <?php
                            $digActivos = array_values(array_filter(
                                $digitales ?? [],
                                fn($d) => in_array($d['estado'], ['PENDIENTE','APROBADO'])
                            ));
                            foreach ($digActivos as $dg):
                                $isPend = $dg['estado'] === 'PENDIENTE';
                            ?>
                            <div style="display:flex;justify-content:space-between;
                                        padding:.08rem 0 .08rem .65rem;font-size:.75rem;">
                                <span style="color:#94a3b8;">
                                    · <?= htmlspecialchars($dg['modo_desc']) ?>
                                    <?php if ($isPend): ?>
                                    <span style="color:#d97706;font-size:.65rem;">(pendiente)</span>
                                    <?php endif; ?>
                                </span>
                                <span style="color:#64748b;">−<?= $f2($dg['monto']) ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Ajustes al esperado -->
                        <?php if (!empty($ajustesDisplay)): ?>
                        <div style="border-top:1px dashed #f1f5f9;padding:.18rem 0 0;">
                            <p style="font-size:.68rem;font-weight:700;color:#64748b;margin:.1rem 0 .2rem;">
                                Ajustes aplicados al cuadre:
                            </p>
                            <?php foreach ($ajustesDisplay as $aj):
                                // AGREGAR: el dinero salió de caja y no estaba registrado → reduce el esperado
                                // QUITAR:  se espera más efectivo del calculado → aumenta el esperado
                                $esAgr  = $aj['accion'] === 'AGREGAR';
                                $signo  = $esAgr ? '−' : '+';
                                $acolor = $esAgr ? '#dc2626' : '#16a34a';
                                switch ($aj['tipo']) {
                                    case 'PERSONAL':
                                        $ajLab = 'Pago a ' . ($aj['staff_desc'] ?? 'personal');
                                        if ($aj['tipo_pago'] ?? '') $ajLab .= ' (' . $aj['tipo_pago'] . ')';
                                        break;
                                    case 'LOCAL':
                                        $ajLab = 'Gasto local · ' . ($aj['local_desc'] ?? '?');
                                        break;
                                    case 'COMPRA':
                                        $ajLab = 'Compra · ' . ($aj['descripcion'] ?? '?');
                                        break;
                                    case 'DEPOSITO':
                                        $ajLab = 'Depósito banco';
                                        if ($aj['descripcion'] ?? '') $ajLab .= ' · ' . $aj['descripcion'];
                                        break;
                                    default:
                                        $ajLab = $aj['descripcion'] ?? $aj['tipo'];
                                }
                            ?>
                            <div style="display:flex;justify-content:space-between;align-items:flex-start;
                                        padding:.18rem 0;border-top:1px dashed #f8fafc;">
                                <span style="color:#475569;flex:1;padding-right:.3rem;font-size:.78rem;">
                                    (<?= $signo ?>) <?= htmlspecialchars($ajLab) ?>
                                </span>
                                <span style="font-weight:600;color:<?= $acolor ?>;white-space:nowrap;font-size:.78rem;">
                                    <?= $signo ?>S/ <?= number_format((float)$aj['monto'], 2) ?>
                                </span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <!-- Total esperado -->
                        <div style="display:flex;justify-content:space-between;padding:.3rem 0;
                                    border-top:2px solid #e2e8f0;margin-top:.25rem;">
                            <span style="font-weight:700;color:#1e293b;">= Total esperado</span>
                            <span style="font-weight:800;color:#1e293b;" id="calc_esperado">
                                <?= $f2($loQueSeDice) ?>
                            </span>
                        </div>
                    </div>

                    <!-- ─ Resultado ─ -->
                    <div id="calc_resultado_box"
                         style="border-radius:9px;padding:.6rem .8rem;
                                background:<?= $difBg ?>;border:1.5px solid <?= $difBd ?>;">
                        <div style="display:flex;align-items:center;justify-content:space-between;
                                    margin-bottom:.12rem;">
                            <span id="calc_dif_label"
                                  style="font-size:.65rem;font-weight:800;text-transform:uppercase;
                                         letter-spacing:.06em;color:<?= $difColor ?>;">
                                <?= $difLabel ?>
                            </span>
                            <span id="calc_dif"
                                  style="font-size:1.5rem;font-weight:800;color:<?= $difColor ?>;">
                                <?= $f2($difActual) ?>
                            </span>
                        </div>
                        <p id="calc_estado"
                           style="font-size:.7rem;color:<?= $difColor ?>;margin:0;
                                  font-weight:600;line-height:1.3;">
                            <?php if (abs($difActual) <= 0.01): ?>
                                ✓ El arqueo cuadra perfectamente
                            <?php elseif ($difActual < 0): ?>
                                Hay S/ <?= number_format(abs($difActual), 2) ?> menos en caja de lo esperado
                            <?php else: ?>
                                Hay S/ <?= number_format($difActual, 2) ?> más en caja de lo esperado
                            <?php endif; ?>
                        </p>
                    </div>

                </div>
            </div>

            <!-- Historial movimientos incidencia -->
            <div class="card">
                <div class="card-head">
                    <p class="card-title">
                        Historial movimientos
                        <span style="font-weight:500;text-transform:none;letter-spacing:0;
                                     color:#cbd5e1;font-size:.68rem;margin-left:.3rem;">
                            · <?= count($movimientos) ?>
                        </span>
                    </p>
                </div>
                <div class="card-body" style="padding-top:.5rem;">
                    <?php if (empty($movimientos)): ?>
                    <p style="color:#94a3b8;font-size:.82rem;">Sin movimientos aún.</p>
                    <?php else: ?>
                    <div class="mov-scroll">
                        <?php foreach ($movimientos as $mov):
                            $mi    = $movTipoInfo[$mov['tipo']] ?? ['label'=>$mov['tipo'],'dot'=>'#94a3b8','bg'=>'#f1f5f9','color'=>'#475569','signo'=>''];
                        ?>
                        <div class="mov-item">
                            <span class="mov-dot" style="background:<?= $mi['dot'] ?>;"></span>
                            <div class="mov-body">
                                <span class="badge" style="background:<?= $mi['bg'] ?>;color:<?= $mi['color'] ?>"><?= $mi['label'] ?></span>
                                <?php if ($mov['descripcion']): ?>
                                <span style="margin-left:.3rem;font-size:.78rem;color:#475569;">
                                    <?= htmlspecialchars($mov['descripcion']) ?>
                                </span>
                                <?php endif; ?>
                                <div class="mov-meta">
                                    <?= htmlspecialchars($mov['registrado_por_nombre'] ?? '—') ?>
                                    · <?= date('d/m/Y H:i', strtotime($mov['fecha'])) ?>
                                </div>
                            </div>
                            <div class="mov-monto" style="color:<?= $mi['color'] ?>">
                                <?= $mi['signo'] ?><?= $f2($mov['monto']) ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Datos del caso -->
            <div class="card">
                <div class="card-head"><p class="card-title">Datos del caso</p></div>
                <div class="card-body" style="font-size:.8rem;color:#475569;">
                    <div style="display:flex;flex-direction:column;gap:.5rem;">
                        <div><span style="color:#94a3b8;font-size:.68rem;text-transform:uppercase;font-weight:700;">Sesión</span><br>
                            <a href="<?= $basePath ?>/caja/reporte/<?= $sesionId ?>" target="_blank" style="color:#3b82f6;font-weight:700;">#<?= $sesionId ?></a>
                        </div>
                        <?php if ($cajera): ?>
                        <div><span style="color:#94a3b8;font-size:.68rem;text-transform:uppercase;font-weight:700;">Cajera</span><br>
                            <strong><?= $cajera ?></strong>
                        </div>
                        <?php endif; ?>
                        <?php if ($vendedora): ?>
                        <div><span style="color:#94a3b8;font-size:.68rem;text-transform:uppercase;font-weight:700;">Vendedora</span><br>
                            <strong><?= $vendedora ?></strong>
                        </div>
                        <?php endif; ?>
                        <div><span style="color:#94a3b8;font-size:.68rem;text-transform:uppercase;font-weight:700;">Apertura</span><br>
                            <?= date('d/m/Y H:i', strtotime($incidencia['fecha_apertura'])) ?>
                        </div>
                        <?php if ($incidencia['fecha_cierre']): ?>
                        <div><span style="color:#94a3b8;font-size:.68rem;text-transform:uppercase;font-weight:700;">Cierre</span><br>
                            <?= date('d/m/Y H:i', strtotime($incidencia['fecha_cierre'])) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div><!-- /col derecha -->
    </div><!-- /inc-cols -->
</div><!-- /inc-wrap -->
</main>

<script>
const BASE       = '<?= $basePath ?>';
const INC_ID     = <?= $incidencia['id_incidencia'] ?>;
const SESION_ID  = <?= $sesionId ?>;
const PENDIENTE  = <?= $pendienteEfectivo ?>;  // diferencia efectiva actual
const ORIGINAL   = <?= $original ?>;
const CAJERA     = <?= json_encode($cajera) ?>;
const VENDEDORA  = <?= json_encode($vendedora) ?>;

// Valores de referencia para calculadora
let calcEfectivo  = <?= $totalContado ?>;
let calcEsperado  = <?= $totalEsperado ?>;
const CALC_SALDO_INI   = <?= $saldoIni ?>;
const CALC_VENTAS      = <?= $totalVentas ?>;
const CALC_GASTOS      = <?= $totalGastos ?>;
const CALC_DIGITAL     = <?= $digitalDecl ?>;
const CALC_AJUSTES_ESP = <?= round($sumAjustesEsp, 2) ?>;
const CALC_RECTIFS     = <?= round($sumRectifs, 2) ?>;

// ── Conteo: recalcular total al escribir ────────────────
function recalcConteo() {
    const ext  = parseFloat(document.getElementById('cnt_exterior')?.value)        || 0;
    const mon  = parseFloat(document.getElementById('cnt_monedas')?.value)          || 0;
    const bil  = parseFloat(document.getElementById('cnt_billetes_caja')?.value)    || 0;
    const fue  = parseFloat(document.getElementById('cnt_billetes_fuerte')?.value)  || 0;
    const age  = parseFloat(document.getElementById('cnt_agente_bcp')?.value)       || 0;
    const total = ext + mon + bil + fue + age;
    document.getElementById('cnt_total_preview').textContent = 'S/ ' + total.toFixed(2);
    document.getElementById('conteoTotal').textContent       = 'S/ ' + total.toFixed(2);
    // Actualizar calculadora
    calcEfectivo = total;
    actualizarCalc();
}

// ── Calculadora arqueo ──────────────────────────────────
function actualizarCalc() {
    const loQueEs     = calcEfectivo + CALC_RECTIFS;
    const loQueSeDice = CALC_SALDO_INI + CALC_VENTAS - CALC_GASTOS - CALC_DIGITAL + CALC_AJUSTES_ESP;
    const dif         = Math.round((loQueEs - loQueSeDice) * 100) / 100;
    const absDif      = Math.abs(dif);
    const color  = absDif < 0.01 ? '#16a34a' : (dif > 0 ? '#1e40af' : '#dc2626');
    const bg     = absDif < 0.01 ? '#d1fae5' : (dif > 0 ? '#dbeafe' : '#fee2e2');
    const bd     = absDif < 0.01 ? '#a7f3d0' : (dif > 0 ? '#93c5fd' : '#fecaca');
    const label  = absDif < 0.01 ? 'CUADRADO' : (dif > 0 ? 'SOBRANTE' : 'FALTANTE');
    const estado = absDif < 0.01
        ? '✓ El arqueo cuadra perfectamente'
        : dif < 0
            ? `Hay S/ ${absDif.toFixed(2)} menos en caja de lo esperado`
            : `Hay S/ ${absDif.toFixed(2)} más en caja de lo esperado`;
    const effDesc = absDif < 0.01
        ? 'El arqueo cuadra perfectamente ✓'
        : absDif <= 10
            ? 'Margen ≤ S/ 10.00 — habilitado para cerrar'
            : dif < 0
                ? `Faltan S/ ${absDif.toFixed(2)} en el arqueo`
                : `Sobran S/ ${absDif.toFixed(2)} en el arqueo`;

    // Arqueo del turno
    document.getElementById('calc_efectivo').textContent  = 'S/ ' + calcEfectivo.toFixed(2);
    document.getElementById('calc_es').textContent        = 'S/ ' + loQueEs.toFixed(2);
    document.getElementById('calc_esperado').textContent  = 'S/ ' + loQueSeDice.toFixed(2);

    // Resultado box
    const box = document.getElementById('calc_resultado_box');
    if (box) { box.style.background = bg; box.style.borderColor = bd; }
    const lblEl = document.getElementById('calc_dif_label');
    if (lblEl) { lblEl.textContent = label; lblEl.style.color = color; }
    const difEl = document.getElementById('calc_dif');
    if (difEl) { difEl.textContent = 'S/ ' + dif.toFixed(2); difEl.style.color = color; }
    const estEl = document.getElementById('calc_estado');
    if (estEl) { estEl.textContent = estado; estEl.style.color = color; }

    // Panel "Pendiente del caso" — número principal y barra
    const pendEl = document.getElementById('displayPendiente');
    if (pendEl) { pendEl.textContent = 'S/ ' + absDif.toFixed(2); pendEl.style.color = color; }
    const pctEfectivo = ORIGINAL > 0
        ? Math.min(100, Math.max(0, Math.round(100 * (1 - absDif / ORIGINAL))))
        : 100;
    const barColorEff = absDif < 0.01 ? '#22c55e'
        : absDif <= 10              ? '#22c55e'
        : pctEfectivo >= 75         ? '#f59e0b' : '#ef4444';
    const barEl = document.getElementById('progressFill');
    if (barEl) { barEl.style.width = pctEfectivo + '%'; barEl.style.background = barColorEff; }
    const effDescEl = document.getElementById('calc_dif_eff_desc');
    if (effDescEl) { effDescEl.textContent = effDesc; effDescEl.style.color = color; }
}

// ── Selector de acción ──────────────────────────────────
function seleccionarAccion(accion) {
    document.getElementById('bloqueCondonar').style.display  = accion === 'condonar'  ? 'block' : 'none';
    document.getElementById('bloqueDescuento').style.display = accion === 'descuento' ? 'block' : 'none';
    const btnCond = document.getElementById('btnAccionCondonar');
    const btnDesc = document.getElementById('btnAccionDescuento');
    btnCond.style.borderColor = accion === 'condonar'  ? '#f59e0b' : '#e2e8f0';
    btnDesc.style.borderColor = accion === 'descuento' ? '#3b82f6' : '#e2e8f0';
    btnCond.style.background  = accion === 'condonar'  ? '#fef3c7' : '#f8fafc';
    btnDesc.style.background  = accion === 'descuento' ? '#eff6ff' : '#f8fafc';
}

// ── Calculadora live — condonación ──────────────────────
function calcularPreview() {
    const monto = parseFloat(document.getElementById('montoMov')?.value) || 0;
    const prev  = document.getElementById('resultPreview');
    if (!prev) return;
    if (!monto) { prev.style.display = 'none'; return; }
    const result = Math.max(0, PENDIENTE - monto);
    showPreview(prev, result);
}

// ── Calculadora live — descuento ────────────────────────
function calcularPreviewDesc() {
    const monto = parseFloat(document.getElementById('montoMovDesc')?.value) || 0;
    const prev  = document.getElementById('resultPreviewDesc');
    if (!prev) return;
    if (!monto) { prev.style.display = 'none'; return; }
    const result = Math.max(0, PENDIENTE - monto);
    showPreview(prev, result);
}

function showPreview(el, result) {
    const color = result <= 10 ? '#065f46' : '#dc2626';
    const bg    = result <= 10 ? '#d1fae5' : '#fee2e2';
    const pct   = ORIGINAL > 0 ? Math.min(100, Math.round(100 * (1 - result / ORIGINAL))) : 100;
    el.style.cssText = `display:block;background:${bg};color:${color};border:1px solid ${result<=10?'#a7f3d0':'#fecaca'}`;
    el.innerHTML = `→ Pendiente resultante: <strong>S/ ${result.toFixed(2)}</strong> (${pct}% resuelto)${result <= 10 ? ' ✓ listo para cerrar' : ''}`;
}

// ── Penalidades (pre-llenan bloque descuento) ───────────
function aplicarPenalidad(quien) {
    const montoEl = document.getElementById('montoMovDesc');
    const descEl  = document.getElementById('descMovDesc');
    if (!montoEl || !descEl) return;
    if (quien === 'cajera') {
        montoEl.value = PENDIENTE.toFixed(2);
        descEl.value  = `Descuento de sueldo — Cajera: ${CAJERA}`;
    } else if (quien === 'vendedora') {
        montoEl.value = PENDIENTE.toFixed(2);
        descEl.value  = `Descuento de sueldo — Vendedora: ${VENDEDORA}`;
    } else {
        montoEl.value = (PENDIENTE / 2).toFixed(2);
        descEl.value  = `Descuento de sueldo — Cajera y Vendedora (50/50): ${CAJERA} / ${VENDEDORA}`;
    }
    calcularPreviewDesc();
    montoEl.focus();
}

// ── Helper fetch ────────────────────────────────────────
async function apiPost(url, body) {
    const r = await fetch(url, {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify(body),
    });
    const json = await r.json();
    if (!r.ok) throw new Error(json.message || 'Error');
    return json;
}

function mostrarAlerta(elId, msg, tipo) {
    const el = document.getElementById(elId);
    if (!el) return;
    el.textContent  = msg;
    el.className    = 'alert ' + (tipo === 'ok' ? 'alert-ok' : 'alert-err');
    el.style.display = 'inline-block';
    if (tipo === 'ok') setTimeout(() => { el.style.display='none'; }, 2500);
}

// ── Guardar conteo ──────────────────────────────────────
async function guardarConteo() {
    const btn = document.getElementById('btnConteo');
    btn.disabled = true; btn.textContent = 'Guardando…';
    try {
        await apiPost(`${BASE}/caja/api/sesion/${SESION_ID}/conteo`, {
            exterior:        parseFloat(document.getElementById('cnt_exterior').value)        || 0,
            monedas:         parseFloat(document.getElementById('cnt_monedas').value)          || 0,
            billetes_caja:   parseFloat(document.getElementById('cnt_billetes_caja').value)    || 0,
            billetes_fuerte: parseFloat(document.getElementById('cnt_billetes_fuerte').value)  || 0,
            agente_bcp:      parseFloat(document.getElementById('cnt_agente_bcp').value)       || 0,
        });
        mostrarAlerta('conteoAlert', '✓ Conteo actualizado', 'ok');
        setTimeout(() => location.reload(), 900);
    } catch(e) {
        mostrarAlerta('conteoAlert', e.message, 'err');
        btn.disabled = false; btn.textContent = 'Guardar conteo';
    }
}

// ── Guardar ventas ──────────────────────────────────────
async function guardarVenta() {
    const monto = parseFloat(document.getElementById('venta_monto').value) || 0;
    const desc  = document.getElementById('venta_desc').value.trim();
    if (monto <= 0) { mostrarAlerta('ventaAlert', 'Ingresa un monto válido', 'err'); return; }
    try {
        await apiPost(`${BASE}/caja/api/sesion/${SESION_ID}/corregir-venta`, { monto_nuevo: monto, motivo: desc });
        mostrarAlerta('ventaAlert', '✓ Venta actualizada', 'ok');
        setTimeout(() => location.reload(), 900);
    } catch(e) {
        mostrarAlerta('ventaAlert', e.message, 'err');
    }
}

// ── Guardar rectificación ───────────────────────────────
async function guardarRectif() {
    const tipo  = document.getElementById('rect_tipo').value;
    const monto = parseFloat(document.getElementById('rect_monto').value) || 0;
    const desc  = document.getElementById('rect_desc').value.trim();
    if (!tipo || monto <= 0) { mostrarAlerta('rectAlert', 'Completa tipo y monto', 'err'); return; }
    if (!desc) { mostrarAlerta('rectAlert', 'La descripción es requerida', 'err'); return; }
    try {
        await apiPost(`${BASE}/caja/api/reporte/${SESION_ID}/rectificar`, {
            tipo_rect_id: parseInt(tipo), monto, descripcion: desc
        });
        mostrarAlerta('rectAlert', '✓ Rectificación agregada', 'ok');
        setTimeout(() => location.reload(), 900);
    } catch(e) {
        mostrarAlerta('rectAlert', e.message, 'err');
    }
}

async function eliminarRectif(rectId, btn) {
    if (!confirm('¿Eliminar esta rectificación?')) return;
    const password = prompt('Contraseña admin:');
    if (!password) return;
    try {
        await apiPost(`${BASE}/caja/api/rectificacion/${rectId}/eliminar`, { password });
        btn.closest('li').remove();
    } catch(e) { alert(e.message); }
}

// ── Cobros electrónicos ─────────────────────────────────
async function guardarCobro() {
    const modo  = parseInt(document.getElementById('ce_modo_id').value);
    const monto = parseFloat(document.getElementById('ce_monto').value) || 0;
    const num   = document.getElementById('ce_num_op').value.trim();
    if (!modo)   { mostrarAlerta('ceAlert', 'Selecciona el modo de pago', 'err'); return; }
    if (monto <= 0) { mostrarAlerta('ceAlert', 'Ingresa un monto válido', 'err'); return; }
    try {
        await apiPost(`${BASE}/incidencias/api/${INC_ID}/cobro-electronico`, {
            modo_id: modo, monto, numero_operacion: num || null
        });
        mostrarAlerta('ceAlert', '✓ Cobro agregado', 'ok');
        setTimeout(() => location.reload(), 900);
    } catch(e) { mostrarAlerta('ceAlert', e.message, 'err'); }
}

async function eliminarCobro(movId, btn) {
    if (!confirm('¿Eliminar este cobro?')) return;
    try {
        await apiPost(`${BASE}/caja/api/pago-digital/${movId}/eliminar`, {});
        btn.closest('li').remove();
    } catch(e) { alert(e.message); }
}

async function eliminarCobroAdmin(sesionId, movId, btn) {
    if (!confirm('¿Eliminar este cobro aprobado? Se requiere contraseña admin.')) return;
    const password = prompt('Contraseña admin:');
    if (!password) return;
    try {
        await apiPost(`${BASE}/caja/api/sesion/${sesionId}/pago-digital/${movId}/eliminar-admin`, { password });
        btn.closest('li').remove();
    } catch(e) { alert(e.message); }
}

// ── Ajuste tipo change ──────────────────────────────────
function ajTipoChange() {
    const tipo = document.getElementById('aj_tipo').value;
    const bloques = ['cobro','personal','local','compra','deposito','otro'];
    bloques.forEach(b => {
        document.getElementById('aj_bloque_' + b).style.display = 'none';
    });
    const map = {PERSONAL:'personal', LOCAL:'local', COMPRA:'compra', DEPOSITO:'deposito', OTRO:'otro'};
    const bloque = map[tipo] || 'personal';
    document.getElementById('aj_bloque_' + bloque).style.display = 'block';
}

// ── Guardar ajuste ──────────────────────────────────────
async function guardarAjuste() {
    const accion = document.getElementById('aj_accion').value;
    const tipo   = document.getElementById('aj_tipo').value;
    const monto  = parseFloat(document.getElementById('aj_monto').value) || 0;
    if (monto <= 0) { mostrarAlerta('ajAlert', 'Ingresa un monto válido', 'err'); return; }

    const payload = { accion, tipo, monto };

    if (tipo === 'COBRO') {
        const desc = document.getElementById('aj_desc_cobro').value.trim();
        if (!desc) { mostrarAlerta('ajAlert', 'La descripción es requerida para Cobro', 'err'); return; }
        payload.descripcion = desc;
        const modoId = document.getElementById('aj_modo_id').value;
        if (modoId) payload.modo_id = parseInt(modoId);

    } else if (tipo === 'PERSONAL') {
        const refId = document.getElementById('aj_ref_personal').value;
        if (!refId) { mostrarAlerta('ajAlert', 'Selecciona el personal', 'err'); return; }
        payload.ref_id    = parseInt(refId);
        payload.tipo_pago = document.getElementById('aj_tipopago').value;
        const nota = document.getElementById('aj_desc_personal').value.trim();
        if (nota) payload.descripcion = nota;

    } else if (tipo === 'LOCAL') {
        const localId = document.getElementById('aj_ref_local').value;
        if (!localId) { mostrarAlerta('ajAlert', 'Selecciona el local', 'err'); return; }
        payload.ref_id      = parseInt(localId);
        const conceptoId    = document.getElementById('aj_ref2_concepto').value;
        if (conceptoId) payload.ref2_id = parseInt(conceptoId);
        const desc = document.getElementById('aj_desc_local').value.trim();
        if (desc) payload.descripcion = desc;

    } else if (tipo === 'COMPRA') {
        const desc = document.getElementById('aj_desc_compra').value.trim();
        if (!desc) { mostrarAlerta('ajAlert', 'La descripción es requerida para Compra', 'err'); return; }
        payload.descripcion   = desc;
        const doc = document.getElementById('aj_doc_compra').value.trim();
        if (doc) payload.tipo_documento = doc;

    } else if (tipo === 'DEPOSITO') {
        const desc  = document.getElementById('aj_desc_deposito').value.trim();
        const banco = document.getElementById('aj_banco_deposito').value;
        if (!desc) { mostrarAlerta('ajAlert', 'Ingresa el N° de operación', 'err'); return; }
        payload.descripcion = desc;
        payload.banco       = banco;

    } else {
        payload.descripcion = document.getElementById('aj_desc_otro').value.trim();
    }

    try {
        await apiPost(`${BASE}/caja/api/sesion/${SESION_ID}/ajuste-esperado`, payload);
        mostrarAlerta('ajAlert', '✓ Ajuste agregado', 'ok');
        setTimeout(() => location.reload(), 900);
    } catch(e) {
        mostrarAlerta('ajAlert', e.message, 'err');
    }
}

async function eliminarAjuste(ajId, btn) {
    if (!confirm('¿Eliminar este ajuste?')) return;
    const password = prompt('Contraseña admin:');
    if (!password) return;
    try {
        await apiPost(`${BASE}/caja/api/ajuste-esperado/${ajId}/eliminar`, { password });
        btn.closest('li').remove();
    } catch(e) { alert(e.message); }
}

// ── Vales SoloBank ──────────────────────────────────────
async function asignarVale() {
    const codigo = document.getElementById('sb_codigo').value;
    if (!codigo) { mostrarAlerta('sbAlert', 'Selecciona un vale', 'err'); return; }
    try {
        await apiPost(`${BASE}/caja/api/sesion/${SESION_ID}/solobank`, { codigo });
        mostrarAlerta('sbAlert', '✓ Vale asignado', 'ok');
        setTimeout(() => location.reload(), 900);
    } catch(e) {
        mostrarAlerta('sbAlert', e.message, 'err');
    }
}

async function quitarVale(movId, btn) {
    if (!confirm('¿Quitar este vale?')) return;
    try {
        await apiPost(`${BASE}/caja/api/solobank-mov/${movId}/quitar`, {});
        btn.closest('li').remove();
    } catch(e) { alert(e.message); }
}

// ── Guardar condonación ─────────────────────────────────
async function guardarMovimiento() {
    const btn   = document.getElementById('btnGuardar');
    const monto = parseFloat(document.getElementById('montoMov').value);
    const desc  = document.getElementById('descMov').value.trim();
    if (!monto || monto <= 0) { mostrarAlerta('movAlert', 'Ingresa un monto válido.', 'err'); return; }
    btn.disabled = true; btn.textContent = 'Guardando…';
    try {
        await apiPost(`${BASE}/incidencias/api/${INC_ID}/abonar`, { tipo: 'CONDONACION', monto, descripcion: desc });
        mostrarAlerta('movAlert', '✓ Condonación registrada', 'ok');
        setTimeout(() => location.reload(), 800);
    } catch(e) {
        mostrarAlerta('movAlert', e.message, 'err');
        btn.disabled = false; btn.textContent = 'Confirmar condonación';
    }
}

// ── Guardar descuento / penalidad ───────────────────────
async function guardarDescuento() {
    const btn   = document.getElementById('btnGuardarDesc');
    const monto = parseFloat(document.getElementById('montoMovDesc').value);
    const desc  = document.getElementById('descMovDesc').value.trim();
    if (!monto || monto <= 0) { mostrarAlerta('movAlertDesc', 'Ingresa un monto válido.', 'err'); return; }
    if (!desc) { mostrarAlerta('movAlertDesc', 'Agrega una nota con el nombre del trabajador.', 'err'); return; }
    btn.disabled = true; btn.textContent = 'Guardando…';
    try {
        await apiPost(`${BASE}/incidencias/api/${INC_ID}/abonar`, { tipo: 'ABONO', monto, descripcion: desc });
        mostrarAlerta('movAlertDesc', '✓ Descuento registrado', 'ok');
        setTimeout(() => location.reload(), 800);
    } catch(e) {
        mostrarAlerta('movAlertDesc', e.message, 'err');
        btn.disabled = false; btn.textContent = 'Confirmar descuento';
    }
}

// ── Guardar descripción ─────────────────────────────────
async function guardarDescripcion() {
    const desc = document.getElementById('descCaso').value.trim();
    try {
        await apiPost(`${BASE}/incidencias/api/${INC_ID}/descripcion`, { descripcion: desc });
        mostrarAlerta('descAlert', '✓ Guardado', 'ok');
    } catch(e) {
        mostrarAlerta('descAlert', e.message, 'err');
    }
}

// ── Cerrar / Reabrir caso ───────────────────────────────
document.getElementById('btnCerrarManual')?.addEventListener('click', async () => {
    if (!confirm('¿Cerrar esta incidencia? El pendiente quedará en S/ 0.00.')) return;
    const r = await fetch(`${BASE}/incidencias/api/${INC_ID}/cerrar`, { method:'POST' });
    if (r.ok) location.reload();
    else { const j = await r.json(); alert(j.message || 'Error al cerrar'); }
});

function bindReabrir(el) {
    el?.addEventListener('click', async () => {
        if (!confirm('¿Reabrir esta incidencia?')) return;
        const r = await fetch(`${BASE}/incidencias/api/${INC_ID}/reabrir`, { method:'POST' });
        if (r.ok) location.reload();
        else alert('Error al reabrir');
    });
}
bindReabrir(document.getElementById('btnReabrir'));
bindReabrir(document.getElementById('btnReabrir2'));

// Mostrar bloque PERSONAL por defecto al cargar
ajTipoChange();

// ── Vales de regularización ─────────────────────────────
async function generarVale() {
    const monto = parseFloat(document.getElementById('vr_monto')?.value) || 0;
    const desc  = document.getElementById('vr_desc')?.value.trim() || '';
    if (monto <= 0) { mostrarAlerta('vrGenAlert', 'Ingresa un monto válido', 'err'); return; }
    try {
        const json = await apiPost(`${BASE}/incidencias/api/${INC_ID}/generar-vale`, { monto, descripcion: desc });
        mostrarAlerta('vrGenAlert', `✓ Vale generado: ${json.data?.codigo}`, 'ok');
        setTimeout(() => location.reload(), 1200);
    } catch(e) {
        mostrarAlerta('vrGenAlert', e.message, 'err');
    }
}

async function usarValeId(valeId, btn) {
    if (!confirm('¿Aplicar este vale? Se registrará un ABONO en la incidencia origen y un ajuste en esta sesión.')) return;
    try {
        await apiPost(`${BASE}/incidencias/api/${INC_ID}/usar-vale`, { vale_id: valeId });
        mostrarAlerta('vrUsarAlert', '✓ Vale aplicado', 'ok');
        setTimeout(() => location.reload(), 1200);
    } catch(e) { mostrarAlerta('vrUsarAlert', e.message, 'err'); }
}

async function anularValeOtro(valeId, btn) {
    if (!confirm('¿Anular este vale? No se podrá usar.')) return;
    try {
        await apiPost(`${BASE}/incidencias/api/${INC_ID}/anular-vale`, { vale_id: valeId });
        btn.closest('li').remove();
    } catch(e) { alert(e.message); }
}

function editarValeInline(valeId, montoActual, descActual, btn) {
    const li = btn.closest('li');
    if (li.querySelector('.vr-edit-form')) return;
    const form = document.createElement('div');
    form.className = 'vr-edit-form';
    form.style.cssText = 'display:flex;gap:.4rem;align-items:center;flex-wrap:wrap;margin-top:.4rem;width:100%;';
    form.innerHTML = `
        <input type="number" step="0.01" min="0.01" value="${montoActual}" style="width:90px;" class="form-input" placeholder="Monto">
        <input type="text" value="${descActual || ''}" style="flex:1;min-width:120px;" class="form-input" placeholder="Nota (opcional)">
        <button class="btn btn-primary btn-sm" style="padding:.2rem .55rem;font-size:.75rem;">Guardar</button>
        <button class="btn btn-secondary btn-sm" style="padding:.2rem .55rem;font-size:.75rem;">Cancelar</button>
    `;
    const [inMonto, inDesc, btnGuardar, btnCancelar] = form.querySelectorAll('input, button');
    btnCancelar.onclick = () => form.remove();
    btnGuardar.onclick = async () => {
        const monto = parseFloat(inMonto.value);
        if (!monto || monto <= 0) { alert('Monto inválido'); return; }
        try {
            await apiPost(`${BASE}/incidencias/api/${INC_ID}/editar-vale`, { vale_id: valeId, monto, descripcion: inDesc.value.trim() });
            setTimeout(() => location.reload(), 400);
        } catch(e) { alert(e.message); }
    };
    li.appendChild(form);
}

async function usarVale() {
    const valeId = parseInt(document.getElementById('vr_usar_id')?.value);
    if (!valeId) { mostrarAlerta('vrUsarAlert', 'Selecciona un vale', 'err'); return; }
    if (!confirm('¿Aplicar este vale? Se registrará un ABONO en la incidencia origen y un ajuste en esta sesión.')) return;
    try {
        await apiPost(`${BASE}/incidencias/api/${INC_ID}/usar-vale`, { vale_id: valeId });
        mostrarAlerta('vrUsarAlert', '✓ Vale aplicado', 'ok');
        setTimeout(() => location.reload(), 900);
    } catch(e) {
        mostrarAlerta('vrUsarAlert', e.message, 'err');
    }
}

async function anularVale(valeId, btn) {
    if (!confirm('¿Anular este vale? No se podrá usar.')) return;
    try {
        await apiPost(`${BASE}/incidencias/api/${INC_ID}/anular-vale`, { vale_id: valeId });
        btn.closest('li').remove();
    } catch(e) { alert(e.message); }
}

async function revertirVale(valeId, btn) {
    if (!confirm('¿Revertir este vale?\nSe eliminará el abono de esta incidencia y el ajuste de la sesión destino.')) return;
    try {
        await apiPost(`${BASE}/incidencias/api/${INC_ID}/revertir-vale`, { vale_id: valeId });
        setTimeout(() => location.reload(), 400);
    } catch(e) { alert(e.message); }
}
</script>
</body>
</html>
