<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');

require_once __DIR__ . '/../controllers/ItemListaVerificacionController.php';

$controller = new ItemListaVerificacionController();
$controller->delete();
?>
