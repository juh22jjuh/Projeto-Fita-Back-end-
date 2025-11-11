<?php
// routes/eventoRoutes.php

// Cabeçalhos CORS (COPIE DO SEU loginRoutes.php ou registerRoutes.php)
header("Access-Control-Allow-Origin: http://localhost:5500"); 
// ... (copie todos os 5 headers e o 'if OPTIONS') ...
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { exit; }

// -----------------------------------------------------------
require_once __DIR__ . '/../controllers/EventoController.php';
require_once __DIR__ . '/../auth/AuthMiddleware.php';

// Protege a rota: Ninguém pode gerenciar eventos sem estar logado
$middleware = new AuthMiddleware();
$middleware->checkAuth(); 

// Pega os dados do usuário logado (armazenados na sessão pelo login)
$criador_id = $_SESSION['user_id'];
$criador_nivel = $_SESSION['nivel_acesso']; 

$controller = new EventoController();
$method = $_SERVER['REQUEST_METHOD'];

// Este arquivo vai lidar com o CRUD
// (Vamos usar POST para tudo por simplicidade, mas poderíamos usar GET, PUT, DELETE)

$data = json_decode(file_get_contents("php://input"), true);
$action = $data['action'] ?? null; // O front-end dirá o que quer fazer

if ($method === 'POST') {
    switch ($action) {
        case 'create':
            // Apenas organizadores ou palestrantes podem criar
            if ($criador_nivel == 'organizador' || $criador_nivel == 'administrador' || $criador_nivel == 'palestrante') {
                $response = $controller->create($data, $criador_id, $criador_nivel);
            } else {
                $response = ['status' => 403, 'message' => 'Você não tem permissão para criar eventos.'];
            }
            break;
        
        case 'update':
            // $response = $controller->update($data, $criador_id, $criador_nivel);
            $response = ['status' => 501, 'message' => 'Atualização não implementada.'];
            break;

        case 'delete':
            // $response = $controller->delete($data, $criador_id, $criador_nivel);
            $response = ['status' => 501, 'message' => 'Deleção não implementada.'];
            break;

        default:
            $response = ['status' => 400, 'message' => 'Ação inválida.'];
            break;
    }
} else {
    // $response = $controller->get($data);
    $response = ['status' => 501, 'message' => 'Busca não implementada.'];
}

http_response_code($response['status']);
echo json_encode(['message' => $response['message']]);