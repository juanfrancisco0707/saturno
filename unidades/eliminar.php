<?php
// Set headers to allow cross-origin requests and specify JSON content type
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

require_once __DIR__ . '/../controllers/UnidadController.php';

$controller = new UnidadController();
$controller->delete();
?>