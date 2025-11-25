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
    !empty($data->id) &&
    !empty($data->nombre) &&
    !empty($data->direccion) &&
    !empty($data->telefono) &&
    !empty($data->correo) &&
    !empty($data->rfc) &&
    !empty($data->representante) &&
    isset($data->folio_factura)
) {
    // Prepare the SQL statement to update the company
    $sentencia = $db->prepare("UPDATE empresa SET nombre = :nombre, direccion = :direccion, telefono = :telefono, correo = :correo, rfc = :rfc, representante = :representante, folio_factura = :folio_factura WHERE id = :id");

    // Bind the data
    $sentencia->bindParam(':id', $data->id);
    $sentencia->bindParam(':nombre', $data->nombre);
    $sentencia->bindParam(':direccion', $data->direccion);
    $sentencia->bindParam(':telefono', $data->telefono);
    $sentencia->bindParam(':correo', $data->correo);
    $sentencia->bindParam(':rfc', $data->rfc);
    $sentencia->bindParam(':representante', $data->representante);
    $sentencia->bindParam(':folio_factura', $data->folio_factura);

    // Execute the statement
    if ($sentencia->execute()) {
        // Success response
        echo json_encode(array('mensaje' => 'Empresa actualizada correctamente'));
    } else {
        // Error response
        echo json_encode(array('error' => 'Error al actualizar la empresa'));
    }
} else {
    // Incomplete data response
    echo json_encode(array('error' => 'Datos incompletos'));
}
?>
