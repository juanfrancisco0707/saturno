<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../controllers/ItemListaVerificacionController.php';

$controller = new ItemListaVerificacionController();
$controller->show();
?>
