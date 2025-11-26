<?php
header('Content-Type: application/json');
require_once '../conexion.php';

$id_empresa = isset($_GET['id']) ? $_GET['id'] : 1; // Por defecto empresa 1

$db = Conexion::conectar();
$stmt = $db->prepare("SELECT folio_factura FROM empresa WHERE id = :id");
$stmt->bindParam(':id', $id_empresa);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode($result);
?>