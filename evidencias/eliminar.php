<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
require_once __DIR__ . '/../controllers/EvidenciaController.php';
$controller = new EvidenciaController();
$controller->delete();
?>
