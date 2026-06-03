<?php
header('Content-Type: application/json');
require_once '../conexion.php';

$idCliente = $_GET['id_cliente'] ?? null;

if (!$idCliente) {
    echo json_encode(['error' => 'id_cliente es requerido']);
    return;
}

try {
    $db = Conexion::conectar();
    
    $sql = "SELECT i.id_instalacion, i.fecha_instalacion, i.componentes_instalados, 
                   s.id_servicio, s.tipo, s.monto, s.id_unidad, u.nombre_unidad
            FROM instalaciones i
            JOIN servicios s ON i.id_servicio = s.id_servicio
            JOIN unidades u ON s.id_unidad = u.id_unidad
            WHERE i.estado = 'completada' 
              AND u.id_cliente = :id_cliente 
              AND NOT EXISTS (
                  SELECT 1 FROM factura_detalles fd WHERE fd.id_servicio = s.id_servicio
              )";
              
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id_cliente', $idCliente);
    $stmt->execute();
    
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($data);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
