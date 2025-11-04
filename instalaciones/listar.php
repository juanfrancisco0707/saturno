<?php
header('Content-Type: application/json');

require_once '../conexion.php';

try {
    $conexion = Conexion::conectar();
    $stmt = $conexion->prepare("SELECT id_instalacion, id_servicio, id_unidad, id_tecnico, fecha_instalacion, componentes_instalados, estado, comentarios, creado_en, actualizado_en FROM instalaciones ORDER BY fecha_instalacion DESC");
    $stmt->execute();

    $instalaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($instalaciones);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al consultar las instalaciones: ' . $e->getMessage()]);
}
