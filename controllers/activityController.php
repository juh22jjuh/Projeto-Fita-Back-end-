<?php
// /controllers/activityController.php

require_once __DIR__ . '/../routes/auth/authMiddleware.php';

class ActivityController {
    private $model;
    private $authMiddleware;

    public function __construct($model) {
        $this->model = $model;
        $this->authMiddleware = new AuthMiddleware();
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $id = $_GET['id'] ?? null;

        try {
            switch ($method) {
                case 'GET':
                    // GET (leitura) é público
                    if ($id) {
                        $data = $this->model->getById($id);
                        if ($data) {
                            $this->jsonResponse($data);
                        } else {
                            $this->jsonResponse(['error' => 'Atividade não encontrada'], 404);
                        }
                    } else {
                        $data = $this->model->getAll();
                        $this->jsonResponse($data);
                    }
                    break;
                
                case 'POST':
                    $this->authMiddleware->checkAuth(); // <-- Rota Protegida
                    
                    $data = json_decode(file_get_contents('php://input'), true);
                    if (empty($data['titulo'])) {
                        $this->jsonResponse(['error' => 'Título é obrigatório'], 400);
                    }
                    
                    $newId = $this->model->create($data);
                    $this->jsonResponse(['id' => $newId, 'message' => 'Atividade criada com sucesso'], 201);
                    break;

                case 'PUT':
                    $this->authMiddleware->checkAuth(); // <-- Rota Protegida

                    if (!$id) {
                        $this->jsonResponse(['error' => 'ID é obrigatório para atualizar'], 400);
                    }
                    $data = json_decode(file_get_contents('php://input'), true);
                     if (empty($data['titulo'])) {
                        $this->jsonResponse(['error' => 'Título é obrigatório'], 400);
                    }

                    $this->model->update($id, $data);
                    $this->jsonResponse(['id' => $id, 'message' => 'Atividade atualizada com sucesso']);
                    break;

                case 'DELETE':
                    $this->authMiddleware->checkAuth(); // <-- Rota Protegida

                    if (!$id) {
                        $this->jsonResponse(['error' => 'ID é obrigatório para deletar'], 400);
                    }
                    $this->model->delete($id);
                    $this->jsonResponse(['id' => $id, 'message' => 'Atividade deletada com sucesso']);
                    break;

                default:
                    $this->jsonResponse(['error' => 'Método não permitido'], 405);
                    break;
            }
        } catch (PDOException $e) {
            $this->jsonResponse(['error' => 'Erro no banco de dados: ' . $e->getMessage()], 500);
        } catch (Exception $e) {
            $this->jsonResponse(['error' => 'Erro interno: ' . $e->getMessage()], 500);
        }
    }

    private function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }
}
?>