<?php
$basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
?>
<footer class="footer">
    <div class="contenedor footer-grid">
        <div>
            <h3>Solo Boticas</h3>
            <p>
                En Solo Boticas, nuestra prioridad es tu bienestar. Garantizamos soluciones de salud integrales con un amplio stock de medicamentos y un equipo farmacéutico comprometido con la excelencia, la ética y el cuidado preventivo de nuestra comunidad.
            </p>
        </div>

        <div>
            <h3>Enlaces Rápidos</h3>
            <ul class="footer-links" style="list-style: none; padding: 0;">
                <li><a href="<?= htmlspecialchars($basePath . '/', ENT_QUOTES, 'UTF-8') ?>">Inicio</a></li>
                <li><a href="<?= htmlspecialchars($basePath . '/postulacion/acceso', ENT_QUOTES, 'UTF-8') ?>">Reclutamiento</a></li>
                <li><a href="<?= htmlspecialchars($basePath . '/login', ENT_QUOTES, 'UTF-8') ?>">Intranet</a></li>
            </ul>
        </div>

        <div>
            <h3>Contacto</h3>
            <p>SJL, Lima, Perú</p>
            <p>Email: contacto@soloboticas.com</p>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="contenedor">
            <p>© Solo Boticas - Todos los derechos reservados</p>
        </div>
    </div>
</footer>