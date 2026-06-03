<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

require_once __DIR__ . '/../controllers/ModeloEquipoGpsController.php';

$controller = new ModeloEquipoGpsController();
$controller->delete();
?>
