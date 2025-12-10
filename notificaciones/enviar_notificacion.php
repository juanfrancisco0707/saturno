<?php
require_once '../vendor/autoload.php'; // Asegúrate de que la ruta a vendor sea correcta

function obtenerAccessToken($rutaCredenciales) {
    $client = new Google_Client();
    $client->setAuthConfig($rutaCredenciales);
    $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
    
    $client->useApplicationDefaultCredentials();
    $token = $client->fetchAccessTokenWithAssertion();
    
    return $token['access_token'];
}

function enviarNotificacion($tokenDispositivo, $titulo, $cuerpo) {
    // 1. Ruta a tu archivo JSON descargado en el Paso 1
    $rutaCredenciales = '../includes/ksaturno-f5f23-firebase-adminsdk-fbsvc-08a6aae6b3.json'; 
    
    // 2. ID de tu proyecto (lo encuentras en el JSON o en la consola de Firebase)
    $projectId = 'ksaturno-f5f23'; // Reemplaza con tu Project ID real

    try {
        // Obtener token de seguridad
        $accessToken = obtenerAccessToken($rutaCredenciales);

        // 3. Configurar la URL de la API v1 de FCM
        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        // 4. Construir el mensaje
        $mensaje = [
            'message' => [
                'token' => $tokenDispositivo, // El token del celular destino (desde tu BD)
                'notification' => [
                    'title' => $titulo,
                    'body'  => $cuerpo
                ],
                'data' => [ // Datos extra opcionales para que tu app procese
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'tipo' => 'renovacion',
                    'id_cliente' => '123'
                ]
            ]
        ];

        // 5. Configurar la petición HTTP (CURL)
        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($mensaje));

        $resultado = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            throw new Exception('Error Curl: ' . curl_error($ch));
        }
        
        curl_close($ch);

        // Verificar respuesta
        if ($httpCode == 200) {
            echo "Notificación enviada con éxito: " . $resultado;
            return true;
        } else {
            echo "Error al enviar. Código: $httpCode. Respuesta: " . $resultado;
            return false;
        }

    } catch (Exception $e) {
        echo "Excepción: " . $e->getMessage();
        return false;
    }
}

// --- EJEMPLO DE USO ---

// 1. Obtén el token del usuario desde tu base de datos
// $tokenDestino = "cEw8s... (Token guardado en BD desde la App Android)";

// 2. Llama a la función
// enviarNotificacion($tokenDestino, "Recordatorio", "Tu servicio vence mañana.");

?>