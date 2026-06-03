<?php
require_once __DIR__ . '/../conexion.php';

class Unidad {
    
    public static function getAll() {
        try {
            $conexion = Conexion::conectar();
            $stmt = $conexion->prepare("SELECT u.*,
             c.nombre as nombre_categoria FROM unidades u JOIN 
             categorias c ON u.idcategoria = c.id ORDER BY u.nombre_unidad");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public static function create($data) {
        try {
            $db = Conexion::conectar();
            $stmt = $db->prepare("INSERT INTO unidades 
            (id_cliente, nombre_unidad, fecha_instalacion, comentarios,
             estatus, tarjeta_sim, iccid, idcategoria, id_modelo_gps) 
            VALUES (:id_cliente, :nombre_unidad, :fecha_instalacion,
             :comentarios, :estatus, :tarjeta_sim, :iccid, :idcategoria, :id_modelo_gps)");
            
            $stmt->bindParam(':id_cliente', $data['id_cliente']);
            $stmt->bindParam(':nombre_unidad', $data['nombre_unidad']);
            $stmt->bindParam(':fecha_instalacion', $data['fecha_instalacion']);
            $stmt->bindParam(':comentarios', $data['comentarios']);
            $stmt->bindParam(':estatus', $data['estatus']);
            $stmt->bindParam(':tarjeta_sim', $data['tarjeta_sim']);
            $stmt->bindParam(':iccid', $data['iccid']);
            $stmt->bindParam(':idcategoria', $data['idcategoria']);
            $stmt->bindParam(':id_modelo_gps', $data['id_modelo_gps']);
            
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
            // Filter and prepare fields to update
            $allowed_fields = ['id_cliente', 'nombre_unidad', 'idcategoria', 
            'fecha_instalacion', 'ultima_fecha_instalacion', 'comentarios', 
            'estatus', 'tarjeta_sim', 'iccid', 'id_modelo_gps'];
            
            foreach ($data as $key => $value) {
                if (in_array($key, $allowed_fields)) {
                    $fields[] = "$key = :$key";
                }
            }
            if (empty($fields)) {
                return false; 
            }
            $query = "UPDATE unidades SET " . implode(', ', $fields) . "
             WHERE id_unidad = :id_unidad";
            $stmt = $db->prepare($query);
            foreach ($data as $key => $value) {
                 if (in_array($key, $allowed_fields)) {
                    $stmt->bindValue(":$key", $value);
                 }
            }
            $stmt->bindValue(':id_unidad', $id);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw $e;
        }
    }
    
    public static function delete($id) {
         try {
            $db = Conexion::conectar();
            $stmt = $db->prepare("DELETE FROM unidades WHERE 
            id_unidad = :id_unidad");
            $stmt->bindParam(':id_unidad', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() > 0;
         } catch (PDOException $e) {
            throw $e;
         }
    }
}
?>
