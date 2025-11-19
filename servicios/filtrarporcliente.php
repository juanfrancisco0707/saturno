<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once '../conexion.php';

if (!isset($_GET['id_cliente'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Se requiere el ID del cliente.']);
    exit;
}

$idCliente = filter_var($_GET['id_cliente'], FILTER_VALIDATE_INT);

if ($idCliente === false) {
    http_response_code(400);
    echo json_encode(['error' => 'El ID del cliente no es válido.']);
    exit;
}

try {
    $conexion = Conexion::conectar();
        $stmt = $conexion->prepare("
        SELECT
            s.*  -- This selects all columns from the servicios table
        FROM servicios AS s
        INNER JOIN unidades AS u ON s.id_unidad = u.id_unidad
        WHERE u.id_cliente = :idCliente
        ORDER BY s.tipo
    ");

    $stmt->bindParam(':idCliente', $idCliente, PDO::PARAM_INT);
    $stmt->execute();

    $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($resultado);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
}
?>