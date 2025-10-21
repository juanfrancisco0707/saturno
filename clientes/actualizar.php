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
    !empty($data->id_cliente) &&
    !empty($data->nombre) &&
    !empty($data->direccion) &&
    !empty($data->telefono) &&
    !empty($data->email) &&
    !empty($data->representante)
) {
    // Prepare the SQL statement to update the client
    $sentencia = $db->prepare("UPDATE clientes SET nombre = :nombre, direccion = :direccion, telefono = :telefono, email = :email, representante = :representante, actualizado_en = current_timestamp() WHERE id_cliente = :id_cliente");

    // Bind the data
    $sentencia->bindParam(':id_cliente', $data->id_cliente);
    $sentencia->bindParam(':nombre', $data->nombre);
    $sentencia->bindParam(':direccion', $data->direccion);
    $sentencia->bindParam(':telefono', $data->telefono);
    $sentencia->bindParam(':email', $data->email);
    $sentencia->bindParam(':representante', $data->representante);

    // Execute the statement
    if ($sentencia->execute()) {
        // Success response
        echo json_encode(array('mensaje' => 'Cliente actualizado correctamente'));
    } else {
        // Error response
        echo json_encode(array('error' => 'Error al actualizar el cliente'));
    }
} else {
    // Incomplete data response
    echo json_encode(array('error' => 'Datos incompletos'));
}
?>