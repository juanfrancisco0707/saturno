<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
require_once __DIR__ . '/../controllers/ListaVerificacionInstalacionController.php';
$controller = new ListaVerificacionInstalacionController();
$controller->update();
?>
