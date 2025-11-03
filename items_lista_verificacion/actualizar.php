<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');

require_once '../conexion.php';

// Leer el cuerpo de la solicitud
$data = json_decode(file_get_contents("php://input"));

if (!isset($data->id_item) || !isset($data->descripcion)) {
    http_response_code(400);
    echo json_encode(['error' => 'Se requieren el ID y la descripción del item']);
    exit;
}

$id_item = filter_var($data->id_item, FILTER_VALIDATE_INT);
$descripcion = trim($data->descripcion);
$es_requerido = isset($data->es_requerido) ? $data->es_requerido : 1;

if ($id_item === false) {
    http_response_code(400);
    echo json_encode(['error' => 'El ID del item debe ser un número entero']);
    exit;
}

if (empty($descripcion)) {
    http_response_code(400);
    echo json_encode(['error' => 'La descripción del item no puede estar vacía']);
    exit;
}

try {
    $conexion = Conexion::conectar();
    $stmt = $conexion->prepare("UPDATE items_lista_verificacion SET descripcion = :descripcion, es_requerido = :es_requerido, actualizado_en = current_timestamp() WHERE id_item = :id_item");
    $stmt->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
    $stmt->bindParam(':es_requerido', $es_requerido, PDO::PARAM_INT);
    $stmt->bindParam(':id_item', $id_item, PDO::PARAM_INT);

    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
            echo json_encode(['mensaje' => 'Item actualizado correctamente']);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Item no encontrado o sin cambios']);
        }
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error al actualizar el item']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
}
