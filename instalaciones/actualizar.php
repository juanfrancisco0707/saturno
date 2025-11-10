<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');

require_once '../conexion.php';

// Leer el cuerpo de la solicitud
$data = json_decode(file_get_contents("php://input"));

if (!isset($data->id_instalacion)) {
    http_response_code(400);
    echo json_encode(['error' => 'Se requiere el ID de la instalación']);
    exit;
}

$id_instalacion = filter_var($data->id_instalacion, FILTER_VALIDATE_INT);

if ($id_instalacion === false) {
    http_response_code(400);
    echo json_encode(['error' => 'El ID de la instalación debe ser un número entero']);
    exit;
}

try {
    $conexion = Conexion::conectar();
    
    // Consultar la instalación actual para comparar
    $stmt_select = $conexion->prepare("SELECT * FROM instalaciones WHERE id_instalacion = :id_instalacion");
    $stmt_select->bindParam(':id_instalacion', $id_instalacion, PDO::PARAM_INT);
    $stmt_select->execute();
    $instalacion_actual = $stmt_select->fetch(PDO::FETCH_ASSOC);

    if(!$instalacion_actual){
        http_response_code(404);
        echo json_encode(['error' => 'Instalación no encontrada']);
        exit;
    }

    // Asignar valores actuales y actualizarlos si se proporcionan nuevos datos
    $id_servicio = isset($data->id_servicio) ? $data->id_servicio : $instalacion_actual['id_servicio'];
    
    $id_tecnico = isset($data->id_tecnico) ? $data->id_tecnico : $instalacion_actual['id_tecnico'];
    $fecha_instalacion = isset($data->fecha_instalacion) ? $data->fecha_instalacion : $instalacion_actual['fecha_instalacion'];
    $componentes_instalados = isset($data->componentes_instalados) ? $data->componentes_instalados : $instalacion_actual['componentes_instalados'];
    $estado = isset($data->estado) ? $data->estado : $instalacion_actual['estado'];
    $comentarios = isset($data->comentarios) ? $data->comentarios : $instalacion_actual['comentarios'];

    $stmt = $conexion->prepare("UPDATE instalaciones SET id_servicio = :id_servicio, id_tecnico = :id_tecnico, fecha_instalacion = :fecha_instalacion, componentes_instalados = :componentes_instalados, estado = :estado, comentarios = :comentarios, actualizado_en = current_timestamp() WHERE id_instalacion = :id_instalacion");
    
    $stmt->bindParam(':id_servicio', $id_servicio);
   
    $stmt->bindParam(':id_tecnico', $id_tecnico);
    $stmt->bindParam(':fecha_instalacion', $fecha_instalacion);
    $stmt->bindParam(':componentes_instalados', $componentes_instalados);
    $stmt->bindParam(':estado', $estado);
    $stmt->bindParam(':comentarios', $comentarios);
    $stmt->bindParam(':id_instalacion', $id_instalacion, PDO::PARAM_INT);

    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
            echo json_encode(['mensaje' => 'Instalación actualizada correctamente']);
        } else {
            echo json_encode(['mensaje' => 'No se realizaron cambios en la instalación']);
        }
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error al actualizar la instalación']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
}
