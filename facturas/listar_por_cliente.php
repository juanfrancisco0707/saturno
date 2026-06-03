<?php
header('Content-Type: application/json');
require_once '../conexion.php';

$id_cliente = isset($_GET['id_cliente']) ? $_GET['id_cliente'] : null;
$estado = isset($_GET['estado']) ? $_GET['estado'] : null;

if ($id_cliente) {
    $db = Conexion::conectar();
    
    // Obtenemos las facturas usando el nuevo campo id_cliente
    // Calculamos también lo abonado hasta el momento
    $sql = "SELECT f.*, 
            (SELECT SUM(monto_abonado) FROM pagos_facturas pf WHERE pf.id_factura = f.id_factura) as total_abonado
            FROM facturas f
            WHERE f.id_cliente = :id_cliente";
            
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
    
    // Agregar detalle de servicios si se necesita en el frontend (opcional)
    foreach ($facturas as &$factura) {
        $stmtDetalles = $db->prepare("SELECT s.tipo as nombre_servicio, u.nombre_unidad 
                                      FROM factura_detalles fd 
                                      JOIN servicios s ON fd.id_servicio = s.id_servicio 
                                      JOIN unidades u ON s.id_unidad = u.id_unidad 
                                      WHERE fd.id_factura = :id_factura");
        $stmtDetalles->bindParam(':id_factura', $factura['id_factura']);
        $stmtDetalles->execute();
        $factura['detalles'] = $stmtDetalles->fetchAll(PDO::FETCH_ASSOC);
        
        if(!$factura['total_abonado']) {
            $factura['total_abonado'] = 0;
        }
    }
    
    echo json_encode($facturas);
} else {
    echo json_encode([]);
}
?>