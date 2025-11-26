<?php
header('Content-Type: application/json');
require_once '../conexion.php';

$db = Conexion::conectar();

try {
    // Consulta directa a la vista que creaste
    $sql = "SELECT * FROM alerta_renovaciones ORDER BY dias_hasta_vencimiento ASC";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    
    $alertas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($alertas);

} catch (Exception $e) {
    echo json_encode([]);
}
?>