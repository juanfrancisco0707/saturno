<?php
header('Content-Type: application/json');
require_once '../conexion.php';

$json = file_get_contents("php://input");
$data = json_decode($json, true);

if (empty($data['instalaciones']) || empty($data['id_empresa'])) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos (instalaciones o id_empresa)']);
    return;
}

$id_instalaciones = $data['instalaciones'];
$id_empresa = $data['id_empresa'];

try {
    $db = Conexion::conectar();
    $db->beginTransaction();

    // 1. Obtener los servicios de las instalaciones
    $inPlaceholders = str_repeat('?,', count($id_instalaciones) - 1) . '?';
    $sqlServ = "SELECT i.id_instalacion, s.id_servicio, s.monto, s.tipo 
                FROM instalaciones i 
                JOIN servicios s ON i.id_servicio = s.id_servicio 
                WHERE i.id_instalacion IN ($inPlaceholders)";
    $stmtServ = $db->prepare($sqlServ);
    $stmtServ->execute($id_instalaciones);
    $servicios = $stmtServ->fetchAll(PDO::FETCH_ASSOC);

    if (count($servicios) === 0) {
        throw new Exception("No se encontraron servicios para las instalaciones seleccionadas.");
    }

    $montoTotal = 0;
    foreach ($servicios as $s) {
        $montoTotal += floatval($s['monto']);
    }

    // 2. Obtener y bloquear el folio de la empresa
    $stmtEmp = $db->prepare("SELECT folio_factura FROM empresa WHERE id = ? FOR UPDATE");
    $stmtEmp->execute([$id_empresa]);
    $empresa = $stmtEmp->fetch(PDO::FETCH_ASSOC);
    if (!$empresa) throw new Exception("Empresa no encontrada.");
    
    $nuevo_folio = $empresa['folio_factura'];
    $sig_folio = $nuevo_folio + 1;

    // 3. Insertar la factura global
    $stmtFact = $db->prepare("INSERT INTO facturas (numero_factura, fecha_emision, monto, estado, comentarios) VALUES (?, NOW(), ?, 'pendiente', 'Generada desde facturación múltiple')");
    $stmtFact->execute([$nuevo_folio, $montoTotal]);
    $id_factura = $db->lastInsertId();

    // 4. Actualizar el folio de la empresa
    $stmtUpdEmp = $db->prepare("UPDATE empresa SET folio_factura = ? WHERE id = ?");
    $stmtUpdEmp->execute([$sig_folio, $id_empresa]);

    // 5. Insertar los detalles y actualizar estado del servicio
    // IMPORTANTE: id_detalle se asume AUTO_INCREMENT
    $stmtDetalle = $db->prepare("INSERT INTO factura_detalles (id_factura, id_servicio, descripcion, monto_unitario, cantidad) VALUES (?, ?, ?, ?, 1)");
    $stmtUpdServ = $db->prepare("UPDATE servicios SET estado = 'pendiente' WHERE id_servicio = ?");

    foreach ($servicios as $s) {
        $desc = "Servicio " . $s['tipo'] . " (Instalación #" . $s['id_instalacion'] . ")";
        $stmtDetalle->execute([$id_factura, $s['id_servicio'], $desc, $s['monto']]);
        $stmtUpdServ->execute([$s['id_servicio']]);
    }

    $db->commit();
    echo json_encode(['success' => true, 'message' => "Factura $nuevo_folio generada correctamente por " . count($servicios) . " servicios."]);
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
