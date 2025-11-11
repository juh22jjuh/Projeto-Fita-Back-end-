<?php
// /models/activityModel.php

class ActivityModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM atividades");
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM atividades WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $sql = "INSERT INTO atividades 
                (titulo, descricao, data_hora_inicio, data_hora_fim, local, palestrante_nome, vagas_disponiveis) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['titulo'],
            $data['descricao'] ?? null,
            $data['data_hora_inicio'] ?? null,
            $data['data_hora_fim'] ?? null,
            $data['local'] ?? null,
            $data['palestrante_nome'] ?? null,
            $data['vagas_disponiveis'] ?? null
        ]);
        return $this->pdo->lastInsertId();
    }

    public function update($id, $data) {
        $sql = "UPDATE atividades SET 
                titulo = ?, descricao = ?, data_hora_inicio = ?, data_hora_fim = ?, 
                local = ?, palestrante_nome = ?, vagas_disponiveis = ?
                WHERE id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['titulo'],
            $data['descricao'] ?? null,
            $data['data_hora_inicio'] ?? null,
            $data['data_hora_fim'] ?? null,
            $data['local'] ?? null,
            $data['palestrante_nome'] ?? null,
            $data['vagas_disponiveis'] ?? null,
            $id
        ]);
        return $stmt->rowCount();
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM atividades WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount();
    }
}
?>