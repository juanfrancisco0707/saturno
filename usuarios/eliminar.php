<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

require_once('../conexion.php');

$data = json_decode(file_get_contents("php://input"));

if(isset($data->id)) {
    try {
        $db = Conexion::conectar();
        $stmt = $db->prepare("DELETE FROM usuarios WHERE id = :id");
        $stmt->bindParam(':id', $data->id);
        
        if($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Usuario eliminado']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
}
?>
