<?php
// /routes/forgotPasswordRoutes.php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:5500");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../controllers/passwordResetController.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $controller = new PasswordResetController();
    $response = $controller->requestReset($data);

    http_response_code($response['status']);
    echo json_encode($response);
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Método não permitido.']);
}