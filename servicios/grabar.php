<?php
header('Content-Type: application/json');

require_once '../conexion.php';

$response = array();

$json_data = file_get_contents("php://input");
$data = json_decode($json_data);

if (isset($data->id_unidad) && isset($data->tipo) && isset($data->fecha_inicio) && isset($data->monto)) {
    $id_unidad = $data->id_unidad;
    $tipo = $data->tipo;
    $fecha_inicio = $data->fecha_inicio;
    $monto = $data->monto;

    // Campos opcionales
    $fecha_fin = isset($data->fecha_fin) ? $data->fecha_fin : null;
    $fecha_vencimiento = isset($data->fecha_vencimiento) ? $data->fecha_vencimiento : null;
    $estado = !empty($data->estado) ? $data->estado : 'pendiente';
    $num_periodos = isset($data->num_periodos) ? $data->num_periodos : 1;
    $comentarios = isset($data->comentarios) ? $data->comentarios : null;
    $id_factura = isset($data->id_factura) ? $data->id_factura : null;
    $periodo_pago = !empty($data->periodo_pago) ? $data->periodo_pago : 'anual';
    $tarjeta_sim = isset($data->tarjeta_sim) ? $data->tarjeta_sim : null;

    // Validaciones de CHECK constraints
    $valid_tipos = ['renovacion', 'instalacion'];
    $valid_estados = ['vencido', 'pendiente', 'pagado'];
    $valid_periodos_pago = ['anual', 'semestral', 'bimestral', 'mensual'];

    if (!in_array($tipo, $valid_tipos)) {
        $response['success'] = false;
        $response['message'] = "Valor inválido para 'tipo'. Valores permitidos: " . implode(', ', $valid_tipos);
        echo json_encode($response);
        exit;
    }

    if (!in_array($estado, $valid_estados)) {
        $response['success'] = false;
        $response['message'] = "Valor inválido para 'estado'. Valores permitidos: " . implode(', ', $valid_estados);
        echo json_encode($response);
        exit;
    }

    if (!in_array($periodo_pago, $valid_periodos_pago)) {
        $response['success'] = false;
        $response['message'] = "Valor inválido para 'periodo_pago'. Valores permitidos: " . implode(', ', $valid_periodos_pago);
        echo json_encode($response);
        exit;
    }

    try {
        $db = Conexion::conectar();
        $stmt = $db->prepare("INSERT INTO servicios (id_unidad, tipo, fecha_inicio, fecha_fin, fecha_vencimiento, monto, estado, num_periodos, comentarios, id_factura, periodo_pago, tarjeta_sim) VALUES (:id_unidad, :tipo, :fecha_inicio, :fecha_fin, :fecha_vencimiento, :monto, :estado, :num_periodos, :comentarios, :id_factura, :periodo_pago, :tarjeta_sim)");
        
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

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = "Servicio guardado exitosamente";
        } else {
            $response['success'] = false;
            $response['message'] = "Error al guardar el servicio";
            $response['error_info'] = $stmt->errorInfo();
        }
    } catch (PDOException $e) {
        $response['success'] = false;
        $response['message'] = "Error de base de datos: " . $e->getMessage();
    }
} else {
    $response['success'] = false;
    $response['message'] = "Faltan datos requeridos (id_unidad, tipo, fecha_inicio, monto)";
}

echo json_encode($response);
?>