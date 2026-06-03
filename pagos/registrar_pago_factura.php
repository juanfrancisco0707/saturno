<?php
header('Content-Type: application/json');
require_once '../conexion.php';

$json = file_get_contents("php://input");
$data = json_decode($json);

if(isset($data->id_cliente) && isset($data->monto_total) && isset($data->facturas_pagadas) && is_array($data->facturas_pagadas)) {
    $db = Conexion::conectar();
    
    try {
        $db->beginTransaction();

        // 1. Insertar el Pago Principal
        $stmtPago = $db->prepare("INSERT INTO pagos (id_cliente, fecha_pago, monto, metodo, comentarios) VALUES (:id_cliente, :fecha, :monto, :metodo, :comentarios)");
        $stmtPago->bindParam(':id_cliente', $data->id_cliente);
        $fecha = date('Y-m-d');
        $stmtPago->bindParam(':fecha', $fecha);
        $stmtPago->bindParam(':monto', $data->monto_total);
        $metodo = isset($data->metodo) ? $data->metodo : 'Efectivo';
        $comentarios = isset($data->comentarios) ? $data->comentarios : '';
        $stmtPago->bindParam(':metodo', $metodo);
        $stmtPago->bindParam(':comentarios', $comentarios);
        $stmtPago->execute();
        $id_pago = $db->lastInsertId();

        $stmtInsertPagoFactura = $db->prepare("INSERT INTO pagos_facturas (id_pago, id_factura, monto_abonado) VALUES (:id_pago, :id_factura, :monto_abonado)");
        $stmtCheckSumaFactura = $db->prepare("SELECT SUM(monto_abonado) as total_abonado FROM pagos_facturas WHERE id_factura = :id_factura");
        $stmtMontoFactura = $db->prepare("SELECT monto FROM facturas WHERE id_factura = :id_factura");
        $stmtUpdFact = $db->prepare("UPDATE facturas SET estado = :estado WHERE id_factura = :id_factura");
        
        $stmtServiciosFactura = $db->prepare("SELECT id_servicio FROM factura_detalles WHERE id_factura = :id_factura");
        $stmtUpdServ = $db->prepare("UPDATE servicios SET estado = 'pagado' WHERE id_servicio = :id");

        foreach ($data->facturas_pagadas as $item) {
            $id_factura = $item->id_factura;
            $monto_abonado = $item->monto_abonado;

            if ($monto_abonado <= 0) continue;

            // Registrar abono a la factura
            $stmtInsertPagoFactura->bindParam(':id_pago', $id_pago);
            $stmtInsertPagoFactura->bindParam(':id_factura', $id_factura);
            $stmtInsertPagoFactura->bindParam(':monto_abonado', $monto_abonado);
            $stmtInsertPagoFactura->execute();

            // Verificar si la factura se liquidó o es pago parcial
            $stmtMontoFactura->bindParam(':id_factura', $id_factura);
            $stmtMontoFactura->execute();
            $factura = $stmtMontoFactura->fetch(PDO::FETCH_ASSOC);

            if ($factura) {
                $stmtCheckSumaFactura->bindParam(':id_factura', $id_factura);
                $stmtCheckSumaFactura->execute();
                $suma = $stmtCheckSumaFactura->fetch(PDO::FETCH_ASSOC);
                $total_abonado = $suma['total_abonado'] ? $suma['total_abonado'] : 0;

                $estado_factura = 'parcial';
                if ($total_abonado >= $factura['monto']) {
                    $estado_factura = 'pagada';
                }

                $stmtUpdFact->bindParam(':estado', $estado_factura);
                $stmtUpdFact->bindParam(':id_factura', $id_factura);
                $stmtUpdFact->execute();

                // Si se liquidó por completo, actualizar el estado de los servicios asociados
                if ($estado_factura == 'pagada') {
                    $stmtServiciosFactura->bindParam(':id_factura', $id_factura);
                    $stmtServiciosFactura->execute();
                    $servicios = $stmtServiciosFactura->fetchAll(PDO::FETCH_ASSOC);
                    
                    foreach($servicios as $serv) {
                        $stmtUpdServ->bindParam(':id', $serv['id_servicio']);
                        $stmtUpdServ->execute();
                    }
                }
            }
        }

        $db->commit();
        echo json_encode(['success' => true, 'message' => 'Pagos registrados y facturas actualizadas correctamente.']);

    } catch (Exception $e) {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos para registrar el pago.']);
}
?>