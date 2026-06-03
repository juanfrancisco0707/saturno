<?php
require_once __DIR__ . '/../conexion.php';

class ItemListaVerificacion {

    /** Retorna todos los ítems ordenados por id */
    public static function getAll() {
        $db = Conexion::conectar();
        $stmt = $db->prepare(
            "SELECT id_item, descripcion, es_requerido, creado_en, actualizado_en
             FROM items_lista_verificacion
             ORDER BY id_item ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Retorna un ítem por su ID, o null si no existe */
    public static function getById($id) {
        $db = Conexion::conectar();
        $stmt = $db->prepare(
            "SELECT id_item, descripcion, es_requerido, creado_en, actualizado_en
             FROM items_lista_verificacion
             WHERE id_item = :id_item"
        );
        $stmt->bindParam(':id_item', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /** Inserta un nuevo ítem, retorna el id insertado */
    public static function create($descripcion, $es_requerido = 1) {
        $db = Conexion::conectar();
        $stmt = $db->prepare(
            "INSERT INTO items_lista_verificacion (descripcion, es_requerido)
             VALUES (:descripcion, :es_requerido)"
        );
        $stmt->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
        $stmt->bindParam(':es_requerido', $es_requerido, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $db->lastInsertId();
        }
        throw new Exception("Error al insertar el ítem: " . implode(' | ', $stmt->errorInfo()));
    }

    /** Actualiza un ítem existente, retorna bool */
    public static function update($id, $descripcion, $es_requerido) {
        $db = Conexion::conectar();
        $stmt = $db->prepare(
            "UPDATE items_lista_verificacion
             SET descripcion = :descripcion,
                 es_requerido = :es_requerido,
                 actualizado_en = current_timestamp()
             WHERE id_item = :id_item"
        );
        $stmt->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
        $stmt->bindParam(':es_requerido', $es_requerido, PDO::PARAM_INT);
        $stmt->bindParam(':id_item', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    /** Elimina un ítem por ID, retorna bool */
    public static function delete($id) {
        $db = Conexion::conectar();
        $stmt = $db->prepare(
            "DELETE FROM items_lista_verificacion WHERE id_item = :id_item"
        );
        $stmt->bindParam(':id_item', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}
?>
