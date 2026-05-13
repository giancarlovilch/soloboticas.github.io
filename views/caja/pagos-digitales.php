<?php
/** @var array $pagos */ /** @var array $locales */ /** @var int $filtroLocal */
$basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
$userName = $userName ?? $_SESSION['user_name'] ?? 'Usuario';
$userRol  = $userRol  ?? $_SESSION['user_rol']  ?? 'STAFF';
$esAdmin  = ($userRol === 'ADMIN');

$estadoClase = [
    'PENDIENTE' => 'badge-pendiente',
    'APROBADO'  => 'badge-contratado',
    'RECHAZADO' => 'badge-rechazado',
    'OBSERVADO' => 'badge-entrevista',
];

$filtroActual = $_GET['estado'] ?? '';
$filtroLocal  = isset($_GET['local']) ? (int)$_GET['local'] : 0;
$filtroCaja   = isset($_GET['caja'])  ? (int)$_GET['caja']  : 0;
$locales      = $locales ?? [];
$cajas        = $cajas   ?? [];

// Totales
$totPendiente = array_sum(array_column(array_filter($pagos, fn($p) => $p['estado'] === 'PENDIENTE'), 'monto'));
$totAprobado  = array_sum(array_column(array_filter($pagos, fn($p) => $p['estado'] === 'APROBADO'), 'monto'));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cobros Electrónicos | Caja SB</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/dashboard.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/caja.css">
</head>
<body style="background:#f1f5f9;">

<header class="caja-header">
    <div class="caja-header__brand">
        <div class="caja-header__logo">SB</div>
        <div>
            <p class="caja-header__company">Grupo KGyR S.A.C</p>
            <p class="caja-header__app"><strong>Cobros Electrónicos</strong> — Verificación</p>
        </div>
    </div>
    <div class="caja-header__right">
        <span class="caja-header__user"><?= htmlspecialchars($userName) ?> (<?= $userRol ?>)</span>
        <a href="<?= $basePath ?>/caja" class="caja-btn-back">← Caja</a>
        <a href="<?= $basePath ?>/<?= ($userRol ?? '') === 'ADMIN' ? 'admin/dashboard' : 'staff' ?>" class="caja-btn-back">Dashboard</a>
    </div>
</header>

