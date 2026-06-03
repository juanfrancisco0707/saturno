<?php
require_once __DIR__ . '/../conexion.php';

class Servicio {

    private static $valid_tipos        = ['renovacion', 'instalacion', 'mantenimiento', 'otro'];
    private static $valid_estados      = ['vencido', 'pendiente', 'pagado'];
    private static $valid_periodos_pago = ['anual', 'semestral', 'bimestral', 'mensual'];

    public static function getValidTipos()        { return self::$valid_tipos; }
    public static function getValidEstados()      { return self::$valid_estados; }
    public static function getValidPeriodosPago() { return self::$valid_periodos_pago; }

    /** Lista todos los servicios con nombre de unidad incluido */
    public static function getAll() {
        $db = Conexion::conectar();
        $stmt = $db->prepare("
            SELECT s.*, u.nombre_unidad, u.id_cliente
            FROM servicios s
            LEFT JOIN unidades u ON s.id_unidad = u.id_unidad
            ORDER BY s.fecha_inicio DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Lista servicios de una unidad específica */
    public static function getByUnidad($id_unidad) {
        $db = Conexion::conectar();
        $stmt = $db->prepare("
            SELECT s.*, u.nombre_unidad, u.id_cliente
            FROM servicios s
            LEFT JOIN unidades u ON s.id_unidad = u.id_unidad
            WHERE s.id_unidad = :id_unidad
            ORDER BY s.fecha_inicio DESC
        ");
        $stmt->bindParam(':id_unidad', $id_unidad, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Lista servicios filtrados por cliente (via unidades) */
    public static function getByCliente($id_cliente) {
        $db = Conexion::conectar();
        $stmt = $db->prepare("
            SELECT s.*, u.nombre_unidad, u.id_cliente
            FROM servicios s
            INNER JOIN unidades u ON s.id_unidad = u.id_unidad
            WHERE u.id_cliente = :id_cliente
            ORDER BY s.tipo
        ");
        $stmt->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Crea un servicio nuevo, retorna el id insertado */
    public static function create($data) {
        $db = Conexion::conectar();
        $stmt = $db->prepare("
            INSERT INTO servicios
                (id_unidad, tipo, fecha_inicio, fecha_fin, fecha_vencimiento,
                 monto, estado, num_periodos, comentarios,
                 periodo_pago, tarjeta_sim, iccid)
            VALUES
                (:id_unidad, :tipo, :fecha_inicio, :fecha_fin, :fecha_vencimiento,
                 :monto, :estado, :num_periodos, :comentarios,
                 :periodo_pago, :tarjeta_sim, :iccid)
        ");

        $stmt->bindParam(':id_unidad',         $data['id_unidad']);
        $stmt->bindParam(':tipo',              $data['tipo']);
        $stmt->bindParam(':fecha_inicio',      $data['fecha_inicio']);
        $stmt->bindParam(':fecha_fin',         $data['fecha_fin']);
        $stmt->bindParam(':fecha_vencimiento', $data['fecha_vencimiento']);
        $stmt->bindParam(':monto',             $data['monto']);
        $stmt->bindParam(':estado',            $data['estado']);
        $stmt->bindParam(':num_periodos',      $data['num_periodos']);
        $stmt->bindParam(':comentarios',       $data['comentarios']);
        $stmt->bindParam(':periodo_pago',      $data['periodo_pago']);
        $stmt->bindParam(':tarjeta_sim',       $data['tarjeta_sim']);
        $stmt->bindParam(':iccid',             $data['iccid']);

        if ($stmt->execute()) {
            return $db->lastInsertId();
        }
        throw new Exception("Error al insertar el servicio: " . implode(' | ', $stmt->errorInfo()));
    }

    /** Actualiza un servicio existente, retorna bool */
    public static function update($id, $data) {
        $db = Conexion::conectar();

        $allowed = [
            'id_unidad', 'tipo', 'fecha_inicio', 'fecha_fin', 'fecha_vencimiento',
            'monto', 'estado', 'num_periodos', 'comentarios',
            'periodo_pago', 'tarjeta_sim', 'iccid'
        ];

        $fields = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $allowed)) {
                $fields[] = "$key = :$key";
            }
        }

        if (empty($fields)) {
            return false;
        }

        $query = "UPDATE servicios SET " . implode(', ', $fields)
               . ", actualizado_en = current_timestamp() WHERE id_servicio = :id_servicio";

        $stmt = $db->prepare($query);
        foreach ($data as $key => $value) {
            if (in_array($key, $allowed)) {
                $stmt->bindValue(":$key", $value);
            }
        }
        $stmt->bindValue(':id_servicio', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /** Elimina un servicio por ID, retorna bool */
    public static function delete($id) {
        $db = Conexion::conectar();
        $stmt = $db->prepare("DELETE FROM servicios WHERE id_servicio = :id_servicio");
        $stmt->bindParam(':id_servicio', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
    /** Verifica si existe un servicio base (instalación o renovación) activo en el periodo indicado */
    public static function hasActiveBaseServiceInPeriod($id_unidad, $fecha_inicio, $fecha_fin = null, $exclude_id = null) {
        $db = Conexion::conectar();
        
        $query = "SELECT id_servicio FROM servicios 
                  WHERE id_unidad = :id_unidad 
                  AND tipo IN ('instalacion', 'renovacion')
                  AND estado IN ('pendiente', 'pagado') ";
                  
        if ($exclude_id !== null) {
            $query .= " AND id_servicio != :exclude_id ";
        }
        
        if (!empty($fecha_fin)) {
            $query .= " AND (fecha_inicio <= :fecha_fin) AND (fecha_fin >= :fecha_inicio OR fecha_fin IS NULL) ";
        } else {
            $query .= " AND (fecha_fin >= :fecha_inicio OR fecha_fin IS NULL) ";
        }
        
        $stmt = $db->prepare($query);
        $stmt->bindValue(':id_unidad', $id_unidad, PDO::PARAM_INT);
        $stmt->bindValue(':fecha_inicio', $fecha_inicio);
        if (!empty($fecha_fin)) {
            $stmt->bindValue(':fecha_fin', $fecha_fin);
        }
        if ($exclude_id !== null) {
            $stmt->bindValue(':exclude_id', $exclude_id, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}
?>
