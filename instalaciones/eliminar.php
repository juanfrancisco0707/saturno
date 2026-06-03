<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../controllers/InstalacionController.php';

$controller = new InstalacionController();
$controller->delete();
?>