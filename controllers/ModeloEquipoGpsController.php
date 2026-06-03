<?php
require_once __DIR__ . '/../models/ModeloEquipoGps.php';

class ModeloEquipoGpsController {

    public function index() {
        try {
            $modelos = ModeloEquipoGps::getAll();
            echo json_encode($modelos);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al consultar los modelos de equipos GPS: '
             . $e->getMessage()]);
        }
    }

    public function store() {
        $json_data = file_get_contents("php://input");
        $data = json_decode($json_data, true);

        if (isset($data['nombre_modelo']) && !empty(trim($data['nombre_modelo']))) {
            
            $modelData = [
                'nombre_modelo' => trim($data['nombre_modelo']),
                'marca' => isset($data['marca']) ? trim($data['marca']) : null,
                'descripcion' => isset($data['descripcion']) ? trim($data['descripcion']) : null
            ];

            try {
                $id = ModeloEquipoGps::create($modelData);
                echo json_encode([
                    'success' => true, 
                    'id' => $id, 
                    'message' => "Modelo de equipo GPS guardado exitosamente"
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false, 
                    'message' => "Error de base de datos: " . $e->getMessage()
                ]);
            }

        } else {
            echo json_encode([
                'success' => false, 
                'message' => "El nombre del modelo es requerido"
            ]);
        }
    }

    public function update() {
        $json_data = file_get_contents("php://input");
        $data = json_decode($json_data, true);
        
        if (!isset($data['id_modelo_gps'])) {
             echo json_encode(['success' => false, 'message' => "Falta el ID del modelo"]);
             return;
        }

        $id = $data['id_modelo_gps'];
        unset($data['id_modelo_gps']);

        try {
            if (ModeloEquipoGps::update($id, $data)) {
                 echo json_encode(['success' => true, 'message' => "Modelo actualizado exitosamente"]);
            } else {
                 echo json_encode(['success' => false, 'message' => "No se pudo actualizar el modelo"]);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => "Error de base de datos: " 
            . $e->getMessage()]);
        }
    }

    public function delete() {
        $json_data = file_get_contents("php://input");
        $data = json_decode($json_data, true); 
        $id = isset($data['id_modelo_gps']) ? $data['id_modelo_gps'] : null;
        if (!$id) {
             echo json_encode(['error' => "ID de modelo no proporcionado", 'success' => false]); 
             return; 
        }
        try {
            if (ModeloEquipoGps::delete($id)) {
                echo json_encode(['mensaje' => 'Modelo eliminado correctamente', 'success' => true]);
            } else {
                echo json_encode(['error' => 'No se encontró ningún modelo con 
                el ID proporcionado', 'success' => false]);
            }
        } catch (Exception $e) {
             echo json_encode(['error' => $e->getMessage(), 'success' => false]);
        }
    }
}
?>
