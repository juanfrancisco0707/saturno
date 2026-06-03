<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../controllers/InstalacionController.php';

$controller = new InstalacionController();
$controller->aceptar();
?>
