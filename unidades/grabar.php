<?php
header('Content-Type: application/json');

require_once '../conexion.php';

$json_data = file_get_contents("php://input");
$data = json_decode($json_data, true);

$response = array();

// CORRECTED: Check for the actual JSON keys sent by the app
if (isset($data['id_cliente']) && isset($data['nombre_unidad']) && isset($data['idcategoria'])) {
    
    // CORRECTED: Read from the actual JSON keys
    $id_cliente = $data['id_cliente'];
    $nombre_unidad = $data['nombre_unidad'];
    $idcategoria = $data['idcategoria'];
    
    // CORRECTED: Optional fields also use the correct JSON keys
    $fecha_instalacion = isset($data['fecha_instalacion']) ? $data['fecha_instalacion'] : null;
    $comentarios = isset($data['comentarios']) ? $data['comentarios'] : null;
    $estatus = isset($data['estatus']) ? $data['estatus'] : 'activa';
    $tarjeta_sim = isset($data['tarjeta_sim']) ? $data['tarjeta_sim'] : null;
    $iccid = isset($data['iccid']) ? $data['iccid'] : null;
    
    $allowed_estatus = ['activa', 'standby', 'baja'];
    if (!in_array($estatus, $allowed_estatus)) {
        $response['success'] = false;
        $response['message'] = "Valor de estatus no válido.";
        echo json_encode($response);
        exit;
    }

    try {
        $db = Conexion::conectar();
        $stmt = $db->prepare("INSERT INTO unidades 
        (id_cliente, nombre_unidad, fecha_instalacion, comentarios, estatus, tarjeta_sim, iccid, idcategoria) 
        VALUES (:id_cliente, :nombre_unidad, :fecha_instalacion, :comentarios, :estatus, :tarjeta_sim, :iccid, :idcategoria)");
        
        $stmt->bindParam(':id_cliente', $id_cliente);
        $stmt->bindParam(':nombre_unidad', $nombre_unidad);
        $stmt->bindParam(':fecha_instalacion', $fecha_instalacion);
        $stmt->bindParam(':comentarios', $comentarios);
        $stmt->bindParam(':estatus', $estatus);
        $stmt->bindParam(':tarjeta_sim', $tarjeta_sim);
        $stmt->bindParam(':iccid', $iccid);
        $stmt->bindParam(':idcategoria', $idcategoria);
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['id'] = $db->lastInsertId();
            $response['message'] = "Unidad guardada exitosamente";
        } else {
            $errorInfo = $stmt->errorInfo();
            $response['success'] = false;
            $response['message'] = "Error al ejecutar la consulta: " . $errorInfo[2];
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