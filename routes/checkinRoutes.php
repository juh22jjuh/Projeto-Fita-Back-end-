<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:5500");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/CheckinModel.php';
require_once __DIR__ . '/../controllers/CheckinController.php';

$pdo = Database::getConnection();
$model = new CheckinModel($pdo);
$controller = new CheckinController($model);

$controller->handleRequest();
?>