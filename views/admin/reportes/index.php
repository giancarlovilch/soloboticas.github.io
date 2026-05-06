<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes | Solo Boticas</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/caja.css">
    <link rel="icon" type="image/x-icon" href="<?= $basePath ?>/assets/img/logo.ico">
    <style>
        .rep-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.25rem;
        }
        .rep-card {
            display: flex; flex-direction: column; gap: .75rem;
            padding: 1.75rem 1.5rem;
            background: #fff; border: 1.5px solid #e2e8f0;
            border-radius: 14px; text-decoration: none; color: inherit;
            transition: box-shadow .15s, border-color .15s, transform .15s;
            box-shadow: 0 1px 4px rgba(0,0,0,.05);
        }
        .rep-card:hover {
            border-color: #0097A7; transform: translateY(-2px);
            box-shadow: 0 6px 24px rgba(0,151,167,.15);
        }
        .rep-card__icon  { font-size: 2.4rem; line-height: 1; }
        .rep-card__title { font-size: 1.05rem; font-weight: 700; color: #1e293b; }
        .rep-card__desc  { font-size: 0.82rem; color: #64748b; line-height: 1.5; }
        .rep-card__badge {
            align-self: flex-start;
            font-size: 0.68rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: .06em; padding: 2px 8px; border-radius: 20px;
            background: #e0f7fa; color: #0097A7;
        }
        .rep-card--soon { opacity: .5; cursor: not-allowed; }
        .rep-card--soon:hover { transform: none; box-shadow: 0 1px 4px rgba(0,0,0,.05); border-color: #e2e8f0; }
    </style>
</head>
<body style="background:#f1f5f9;min-height:100vh;">

<header class="caja-header">
    <div class="caja-header__brand">
        <div class="caja-header__logo">SB</div>
        <div>
            <p class="caja-header__company">Grupo KGyR S.A.C</p>
            <p class="caja-header__app">Centro de <strong>Reportes</strong></p>
        </div>
    </div>
    <div class="caja-header__right">
        <span class="caja-header__user"><?= htmlspecialchars($userName) ?></span>
        <a href="<?= $basePath ?>/admin/dashboard" class="caja-btn-back">← Dashboard</a>
    </div>
</header>

<main class="caja-main" style="max-width:1000px;">

    <div style="margin-bottom:1.5rem;">
        <p style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#0097A7;margin-bottom:4px;">
            Análisis y métricas
        </p>
        <h1 style="font-size:1.5rem;font-weight:800;color:#1e293b;">Reportes del sistema</h1>
        <p style="font-size:0.85rem;color:#64748b;margin-top:4px;">
            Selecciona el reporte que deseas consultar.
        </p>
    </div>

    <div class="rep-grid">

        <!-- ── Resultado de Arqueos ─── -->
        <a href="<?= $basePath ?>/admin/reportes/arqueos" class="rep-card">
            <div class="rep-card__icon">🧾</div>
            <div class="rep-card__title">Resultado de Arqueos</div>
            <div class="rep-card__desc">
                Lista todos los cierres de caja con su resultado (conforme, superávit o déficit),
                cajera, vendedor, caja, turno y fecha.
            </div>
            <span class="rep-card__badge">Caja</span>
        </a>

        <!-- ── Próximos reportes (pronto) ─── -->
        <div class="rep-card rep-card--soon">
            <div class="rep-card__icon">📅</div>
            <div class="rep-card__title">Asistencias por período</div>
            <div class="rep-card__desc">Resumen de asistencias, tardanzas y faltas por trabajador en un rango de fechas.</div>
            <span class="rep-card__badge" style="background:#f1f5f9;color:#94a3b8;">Próximamente</span>
        </div>

        <div class="rep-card rep-card--soon">
            <div class="rep-card__icon">💸</div>
            <div class="rep-card__title">Faltantes por cajero</div>
            <div class="rep-card__desc">Acumulado mensual de déficits por cada cajero para descuentos y evaluación.</div>
            <span class="rep-card__badge" style="background:#f1f5f9;color:#94a3b8;">Próximamente</span>
        </div>

        <div class="rep-card rep-card--soon">
            <div class="rep-card__icon">📲</div>
            <div class="rep-card__title">Cobros electrónicos</div>
            <div class="rep-card__desc">Detalle de Yapes, Visas y transferencias por sesión, cajero y vendedor.</div>
            <span class="rep-card__badge" style="background:#f1f5f9;color:#94a3b8;">Próximamente</span>
        </div>

    </div>

</main>
</body>
</html>
