<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once __DIR__ . '/../controllers/ServicioController.php';

$controller = new ServicioController();
$controller->byCliente();
?>