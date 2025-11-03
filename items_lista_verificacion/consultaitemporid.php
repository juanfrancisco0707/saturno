<?php
header('Content-Type: application/json');

require_once '../conexion.php';

if (!isset($_GET['id_item']) || empty($_GET['id_item'])) {
    http_response_code(400);
    echo json_encode(['error' => 'El ID del item es requerido']);
    exit;
}

$id_item = filter_input(INPUT_GET, 'id_item', FILTER_VALIDATE_INT);

if ($id_item === false) {
    http_response_code(400);
    echo json_encode(['error' => 'El ID del item debe ser un número entero']);
    exit;
}

try {
    $conexion = Conexion::conectar();
    $stmt = $conexion->prepare("SELECT id_item, descripcion, es_requerido, creado_en, actualizado_en FROM items_lista_verificacion WHERE id_item = :id_item");
    $stmt->bindParam(':id_item', $id_item, PDO::PARAM_INT);
    $stmt->execute();

    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($item) {
        echo json_encode($item);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Item no encontrado']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al consultar el item: ' . $e->getMessage()]);
}
