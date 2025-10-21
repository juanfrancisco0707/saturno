<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');

require_once '../conexion.php';

// Leer el cuerpo de la solicitud
$data = json_decode(file_get_contents("php://input"));

if (!isset($data->id) || !isset($data->nombre)) {
    http_response_code(400);
    echo json_encode(['error' => 'Se requieren el ID y el nombre de la categoría']);
    exit;
}

$id = filter_var($data->id, FILTER_VALIDATE_INT);
$nombre = trim($data->nombre);

if ($id === false) {
    http_response_code(400);
    echo json_encode(['error' => 'El ID de la categoría debe ser un número entero']);
    exit;
}

if (empty($nombre)) {
    http_response_code(400);
    echo json_encode(['error' => 'El nombre de la categoría no puede estar vacío']);
    exit;
}

try {
    $conexion = Conexion::conectar();
    $stmt = $conexion->prepare("UPDATE categorias SET nombre = :nombre WHERE id = :id");
    $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
            echo json_encode(['mensaje' => 'Categoría actualizada correctamente']);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Categoría no encontrada o sin cambios']);
        }
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error al actualizar la categoría']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
}
