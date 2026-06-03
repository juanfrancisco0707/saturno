<?php
require_once __DIR__ . '/../conexion.php';

class ModeloEquipoGps {
    
    public static function getAll() {
        try {
            $conexion = Conexion::conectar();
            $stmt = $conexion->prepare("SELECT * FROM modelos_equipos_gps ORDER BY nombre_modelo");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public static function getById($id) {
        try {
            $conexion = Conexion::conectar();
            $stmt = $conexion->prepare("SELECT * FROM modelos_equipos_gps WHERE id_modelo_gps = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public static function create($data) {
        try {
            $db = Conexion::conectar();
            $stmt = $db->prepare("INSERT INTO modelos_equipos_gps 
            (nombre_modelo, marca, descripcion) 
            VALUES (:nombre_modelo, :marca, :descripcion)");
            
            $stmt->bindParam(':nombre_modelo', $data['nombre_modelo']);
            $stmt->bindParam(':marca', $data['marca']);
            $stmt->bindParam(':descripcion', $data['descripcion']);
            
            if ($stmt->execute()) {
                return $db->lastInsertId();
            } else {
                $errorInfo = $stmt->errorInfo();
                throw new Exception("Error al ejecutar la consulta: " . $errorInfo[2]);
            }
        } catch (PDOException $e) {
            throw $e;
        }
    }
    
    public static function update($id, $data) {
        try {
            $db = Conexion::conectar();
            
            $fields = [];
            $allowed_fields = ['nombre_modelo', 'marca', 'descripcion'];
            
            foreach ($data as $key => $value) {
                if (in_array($key, $allowed_fields)) {
                    $fields[] = "$key = :$key";
                }
            }
            if (empty($fields)) {
                return false; 
            }
            $query = "UPDATE modelos_equipos_gps SET " . implode(', ', $fields) . "
             WHERE id_modelo_gps = :id_modelo_gps";
            $stmt = $db->prepare($query);
            foreach ($data as $key => $value) {
                 if (in_array($key, $allowed_fields)) {
                    $stmt->bindValue(":$key", $value);
                 }
            }
            $stmt->bindValue(':id_modelo_gps', $id);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw $e;
        }
    }
    
    public static function delete($id) {
         try {
            $db = Conexion::conectar();
            $stmt = $db->prepare("DELETE FROM modelos_equipos_gps WHERE 
            id_modelo_gps = :id_modelo_gps");
            $stmt->bindParam(':id_modelo_gps', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() > 0;
         } catch (PDOException $e) {
            throw $e;
         }
    }
}
?>
