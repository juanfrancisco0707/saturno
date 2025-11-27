<?php
header('Content-Type: application/json');
require_once '../conexion.php';

$json = file_get_contents("php://input");
$data = json_decode($json);

if(isset($data->id_factura) && isset($data->id_cliente) && isset($data->monto)) {
    $db = Conexion::conectar();
    
    try {
        $db->beginTransaction();

        // 1. Insertar el Pago
        $stmtPago = $db->prepare("INSERT INTO pagos (id_cliente,
         fecha_pago, monto, metodo, comentarios) VALUES (:id_cliente, :fecha, :monto, :metodo, :comentarios)");
        $stmtPago->bindParam(':id_cliente', $data->id_cliente);
        $fecha = date('Y-m-d'); // Usamos fecha actual o la que envíe el usuario
        $stmtPago->bindParam(':fecha', $fecha);
        $stmtPago->bindParam(':monto', $data->monto);
        $stmtPago->bindParam(':metodo', $data->metodo); // 'efectivo', 'transferencia', etc.
        $stmtPago->bindParam(':comentarios', $data->comentarios);
        $stmtPago->execute();
        $id_pago = $db->lastInsertId();

        // 2. Obtener servicios asociados a la factura para distribuir el pago
        $stmtServicios = $db->prepare("SELECT id_servicio, monto FROM servicios WHERE id_factura = :id_factura");
        $stmtServicios->bindParam(':id_factura', $data->id_factura);
        $stmtServicios->execute();
        $servicios = $stmtServicios->fetchAll(PDO::FETCH_ASSOC);

        // 3. Relacionar pago con servicios (pagos_servicios)
        // Por simplicidad, asignamos el pago proporcionalmente o al primero si cubre todo
        $stmtRel = $db->prepare("INSERT INTO pagos_servicios (id_pago, id_servicio, monto_asignado) VALUES (:id_pago, :id_servicio, :monto_asignado)");
        
        $monto_restante = $data->monto;
        foreach ($servicios as $serv) {
            if ($monto_restante <= 0) break;
            
            $asignar = min($monto_restante, $serv['monto']);
            
            $stmtRel->bindParam(':id_pago', $id_pago);
            $stmtRel->bindParam(':id_servicio', $serv['id_servicio']);
            $stmtRel->bindParam(':monto_asignado', $asignar);
            $stmtRel->execute();
            
            // Actualizar estado del servicio a 'pagado'
            $stmtUpdServ = $db->prepare("UPDATE servicios SET estado = 'pagado' WHERE id_servicio = :id");
            $stmtUpdServ->bindParam(':id', $serv['id_servicio']);
            $stmtUpdServ->execute();
            
            $monto_restante -= $asignar;
        }

        // 4. Actualizar estado de la Factura a 'pagado'
        // Nota: Aquí asumimos pago completo. Si quieres pagos parciales, requeriría más lógica.
        $stmtUpdFact = $db->prepare("UPDATE facturas SET estado = 'pagado' WHERE id_factura = :id");
        $stmtUpdFact->bindParam(':id', $data->id_factura);
        $stmtUpdFact->execute();

        $db->commit();
        echo json_encode(['success' => true, 'message' => 'Pago registrado correctamente']);

    } catch (Exception $e) {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
}
?>