<?php
header('Content-Type: application/json');

require_once '../conexion.php';

// Obtener datos JSON del cuerpo de la petición
$json_data = file_get_contents("php://input");
$data = json_decode($json_data, true);

$response = array();

if (isset($data['id_cliente']) && isset($data['nombre_unidad']) && isset($data['idcategoria'])) {
    $id_cliente = $data['id_cliente'];
    $nombre_unidad = $data['nombre_unidad'];
    $idcategoria = $data['idcategoria'];
    
    // Campos opcionales
    $fecha_instalacion = isset($data['fecha_instalacion']) ? $data['fecha_instalacion'] : null;
    $ultima_fecha_instalacion = isset($data['ultima_fecha_instalacion']) ? $data['ultima_fecha_instalacion'] : null;
    $comentarios = isset($data['comentarios']) ? $data['comentarios'] : null;
    $estatus = isset($data['estatus']) ? $data['estatus'] : 'activa';
    $tarjeta_sim = isset($data['tarjeta_sim']) ? $data['tarjeta_sim'] : null;

    try {
        $db = Conexion::conectar();
        $stmt = $db->prepare("INSERT INTO unidades (id_cliente, nombre_unidad, fecha_instalacion, ultima_fecha_instalacion, comentarios, estatus, tarjeta_sim, idcategoria) VALUES (:id_cliente, :nombre_unidad, :fecha_instalacion, :ultima_fecha_instalacion, :comentarios, :estatus, :tarjeta_sim, :idcategoria)");
        
        $stmt->bindParam(':id_cliente', $id_cliente);
        $stmt->bindParam(':nombre_unidad', $nombre_unidad);
        $stmt->bindParam(':fecha_instalacion', $fecha_instalacion);
        $stmt->bindParam(':ultima_fecha_instalacion', $ultima_fecha_instalacion);
        $stmt->bindParam(':comentarios', $comentarios);
        $stmt->bindParam(':estatus', $estatus);
        $stmt->bindParam(':tarjeta_sim', $tarjeta_sim);
        $stmt->bindParam(':idcategoria', $idcategoria);

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = "Unidad guardada exitosamente";
        } else {
            $response['success'] = false;
            $response['message'] = "Error al guardar la unidad";
        }
    } catch (PDOException $e) {
        $response['success'] = false;
        $response['message'] = "Error de base de datos: " . $e->getMessage();
    }
} else {
    $response['success'] = false;
    $response['message'] = "Faltan datos requeridos (id_cliente, nombre_unidad, idcategoria)";
}

echo json_encode($response);
?>