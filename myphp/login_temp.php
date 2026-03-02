<?php
session_start();
include('db_connection.php');

$error = "";

// Procesar login
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nickname = trim($_POST['nickname']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE nickname = :nickname");
    $stmt->bindParam(':nickname', $nickname);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {

        $_SESSION['nickname'] = $user['nickname'];
        $_SESSION['last_activity'] = time();

        header("Location: dashboard.php");
        exit();

    } else {
        $error = "Credenciales incorrectas";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login Temporal</title>
</head>
<body>

<h2>Login Temporal</h2>

<?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>

<form method="post">
    <input type="text" name="nickname" placeholder="Usuario" required>
    <input type="password" name="password" placeholder="Contraseña" required>
    <button type="submit">Entrar</button>
</form>

</body>
</html>