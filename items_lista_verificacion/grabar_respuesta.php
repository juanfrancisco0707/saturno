<?php
header('Content-Type: application/json');
require_once '../conexion.php';

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->id_instalacion) || !isset($data->id_item)) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

$id_instalacion = $data->id_instalacion;
$id_item = $data->id_item;
$verificado = $data->verificado ? 1 : 0;
$comentarios = $data->comentarios ?? '';

try {
    $con = Conexion::conectar();

    // 1. Verificar si ya existe el registro para actualizarlo o crearlo
    // OJO: Usamos el nombre en PLURAL: listas_verificacion_instalacion
    $checkSql = "SELECT id_lista_verificacion FROM listas_verificacion_instalacion 
                 WHERE id_instalacion = :id_inst AND id_item = :id_item";
    
    $stmtCheck = $con->prepare($checkSql);
    $stmtCheck->execute([':id_inst' => $id_instalacion, ':id_item' => $id_item]);
    $row = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        // Actualizar
        $sql = "UPDATE listas_verificacion_instalacion 
                SET verificado = :verificado, comentarios = :comentarios, actualizado_en = NOW() 
                WHERE id_lista_verificacion = :id";
        $stmt = $con->prepare($sql);
        $stmt->execute([
            ':verificado' => $verificado,
            ':comentarios' => $comentarios,
            ':id' => $row['id_lista_verificacion']
        ]);
    } else {
        // Insertar nuevo
        $sql = "INSERT INTO listas_verificacion_instalacion (id_instalacion, id_item, verificado, comentarios) 
                VALUES (:id_inst, :id_item, :verificado, :comentarios)";
        $stmt = $con->prepare($sql);
        $stmt->execute([
            ':id_inst' => $id_instalacion,
            ':id_item' => $id_item,
            ':verificado' => $verificado,
            ':comentarios' => $comentarios
        ]);
    }

    echo json_encode(['success' => true, 'message' => 'Guardado correctamente']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error DB: ' . $e->getMessage()]);
}
?>