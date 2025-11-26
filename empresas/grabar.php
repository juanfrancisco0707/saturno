<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

require_once('../conexion.php');

$response = array();

try {
    $db = Conexion::conectar();
    $json_data = file_get_contents("php://input");
    $data = json_decode($json_data);

    if (
        isset($data->nombre) && !empty($data->nombre) &&
        isset($data->direccion) &&
        isset($data->telefono) &&
        isset($data->correo) &&
        isset($data->rfc) &&
        isset($data->representante) &&
        isset($data->folio_factura)
    ) {
        $sentencia = $db->prepare("INSERT INTO empresa (nombre, direccion, telefono, correo, rfc, representante, folio_factura) VALUES (:nombre, :direccion, :telefono, :correo, :rfc, :representante, :folio_factura)");

        $sentencia->bindParam(':nombre', $data->nombre);
        $sentencia->bindParam(':direccion', $data->direccion);
        $sentencia->bindParam(':telefono', $data->telefono);
        $sentencia->bindParam(':correo', $data->correo);
        $sentencia->bindParam(':rfc', $data->rfc);
        $sentencia->bindParam(':representante', $data->representante);
        $sentencia->bindParam(':folio_factura', $data->folio_factura);

        if ($sentencia->execute()) {
            $response['success'] = true;
            $response['message'] = "Empresa creada correctamente";
        } else {
            $response['success'] = false;
            $response['message'] = "Error al insertar en la base de datos";
        }
    } else {
        $response['success'] = false;
        $response['message'] = "Datos incompletos. Recibido: " . $json_data;
    }

} catch (Exception $e) {
    // Capturamos cualquier error del servidor y lo enviamos como JSON en lugar de un error 500
    $response['success'] = false;
    $response['message'] = "Excepción en el servidor: " . $e->getMessage();
}

echo json_encode($response);
?>