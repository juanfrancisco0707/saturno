<?php
header('Content-Type: application/json');

require_once '../conexion.php';

$response = array();

// Obtener datos JSON del cuerpo de la petición
$json_data = file_get_contents("php://input");
$data = json_decode($json_data);

if(isset($data->nombre)){
    $nombre = $data->nombre;

    $db = Conexion::conectar();
    $stmt = $db->prepare("INSERT INTO categorias (nombre) VALUES (:nombre)");
    $stmt->bindParam(':nombre', $nombre);

    if($stmt->execute()){
        $response['success'] = true;
        $response['message'] = "Categoría guardada exitosamente";
    } else {
        $response['success'] = false;
        $response['message'] = "Error al guardar la categoría";
    }
} else {
    $response['success'] = false;
    $response['message'] = "No se recibió el nombre de la categoría";
}

echo json_encode($response);
?>