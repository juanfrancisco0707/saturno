<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');

require_once '../conexion.php';

// Leer el cuerpo de la solicitud
$data = json_decode(file_get_contents("php://input"));

if (!isset($data->id)) {
    http_response_code(400);
    echo json_encode(['error' => 'Se requiere el ID de la categoría']);
    exit;
}

$id = filter_var($data->id, FILTER_VALIDATE_INT);

if ($id === false) {
    http_response_code(400);
    echo json_encode(['error' => 'El ID de la categoría debe ser un número entero']);
    exit;
}

try {
    $conexion = Conexion::conectar();
    $stmt = $conexion->prepare("DELETE FROM categorias WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
            echo json_encode(['mensaje' => 'Categoría borrada correctamente']);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Categoría no encontrada']);
        }
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error al borrar la categoría']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    // Check for foreign key constraint violation
    if ($e->getCode() == '23000') {
        echo json_encode(['error' => 'No se puede borrar la categoría porque tiene productos asociados.']);
    } else {
        echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
    }
}
