<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitudes de horario | Solo Boticas</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/horario.css">
    <link rel="icon" type="image/x-icon" href="<?= $basePath ?>/assets/img/logo.ico">
    <style>
        .sol-grid { display: flex; flex-direction: column; gap: .6rem; }
        .sol-row {
            display: grid;
            grid-template-columns: 110px 90px 1fr 1fr auto;
            gap: .6rem; align-items: center;
            background: #fff; border: 1px solid #e2e8f0;
            border-radius: 10px; padding: .65rem 1rem;
        }
        .sol-row--pasado { opacity: .5; }
        .sol-row__local { font-size: 0.82rem; font-weight: 700; color: #1e293b; }
        .sol-row__turno { font-size: 0.75rem; color: #64748b; }
        .sol-row__rol   { font-size: 0.78rem; color: #475569; }
        .sol-row__name  { font-size: 0.85rem; font-weight: 600; }
        .sol-row__empty { font-size: 0.78rem; color: #94a3b8; font-style: italic; }
        .sol-btn {
            padding: 5px 14px; border-radius: 7px; font-size: 0.78rem; font-weight: 700;
            border: none; cursor: pointer; white-space: nowrap;
            background: #0097A7; color: #fff; transition: background .15s;
        }
        .sol-btn:hover { background: #007b8a; }
        .sol-btn--lock { background: #f1f5f9; color: #94a3b8; cursor: default; }

        /* Modal */
        .sol-modal-overlay {
            position: fixed; inset: 0; background: rgba(15,23,42,.5);
            z-index: 500; display: flex; align-items: center; justify-content: center;
        }
        .sol-modal-overlay[hidden] { display: none !important; }
        .sol-modal {
            background: #fff; border-radius: 14px; padding: 1.5rem;
            width: 340px; max-width: 90vw;
            box-shadow: 0 20px 60px rgba(0,0,0,.2);
        }
        .sol-modal h3 { font-size: 1rem; font-weight: 700; margin-bottom: .4rem; }
        .sol-modal p  { font-size: 0.8rem; color: #64748b; margin-bottom: 1rem; line-height: 1.5; }
        .sol-modal input {
            width: 100%; padding: .55rem .75rem; border: 1.5px solid #e2e8f0;
            border-radius: 8px; font-size: 0.85rem; margin-bottom: .75rem;
            box-sizing: border-box; outline: none;
        }
        .sol-modal input:focus { border-color: #0097A7; }
        .sol-modal__footer { display: flex; gap: .6rem; justify-content: flex-end; }
        .sol-msg { font-size: 0.78rem; color: #dc2626; margin-bottom: .5rem; display: none; }

        /* Historial */
        .sol-historial-badge--COBERTURA { background: #fef3c7; color: #92400e; }
        .sol-historial-badge--CAMBIO    { background: #e0f7fa; color: #0097A7; }

        @media (max-width: 600px) {
            .sol-row { grid-template-columns: 1fr 1fr; }
            .sol-row__rol, .sol-row__turno { display: none; }
        }
    </style>
</head>
<body>
<?php
$hoy     = new DateTime('now', new DateTimeZone('America/Lima'));
$fechaDt = new DateTime($fecha, new DateTimeZone('America/Lima'));
$esPasado = $fechaDt < $hoy->setTime(0, 0);

$roles = ['CAJERA' => 'Cajera', 'VENDEDORA' => 'Vendedora', 'ALMACENERA' => 'Almacenera'];
$turnosLabel = [1 => '☀️ Mañana', 2 => '🌙 Tarde'];
?>

<header class="hor-header">
    <div class="hor-header__brand">
        <div class="hor-header__logo">SB</div>
        <div>
            <p class="hor-header__company">Grupo KGyR S.A.C</p>
            <p class="hor-header__app">Solicitudes de cambio de horario</p>
        </div>
    </div>
    <div class="hor-header__right">
        <span class="hor-header__user"><?= htmlspecialchars($userName) ?></span>
        <a href="<?= $basePath ?>/horario/informacion" class="hor-btn hor-btn--outline" style="font-size:0.78rem;">ℹ️ Información</a>
        <a href="<?= $basePath ?>/horario" class="hor-btn-back">← Horario</a>
    </div>
</header>

<!-- Modal confirmación -->
<div id="solModal" class="sol-modal-overlay" hidden>
    <div class="sol-modal">
        <h3 id="solModalTitulo">Confirmar acción</h3>
        <p id="solModalDesc"></p>
        <div id="solMsg" class="sol-msg"></div>
        <input type="password" id="solPassword" placeholder="Tu contraseña" autocomplete="current-password">
        <div class="sol-modal__footer">
            <button onclick="cerrarModal()"
                    style="background:#f1f5f9;border:none;border-radius:7px;padding:.5rem 1rem;font-size:0.82rem;cursor:pointer;color:#475569;">
                Cancelar
            </button>
            <button onclick="confirmarCubrir()" class="sol-btn">Confirmar</button>
        </div>
    </div>
</div>

<main class="hor-main">

    <!-- ── Selector de fecha ─── -->
    <form method="GET" style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
        <label style="font-size:0.82rem;font-weight:600;color:#475569;">Ver turnos del día:</label>
        <input type="date" name="fecha" value="<?= htmlspecialchars($fecha) ?>"
               class="hor-btn hor-btn--outline" style="padding:.4rem .75rem;font-size:0.85rem;cursor:pointer;">
        <button type="submit" class="hor-btn hor-btn--primary">Buscar</button>
    </form>

    <?php if ($esPasado): ?>
    <div class="hor-alert hor-alert--info">
        📅 Los turnos de días pasados no pueden modificarse. Solo se muestran como referencia.
    </div>
    <?php endif; ?>

    <?php if (empty($slots)): ?>
    <div class="hor-empty">
        <div class="hor-empty__icon">📋</div>
        <h2>Sin turnos registrados</h2>
        <p>No hay horario cargado para el <?= $fechaDt->format('d/m/Y') ?>.</p>
    </div>

    <?php else: ?>

    <!-- ── Tabla de turnos ─── -->
    <section class="hor-semana-header" style="flex-direction:column;align-items:flex-start;gap:.5rem;">
        <h2 class="hor-semana-titulo">
            Turnos del <?= $fechaDt->format('d/m/Y') ?>
            <span class="hor-semana-estado <?= $esPasado ? 'estado-cerrada' : 'estado-abierta' ?>">
                <?= $esPasado ? '🔒 Pasado' : '✏️ Modificable' ?>
            </span>
        </h2>
        <p style="font-size:0.75rem;color:#64748b;">
            <?= $esPasado ? 'Solo visualización' : 'Puedes solicitar cubrir o cambiar un turno de esta fecha.' ?>
        </p>
    </section>

    <div class="sol-grid">
        <!-- Cabecera -->
        <div class="sol-row" style="background:#f8fafc;font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#64748b;">
            <div>Local</div>
            <div>Turno</div>
            <div>Posición</div>
            <div>Asignado a</div>
            <div>Acción</div>
        </div>

        <?php foreach ($slots as $s):
            $esLibre  = !$s['postulante_id'];
            $esPropio = (int)($s['postulante_id'] ?? 0) === (int)($_SESSION['user_id'] ?? 0);
            $bloqueado = $esPasado;
            $rol = $roles[$s['rol_puesto']] ?? $s['rol_puesto'];
        ?>
        <div class="sol-row <?= $bloqueado ? 'sol-row--pasado' : '' ?>">
            <div>
                <div class="sol-row__local"><?= htmlspecialchars($s['local_desc']) ?></div>
            </div>
            <div class="sol-row__turno"><?= $turnosLabel[$s['turno_id']] ?? $s['turno_id'] ?></div>
            <div class="sol-row__rol"><?= $rol ?> <?= $s['slot_num'] > 1 ? '#'.$s['slot_num'] : '' ?></div>
            <div>
                <?php if ($esLibre): ?>
                    <span class="sol-row__empty">— Libre —</span>
                <?php elseif ($esPropio): ?>
                    <span class="sol-row__name" style="color:#0097A7;">Tú (<?= htmlspecialchars($s['trabajador_nombre']) ?>)</span>
                <?php else: ?>
                    <span class="sol-row__name"><?= htmlspecialchars($s['trabajador_nombre']) ?></span>
                <?php endif; ?>
            </div>
            <div>
                <?php if ($bloqueado || $esPropio): ?>
                    <button class="sol-btn sol-btn--lock" disabled>
                        <?= $bloqueado ? '🔒' : '✓ Tuyo' ?>
                    </button>
                <?php else: ?>
                    <button class="sol-btn"
                            onclick="abrirModal(<?= $s['id_slot'] ?>,
                                '<?= addslashes($s['local_desc']) ?> — <?= addslashes($turnosLabel[$s['turno_id']] ?? '') ?>',
                                '<?= $esLibre ? '' : addslashes($s['trabajador_nombre']) ?>')">
                        <?= $esLibre ? '+ Tomar turno' : '⇄ Cubrir' ?>
                    </button>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php endif; ?>

    <!-- ── Historial de solicitudes ─── -->
    <?php if (!empty($historial)): ?>
    <section class="hor-sala" style="--sala-color:#0097A7;">
        <div class="hor-sala__titulo">📋 Historial de solicitudes recientes</div>
        <div style="padding:.75rem 1rem;">
            <table style="width:100%;border-collapse:collapse;font-size:0.8rem;">
                <thead>
                    <tr style="border-bottom:2px solid #e2e8f0;">
                        <th style="text-align:left;padding:6px 8px;color:#64748b;font-size:0.72rem;text-transform:uppercase;">Fecha turno</th>
                        <th style="text-align:left;padding:6px 8px;color:#64748b;font-size:0.72rem;text-transform:uppercase;">Local / Turno</th>
                        <th style="text-align:left;padding:6px 8px;color:#64748b;font-size:0.72rem;text-transform:uppercase;">Tipo</th>
                        <th style="text-align:left;padding:6px 8px;color:#64748b;font-size:0.72rem;text-transform:uppercase;">Quién cubrió</th>
                        <th style="text-align:left;padding:6px 8px;color:#64748b;font-size:0.72rem;text-transform:uppercase;">Reemplazó a</th>
                        <th style="text-align:left;padding:6px 8px;color:#64748b;font-size:0.72rem;text-transform:uppercase;">Registrado</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($historial as $h): ?>
                    <tr style="border-bottom:1px solid #f1f5f9;">
                        <td style="padding:7px 8px;"><?= date('d/m/Y', strtotime($h['fecha_dia'])) ?></td>
                        <td style="padding:7px 8px;"><?= htmlspecialchars($h['local_desc']) ?> · <?= htmlspecialchars($h['turno_desc']) ?></td>
                        <td style="padding:7px 8px;">
                            <span class="hor-semana-estado sol-historial-badge--<?= $h['tipo'] ?>" style="font-size:0.7rem;">
                                <?= $h['tipo'] === 'COBERTURA' ? 'Cobertura' : 'Cambio' ?>
                            </span>
                        </td>
                        <td style="padding:7px 8px;font-weight:600;"><?= htmlspecialchars($h['solicitante_nombre']) ?></td>
                        <td style="padding:7px 8px;color:#64748b;"><?= $h['original_nombre'] ? htmlspecialchars($h['original_nombre']) : '<span style="color:#cbd5e1">—</span>' ?></td>
                        <td style="padding:7px 8px;color:#94a3b8;font-size:0.72rem;">
                            <?= date('d/m H:i', strtotime($h['fecha_solicitud'])) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
    <?php endif; ?>

</main>

<script>
const BASE = '<?= $basePath ?>';
let _slotId = null;

function abrirModal(slotId, ubicacion, nombreOriginal) {
    _slotId = slotId;
    const esCobertura = nombreOriginal !== '';
    document.getElementById('solModalTitulo').textContent = esCobertura ? 'Confirmar cobertura' : 'Tomar turno libre';
    document.getElementById('solModalDesc').textContent   = esCobertura
        ? `Vas a cubrir el turno de "${nombreOriginal}" en ${ubicacion}. Confirma con tu contraseña.`
        : `Vas a asignarte el turno libre en ${ubicacion}. Confirma con tu contraseña.`;
    document.getElementById('solPassword').value = '';
    document.getElementById('solMsg').style.display = 'none';
    document.getElementById('solModal').removeAttribute('hidden');
    setTimeout(() => document.getElementById('solPassword').focus(), 50);
}

function cerrarModal() {
    document.getElementById('solModal').setAttribute('hidden', '');
    _slotId = null;
}

async function confirmarCubrir() {
    const password = document.getElementById('solPassword').value.trim();
    const msgEl    = document.getElementById('solMsg');
    if (!password) { msgEl.textContent = 'Ingresa tu contraseña.'; msgEl.style.display = 'block'; return; }

    try {
        const r   = await fetch(`${BASE}/horario/api/solicitud/cubrir`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ slot_id: _slotId, password }),
        });
        const res = await r.json();
        if (res.success) {
            cerrarModal();
            location.reload();
        } else {
            msgEl.textContent   = res.message || 'Error al procesar.';
            msgEl.style.display = 'block';
        }
    } catch {
        msgEl.textContent   = 'Error de conexión.';
        msgEl.style.display = 'block';
    }
}
</script>
</body>
</html>
