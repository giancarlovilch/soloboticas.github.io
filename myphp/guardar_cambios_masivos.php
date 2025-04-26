<?php
session_start();
include('db_connection.php');
include('session_manager.php');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $cambios = $data['cambios'] ?? [];
    
    try {
        $pdo->beginTransaction();
        
        foreach ($cambios as $cambio) {
            $id = intval($cambio['id']);
            $field = $cambio['field'];
            $value = $cambio['value'];
            
            // Verificar permisos
            $stmt = $pdo->prepare("SELECT nickname FROM registros_asistencia WHERE id = ?");
            $stmt->execute([$id]);
            $registro = $stmt->fetch();
            
            if (!$registro || $registro['nickname'] !== $_SESSION['nickname']) {
                throw new Exception('No tienes permiso para editar este registro');
            }
            
            // Validar y actualizar
            if ($field === 'hora_entrada' || $field === 'hora_salida') {
                if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $value)) {
                    throw new Exception('Formato de hora inválido');
                }
                $value .= ':00';
            } elseif ($field === 'tardanza_minutos') {
                $value = intval($value);
                if ($value < 0) {
                    throw new Exception('La tardanza no puede ser negativa');
                }
            }
            
            $stmt = $pdo->prepare("UPDATE registros_asistencia SET $field = ?, estado = 'Pendiente', fecha_modificacion = NOW() WHERE id = ?");
            $stmt->execute([$value, $id]);
        }
        
        $pdo->commit();
        $response['success'] = true;
        $response['message'] = 'Cambios guardados correctamente';
    } catch (Exception $e) {
        $pdo->rollBack();
        $response['message'] = 'Error: ' . $e->getMessage();
    }
}

echo json_encode($response);
?>