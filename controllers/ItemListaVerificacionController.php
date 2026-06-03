<?php
require_once __DIR__ . '/../models/ItemListaVerificacion.php';

class ItemListaVerificacionController {

    /** GET → lista todos los ítems */
    public function index() {
        try {
            $items = ItemListaVerificacion::getAll();
            echo json_encode($items);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al consultar los ítems: ' . $e->getMessage()]);
        }
    }

    /** GET ?id_item=X → retorna un ítem por ID */
    public function show() {
        if (!isset($_GET['id_item'])) {
            http_response_code(400);
            echo json_encode(['error' => 'El ID del ítem es requerido']);
            return;
        }

        $id = filter_var($_GET['id_item'], FILTER_VALIDATE_INT);
        if ($id === false) {
            http_response_code(400);
            echo json_encode(['error' => 'El ID del ítem debe ser un número entero']);
            return;
        }

        try {
            $item = ItemListaVerificacion::getById($id);
            if ($item) {
                echo json_encode($item);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Ítem no encontrado']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al consultar el ítem: ' . $e->getMessage()]);
        }
    }

    /** POST → crea un ítem nuevo */
    public function store() {
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['descripcion'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'La descripción del ítem es requerida']);
            return;
        }

        $descripcion  = trim($data['descripcion']);
        $es_requerido = isset($data['es_requerido']) ? (int)$data['es_requerido'] : 1;

        try {
            $id = ItemListaVerificacion::create($descripcion, $es_requerido);
            echo json_encode([
                'success' => true,
                'id'      => $id,
                'message' => 'Ítem guardado exitosamente'
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()]);
        }
    }

    /** POST (body JSON con id_item) → actualiza un ítem */
    public function update() {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['id_item']) || empty($data['descripcion'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Se requieren el ID y la descripción del ítem']);
            return;
        }

        $id = filter_var($data['id_item'], FILTER_VALIDATE_INT);
        if ($id === false) {
            http_response_code(400);
            echo json_encode(['error' => 'El ID del ítem debe ser un número entero']);
            return;
        }

        $descripcion  = trim($data['descripcion']);
        $es_requerido = isset($data['es_requerido']) ? (int)$data['es_requerido'] : 1;

        if (empty($descripcion)) {
            http_response_code(400);
            echo json_encode(['error' => 'La descripción del ítem no puede estar vacía']);
            return;
        }

        try {
            if (ItemListaVerificacion::update($id, $descripcion, $es_requerido)) {
                echo json_encode(['mensaje' => 'Ítem actualizado correctamente']);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Ítem no encontrado o sin cambios']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
        }
    }

    /** POST (body JSON con id_item) → elimina un ítem */
    public function delete() {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['id_item'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Se requiere el ID del ítem']);
            return;
        }

        $id = filter_var($data['id_item'], FILTER_VALIDATE_INT);
        if ($id === false) {
            http_response_code(400);
            echo json_encode(['error' => 'El ID del ítem debe ser un número entero']);
            return;
        }

        try {
            if (ItemListaVerificacion::delete($id)) {
                echo json_encode(['mensaje' => 'Ítem borrado correctamente']);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Ítem no encontrado']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
        }
    }
    /** POST → guarda o actualiza la respuesta de un ítem para una instalación */
    public function storeRespuesta() {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['id_instalacion']) || !isset($data['id_item'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Datos incompletos: se requieren id_instalacion e id_item']);
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
            $result = ItemListaVerificacion::saveRespuesta($id_instalacion, $id_item, $verificado, $comentarios);
            echo json_encode(['success' => true, 'message' => 'Guardado correctamente', 'action' => $result]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error DB: ' . $e->getMessage()]);
        }
    }
}
?>
