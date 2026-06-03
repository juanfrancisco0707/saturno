<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../controllers/ModeloEquipoGpsController.php';

$controller = new ModeloEquipoGpsController();
$controller->update();
?>
