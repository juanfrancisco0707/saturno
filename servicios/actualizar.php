<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');

require_once '../conexion.php';

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->id_servicio)) {
    http_response_code(400);
    echo json_encode(['error' => 'Se requiere el ID del servicio']);
    exit;
}

$id_servicio = filter_var($data->id_servicio, FILTER_VALIDATE_INT);

if ($id_servicio === false) {
    http_response_code(400);
    echo json_encode(['error' => 'El ID del servicio debe ser un número entero']);
    exit;
}

try {
    $conexion = Conexion::conectar();
    
    $stmt_select = $conexion->prepare("SELECT * FROM servicios WHERE id_servicio = :id_servicio");
    $stmt_select->bindParam(':id_servicio', $id_servicio, PDO::PARAM_INT);
    $stmt_select->execute();
    $servicio_actual = $stmt_select->fetch(PDO::FETCH_ASSOC);

    if(!$servicio_actual){
        http_response_code(404);
        echo json_encode(['error' => 'Servicio no encontrado']);
        exit;
    }

    // Asignar valores actuales y actualizarlos si se proporcionan nuevos datos
    $id_unidad = isset($data->id_unidad) ? $data->id_unidad : $servicio_actual['id_unidad'];
    $tipo = isset($data->tipo) ? $data->tipo : $servicio_actual['tipo'];
    $fecha_inicio = isset($data->fecha_inicio) ? $data->fecha_inicio : $servicio_actual['fecha_inicio'];
    $fecha_fin = isset($data->fecha_fin) ? $data->fecha_fin : $servicio_actual['fecha_fin'];
    $fecha_vencimiento = isset($data->fecha_vencimiento) ? $data->fecha_vencimiento : $servicio_actual['fecha_vencimiento'];
    $monto = isset($data->monto) ? $data->monto : $servicio_actual['monto'];
    $estado = isset($data->estado) ? $data->estado : $servicio_actual['estado'];
    $num_periodos = isset($data->num_periodos) ? $data->num_periodos : $servicio_actual['num_periodos'];
    $comentarios = isset($data->comentarios) ? $data->comentarios : $servicio_actual['comentarios'];
    $id_factura = isset($data->id_factura) ? $data->id_factura : $servicio_actual['id_factura'];
    $periodo_pago = isset($data->periodo_pago) ? $data->periodo_pago : $servicio_actual['periodo_pago'];
    $tarjeta_sim = isset($data->tarjeta_sim) ? $data->tarjeta_sim : $servicio_actual['tarjeta_sim'];

    // Validaciones de CHECK constraints
    $valid_tipos = ['renovacion', 'instalacion'];
    $valid_estados = ['vencido', 'pendiente', 'pagado'];
    $valid_periodos_pago = ['anual', 'semestral', 'bimestral', 'mensual'];

    if (!in_array($tipo, $valid_tipos)) {
        http_response_code(400);
        echo json_encode(['error' => "Valor inválido para 'tipo'. Valores permitidos: " . implode(', ', $valid_tipos)]);
        exit;
    }

    if (!in_array($estado, $valid_estados)) {
        http_response_code(400);
        echo json_encode(['error' => "Valor inválido para 'estado'. Valores permitidos: " . implode(', ', $valid_estados)]);
        exit;
    }

    if (!in_array($periodo_pago, $valid_periodos_pago)) {
        http_response_code(400);
        echo json_encode(['error' => "Valor inválido para 'periodo_pago'. Valores permitidos: " . implode(', ', $valid_periodos_pago)]);
        exit;
    }

    $stmt = $conexion->prepare("UPDATE servicios SET id_unidad = :id_unidad, tipo = :tipo, fecha_inicio = :fecha_inicio, fecha_fin = :fecha_fin, fecha_vencimiento = :fecha_vencimiento, monto = :monto, estado = :estado, num_periodos = :num_periodos, comentarios = :comentarios, id_factura = :id_factura, periodo_pago = :periodo_pago, tarjeta_sim = :tarjeta_sim, actualizado_en = current_timestamp() WHERE id_servicio = :id_servicio");
    
    $stmt->bindParam(':id_unidad', $id_unidad);
    $stmt->bindParam(':tipo', $tipo);
    $stmt->bindParam(':fecha_inicio', $fecha_inicio);
    $stmt->bindParam(':fecha_fin', $fecha_fin);
    $stmt->bindParam(':fecha_vencimiento', $fecha_vencimiento);
    $stmt->bindParam(':monto', $monto);
    $stmt->bindParam(':estado', $estado);
    $stmt->bindParam(':num_periodos', $num_periodos);
    $stmt->bindParam(':comentarios', $comentarios);
    $stmt->bindParam(':id_factura', $id_factura);
    $stmt->bindParam(':periodo_pago', $periodo_pago);
    $stmt->bindParam(':tarjeta_sim', $tarjeta_sim);
    $stmt->bindParam(':id_servicio', $id_servicio, PDO::PARAM_INT);

    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
            echo json_encode(['mensaje' => 'Servicio actualizado correctamente']);
        } else {
            echo json_encode(['mensaje' => 'No se realizaron cambios en el servicio']);
        }
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error al actualizar el servicio', 'error_info' => $stmt->errorInfo()]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
}
