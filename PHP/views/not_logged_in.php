<?php
// show potential errors / feedback (from login object)
if (isset($login)) {
    if ($login->errors) {
        foreach ($login->errors as $error) {
            echo $error;
        }
    }
    if ($login->messages) {
        foreach ($login->messages as $message) {
            echo $message;
        }
    }
}
?>

<!-- login form box -->
<form method="post" action="index.php" name="loginform">

    <label for="login_input_username">Usuario o Email: </label>
    <input id="login_input_username" class="login_input" type="text" name="user_name" required />
    <br>
    <label for="login_input_password">Contraseña: </label>
    <input id="login_input_password" class="login_input" type="password" name="user_password" autocomplete="off" required />

    <input type="submit"  name="login" value="ENTRAR" />

</form>

<a href="register.php">Register new account</a>
