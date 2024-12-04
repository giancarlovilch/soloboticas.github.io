<?php
// $host = 'localhost'; // o la dirección de tu servidor
// $dbname = 'soloboticas';
// $username = 'root'; // tu usuario de base de datos
// $password = ''; // tu contraseña de base de datos



$host = 'serv502242'; // o la dirección de tu servidor
$dbname = 'soloboticas';
$username = 'giancarlovilch'; // tu usuario de base de datos
$password = '@a20102552A@'; // tu contraseña de base de datos


//PARAMETROS PARA LA BASE DE DATOS

// define("DB_HOST", "serv502242");
// define("DB_NAME", "soloboticas");
// define("DB_USER", "giancarlovilch");
// define("DB_PASS", "@a20102552A@");


try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
}
?>
