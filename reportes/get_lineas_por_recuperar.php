<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once '../conexion.php';

$db = Conexion::conectar();

try {
    $sql = "SELECT * FROM lineas_por_recuperar ORDER BY dias_hasta_recuperacion ASC";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo json_encode([]);
}
?>