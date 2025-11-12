<?php
// Establecer encabezados para la respuesta JSON y el método PUT
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: PUT');
header('Access-Control-Allow-Origin: *'); // Opcional: para desarrollo

require_once '../conexion.php';

// Obtener los datos del cuerpo de la petición PUT
$data = json_decode(file_get_contents("php://input"));

// El ID del servicio ahora viene de la URL, no del cuerpo
$id_servicio = null;
if (isset($_GET['id'])) {
    $id_servicio = filter_var($_GET['id'], FILTER_VALIDATE_INT);
}

if ($id_servicio === null || $id_servicio === false) {
    http_response_code(400);
    // Formato de error consistente
    echo json_encode(['success' => false, 'message' => 'Se requiere un ID de servicio válido en la URL']);
    exit;
}

try {
    $conexion = Conexion::conectar();
    
    // 1. Obtener el servicio actual
    $stmt_select = $conexion->prepare("SELECT * FROM servicios WHERE id_servicio = :id_servicio");
    $stmt_select->bindParam(':id_servicio', $id_servicio, PDO::PARAM_INT);
    $stmt_select->execute();
    $servicio_actual = $stmt_select->fetch(PDO::FETCH_ASSOC);

    if(!$servicio_actual){
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Servicio no encontrado']);
        exit;
    }

    // 2. Asignar valores (si no se proporcionan, se mantienen los actuales)
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
    $iccid = isset($data->iccid) ? $data->iccid : $servicio_actual['iccid'];

    // 3. Validaciones de CHECK constraints (¡CORREGIDAS!)
    $valid_tipos = ['renovacion', 'instalacion', 'mantenimiento', 'otro']; // ACTUALIZADO
    $valid_estados = ['vencido', 'pendiente', 'pagado'];
    $valid_periodos_pago = ['anual', 'semestral', 'bimestral', 'mensual'];

    if (!in_array(strtolower($tipo), $valid_tipos)) {
        http_response_code(400);
        // Formato de error consistente
        echo json_encode(['success' => false, 'message' => "Valor inválido para 'tipo'."]);
        exit;
    }
    // ... (puedes añadir las otras validaciones si lo deseas) ...

    // 4. Preparar y ejecutar la consulta UPDATE
    $stmt = $conexion->prepare("UPDATE servicios SET id_unidad = :id_unidad, tipo = :tipo, fecha_inicio = :fecha_inicio, fecha_fin = :fecha_fin, fecha_vencimiento = :fecha_vencimiento, monto = :monto, estado = :estado, num_periodos = :num_periodos, comentarios = :comentarios, id_factura = :id_factura, periodo_pago = :periodo_pago, tarjeta_sim = :tarjeta_sim,iccid = :iccid,actualizado_en = current_timestamp() WHERE id_servicio = :id_servicio");
    
    // Bind de parámetros...
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
    $stmt->bindParam(':iccid', $iccid);


    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Servicio actualizado correctamente']);
    } else {
        http_response_code(500);
        // Formato de error consistente
        $errorInfo = $stmt->errorInfo();
        echo json_encode(['success' => false, 'message' => 'Error al actualizar el servicio: ' . $errorInfo[2]]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
}
