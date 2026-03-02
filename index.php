<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>GRUPO KGyR S.A.C</title>
    <link rel="preload" href="css/normalize.css" as="style">
    <link rel="stylesheet" href="css/normalize.css">
    <link href="https://fonts.googleapis.com/css2?family=Signika:wght@400;700&display=swap" rel="stylesheet">
    <link rel="preload" href="css/style.css" as="style">
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" type="image/x-icon" href="img/img_1/SB007.ico">
    <meta property="og:description" content="Salud y cuidado de familia a familia. ¡Bienvenidos a SoloBoticas!" />
</head>

<body>
    <?php
    $menu = [
        'PRINCIPAL' => '/',
        'INTRANET' => '/myphp/login.php'        
    ];

    $locations = [
        'SoloBoticas 1' => '#',
        'SoloBoticas 2' => '#',
        'SoloBoticas 3' => '#',
        'SoloBoticas 4' => '#'
    ];
    ?>

    <header class="logotipo">
        <h1>Solo Boticas <span>Para Su Buena Salud</span></h1>
    </header>

    <div class="nav-bg">
        <nav class="navegacion contenedor">
            <?php foreach ($menu as $name => $url): ?>
                <a href="<?= $url ?>"><?= $name ?></a>
            <?php endforeach; ?>
        </nav>
    </div>

    <div class="hero">
        <div class="contenido-hero">
            <h2>Bienvenido a Grupo KGyR S.A.C</h2>
            <p>
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-current-location" width="32" height="32" viewBox="0 0 24 24" stroke-width="2" stroke="#ffffff" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="3"></circle>
                    <circle cx="12" cy="12" r="8"></circle>
                    <line x1="12" y1="2" x2="12" y2="4"></line>
                    <line x1="12" y1="20" x2="12" y2="22"></line>
                    <line x1="20" y1="12" x2="22" y2="12"></line>
                    <line x1="2" y1="12" x2="4" y2="12"></line>
                </svg>
                SJL, Lima, Peru
            </p>
            <a class="boton" href="#">Contactar</a>
        </div>
    </div>

    <div class="contenedor sombra">
        <h2>Información</h2>
        <main class="servicios">
        <div class="servicio">
                <h3>Trabaja con Nosotros</h3>
                <div class="iconos">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-affiliate" width="52"
                        height="52" viewBox="0 0 24 24" stroke-width="1.5" stroke="#ffffff" fill="none"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M5.931 6.936l1.275 4.249m5.607 5.609l4.251 1.275" />
                        <path d="M11.683 12.317l5.759 -5.759" />
                        <circle cx="5.5" cy="5.5" r="1.5" />
                        <circle cx="18.5" cy="5.5" r="1.5" />
                        <circle cx="18.5" cy="18.5" r="1.5" />
                        <circle cx="8.5" cy="15.5" r="4.5" />
                    </svg>
                </div>
                <p>Debido a nuestro crecimiento constante, necesitamos nuevos colaboradores con vocación de servicio,
                    que acepten nuevos retos y se propongan superar obstáculos. En Solo Boticas apostamos por el
                    desarrollo de nuestros colaboradores en el aspecto laboral y personal. Creemos que el elemento más
                    importante de nuestra empresa son sus colaboradores, los cuales son la razón de nuestro éxito. ¡Sé
                    parte de nuestro gran equipo!
                    <strong>
                        <a title="Revisa las posiciones que tenemos disponibles"
                            href="/info/oportunidades-laborales.html" target="_blank" rel="noopener"
                            style="text-decoration: none;color:cadetblue;">Revisa las posiciones que tenemos
                            disponibles!</a>
                    </strong>
                </p>
            </div>
            <div class="servicio">
                <h3>Nuestros Locales</h3>
                <div class="iconos">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-building-skyscraper"
                        width="52" height="52" viewBox="0 0 24 24" stroke-width="1.5" stroke="#ffffff" fill="none"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <line x1="3" y1="21" x2="21" y2="21" />
                        <path d="M5 21v-14l8 -4v18" />
                        <path d="M19 21v-10l-6 -4" />
                        <line x1="9" y1="9" x2="9" y2="9.01" />
                        <line x1="9" y1="12" x2="9" y2="12.01" />
                        <line x1="9" y1="15" x2="9" y2="15.01" />
                        <line x1="9" y1="18" x2="9" y2="18.01" />
                    </svg>
                </div>
                <p>¿Buscas las tiendas de Solo Boticas en Lima?<br>
                    Encuentra todas las tiendas de Solo Boticas en SJL, Lima. <br> Entra en la que te interese para ver
                    la dirección.<br><a href="#" style="text-decoration: none;color:cadetblue">SoloBoticas 1</a><br><a
                        href="#" style="text-decoration: none;color:cadetblue">SoloBoticas 2</a><br><a href="#"
                        style="text-decoration: none;color:cadetblue">SoloBoticas 3</a><br><a href="#"
                        style="text-decoration: none;color:cadetblue">SoloBoticas 4</a></p>
            </div>
        </main>
    </div>

    <footer class="footer">
        <p>Todos los derechos reservados a 小杨</p>
    </footer>
</body>

</html>
