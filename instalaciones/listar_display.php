<?php
header('Content-Type: application/json');
require_once '../conexion.php';

try {
    $conexion = Conexion::conectar();
    // Esta consulta une las 4 tablas y selecciona solo los campos necesarios
    $stmt = $conexion->prepare("
        SELECT
            i.id_instalacion,
            i.fecha_instalacion,
            i.estado,
            i.componentes_instalados AS componentes,
            i.comentarios,
            s.tipo AS nombreServicio,
            u.nombre_unidad AS nombreUnidad,
            t.nombre AS nombreTecnico
        FROM
            instalaciones i
        JOIN
            servicios s ON i.id_servicio = s.id_servicio
        JOIN
            unidades u ON s.id_unidad = u.id_unidad
        JOIN
            tecnicos t ON i.id_tecnico = t.id_tecnico
        ORDER BY
            i.id_instalacion DESC
    ");
    $stmt->execute();
    $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // La respuesta JSON debe coincidir con el modelo de datos InstalacionDisplay en Android
    echo json_encode($resultado);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al consultar las instalaciones: ' . $e->getMessage()]);
}