<?php
require_once __DIR__ . '/../routes/auth/authMiddleware.php';

class DashboardController {
    private $checkinModel;
    private $authMiddleware;

    public function __construct($checkinModel) {
        $this->checkinModel = $checkinModel;
        $this->authMiddleware = new AuthMiddleware();
    }

    public function getDashboardData($evento_id) {
        $this->authMiddleware->checkAuth();
        
        $ocupacao = $this->checkinModel->getOcupacaoTempoReal($evento_id);
        $relatorio = $this->checkinModel->getRelatorioPresenca($evento_id);
        
        return [
            'ocupacao' => $ocupacao,
            'presencas' => $relatorio,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    public function getEstatisticasCertificados($evento_id) {
        $this->authMiddleware->checkAuth();
        
        $relatorio = $this->checkinModel->getRelatorioPresenca($evento_id);
        
        $total_participantes = count($relatorio);
        $elegiveis = 0;
        
        foreach ($relatorio as $participante) {
            if ($participante['percentual_presenca'] >= 75.00) {
                $elegiveis++;
            }
        }
        
        return [
            'total_participantes' => $total_participantes,
            'elegiveis_certificado' => $elegiveis,
            'percentual_elegiveis' => $total_participantes > 0 ? 
                round(($elegiveis / $total_participantes) * 100, 2) : 0
        ];
    }
}
?>