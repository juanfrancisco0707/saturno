<?php
require 'conexion.php';
$conn = Conexion::conectar();
$stmt = $conn->query('SHOW TABLES');
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
$schema = [];
foreach($tables as $t) {
    if (strpos($t, 'vista') !== false || $t == 'detalle_facturas_servicios' || $t == 'facturas_con_detalles') continue; // skip views
    try {
        $stmt2 = $conn->query("DESCRIBE `$t`");
        $schema[$t] = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    } catch(Exception $e) { }
}
file_put_contents('schema_full.json', json_encode($schema, JSON_PRETTY_PRINT));
echo "OK";
