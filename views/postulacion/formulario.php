<?php
$basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
$today    = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Postulación | Solo Boticas</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/home.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/formulario.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Signika:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<?php require_once __DIR__ . '/../partials/navbar.php'; ?>

<section class="form-hero">
    <div class="form-hero-overlay">
        <div class="contenedor form-hero-content">
            <p class="form-kicker">Reclutamiento</p>
            <h1>Formulario de postulación</h1>
            <p class="form-hero-text">
                Completa tu información con cuidado. Será usada para evaluar tu perfil en Solo Boticas.
            </p>
        </div>
    </div>
</section>

<main class="form-main">
    <section class="contenedor form-layout">

        <!-- Banner de estado/etapa -->
        <div id="stageBanner" class="stage-banner" hidden></div>

        <!-- Alerta de estado general -->
        <div id="statusBox" class="status-banner" role="status" aria-live="polite" hidden></div>

        <form id="postulacionForm" class="postulacion-form" novalidate>

            <!-- ── SECCIÓN: FOTO ─────────────────────────────────── -->
            <section class="form-section form-section-foto">
                <div class="foto-layout">
                    <div class="foto-preview-wrap">
                        <div class="foto-preview" id="fotoPreview">
                            <span class="foto-placeholder">Sin foto</span>
                        </div>
                        <label for="fotoInput" class="foto-btn" id="fotoBtnLabel">
                            Subir foto
                            <input type="file" id="fotoInput" accept="image/jpeg,image/png,image/webp"
                                   style="display:none;">
                        </label>
                        <p class="foto-hint">JPG, JPEG o PNG · Cualquier tamaño, se comprime automáticamente a 400×400 px</p>
                    </div>
                    <div class="foto-info">
                        <div class="form-section-header">
                            <p class="section-kicker">Identificación</p>
                            <h2>Tu foto de perfil</h2>
                        </div>
                        <p>La foto es opcional pero ayuda al equipo a reconocerte durante el proceso de selección. Usa una foto de frente, con buena iluminación.</p>
                    </div>
                </div>
            </section>

            <!-- ── SECCIÓN 1: DATOS PERSONALES ───────────────────── -->
            <section class="form-section">
                <div class="form-section-header">
                    <p class="section-kicker">Sección 1</p>
                    <h2>Datos personales</h2>
                </div>

                <div class="form-grid form-grid-2">
                    <div class="input-group">
                        <label for="nombres">Nombres <span class="req">*</span></label>
                        <input type="text" id="nombres" name="nombres" autocomplete="given-name">
                    </div>

                    <div class="input-group">
                        <label for="apellidos">Apellidos</label>
                        <input type="text" id="apellidos" name="apellidos" autocomplete="family-name">
                    </div>

                    <div class="input-group">
                        <label for="genero_id">Género</label>
                        <select id="genero_id" name="genero_id">
                            <option value="">Seleccione</option>
                        </select>
                    </div>

                    <div class="input-group">
                        <label for="fecha_nacimiento">Fecha de nacimiento <span class="req">*</span></label>
                        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" max="<?= $today ?>">
                    </div>

                    <div class="input-group">
                        <label for="email">Correo electrónico</label>
                        <input type="email" id="email" name="email" autocomplete="email">
                    </div>

                    <div class="input-group">
                        <label for="telefono">Teléfono</label>
                        <input type="text" id="telefono" name="telefono" inputmode="numeric" maxlength="15">
                    </div>

                    <div class="input-group form-grid-full">
                        <label for="situacion_vivienda_id">Situación de vivienda</label>
                        <select id="situacion_vivienda_id" name="situacion_vivienda_id">
                            <option value="">Seleccione</option>
                        </select>
                    </div>
                </div>
            </section>

            <!-- ── SECCIÓN 2: HABILIDADES ─────────────────────────── -->
            <section class="form-section">
                <div class="form-section-header with-action">
                    <div>
                        <p class="section-kicker">Sección 2</p>
                        <h2>Habilidades</h2>
                    </div>
                    <button type="button" id="addSkillBtn" class="btn-outline">+ Agregar</button>
                </div>

                <div id="skillsContainer" class="dynamic-stack">
                    <div class="skill-item dynamic-card">
                        <div class="form-grid form-grid-2">
                            <div class="input-group">
                                <label>Habilidad</label>
                                <select name="skill_id[]"><option value="">Seleccione</option></select>
                            </div>
                            <div class="input-group">
                                <label>Nivel</label>
                                <select name="nivel_id[]"><option value="">Seleccione</option></select>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ── SECCIÓN 3: ESTUDIOS ─────────────────────────────── -->
            <section class="form-section">
                <div class="form-section-header">
                    <p class="section-kicker">Sección 3</p>
                    <h2>Formación académica</h2>
                </div>

                <div class="form-grid form-grid-2">
                    <div class="input-group">
                        <label for="institucion_id">Institución <span class="req">*</span></label>
                        <select id="institucion_id" name="institucion_id">
                            <option value="">Seleccione</option>
                        </select>
                    </div>

                    <div class="input-group">
                        <label for="tipo_estudio_id">Tipo de estudio <span class="req">*</span></label>
                        <select id="tipo_estudio_id" name="tipo_estudio_id">
                            <option value="">Seleccione</option>
                        </select>
                    </div>

                    <div class="input-group">
                        <label for="estado_id">Estado <span class="req">*</span></label>
                        <select id="estado_id" name="estado_id">
                            <option value="">Seleccione</option>
                        </select>
                    </div>

                    <div class="input-group">
                        <label for="fecha_inicio">Fecha inicio <span class="req">*</span></label>
                        <input type="date" id="fecha_inicio" name="fecha_inicio" max="<?= $today ?>">
                    </div>

                    <div class="input-group">
                        <label for="fecha_fin">Fecha fin <small>(si egresaste)</small></label>
                        <input type="date" id="fecha_fin" name="fecha_fin" max="<?= $today ?>">
                    </div>
                </div>
            </section>

            <!-- ── SECCIÓN 4: EXPERIENCIA ─────────────────────────── -->
            <section class="form-section">
                <div class="form-section-header with-action">
                    <div>
                        <p class="section-kicker">Sección 4</p>
                        <h2>Experiencia laboral</h2>
                    </div>
                    <button type="button" id="addExperienciaBtn" class="btn-outline">+ Agregar</button>
                </div>

                <div id="experienciasContainer" class="dynamic-stack">
                    <div class="experiencia-item dynamic-card">
                        <div class="form-grid form-grid-2">
                            <div class="input-group">
                                <label>Empresa</label>
                                <input type="text" name="empresa[]">
                            </div>
                            <div class="input-group">
                                <label>Cargo</label>
                                <input type="text" name="cargo[]">
                            </div>
                            <div class="input-group">
                                <label>Fecha inicio</label>
                                <input type="date" name="exp_fecha_inicio[]" max="<?= $today ?>">
                            </div>
                            <div class="input-group">
                                <label>Fecha fin</label>
                                <input type="date" name="exp_fecha_fin[]" max="<?= $today ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ── SECCIÓN 5: POSTULACIÓN ─────────────────────────── -->
            <section class="form-section">
                <div class="form-section-header">
                    <p class="section-kicker">Sección 5</p>
                    <h2>Postulación</h2>
                </div>

                <div class="form-grid form-grid-2">
                    <div class="input-group">
                        <label for="puesto_id">Puesto al que aplica <span class="req">*</span></label>
                        <select id="puesto_id" name="puesto_id">
                            <option value="">Seleccione</option>
                        </select>
                    </div>

                    <div class="input-group">
                        <label for="turno_id">Turno preferido <span class="req">*</span></label>
                        <select id="turno_id" name="turno_id">
                            <option value="">Seleccione</option>
                        </select>
                    </div>
                </div>
            </section>

            <!-- ── VERIFICACIÓN ANTI-SPAM ─────────────────────────── -->
            <section class="form-section">
                <div class="form-grid form-grid-2">
                    <div class="input-group">
                        <label for="captcha_answer" id="captchaLabel">Verificación: cargando...</label>
                        <input type="text" id="captcha_answer" name="captcha_answer" inputmode="numeric"
                               autocomplete="off" placeholder="Escribe el resultado">
                        <small class="input-help">Resuelve la operación antes de enviar tu postulación.</small>
                    </div>
                </div>
            </section>

            <div class="form-actions">
                <button type="submit" id="submitBtn" class="btn-submit">
                    <span id="submitText">Enviar postulación</span>
                    <span id="submitSpinner" hidden>Enviando...</span>
                </button>
            </div>

        </form>

    </section>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

<script src="<?= $basePath ?>/assets/js/home.js"></script>
<script src="<?= $basePath ?>/assets/js/postulacion-formulario.js"></script>
</body>
</html>
