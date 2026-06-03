<?php
require_once __DIR__ . '/../models/ListaVerificacionInstalacion.php';

class ListaVerificacionInstalacionController {

    public function index() {
        try {
            $registros = ListaVerificacionInstalacion::getAll();
            echo json_encode($registros);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al consultar: ' . $e->getMessage()]);
        }
    }

    public function indexByInstalacion() {
        if (!isset($_GET['id_instalacion'])) {
            http_response_code(400);
            echo json_encode(['error' => 'El parámetro id_instalacion es requerido']);
            return;
        }
        $id = filter_var($_GET['id_instalacion'], FILTER_VALIDATE_INT);
        if ($id === false) {
            http_response_code(400);
            echo json_encode(['error' => 'id_instalacion debe ser un número entero']);
            return;
        }
        try {
            $registros = ListaVerificacionInstalacion::getByInstalacion($id);
            echo json_encode($registros);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al consultar: ' . $e->getMessage()]);
        }
    }

    public function show() {
        if (!isset($_GET['id_lista_verificacion'])) {
            http_response_code(400);
            echo json_encode(['error' => 'El parámetro id_lista_verificacion es requerido']);
            return;
        }
        $id = filter_var($_GET['id_lista_verificacion'], FILTER_VALIDATE_INT);
        if ($id === false) {
            http_response_code(400);
            echo json_encode(['error' => 'id_lista_verificacion debe ser un número entero']);
            return;
        }
        try {
            $registro = ListaVerificacionInstalacion::getById($id);
            if ($registro) {
                echo json_encode($registro);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Registro no encontrado']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al consultar: ' . $e->getMessage()]);
        }
    }

    public function storeRespuesta() {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['id_instalacion']) || !isset($data['id_item'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Se requieren id_instalacion e id_item']);
            return;
        }
        $id_instalacion = filter_var($data['id_instalacion'], FILTER_VALIDATE_INT);
        $id_item        = filter_var($data['id_item'], FILTER_VALIDATE_INT);
        if ($id_instalacion === false || $id_item === false) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Los IDs deben ser números enteros']);
            return;
        }
        $verificado  = !empty($data['verificado']) ? 1 : 0;
        $comentarios = isset($data['comentarios']) ? trim($data['comentarios']) : '';
        try {
            $action = ListaVerificacionInstalacion::saveRespuesta($id_instalacion, $id_item, $verificado, $comentarios);
            echo json_encode(['success' => true, 'message' => 'Guardado correctamente', 'action' => $action]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error DB: ' . $e->getMessage()]);
        }
    }

    public function store() {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['id_instalacion']) || !isset($data['id_item'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Se requieren id_instalacion e id_item']);
            return;
        }
        $id_instalacion = filter_var($data['id_instalacion'], FILTER_VALIDATE_INT);
        $id_item        = filter_var($data['id_item'], FILTER_VALIDATE_INT);
        $verificado     = isset($data['verificado']) ? (int)$data['verificado'] : 0;
        $comentarios    = isset($data['comentarios']) ? trim($data['comentarios']) : '';
        try {
            $id = ListaVerificacionInstalacion::create($id_instalacion, $id_item, $verificado, $comentarios);
            echo json_encode(['success' => true, 'id' => $id, 'message' => 'Registro creado correctamente']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error DB: ' . $e->getMessage()]);
        }
    }

    public function update() {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['id_lista_verificacion'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Se requiere id_lista_verificacion']);
            return;
        }
        $id = filter_var($data['id_lista_verificacion'], FILTER_VALIDATE_INT);
        $verificado  = isset($data['verificado']) ? (int)$data['verificado'] : 0;
        $comentarios = isset($data['comentarios']) ? trim($data['comentarios']) : '';
        try {
            if (ListaVerificacionInstalacion::update($id, $verificado, $comentarios)) {
                echo json_encode(['mensaje' => 'Registro actualizado correctamente']);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Registro no encontrado o sin cambios']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error DB: ' . $e->getMessage()]);
        }
    }

    public function delete() {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['id_lista_verificacion'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Se requiere id_lista_verificacion']);
            return;
        }
        $id = filter_var($data['id_lista_verificacion'], FILTER_VALIDATE_INT);
        try {
            if (ListaVerificacionInstalacion::delete($id)) {
                echo json_encode(['mensaje' => 'Registro eliminado correctamente']);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Registro no encontrado']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error DB: ' . $e->getMessage()]);
        }
    }
}
?>
