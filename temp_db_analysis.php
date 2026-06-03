<?php
require_once 'conexion.php';
$db = Conexion::conectar();
$r = $db->query("SHOW CREATE TABLE detalle_pagos")->fetch(PDO::FETCH_ASSOC);
if (isset($r['Create View'])) {
    echo "TYPE: VIEW\n" . $r['Create View'];
} else {
    echo "TYPE: TABLE\n" . $r['Create Table'];
}
