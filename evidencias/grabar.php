<?php
header('Content-Type: application/json');
require_once '../conexion.php';

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->id_lista_verificacion) || !isset($data->ruta_archivo)) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos para registrar la evidencia.']);
    exit;
}

$id_lista_verificacion = $data->id_lista_verificacion;
$ruta_archivo = $data->ruta_archivo;
$descripcion = $data->descripcion ?? '';

try {
    $con = Conexion::conectar();
    $sql = "INSERT INTO evidencias (id_lista_verificacion, ruta_archivo, descripcion) 
            VALUES (:id_lista, :ruta, :desc)";
    
    $stmt = $con->prepare($sql);
    $stmt->execute([
        ':id_lista' => $id_lista_verificacion,
        ':ruta' => $ruta_archivo,
        ':desc' => $descripcion
    ]);

    echo json_encode(['success' => true, 'message' => 'Evidencia registrada en la base de datos.']);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al guardar en la base de datos: ' . $e->getMessage()]);
}
?>