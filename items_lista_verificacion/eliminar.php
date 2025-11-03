<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');

require_once '../conexion.php';

// Leer el cuerpo de la solicitud
$data = json_decode(file_get_contents("php://input"));

if (!isset($data->id_item)) {
    http_response_code(400);
    echo json_encode(['error' => 'Se requiere el ID del item']);
    exit;
}

$id_item = filter_var($data->id_item, FILTER_VALIDATE_INT);

if ($id_item === false) {
    http_response_code(400);
    echo json_encode(['error' => 'El ID del item debe ser un número entero']);
    exit;
}

try {
    $conexion = Conexion::conectar();
    $stmt = $conexion->prepare("DELETE FROM items_lista_verificacion WHERE id_item = :id_item");
    $stmt->bindParam(':id_item', $id_item, PDO::PARAM_INT);

    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
            echo json_encode(['mensaje' => 'Item borrado correctamente']);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Item no encontrado']);
        }
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error al borrar el item']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
}
