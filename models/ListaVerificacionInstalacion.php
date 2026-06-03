<?php
require_once __DIR__ . '/../conexion.php';

class ListaVerificacionInstalacion {

    /** Retorna todos los registros con datos del ítem */
    public static function getAll() {
        $db = Conexion::conectar();
        $stmt = $db->prepare(
            "SELECT lv.id_lista_verificacion, lv.id_instalacion, lv.id_item,
                    lv.verificado, lv.comentarios, lv.creado_en, lv.actualizado_en,
                    i.descripcion, i.es_requerido
             FROM listas_verificacion_instalacion lv
             INNER JOIN items_lista_verificacion i ON lv.id_item = i.id_item
             ORDER BY lv.id_instalacion ASC, lv.id_item ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Retorna todos los registros de una instalación concreta */
    public static function getByInstalacion($id_instalacion) {
        $db = Conexion::conectar();
        $stmt = $db->prepare(
            "SELECT lv.id_lista_verificacion, lv.id_instalacion, lv.id_item,
                    lv.verificado, lv.comentarios, lv.creado_en, lv.actualizado_en,
                    i.descripcion, i.es_requerido
             FROM listas_verificacion_instalacion lv
             INNER JOIN items_lista_verificacion i ON lv.id_item = i.id_item
             WHERE lv.id_instalacion = :id_instalacion
             ORDER BY i.es_requerido DESC, lv.id_item ASC"
        );
        $stmt->bindParam(':id_instalacion', $id_instalacion, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Retorna un registro por su ID primario */
    public static function getById($id) {
        $db = Conexion::conectar();
        $stmt = $db->prepare(
            "SELECT lv.id_lista_verificacion, lv.id_instalacion, lv.id_item,
                    lv.verificado, lv.comentarios, lv.creado_en, lv.actualizado_en,
                    i.descripcion, i.es_requerido
             FROM listas_verificacion_instalacion lv
             INNER JOIN items_lista_verificacion i ON lv.id_item = i.id_item
             WHERE lv.id_lista_verificacion = :id"
        );
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * INSERT o UPDATE según si ya existe el par (id_instalacion, id_item).
     */
    public static function saveRespuesta($id_instalacion, $id_item, $verificado, $comentarios) {
        $db = Conexion::conectar();

        $check = $db->prepare(
            "SELECT id_lista_verificacion
             FROM listas_verificacion_instalacion
             WHERE id_instalacion = :id_inst AND id_item = :id_item"
        );
        $check->execute([':id_inst' => $id_instalacion, ':id_item' => $id_item]);
        $row = $check->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $stmt = $db->prepare(
                "UPDATE listas_verificacion_instalacion
                 SET verificado = :verificado, comentarios = :comentarios, actualizado_en = NOW()
                 WHERE id_lista_verificacion = :id"
            );
            $stmt->execute([
                ':verificado'   => $verificado,
                ':comentarios'  => $comentarios,
                ':id'           => $row['id_lista_verificacion']
            ]);
            return 'updated';
        } else {
            $stmt = $db->prepare(
                "INSERT INTO listas_verificacion_instalacion
                    (id_instalacion, id_item, verificado, comentarios)
                 VALUES (:id_inst, :id_item, :verificado, :comentarios)"
            );
            $stmt->execute([
                ':id_inst'      => $id_instalacion,
                ':id_item'      => $id_item,
                ':verificado'   => $verificado,
                ':comentarios'  => $comentarios
            ]);
            return 'inserted';
        }
    }

    public static function create($id_instalacion, $id_item, $verificado = 0, $comentarios = '') {
        $db = Conexion::conectar();
        $stmt = $db->prepare(
            "INSERT INTO listas_verificacion_instalacion
                (id_instalacion, id_item, verificado, comentarios)
             VALUES (:id_inst, :id_item, :verificado, :comentarios)"
        );
        $stmt->execute([
            ':id_inst'     => $id_instalacion,
            ':id_item'     => $id_item,
            ':verificado'  => $verificado,
            ':comentarios' => $comentarios
        ]);
        return $db->lastInsertId();
    }

    public static function update($id, $verificado, $comentarios) {
        $db = Conexion::conectar();
        $stmt = $db->prepare(
            "UPDATE listas_verificacion_instalacion
             SET verificado = :verificado, comentarios = :comentarios, actualizado_en = NOW()
             WHERE id_lista_verificacion = :id"
        );
        $stmt->execute([
            ':verificado'  => $verificado,
            ':comentarios' => $comentarios,
            ':id'          => $id
        ]);
        return $stmt->rowCount() > 0;
    }

    public static function delete($id) {
        $db = Conexion::conectar();
        $stmt = $db->prepare(
            "DELETE FROM listas_verificacion_instalacion WHERE id_lista_verificacion = :id"
        );
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}
?>
