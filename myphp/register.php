<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario</title>
</head>
<body>
    <h2>Registro de Usuario</h2>
    <form action="AuthController.php" method="POST">
        <label for="nickname">Nombre de Usuario:</label>
        <input type="text" name="nickname" required><br>

        <label for="nombre">Nombre:</label>
        <input type="text" name="nombre" required><br>

        <label for="password">Contraseña:</label>
        <input type="password" name="password" required><br>

        <button type="submit" name="register">Registrar</button>
    </form>
</body>
</html>
