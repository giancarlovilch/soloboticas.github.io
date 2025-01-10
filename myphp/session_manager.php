<?php
// session_manager.php

// Verificar si el usuario está logeado
if (!isset($_SESSION['nickname'])) {
    header('Location: login.php'); // Redirigir al login si no hay sesión activa
    exit();
}

// Tiempo de inactividad (en segundos)
$inactividad = 10;  // 60 segundos de inactividad para cerrar sesión

// Verificar si la última actividad fue hace más de $inactividad segundos
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $inactividad) {
    session_unset();  // Destruir las variables de sesión
    session_destroy();  // Destruir la sesión
    header('Location: login.php');  // Redirigir al login después del cierre de sesión
    exit();
}

// Actualizar la última actividad al momento actual
$_SESSION['last_activity'] = time();
?>
