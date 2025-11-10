<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');

require_once '../conexion.php';

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->id_servicio)) {
    http_response_code(400);
    echo json_encode(['error' => 'Se requiere el ID del servicio']);
    exit;
}

$id_servicio = filter_var($data->id_servicio, FILTER_VALIDATE_INT);

if ($id_servicio === false) {
    http_response_code(400);
    echo json_encode(['error' => 'El ID del servicio debe ser un número entero']);
    exit;
}

try {
    $conexion = Conexion::conectar();
    $stmt = $conexion->prepare("DELETE FROM servicios WHERE id_servicio = :id_servicio");
    $stmt->bindParam(':id_servicio', $id_servicio, PDO::PARAM_INT);

    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
            echo json_encode(['mensaje' => 'Servicio borrado correctamente']);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Servicio no encontrado']);
        }
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error al borrar el servicio']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
}
