<?php
header('Content-Type: application/json');

$response = array('success' => false, 'message' => '', 'path' => '');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['image'])) {
        $file_name = $_FILES['image']['name'];
        $file_tmp = $_FILES['image']['tmp_name'];
        $temp_ext = explode('.', $file_name);
        $file_ext = strtolower(end($temp_ext));
        
        $extensions = array("jpeg", "jpg", "png");

        if (in_array($file_ext, $extensions)) {
            // Crear nombre único para evitar duplicados
            $new_file_name = uniqid('evidencia_', true) . '.' . $file_ext;
            
            // Carpeta de destino (asegúrate de crear esta carpeta 'uploads' con permisos 777)
            $directory = "uploads/";
            if (!file_exists($directory)) {
                mkdir($directory, 0777, true);
            }
            
            $upload_path = $directory . $new_file_name;

            if (move_uploaded_file($file_tmp, $upload_path)) {
                $response['success'] = true;
                $response['message'] = 'Imagen subida correctamente';
                // Esta es la ruta que guardaremos en la Base de Datos
                $response['path'] = "evidencias/" . $upload_path; 
            } else {
                $response['message'] = 'Error al mover el archivo al directorio de destino';
            }
        } else {
            $response['message'] = 'Extensión no permitida, solo JPG, JPEG y PNG';
        }
    } else {
        $response['message'] = 'No se recibió ninguna imagen';
    }
} else {
    $response['message'] = 'Método no permitido';
}

echo json_encode($response);
?>