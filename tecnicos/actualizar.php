<?php
// Set headers to allow cross-origin requests and specify JSON content type
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: PUT');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

// Include the database connection file
require_once('../conexion.php');

// Get the database connection
$db = Conexion::conectar();

// Leer el cuerpo de la solicitud
$data = json_decode(file_get_contents("php://input"));

// Check if data is not empty
if (
    !empty($data->id_tecnico) &&
    !empty($data->nombre) &&
    !empty($data->direccion) &&
    !empty($data->telefono) &&
    !empty($data->correo) &&
    !empty($data->foto)
) {
    // Prepare the SQL statement to update the technician
    $sentencia = $db->prepare("UPDATE tecnicos SET 
        nombre = :nombre, 
        direccion = :direccion, 
        telefono = :telefono, 
        correo = :correo, 
        foto = :foto 
        WHERE id_tecnico = :id_tecnico");

    // Bind the data
    $sentencia->bindParam(':id_tecnico', $data->id_tecnico);
    $sentencia->bindParam(':nombre', $data->nombre);
    $sentencia->bindParam(':direccion', $data->direccion);
    $sentencia->bindParam(':telefono', $data->telefono);
    $sentencia->bindParam(':correo', $data->correo);
    $sentencia->bindParam(':foto', $data->foto);

    // Execute the statement
    if ($sentencia->execute()) {
        // Success response
        echo json_encode(array('mensaje' => 'Técnico actualizado correctamente'));
    } else {
        // Error response
        echo json_encode(array('error' => 'Error al actualizar el técnico'));
    }
} else {
    // Incomplete data response
    echo json_encode(array('error' => 'Datos incompletos'));
}
?>