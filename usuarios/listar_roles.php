<?php
header('Content-Type: application/json');
require_once '../conexion.php';

try {
    $db = Conexion::conectar();
    $stmt = $db->query("SELECT id_rol, nombre FROM roles ORDER BY nombre");
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($roles);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
