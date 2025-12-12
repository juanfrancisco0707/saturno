<?php
header('Content-Type: application/json');
require_once '../conexion.php';

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['nombre']) && isset($data['username']) && isset($data['password']) && isset($data['id_rol'])) {
    
    try {
        $db = Conexion::conectar();
        
    // Validar si existe username OR correo
        $check = $db->prepare("SELECT id FROM usuarios WHERE username = :username");
        $check->execute([':username' => $data['username']]);
        if($check->fetch()) {
            echo json_encode(['success' => false, 'message' => 'El nombre de usuario ya existe']);
            exit;
        }

        $sql = "INSERT INTO usuarios (nombre, username, password, id_rol, correo, telefono, estatus) VALUES (:nombre, :username, :password, :id_rol, :correo, :telefono, :estatus)";
        $stmt = $db->prepare($sql);
        
        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $stmt->bindParam(':nombre', $data['nombre']);
        $stmt->bindParam(':username', $data['username']);
        $stmt->bindParam(':password', $passwordHash);
        $stmt->bindParam(':id_rol', $data['id_rol']);
        $stmt->bindParam(':correo', $data['correo']);
        $stmt->bindParam(':telefono', $data['telefono']);
        $estatus = isset($data['estatus']) ? $data['estatus'] : 1; 
        $stmt->bindParam(':estatus', $estatus);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Usuario creado exitosamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al crear usuario']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error de BD: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
}
?>
