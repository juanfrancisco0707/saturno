<?php
require_once 'conexion.php';

try {
    $db = Conexion::conectar();
    $stmt = $db->query("DESCRIBE unidades");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($columns, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
