<?php
// Set headers to allow cross-origin requests and specify JSON content type
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// Include the database connection file
require_once('../conexion.php');

try {
    // Leer el cuerpo de la solicitud
    $data = json_decode(file_get_contents("php://input"));

    // Get the ID from the JSON data
    if (!isset($data->id)) {
        throw new Exception("ID de unidad no proporcionado");
    }

    $id = $data->id;

    // Get the database connection
    $db = Conexion::conectar();

    // Prepare the SQL statement to delete the unit
    $sentencia = $db->prepare("DELETE FROM unidades WHERE id = :id");
    $sentencia->bindParam(':id', $id, PDO::PARAM_INT);

    // Execute the statement
    if ($sentencia->execute()) {
        // Check if any row was affected
        if ($sentencia->rowCount() > 0) {
            // Success response
            echo json_encode(array('mensaje' => 'Unidad eliminada correctamente'));
        } else {
            // No row found with the given ID
            echo json_encode(array('error' => 'No se encontró ninguna unidad con el ID proporcionado'));
        }
    } else {
        // Error executing the statement
        throw new Exception("Error al eliminar la unidad");
    }
} catch (Exception $e) {
    // Error response
    echo json_encode(array('error' => $e->getMessage()));
}
?>