<?php
require_once 'conexion.php';
try {
    $db = Conexion::conectar();
    $stmt = $db->query("DESCRIBE usuarios");
    foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
        echo $col['Field'] . "\n";
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
?>
