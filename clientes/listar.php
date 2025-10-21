<?php
header('Content-Type: application/json');

require_once '../conexion.php';

try {
    $conexion = Conexion::conectar();
    $stmt = $conexion->prepare("SELECT id_cliente, nombre, direccion, telefono, email, representante FROM clientes ORDER BY nombre");
    $stmt->execute();
 
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($clientes);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al consultar los clientes: ' . $e->getMessage()]);
}
