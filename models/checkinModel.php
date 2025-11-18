<?php
class CheckinModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function contarInscricoesUsuario($usuario_id) {
        $sql = "SELECT COUNT(*) as total FROM checkins WHERE usuario_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$resultado['total'];
    }
    
    public function gerarQRCodeToken($usuario_id, $evento_id) {
        $token = bin2hex(random_bytes(32));
        $sql = "INSERT INTO checkins (usuario_id, evento_id, qr_code_token) VALUES (?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id, $evento_id, $token]);
        return $token;
    }

    public function validarCheckin($token, $usuario_id) {
        $sql = "SELECT c.*, e.data_hora_inicio, e.duracao 
                FROM checkins c 
                JOIN eventos e ON c.evento_id = e.id 
                WHERE c.qr_code_token = ? AND c.usuario_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$token, $usuario_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function registrarCheckin($token, $usuario_id) {
        $checkin_data = $this->validarCheckin($token, $usuario_id);
        
        if (!$checkin_data) {
            return false;
        }

        $data_hora_atual = date('Y-m-d H:i:s');
        $data_hora_inicio = $checkin_data['data_hora_inicio'];
        
        // Verificar se está dentro do período permitido (30 minutos antes)
        $limite_antes = date('Y-m-d H:i:s', strtotime('-30 minutes', strtotime($data_hora_inicio)));
        
        if ($data_hora_atual < $limite_antes) {
            throw new Exception("Check-in permitido apenas 30 minutos antes da atividade");
        }

        // Verificar se há check-in anterior
        if ($checkin_data['data_hora_checkin']) {
            throw new Exception("Check-in já realizado");
        }

        // Verificar atraso
        $status = 'presente';
        $tolerancia = 10; // minutos
        $limite_atraso = date('Y-m-d H:i:s', strtotime("+$tolerancia minutes", strtotime($data_hora_inicio)));
        
        if ($data_hora_atual > $limite_atraso) {
            $status = 'atrasado';
        }

        $sql = "UPDATE checkins SET data_hora_checkin = ?, status = ? WHERE qr_code_token = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$data_hora_atual, $status, $token]);
    }

    public function registrarCheckout($token, $usuario_id) {
        $checkin_data = $this->validarCheckin($token, $usuario_id);
        
        if (!$checkin_data || !$checkin_data['data_hora_checkin']) {
            throw new Exception("Check-in não realizado ou token inválido");
        }

        if ($checkin_data['data_hora_checkout']) {
            throw new Exception("Check-out já realizado");
        }

        $data_hora_atual = date('Y-m-d H:i:s');
        $data_hora_checkin = $checkin_data['data_hora_checkin'];
        
        // Calcular minutos presentes
        $minutos_presente = round((strtotime($data_hora_atual) - strtotime($data_hora_checkin)) / 60);

        $sql = "UPDATE checkins SET data_hora_checkout = ?, minutos_presente = ? WHERE qr_code_token = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$data_hora_atual, $minutos_presente, $token]);
    }

    public function getOcupacaoTempoReal($evento_id) {
        $sql = "SELECT 
                COUNT(*) as total_inscritos,
                SUM(CASE WHEN data_hora_checkin IS NOT NULL THEN 1 ELSE 0 END) as presentes,
                SUM(CASE WHEN status = 'atrasado' THEN 1 ELSE 0 END) as atrasados
                FROM checkins 
                WHERE evento_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$evento_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getRelatorioPresenca($evento_id) {
        $sql = "SELECT 
                u.nome,
                u.email,
                c.data_hora_checkin,
                c.data_hora_checkout,
                c.status,
                c.minutos_presente,
                e.duracao as duracao_total,
                ROUND((c.minutos_presente / e.duracao) * 100, 2) as percentual_presenca
                FROM checkins c
                JOIN usuarios u ON c.usuario_id = u.id
                JOIN eventos e ON c.evento_id = e.id
                WHERE c.evento_id = ?
                ORDER BY c.data_hora_checkin";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$evento_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function verificarElegibilidadeCertificado($usuario_id, $evento_id) {
        $sql = "SELECT 
                c.minutos_presente,
                e.duracao,
                cc.presenca_minima,
                ROUND((c.minutos_presente / e.duracao) * 100, 2) as percentual_presenca
                FROM checkins c
                JOIN eventos e ON c.evento_id = e.id
                LEFT JOIN configuracoes_certificado cc ON e.id = cc.evento_id
                WHERE c.usuario_id = ? AND c.evento_id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id, $evento_id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) return false;

        $presenca_minima = $data['presenca_minima'] ?? 75.00;
        return $data['percentual_presenca'] >= $presenca_minima;
    }
}
?>