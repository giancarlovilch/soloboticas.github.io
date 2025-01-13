<?php
include('db_connection.php');
include('session_manager.php');

// Obtener el nickname del usuario logeado
$nickname = $_SESSION['nickname'];
$mensaje = ''; // Mensaje de éxito o error

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Procesar la actualización de la información

    // Recoger los datos del formulario
    $fechaNacimiento = $_POST['fecha_nacimiento'] ?? null;

    if (!empty($fechaNacimiento)) {
        try {
            // Verificar si ya tiene fecha de nacimiento registrada
            $stmt = $pdo->prepare("SELECT fecha_nacimiento FROM informacion_personal WHERE nickname = :nickname");
            $stmt->bindParam(':nickname', $nickname);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result && $result['fecha_nacimiento']) {
                $mensaje = "No puedes modificar tu fecha de nacimiento.";
            } else {
                // Actualizar la fecha de nacimiento
                $stmt = $pdo->prepare("
                    UPDATE informacion_personal
                    SET fecha_nacimiento = :fecha_nacimiento, fecha_actualizacion = NOW()
                    WHERE nickname = :nickname
                ");
                $stmt->bindParam(':fecha_nacimiento', $fechaNacimiento);
                $stmt->bindParam(':nickname', $nickname);
                $stmt->execute();

                $mensaje = "Fecha de nacimiento registrada correctamente.";
            }
        } catch (PDOException $e) {
            $mensaje = "Error al actualizar la fecha de nacimiento: " . $e->getMessage();
        }
    } else {
        $mensaje = "La fecha de nacimiento es obligatoria.";
    }
}

// Consultar la información actual del usuario
$stmt = $pdo->prepare("SELECT fecha_nacimiento FROM informacion_personal WHERE nickname = :nickname");
$stmt->bindParam(':nickname', $nickname);
$stmt->execute();
$userInfo = $stmt->fetch(PDO::FETCH_ASSOC);
$fechaNacimiento = $userInfo['fecha_nacimiento'] ?? null;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar Información</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: 'Krub', sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
        }

        .update-container {
            width: 350px;
            margin: 20px auto;
            background: #ffffff;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .update-container h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            color: #555;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .form-group input:focus {
            border-color: #007BFF;
            outline: none;
        }

        .btn {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        .message {
            text-align: center;
            margin-bottom: 20px;
        }

        .success-message {
            color: green;
        }

        .error-message {
            color: red;
        }

        input[readonly] {
            background-color: #f0f0f0;
            color: #888;
            cursor: not-allowed;
        }
    </style>
</head>

<body>
    <div class="update-container">
        <h2>Actualizar Fecha de Nacimiento</h2>

        <!-- Mensaje de éxito o error -->
        <?php if ($mensaje): ?>
            <div class="message <?php echo (strpos($mensaje, 'correctamente') !== false) ? 'success-message' : 'error-message'; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <!-- Formulario de fecha de nacimiento -->
        <form action="" method="POST">
            <div class="form-group">
                <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                <input 
                    type="date" 
                    id="fecha_nacimiento" 
                    name="fecha_nacimiento" 
                    value="<?php echo htmlspecialchars($fechaNacimiento); ?>" 
                    <?php echo $fechaNacimiento ? 'readonly' : 'required'; ?>>
            </div>
            <button type="submit" class="btn">Guardar</button>
        </form>
    </div>
</body>

</html>
