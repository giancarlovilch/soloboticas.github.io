<?php
include('db_connection.php');
include('session_manager.php');

// Obtener el nickname del usuario logeado
$nickname = $_SESSION['nickname'];
$mensaje = ''; // Mensaje de éxito o error

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Procesar la actualización de la información

    // Recoger los datos del formulario
    $nombreCompleto = $_POST['nombre_completo'] ?? null;
    $correo = $_POST['correo'] ?? null;
    $telefono = $_POST['telefono'] ?? null;
    $fechaNacimiento = $_POST['fecha_nacimiento'] ?? null;
    $dni = $_POST['dni'] ?? null;
    $rol = $_POST['rol'] ?? null;

    // Validar el DNI
    if (!empty($dni) && !preg_match('/^\d{8}$/', $dni)) {
        $mensaje = "El DNI debe tener exactamente 8 dígitos.";
    } elseif (!empty($nombreCompleto) || !empty($correo) || !empty($telefono) || !empty($fechaNacimiento) || !empty($dni) || !empty($rol)) {
        try {
            // Verificar si ya tiene la información registrada
            $stmt = $pdo->prepare("SELECT nombre_completo, correo, telefono, fecha_nacimiento, dni, rol FROM informacion_personal WHERE nickname = :nickname");
            $stmt->bindParam(':nickname', $nickname);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            // Actualizar la información
            if ($result) {
                // Si alguno de los datos es null o vacío, permitir la actualización
                if (empty($result['nombre_completo']) && !empty($nombreCompleto)) {
                    $stmt = $pdo->prepare("UPDATE informacion_personal SET nombre_completo = :nombre_completo, fecha_actualizacion = NOW() WHERE nickname = :nickname");
                    $stmt->bindParam(':nombre_completo', $nombreCompleto);
                    $stmt->bindParam(':nickname', $nickname);
                    $stmt->execute();
                }

                if (empty($result['correo']) && !empty($correo)) {
                    $stmt = $pdo->prepare("UPDATE informacion_personal SET correo = :correo, fecha_actualizacion = NOW() WHERE nickname = :nickname");
                    $stmt->bindParam(':correo', $correo);
                    $stmt->bindParam(':nickname', $nickname);
                    $stmt->execute();
                }

                if (empty($result['telefono']) && !empty($telefono)) {
                    $stmt = $pdo->prepare("UPDATE informacion_personal SET telefono = :telefono, fecha_actualizacion = NOW() WHERE nickname = :nickname");
                    $stmt->bindParam(':telefono', $telefono);
                    $stmt->bindParam(':nickname', $nickname);
                    $stmt->execute();
                }

                if (empty($result['fecha_nacimiento']) && !empty($fechaNacimiento)) {
                    $stmt = $pdo->prepare("UPDATE informacion_personal SET fecha_nacimiento = :fecha_nacimiento, fecha_actualizacion = NOW() WHERE nickname = :nickname");
                    $stmt->bindParam(':fecha_nacimiento', $fechaNacimiento);
                    $stmt->bindParam(':nickname', $nickname);
                    $stmt->execute();
                }

                if (empty($result['dni']) && !empty($dni)) {
                    $stmt = $pdo->prepare("UPDATE informacion_personal SET dni = :dni, fecha_actualizacion = NOW() WHERE nickname = :nickname");
                    $stmt->bindParam(':dni', $dni);
                    $stmt->bindParam(':nickname', $nickname);
                    $stmt->execute();
                }

                if (empty($result['rol']) && !empty($rol)) {
                    $stmt = $pdo->prepare("UPDATE informacion_personal SET rol = :rol, fecha_actualizacion = NOW() WHERE nickname = :nickname");
                    $stmt->bindParam(':rol', $rol);
                    $stmt->bindParam(':nickname', $nickname);
                    $stmt->execute();
                }

                $mensaje = "Información actualizada correctamente.";
            } else {
                $mensaje = "No se encontró el usuario.";
            }
        } catch (PDOException $e) {
            $mensaje = "Error al actualizar la información: " . $e->getMessage();
        }
    } else {
        $mensaje = "Debe proporcionar al menos un dato para actualizar.";
    }
}

// Consultar la información actual del usuario
$stmt = $pdo->prepare("SELECT nombre_completo, correo, telefono, fecha_nacimiento, dni, rol FROM informacion_personal WHERE nickname = :nickname");
$stmt->bindParam(':nickname', $nickname);
$stmt->execute();
$userInfo = $stmt->fetch(PDO::FETCH_ASSOC);
$nombreCompleto = $userInfo['nombre_completo'] ?? null;
$correo = $userInfo['correo'] ?? null;
$telefono = $userInfo['telefono'] ?? null;
$fechaNacimiento = $userInfo['fecha_nacimiento'] ?? null;
$dni = $userInfo['dni'] ?? null;
$rol = $userInfo['rol'] ?? null;
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
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .update-container {
            background-color: white;
            padding: 20px;            
            width: 600px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #333;
            font-size: 24px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            font-size: 14px;
            color: #555;
            display: block;
            margin-bottom: 5px;
        }

        input[type="text"], input[type="email"], input[type="date"], select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
            color: #333;
        }

        button[type="submit"] {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }

        button[type="submit"]:hover {
            background-color: #218838;
        }

        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
        }

        input[readonly] {
            background-color: #e9ecef;
            cursor: not-allowed;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .update-container {
                width: 350px;
            }
        }
    </style>
</head>
<body>
    <div class="update-container">
        <h2>Actualizar Información</h2>

        <!-- Mensaje de éxito o error -->
        <?php if ($mensaje): ?>
            <div class="message <?php echo (strpos($mensaje, 'correctamente') !== false) ? 'success-message' : 'error-message'; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <!-- Formulario para actualizar la información -->
        <form action="" method="POST">
            <div class="form-group">
                <label for="nombre_completo">Nombre Completo</label>
                <input type="text" id="nombre_completo" name="nombre_completo" value="<?php echo htmlspecialchars($nombreCompleto); ?>" <?php echo $nombreCompleto ? 'readonly' : 'required'; ?>>
            </div>
            <div class="form-group">
                <label for="correo">Correo</label>
                <input type="email" id="correo" name="correo" value="<?php echo htmlspecialchars($correo); ?>" <?php echo $correo ? 'readonly' : 'required'; ?>>
            </div>
            <div class="form-group">
                <label for="telefono">Teléfono</label>
                <input type="text" id="telefono" name="telefono" value="<?php echo htmlspecialchars($telefono); ?>" <?php echo $telefono ? 'readonly' : 'required'; ?>>
            </div>
            <div class="form-group">
                <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="<?php echo htmlspecialchars($fechaNacimiento); ?>" <?php echo $fechaNacimiento ? 'readonly' : 'required'; ?>>
            </div>
            <div class="form-group">
                <label for="dni">DNI</label>
                <input type="text" id="dni" name="dni" value="<?php echo htmlspecialchars($dni); ?>" <?php echo $dni ? 'readonly' : 'required'; ?>>
            </div>
            
            <div class="form-group">
                <label for="rol">Rol</label>
                <select id="rol" name="rol" <?php echo $rol ? 'disabled' : 'required'; ?>>
                    <option value="Administrador" <?php echo $rol == 'Administrador' ? 'selected' : ''; ?>>Administrador</option>
                    <option value="Usuario" <?php echo $rol == 'Usuario' ? 'selected' : ''; ?>>Usuario</option>
                </select>
            </div>
            <button type="submit">Guardar</button>
        </form>
    </div>
</body>
</html>