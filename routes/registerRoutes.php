<?php
// /routes/registerRoutes.php

// 1. Permite a origem exata do seu front-end (Live Server)
header("Access-Control-Allow-Origin: http://127.0.0.1:5500"); // Ajuste para seu front-end

// 2. Permite que o navegador envie cookies (essencial para sessões)
header("Access-Control-Allow-Credentials: true");

// 3. Define os métodos permitidos
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

// 4. Define os cabeçalhos (headers) permitidos
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// 5. Responde ao "preflight" (requisição OPTIONS)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200); // Responde com OK
    exit; // Para a execução, pois foi só uma verificação
}

// -----------------------------------------------------------
require_once __DIR__ . '/../controllers/authController.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $controller = new AuthController();
    $response = $controller->register($data);

    http_response_code($response['status']);
    echo json_encode(['message' => $response['message']]);
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['message' => 'Método não permitido.']);
}