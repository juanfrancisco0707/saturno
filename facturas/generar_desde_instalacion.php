<?php
header('Content-Type: application/json');
require_once '../conexion.php';

$json = file_get_contents("php://input");
$data = json_decode($json);

if(isset($data->id_instalacion) && isset($data->id_empresa)) {
    $db = Conexion::conectar();
    
    try {
        $db->beginTransaction();

        // 1. Obtener datos de la instalación y servicio asociado
        $stmtInst = $db->prepare("SELECT i.id_servicio, s.monto, s.id_unidad FROM instalaciones i JOIN servicios s ON i.id_servicio = s.id_servicio WHERE i.id_instalacion = :id");
        $stmtInst->bindParam(':id', $data->id_instalacion);
        $stmtInst->execute();
        $info = $stmtInst->fetch(PDO::FETCH_ASSOC);
        
        if(!$info) throw new Exception("Instalación no encontrada");

        // 2. Obtener y bloquear el folio de la empresa
        $stmtEmp = $db->prepare("SELECT folio_factura, rfc FROM empresa WHERE id = :id FOR UPDATE");
        $stmtEmp->bindParam(':id', $data->id_empresa);
        $stmtEmp->execute();
        $empresa = $stmtEmp->fetch(PDO::FETCH_ASSOC);

        $nuevo_folio = $empresa['folio_factura']; // Usar el actual
        $sig_folio = $nuevo_folio + 1;

        // 3. Insertar Factura
        $stmtFact = $db->prepare("INSERT INTO facturas (numero_factura, fecha_emision, monto, estado, comentarios) VALUES (:num, NOW(), :monto, 'pendiente', 'Generada desde instalación')");
        $stmtFact->bindParam(':num', $nuevo_folio);
        $stmtFact->bindParam(':monto', $info['monto']);
        $stmtFact->execute();
        $id_factura = $db->lastInsertId();

        // 4. Actualizar Empresa (Incrementar Folio)
        $stmtUpdEmp = $db->prepare("UPDATE empresa SET folio_factura = :folio WHERE id = :id");
        $stmtUpdEmp->bindParam(':folio', $sig_folio);
        $stmtUpdEmp->bindParam(':id', $data->id_empresa);
        $stmtUpdEmp->execute();

        // 5. Actualizar Instalación (Estado completada)
        $stmtUpdInst = $db->prepare("UPDATE instalaciones SET estado = 'completada' WHERE id_instalacion = :id");
        $stmtUpdInst->bindParam(':id', $data->id_instalacion);
        $stmtUpdInst->execute();
        
        // 6. Actualizar Servicio (Vincular factura)
        $stmtUpdServ = $db->prepare("UPDATE servicios SET id_factura = :id_fact, estado = 'pendiente' WHERE id_servicio = :id_serv");
        $stmtUpdServ->bindParam(':id_fact', $id_factura);
        $stmtUpdServ->bindParam(':id_serv', $info['id_servicio']);
        $stmtUpdServ->execute();

        $db->commit();
        echo json_encode(['success' => true, 'message' => 'Factura ' . $nuevo_folio . ' generada correctamente']);

    } catch (Exception $e) {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
}
?>