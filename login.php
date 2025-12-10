<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once "conexion.php";

$conn = Conexion::conectar();

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión']);
    exit();
}

// Obtener datos JSON del cuerpo de la petición
$data = json_decode(file_get_contents('php://input'), true);

$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos']);
    exit();
}

try {
    // 1. Agregamos u.password al SELECT
    // 2. Cambiamos :user por :username en el WHERE para que coincida con el bindParam
    $sql = "SELECT u.id, u.username, u.password, u.nombre, r.nombre as rol_nombre 
        FROM usuarios u 
        INNER JOIN roles r ON u.id_rol = r.id_rol 
        WHERE u.username = :username";
        
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();   

    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Verifica la contraseña
        if (password_verify($password, $row['password'])) {
            echo json_encode([
                'success' => true,
                'user' => [
                    'id' => $row['id'],
                    'username' => $row['username'],
                    'nombre' => $row['nombre'],
                    // 3. Enviamos 'rol' para que coincida con Android
                    'rol' => $row['rol_nombre'] 
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Contraseña incorrecta']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
    }
} catch (PDOException $e) {
    // Muestra el error real para depurar (quítalo en producción si prefieres)
    echo json_encode(['success' => false, 'message' => 'Error en la consulta: ' . $e->getMessage()]);
}
?>