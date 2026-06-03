<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../controllers/UnidadController.php';

$controller = new UnidadController();
$controller->update();
?>
