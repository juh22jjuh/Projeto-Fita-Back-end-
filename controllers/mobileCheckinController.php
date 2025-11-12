<?php
class MobileCheckinController {
    private $checkinModel;

    public function __construct($checkinModel) {
        $this->checkinModel = $checkinModel;
    }

    public function scanQRCode($qrCodeData, $usuario_id) {
        try {
            // O QR code contém o token diretamente
            $token = $qrCodeData;
            
            $checkin_data = $this->checkinModel->validarCheckin($token, $usuario_id);
            
            if (!$checkin_data) {
                return ['success' => false, 'message' => 'QR Code inválido'];
            }

            if ($checkin_data['data_hora_checkin']) {
                // Se já fez check-in, faz check-out
                $this->checkinModel->registrarCheckout($token, $usuario_id);
                return ['success' => true, 'action' => 'checkout', 'message' => 'Check-out realizado'];
            } else {
                // Faz check-in
                $this->checkinModel->registrarCheckin($token, $usuario_id);
                return ['success' => true, 'action' => 'checkin', 'message' => 'Check-in realizado'];
            }

        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getEventosUsuario($usuario_id) {
        // Implementar busca de eventos do usuário
        $sql = "SELECT e.*, c.qr_code_token 
                FROM eventos e 
                JOIN checkins c ON e.id = c.evento_id 
                WHERE c.usuario_id = ?";
        // ... implementação completa
    }
}
?>