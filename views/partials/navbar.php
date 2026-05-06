<?php
$basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
?>

<header class="site-header">
    <div class="topbar">
        <div class="contenedor topbar-content">
            <p class="topbar-text">Atención confiable en salud y bienestar para toda la familia</p>
            <a class="topbar-link" href="#">Contáctanos</a>
        </div>
    </div>

    <div class="brand-wrap contenedor">
        <div class="brand-block">
            <h1 class="brand-title">Solo Boticas</h1>
            <p class="brand-subtitle">Para su buena salud</p>
        </div>

        <div class="ayacucho-img"></div>

        <button class="menu-toggle" id="menuToggle" aria-label="Abrir menú" aria-expanded="false" aria-controls="mainNav">
            ☰
        </button>
    </div>

    <div class="nav-bg">
        <nav class="navegacion contenedor" id="mainNav" aria-label="Navegación principal">
            <a href="<?= htmlspecialchars($basePath . '/', ENT_QUOTES, 'UTF-8') ?>">Principal</a>
            
            <a href="<?= htmlspecialchars($basePath . '/postulacion/acceso', ENT_QUOTES, 'UTF-8') ?>">Trabaja con nosotros</a>
            
            <!-- Enlace actualizado para dirigir a la nueva interfaz de Login[cite: 15] -->
            <a href="<?= htmlspecialchars($basePath . '/login', ENT_QUOTES, 'UTF-8') ?>">Intranet</a>
        </nav>
    </div>
</header>