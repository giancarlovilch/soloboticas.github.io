<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso restringido | Caja SB</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/caja.css">
</head>
<body>
<header class="caja-header">
    <div class="caja-header__brand">
        <div class="caja-header__logo">SB</div>
        <div>
            <p class="caja-header__company">Grupo KGyR S.A.C</p>
            <p class="caja-header__app">Acceso restringido</p>
        </div>
    </div>
    <div class="caja-header__right">
        <a href="<?= $basePath ?>/caja" class="caja-btn-back">← Volver</a>
    </div>
</header>
<main class="caja-main caja-main--narrow">
    <section class="caja-card" style="text-align:center;padding:3rem 2rem;">
        <div style="font-size:3rem;margin-bottom:1rem;">🔒</div>
        <h2 style="font-size:1.15rem;font-weight:700;color:#1e293b;margin-bottom:.75rem;">
            Solo <strong><?= htmlspecialchars($nombreVend) ?></strong> tiene acceso a esta página
        </h2>
        <p style="font-size:.85rem;color:#64748b;line-height:1.6;margin-bottom:1.5rem;">
            Esta sección es exclusiva para la vendedora asignada a este turno.<br>
            Si eres la vendedora, inicia sesión con tu cuenta.
        </p>
        <a href="<?= $basePath ?>/caja" class="caja-btn caja-btn--primary" style="display:inline-block;text-decoration:none;">
            ← Volver a caja
        </a>
    </section>
</main>
</body>
</html>
