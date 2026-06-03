<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: PUT');

require_once __DIR__ . '/../controllers/ServicioController.php';

$controller = new ServicioController();
$controller->update();
?>
