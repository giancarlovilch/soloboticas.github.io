<?php
$basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
$userRol  = $userRol ?? $_SESSION['user_rol'] ?? 'STAFF';
$userName = $userName ?? $_SESSION['user_name'] ?? 'Usuario';

$estadoInfo = [
    'ABIERTO'  => ['label' => 'Abierto',  'bg' => '#fee2e2', 'color' => '#991b1b'],
    'PARCIAL'  => ['label' => 'Parcial',  'bg' => '#fef3c7', 'color' => '#92400e'],
    'CERRADO'  => ['label' => 'Cerrado',  'bg' => '#d1fae5', 'color' => '#065f46'],
];
$tipoInfo = [
    'FALTANTE' => ['label' => 'Faltante', 'bg' => '#fee2e2', 'color' => '#991b1b'],
    'SOBRANTE' => ['label' => 'Sobrante', 'bg' => '#dbeafe', 'color' => '#1e40af'],
];
$f2 = fn($v) => 'S/ ' . number_format((float)$v, 2, '.', ',');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incidencias Contables | Solo Boticas</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/staff.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="<?= $basePath ?>/assets/img/logo.ico">
    <style>
        .ic-wrap     { max-width:960px;margin:0 auto;padding:1.25rem 1rem 3rem; }
        .ic-header   { display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem;flex-wrap:wrap; }
        .ic-title    { font-size:1.35rem;font-weight:800;color:#1e293b;flex:1; }
        .ic-badge    { display:inline-flex;align-items:center;gap:.35rem;padding:.3rem .75rem;
                       border-radius:20px;font-size:.75rem;font-weight:700;
                       background:#fee2e2;color:#991b1b; }

        /* Filtros */
        .ic-filters  { display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:1.25rem; }
        .ic-filters a { padding:.38rem .9rem;border-radius:8px;font-size:.8rem;font-weight:600;
                        border:1.5px solid #e2e8f0;background:#f8fafc;color:#475569;text-decoration:none;
                        transition:background .15s; }
        .ic-filters a.active { background:#1e293b;color:#fff;border-color:#1e293b; }

        /* Tabla */
        .ic-table    { width:100%;border-collapse:collapse;background:#fff;border-radius:12px;
                       overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.07); }
        .ic-table th { background:#f1f5f9;font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;
                       color:#64748b;padding:.65rem .9rem;text-align:left; }
        .ic-table td { padding:.7rem .9rem;font-size:.85rem;color:#334155;border-top:1px solid #f1f5f9; }
        .ic-table tr:hover td { background:#f8fafc; }
        .ic-table a  { color:#3b82f6;font-weight:600;text-decoration:none; }
        .ic-table a:hover { text-decoration:underline; }

        .badge       { display:inline-block;padding:.2rem .55rem;border-radius:6px;font-size:.71rem;font-weight:700; }

        .ic-empty    { text-align:center;padding:3rem 1rem;color:#94a3b8;font-size:.9rem; }

        @media(max-width:600px){
            .ic-table th:nth-child(4), .ic-table td:nth-child(4),
            .ic-table th:nth-child(5), .ic-table td:nth-child(5) { display:none; }
        }
    </style>
</head>
<body>

<header class="staff-header">
    <div class="staff-header__brand">
        <div class="staff-header__logo">SB</div>
        <div>
            <p class="staff-header__company">Grupo KGyR S.A.C</p>
            <p class="staff-header__app">Solo Boticas <span>Incidencias Contables</span></p>
        </div>
    </div>
    <div class="staff-header__user">
        <div>
            <p class="staff-header__name"><?= htmlspecialchars($userName) ?></p>
            <p class="staff-header__rol"><?= htmlspecialchars($userRol) ?></p>
        </div>
        <a href="<?= $basePath ?>/logout" class="staff-btn-logout">Salir</a>
    </div>
</header>

<main style="padding:1rem;">
<div class="ic-wrap">

    <!-- Header -->
    <div class="ic-header">
        <h1 class="ic-title">Incidencias Contables</h1>
        <?php if ($totalAbiertos > 0): ?>
            <span class="ic-badge"><?= $totalAbiertos ?> pendiente<?= $totalAbiertos > 1 ? 's' : '' ?></span>
        <?php endif; ?>
        <?php if ($userRol === 'ADMIN'): ?>
        <a href="<?= $basePath ?>/admin/dashboard"
           style="font-size:.8rem;color:#64748b;text-decoration:none;">← Volver al dashboard</a>
        <?php else: ?>
        <a href="<?= $basePath ?>/staff"
           style="font-size:.8rem;color:#64748b;text-decoration:none;">← Volver</a>
        <?php endif; ?>
    </div>

    <!-- Filtros -->
    <?php
    $qEstado = $_GET['estado'] ?? '';
    $qTipo   = $_GET['tipo']   ?? '';
    $baseUrl = $basePath . '/incidencias';
    ?>
    <div class="ic-filters">
        <a href="<?= $baseUrl ?>" class="<?= (!$qEstado && !$qTipo) ? 'active' : '' ?>">Todos</a>
        <a href="<?= $baseUrl ?>?estado=ABIERTO"  class="<?= $qEstado==='ABIERTO'  ? 'active' : '' ?>">Abiertos</a>
        <a href="<?= $baseUrl ?>?estado=PARCIAL"  class="<?= $qEstado==='PARCIAL'  ? 'active' : '' ?>">Parciales</a>
        <a href="<?= $baseUrl ?>?estado=CERRADO"  class="<?= $qEstado==='CERRADO'  ? 'active' : '' ?>">Cerrados</a>
        <a href="<?= $baseUrl ?>?tipo=FALTANTE"   class="<?= $qTipo==='FALTANTE'   ? 'active' : '' ?>">Faltantes</a>
        <a href="<?= $baseUrl ?>?tipo=SOBRANTE"   class="<?= $qTipo==='SOBRANTE'   ? 'active' : '' ?>">Sobrantes</a>
    </div>

    <!-- Tabla -->
    <?php if (empty($incidencias)): ?>
        <div class="ic-empty">No hay incidencias<?= $qEstado || $qTipo ? ' con ese filtro' : '' ?>.</div>
    <?php else: ?>
    <table class="ic-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Sesión</th>
                <th>Cajera</th>
                <th>Tipo</th>
                <th>Monto orig.</th>
                <th>Pendiente</th>
                <th>Estado</th>
                <th>Fecha</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($incidencias as $inc): ?>
            <?php
            $ei = $estadoInfo[$inc['estado']] ?? ['label'=>$inc['estado'],'bg'=>'#f1f5f9','color'=>'#475569'];
            $ti = $tipoInfo[$inc['tipo']]     ?? ['label'=>$inc['tipo'],  'bg'=>'#f1f5f9','color'=>'#475569'];
            ?>
            <tr>
                <td><?= $inc['id_incidencia'] ?></td>
                <td>
                    <a href="<?= $basePath ?>/caja/reporte/<?= $inc['sesion_origen_id'] ?>" target="_blank">
                        #<?= $inc['sesion_origen_id'] ?>
                    </a>
                    <?php if ($inc['caja_desc'] ?? ''): ?>
                        <br><small style="color:#94a3b8;font-size:.72rem;"><?= htmlspecialchars($inc['local_desc'] ?? '') ?> — <?= htmlspecialchars($inc['caja_desc']) ?></small>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($inc['responsable_nombre'] ?? '—') ?></td>
                <td><span class="badge" style="background:<?= $ti['bg'] ?>;color:<?= $ti['color'] ?>"><?= $ti['label'] ?></span></td>
                <td><?= $f2($inc['monto_original']) ?></td>
                <td style="font-weight:700;color:<?= (float)$inc['monto_pendiente'] > 0 ? '#dc2626' : '#16a34a' ?>">
                    <?= $f2($inc['monto_pendiente']) ?>
                </td>
                <td><span class="badge" style="background:<?= $ei['bg'] ?>;color:<?= $ei['color'] ?>"><?= $ei['label'] ?></span></td>
                <td style="white-space:nowrap;color:#64748b;font-size:.78rem;">
                    <?= date('d/m/y', strtotime($inc['fecha_apertura'])) ?>
                </td>
                <td>
                    <a href="<?= $basePath ?>/incidencias/<?= $inc['id_incidencia'] ?>">Ver →</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

</div>
</main>

</body>
</html>
