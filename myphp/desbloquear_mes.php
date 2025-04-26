<?php
session_start();
include('db_connection.php');
include('session_manager.php');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mes = intval($_POST['mes']);
    $anio = intval($_POST['anio']);
    $nickname = $_SESSION['nickname'];

    // Eliminar el registro de bloqueo si existe
    $stmt = $pdo->prepare("DELETE FROM meses_bloqueados WHERE nickname = ? AND mes = ? AND anio = ?");
    $stmt->execute([$nickname, $mes, $anio]);

    $response['success'] = true;
    $response['message'] = 'Mes desbloqueado correctamente';
}

echo json_encode($response);
?>