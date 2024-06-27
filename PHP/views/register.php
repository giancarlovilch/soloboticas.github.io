<?php
// Muestra los Posibles Errores
if (isset($registration)) {
    if ($registration->errors) {
        foreach ($registration->errors as $error) {
            echo $error;
        }
    }
    if ($registration->messages) {
        foreach ($registration->messages as $message) {
            echo $message;
        }
    }
}
?>

<!-- Formato de Registro -->
<form method="post" action="register.php" name="registerform">

    <!-- the user name input field uses a HTML5 pattern check -->
    <label for="login_input_username">Nombre de usuario (solo letras y números, de 2 a 64 caracteres)</label>
    <input id="login_input_username" class="login_input" type="text" pattern="[a-zA-Z0-9]{2,64}" name="user_name" required />
    <br>
    <!-- the email input field uses a HTML5 email type check -->
    <label for="login_input_email">Correo Electrónico del Usuario</label>
    <input id="login_input_email" class="login_input" type="email" name="user_email" required />
    <br>
    <label for="login_input_password_new">Contraseña (Mínimo 6 Caracteres)</label>
    <input id="login_input_password_new" class="login_input" type="password" name="user_password_new" pattern=".{6,}" required autocomplete="off" />
    <br>
    <label for="login_input_password_repeat">Repita la Contraseña</label>
    <input id="login_input_password_repeat" class="login_input" type="password" name="user_password_repeat" pattern=".{6,}" required autocomplete="off" />
    <br>
    <input type="submit"  name="register" value="REGISTRAR" />

</form>

<!-- backlink -->
<a href="index.php">Volver a la Página de Inicio de Sesión</a>
