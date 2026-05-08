<?php
/** @var array $sesion */ /** @var array $detalle */ /** @var array|false $venta */
/** @var array $gastos */ /** @var array $rectifs */ /** @var array $digitales */
/** @var float $digital_aprobado */ /** @var string|null $vendedor */
$basePath  = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
$userName  = $userName ?? $_SESSION['user_name'] ?? 'Usuario';
$f2 = fn($v) => 'S/ ' . number_format((float)$v, 2, '.', ',');

// LO QUE ES = conteo físico + ajustes de cierre (rectificaciones)
$loQueEsFisico = (float)($detalle['monto_caja_exterior']        ?? 0)
               + (float)($detalle['monto_monedas']              ?? 0)
               + (float)($detalle['monto_billetes_caja']        ?? 0)
               + (float)($detalle['monto_billetes_caja_fuerte'] ?? 0)
               + (float)($detalle['monto_agente_bcp']           ?? 0);

$sum_rectifs = array_sum(array_column($rectifs ?? [], 'monto'));
$loQueEs     = $loQueEsFisico + $sum_rectifs;

// LO QUE SE DICE = base + ventas - gastos - digitales declarados
$saldo_ini        = (float)($sesion['saldo_inicial']         ?? 0);
$total_ventas     = (float)($detalle['total_ventas_sistema'] ?? ($venta['monto'] ?? 0));
$total_gastos     = (float)($detalle['total_gastos_sistema'] ?? 0);
$digital_aprobado = $digital_aprobado ?? 0;
$loQueSeDice      = $saldo_ini + $total_ventas - $total_gastos - $digital_aprobado;

$diferencia = $loQueEs - $loQueSeDice;
$resultado  = abs($diferencia) < 0.01 ? 'CONSISTENTE' : ($diferencia > 0 ? 'SOBRANTE' : 'FALTANTE');
$clsDif     = abs($diferencia) < 0.01 ? 'dif-ok' : ($diferencia > 0 ? 'dif-sobrante' : 'dif-faltante');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arqueo de caja | Solo Boticas</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/caja.css">
</head>
<body>

<header class="caja-header">
    <div class="caja-header__brand">
        <div class="caja-header__logo">SB</div>
        <div>
            <p class="caja-header__company">Grupo KGyR S.A.C</p>
            <p class="caja-header__app">Arqueo de caja</p>
        </div>
    </div>
    <div class="caja-header__right">
        <a href="<?= $basePath ?>/caja" class="caja-btn-back">← Volver a caja</a>
    </div>
</header>

