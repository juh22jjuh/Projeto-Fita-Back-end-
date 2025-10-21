<?php
// /auth/AuthMiddleware.php

class AuthMiddleware {
    // Este é o token secreto que o cliente deve enviar.
    // No futuro, isso será gerado dinamicamente por um sistema de login.
    private static $meuTokenSecreto = "meu_token_secreto_123";

    /**
     * Verifica se o usuário está autenticado.
     * Por enquanto, apenas checa um 'Bearer' token fixo no cabeçalho.
     */
    public static function checkAuth() {
        $token = self::getTokenFromHeader();

        if ($token === null) {
            self::unauthorizedResponse('Token de autorização não fornecido.');
        }

        if ($token !== self::$meuTokenSecreto) {
            self::unauthorizedResponse('Token de autorização inválido.');
        }

        // Se chegou aqui, o token é válido.
        return true;
    }

    /**
     * Pega o "Bearer" token do cabeçalho HTTP 'Authorization'.
     */
    private static function getTokenFromHeader() {
        $headers = apache_request_headers(); // Pega todos os cabeçalhos

        // Verifica se o cabeçalho 'Authorization' existe
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
            // O formato é "Bearer <token>"
            // Vamos extrair apenas o <token>
            if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                return $matches[1];
            }
        }
        
        // Tenta pegar de uma forma alternativa (alguns servidores/ambientes)
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
            if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Envia uma resposta 401 Unauthorized e para a execução.
     */
    private static function unauthorizedResponse($message) {
        http_response_code(401); // 401 Unauthorized
        header("Content-Type: application/json");
        echo json_encode(['error' => $message]);
        exit;
    }
}
?>