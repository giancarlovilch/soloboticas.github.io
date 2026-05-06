<?php
/** @var array $pendientes */ /** @var array $abiertas */ /** @var array $recientes */
/** @var array $locales */ /** @var int $filtroLocal */ /** @var string $filtroMes */
$basePath    = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
$userName    = $userName    ?? $_SESSION['user_name'] ?? 'Usuario';
$userRol     = $userRol     ?? $_SESSION['user_rol']  ?? 'STAFF';
$pendientes  = $pendientes  ?? [];
$abiertas    = $abiertas    ?? [];
$recientes   = $recientes   ?? [];
$locales     = $locales     ?? [];
$filtroLocal = $filtroLocal ?? 0;
$filtroMes   = $filtroMes   ?? date('Y-m');

$estadoLabel = [
    'ABIERTA'         => ['label' => 'Turno abierto',     'cls' => 'estado-abierta'],
    'PENDIENTE_VENTA' => ['label' => 'Pdte. de ventas',   'cls' => 'estado-pendiente'],
    'CERRADA'         => ['label' => 'Arqueo cerrado',     'cls' => 'estado-cerrada'],
    'APROBADA'        => ['label' => 'Aprobado',           'cls' => 'estado-aprobada'],
    'OBSERVADA'       => ['label' => 'Observado',          'cls' => 'estado-observada'],
    'RECHAZADA'       => ['label' => 'Rechazado',          'cls' => 'estado-rechazada'],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Módulo de Caja | Solo Boticas</title>
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
            <p class="caja-header__app">Módulo de <strong>Caja</strong></p>
        </div>
    </div>
    <div class="caja-header__right">
        <span class="caja-header__user"><?= htmlspecialchars($userName) ?></span>
        <?php if (($userRol ?? '') === 'ADMIN'): ?>
        <a href="<?= $basePath ?>/caja/pagos-digitales" class="caja-btn-back" style="border-color:#fbbf24;color:#fbbf24;">
            Cobros Electrónicos
        </a>
        <?php endif; ?>
        <a href="<?= $basePath ?>/admin/dashboard" class="caja-btn-back">← Dashboard</a>
        <a href="<?= $basePath ?>/logout" class="caja-btn-back" style="border-color:#fca5a5;color:#ef4444;">Salir</a>
    </div>
</header>

<main class="caja-main">

    <!-- ── Pendientes de ventas ──────────────────────────── -->
    <?php if (!empty($pendientes)): ?>
    <section class="caja-section">
        <div class="caja-section__header">
            <div>
                <p class="caja-kicker">Acción requerida</p>
                <h2>Pendientes de ingreso de ventas <span class="caja-badge-count"><?= count($pendientes) ?></span></h2>
            </div>
        </div>
        <div class="caja-cards">
            <?php foreach ($pendientes as $s): ?>
            <div class="caja-card caja-card--pendiente">
                <div class="caja-card__top">
                    <div>
                        <p class="caja-card__caja"><?= htmlspecialchars($s['caja_desc']) ?> — <?= htmlspecialchars($s['local_desc']) ?></p>
                        <p class="caja-card__meta"><?= htmlspecialchars($s['turno_desc']) ?> · <?= date('d/m/Y', strtotime($s['fecha_operacion'])) ?></p>
                        <p class="caja-card__cajera">Responsable: <?= htmlspecialchars($s['cajera_nombre']) ?></p>
                    </div>
                    <span class="caja-estado estado-pendiente">Pdte. de ventas</span>
                </div>
                <div class="caja-card__actions">
                    <a href="<?= $basePath ?>/caja/<?= $s['id_sesion'] ?>/ventas" class="caja-btn caja-btn--primary">
                        Registrar ventas del turno →
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- ── Turnos activos ─────────────────────────────────── -->
    <?php if (!empty($abiertas)): ?>
    <section class="caja-section">
        <div class="caja-section__header">
            <div>
                <p class="caja-kicker">En curso</p>
                <h2>Turnos activos</h2>
            </div>
        </div>
        <div class="caja-cards">
            <?php foreach ($abiertas as $s): ?>
            <div class="caja-card caja-card--action">
                <div class="caja-card__top">
                    <div>
                        <p class="caja-card__caja"><?= htmlspecialchars($s['caja_desc']) ?> — <?= htmlspecialchars($s['local_desc']) ?></p>
                        <p class="caja-card__meta"><?= htmlspecialchars($s['turno_desc']) ?> · <?= date('d/m/Y', strtotime($s['fecha_operacion'])) ?></p>
                        <p class="caja-card__cajera">Responsable: <?= htmlspecialchars($s['cajera_nombre']) ?></p>
                    </div>
                    <span class="caja-estado estado-abierta">Turno abierto</span>
                </div>
                <div class="caja-card__actions">
                    <a href="<?= $basePath ?>/caja/sesion/<?= $s['id_sesion'] ?>" class="caja-btn caja-btn--secondary">
                        Continuar arqueo
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- ── Apertura de turno ──────────────────────────────── -->
    <section class="caja-section">
        <div class="caja-section__header">
            <div>
                <p class="caja-kicker">Nuevo registro</p>
                <h2>Apertura de turno de caja</h2>
            </div>
            <a href="<?= $basePath ?>/caja/sesion/nueva" class="caja-btn caja-btn--primary">
                + Abrir turno
            </a>
        </div>
    </section>

    <!-- ── Historial de cuadres ──────────────────────────── -->
    <section class="caja-section">
        <div class="caja-section__header" style="flex-wrap:wrap;gap:.75rem;">
            <h2>Registro de arqueos</h2>
            <!-- Filtros -->
            <form method="GET" style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;">
                <select name="local" class="caja-input" style="max-width:160px;" onchange="this.form.submit()">
                    <option value="0">Todos los locales</option>
                    <?php foreach ($locales as $l): ?>
                        <option value="<?= $l['id'] ?>" <?= $filtroLocal == $l['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($l['descripcion']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="month" name="mes" class="caja-input" style="max-width:140px;"
                       value="<?= htmlspecialchars($filtroMes) ?>" onchange="this.form.submit()">
                <a href="<?= $basePath ?>/caja" class="caja-btn caja-btn--outline" style="font-size:0.78rem;padding:5px 10px;">Limpiar</a>
            </form>
        </div>
        <div class="caja-table-wrap">
            <table class="caja-table">
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th>Fecha · Hora</th>
                        <th>Caja / Local</th>
                        <th>Turno</th>
                        <th>Cajera</th>
                        <th>Vendedor/a</th>
                        <th>Anterior</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Acción</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($recientes as $s):
                    $e    = $estadoLabel[$s['estado']] ?? ['label' => $s['estado'], 'cls' => ''];
                    $hora = $s['fecha_apertura'] ? date('H:i', strtotime($s['fecha_apertura'])) : '—';
                ?>
                    <tr>
                        <td><code style="font-size:0.75rem;color:#475569;">#<?= $s['id_sesion'] ?></code></td>
                        <td>
                            <?= date('d/m/Y', strtotime($s['fecha_operacion'])) ?>
                            <span style="color:#94a3b8;font-size:0.72rem;display:block;"><?= $hora ?></span>
                        </td>
                        <td>
                            <?= htmlspecialchars($s['caja_desc']) ?>
                            <span style="color:#94a3b8;font-size:0.72rem;display:block;"><?= htmlspecialchars($s['local_desc']) ?></span>
                        </td>
                        <td><?= htmlspecialchars($s['turno_desc']) ?></td>
                        <td><?= htmlspecialchars($s['cajera_nombre']) ?></td>
                        <td><?= $s['vendedor_nombre'] ? htmlspecialchars($s['vendedor_nombre']) : '<span style="color:#cbd5e1">—</span>' ?></td>
                        <td>
                            <?php if ($s['sesion_anterior_id']): ?>
                                <a href="<?= $basePath ?>/caja/reporte/<?= $s['sesion_anterior_id'] ?>"
                                   style="font-size:0.72rem;color:#0097A7;">#<?= $s['sesion_anterior_id'] ?></a>
                            <?php else: ?>
                                <span style="color:#cbd5e1;font-size:0.72rem;">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center"><span class="caja-estado <?= $e['cls'] ?>"><?= $e['label'] ?></span></td>
                        <td class="text-center" style="white-space:nowrap;">
                            <?php if ($s['estado'] === 'ABIERTA'): ?>
                                <a href="<?= $basePath ?>/caja/sesion/<?= $s['id_sesion'] ?>" class="caja-link">Continuar</a>
                            <?php elseif ($s['estado'] === 'PENDIENTE_VENTA'): ?>
                                <a href="<?= $basePath ?>/caja/<?= $s['id_sesion'] ?>/ventas" class="caja-link">Registrar ventas</a>
                            <?php elseif (in_array($s['estado'], ['CERRADA','APROBADA','OBSERVADA','RECHAZADA'])): ?>
                                <a href="<?= $basePath ?>/caja/reporte/<?= $s['id_sesion'] ?>" class="caja-link">Ver arqueo</a>
                            <?php else: ?>
                                <span style="color:#94a3b8">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($recientes)): ?>
                    <tr><td colspan="9" class="caja-table__empty">No hay cuadres en este período.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <p style="font-size:0.72rem;color:#94a3b8;margin-top:.5rem;text-align:right;">
            <?= count($recientes) ?> registro<?= count($recientes) !== 1 ? 's' : '' ?> en <?= date('F Y', strtotime($filtroMes . '-01')) ?>
        </p>
    </section>

</main>
</body>
</html>
