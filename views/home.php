<?php
$basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solo Boticas | Tu botica de confianza</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/home.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Signika:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="<?= $basePath ?>/assets/img/logo.ico">
</head>
<body>

<?php require_once __DIR__ . '/partials/navbar.php'; ?>

<!-- ── HERO ───────────────────────────────────────────────── -->
<section class="hero">
    <div class="hero-overlay">
        <div class="contenedor hero-grid">
            <div class="hero-copy">
                <p class="hero-kicker">Salud y bienestar</p>
                <h2>Tu botica de confianza,<br>más cerca de ti</h2>
                <p class="hero-text">
                    En Solo Boticas trabajamos para brindar productos farmacéuticos, orientación cercana
                    y una experiencia confiable para el cuidado diario de las familias peruanas.
                </p>
                <div class="hero-actions">
                    <a class="boton boton-principal" href="#locales">Ver locales</a>
                    <a class="boton boton-secundario" href="<?= $basePath ?>/postulacion/acceso">
                        Trabaja con nosotros
                    </a>
                </div>
                <div class="hero-stats">
                    <div class="stat-card">
                        <strong>12+</strong>
                        <span>Años de atención</span>
                    </div>
                    <div class="stat-card">
                        <strong>3</strong>
                        <span>Locales activos</span>
                    </div>
                    <div class="stat-card">
                        <strong>100%</strong>
                        <span>Compromiso</span>
                    </div>
                </div>
            </div>

            <div class="hero-panel">
                <div class="hero-panel-card glass-card">
                    <h3>Atención responsable</h3>
                    <p>
                        Nuestro equipo está enfocado en brindar orientación clara, atención amable y acceso
                        a productos esenciales para la salud.
                    </p>
                    <a href="#servicios" class="mini-link">Conocer servicios →</a>
                </div>
            </div>
        </div>
    </div>
</section>

<main class="main-home">

    <!-- ── NOSOTROS ───────────────────────────────────────── -->
    <section class="section contenedor" id="nosotros">
        <div class="section-heading">
            <p class="section-kicker">Nosotros</p>
            <h2>Comprometidos con la salud de cada familia</h2>
            <p>
                Solo Boticas nace con el objetivo de acercar atención, confianza y productos esenciales
                a la comunidad, manteniendo una relación cercana con cada cliente.
            </p>
        </div>

        <div class="feature-grid">
            <article class="feature-card sombra">
                <div class="feature-icon">🎯</div>
                <h3>Misión</h3>
                <p>
                    Brindar atención farmacéutica accesible y confiable, contribuyendo al bienestar de
                    nuestros clientes con un enfoque humano y responsable.
                </p>
            </article>
            <article class="feature-card sombra">
                <div class="feature-icon">🔭</div>
                <h3>Visión</h3>
                <p>
                    Ser una red de boticas reconocida por su cercanía, calidad de atención y compromiso
                    con la salud integral de la población de Lima.
                </p>
            </article>
            <article class="feature-card sombra">
                <div class="feature-icon">⭐</div>
                <h3>Valores</h3>
                <p>
                    Servicio, responsabilidad, confianza, respeto y mejora continua en cada proceso
                    de atención al cliente y al equipo.
                </p>
            </article>
        </div>
    </section>

    <!-- ── SERVICIOS ──────────────────────────────────────── -->
    <section class="section section-soft" id="servicios">
        <div class="contenedor">
            <div class="section-heading">
                <p class="section-kicker">Servicios</p>
                <h2>Lo que ofrecemos en Solo Boticas</h2>
            </div>
            <div class="services-grid">
                <article class="service-card">
                    <div class="service-icon">💊</div>
                    <h3>Venta de medicamentos</h3>
                    <p>Amplio stock farmacéutico con precios accesibles y atención responsable.</p>
                </article>
                <article class="service-card">
                    <div class="service-icon">🩺</div>
                    <h3>Orientación al paciente</h3>
                    <p>Atención personalizada para ayudarte a encontrar el producto adecuado.</p>
                </article>
                <article class="service-card">
                    <div class="service-icon">🏧</div>
                    <h3>Agente BCP</h3>
                    <p>Realiza operaciones bancarias BCP desde cualquiera de nuestros locales.</p>
                </article>
                <article class="service-card">
                    <div class="service-icon">🤝</div>
                    <h3>Empleo con propósito</h3>
                    <p>Desarrollamos talento humano para crecer juntos como equipo comprometido.</p>
                </article>
            </div>
        </div>
    </section>

    <!-- ── LOCALES ────────────────────────────────────────── -->
    <section class="section contenedor" id="locales">
        <div class="section-heading">
            <p class="section-kicker">Locales</p>
            <h2>Nuestra presencia en SJL</h2>
            <p>Estamos más cerca de ti para acompañarte en tu cuidado diario.</p>
        </div>
        <div class="location-grid">
            <article class="location-card sombra">
                <div class="location-num">02</div>
                <h3>Solo Boticas 2</h3>
                <p>Av. Canto Grande</p>
                <span class="location-badge location-badge--open">Abierto</span>
            </article>
            <article class="location-card sombra">
                <div class="location-num">03</div>
                <h3>Solo Boticas 3</h3>
                <p>Av. San Martín</p>
                <span class="location-badge location-badge--open">Abierto</span>
            </article>
            <article class="location-card sombra">
                <div class="location-num">04</div>
                <h3>Solo Boticas 4</h3>
                <p>Av. Las Flores</p>
                <span class="location-badge location-badge--open">Abierto</span>
            </article>
        </div>
    </section>

    <!-- ── CTA RECLUTAMIENTO ──────────────────────────────── -->
    <section class="section section-accent">
        <div class="contenedor cta-grid">
            <div>
                <p class="section-kicker section-kicker-light">Oportunidades</p>
                <h2>Únete al equipo de Solo Boticas</h2>
                <p>
                    Buscamos personas con vocación de servicio, actitud proactiva y compromiso
                    con la atención al cliente. Postula en minutos desde aquí.
                </p>
            </div>
            <div class="cta-actions">
                <a class="boton boton-principal" href="<?= $basePath ?>/postulacion/acceso">
                    Postular ahora
                </a>
                <a class="boton boton-ghost" href="<?= $basePath ?>/login">
                    Intranet
                </a>
            </div>
        </div>
    </section>

</main>

<?php require_once __DIR__ . '/partials/footer.php'; ?>

<script src="<?= $basePath ?>/assets/js/home.js"></script>
</body>
</html>
