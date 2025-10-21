<?php
header('Content-Type: application/json');
require_once '../conexion.php';

// Obtener datos JSON del cuerpo de la petición
$json_data = file_get_contents("php://input");
$data = json_decode($json_data, true);

$response = array();

if (isset($data['id_unidad'])) {
    $id_unidad = $data['id_unidad'];

    // Campos a actualizar
    $id_cliente = isset($data['id_cliente']) ? $data['id_cliente'] : null;
    $nombre_unidad = isset($data['nombre_unidad']) ? $data['nombre_unidad'] : null;
    $idcategoria = isset($data['idcategoria']) ? $data['idcategoria'] : null;
    $fecha_instalacion = isset($data['fecha_instalacion']) ? $data['fecha_instalacion'] : null;
    $ultima_fecha_instalacion = isset($data['ultima_fecha_instalacion']) ? $data['ultima_fecha_instalacion'] : null;
    $comentarios = isset($data['comentarios']) ? $data['comentarios'] : null;
    $estatus = isset($data['estatus']) ? $data['estatus'] : null;
    $tarjeta_sim = isset($data['tarjeta_sim']) ? $data['tarjeta_sim'] : null;

    try {
        $db = Conexion::conectar();
        
        // Construir la consulta dinámicamente
        $update_fields = array();
        if ($id_cliente !== null) $update_fields[] = "id_cliente = :id_cliente";
        if ($nombre_unidad !== null) $update_fields[] = "nombre_unidad = :nombre_unidad";
        if ($idcategoria !== null) $update_fields[] = "idcategoria = :idcategoria";
        if ($fecha_instalacion !== null) $update_fields[] = "fecha_instalacion = :fecha_instalacion";
        if ($ultima_fecha_instalacion !== null) $update_fields[] = "ultima_fecha_instalacion = :ultima_fecha_instalacion";
        if ($comentarios !== null) $update_fields[] = "comentarios = :comentarios";
        if ($estatus !== null) $update_fields[] = "estatus = :estatus";
        if ($tarjeta_sim !== null) $update_fields[] = "tarjeta_sim = :tarjeta_sim";

        if (count($update_fields) > 0) {
            $query = "UPDATE unidades SET " . implode(', ', $update_fields) . " WHERE id_unidad = :id_unidad";
            $stmt = $db->prepare($query);

            // Vincular parámetros
            if ($id_cliente !== null) $stmt->bindParam(':id_cliente', $id_cliente);
            if ($nombre_unidad !== null) $stmt->bindParam(':nombre_unidad', $nombre_unidad);
            if ($idcategoria !== null) $stmt->bindParam(':idcategoria', $idcategoria);
            if ($fecha_instalacion !== null) $stmt->bindParam(':fecha_instalacion', $fecha_instalacion);
            if ($ultima_fecha_instalacion !== null) $stmt->bindParam(':ultima_fecha_instalacion', $ultima_fecha_instalacion);
            if ($comentarios !== null) $stmt->bindParam(':comentarios', $comentarios);
            if ($estatus !== null) $stmt->bindParam(':estatus', $estatus);
            if ($tarjeta_sim !== null) $stmt->bindParam(':tarjeta_sim', $tarjeta_sim);
            $stmt->bindParam(':id_unidad', $id_unidad);

            if ($stmt->execute()) {
                if ($stmt->rowCount() > 0) {
                    $response['success'] = true;
                    $response['message'] = "Unidad actualizada exitosamente";
                } else {
                    $response['success'] = false;
                    $response['message'] = "No se encontró la unidad o no hubo cambios";
                }
            } else {
                $response['success'] = false;
                $response['message'] = "Error al actualizar la unidad";
            }
        } else {
            $response['success'] = false;
            $response['message'] = "No se proporcionaron datos para actualizar";
        }
    } catch (PDOException $e) {
        $response['success'] = false;
        $response['message'] = "Error de base de datos: " . $e->getMessage();
    }
} else {
    $response['success'] = false;
    $response['message'] = "Falta el ID de la unidad";
}

echo json_encode($response);
?>
