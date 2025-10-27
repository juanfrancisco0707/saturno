<?php
// Set headers to allow cross-origin requests and specify JSON content type
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

// Include the database connection file
require_once('../conexion.php');

// Get the database connection
$db = Conexion::conectar();

// Leer el cuerpo de la solicitud
$data = json_decode(file_get_contents("php://input"));

// Check if data is not empty
if (
    !empty($data->nombre) &&
    !empty($data->direccion) &&
    !empty($data->telefono) &&
    !empty($data->correo) &&
    !empty($data->foto)
) {
   
    // Prepare the SQL statement to insert the client
    $sentencia = $db->prepare("INSERT INTO tecnicos 
    (nombre, direccion, telefono, correo, foto) 
    VALUES (:nombre, :direccion, :telefono, :correo, :foto)");

    // Bind the data
    $sentencia->bindParam(':nombre', $data->nombre);
    $sentencia->bindParam(':direccion', $data->direccion);
    $sentencia->bindParam(':telefono', $data->telefono);
    $sentencia->bindParam(':correo', $data->correo);
    $sentencia->bindParam(':foto', $data->foto);

    // Execute the statement
    if ($sentencia->execute()) {
        // Success response
        echo json_encode(array('mensaje' => 'Técnico creado correctamente'));
    } else {
        // Error response
        echo json_encode(array('error' => 'Error al crear el técnico'));
    }
} else {
    // Incomplete data response
    echo json_encode(array('error' => 'Datos incompletos'));
}
?>