<?php
require_once __DIR__ . '/../conexion.php';

class Evidencia {

    public static function getAll() {
        $db = Conexion::conectar();
        $stmt = $db->prepare("SELECT * FROM evidencias ORDER BY fecha_subida DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getByVerificacion($id_lista_verificacion) {
        $db = Conexion::conectar();
        $stmt = $db->prepare("SELECT * FROM evidencias WHERE id_lista_verificacion = :id ORDER BY fecha_subida DESC");
        $stmt->bindParam(':id', $id_lista_verificacion, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function create($id_lista_verificacion, $ruta_archivo, $descripcion = '') {
        $db = Conexion::conectar();
        $stmt = $db->prepare(
            "INSERT INTO evidencias (id_lista_verificacion, ruta_archivo, descripcion) 
             VALUES (:id_lv, :ruta, :desc)"
        );
        $stmt->execute([
            ':id_lv' => $id_lista_verificacion,
            ':ruta'  => $ruta_archivo,
            ':desc'  => $descripcion
        ]);
        return $db->lastInsertId();
    }

    public static function delete($id) {
        $db = Conexion::conectar();
        // Primero obtenemos la ruta para borrar el archivo físico fuera
        $stmt = $db->prepare("SELECT ruta_archivo FROM evidencias WHERE id_evidencia = :id");
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $sql = "DELETE FROM evidencias WHERE id_evidencia = :id";
        $stmtDel = $db->prepare($sql);
        $stmtDel->execute([':id' => $id]);
        
        return $res ? $res['ruta_archivo'] : false;
    }
}
?>
