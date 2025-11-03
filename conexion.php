<?php

class Conexion{

    static public function conectar(){
        try {
            $conn = new PDO("mysql:host=localhost;dbname=bdsaturno",
            "root","clave",array(PDO::MYSQL_ATTR_INIT_COMMAND =>
             "SET NAMES utf8"));
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $conn;
        }
        catch (PDOException $e) {
            echo 'Falló la conexión: ' . $e->getMessage();
            return null;
        }

    }
}
// Prueba de conexión

/*
if (!Conexion::conectar()) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión']);
    exit();
} else {
    echo json_encode(['success' => true, 'message' => 'Conexión exitosa']);
}*/
