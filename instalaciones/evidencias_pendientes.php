<?php
header('Content-Type: application/json');
require_once '../conexion.php';$id_instalacion = $_GET['id_instalacion'] ?? null;

if (!$id_instalacion) {
    echo json_encode([]);
    exit;
}

try {
    $conexion = Conexion::conectar();
    
    // CORRECCIÓN: Se cambió 'lista_...' por 'listas_verificacion_instalacion' (plural)
    $sql = "
        SELECT 
            lv.id_lista_verificacion AS id, 
            ilv.descripcion AS nombre_item
        FROM listas_verificacion_instalacion lv
        INNER JOIN items_lista_verificacion ilv ON lv.id_item = ilv.id_item
        LEFT JOIN evidencias e ON lv.id_lista_verificacion = e.id_lista_verificacion
        WHERE lv.id_instalacion = :id
        AND lv.verificado = 1 -- Solo los items marcados como 'Sí'
        AND e.id_evidencia IS NULL -- Que NO tengan evidencia subida aún
    ";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([':id' => $id_instalacion]);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($resultados);

} catch (PDOException $e) {
    http_response_code(500);
    // Esto enviará el error exacto al Logcat si algo más falla
    echo json_encode(['error' => 'Error DB: ' . $e->getMessage()]);
}
?>