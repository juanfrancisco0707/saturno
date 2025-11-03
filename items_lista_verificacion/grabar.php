<?php
header('Content-Type: application/json');

require_once '../conexion.php';

$response = array();

// Obtener datos JSON del cuerpo de la petición
$json_data = file_get_contents("php://input");
$data = json_decode($json_data);

if(isset($data->descripcion)){
    $descripcion = $data->descripcion;
    $es_requerido = isset($data->es_requerido) ? $data->es_requerido : 1;

    $db = Conexion::conectar();
    $stmt = $db->prepare("INSERT INTO items_lista_verificacion (descripcion, es_requerido) VALUES (:descripcion, :es_requerido)");
    $stmt->bindParam(':descripcion', $descripcion);
    $stmt->bindParam(':es_requerido', $es_requerido);

    if($stmt->execute()){
        $response['success'] = true;
        $response['message'] = "Item guardado exitosamente";
    } else {
        $response['success'] = false;
        $response['message'] = "Error al guardar el item";
    }
} else {
    $response['success'] = false;
    $response['message'] = "No se recibió la descripción del item";
}

echo json_encode($response);
?>