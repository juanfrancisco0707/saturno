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
$input = file_get_contents("php://input");
file_put_contents('debug_delete.txt', "Input received: " . $input . "\n", FILE_APPEND);
$data = json_decode($input);

// Si no hay JSON válido, revisar si viene por $_GET (query params) o $_POST
if (empty($data->id_tecnico)) {
    if (!empty($_GET['id_tecnico'])) {
        $data = (object) ['id_tecnico' => $_GET['id_tecnico']];
        file_put_contents('debug_delete.txt', "Using GET data: " . $_GET['id_tecnico'] . "\n", FILE_APPEND);
    } elseif (!empty($_POST['id_tecnico'])) {
        $data = (object) ['id_tecnico' => $_POST['id_tecnico']];
        file_put_contents('debug_delete.txt', "Using POST data: " . $_POST['id_tecnico'] . "\n", FILE_APPEND);
    }
}

// Log decoded data
file_put_contents('debug_delete.txt', "Decoded data: " . print_r($data, true) . "\n", FILE_APPEND);


// Check if data is not empty
if (!empty($data->id_tecnico)) {
    // Prepare the SQL statement to delete the technician
    $sentencia = $db->prepare("DELETE FROM tecnicos WHERE id_tecnico = :id_tecnico");

    // Bind the data
    $sentencia->bindParam(':id_tecnico', $data->id_tecnico);

    // Execute the statement
    if ($sentencia->execute()) {
        // Check if any row was affected
        if ($sentencia->rowCount() > 0) {
            // Success response
            echo json_encode(array('mensaje' => 'Técnico eliminado correctamente'));
        } else {
            // No row found with the given ID
            echo json_encode(array('error' => 'No se encontró ningun técnico con el ID proporcionado'));
        }
    } else {
        // Error response
        echo json_encode(array('error' => 'Error al eliminar el técnico'));
    }
} else {
    // Incomplete data response
    echo json_encode(array(
        'error' => 'ID de técnico no proporcionado',
        'received_input' => $input,
        'received_get' => $_GET,
        'received_post' => $_POST
    ));
}
?>