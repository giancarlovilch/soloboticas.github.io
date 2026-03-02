<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar login
if (!isset($_SESSION['nickname'])) {
    header("Location: login_temp.php");
    exit();
}

// Control de inactividad (5 minutos)
$timeout = 300;

if (isset($_SESSION['last_activity']) && 
    (time() - $_SESSION['last_activity']) > $timeout) {
    
    session_unset();
    session_destroy();
    header("Location: login_temp.php");
    exit();
}

$_SESSION['last_activity'] = time();