<main class="caja-main">

    <!-- ── Encabezado del reporte ─────────────────────────── -->
    <section class="caja-card caja-card--info">
        <div class="caja-info-row">
            <strong>#<?= $sesion['id_sesion'] ?> — <?= htmlspecialchars($sesion['caja_desc']) ?> · <?= htmlspecialchars($sesion['local_desc']) ?></strong>
            <span><?= htmlspecialchars($sesion['turno_desc']) ?> · <?= date('d/m/Y', strtotime($sesion['fecha_operacion'])) ?></span>
            <span>Cajera: <?= htmlspecialchars($sesion['cajera_nombre']) ?><?= $vendedor ? ' · Vendedor/a: ' . htmlspecialchars($vendedor) : '' ?></span>
            <button onclick="abrirModalEliminar()"
                    style="margin-left:auto;background:transparent;border:1px solid #fca5a5;color:#ef4444;border-radius:6px;padding:4px 12px;font-size:0.75rem;font-weight:600;cursor:pointer;">
                🗑 Eliminar arqueo
            </button>
        </div>
    </section>

    <!-- ── Modal eliminar ────────────────────────────────── -->
    <div id="modalEliminar" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.55);z-index:500;align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:14px;padding:1.75rem;width:320px;max-width:90vw;box-shadow:0 20px 60px rgba(0,0,0,.22);">
            <h3 style="font-size:1rem;font-weight:700;color:#1e293b;margin-bottom:.35rem;">Eliminar cuadre #<?= $sesion['id_sesion'] ?></h3>
            <p style="font-size:0.8rem;color:#64748b;margin-bottom:1rem;">
                Esta acción borrará el cuadre y todos sus datos (activos, gastos, pagos digitales, rectificaciones).
                No se puede deshacer.
            </p>
            <label style="font-size:0.8rem;font-weight:600;color:#334155;display:block;margin-bottom:.35rem;">
                Confirma con tu contraseña de administrador
            </label>
            <input type="password" id="eliminarPassword"
                   style="width:100%;padding:.55rem .75rem;border:1.5px solid #e2e8f0;border-radius:8px;font-size:0.85rem;margin-bottom:.75rem;box-sizing:border-box;"
                   placeholder="Contraseña" autocomplete="current-password">
            <div id="eliminarMsg" style="font-size:0.78rem;color:#991b1b;margin-bottom:.5rem;display:none;"></div>
            <div style="display:flex;gap:.6rem;justify-content:flex-end;">
                <button onclick="cerrarModalEliminar()"
                        style="background:#f1f5f9;border:none;border-radius:7px;padding:.5rem 1rem;font-size:0.82rem;cursor:pointer;color:#475569;">
                    Cancelar
                </button>
                <button onclick="confirmarEliminar(<?= $sesion['id_sesion'] ?>)"
                        style="background:#dc2626;border:none;border-radius:7px;padding:.5rem 1rem;font-size:0.82rem;font-weight:700;color:#fff;cursor:pointer;">
                    Eliminar definitivamente
                </button>
            </div>
        </div>
    </div>

    <!-- ── Resultado del arqueo ──────────────────────────── -->
    <section class="caja-card caja-resultado <?= $clsDif ?>">
        <div class="caja-resultado__icono">
            <?= abs($diferencia) < 0.01 ? '✓' : ($diferencia > 0 ? '↑' : '↓') ?>
        </div>
        <div class="caja-resultado__info">
            <p class="caja-resultado__label">Resultado del arqueo</p>
            <h2 class="caja-resultado__estado">
                <?= $resultado === 'CONSISTENTE' ? 'CONFORME' : $resultado ?>
            </h2>
            <p class="caja-resultado__dif">
                Diferencia: <strong><?= $f2($diferencia) ?></strong>
                <?= abs($diferencia) < 0.01 ? '' : ($diferencia > 0 ? '— Superávit' : '— Déficit') ?>
            </p>
        </div>
    </section>

    <!-- ── Cálculo detallado ──────────────────────────────── -->
    <section class="caja-card">
        <h2 class="caja-card__title">Detalle del arqueo</h2>
        <div class="caja-cuadre-grid">

            <!-- SALDO ESPERADO -->
            <div class="caja-cuadre-col caja-cuadre-col--esperado">
                <h3 class="caja-cuadre-col__title">📋 SALDO ESPERADO</h3>
                <div class="caja-linea">
                    <span>Saldo de apertura</span>
                    <strong><?= $f2($saldo_ini) ?></strong>
                </div>
                <div class="caja-linea">
                    <span>+ Ventas del turno</span>
                    <strong><?= $f2($total_ventas) ?></strong>
                </div>
                <div class="caja-linea caja-linea--sub">
                    <span>− Egresos del turno</span>
                    <strong class="text-danger"><?= $f2($total_gastos) ?></strong>
                </div>
                <?php if ($digital_aprobado > 0): ?>
                <div class="caja-linea caja-linea--sub">
                    <span>− Cobros electrónicos</span>
                    <strong class="text-danger"><?= $f2($digital_aprobado) ?></strong>
                </div>
                <?php endif; ?>
                <div class="caja-linea caja-linea--total">
                    <span>= Efectivo esperado</span>
                    <strong><?= $f2($loQueSeDice) ?></strong>
                </div>
            </div>

            <!-- ARQUEO DE CAJA -->
            <div class="caja-cuadre-col caja-cuadre-col--real">
                <h3 class="caja-cuadre-col__title">🪙 ARQUEO DE CAJA</h3>
                <div class="caja-linea">
                    <span>Caja</span>
                    <strong><?= $f2($detalle['monto_caja_exterior'] ?? 0) ?></strong>
                </div>
                <div class="caja-linea">
                    <span>+ Monedas</span>
                    <strong><?= $f2($detalle['monto_monedas'] ?? 0) ?></strong>
                </div>
                <div class="caja-linea">
                    <span>+ Billetes</span>
                    <strong><?= $f2($detalle['monto_billetes_caja'] ?? 0) ?></strong>
                </div>
                <div class="caja-linea">
                    <span>+ Caja fuerte</span>
                    <strong><?= $f2($detalle['monto_billetes_caja_fuerte'] ?? 0) ?></strong>
                </div>
                <div class="caja-linea">
                    <span>+ Saldo Agente BCP</span>
                    <strong><?= $f2($detalle['monto_agente_bcp'] ?? 0) ?></strong>
                </div>
                <?php if ($sum_rectifs != 0): ?>
                <div class="caja-linea" style="margin-top:.25rem;">
                    <span style="color:#dc2626;font-weight:600;">+ Ajuste de caja</span>
                    <strong style="color:#dc2626;"><?= $f2($sum_rectifs) ?></strong>
                </div>
                <?php endif; ?>
                <div class="caja-linea caja-linea--total">
                    <span>= Total arqueado</span>
                    <strong><?= $f2($loQueEs) ?></strong>
                </div>
            </div>

        </div>

        <div class="caja-proximo">
            <p>→ Saldo de apertura del próximo turno: <strong class="text-highlight"><?= $f2($detalle['saldo_proximo_dia'] ?? $loQueEs) ?></strong>
               <small>(total arqueado)</small></p>
            <?php if (!empty($detalle['num_operaciones_bcp'])): ?>
            <p style="margin-top:.4rem;font-size:0.82rem;color:#64748b;">
                Operaciones BCP realizadas: <strong><?= (int)$detalle['num_operaciones_bcp'] ?></strong>
            </p>
            <?php endif; ?>
        </div>
    </section>

    <!-- ── Gastos detallados ───────────────────────────────── -->
    <section class="caja-card">
        <h2 class="caja-card__title">Egresos del turno (<?= count($gastos) ?>)</h2>
        <?php if (empty($gastos)): ?>
            <p class="caja-empty">Sin gastos registrados.</p>
        <?php else: ?>
        <table class="caja-table">
            <thead><tr><th>Tipo</th><th>Descripción</th><th>Comprobante</th><th class="text-right">Monto</th></tr></thead>
            <tbody>
            <?php foreach ($gastos as $g): ?>
                <tr>
                    <td><span class="caja-gasto-badge caja-gasto-badge--<?= strtolower($g['tipo']) ?>"><?= $g['tipo'] ?></span></td>
                    <td><?= htmlspecialchars($g['descripcion']) ?></td>
                    <td><?= htmlspecialchars($g['comprobante'] ?? '—') ?></td>
                    <td class="text-right"><?= $f2($g['monto']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </section>

    <!-- ── Pagos digitales detallados ────────────────────────── -->
    <section class="caja-card">
        <h2 class="caja-card__title">Cobros electrónicos del turno (<?= count($digitales ?? []) ?>)</h2>
        <?php if (empty($digitales)): ?>
            <p class="caja-empty">Sin pagos digitales registrados.</p>
        <?php else: ?>
        <table class="caja-table">
            <thead>
                <tr>
                    <th>Hora</th>
                    <th>Modo</th>
                    <th>N° Operación</th>
                    <th class="text-right">Monto</th>
                    <th class="text-center">Estado</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $estadoDigital = [
                'PENDIENTE' => ['label' => 'Pendiente',  'cls' => 'background:#fef3c7;color:#92400e;'],
                'APROBADO'  => ['label' => 'Aprobado',   'cls' => 'background:#d1fae5;color:#065f46;'],
                'RECHAZADO' => ['label' => 'Rechazado',  'cls' => 'background:#fee2e2;color:#991b1b;'],
            ];
            foreach ($digitales as $dg):
                $est = $estadoDigital[$dg['estado']] ?? ['label' => $dg['estado'], 'cls' => ''];
            ?>
                <tr>
                    <td style="font-size:0.78rem;color:#64748b;">
                        <?= date('H:i', strtotime($dg['fecha_movimiento'])) ?>
                    </td>
                    <td><strong><?= htmlspecialchars($dg['modo_desc']) ?></strong></td>
                    <td style="font-size:0.78rem;"><?= htmlspecialchars($dg['numero_operacion'] ?? '—') ?></td>
                    <td class="text-right"><?= $f2($dg['monto']) ?></td>
                    <td class="text-center">
                        <span style="font-size:0.72rem;font-weight:700;border-radius:4px;padding:2px 8px;<?= $est['cls'] ?>">
                            <?= $est['label'] ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </section>

    <!-- ── Rectificaciones ────────────────────────────────── -->
    <section class="caja-card">
        <div class="caja-card__header-row">
            <div>
                <h2 class="caja-card__title">Ajustes de cierre</h2>
                <p class="caja-card__desc">Si hay diferencias justificadas, regístralas aquí. El monto ajusta el saldo de apertura del siguiente turno.</p>
            </div>
        </div>

        <?php
        $tipoLabel = [
            'DINERO_ENCONTRADO' => 'Efectivo encontrado',
            'DEVOLUCION_DINERO' => 'Devolución de efectivo',
            'AJUSTE_CONTEO'     => 'Ajuste de conteo',
            'COMPENSACION'      => 'Compensación autorizada',
            'OTRO'              => 'Otro concepto',
        ];
        ?>
        <?php if (!empty($rectifs)): ?>
        <table class="caja-table" style="margin-bottom:1rem;">
            <thead><tr><th>Tipo</th><th>Descripción</th><th>Por</th><th class="text-right">Monto</th></tr></thead>
            <tbody>
            <?php foreach ($rectifs as $r): ?>
                <tr>
                    <td><?= $tipoLabel[$r['tipo_rectificacion']] ?? htmlspecialchars($r['tipo_rectificacion']) ?></td>
                    <td><?= htmlspecialchars($r['descripcion_contexto']) ?></td>
                    <td><?= htmlspecialchars($r['registrado_por']) ?></td>
                    <td class="text-right <?= $r['monto'] < 0 ? 'text-danger':'' ?>"><?= $f2($r['monto']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <div class="caja-rect-form">
            <select id="rectTipo" class="caja-input">
                <option value="DINERO_ENCONTRADO">Efectivo encontrado</option>
                <option value="DEVOLUCION_DINERO">Devolución de efectivo</option>
                <option value="AJUSTE_CONTEO">Ajuste de conteo</option>
                <option value="COMPENSACION">Compensación autorizada</option>
                <option value="OTRO">Otro concepto</option>
            </select>
            <input type="text" id="rectDesc" class="caja-input" placeholder="Descripción (ej: encontré S/20 en bolsillo)">
            <div class="caja-input-money">
                <span>S/</span>
                <input type="number" id="rectMonto" class="caja-input caja-input--money" step="0.01" placeholder="Monto (negativo = descuento)">
            </div>
            <button class="caja-btn caja-btn--secondary" onclick="submitRectificacion(<?= $sesion['id_sesion'] ?>)">
                Aplicar
            </button>
        </div>
        <div id="rectMsg" class="caja-alert" hidden></div>
    </section>

</main>

<script>
const BASE = '<?= $basePath ?>';

async function submitRectificacion(sesionId) {
    const tipo  = document.getElementById('rectTipo').value;
    const desc  = document.getElementById('rectDesc').value.trim();
    const monto = parseFloat(document.getElementById('rectMonto').value);
    const msg   = document.getElementById('rectMsg');

    if (!desc || isNaN(monto) || monto === 0) {
        showAlert(msg, 'Completa descripción y monto.', 'error'); return;
    }

    try {
        const r   = await fetch(`${BASE}/caja/api/reporte/${sesionId}/rectificar`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ tipo, descripcion: desc, monto }),
        });
        const res = await r.json();
        if (res.success) { showAlert(msg, '✓ ' + res.message, 'ok'); setTimeout(() => location.reload(), 1200); }
        else showAlert(msg, res.message, 'error');
    } catch { showAlert(msg, 'Error de conexión.', 'error'); }
}

