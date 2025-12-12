<?php
require_once 'conexion.php';
try {
    $db = Conexion::conectar();
    $stmt = $db->query("DESCRIBE usuarios");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo $e->getMessage();
}
?>
