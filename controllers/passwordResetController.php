<?php
// /controllers/passwordResetController.php

require_once __DIR__ . '/../models/userModel.php';
require_once __DIR__ . '/../models/passwordResetModel.php';

class PasswordResetController {
    private $userModel;
    private $passwordResetModel;

    public function __construct() {
        $this->userModel = new UserModel();
        $this->passwordResetModel = new PasswordResetModel(Database::getConnection());
    }

    public function requestReset($data) {
        // Validação do email
        if (empty($data['email'])) {
            return ['status' => 400, 'message' => 'Email é obrigatório.'];
        }

        $email = $data['email'];

        // Verifica se o email existe
        if (!$this->userModel->emailExists($email)) {
            // Por segurança, retornamos sucesso mesmo se o email não existir
            return ['status' => 200, 'message' => 'Se o email existir em nosso sistema, enviaremos instruções de redefinição.'];
        }

        // Gera token único
        $token = bin2hex(random_bytes(32));
        
        // Salva token no banco
        if ($this->passwordResetModel->createToken($email, $token)) {
            // Aqui você implementaria o envio de email
            // Por enquanto, retornamos o token (em produção, remova esta linha)
            $resetLink = "http://localhost:8000/redefinir-senha.html?token=" . $token;
            
            // Simulação de envio de email
            error_log("Link de redefinição para $email: $resetLink");
            
            return ['status' => 200, 'message' => 'Se o email existir em nosso sistema, enviaremos instruções de redefinição.'];
        }

        return ['status' => 500, 'message' => 'Erro ao processar solicitação.'];
    }

    public function resetPassword($data) {
        // Validações
        if (empty($data['token']) || empty($data['nova_senha']) || empty($data['confirmar_senha'])) {
            return ['status' => 400, 'message' => 'Token, nova senha e confirmação são obrigatórios.'];
        }

        if ($data['nova_senha'] !== $data['confirmar_senha']) {
            return ['status' => 400, 'message' => 'As senhas não coincidem.'];
        }

        // Valida força da senha
        if (strlen($data['nova_senha']) < 8) {
            return ['status' => 400, 'message' => 'A senha deve ter no mínimo 8 caracteres.'];
        }

        // Verifica token válido
        $tokenData = $this->passwordResetModel->findValidToken($data['token']);
        if (!$tokenData) {
            return ['status' => 400, 'message' => 'Token inválido ou expirado.'];
        }

        // Atualiza senha
        $senhaHash = password_hash($data['nova_senha'], PASSWORD_BCRYPT);
        if ($this->userModel->updatePassword($tokenData['email'], $senhaHash)) {
            // Marca token como usado
            $this->passwordResetModel->markTokenAsUsed($data['token']);
            
            // Limpa tokens expirados
            $this->passwordResetModel->deleteExpiredTokens();

            return ['status' => 200, 'message' => 'Senha redefinida com sucesso.'];
        }

        return ['status' => 500, 'message' => 'Erro ao redefinir senha.'];
    }

    public function validateToken($token) {
        if (empty($token)) {
            return ['status' => 400, 'message' => 'Token é obrigatório.'];
        }

        $tokenData = $this->passwordResetModel->findValidToken($token);
        if ($tokenData) {
            return ['status' => 200, 'message' => 'Token válido.', 'valid' => true];
        } else {
            return ['status' => 400, 'message' => 'Token inválido ou expirado.', 'valid' => false];
        }
    }
}