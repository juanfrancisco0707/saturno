<?php
header('Content-Type: application/json');

require_once '../conexion.php';

if (!isset($_GET['id_instalacion']) || empty($_GET['id_instalacion'])) {
    http_response_code(400);
    echo json_encode(['error' => 'El ID de la instalación es requerido']);
    exit;
}

$id_instalacion = filter_input(INPUT_GET, 'id_instalacion', FILTER_VALIDATE_INT);

if ($id_instalacion === false) {
    http_response_code(400);
    echo json_encode(['error' => 'El ID de la instalación debe ser un número entero']);
    exit;
}

try {
    $conexion = Conexion::conectar();
    $stmt = $conexion->prepare("SELECT id_instalacion, id_servicio, id_unidad, id_tecnico, fecha_instalacion, componentes_instalados, estado, comentarios, creado_en, actualizado_en FROM instalaciones WHERE id_instalacion = :id_instalacion");
    $stmt->bindParam(':id_instalacion', $id_instalacion, PDO::PARAM_INT);
    $stmt->execute();

    $instalacion = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($instalacion) {
        echo json_encode($instalacion);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Instalación no encontrada']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al consultar la instalación: ' . $e->getMessage()]);
}
