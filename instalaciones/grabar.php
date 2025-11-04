<?php
header('Content-Type: application/json');

require_once '../conexion.php';

$response = array();

// Obtener datos JSON del cuerpo de la petición
$json_data = file_get_contents("php://input");
$data = json_decode($json_data);

if(isset($data->id_servicio) && isset($data->id_unidad) && isset($data->id_tecnico) && isset($data->fecha_instalacion)){
    $id_servicio = $data->id_servicio;
    $id_unidad = $data->id_unidad;
    $id_tecnico = $data->id_tecnico;
    $fecha_instalacion = $data->fecha_instalacion;
    $componentes_instalados = isset($data->componentes_instalados) ? $data->componentes_instalados : null;
    $estado = !empty($data->estado) ? $data->estado : 'en_progreso';
    $comentarios = isset($data->comentarios) ? $data->comentarios : null;

    $db = Conexion::conectar();
    $stmt = $db->prepare("INSERT INTO instalaciones (id_servicio, id_unidad, id_tecnico, fecha_instalacion, componentes_instalados, estado, comentarios) VALUES (:id_servicio, :id_unidad, :id_tecnico, :fecha_instalacion, :componentes_instalados, :estado, :comentarios)");
    $stmt->bindParam(':id_servicio', $id_servicio);
    $stmt->bindParam(':id_unidad', $id_unidad);
    $stmt->bindParam(':id_tecnico', $id_tecnico);
    $stmt->bindParam(':fecha_instalacion', $fecha_instalacion);
    $stmt->bindParam(':componentes_instalados', $componentes_instalados);
    $stmt->bindParam(':estado', $estado);
    $stmt->bindParam(':comentarios', $comentarios);

    if($stmt->execute()){
        $response['success'] = true;
        $response['message'] = "Instalación guardada exitosamente";
    } else {
        $response['success'] = false;
        $response['message'] = "Error al guardar la instalación";
        $response['error_info'] = $stmt->errorInfo();
    }
} else {
    $response['success'] = false;
    $response['message'] = "No se recibieron todos los datos requeridos";
}

echo json_encode($response);
?>