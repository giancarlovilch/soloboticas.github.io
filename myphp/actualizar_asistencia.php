<?php
session_start();
include('db_connection.php');
include('session_manager.php');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $field = $_POST['field'];
    $value = $_POST['value'];
    
    // Validar campos permitidos
    $allowedFields = ['hora_entrada', 'hora_salida', 'tardanza_minutos'];
    if (!in_array($field, $allowedFields)) {
        $response['message'] = 'Campo no permitido';
        echo json_encode($response);
        exit;
    }
    
    // Verificar que el registro pertenezca al usuario
    $stmt = $pdo->prepare("SELECT nickname FROM registros_asistencia WHERE id = ?");
    $stmt->execute([$id]);
    $registro = $stmt->fetch();
    
    if (!$registro || $registro['nickname'] !== $_SESSION['nickname']) {
        $response['message'] = 'No tienes permiso para editar este registro';
        echo json_encode($response);
        exit;
    }
    
    // Actualizar el campo correspondiente
    try {
        if ($field === 'hora_entrada' || $field === 'hora_salida') {
            // Validar formato de hora
            if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $value)) {
                throw new Exception('Formato de hora inválido');
            }
            
            $stmt = $pdo->prepare("UPDATE registros_asistencia SET $field = ?, estado = 'Pendiente', fecha_modificacion = NOW() WHERE id = ?");
            $stmt->execute([$value . ':00', $id]);
        } else {
            // Para tardanza_minutos, validar que sea un número positivo
            $tardanza = intval($value);
            if ($tardanza < 0) {
                throw new Exception('La tardanza no puede ser negativa');
            }
            
            $stmt = $pdo->prepare("UPDATE registros_asistencia SET tardanza_minutos = ?, estado = 'Pendiente', fecha_modificacion = NOW() WHERE id = ?");
            $stmt->execute([$tardanza, $id]);
        }
        
        $response['success'] = true;
        $response['message'] = 'Registro actualizado correctamente';
    } catch (Exception $e) {
        $response['message'] = 'Error: ' . $e->getMessage();
    }
}

echo json_encode($response);
?>