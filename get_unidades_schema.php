<?php
require 'conexion.php';
$c = Conexion::conectar();
$q = $c->query("SHOW CREATE TABLE unidades");
$res = $q->fetchAll();
file_put_contents('unidades_schema_clean.txt', $res[0]['Create Table']);
?>
