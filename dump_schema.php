<?php
$db = new PDO("mysql:host=localhost;dbname=bdsaturno", "root", "clave");
$tables = ['empresa', 'instalaciones', 'facturas', 'factura_detalles', 'detalle_facturas_servicios', 'servicios', 'clientes'];
$schema = [];
foreach($tables as $t) {
  try {
    $stmt = $db->query("DESCRIBE $t");
    if($stmt) $schema[$t] = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch(Exception $e) { $schema[$t] = $e->getMessage(); }
}
file_put_contents('schema.json', json_encode($schema, JSON_PRETTY_PRINT));
echo "Schema dumped successfully.";
