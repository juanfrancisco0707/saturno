<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../controllers/EvidenciaController.php';
$controller = new EvidenciaController();
$controller->store();
?>