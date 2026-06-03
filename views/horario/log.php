<?php
/** @var array $logs */ /** @var array $trabajadores */
/** @var string $basePath */ /** @var string $userName */
/** @var bool $esAdmin */ /** @var int $filtroPersona */ /** @var int $filtroLocal */
$esAdmin = $esAdmin ?? false;

$LOCALES = [
    2 => 'Local 2',
    3 => 'Local 3',
    4 => 'Local 4',
];
$TURNOS = [1 => '☀️ Mañana', 2 => '🌙 Tarde'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de coberturas | Solo Boticas</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/horario.css">
    <link rel="icon" type="image/x-icon" href="<?= $basePath ?>/assets/img/logo.ico">
    <style>
        .log-filters {
            display: flex; align-items: center; gap: .75rem; flex-wrap: wrap;
            background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
            padding: 1rem 1.4rem; box-shadow: 0 1px 4px rgba(0,0,0,.05);
        }
        .log-filters label {
            font-size: .68rem; font-weight: 700; color: #94a3b8;
            text-transform: uppercase; letter-spacing: .07em; white-space: nowrap;
        }
        .log-filters select {
            padding: .38rem .7rem; border: 1.5px solid #e2e8f0; border-radius: 8px;
            font-size: .82rem; outline: none; cursor: pointer;
            background: #fff; color: #1e293b; transition: border-color .15s;
        }
        .log-filters select:focus { border-color: #0097A7; }
        .log-filters button {
            padding: .38rem 1rem; border-radius: 8px; font-size: .78rem; font-weight: 700;
            background: #0097A7; color: #fff; border: none; cursor: pointer;
        }
        .log-filters button:hover { background: #007b8a; }
        .log-filters a.log-clear {
            font-size: .75rem; color: #94a3b8; text-decoration: none; padding: .3rem .5rem;
        }
        .log-filters a.log-clear:hover { color: #64748b; }

        .log-table-wrap { overflow-x: auto; }
        .log-table {
            width: 100%; border-collapse: collapse;
            font-size: .78rem; background: #fff;
            border: 1px solid #e2e8f0; border-radius: 12px;
            overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.05);
        }
        .log-table th {
            text-align: left; padding: 9px 12px;
            font-size: .67rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: .06em; color: #94a3b8;
            border-bottom: 2px solid #e2e8f0; background: #f8fafc;
        }
        .log-table td { padding: 9px 12px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        .log-table tr:last-child td { border-bottom: none; }
        .log-table tr:hover td { background: #f8fafc; }

        .log-badge {
            display: inline-block; padding: 2px 9px; border-radius: 20px;
            font-size: .67rem; font-weight: 700;
        }
        .log-badge--COBERTURA { background: #fef3c7; color: #92400e; }
        .log-badge--CAMBIO    { background: #e0f7fa; color: #0097A7; }
        .log-estado--REVERTIDA { color: #dc2626; font-size: .68rem; font-weight: 700; }
        .log-estado--ACTIVA    { color: #059669; font-size: .68rem; font-weight: 700; }

        .log-local {
            display: inline-block; padding: 2px 8px; border-radius: 20px;
            font-size: .68rem; font-weight: 700;
        }
        .log-nombre { font-weight: 600; color: #1e293b; }
        .log-muted  { color: #94a3b8; }

        .log-empty {
            text-align: center; padding: 3rem 2rem; color: #94a3b8; font-size: .85rem;
        }
    </style>
</head>
<body>

<header class="hor-header">
    <div class="hor-header__brand">
        <div class="hor-header__logo">SB</div>
        <div>
            <p class="hor-header__company">Grupo KGyR S.A.C</p>
            <p class="hor-header__app">Historial de coberturas</p>
        </div>
    </div>
    <div class="hor-header__right">
        <span class="hor-header__user"><?= htmlspecialchars($userName) ?></span>
        <a href="<?= $basePath ?>/horario" class="hor-btn-back">←<span class="hor-btn-txt"> Volver</span></a>
    </div>
</header>

<main class="hor-main">

    <!-- ── Filtros ────────────────────────────────────────── -->
    <form method="GET" class="log-filters">
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

        <?php if ($filtroPersona || $filtroLocal): ?>
        <a href="<?= $basePath ?>/horario/log" class="log-clear">✕ Limpiar</a>
        <?php endif; ?>

        <span style="margin-left:auto;font-size:.72rem;color:#94a3b8;">
            <?= count($logs) ?> registro<?= count($logs) !== 1 ? 's' : '' ?>
        </span>
    </form>

    <!-- ── Tabla de log ───────────────────────────────────── -->
    <?php if (empty($logs)): ?>
    <div class="log-empty">
        <div style="font-size:2.5rem;margin-bottom:.75rem;">📋</div>
        <p>Sin registros con los filtros aplicados.</p>
    </div>
    <?php else: ?>

    <?php
    $colores = [2 => '#0097A7', 3 => '#5b21b6', 4 => '#d97706'];
    ?>
    <div class="log-table-wrap">
    <table class="log-table">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Local · Turno</th>
                <th>Posición</th>
                <th>Tipo</th>
                <th>Modificado por</th>
                <th>Nuevo ocupante</th>
                <th>Ocupante anterior</th>
                <th>Estado</th>
                <th>Registrado</th>
                <?php if ($esAdmin): ?><th></th><?php endif; ?>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($logs as $r):
            $color = $colores[$r['local_id']] ?? '#64748b';

            // Determinar si el solicitante TOMÓ el slot o lo VACIÓ
            // COBERTURA o CAMBIO-sin-original → tomó el slot
            // CAMBIO-con-original → sacó a alguien (slot queda libre)
            $vaciado = $r['tipo'] === 'CAMBIO' && !empty($r['original_nombre']);
        ?>
        <tr>
            <td style="font-weight:600;white-space:nowrap;">
                <?= date('d/m/Y', strtotime($r['fecha_dia'])) ?>
            </td>
            <td>
                <span class="log-local" style="background:<?= $color ?>22;color:<?= $color ?>;">
                    <?= htmlspecialchars($r['local_desc']) ?>
                </span>
                <span class="log-muted" style="margin-left:.3rem;">
                    <?= $TURNOS[$r['turno_id']] ?? $r['turno_id'] ?>
                </span>
            </td>
            <td class="log-muted">
                <?= htmlspecialchars($r['rol_puesto']) ?>
                <?= $r['slot_num'] > 1 ? ' #'.$r['slot_num'] : '' ?>
            </td>
            <td>
                <span class="log-badge log-badge--<?= $r['tipo'] ?>">
                    <?= $r['tipo'] === 'COBERTURA' ? 'Cobertura' : ($vaciado ? 'Liberado' : 'Tomó turno') ?>
                </span>
            </td>
            <!-- Quién ejecutó la acción (siempre) -->
            <td class="log-nombre"><?= htmlspecialchars($r['solicitante_nombre']) ?></td>
            <!-- Quién quedó en el slot -->
            <td>
                <?php if ($vaciado): ?>
                    <span class="log-muted">— libre —</span>
                <?php else: ?>
                    <span style="color:#059669;font-weight:600;"><?= htmlspecialchars($r['solicitante_nombre']) ?></span>
                <?php endif; ?>
            </td>
            <!-- Quién había antes -->
            <td>
                <?php if ($r['original_nombre']): ?>
                    <span style="color:#475569;"><?= htmlspecialchars($r['original_nombre']) ?></span>
                <?php else: ?>
                    <span class="log-muted">— libre —</span>
                <?php endif; ?>
            </td>
            <td>
                <span class="log-estado--<?= $r['sol_estado'] ?>">
                    <?= $r['sol_estado'] === 'ACTIVA' ? '✓ Activa' : '↩ Revertida' ?>
                </span>
                <?php if ($r['sol_estado'] === 'REVERTIDA' && $r['revertida_por_nombre']): ?>
                <div style="font-size:.65rem;color:#94a3b8;margin-top:1px;">
                    por <?= htmlspecialchars($r['revertida_por_nombre']) ?>
                </div>
                <?php endif; ?>
            </td>
            <td class="log-muted" style="white-space:nowrap;font-size:.72rem;">
                <?= date('d/m H:i', strtotime($r['fecha_solicitud'])) ?>
            </td>
            <?php if ($esAdmin): ?>
            <td>
                <?php if ($r['sol_estado'] === 'ACTIVA'): ?>
                <button class="log-btn-anular"
                        onclick="logAbrirAnular(<?= $r['id_solicitud'] ?>, '<?= addslashes($r['solicitante_nombre']) ?>', '<?= addslashes($r['tipo']) ?>')">
                    ↩ Anular
                </button>
                <?php endif; ?>
            </td>
            <?php endif; ?>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>

    <?php endif; ?>

</main>

<?php if ($esAdmin): ?>
<!-- ── Modal: anular (admin) ────────────────────────────── -->
<div id="logAnularOverlay" class="log-modal-overlay" hidden>
    <div class="log-modal">
        <h3>↩ Anular registro</h3>
        <p id="logAnularDesc"></p>
        <input type="password" id="logAnularPwd" placeholder="Tu contraseña de administrador"
               onkeydown="if(event.key==='Enter') logConfirmarAnular()">
        <div id="logAnularErr" class="log-modal__err"></div>
        <div class="log-modal__footer">
            <button onclick="logCerrarAnular()" style="background:#f1f5f9;color:#64748b;">Cancelar</button>
            <button onclick="logConfirmarAnular()" style="background:#dc2626;color:#fff;">Anular</button>
        </div>
    </div>
</div>

<style>
.log-btn-anular { padding:3px 10px;border-radius:6px;font-size:.72rem;font-weight:700;background:#dc2626;color:#fff;border:none;cursor:pointer;white-space:nowrap;transition:background .12s; }
.log-btn-anular:hover { background:#b91c1c; }
.log-modal-overlay { position:fixed;inset:0;background:rgba(15,23,42,.5);z-index:9000;display:flex;align-items:center;justify-content:center; }
.log-modal-overlay[hidden] { display:none!important; }
.log-modal { background:#fff;border-radius:14px;padding:1.5rem;width:360px;max-width:92vw;box-shadow:0 20px 60px rgba(0,0,0,.22);display:flex;flex-direction:column;gap:.85rem; }
.log-modal h3 { font-size:.95rem;font-weight:700;color:#1e293b;margin:0; }
.log-modal p  { font-size:.82rem;color:#64748b;margin:0;line-height:1.5; }
.log-modal input { width:100%;padding:.5rem .75rem;border:1.5px solid #e2e8f0;border-radius:8px;font-size:.85rem;box-sizing:border-box;outline:none; }
.log-modal input:focus { border-color:#dc2626; }
.log-modal__err { font-size:.75rem;color:#dc2626;display:none; }
.log-modal__footer { display:flex;gap:.5rem;justify-content:flex-end; }
.log-modal__footer button { padding:.45rem 1rem;border-radius:7px;font-size:.8rem;font-weight:700;border:none;cursor:pointer; }
</style>

<script>
const BASE_LOG = '<?= $basePath ?>';
let _logAnularId = null;

function logAbrirAnular(solicitudId, nombre, tipo) {
    _logAnularId = solicitudId;
    const tipoLabel = { COBERTURA: 'cobertura', CAMBIO: 'cambio de turno', INTERCAMBIO: 'intercambio de puesto' }[tipo] || tipo;
    document.getElementById('logAnularDesc').textContent =
        `Se anulará el ${tipoLabel} realizado por "${nombre}". Los slots quedarán restaurados a su estado original.`;
    document.getElementById('logAnularPwd').value = '';
    document.getElementById('logAnularErr').style.display = 'none';
    document.getElementById('logAnularOverlay').removeAttribute('hidden');
    setTimeout(() => document.getElementById('logAnularPwd').focus(), 50);
}

function logCerrarAnular() {
    document.getElementById('logAnularOverlay').setAttribute('hidden', '');
    _logAnularId = null;
}

async function logConfirmarAnular() {
    const pwd   = document.getElementById('logAnularPwd').value.trim();
    const errEl = document.getElementById('logAnularErr');
    if (!pwd) { errEl.textContent = 'Ingresa tu contraseña.'; errEl.style.display = 'block'; return; }

    try {
        const r   = await fetch(`${BASE_LOG}/horario/api/solicitud/${_logAnularId}/anular`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ password: pwd }),
        });
        const res = await r.json();
        if (res.success) { logCerrarAnular(); location.reload(); }
        else { errEl.textContent = res.message || 'Error.'; errEl.style.display = 'block'; }
    } catch { errEl.textContent = 'Error de conexión.'; errEl.style.display = 'block'; }
}
</script>
<?php endif; ?>

</body>
</html>
