<?php
/**
 * LEGACY ENDPOINT – redirige al nuevo controlador MVC.
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../controllers/ListaVerificacionInstalacionController.php';

$controller = new ListaVerificacionInstalacionController();
$controller->storeRespuesta();
?>