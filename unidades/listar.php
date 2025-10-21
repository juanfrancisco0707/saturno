<?php
header('Content-Type: application/json');

require_once '../conexion.php';

try {
    $conexion = Conexion::conectar();
    $stmt = $conexion->prepare("SELECT u.*, c.nombre as nombre_categoria FROM unidades u JOIN categorias c ON u.idcategoria = c.id ORDER BY u.nombre_unidad");
    $stmt->execute();

    $unidades = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($unidades);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al consultar las unidades: ' . $e->getMessage()]);
}
