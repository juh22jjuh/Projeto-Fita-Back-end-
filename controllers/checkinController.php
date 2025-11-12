<?php
require_once __DIR__ . '/../routes/auth/authMiddleware.php';

class CheckinController {
    private $model;
    private $authMiddleware;

    public function __construct($model) {
        $this->model = $model;
        $this->authMiddleware = new AuthMiddleware();
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? null;

        try {
            switch ($method) {
                case 'GET':
                    $this->handleGetRequest($action);
                    break;
                case 'POST':
                    $this->handlePostRequest($action);
                    break;
                default:
                    $this->jsonResponse(['error' => 'Método não permitido'], 405);
                    break;
            }
        } catch (Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    private function handleGetRequest($action) {
        switch ($action) {
            case 'gerar-qrcode':
                $this->authMiddleware->checkAuth();
                $this->gerarQRCode();
                break;
            case 'ocupacao':
                $this->getOcupacao();
                break;
            case 'relatorio':
                $this->authMiddleware->checkAuth();
                $this->getRelatorio();
                break;
            case 'verificar-certificado':
                $this->authMiddleware->checkAuth();
                $this->verificarCertificado();
                break;
            default:
                $this->jsonResponse(['error' => 'Ação não reconhecida'], 400);
                break;
        }
    }

    private function handlePostRequest($action) {
        switch ($action) {
            case 'checkin':
                $this->realizarCheckin();
                break;
            case 'checkout':
                $this->realizarCheckout();
                break;
            default:
                $this->jsonResponse(['error' => 'Ação não reconhecida'], 400);
                break;
        }
    }

    private function gerarQRCode() {
        $usuario_id = $_SESSION['user_id'];
        $evento_id = $_GET['evento_id'] ?? null;

        if (!$evento_id) {
            $this->jsonResponse(['error' => 'ID do evento é obrigatório'], 400);
        }

        $token = $this->model->gerarQRCodeToken($usuario_id, $evento_id);
        $this->jsonResponse([
            'qr_code_token' => $token,
            'qr_code_url' => "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($token)
        ]);
    }

    private function realizarCheckin() {
        $data = json_decode(file_get_contents('php://input'), true);
        $token = $data['token'] ?? null;
        $usuario_id = $data['usuario_id'] ?? null;

        if (!$token || !$usuario_id) {
            $this->jsonResponse(['error' => 'Token e ID do usuário são obrigatórios'], 400);
        }

        $success = $this->model->registrarCheckin($token, $usuario_id);
        
        if ($success) {
            $this->jsonResponse(['message' => 'Check-in realizado com sucesso']);
        } else {
            $this->jsonResponse(['error' => 'Erro ao realizar check-in'], 500);
        }
    }

    private function realizarCheckout() {
        $data = json_decode(file_get_contents('php://input'), true);
        $token = $data['token'] ?? null;
        $usuario_id = $data['usuario_id'] ?? null;

        if (!$token || !$usuario_id) {
            $this->jsonResponse(['error' => 'Token e ID do usuário são obrigatórios'], 400);
        }

        $success = $this->model->registrarCheckout($token, $usuario_id);
        
        if ($success) {
            $this->jsonResponse(['message' => 'Check-out realizado com sucesso']);
        } else {
            $this->jsonResponse(['error' => 'Erro ao realizar check-out'], 500);
        }
    }

    private function getOcupacao() {
        $evento_id = $_GET['evento_id'] ?? null;

        if (!$evento_id) {
            $this->jsonResponse(['error' => 'ID do evento é obrigatório'], 400);
        }

        $ocupacao = $this->model->getOcupacaoTempoReal($evento_id);
        $this->jsonResponse($ocupacao);
    }

    private function getRelatorio() {
        $evento_id = $_GET['evento_id'] ?? null;

        if (!$evento_id) {
            $this->jsonResponse(['error' => 'ID do evento é obrigatório'], 400);
        }

        $relatorio = $this->model->getRelatorioPresenca($evento_id);
        $this->jsonResponse($relatorio);
    }

    private function verificarCertificado() {
        $usuario_id = $_SESSION['user_id'];
        $evento_id = $_GET['evento_id'] ?? null;

        if (!$evento_id) {
            $this->jsonResponse(['error' => 'ID do evento é obrigatório'], 400);
        }

        $elegivel = $this->model->verificarElegibilidadeCertificado($usuario_id, $evento_id);
        $this->jsonResponse(['elegivel_para_certificado' => $elegivel]);
    }

    private function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
?>