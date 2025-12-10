<?php
require_once '../conexion.php';
require_once 'enviar_notificacion.php';

// Este script está diseñado para ser ejecutado por un CRON JOB (tarea programada)
// Ej: Ejecutar una vez al día a las 8:00 AM

echo "Iniciando proceso de verificación de vencimientos...\n";

$db = Conexion::conectar();

// 1. Definir qué fecha buscamos (ej: servicios que vencen MAÑANA)
$fecha_busqueda = date('Y-m-d', strtotime('+1 day'));

// 2. Consulta SQL para obtener servicios y tokens
// IMPORTANTE: Debes ajustar el JOIN con 'usuarios' según cómo relaciones tus tablas.
// Aquí asumo un ejemplo donde 'clientes' tiene el mismo 'email' que 'usuarios', 
// o si tienes un campo 'id_usuario' en la tabla 'clientes'.

$sql = "
    SELECT 
        s.id_servicio,
        s.tipo,
        s.fecha_vencimiento,
        u.nombre_unidad,
        usr.fcm_token,
        usr.username
    FROM servicios s
    INNER JOIN unidades u ON s.id_unidad = u.id_unidad
    INNER JOIN clientes c ON u.id_cliente = c.id_cliente
    -- AJUSTA ESTA LÍNEA SEGÚN TU RELACIÓN REAL:
    INNER JOIN usuarios usr ON c.email = usr.username 
    WHERE s.fecha_vencimiento = :fecha
    AND usr.fcm_token IS NOT NULL
    AND usr.fcm_token != ''
";

try {
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':fecha', $fecha_busqueda);
    $stmt->execute();
    
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $cantidad = count($resultados);
    
    echo "Se encontraron {$cantidad} servicios que vencen el {$fecha_busqueda}.\n";

    foreach ($resultados as $fila) {
        $token = $fila['fcm_token'];
        $titulo = "Recordatorio de Vencimiento";
        $cuerpo = "Hola, tu servicio de {$fila['tipo']} para la unidad '{$fila['nombre_unidad']}' vence mañana ({$fila['fecha_vencimiento']}). Por favor realiza tu pago.";

        echo "Enviando notificación a usuario {$fila['username']} (Servicio ID: {$fila['id_servicio']})... ";
        
        $enviado = enviarNotificacion($token, $titulo, $cuerpo);
        
        if ($enviado) {
            echo "[OK]\n";
        } else {
            echo "[ERROR]\n";
        }
    }

} catch (PDOException $e) {
    echo "Error en la base de datos: " . $e->getMessage();
}

echo "Proceso finalizado.\n";
?>
