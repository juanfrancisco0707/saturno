<?php
header('Content-Type: application/json');
require_once '../conexion.php';

$id_instalacion = $_GET['id'] ?? null;

if (!$id_instalacion) {
    echo json_encode(['success' => false, 'message' => 'ID de instalación no proporcionado.']);
    exit;
}

try {
    $con = Conexion::conectar();

    // Esta consulta une todas las tablas para obtener la información en un solo viaje
    $sql = "
        SELECT
            i.id_instalacion,
            i.fecha_instalacion,
            i.componentes_instalados,
            i.estado,
            i.comentarios,
            s.id_servicio,
            u.id_unidad,
            u.nombre_unidad,
            c.id_cliente,
            c.nombre AS nombre_cliente,
            t.id_tecnico
        FROM instalaciones i
        JOIN servicios s ON i.id_servicio = s.id_servicio
        JOIN unidades u ON s.id_unidad = u.id_unidad
        JOIN clientes c ON u.id_cliente = c.id_cliente
        JOIN tecnicos t ON i.id_tecnico = t.id_tecnico
        WHERE i.id_instalacion = :id_instalacion
    ";

    $stmt = $con->prepare($sql);
    $stmt->execute([':id_instalacion' => $id_instalacion]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($resultado) {
        echo json_encode(['success' => true, 'data' => $resultado]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se encontraron detalles para la instalación.']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error DB: ' . $e->getMessage()]);
}
?>