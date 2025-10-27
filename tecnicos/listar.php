<?php
header('Content-Type: application/json');

require_once '../conexion.php';

try {
    $conexion = Conexion::conectar();
    $stmt = $conexion->prepare("SELECT * FROM tecnicos ORDER BY nombre");
    $stmt->execute();
 
    $tecnicos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($tecnicos);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al consultar los técnicos: ' . $e->getMessage()]);
}
