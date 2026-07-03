<?php
$basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';

// Fijo a la URL pública real (no la de desarrollo local), para que el QR/enlace
// compartido siempre apunte al sitio en producción sin importar dónde se vea esta página.
$shareUrl = 'https://www.soloboticas.com/public/postulacion/acceso';
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

        <section class="contenedor acceso-access-section">
            <div class="acceso-access-grid">
                <div class="acceso-card acceso-card-form">
                    <div class="acceso-card-header">
                        <p class="section-kicker">Acceso a postulación</p>
                        <h2>Ingresa tus datos</h2>
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
                            <label for="captcha_answer" id="captchaLabel">Verificación: cargando...</label>
                            <input
                                type="text"
                                id="captcha_answer"
                                name="captcha_answer"
                                inputmode="numeric"
                                autocomplete="off"
                                placeholder="Escribe el resultado"
                                required>
                            <small class="input-help">Resuelve la operación para confirmar que no eres un robot.</small>
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
            </div>
        </section>

        <section class="contenedor vacantes-panel">
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
        </section>

        <section class="contenedor acceso-share-section">
            <div class="acceso-share-card">
                <div class="acceso-share-text">
                    <p class="section-kicker">Comparte esta postulación</p>
                    <h2>¿Conoces a alguien que busque trabajo?</h2>
                    <p>
                        Comparte este enlace o el código QR para que cualquier persona pueda entrar
                        directamente a postular, desde su celular o computadora.
                    </p>
                    <a
                        class="boton-acceso acceso-share-btn"
                        id="shareWhatsappBtn"
                        href="https://wa.me/?text=<?= urlencode('¡Postula con nosotros en Solo Boticas! Ingresa tu DNI aquí: ' . $shareUrl) ?>"
                        target="_blank"
                        rel="noopener noreferrer">
                        Compartir por WhatsApp
                    </a>
                </div>

                <div class="acceso-share-qr">
                    <img
                        src="<?= htmlspecialchars('https://api.qrserver.com/v1/create-qr-code/?size=240x240&margin=10&data=' . urlencode($shareUrl), ENT_QUOTES, 'UTF-8') ?>"
                        alt="Código QR para postular en Solo Boticas"
                        width="240" height="240" loading="lazy">
                    <p class="acceso-share-url"><?= htmlspecialchars($shareUrl, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            </div>
        </section>

    </main>

    <!-- Aviso rápido: cómo postular -->
    <div id="tipModal" class="tip-modal" hidden>
        <div class="tip-modal-backdrop" id="tipModalBackdrop"></div>
        <div class="tip-modal-box" role="dialog" aria-modal="true" aria-labelledby="tipModalTitle">
            <button type="button" class="tip-modal-close" id="tipModalClose" aria-label="Cerrar">✕</button>
            <p class="tip-modal-emoji">👋</p>
            <h3 id="tipModalTitle">¿Quieres postular con nosotros?</h3>
            <p>
                Ingresa tu <strong>DNI</strong> en el cuadro <strong>“Acceso a postulación”</strong> y sigue
                los pasos para completar tu solicitud.
            </p>
            <button type="button" class="boton-acceso tip-modal-btn" id="tipModalOk">Entendido</button>
        </div>
    </div>

    <?php require_once __DIR__ . '/../partials/footer.php'; ?>

    <script src="<?= htmlspecialchars($basePath . '/assets/js/home.js', ENT_QUOTES, 'UTF-8') ?>"></script>
    <script src="<?= htmlspecialchars($basePath . '/assets/js/acceso.js', ENT_QUOTES, 'UTF-8') ?>"></script>
    <script src="<?= htmlspecialchars($basePath . '/assets/js/postulacion-acceso.js', ENT_QUOTES, 'UTF-8') ?>"></script>
</body>

</html>