<?php
header('Content-Type: application/json');
require_once '../conexion.php';

try {
    $db = Conexion::conectar();
    
    $sql = "SELECT f.*, s.tipo as nombre_servicio, u.nombre_unidad, c.nombre as nombre_cliente 
            FROM facturas f
            LEFT JOIN factura_detalles fd ON f.id_factura = fd.id_factura
            LEFT JOIN servicios s ON fd.id_servicio = s.id_servicio
            LEFT JOIN unidades u ON s.id_unidad = u.id_unidad
            LEFT JOIN clientes c ON u.id_cliente = c.id_cliente
            ORDER BY f.fecha_emision DESC";
            
    $stmt = $db->prepare($sql);
    $stmt->execute();
    
    $facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($facturas);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
