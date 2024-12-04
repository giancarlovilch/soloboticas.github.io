<?php
session_start();
include('db_connection.php');

// Verificar si el formulario es de registro
if (isset($_POST['register'])) {
    $nickname = $_POST['nickname'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hashear la contraseña    
    
    // Verificar si el nickname ya está en uso
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE nickname = :nickname");
    $stmt->bindParam(':nickname', $nickname);
    $stmt->execute();
    if ($stmt->rowCount() > 0) {        
        $_SESSION['error'] = "El nombre de usuario ya está en uso.";
        header("Location: login.php?registered=false");
    } else {
        // Insertar el nuevo usuario en la base de datos
        $sql = "INSERT INTO usuarios (nickname, password) VALUES (:nickname, :password)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nickname', $nickname);
        $stmt->bindParam(':password', $password);
        $stmt->execute();

        $_SESSION['success'] = "Usuario registrado exitosamente.";
        header("Location: login.php?registered=true");
    }
}

// Verificar si el formulario es de inicio de sesión
if (isset($_POST['login'])) {
    $nickname = $_POST['nickname'];
    $password = $_POST['password'];

    // Buscar el usuario por su nickname
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE nickname = :nickname");
    $stmt->bindParam(':nickname', $nickname);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Iniciar sesión si la contraseña es correcta
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nickname'] = $user['nickname'];

        // Redirigir al usuario al área protegida
        header('Location: dashboard.php');
        exit();
    } else {
        $_SESSION['error'] = "Nickname o contraseña incorrectos.";
        header("Location: login.php?registered=false");  
    }
}
?>
