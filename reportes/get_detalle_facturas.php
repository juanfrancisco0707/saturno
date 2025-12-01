<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once '../conexion.php';

$db = Conexion::conectar();
 // detalle de facturas
try {
    $sql = "SELECT * FROM detalle_facturas_servicios ORDER BY fecha_emision DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo json_encode([]);
}
?>
