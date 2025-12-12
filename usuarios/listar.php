<?php
header('Content-Type: application/json');
require_once '../conexion.php';

try {
    $db = Conexion::conectar();
    $sql = "SELECT u.id, u.username, u.nombre, u.correo, u.telefono, u.estatus, u.id_rol, r.nombre as rol_nombre 
            FROM usuarios u 
            LEFT JOIN roles r ON u.id_rol = r.id_rol 
            ORDER BY u.nombre";
    $stmt = $db->query($sql);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($usuarios);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
