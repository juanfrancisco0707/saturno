<?php
header('Content-Type: application/json');

require_once '../conexion.php';

try {
    $conexion = Conexion::conectar();
    $stmt = $conexion->prepare("SELECT id_servicio, id_unidad, tipo, fecha_inicio, fecha_fin, fecha_vencimiento, monto, estado, num_periodos, comentarios, id_factura, creado_en, actualizado_en, periodo_pago, tarjeta_sim FROM servicios ORDER BY fecha_inicio DESC");
    $stmt->execute();

    $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($servicios);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al consultar los servicios: ' . $e->getMessage()]);
}
