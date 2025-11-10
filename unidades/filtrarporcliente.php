<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET');

require_once '../conexion.php';

if (!isset($_GET['id_cliente'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Se requiere el ID del cliente']);
    exit;
}

$id_cliente = filter_var($_GET['id_cliente'], FILTER_VALIDATE_INT);

if ($id_cliente === false) {
    http_response_code(400);
    echo json_encode(['error' => 'El ID del cliente debe ser un número entero']);
    exit;
}

try {
    $conexion = Conexion::conectar();
    
    $stmt = $conexion->prepare("SELECT u.*, c.nombre as nombre_categoria FROM unidades u JOIN categorias c ON u.idcategoria = c.id WHERE u.id_cliente = :id_cliente ORDER BY u.nombre_unidad");
    
    $stmt->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
    $stmt->execute();

    $unidades = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($unidades);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al consultar las unidades: ' . $e->getMessage()]);
}
