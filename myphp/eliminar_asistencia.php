<?php
session_start();
include('db_connection.php');
include('session_manager.php');

if (!isset($_SESSION['nickname'])) {
    header("Location: login.php");
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    // Verificar que el registro pertenece al usuario
    $stmt = $pdo->prepare("SELECT id FROM registros_asistencia WHERE id = ? AND nickname = ?");
    $stmt->execute([$id, $_SESSION['nickname']]);
    
    if ($stmt->fetch()) {
        $delete = $pdo->prepare("DELETE FROM registros_asistencia WHERE id = ?");
        $delete->execute([$id]);
        $_SESSION['exito'] = "Asistencia eliminada correctamente.";
    } else {
        $_SESSION['error'] = "No tienes permiso para eliminar este registro.";
    }
}

header("Location: asistencias.php");
exit();
?>