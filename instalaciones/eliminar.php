<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');

require_once '../conexion.php';

// Leer el cuerpo de la solicitud
$data = json_decode(file_get_contents("php://input"));

if (!isset($data->id_instalacion)) {
    http_response_code(400);
    echo json_encode(['error' => 'Se requiere el ID de la instalación']);
    exit;
}

$id_instalacion = filter_var($data->id_instalacion, FILTER_VALIDATE_INT);

if ($id_instalacion === false) {
    http_response_code(400);
    echo json_encode(['error' => 'El ID de la instalación debe ser un número entero']);
    exit;
}

try {
    $conexion = Conexion::conectar();
    $stmt = $conexion->prepare("DELETE FROM instalaciones WHERE id_instalacion = :id_instalacion");
    $stmt->bindParam(':id_instalacion', $id_instalacion, PDO::PARAM_INT);

    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
            echo json_encode(['mensaje' => 'Instalación borrada correctamente']);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Instalación no encontrada']);
        }
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error al borrar la instalación']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
}
