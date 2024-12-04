<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="preload" href="../css/login.css" as="style">
    <link href="../css/login.css" rel="stylesheet" type="text/css" />
    <link rel="icon" type="image/x-icon" href="../img/img_1/SB007.ico">
    <meta property="og:description" content="Salud y cuidado de familia a familia. ¡Bienvenidos a SoloBoticas!" />
    <title>GRUPO KGyR S.A.C</title>
</head>

<body>


    <div class="container" id="container">

        <!-- Formulario de Registro -->
        <div class="form-container sign-up">
            <!-- Mensaje de error para registro -->


            <!-- Mensaje de éxito -->


            <form action="AuthController.php" method="post">
                <h1>Crear Cuenta</h1>
                <br><br>                
                <input type="text" name="nickname" placeholder="Nombre de Usuario" required>
                <input type="password" name="password" placeholder="Contraseña" required>
                <input type="hidden" name="register" value="1"> <!-- Indica que es un registro -->
                <button type="submit">Registrarse</button>
            </form>
        </div>

        <!-- Formulario de Inicio de Sesión -->
        <div class="form-container sign-in">
            <!-- Mensajes de error -->
            <?php
            session_start(); // Inicia la sesión

            // Mostrar mensajes de éxito y error
            if (isset($_SESSION['success'])) {
                echo '<div class="registration-success alert">' . htmlspecialchars($_SESSION['success']) . '</div>';
                unset($_SESSION['success']); // Limpia el mensaje de éxito
            }
            if (isset($_SESSION['error'])) {
                echo '<div class="registration-fail alert">' . htmlspecialchars($_SESSION['error']) . '</div>';
                unset($_SESSION['error']); // Limpia el mensaje de error
            }
            ?>

            <!-- Mensaje de contraseña actualizada -->

            <form action="AuthController.php" method="post">
                <h1>Iniciar Sesión</h1>
                <br><br>
                <input type="text" name="nickname" placeholder="Nombre de Usuario" required>
                <input type="password" name="password" placeholder="Contraseña" required>
                <input type="hidden" name="login" value="1"> <!-- Indica que es un login -->
                <a href="#" onclick="openForgotPasswordModal()">¿Olvidaste tu contraseña?</a>


                <button type="submit">Iniciar Sesión</button>
            </form>
        </div>

        <div class="toggle-container">
            <div class="toggle">
                <div class="toggle-panel toggle-left">
                    <h1>¡Bienvenido!</h1>
                    <p>Aprovecha todas las funciones del sitio para ver los perfiles de los animales disponibles, leer sus historias y contactar con refugios. ¡Tu nuevo compañero te está esperando!</p>
                    <button class="hidden" id="login">Iniciar Sesión</button>
                </div>
                <div class="toggle-panel toggle-right">
                    <h1>¡Hola, familia SB!</h1><br>
                    <p>¡Bienvenido a nuestra nueva plataforma!<br>Por favor, ingrese sus credenciales SICAR.</p>                    
                    <!-- <button class="hidden" id="register">Registrarse</button> -->
                </div>
            </div>
        </div>
    </div>
    <div id="forgotPasswordModal" class="modal">
        <div class="modal-content">

            <h2>¿Olvidaste tu contraseña?</h2>
            <br>
            <p>Por favor, contacta a soporte. <br>Presiona "Aceptar" para continuar.</p>
            <br>
            <button onclick="closeForgotPasswordModal()">Aceptar</button>
        </div>
    </div>

    <div style="margin-top: 50px;">
        <a href="/" class="btn btn-secondary" style="font-size: 0.875rem;">Continuar como Invitado</a>
    </div>

    <script src="../js/login.js" type="text/javascript"></script>
</body>

</html>