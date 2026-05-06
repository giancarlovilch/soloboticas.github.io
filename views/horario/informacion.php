<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Información · Penalidades | Solo Boticas</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/horario.css">
    <link rel="icon" type="image/x-icon" href="<?= $basePath ?>/assets/img/logo.ico">
    <style>
        .info-section { display: flex; flex-direction: column; gap: 1.25rem; }
        .info-card {
            background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
            overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.05);
        }
        .info-card__header {
            padding: .85rem 1.25rem; font-size: 0.7rem; font-weight: 800;
            text-transform: uppercase; letter-spacing: .08em;
            border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; gap: .5rem;
        }
        .info-card--penalidad .info-card__header { background: #fee2e2; color: #991b1b; }
        .info-card--beneficio .info-card__header { background: #d1fae5; color: #065f46; }
        .info-card--tarifa    .info-card__header { background: #eff6ff; color: #1d4ed8; }

        .info-row {
            display: flex; justify-content: space-between; align-items: flex-start;
            padding: .85rem 1.25rem; gap: 1rem;
        }
        .info-row + .info-row { border-top: 1px solid #f1f5f9; }
        .info-row__desc { flex: 1; }
        .info-row__desc strong { display: block; font-size: 0.87rem; color: #1e293b; margin-bottom: 2px; }
        .info-row__desc span  { font-size: 0.75rem; color: #64748b; line-height: 1.4; }
        .info-row__monto {
            font-size: 1rem; font-weight: 800; white-space: nowrap;
            font-variant-numeric: tabular-nums;
        }
        .monto--neg { color: #dc2626; }
        .monto--pos { color: #059669; }
        .monto--neu { color: #2563eb; }

        .info-flujo {
            background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px;
            padding: 1.25rem; display: flex; flex-direction: column; gap: .75rem;
        }
        .info-flujo__title { font-size: 0.82rem; font-weight: 700; color: #1e293b; margin-bottom: .25rem; }
        .info-flujo__step  { display: flex; align-items: center; gap: .75rem; font-size: 0.82rem; color: #475569; }
        .info-flujo__icon  { font-size: 1.1rem; flex-shrink: 0; }

        <?php if ($esAdmin): ?>
        .admin-edit-form { display: flex; gap: .5rem; align-items: center; margin-top: .5rem; flex-wrap: wrap; }
        .admin-edit-form input { padding: .35rem .6rem; border: 1.5px solid #e2e8f0; border-radius: 6px; font-size: 0.82rem; width: 90px; }
        .admin-edit-form button { padding: .35rem .8rem; border-radius: 6px; font-size: 0.75rem; font-weight: 600; border: none; cursor: pointer; background: #0097A7; color: #fff; }
        <?php endif; ?>
    </style>
</head>
<body>
<?php
$tipos = [
    'PENALIDAD' => ['label' => '⚠️ Penalidades', 'cls' => 'penalidad'],
    'BENEFICIO' => ['label' => '🎁 Beneficios',  'cls' => 'beneficio'],
    'TARIFA'    => ['label' => '💼 Tarifas',      'cls' => 'tarifa'],
];
$porTipo = [];
foreach ($conceptos as $c) $porTipo[$c['tipo']][] = $c;
$f2 = fn($v) => 'S/ ' . number_format(abs((float)$v), 2, '.', ',');
?>

<header class="hor-header">
    <div class="hor-header__brand">
        <div class="hor-header__logo">SB</div>
        <div>
            <p class="hor-header__company">Grupo KGyR S.A.C</p>
            <p class="hor-header__app">Información · Penalidades y beneficios</p>
        </div>
    </div>
    <div class="hor-header__right">
        <span class="hor-header__user"><?= htmlspecialchars($userName) ?></span>
        <a href="<?= $basePath ?>/horario/solicitudes" class="hor-btn hor-btn--outline" style="font-size:0.78rem;">📋 Solicitudes</a>
        <a href="<?= $basePath ?>/horario" class="hor-btn-back">← Horario</a>
    </div>
</header>

<main class="hor-main" style="max-width:740px;">

    <!-- ── Flujo explicativo ─── -->
    <div class="info-flujo">
        <p class="info-flujo__title">¿Cómo funciona la cobertura de turno?</p>
        <div class="info-flujo__step"><span class="info-flujo__icon">1️⃣</span> Un trabajador no puede presentarse a su turno asignado.</div>
        <div class="info-flujo__step"><span class="info-flujo__icon">2️⃣</span> Otro trabajador entra a <em>Solicitudes</em>, selecciona la fecha y hace clic en <strong>Cubrir</strong>.</div>
        <div class="info-flujo__step"><span class="info-flujo__icon">3️⃣</span> Confirma con su contraseña. El turno queda reasignado y queda registrado.</div>
        <div class="info-flujo__step"><span class="info-flujo__icon">4️⃣</span> Se aplica la penalidad al ausente y el bono al que cubrió, según la tabla siguiente.</div>
        <div class="info-flujo__step"><span class="info-flujo__icon">💡</span> Los cambios de horario <em>voluntarios</em> tienen un costo de tarifa por solicitud.</div>
    </div>

    <!-- ── Tablas por tipo ─── -->
    <div class="info-section">
        <?php foreach ($tipos as $tipo => $info): if (empty($porTipo[$tipo])) continue; ?>
        <div class="info-card info-card--<?= $info['cls'] ?>">
            <div class="info-card__header"><?= $info['label'] ?></div>
            <?php foreach ($porTipo[$tipo] as $c):
                $monto = (float)$c['monto'];
                $montoClass = $monto < 0 ? 'monto--neg' : ($monto > 0 ? 'monto--pos' : 'monto--neu');
                $signo = $monto < 0 ? '−' : ($monto > 0 ? '+' : '');
            ?>
            <div class="info-row">
                <div class="info-row__desc">
                    <strong><?= htmlspecialchars($c['descripcion']) ?></strong>
                    <?php if ($c['notas']): ?>
                        <span><?= htmlspecialchars($c['notas']) ?></span>
                    <?php endif; ?>
                    <?php if ($esAdmin): ?>
                    <form class="admin-edit-form" onsubmit="actualizarMonto(event, <?= $c['id_concepto'] ?>)">
                        <input type="number" step="0.01" placeholder="Nuevo monto"
                               name="monto" value="<?= $monto ?>">
                        <button type="submit">Actualizar</button>
                        <span id="fb-<?= $c['id_concepto'] ?>" style="font-size:0.72rem;"></span>
                    </form>
                    <?php endif; ?>
                </div>
                <div class="info-row__monto <?= $montoClass ?>">
                    <?= $signo ?> <?= $f2($monto) ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <p style="font-size:0.72rem;color:#94a3b8;text-align:center;margin-top:.5rem;">
        Los montos son referenciales y pueden ser actualizados por el administrador.
        Última revisión: <?= date('d/m/Y') ?>
    </p>

</main>

<?php if ($esAdmin): ?>
<script>
const BASE = '<?= $basePath ?>';
async function actualizarMonto(e, id) {
    e.preventDefault();
    const monto = parseFloat(e.target.monto.value);
    const fb    = document.getElementById(`fb-${id}`);
    if (isNaN(monto)) { fb.textContent = 'Ingresa un número válido.'; return; }
    try {
        const r   = await fetch(`${BASE}/admin/api/penalidad/${id}`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ monto }),
        });
        const res = await r.json();
        fb.textContent = res.success ? '✓ Guardado' : '✗ Error';
        fb.style.color = res.success ? '#059669' : '#dc2626';
        if (res.success) setTimeout(() => location.reload(), 800);
    } catch { fb.textContent = 'Error de conexión.'; }
}
</script>
<?php endif; ?>
</body>
</html>
