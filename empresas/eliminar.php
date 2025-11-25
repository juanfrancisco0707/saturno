<?php
// Set headers to allow cross-origin requests and specify JSON content type
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: DELETE');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

// Include the database connection file
require_once('../conexion.php');

// Get the database connection
$db = Conexion::conectar();

// Leer el cuerpo de la solicitud
$data = json_decode(file_get_contents("php://input"));

// Check if data is not empty
if (!empty($data->id)) {
    // Prepare the SQL statement to delete the company
    $sentencia = $db->prepare("DELETE FROM empresa WHERE id = :id");

    // Bind the data
    $sentencia->bindParam(':id', $data->id);

    // Execute the statement
    if ($sentencia->execute()) {
        // Check if any row was affected
        if ($sentencia->rowCount() > 0) {
            // Success response
            echo json_encode(array('mensaje' => 'Empresa eliminada correctamente'));
        } else {
            // No row found with the given ID
            echo json_encode(array('error' => 'No se encontró ninguna empresa con el ID proporcionado'));
        }
    } else {
        // Error response
        echo json_encode(array('error' => 'Error al eliminar la empresa'));
    }
} else {
    // Incomplete data response
    echo json_encode(array('error' => 'ID de empresa no proporcionado'));
}
?>
