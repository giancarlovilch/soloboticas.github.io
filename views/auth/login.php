<?php $basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : ''; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Intranet | Solo Boticas</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/normalize.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/login.css">
    <link rel="icon" type="image/x-icon" href="<?= $basePath ?>/assets/img/logo.ico">
</head>
<body>

    <div class="login-wrapper">

        <div class="login-card">

            <!-- Branding -->
            <div class="login-brand">
                <div class="login-brand__logo">SB</div>
                <div class="login-brand__text">
                    <span class="login-brand__company">Grupo KGyR S.A.C</span>
                    <span class="login-brand__app">Solo Boticas <small>Intranet</small></span>
                </div>
            </div>

            <h2 class="login-title">Iniciar Sesión</h2>
            <p class="login-subtitle">Ingresa tus credenciales de trabajador</p>

            <!-- Mensaje de error / éxito -->
            <div id="loginAlert" class="login-alert" role="alert" aria-live="polite"></div>

            <form id="loginForm" novalidate>
                <div class="login-field">
                    <label for="loginUsername" class="login-field__label">Usuario</label>
                    <input
                        type="text"
                        id="loginUsername"
                        name="username"
                        class="login-field__input"
                        placeholder="Tu nombre de usuario"
                        autocomplete="username"
                        required
                    >
                </div>

                <div class="login-field">
                    <label for="loginPassword" class="login-field__label">Contraseña</label>
                    <div class="login-field__wrap">
                        <input
                            type="password"
                            id="loginPassword"
                            name="password"
                            class="login-field__input"
                            placeholder="Tu contraseña"
                            autocomplete="current-password"
                            required
                        >
                        <button type="button" class="login-field__toggle" id="togglePassword" aria-label="Mostrar contraseña">
                            <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="login-btn" id="loginBtn">
                    <span id="loginBtnText">Ingresar</span>
                    <span id="loginBtnSpinner" class="login-btn__spinner" hidden>
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="spin"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                        Verificando...
                    </span>
                </button>
            </form>

            <p class="login-help">
                ¿Olvidaste tu contraseña? Contacta al administrador del local.
            </p>

        </div>

        <a href="<?= $basePath ?>/" class="login-back">← Volver a la web principal</a>

    </div>

    <script src="<?= $basePath ?>/assets/js/auth-logic.js"></script>
</body>
</html>
