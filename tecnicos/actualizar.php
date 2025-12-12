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
$input = file_get_contents("php://input");
file_put_contents('debug_update.txt', "Input received: " . $input . "\n", FILE_APPEND);
$data = json_decode($input);

// Si se recibe como form-data (POST normal), intenta usar $_POST
if (empty($data) && !empty($_POST)) {
    $data = (object) $_POST;
    file_put_contents('debug_update.txt', "Using _POST data: " . print_r($data, true) . "\n", FILE_APPEND);
}

// Log decoded data
file_put_contents('debug_update.txt', "Decoded data: " . print_r($data, true) . "\n", FILE_APPEND);


// Check if data is not empty
if (
    !empty($data->id_tecnico) &&
    !empty($data->nombre) &&
    !empty($data->direccion) &&
    !empty($data->telefono) &&
    !empty($data->correo) 
) {
    // Prepare the SQL statement to update the technician
    $sentencia = $db->prepare("UPDATE tecnicos SET 
        nombre = :nombre, 
        direccion = :direccion, 
        telefono = :telefono, 
        correo = :correo
        WHERE id_tecnico = :id_tecnico");

    // Bind the data
    $sentencia->bindParam(':id_tecnico', $data->id_tecnico);
    $sentencia->bindParam(':nombre', $data->nombre);
    $sentencia->bindParam(':direccion', $data->direccion);
    $sentencia->bindParam(':telefono', $data->telefono);
    $sentencia->bindParam(':correo', $data->correo);
    

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
    $missing = [];
    if (empty($data->id_tecnico)) $missing[] = 'id_tecnico';
    if (empty($data->nombre)) $missing[] = 'nombre';
    if (empty($data->direccion)) $missing[] = 'direccion';
    if (empty($data->telefono)) $missing[] = 'telefono';
    if (empty($data->correo)) $missing[] = 'correo';
    
    echo json_encode(array(
        'error' => 'Datos incompletos',
        'missing' => $missing,
        'received' => $data,
        'raw_input' => $input
    ));
}
?>