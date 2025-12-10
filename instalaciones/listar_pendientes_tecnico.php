<?php
header('Content-Type: application/json');
require_once '../conexion.php';

$id_usuario = $_GET['id_usuario'] ?? null;

if (!$id_usuario) {
    echo json_encode([]);
    exit;
}

try {
    $conexion = Conexion::conectar();
    
    // Esta consulta es "mágica":
    // 1. Une instalaciones con tecnicos.
    // 2. Une tecnicos con usuarios a través del correo (como acordamos).
    // 3. Filtra por el ID del usuario logueado.
    // 4. Filtra que el estado NO sea 'completada'.
    
    $sql = "
        SELECT 
            i.id_instalacion,
            i.fecha_instalacion,
            i.estado,
            i.componentes_instalados AS componentes,
            i.comentarios,
            s.tipo AS nombreServicio,
            u.nombre_unidad AS nombreUnidad,
            t.nombre AS nombreTecnico
        FROM instalaciones i
        JOIN servicios s ON i.id_servicio = s.id_servicio
        JOIN unidades u ON s.id_unidad = u.id_unidad
        JOIN tecnicos t ON i.id_tecnico = t.id_tecnico
        JOIN usuarios usr ON t.correo = usr.username
        WHERE usr.id = :id_usuario
        AND i.estado != 'completada'
        ORDER BY i.fecha_instalacion ASC
    ";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([':id_usuario' => $id_usuario]);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($resultados);

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>