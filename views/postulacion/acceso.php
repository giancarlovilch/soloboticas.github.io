<?php
$basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trabaja con nosotros | Solo Boticas</title>

    <link rel="stylesheet" href="<?= htmlspecialchars($basePath . '/assets/css/normalize.css', ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($basePath . '/assets/css/home.css', ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($basePath . '/assets/css/acceso.css', ENT_QUOTES, 'UTF-8') ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Signika:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>

    <?php require_once __DIR__ . '/../partials/navbar.php'; ?>

    <section class="acceso-hero">
        <div class="acceso-hero-overlay">
            <div class="contenedor acceso-hero-content">
                <p class="acceso-kicker">Oportunidades laborales</p>
                <h1>Trabaja con nosotros</h1>
                <p class="acceso-hero-text">
                    En Solo Boticas buscamos personas con vocación de servicio, responsabilidad y actitud proactiva
                    para seguir creciendo junto a nuestro equipo.
                </p>
            </div>
        </div>
    </section>

    <main class="acceso-main">
        <section class="contenedor acceso-layout">

            <div class="vacantes-panel">
                <div class="section-heading acceso-heading">
                    <p class="section-kicker">Posiciones disponibles</p>
                    <h2>Oportunidades que podrías encontrar en Solo Boticas</h2>
                    <p>
                        Estas posiciones son referenciales y te muestran el tipo de perfiles que valoramos
                        dentro de nuestra organización.
                    </p>
                </div>

                <div class="vacantes-grid">
                    <article class="vacante-card">
                        <span class="vacante-tag">Salud y Bienestar</span>
                        <h3>Especialista en Atención Farmacéutica</h3>
                        <p>
                            Brinda orientación experta en salud, gestión de dispensación y cuidado directo al paciente
                            con los más altos estándares éticos.
                        </p>
                    </article>

                    <article class="vacante-card">
                        <span class="vacante-tag">Servicios Financieros</span>
                        <h3>Anfitrión(a) de Caja y Agente BCP</h3>
                        <p>
                            Gestión eficiente de transacciones, recaudación y atención personalizada de servicios
                            bancarios integrados en nuestra sede.
                        </p>
                    </article>

                    <article class="vacante-card">
                        <span class="vacante-tag">Administración</span>
                        <h3>Coordinador(a) de Gestión Operativa</h3>
                        <p>
                            Soporte estratégico en procesos administrativos, documentación y flujo de información
                            para asegurar la excelencia del local.
                        </p>
                    </article>

                    <article class="vacante-card">
                        <span class="vacante-tag">Logística</span>
                        <h3>Gestor(a) de Inventario y Almacén</h3>
                        <p>
                            Control riguroso de stock, recepción de productos de salud y optimización de la
                            cadena de suministro interna.
                        </p>
                    </article>
                </div>
            </div>

            <aside class="acceso-sidebar">
                <div class="acceso-card acceso-card-form">
                    <div class="acceso-card-header">
                        <p class="section-kicker">Acceso a postulación</p>
                        <h2>Ingresa tus datos</h2>
                        <p>
                            Ingresa tu DNI y la clave de acceso para continuar. Si ya existe una solicitud enviada,
                            validaremos tu fecha de nacimiento para mostrar tu información.
                        </p>
                    </div>

                    <form id="accessForm" class="access-form" novalidate>
                        <div class="input-group">
                            <label for="num_documento">DNI</label>
                            <input
                                type="text"
                                id="num_documento"
                                name="num_documento"
                                maxlength="8"
                                inputmode="numeric"
                                autocomplete="off"
                                placeholder="Ingresa tu DNI"
                                required>
                            <small class="input-help">Debe contener 8 dígitos.</small>
                        </div>

                        <div class="input-group">
                            <label for="access_key">Clave de acceso</label>
                            <input
                                type="text"
                                id="access_key"
                                name="access_key"
                                autocomplete="off"
                                placeholder="Ingresa la clave entregada en botica"
                                required>
                            <small class="input-help">Solicítala personalmente en cualquiera de nuestros locales.</small>
                        </div>

                        <div id="birthDateContainer" class="input-group input-group-hidden" aria-hidden="true">
                            <label for="fecha_nacimiento">Fecha de nacimiento</label>
                            <input
                                type="date"
                                id="fecha_nacimiento"
                                name="fecha_nacimiento"
                                max="<?= date('Y-m-d') ?>">
                            <small class="input-help">Usa la misma fecha registrada en tu solicitud.</small>
                        </div>

                        <button type="submit" id="submitBtn" class="boton-acceso">
                            <span id="submitBtnText">Continuar</span>
                        </button>
                    </form>

                    <div id="messageBox" class="message-box" role="status" aria-live="polite" style="display:none;"></div>
                </div>

                <div class="acceso-card acceso-card-info">
                    <h3>Antes de empezar</h3>
                    <ul class="check-list">
                        <li>Ten tu DNI a la mano.</li>
                        <li>Verifica que tus datos estén correctos antes de enviar.</li>
                        <li>Si ya enviaste tu solicitud, el sistema la mostrará en modo lectura.</li>
                    </ul>
                </div>
            </aside>

        </section>
    </main>

    <?php require_once __DIR__ . '/../partials/footer.php'; ?>

    <script src="<?= htmlspecialchars($basePath . '/assets/js/home.js', ENT_QUOTES, 'UTF-8') ?>"></script>
    <script src="<?= htmlspecialchars($basePath . '/assets/js/acceso.js', ENT_QUOTES, 'UTF-8') ?>"></script>
    <script src="<?= htmlspecialchars($basePath . '/assets/js/postulacion-acceso.js', ENT_QUOTES, 'UTF-8') ?>"></script>
</body>

</html>