<?php
$basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
$nombreUsuario = $_SESSION['user_name'] ?? 'Usuario';
$rolUsuario    = $_SESSION['user_rol'] ?? 'Invitado';
$page          = $_GET['page'] ?? 'home';

$navActive = [
    'home'        => ($page === 'home'),
    'postulantes' => ($page === 'postulantes' || $page === 'update'),
    'status'      => ($page === 'status'),
    'asistencias' => ($page === 'asistencias'),
    'economia'    => ($page === 'economia'),
    'bonos'       => ($page === 'bonos'),
];
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Solo Boticas</title>

    <!-- Fuentes y estilos base[cite: 10] -->
    <link href="https://fonts.googleapis.com/css2?family=Krub:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/dashboard.css">

    <?php if ($page === 'postulantes' || $page === 'status' || $page === 'update'): ?>
        <link rel="stylesheet" href="<?= $basePath ?>/assets/css/postulantes.css">
    <?php endif; ?>

    <link rel="icon" type="image/x-icon" href="<?= $basePath ?>/assets/img/logo.ico">
</head>

<body>
    <header class="main-header">
        <div class="header-logo">
            <div class="logo-circle">SB</div>
            <div class="logo-text">
                <span class="company-name">Grupo KGyR S.A.C</span>
                <span class="app-name">Solo Boticas <small>Intranet</small></span>
            </div>
        </div>

        <button class="nav-toggle" id="navToggle" onclick="toggleNav()" aria-label="Menú">
            <span></span><span></span><span></span>
        </button>

        <div class="header-user">
            <div class="user-details">
                <span class="user-name"><?= htmlspecialchars($nombreUsuario); ?></span>
                <span class="user-role"><?= htmlspecialchars($rolUsuario); ?></span>
            </div>
            <div class="user-avatar">
                <?= strtoupper(substr($nombreUsuario, 0, 1)); ?>
            </div>
        </div>
    </header>

    <div id="navOverlay" class="nav-overlay" onclick="toggleNav()"></div>

    <div class="contenedor contenedor-grid">
        <nav class="nav" id="sideNav">
            <ul class="list">
                <li class="list__item <?= $navActive['home'] ? 'list__item--active' : '' ?>">
                    <a href="<?= $basePath ?>/admin/dashboard" class="list__button">
                        <img src="<?= $basePath ?>/assets/img/icons/home.svg" class="list__img">
                        <span class="nav__link">Inicio</span>
                    </a>
                </li>

                <li class="list__item">
                    <a href="<?= $basePath ?>/horario" class="list__button">
                        <img src="<?= $basePath ?>/assets/img/icons/staff.svg" class="list__img">
                        <span class="nav__link">Horarios</span>
                    </a>
                </li>

                <p class="nav__section">Reclutamiento</p>

                <li class="list__item <?= $navActive['postulantes'] ? 'list__item--active' : '' ?>">
                    <a href="?page=postulantes" class="list__button">
                        <img src="<?= $basePath ?>/assets/img/icons/staff.svg" class="list__img">
                        <span class="nav__link">Postulantes</span>
                    </a>
                </li>

                <li class="list__item <?= $navActive['status'] ? 'list__item--active' : '' ?>">
                    <a href="?page=status" class="list__button">
                        <img src="<?= $basePath ?>/assets/img/icons/staff.svg" class="list__img">
                        <span class="nav__link">Accesos</span>
                    </a>
                </li>

                <p class="nav__section">Operaciones</p>

                <li class="list__item">
                    <a href="<?= $basePath ?>/caja" class="list__button">
                        <img src="<?= $basePath ?>/assets/img/icons/sales.svg" class="list__img">
                        <span class="nav__link">Caja</span>
                    </a>
                </li>

                <li class="list__item <?= $navActive['asistencias'] ? 'list__item--active' : '' ?>">
                    <a href="?page=asistencias" class="list__button">
                        <img src="<?= $basePath ?>/assets/img/icons/sales.svg" class="list__img">
                        <span class="nav__link">Asistencias</span>
                    </a>
                </li>

                <li class="list__item <?= $navActive['economia'] ? 'list__item--active' : '' ?>">
                    <a href="?page=economia" class="list__button">
                        <img src="<?= $basePath ?>/assets/img/icons/sales.svg" class="list__img">
                        <span class="nav__link">Economía</span>
                    </a>
                </li>

                <li class="list__item <?= $navActive['bonos'] ? 'list__item--active' : '' ?>">
                    <a href="?page=bonos" class="list__button">
                        <img src="<?= $basePath ?>/assets/img/icons/sales.svg" class="list__img">
                        <span class="nav__link">Bonos</span>
                    </a>
                </li>

                <li class="list__item">
                    <a href="<?= $basePath ?>/admin/reportes" class="list__button">
                        <img src="<?= $basePath ?>/assets/img/icons/sales.svg" class="list__img">
                        <span class="nav__link">Reportes</span>
                    </a>
                </li>

                <p class="nav__section">Sistema</p>

                <li class="list__item">
                    <a href="<?= $basePath ?>/admin/database" class="list__button">
                        <img src="<?= $basePath ?>/assets/img/icons/sales.svg" class="list__img">
                        <span class="nav__link">Base de Datos</span>
                    </a>
                </li>

                <li class="list__item--bottom">
                    <a href="<?= $basePath ?>/logout" class="list__button list__button--logout">
                        <img src="<?= $basePath ?>/assets/img/icons/logout.svg" class="list__img">
                        <span class="nav__link">Cerrar Sesión</span>
                    </a>
                </li>
            </ul>
        </nav>

        <aside class="sidebar-1">
            <div id="dashboard-content">
                <?php
                switch ($page) {
                    case 'postulantes':
                        require_once __DIR__ . '/postulantes_lista.php';
                        break;
                    case 'status':
                        require_once __DIR__ . '/status_lista.php';
                        break;
                    case 'asistencias':
                        require_once __DIR__ . '/asistencias_lista.php';
                        break;
                    case 'economia':
                        require_once __DIR__ . '/economia_lista.php';
                        break;
                    case 'bonos':
                        require_once __DIR__ . '/bonos_lista.php';
                        break;
                    case 'update':
                        if (isset($p) && !empty($p)) {
                            require_once __DIR__ . '/postulante_detalle.php';
                        } else {
                            echo '<div style="padding:1rem 1.5rem; color:#721c24; background:#f8d7da; border:1px solid #f5c6cb; border-radius:6px; margin:1.5rem;">';
                            echo '<strong>Error:</strong> No se encontró el postulante ID: ' . htmlspecialchars($_GET['id'] ?? 'N/A') . '.';
                            echo '</div>';
                        }
                        break;
                    default:
                        require_once __DIR__ . '/home.php';
                        break;
                }
                ?>
            </div>
        </aside>
    </div>

    <script>
const BASE = '<?= $basePath ?>';
function toggleNav() {
    const nav     = document.getElementById('sideNav');
    const overlay = document.getElementById('navOverlay');
    const open    = nav.classList.toggle('nav--open');
    overlay.classList.toggle('nav-overlay--visible', open);
    document.body.classList.toggle('nav-body-lock', open);
}
</script>
    <script src="<?= $basePath ?>/assets/js/session-guard.js"></script>
    <script src="<?= $basePath ?>/assets/js/dashboard.js"></script>

    <?php if ($page === 'postulantes' || $page === 'update'): ?>
        <script src="<?= $basePath ?>/assets/js/postulantes.js"></script>
    <?php endif; ?>

    <?php if ($page === 'status'): ?>
        <script src="<?= $basePath ?>/assets/js/status.js"></script>
        <script>document.addEventListener('DOMContentLoaded', cargarUsuariosStatus);</script>
    <?php endif; ?>

    <?php if ($page === 'asistencias'): ?>
        <script src="<?= $basePath ?>/assets/js/asistencias-admin.js"></script>
    <?php endif; ?>
</body>

</html>