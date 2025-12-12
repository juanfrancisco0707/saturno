<?php
header('Content-Type: application/json');
require_once '../conexion.php';

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['id']) && isset($data['nombre']) && isset($data['username']) && isset($data['id_rol'])) {
    
    try {
        $db = Conexion::conectar();
        
        // Construir query dinámicamente según si se cambia el password o no
        if (!empty($data['password'])) {
            $sql = "UPDATE usuarios SET nombre = :nombre, username = :username, password = :password, id_rol = :id_rol, correo = :correo, telefono = :telefono, estatus = :estatus WHERE id = :id";
        } else {
            $sql = "UPDATE usuarios SET nombre = :nombre, username = :username, id_rol = :id_rol, correo = :correo, telefono = :telefono, estatus = :estatus WHERE id = :id";
        }
        
        $stmt = $db->prepare($sql);
        
        $stmt->bindParam(':id', $data['id']);
        $stmt->bindParam(':nombre', $data['nombre']);
        $stmt->bindParam(':username', $data['username']);
        $stmt->bindParam(':id_rol', $data['id_rol']);
        $stmt->bindParam(':correo', $data['correo']);
        $stmt->bindParam(':telefono', $data['telefono']);
        $stmt->bindParam(':estatus', $data['estatus']);
        
        if (!empty($data['password'])) {
            $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
            $stmt->bindParam(':password', $passwordHash);
        }
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Usuario actualizado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar usuario']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error de BD: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
}
?>