<main class="caja-main" style="max-width:1000px;">

    <!-- ── Resumen ────────────────────────────────────────── -->
    <div style="display:flex;gap:1rem;flex-wrap:wrap;">
        <div class="caja-card" style="flex:1;min-width:180px;text-align:center;">
            <p style="font-size:0.68rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.4rem;">Pendientes</p>
            <p style="font-size:1.6rem;font-weight:700;color:#d97706;">S/ <?= number_format($totPendiente,2) ?></p>
        </div>
        <div class="caja-card" style="flex:1;min-width:180px;text-align:center;">
            <p style="font-size:0.68rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.4rem;">Aprobados</p>
            <p style="font-size:1.6rem;font-weight:700;color:#059669;">S/ <?= number_format($totAprobado,2) ?></p>
        </div>
        <div class="caja-card" style="flex:1;min-width:180px;text-align:center;">
            <p style="font-size:0.68rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.4rem;">Total registros</p>
            <p style="font-size:1.6rem;font-weight:700;color:#1e293b;"><?= count($pagos) ?></p>
        </div>
    </div>

    <!-- ── Filtros ─────────────────────────────────────────── -->
    <section class="caja-card">
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;margin-bottom:.75rem;">
            <span style="font-size:0.8rem;color:#64748b;font-weight:600;">Estado:</span>
            <?php foreach ([''=>'Todos','PENDIENTE'=>'Pendientes','APROBADO'=>'Aprobados','RECHAZADO'=>'Rechazados'] as $val => $lbl): ?>
                <a href="?estado=<?= $val ?>&local=<?= $filtroLocal ?>&caja=<?= $filtroCaja ?>"
                   class="caja-btn <?= $filtroActual === $val ? 'caja-btn--primary' : 'caja-btn--outline' ?>"
                   style="padding:4px 12px;font-size:0.78rem;">
                    <?= $lbl ?>
                </a>
            <?php endforeach; ?>
        </div>
        <form method="GET" style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;">
            <input type="hidden" name="estado" value="<?= htmlspecialchars($filtroActual) ?>">
            <select name="local" class="caja-input" style="max-width:170px;" onchange="this.form.submit()">
                <option value="0">— Todos los locales —</option>
                <?php foreach ($locales as $l): ?>
                    <option value="<?= $l['id'] ?>" <?= $filtroLocal == $l['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($l['descripcion']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if ($filtroLocal > 0): ?>
            <select name="caja" class="caja-input" style="max-width:170px;" onchange="this.form.submit()">
                <option value="0">— Todas las cajas —</option>
                <?php foreach ($cajas as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $filtroCaja == $c['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['descripcion']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php else: ?>
            <span style="font-size:0.78rem;color:#94a3b8;">Selecciona un local para filtrar por caja</span>
            <?php endif; ?>
            <?php if ($filtroLocal || $filtroCaja): ?>
            <a href="?estado=<?= htmlspecialchars($filtroActual) ?>" class="caja-btn caja-btn--outline"
               style="padding:4px 10px;font-size:0.78rem;">✕ Limpiar</a>
            <?php endif; ?>
        </form>
    </section>

    <!-- ── Tabla ──────────────────────────────────────────── -->
    <section class="caja-card" style="padding:1rem;">
        <div id="pagoMsg" class="caja-alert" hidden></div>
        <table class="caja-table" id="tablaPagos" style="width:100%;table-layout:fixed;">
            <colgroup>
                <col style="width:90px;">  <!-- Fecha -->
                <col style="width:130px;"> <!-- Caja/Local -->
                <col>                      <!-- Personal -->
                <col style="width:120px;"> <!-- Cobro -->
                <col style="width:80px;">  <!-- Monto -->
                <col style="width:90px;">  <!-- Estado -->
                <?php if ($esAdmin): ?><col style="width:130px;"><?php endif; ?>
            </colgroup>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Caja / Local</th>
                    <th>Personal</th>
                    <th>Cobro</th>
                    <th class="text-right">Monto</th>
                    <th class="text-center">Estado</th>
                    <?php if ($esAdmin): ?><th class="text-center">Acción</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($pagos as $p):
                $cls = $estadoClase[$p['estado']] ?? 'badge-pendiente';
            ?>
                <tr id="row-<?= $p['id_movimiento'] ?>">
                    <td>
                        <span style="font-size:0.8rem;color:#1e293b;display:block;"><?= date('d/m/Y', strtotime($p['fecha_movimiento'])) ?></span>
                        <span style="font-size:0.72rem;color:#94a3b8;"><?= date('H:i', strtotime($p['fecha_movimiento'])) ?></span>
                    </td>
                    <td>
                        <span style="font-size:0.8rem;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= htmlspecialchars($p['caja_desc']) ?>">
                            <?= htmlspecialchars($p['caja_desc']) ?>
                        </span>
                        <span style="font-size:0.72rem;color:#94a3b8;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:block;">
                            <?= htmlspecialchars($p['local_desc']) ?>
                        </span>
                    </td>
                    <td>
                        <span style="font-size:0.8rem;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            <?= htmlspecialchars($p['cajera_nombre']) ?>
                        </span>
                        <?php if ($p['vendedor_nombre']): ?>
                        <span style="font-size:0.72rem;color:#94a3b8;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:block;">
                            <?= htmlspecialchars($p['vendedor_nombre']) ?>
                        </span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span style="font-size:0.8rem;font-weight:700;display:block;"><?= htmlspecialchars($p['modo_desc']) ?></span>
                        <span style="font-size:0.72rem;color:#94a3b8;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:block;">
                            <?= htmlspecialchars($p['numero_operacion'] ?? '—') ?>
                        </span>
                    </td>
                    <td class="text-right">
                        <strong style="font-size:0.88rem;font-variant-numeric:tabular-nums;">S/ <?= number_format($p['monto'],2) ?></strong>
                    </td>
                    <td class="text-center">
                        <span class="badge <?= $cls ?>" id="badge-<?= $p['id_movimiento'] ?>"><?= $p['estado'] ?></span>
                    </td>
                    <?php if ($esAdmin): ?>
                    <td class="text-center">
                        <?php if ($p['estado'] === 'PENDIENTE'): ?>
                            <button class="btn-edit" style="font-size:0.7rem;padding:3px 8px;background:#059669;display:block;width:100%;margin-bottom:3px;"
                                    onclick="confirmar(<?= $p['id_movimiento'] ?>,'APROBADO')">✓ Aprobar</button>
                            <button class="btn-danger" style="font-size:0.7rem;padding:3px 8px;display:block;width:100%;"
                                    onclick="confirmar(<?= $p['id_movimiento'] ?>,'RECHAZADO')">✗ Rechazar</button>
                        <?php elseif ($p['estado'] === 'APROBADO'): ?>
                            <button class="btn-danger" style="font-size:0.7rem;padding:3px 8px;"
                                    onclick="confirmar(<?= $p['id_movimiento'] ?>,'PENDIENTE')">Revertir</button>
                        <?php else: ?>
                            <span style="color:#94a3b8;font-size:0.75rem;">—</span>
                        <?php endif; ?>
                    </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($pagos)): ?>
                <tr><td colspan="<?= $esAdmin ? 7 : 6 ?>" class="caja-table__empty">No hay cobros electrónicos registrados.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </section>

</main>

<script>
const BASE = '<?= $basePath ?>';

async function confirmar(id, estado) {
    const msg = document.getElementById('pagoMsg');
    try {
        const r   = await fetch(`${BASE}/caja/api/pago-digital/${id}/confirmar`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ estado }),
        });
        const res = await r.json();
        if (res.success) {
            // Actualizar badge y botones inline sin recargar
            const badge = document.getElementById(`badge-${id}`);
            if (badge) {
                const mapa = { APROBADO:'badge-contratado', RECHAZADO:'badge-rechazado', PENDIENTE:'badge-pendiente' };
                badge.className = `badge ${mapa[estado] || 'badge-pendiente'}`;
                badge.textContent = estado;
            }
            location.reload(); // recarga para actualizar totales y botones
        } else {
            msg.textContent = res.message; msg.className = 'caja-alert caja-alert--error'; msg.hidden = false;
        }
    } catch {
        msg.textContent = 'Error de conexión.'; msg.className = 'caja-alert caja-alert--error'; msg.hidden = false;
    }
}
</script>
</body>
</html>
