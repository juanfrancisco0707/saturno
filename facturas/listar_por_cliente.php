<?php
header('Content-Type: application/json');
require_once '../conexion.php';

$id_cliente = isset($_GET['id_cliente']) ? $_GET['id_cliente'] : null;
$estado = isset($_GET['estado']) ? $_GET['estado'] : null;

if ($id_cliente) {
    $db = Conexion::conectar();
    
    // Unimos Facturas con Servicios, Unidades y Clientes
    $sql = "SELECT f.*, s.tipo as nombre_servicio, u.nombre_unidad 
            FROM facturas f
            JOIN servicios s ON f.id_factura = s.id_factura
            JOIN unidades u ON s.id_unidad = u.id_unidad
            WHERE u.id_cliente = :id_cliente";
            
    // Si se envió un estado específico (y no es 'Todos'), filtramos
    if ($estado && $estado != 'Todos') {
        $sql .= " AND f.estado = :estado";
    }
    
    $sql .= " ORDER BY f.fecha_emision DESC";

    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id_cliente', $id_cliente);
    
    if ($estado && $estado != 'Todos') {
        $stmt->bindParam(':estado', $estado);
    }
    
    $stmt->execute();
    $facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($facturas);
} else {
    echo json_encode([]);
}
?>