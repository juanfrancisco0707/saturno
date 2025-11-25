<?php
header('Content-Type: application/json');
require_once '../conexion.php';

$response = array();
$json_data = file_get_contents("php://input");
$data = json_decode($json_data);

if(isset($data->id_instalacion) && isset($data->id_item)){
    $id_instalacion = $data->id_instalacion;
    $id_item = $data->id_item;
    $verificado = isset($data->verificado) && $data->verificado ? 1 : 0;
    $comentarios = isset($data->comentarios) ? $data->comentarios : '';

    $db = Conexion::conectar();
    
    // Verificar si ya existe el registro para actualizarlo o insertarlo
    $check = $db->prepare("SELECT id FROM listas_verificacion_instalacion WHERE id_instalacion = :id_instalacion AND id_item = :id_item");
    $check->bindParam(':id_instalacion', $id_instalacion);
    $check->bindParam(':id_item', $id_item);
    $check->execute();
    
    if($check->rowCount() > 0){
        $stmt = $db->prepare("UPDATE listas_verificacion_instalacion SET verificado = :verificado, comentarios = :comentarios WHERE id_instalacion = :id_instalacion AND id_item = :id_item");
    } else {
        $stmt = $db->prepare("INSERT INTO listas_verificacion_instalacion (id_instalacion, id_item, verificado, comentarios) VALUES (:id_instalacion, :id_item, :verificado, :comentarios)");
    }
    
    $stmt->bindParam(':id_instalacion', $id_instalacion);
    $stmt->bindParam(':id_item', $id_item);
    $stmt->bindParam(':verificado', $verificado);
    $stmt->bindParam(':comentarios', $comentarios);

    if($stmt->execute()){
        $response['success'] = true;
        $response['message'] = "Guardado exitosamente";
    } else {
        $response['success'] = false;
        $response['message'] = "Error al guardar en base de datos";
    }
} else {
    $response['success'] = false;
    $response['message'] = "Datos incompletos: Se requiere id_instalacion e id_item";
}

echo json_encode($response);
?>