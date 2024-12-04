<?php
// Incluir el archivo de conexión a la base de datos
include('db_connection.php'); 
try {
    // Intentar conectar a la base de datos
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Conexión exitosa a la base de datos $dbname en $host.";
} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
}
?>
