<?php
require_once __DIR__ . '/../models/Unidad.php';

class UnidadController {

    public function index() {
        try {
            $unidades = Unidad::getAll();
            echo json_encode($unidades);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al consultar las unidades: '
             . $e->getMessage()]);
        }
    }

    public function store() {
        $json_data = file_get_contents("php://input");
        $data = json_decode($json_data, true);
        
        $response = array();

        if (isset($data['id_cliente']) && isset($data['nombre_unidad']) 
            && isset($data['idcategoria'])) {
            
            // Prepare data with defaults
            $unitData = [
                'id_cliente' => $data['id_cliente'],
                'nombre_unidad' => $data['nombre_unidad'],
                'idcategoria' => $data['idcategoria'],
                'fecha_instalacion' => isset($data['fecha_instalacion']) 
                ? $data['fecha_instalacion'] : null,
                'comentarios' => isset($data['comentarios']) ? $data['comentarios'] : null,
                'estatus' => isset($data['estatus']) ? $data['estatus'] : 'activa',
                'tarjeta_sim' => isset($data['tarjeta_sim']) ? $data['tarjeta_sim'] : null,
                'iccid' => isset($data['iccid']) ? $data['iccid'] : null,
                'id_modelo_gps' => (!empty($data['id_modelo_gps'])) ? $data['id_modelo_gps'] : null
            ];

            // Validation
            $allowed_estatus = ['activa', 'standby', 'baja'];
            if (!in_array($unitData['estatus'], $allowed_estatus)) {
                echo json_encode(['success' => false, 'message' => "Valor de estatus no válido."]);
                return;
            }
            try {
                $id = Unidad::create($unitData);
                echo json_encode([
                    'success' => true, 
                    'id' => $id, 
                    'message' => "Unidad guardada exitosamente"
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
                'message' => "Faltan datos requeridos (id_cliente, nombre_unidad, idcategoria)"
            ]);
        }
    }

    public function update() {
        $json_data = file_get_contents("php://input");
        $data = json_decode($json_data, true);
        
        if (!isset($data['id_unidad'])) {
             echo json_encode(['success' => false, 'message' => "Falta el ID de la unidad"]);
             return;
        }

        $id = $data['id_unidad'];
        unset($data['id_unidad']); // Remove ID from data to update

        // Validate estatus if present
        if (isset($data['estatus'])) {
             $allowed_estatus = ['activa', 'standby', 'baja'];
             if (!in_array($data['estatus'], $allowed_estatus)) {
                echo json_encode(['success' => false, 'message' => "Valor de estatus no válido."]);
                return;
             }
        }

        // Convert empty id_modelo_gps to null for FK
        if (isset($data['id_modelo_gps']) && empty($data['id_modelo_gps'])) {
            $data['id_modelo_gps'] = null;
        }

        try {
            if (Unidad::update($id, $data)) {
                 echo json_encode(['success' => true, 'message' => "Unidad actualizada exitosamente"]);
            } else {
                 echo json_encode(['success' => false, 'message' => "No se pudo actualizar la unidad"]);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => "Error de base de datos: " 
            . $e->getMessage()]);
        }
    }

    public function delete() {
        $json_data = file_get_contents("php://input");
        $data = json_decode($json_data, true); 
        $id_unidad = isset($data['id_unidad']) ? $data['id_unidad'] : null;
        if (!$id_unidad) {
             echo json_encode(['error' => "ID de unidad no proporcionado", 'success' => false]); 
             return; 
        }
        try {
            if (Unidad::delete($id_unidad)) {
                echo json_encode(['mensaje' => 'Unidad eliminada correctamente', 'success' => true]);
            } else {
                echo json_encode(['error' => 'No se encontró ninguna unidad con 
                el ID proporcionado', 'success' => false]);
            }
        } catch (Exception $e) {
             echo json_encode(['error' => $e->getMessage(), 'success' => false]);
        }
    }
}
?>
