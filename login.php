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
    $stmt = $conn->prepare("SELECT id, username, password, nombre FROM usuarios WHERE username = :username");
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();

    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Verifica la contraseña (ajusta según tu método de almacenamiento)
        if (password_verify($password, $row['password'])) {
            echo json_encode([
                'success' => true,
                'user' => [
                    'id' => $row['id'],
                    'username' => $row['username'],
                    'nombre' => $row['nombre']
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Contraseña incorrecta']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error en la consulta']);
}
?>