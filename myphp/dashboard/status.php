<?php
include('db_connection.php');
include('session_manager.php');

// Verificar si el nickname es "giancarlovilch"
if ($_SESSION['nickname'] !== 'GIANCARLOVILCH') {
    echo "<script>window.location.href='dashboard.php';</script>";
    // header('Location: dashboard.php');
    exit();
}

$mensaje = ''; // Mensaje de éxito o error

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['estados'])) {
    try {
        $pdo->beginTransaction();
        foreach ($_POST['estados'] as $nickname => $estado) {
            $stmt = $pdo->prepare("UPDATE informacion_personal SET estado = :estado WHERE nickname = :nickname");
            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':nickname', $nickname);
            $stmt->execute();
        }
        $pdo->commit();
        $mensaje = "Estados actualizados correctamente.";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $mensaje = "Error al actualizar los estados: " . $e->getMessage();
    }
}

// Obtener la lista de usuarios con su estado
$stmt = $pdo->query("SELECT nickname, nombre_completo, estado FROM informacion_personal");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado de Usuarios</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="preload" href="/css/status.css" as="style">
    <link href="/css/status.css" rel="stylesheet" type="text/css" />
    <style>
        
    </style>
</head>
<body>
    <div class="container">
        <h2>Estado de Usuarios</h2>

        <?php if ($mensaje): ?>
            <div class="message <?php echo (strpos($mensaje, 'correctamente') !== false) ? 'success-message' : 'error-message'; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <table>
                <tr>
                    <th>Nombre Completo</th>
                    <th>Estado</th>                
                </tr>
                <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($usuario['nombre_completo']); ?></td>
                        <td>
                            <select name="estados[<?php echo htmlspecialchars($usuario['nickname']); ?>]">
                                <option value="Activo" <?php echo $usuario['estado'] == 'Activo' ? 'selected' : ''; ?>>Activo</option>
                                <option value="Pendiente" <?php echo $usuario['estado'] == 'Pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                                <option value="Retirado" <?php echo $usuario['estado'] == 'Retirado' ? 'selected' : ''; ?>>Retirado</option>
                            </select>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <button type="submit" class="save-btn">Guardar</button>
        </form>
    </div>
</body>
</html>
