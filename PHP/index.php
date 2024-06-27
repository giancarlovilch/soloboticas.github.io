<?php

// Se asegura de que el archivo especificado sea incluido solo una vez
require_once("config/db.php");
require_once("classes/Login.php");

// Crear Login
$login = new Login();

// Verificar si estas logeado
if ($login->isUserLoggedIn() == true) {
    include("views/logged_in.php");
} else {
    include("views/not_logged_in.php");
}
