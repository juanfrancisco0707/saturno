<?php
require_once __DIR__ . '/../models/Servicio.php';

class ServicioController {

    /** GET  → lista todos los servicios */
    public function index() {
        try {
            $servicios = Servicio::getAll();
            echo json_encode($servicios);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al consultar los servicios: ' . $e->getMessage()]);
        }
    }

    /** GET ?id_unidad=X → filtra por unidad */
    public function byUnidad() {
        if (!isset($_GET['id_unidad'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Se requiere id_unidad']);
            return;
        }
        $id = filter_var($_GET['id_unidad'], FILTER_VALIDATE_INT);
        if ($id === false) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'id_unidad inválido']);
            return;
        }
        try {
            echo json_encode(Servicio::getByUnidad($id));
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /** GET ?id_cliente=X → filtra por cliente */
    public function byCliente() {
        if (!isset($_GET['id_cliente'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Se requiere id_cliente']);
            return;
        }
        $id = filter_var($_GET['id_cliente'], FILTER_VALIDATE_INT);
        if ($id === false) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'id_cliente inválido']);
            return;
        }
        try {
            echo json_encode(Servicio::getByCliente($id));
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /** POST → crea un servicio nuevo */
    public function store() {
        $data = json_decode(file_get_contents("php://input"), true);

        // Validar campos requeridos
        if (!isset($data['id_unidad']) || !isset($data['tipo'])
            || !isset($data['fecha_inicio']) || !isset($data['monto'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Faltan datos requeridos: id_unidad, tipo, fecha_inicio, monto'
            ]);
            return;
        }

        // Aplicar defaults
        $servData = [
            'id_unidad'         => $data['id_unidad'],
            'tipo'              => $data['tipo'],
            'fecha_inicio'      => $data['fecha_inicio'],
            'monto'             => $data['monto'],
            'fecha_fin'         => !empty($data['fecha_fin']) ? $data['fecha_fin'] : null,
            'fecha_vencimiento' => !empty($data['fecha_vencimiento']) ? $data['fecha_vencimiento'] : null,
            'estado'            => !empty($data['estado']) ? $data['estado'] : 'pendiente',
            'num_periodos'      => isset($data['num_periodos']) ? $data['num_periodos'] : 1,
            'comentarios'       => isset($data['comentarios']) ? $data['comentarios'] : null,
            'periodo_pago'      => !empty($data['periodo_pago']) ? $data['periodo_pago'] : 'anual',
            'tarjeta_sim'       => !empty($data['tarjeta_sim']) ? $data['tarjeta_sim'] : null,
            'iccid'             => isset($data['iccid']) ? $data['iccid'] : '',
        ];

        // Validar enums
        if (!in_array($servData['tipo'], Servicio::getValidTipos())) {
            echo json_encode(['success' => false, 'message' => "Valor inválido para 'tipo'."]);
            return;
        }
        if (!in_array($servData['estado'], Servicio::getValidEstados())) {
            echo json_encode(['success' => false, 'message' => "Valor inválido para 'estado'."]);
            return;
        }
        if (!in_array($servData['periodo_pago'], Servicio::getValidPeriodosPago())) {
            echo json_encode(['success' => false, 'message' => "Valor inválido para 'periodo_pago'."]);
            return;
        }

        // Validación de superposición de periodos según el tipo de servicio
        $hasActive = Servicio::hasActiveBaseServiceInPeriod($servData['id_unidad'], $servData['fecha_inicio'], $servData['fecha_fin']);
        
        if ($servData['tipo'] === 'instalacion' || $servData['tipo'] === 'renovacion') {
            if ($hasActive) {
                echo json_encode(['success' => false, 'message' => "La unidad ya cuenta con una instalación o renovación activa en el periodo especificado."]);
                return;
            }
        } elseif ($servData['tipo'] === 'mantenimiento' || $servData['tipo'] === 'otro') {
            if (!$hasActive) {
                echo json_encode(['success' => false, 'message' => "Para registrar mantenimiento u otro, la unidad DEBE tener un servicio base (instalación o renovación) activo en ese periodo."]);
                return;
            }
        }

        try {
            $id = Servicio::create($servData);
            echo json_encode([
                'success' => true,
                'id'      => $id,
                'message' => 'Servicio guardado exitosamente'
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()]);
        }
    }

    /** PUT → actualiza un servicio (id en URL: ?id=X) */
    public function update() {
        // Obtener ID desde URL o body
        $id_servicio = null;
        if (isset($_GET['id'])) {
            $id_servicio = filter_var($_GET['id'], FILTER_VALIDATE_INT);
        }

        $data = json_decode(file_get_contents("php://input"), true);

        // También aceptar id en el body para compatibilidad
        if (!$id_servicio && isset($data['id_servicio'])) {
            $id_servicio = filter_var($data['id_servicio'], FILTER_VALIDATE_INT);
        }

        if (!$id_servicio) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Se requiere un ID de servicio válido']);
            return;
        }

        // Validar enums si vienen en la data
        if (isset($data['tipo']) && !in_array(strtolower($data['tipo']), Servicio::getValidTipos())) {
            echo json_encode(['success' => false, 'message' => "Valor inválido para 'tipo'."]);
            return;
        }
        if (isset($data['estado']) && !in_array($data['estado'], Servicio::getValidEstados())) {
            echo json_encode(['success' => false, 'message' => "Valor inválido para 'estado'."]);
            return;
        }
        if (isset($data['periodo_pago']) && !in_array($data['periodo_pago'], Servicio::getValidPeriodosPago())) {
            echo json_encode(['success' => false, 'message' => "Valor inválido para 'periodo_pago'."]);
            return;
        }

        // Limpiar campos vacíos opcionales a null
        foreach (['fecha_fin', 'fecha_vencimiento', 'tarjeta_sim'] as $f) {
            if (isset($data[$f]) && $data[$f] === '') $data[$f] = null;
        }

        unset($data['id_servicio']); // no actualizar la PK

        // Validación de superposición de periodos (si se envían todos los datos requeridos)
        if (isset($data['id_unidad']) && isset($data['tipo']) && isset($data['fecha_inicio'])) {
            $hasActive = Servicio::hasActiveBaseServiceInPeriod(
                $data['id_unidad'], 
                $data['fecha_inicio'], 
                $data['fecha_fin'] ?? null, 
                $id_servicio
            );

            if ($data['tipo'] === 'instalacion' || $data['tipo'] === 'renovacion') {
                if ($hasActive) {
                    echo json_encode(['success' => false, 'message' => "La unidad ya cuenta con una instalación o renovación activa en el periodo especificado."]);
                    return;
                }
            } elseif ($data['tipo'] === 'mantenimiento' || $data['tipo'] === 'otro') {
                if (!$hasActive) {
                    echo json_encode(['success' => false, 'message' => "Para registrar mantenimiento u otro, la unidad DEBE tener un servicio base (instalación o renovación) activo en ese periodo."]);
                    return;
                }
            }
        }

        try {
            if (Servicio::update($id_servicio, $data)) {
                echo json_encode(['success' => true, 'message' => 'Servicio actualizado correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se pudo actualizar el servicio']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
        }
    }

    /** DELETE / POST → elimina un servicio */
    public function delete() {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['id_servicio'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Se requiere el ID del servicio']);
            return;
        }

        $id = filter_var($data['id_servicio'], FILTER_VALIDATE_INT);
        if ($id === false) {
            http_response_code(400);
            echo json_encode(['error' => 'El ID del servicio debe ser un número entero']);
            return;
        }

        try {
            if (Servicio::delete($id)) {
                echo json_encode(['success' => true, 'mensaje' => 'Servicio eliminado correctamente']);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Servicio no encontrado']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
?>