function showAlert(el, txt, type) {
    el.textContent = txt;
    el.className   = `caja-alert caja-alert--${type}`;
    el.hidden      = false;
}

// ── Eliminar cuadre ───────────────────────────────────
function abrirModalEliminar() {
    document.getElementById('modalEliminar').style.display = 'flex';
    document.getElementById('eliminarPassword').value = '';
    document.getElementById('eliminarMsg').style.display = 'none';
    setTimeout(() => document.getElementById('eliminarPassword').focus(), 50);
}

function cerrarModalEliminar() {
    document.getElementById('modalEliminar').style.display = 'none';
}

async function confirmarEliminar(sesionId) {
    const password = document.getElementById('eliminarPassword').value.trim();
    const msgEl    = document.getElementById('eliminarMsg');

    if (!password) {
        msgEl.textContent = 'Ingresa tu contraseña.';
        msgEl.style.display = 'block'; return;
    }

    try {
        const r   = await fetch(`${BASE}/caja/api/sesion/${sesionId}/eliminar`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ password }),
        });
        const res = await r.json();
        if (res.success) {
            window.location.href = `${BASE}/caja`;
        } else {
            msgEl.textContent = res.message || 'Error al eliminar.';
            msgEl.style.display = 'block';
        }
    } catch {
        msgEl.textContent = 'Error de conexión.';
        msgEl.style.display = 'block';
    }
}
</script>
</body>
</html>
