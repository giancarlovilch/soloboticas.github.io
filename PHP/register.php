<?php

// Se asegura de que el archivo especificado sea incluido solo una vez
require_once("config/db.php");
require_once("classes/Registration.php");

// Asignar la variable registro
$registration = new Registration();

// mostrar la ventana de registro
include("views/register.php");
