<?php
header('Content-Type: application/json');// Incluir tu conexión y la función de envío que ya configuramos antes
require_once '../conexion.php';
require_once 'enviar_notificacion.php'; 

// 1. Recibir el ID de la instalación que acabamos de crear/editar
$data = json_decode(file_get_contents("php://input"));

if (!isset($data->id_instalacion)) {
    echo json_encode(['success' => false, 'message' => 'Falta id_instalacion']);
    exit;
}

$id_instalacion = $data->id_instalacion;

try {
    $con = Conexion::conectar();

    // 2. CONSULTA SQL CON EL ENLACE POR CORREO
    // Obtenemos detalles de la instalación y el token del técnico
    $sql = "
        SELECT 
            i.fecha_instalacion,
            s.tipo AS nombre_servicio,
            u.nombre_unidad,
            t.nombre AS nombre_tecnico,
            usr.fcm_token
        FROM instalaciones i
        INNER JOIN servicios s ON i.id_servicio = s.id_servicio
        INNER JOIN unidades u ON s.id_unidad = u.id_unidad
        INNER JOIN tecnicos t ON i.id_tecnico = t.id_tecnico
        -- AQUÍ ESTÁ LA CLAVE: Unimos técnico con usuario por el CORREO
        INNER JOIN usuarios usr ON t.correo = usr.username 
        WHERE i.id_instalacion = :id
        AND usr.fcm_token IS NOT NULL
    ";

    $stmt = $con->prepare($sql);
    $stmt->execute([':id' => $id_instalacion]);
    $info = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($info) {
        // 3. Preparar la notificación
        $token = $info['fcm_token'];
        $titulo = "Nueva Asignación de Servicio";
        $cuerpo = "Hola {$info['nombre_tecnico']}, tienes una nueva instalación de {$info['nombre_servicio']} para la unidad '{$info['nombre_unidad']}' programada el {$info['fecha_instalacion']}.";

        // 4. Enviar
        $enviado = enviarNotificacion($token, $titulo, $cuerpo);

        if ($enviado) {
            echo json_encode(['success' => true, 'message' => 'Notificación enviada al técnico']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Fallo al enviar a Firebase']);
        }
    } else {
        // Puede pasar si el técnico no tiene usuario en la App o no tiene token aún
        echo json_encode(['success' => false, 'message' => 'No se encontró token para el técnico asignado']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>