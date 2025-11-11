<?php
// models/Evento.php

/**
 * A classe Evento é o Model.
 * Ela é responsável por toda a comunicação direta com o banco de dados
 * referente às tabelas 'eventos' e 'materiais_evento'.
 * Não contém regras de negócio (isso fica no Controller).
 */

require_once __DIR__ . '/../config/db.php';

class Evento {
    private $conn;

    /**
     * Pega a conexão com o banco de dados ao ser instanciada.
     */
    public function __construct() {
        $this->conn = Database::getConnection();
    }

    /**
     * Cria um novo evento no banco de dados.
     * Retorna o ID do evento criado.
     */
    public function create($titulo, $desc, $tipo, $cat, $inicio, $duracao, $prereq, $vagas, $criador_id, $status) {
        $sql = "INSERT INTO eventos (titulo, descricao, tipo, categoria, data_hora_inicio, duracao, pre_requisitos, limite_vagas, criador_id, status_aprovacao)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$titulo, $desc, $tipo, $cat, $inicio, $duracao, $prereq, $vagas, $criador_id, $status]);
        return $this->conn->lastInsertId();
    }

    /**
     * Retorna todos os eventos, ordenados pela data de início.
     */
    public function findAll() {
        $sql = "SELECT * FROM eventos ORDER BY data_hora_inicio ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca e retorna um evento específico pelo seu ID.
     */
    public function findById($id) {
        $sql = "SELECT * FROM eventos WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }   

    /**
     * Atualiza um evento no banco de dados.
     * $data é um array associativo (ex: ['titulo' => 'Novo Título', 'descricao' => '...'])
     * Retorna true se a atualização foi bem-sucedida, false caso contrário.
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];
        
        // Colunas permitidas para atualização
        $allowed_keys = ['titulo', 'descricao', 'tipo', 'categoria', 'data_hora_inicio', 'duracao', 'pre_requisitos', 'limite_vagas', 'status_aprovacao'];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowed_keys)) {
                $fields[] = "$key = ?"; // Ex: "titulo = ?"
                $params[] = $value;   // Ex: "Nova Palestra"
            }
        }
        
        if (empty($fields)) {
            return false; // Nenhum campo válido para atualizar
        }

        $sql = "UPDATE eventos SET " . implode(', ', $fields) . " WHERE id = ?";
        $params[] = $id; // Adiciona o ID no final do array de parâmetros
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->rowCount() > 0; // Retorna true se pelo menos uma linha foi afetada
    }

    /**
     * Deleta um evento do banco de dados pelo seu ID.
     * (A tabela 'materiais_evento' deve ter "ON DELETE CASCADE" para apagar os materiais juntos)
     * Retorna true se a deleção foi bem-sucedida.
     */
    public function delete($id) {
        $sql = "DELETE FROM eventos WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Muda o status de um evento para 'aprovado'.
     * Retorna true se a atualização foi bem-sucedida.
     */
    public function approve($id) {
        $sql = "UPDATE eventos SET status_aprovacao = 'aprovado' WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Adiciona um novo registro de material na tabela 'materiais_evento'.
     * Retorna o ID do material que foi salvo.
     */
    public function addMaterial($evento_id, $nome_arquivo, $caminho_arquivo) {
        $sql = "INSERT INTO materiais_evento (evento_id, nome_arquivo, caminho_arquivo) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$evento_id, $nome_arquivo, $caminho_arquivo]);
        return $this->conn->lastInsertId();
    }

    /**
     * (BÔNUS) Busca todos os materiais associados a um evento.
     */
    public function findMaterialsByEventId($evento_id) {
        $sql = "SELECT * FROM materiais_evento WHERE evento_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$evento_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Verifica se um novo evento (data/duração) entra em conflito com eventos existentes.
     * Retorna o evento conflitante se houver, ou false se não houver conflito.
     */
    public function checkConflict($data_hora_inicio, $duracao, $evento_id_excluir = null) {
        // Calcula a data/hora de término do NOVO evento
        $novo_fim = date('Y-m-d H:i:s', strtotime($data_hora_inicio . " + $duracao minutes"));
        $novo_inicio = $data_hora_inicio;

        // SQL para checar sobreposição: (Novo Início < Fim Antigo) E (Novo Fim > Início Antigo)
        $sql = "SELECT * FROM eventos WHERE 
                    ? < DATE_ADD(data_hora_inicio, INTERVAL duracao MINUTE) 
                    AND 
                    ? > data_hora_inicio";
        
        $params = [$novo_inicio, $novo_fim];

        // Se estivermos ATUALIZANDO, não queremos que o evento compare com ele mesmo
        if ($evento_id_excluir) {
            $sql .= " AND id != ?";
            $params[] = $evento_id_excluir;
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetch(PDO::FETCH_ASSOC); // Retorna o conflito ou 'false'
    }
}
?>