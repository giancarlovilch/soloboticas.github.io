<?php
/** @var array $registros */ /** @var array $trabajadores */ /** @var array $estadisticas */
/** @var string $basePath */ /** @var string $userName */
/** @var int $filtroPersona */ /** @var int $filtroLocal */
/** @var string $filtroMes */ /** @var string $fechaDesde */ /** @var string $fechaHasta */
$estadisticas = $estadisticas ?? [];

$filtroMes = $filtroMes ?? date('Y-m');

// Generar lista de meses (últimos 12)
$mesesOpciones = [];
for ($i = 0; $i < 12; $i++) {
    $val = date('Y-m', strtotime("-{$i} months"));
    $mesesOpciones[$val] = strftime('%B %Y', strtotime($val . '-01'));
}

$LOCALES = [2 => 'Local 2', 3 => 'Local 3', 4 => 'Local 4'];
$TURNOS  = [1 => '☀️ Mañana', 2 => '🌙 Tarde'];
$colores = [2 => '#0097A7', 3 => '#5b21b6', 4 => '#d97706'];

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Métricas | Solo Boticas</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/horario.css">
    <link rel="icon" type="image/x-icon" href="<?= $basePath ?>/assets/img/logo.ico">
    <style>
        .asi-filters {
            display: flex; align-items: center; gap: .75rem; flex-wrap: wrap;
            background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
            padding: 1rem 1.4rem; box-shadow: 0 1px 4px rgba(0,0,0,.05);
        }
        .asi-filters label { font-size:.68rem; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.07em; white-space:nowrap; }
        .asi-filters select, .asi-filters input[type=date] {
            padding:.38rem .7rem; border:1.5px solid #e2e8f0; border-radius:8px;
            font-size:.82rem; outline:none; cursor:pointer; background:#fff; color:#1e293b; transition:border-color .15s;
        }
        .asi-filters select:focus, .asi-filters input[type=date]:focus { border-color:#0097A7; }
        .asi-filters button { padding:.38rem 1rem; border-radius:8px; font-size:.78rem; font-weight:700; background:#0097A7; color:#fff; border:none; cursor:pointer; }
        .asi-filters button:hover { background:#007b8a; }
        .asi-filters a.asi-clear { font-size:.75rem; color:#94a3b8; text-decoration:none; padding:.3rem .5rem; }
        .asi-filters a.asi-clear:hover { color:#64748b; }

        /* Tabla principal */
        .asi-table-wrap { overflow-x:auto; }
        .asi-table {
            width:100%; border-collapse:collapse; font-size:.78rem;
            background:#fff; border:1px solid #e2e8f0; border-radius:12px;
            overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.05);
        }
        .asi-table th {
            text-align:left; padding:9px 14px;
            font-size:.65rem; font-weight:700; text-transform:uppercase;
            letter-spacing:.06em; color:#94a3b8;
            border-bottom:2px solid #e2e8f0; background:#f8fafc;
        }
        .asi-table td { padding:8px 14px; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
        .asi-table tr:last-child td { border-bottom:none; }
        .asi-table tr:hover td { background:#f8fafc; }

        .asi-local  { display:inline-block; padding:1px 8px; border-radius:20px; font-size:.68rem; font-weight:700; }
        .asi-turno  { font-size:.72rem; color:#64748b; }
        .asi-rol    { font-size:.72rem; color:#475569; }
        .asi-nombre { font-weight:600; color:#1e293b; }
        .asi-fecha  { font-weight:600; white-space:nowrap; color:#1e293b; }
        .asi-dow    { font-size:.68rem; color:#94a3b8; margin-left:.3rem; }

        .asi-empty { text-align:center; padding:3rem 2rem; color:#94a3b8; font-size:.85rem; }

        .asi-resumen {
            display:flex; align-items:center; gap:1rem; flex-wrap:wrap;
            padding:.65rem 1.25rem; background:#f0f9ff;
            border:1px solid #bae6fd; border-radius:10px;
            font-size:.78rem; color:#0369a1;
        }
        .asi-resumen strong { font-weight:700; }
    </style>
</head>
<body>

<header class="hor-header">
    <div class="hor-header__brand">
        <div class="hor-header__logo">SB</div>
        <div>
            <p class="hor-header__company">Grupo KGyR S.A.C</p>
            <p class="hor-header__app">Métricas del equipo</p>
        </div>
    </div>
    <div class="hor-header__right">
        <span class="hor-header__user"><?= htmlspecialchars($userName) ?></span>
        <a href="<?= $basePath ?>/horario/log" class="hor-btn hor-btn--outline" style="font-size:0.78rem;">📋<span class="hor-btn-txt"> Logs</span></a>
        <a href="<?= $basePath ?>/horario" class="hor-btn-back">←<span class="hor-btn-txt"> Volver</span></a>
    </div>
</header>

<main class="hor-main">

    <!-- ── Filtros ─────────────────────────────────────────── -->
    <form method="GET" class="asi-filters">
        <label>Mes</label>
        <select name="mes" onchange="this.form.submit()" style="padding:.38rem .7rem;border:1.5px solid #e2e8f0;border-radius:8px;font-size:.82rem;outline:none;cursor:pointer;background:#fff;color:#1e293b;">
            <?php foreach ($mesesOpciones as $val => $label): ?>
            <option value="<?= $val ?>" <?= $filtroMes === $val ? 'selected' : '' ?>>
                <?= htmlspecialchars($label) ?>
            </option>
            <?php endforeach; ?>
        </select>

        <label>Trabajador</label>
        <select name="persona">
            <option value="">— Todos —</option>
            <?php foreach ($trabajadores as $t): ?>
            <option value="<?= $t['id'] ?>" <?= $filtroPersona === (int)$t['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($t['nombre']) ?>
            </option>
            <?php endforeach; ?>
        </select>

        <label>Local</label>
        <select name="local">
            <option value="">— Todos —</option>
            <?php foreach ($LOCALES as $lid => $lnombre): ?>
            <option value="<?= $lid ?>" <?= $filtroLocal === $lid ? 'selected' : '' ?>>
                <?= $lnombre ?>
            </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Filtrar</button>

        <?php if ($filtroLocal): ?>
        <a href="<?= $basePath ?>/horario/asistencia?mes=<?= $filtroMes ?>&persona=<?= $filtroPersona ?>" class="asi-clear">✕ Quitar filtro local</a>
        <?php endif; ?>
    </form>

    <!-- ── Bloque de estadísticas ───────────────────────────── -->
    <?php
    $p   = $estadisticas['personal']  ?? [];
    $tV  = $estadisticas['topVend']   ?? [];
    $tC  = $estadisticas['topCaj']    ?? [];
    $pu  = $estadisticas['puestoPropio'] ?? null;
    $rol = $estadisticas['rolPropio']    ?? null;

    $tieneVend = ($p['turnos_vend'] ?? 0) > 0;
    $tieneCaj  = ($p['turnos_caj']  ?? 0) > 0;

    if ($tieneVend || $tieneCaj || !empty($tV) || !empty($tC)):
    ?>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.75rem;">

        <?php
        // Helper para filas de stat
        $stat = fn($label, $val, $color='#1e293b') =>
            "<div style='display:flex;justify-content:space-between;align-items:baseline;
                         margin-bottom:.28rem;padding-bottom:.28rem;border-bottom:1px solid #f1f5f9;'>
                <span style='font-size:.73rem;color:#64748b;'>{$label}</span>
                <span style='font-size:.8rem;font-weight:700;color:{$color};white-space:nowrap;'>{$val}</span>
             </div>";
        $nombre1 = fn($nombre) => implode(' ', array_slice(explode(' ', $nombre), 0, 2));
        $medalColor = fn($i) => $i===0?'#d97706':($i===1?'#94a3b8':($i===2?'#b45309':'#cbd5e1'));
        ?>

        <!-- Mi resumen -->
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:.9rem 1rem;">
            <div style="font-size:.65rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:.7rem;display:flex;justify-content:space-between;align-items:center;">
                <span>Mi resumen</span>
                <?php if ($pu): ?>
                <span style="font-size:.7rem;background:#fef3c7;color:#92400e;padding:1px 8px;border-radius:10px;font-weight:700;">
                    #<?= $pu ?> <?= $rol === 'VENDEDORA' ? 'vendedoras' : 'cajeras' ?>
                </span>
                <?php endif; ?>
            </div>

            <?php if ($tieneVend): ?>
            <?= $stat('Promedio de ventas', 'S/ ' . number_format($p['prom_ventas']??0,2), '#059669') ?>
            <?= $stat('Total del mes',      'S/ ' . number_format($p['total_ventas']??0,2)) ?>
            <?= $stat('Mejor turno',        'S/ ' . number_format($p['max_ventas']??0,2), '#d97706') ?>
            <?= $stat('Turno más bajo',     'S/ ' . number_format($p['min_ventas']??0,2), '#dc2626') ?>
            <?= $stat('Turnos registrados', $p['turnos_vend'] . ' turno' . ($p['turnos_vend']!=1?'s':''), '#0284c7') ?>
            <?= $stat('Días trabajados',    $p['dias_trabajados'] . ' día' . ($p['dias_trabajados']!=1?'s':'')) ?>
            <div style="display:flex;justify-content:space-between;align-items:baseline;">
                <span style="font-size:.73rem;color:#64748b;">Locales distintos</span>
                <span style="font-size:.8rem;font-weight:700;color:#1e293b;"><?= $p['locales_distintos'] ?></span>
            </div>

            <?php elseif ($tieneCaj): ?>
            <?= $stat('Promedio ops BCP',   number_format($p['prom_ops']??0,1) . ' ops', '#0284c7') ?>
            <?= $stat('Total ops del mes',  $p['total_ops']??0 . ' ops') ?>
            <?= $stat('Mejor turno',        ($p['max_ops']??0) . ' ops', '#d97706') ?>
            <?= $stat('Turno más bajo',     ($p['min_ops']??0) . ' ops', '#dc2626') ?>
            <?= $stat('Turnos registrados', $p['turnos_caj'] . ' turno' . ($p['turnos_caj']!=1?'s':''), '#059669') ?>
            <?= $stat('Días trabajados',    $p['dias_trabajados'] . ' día' . ($p['dias_trabajados']!=1?'s':'')) ?>
            <div style="display:flex;justify-content:space-between;align-items:baseline;">
                <span style="font-size:.73rem;color:#64748b;">Locales distintos</span>
                <span style="font-size:.8rem;font-weight:700;color:#1e293b;"><?= $p['locales_distintos'] ?></span>
            </div>

            <?php else: ?>
            <p style="font-size:.75rem;color:#94a3b8;margin:0;">Sin datos registrados para este mes.</p>
            <?php endif; ?>
        </div>

        <!-- Top 10 Vendedoras -->
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:.9rem 1rem;">
            <div style="font-size:.65rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:.7rem;">
                🏆 Top 10 Vendedoras · Prom. ventas
            </div>
            <?php if (empty($tV)): ?>
            <p style="font-size:.75rem;color:#94a3b8;margin:0;">Sin datos.</p>
            <?php else: foreach ($tV as $i => $v): ?>
            <div style="display:flex;align-items:center;gap:.4rem;margin-bottom:.28rem;<?= $i<count($tV)-1?'border-bottom:1px solid #f8fafc;padding-bottom:.28rem;':'' ?>">
                <span style="font-size:.68rem;font-weight:800;color:<?= $medalColor($i) ?>;width:16px;text-align:center;flex-shrink:0;"><?= $i+1 ?></span>
                <span style="font-size:.73rem;color:#1e293b;flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="<?= htmlspecialchars($v['nombres']) ?>"><?= htmlspecialchars($nombre1($v['nombres'])) ?></span>
                <span style="font-size:.73rem;font-weight:700;color:#059669;white-space:nowrap;">S/ <?= number_format($v['prom_ventas'],0) ?></span>
                <span style="font-size:.65rem;color:#94a3b8;white-space:nowrap;">(<?= $v['turnos_con_datos'] ?>t)</span>
            </div>
            <?php endforeach; endif; ?>
        </div>

        <!-- Top 10 Cajeras -->
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:.9rem 1rem;">
            <div style="font-size:.65rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:.7rem;">
                🏆 Top 10 Cajeras · Prom. ops BCP
            </div>
            <?php if (empty($tC)): ?>
            <p style="font-size:.75rem;color:#94a3b8;margin:0;">Sin datos.</p>
            <?php else: foreach ($tC as $i => $c): ?>
            <div style="display:flex;align-items:center;gap:.4rem;margin-bottom:.28rem;<?= $i<count($tC)-1?'border-bottom:1px solid #f8fafc;padding-bottom:.28rem;':'' ?>">
                <span style="font-size:.68rem;font-weight:800;color:<?= $medalColor($i) ?>;width:16px;text-align:center;flex-shrink:0;"><?= $i+1 ?></span>
                <span style="font-size:.73rem;color:#1e293b;flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="<?= htmlspecialchars($c['nombres']) ?>"><?= htmlspecialchars($nombre1($c['nombres'])) ?></span>
                <span style="font-size:.73rem;font-weight:700;color:#0284c7;white-space:nowrap;"><?= number_format($c['prom_ops'],1) ?></span>
                <span style="font-size:.65rem;color:#94a3b8;white-space:nowrap;">(<?= $c['turnos_con_datos'] ?>t)</span>
            </div>
            <?php endforeach; endif; ?>
        </div>

    </div>
    <?php endif; ?>

    <!-- Resumen -->
    <?php if (!empty($registros)): ?>
    <div class="asi-resumen">
        <strong><?= count($registros) ?></strong> turno<?= count($registros) !== 1 ? 's' : '' ?> registrado<?= count($registros) !== 1 ? 's' : '' ?>
        · <strong><?= htmlspecialchars($mesesOpciones[$filtroMes] ?? $filtroMes) ?></strong>
        <?php if ($filtroPersona): ?>
        · Filtrado por trabajador
        <?php endif; ?>
        <?php if ($filtroLocal): ?>
        · <?= $LOCALES[$filtroLocal] ?? '' ?>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- ── Tabla de registros ───────────────────────────────── -->
    <?php if (empty($registros)): ?>
    <div class="asi-empty">
        <div style="font-size:2.5rem;margin-bottom:.75rem;">📊</div>
        <p>Sin registros para el período y filtros seleccionados.</p>
    </div>
    <?php else: ?>

    <?php
    $dias = ['0'=>'Dom','1'=>'Lun','2'=>'Mar','3'=>'Mié','4'=>'Jue','5'=>'Vie','6'=>'Sáb'];
    ?>
    <div class="asi-table-wrap">
    <table class="asi-table">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Local</th>
                <th>Turno</th>
                <th>Posición</th>
                <th>Trabajador</th>
                <th>Operaciones BCP</th>
                <th>Ventas S/</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($registros as $r):
            $color = $colores[$r['local_id']] ?? '#64748b';
            $dow   = $dias[date('w', strtotime($r['fecha_dia']))] ?? '';
            $ops   = $r['operaciones_bcp'] ?? null;
            $ventas = $r['ventas_monto']   ?? null;
        ?>
        <tr>
            <td class="asi-fecha">
                <?= date('d/m/Y', strtotime($r['fecha_dia'])) ?>
                <span class="asi-dow"><?= $dow ?></span>
            </td>
            <td>
                <span class="asi-local" style="background:<?= $color ?>22;color:<?= $color ?>;">
                    <?= htmlspecialchars($r['local_desc']) ?>
                </span>
            </td>
            <td class="asi-turno"><?= $TURNOS[$r['turno_id']] ?? $r['turno_id'] ?></td>
            <td class="asi-rol">
                <?= htmlspecialchars($r['rol_desc'] ?: $r['rol_puesto']) ?>
                <?= $r['slot_num'] > 1 ? ' #'.$r['slot_num'] : '' ?>
            </td>
            <td class="asi-nombre"><?= htmlspecialchars($r['trabajador_nombre']) ?></td>
            <td style="text-align:center;">
                <?php if ($ops !== null): ?>
                    <span style="font-weight:600;color:#0369a1;"><?= (int)$ops ?></span>
                <?php else: ?>
                    <span style="color:#cbd5e1;">—</span>
                <?php endif; ?>
            </td>
            <td style="text-align:right;">
                <?php if ($ventas !== null): ?>
                    <span style="font-weight:600;color:#059669;">S/ <?= number_format((float)$ventas, 2) ?></span>
                <?php else: ?>
                    <span style="color:#cbd5e1;">—</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>

    <?php endif; ?>

</main>
</body>
</html>
