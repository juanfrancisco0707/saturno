<?php
header('Content-Type: application/json');

require_once '../conexion.php';

try {
    $conexion = Conexion::conectar();
    $stmt = $conexion->prepare("SELECT id, nombre, direccion, telefono, correo, rfc, representante, folio_factura FROM empresa ORDER BY nombre");
    $stmt->execute();

    $empresas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($empresas);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al consultar las empresas: ' . $e->getMessage()]);
}
