<?php
// /models/passwordResetModel.php

class PasswordResetModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function createToken($email, $token) {
        // Remove tokens existentes para este email
        $this->deleteTokenByEmail($email);
        
        // Cria novo token com expiração de 1 hora
        $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $sql = "INSERT INTO password_reset_tokens (email, token, expires_at) VALUES (?, ?, ?)";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$email, $token, $expires_at]);
    }

    public function findValidToken($token) {
        $sql = "SELECT * FROM password_reset_tokens WHERE token = ? AND expires_at > NOW() AND used = 0";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function markTokenAsUsed($token) {
        $sql = "UPDATE password_reset_tokens SET used = 1 WHERE token = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$token]);
    }

    public function deleteTokenByEmail($email) {
        $sql = "DELETE FROM password_reset_tokens WHERE email = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$email]);
    }

    public function deleteExpiredTokens() {
        $sql = "DELETE FROM password_reset_tokens WHERE expires_at <= NOW()";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute();
    }
}