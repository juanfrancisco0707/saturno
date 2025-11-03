<?php
header('Content-Type: application/json');

require_once '../conexion.php';

try {
    $conexion = Conexion::conectar();
    $stmt = $conexion->prepare("SELECT id_item, descripcion, es_requerido, creado_en, actualizado_en FROM items_lista_verificacion ORDER BY descripcion");
    $stmt->execute();

    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($items);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al consultar los items: ' . $e->getMessage()]);
}
