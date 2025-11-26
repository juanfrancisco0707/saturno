<?php
// Configuración de headers para permitir el acceso desde la app
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: PUT');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

require_once('../conexion.php');

$response = array();

try {
    $db = Conexion::conectar();
    
    // Leer el cuerpo de la solicitud (JSON)
    $json_data = file_get_contents("php://input");
    $data = json_decode($json_data);

    if (
        isset($data->id) &&
        isset($data->nombre) &&
        isset($data->direccion) &&
        isset($data->telefono) &&
        isset($data->correo) &&
        isset($data->rfc) &&
        isset($data->representante) &&
        isset($data->folio_factura)
    ) {
        $sentencia = $db->prepare("UPDATE empresa SET nombre = :nombre, direccion = :direccion, telefono = :telefono, correo = :correo, rfc = :rfc, representante = :representante, folio_factura = :folio_factura WHERE id = :id");

        $sentencia->bindParam(':id', $data->id);
        $sentencia->bindParam(':nombre', $data->nombre);
        $sentencia->bindParam(':direccion', $data->direccion);
        $sentencia->bindParam(':telefono', $data->telefono);
        $sentencia->bindParam(':correo', $data->correo);
        $sentencia->bindParam(':rfc', $data->rfc);
        $sentencia->bindParam(':representante', $data->representante);
        $sentencia->bindParam(':folio_factura', $data->folio_factura);

        if ($sentencia->execute()) {
            $response['success'] = true;
            $response['message'] = "Empresa actualizada correctamente";
        } else {
            $response['success'] = false;
            $response['message'] = "Error al ejecutar la actualización";
        }
    } else {
        $response['success'] = false;
        $response['message'] = "Datos incompletos. Asegúrate de enviar todos los campos.";
    }

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = "Excepción en el servidor: " . $e->getMessage();
}

echo json_encode($response);
?>