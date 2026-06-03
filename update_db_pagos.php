<?php
require 'conexion.php';
try {
    $db = Conexion::conectar();
    
    // 1. Add id_cliente to facturas if it doesn't exist
    $result = $db->query("SHOW COLUMNS FROM facturas LIKE 'id_cliente'");
    if ($result->rowCount() == 0) {
        $db->exec("ALTER TABLE facturas ADD COLUMN id_cliente INT(11) NULL AFTER numero_factura");
        echo "Added id_cliente to facturas.\n";
        
        // Populate id_cliente for existing facturas
        $db->exec("UPDATE facturas f 
        JOIN factura_detalles fd ON f.id_factura = fd.id_factura
        JOIN servicios s ON fd.id_servicio = s.id_servicio
        JOIN unidades u ON s.id_unidad = u.id_unidad
        SET f.id_cliente = u.id_cliente");
        echo "Updated id_cliente values for existing facturas.\n";
    }

    // 2. Create pagos_facturas table
    $db->exec("CREATE TABLE IF NOT EXISTS pagos_facturas (
        id_pago INT(11) NOT NULL,
        id_factura INT(11) NOT NULL,
        monto_abonado DECIMAL(10,2) NOT NULL,
        PRIMARY KEY (id_pago, id_factura)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "Table pagos_facturas ensured.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
