<?php
require_once __DIR__ . '/../models/Evidencia.php';

class EvidenciaController {

    public function indexByVerificacion() {
        if (!isset($_GET['id_lista_verificacion'])) {
            http_response_code(400);
            echo json_encode(['error' => 'id_lista_verificacion es requerido']);
            return;
        }
        $id = (int)$_GET['id_lista_verificacion'];
        try {
            $data = Evidencia::getByVerificacion($id);
            echo json_encode($data);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function store() {
        if (!isset($_POST['id_lista_verificacion']) || !isset($_FILES['archivo'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Datos o archivo incompletos']);
            return;
        }

        $id_lv = (int)$_POST['id_lista_verificacion'];
        $descripcion = $_POST['descripcion'] ?? '';
        $file = $_FILES['archivo'];

        // Directorio de subida (en saturno/evidencias/uploads/)
        $uploadDir = __DIR__ . '/../evidencias/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = 'evidencia_' . uniqid() . '.' . $ext;
        $targetFile = $uploadDir . $fileName;
        
        // La ruta que guardamos en BD es relativa al proyecto o absoluta según se prefiera.
        // Siguiendo el ejemplo del volcado: 'evidencias/uploads/...'
        $dbPath = 'evidencias/uploads/' . $fileName;

        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            try {
                $id = Evidencia::create($id_lv, $dbPath, $descripcion);
                echo json_encode(['success' => true, 'id' => $id, 'ruta' => $dbPath]);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Error BD: ' . $e->getMessage()]);
            }
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al mover el archivo subido']);
        }
    }

    public function delete() {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['id_evidencia'])) {
            http_response_code(400);
            echo json_encode(['error' => 'id_evidencia es requerido']);
            return;
        }
        $id = (int)$data['id_evidencia'];
        try {
            $ruta = Evidencia::delete($id);
            if ($ruta) {
                $fullPath = __DIR__ . '/../' . $ruta;
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
                echo json_encode(['success' => true, 'message' => 'Evidencia eliminada']);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Evidencia no encontrada']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
?>
