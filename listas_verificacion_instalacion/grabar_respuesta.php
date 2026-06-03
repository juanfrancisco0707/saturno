<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../controllers/ListaVerificacionInstalacionController.php';
$controller = new ListaVerificacionInstalacionController();
$controller->storeRespuesta();
?>
