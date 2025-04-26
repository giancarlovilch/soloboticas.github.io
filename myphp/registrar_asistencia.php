<?php
session_start();
include('db_connection.php');
include('session_manager.php');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fecha = $_POST['fecha'];
    $horaEntrada = $_POST['hora_entrada'];
    $horaSalida = $_POST['hora_salida'];
    $tardanza = isset($_POST['tardanza']) ? intval($_POST['tardanza']) : 0;
    $nickname = $_SESSION['nickname'];

    // Validar que no sea fecha futura
    $hoy = new DateTime();
    $fechaRegistro = new DateTime($fecha);

    if ($fechaRegistro > $hoy) {
        $response['message'] = "No puedes registrar asistencias para fechas futuras.";
        echo json_encode($response);
        exit;
    }

    // Validar que no exista registro
    $stmt = $pdo->prepare("SELECT id FROM registros_asistencia WHERE nickname = ? AND fecha = ?");
    $stmt->execute([$nickname, $fecha]);

    if ($stmt->fetch()) {
        $response['message'] = "Ya existe un registro para esta fecha.";
        echo json_encode($response);
        exit;
    }

    // Validar tardanza
    if ($tardanza < 0) {
        $response['message'] = "La tardanza no puede ser negativa.";
        echo json_encode($response);
        exit;
    }

    // Insertar registro
    $stmt = $pdo->prepare("INSERT INTO registros_asistencia 
                      (nickname, fecha, hora_entrada, hora_salida, tardanza_minutos, estado, fecha_registro) 
                      VALUES (?, ?, ?, ?, ?, 'Pendiente', NOW())");
    $stmt->execute([$nickname, $fecha, $horaEntrada, $horaSalida, $tardanza]);

    $response['success'] = true;
    $response['message'] = "Asistencia registrada correctamente.";
}

echo json_encode($response);
