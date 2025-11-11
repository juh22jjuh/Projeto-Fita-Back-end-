<?php
// controllers/EventoController.php

/**
 * A classe EventoController é o "Cérebro" da gestão de eventos.
 * Ela aplica as Regras de Negócio e validações antes de chamar o Model.
 */

require_once __DIR__ . '/../models/eventoModel.php';
// Não precisamos do Middleware aqui, pois a ROTA é que vai usá-lo
// para nos enviar os dados do usuário (ID e Nível)

class EventoController {
    
    private $eventoModel;

    public function __construct() {
        $this->eventoModel = new Evento();
    }

    /**
     * Pega todos os eventos.
     * (Qualquer um pode ver os eventos)
     */
    public function getAll() {
        try {
            $eventos = $this->eventoModel->findAll();
            return ['status' => 200, 'data' => $eventos];
        } catch (PDOException $e) {
            return ['status' => 500, 'message' => 'Erro ao buscar eventos: ' . $e->getMessage()];
        }
    }

    /**
     * Pega um evento específico por ID.
     * (Qualquer um pode ver)
     */
    public function getById($id) {
        try {
            $evento = $this->eventoModel->findById($id);
            if (!$evento) {
                return ['status' => 404, 'message' => 'Evento não encontrado.'];
            }
            // (BÔNUS) Pega os materiais associados
            $evento['materiais'] = $this->eventoModel->findMaterialsByEventId($id);
            return ['status' => 200, 'data' => $evento];
        } catch (PDOException $e) {
            return ['status' => 500, 'message' => 'Erro ao buscar evento: ' . $e->getMessage()];
        }
    }

    /**
     * Cria um novo evento, aplicando todas as regras de negócio.
     * $data = dados do formulário
     * $user_id = ID do usuário logado (da sessão)
     * $user_role = Nível do usuário logado (da sessão)
     */
    public function create($data, $user_id, $user_role) {
        // --- 1. Validação de Dados e Regras de Negócio ---

        // Regra: Descrição obrigatória com mínimo 100 caracteres
        if (empty($data['descricao']) || strlen($data['descricao']) < 100) {
            return ['status' => 400, 'message' => 'Descrição é obrigatória e deve ter no mínimo 100 caracteres.'];
        }

        // Regra: Máximo 50 (oficina) ou 200 (palestra)
        $limite_vagas = 0;
        if ($data['tipo'] == 'oficina') {
            $limite_vagas = 50;
        } else if ($data['tipo'] == 'palestra') {
            $limite_vagas = 200;
        } else if ($data['tipo'] == 'minicurso' && !empty($data['limite_vagas'])) {
            $limite_vagas = (int)$data['limite_vagas']; // Minicurso pode ter limite customizado
        } else {
             return ['status' => 400, 'message' => 'Tipo de evento inválido ou limite de vagas não especificado para minicurso.'];
        }

        // Regra: Conflitos de horário não permitidos
        if ($this->eventoModel->checkConflict($data['data_hora_inicio'], $data['duracao'])) {
            return ['status' => 409, 'message' => 'Conflito de horário! Já existe um evento neste período.'];
        }

        // Requisito: Sistema de aprovação de conteúdo
        // Se for palestrante, o evento nasce pendente. Se for admin/organizador, já nasce aprovado.
        if ($user_role == 'palestrante') {
            $status_aprovacao = 'pendente';
        } else if ($user_role == 'organizador' || $user_role == 'administrador') {
            $status_aprovacao = 'aprovado';
        } else {
             return ['status' => 403, 'message' => 'Você não tem permissão para criar eventos.'];
        }

        // --- 2. Se passou em tudo, pode criar ---
        try {
            $evento_id = $this->eventoModel->create(
                $data['titulo'],
                $data['descricao'],
                $data['tipo'],
                $data['categoria'],
                $data['data_hora_inicio'],
                (int)$data['duracao'],
                $data['pre_requisitos'] ?? null,
                $limite_vagas,
                $user_id, // criador_id
                $status_aprovacao
            );
            return ['status' => 201, 'message' => 'Evento criado com sucesso.', 'evento_id' => $evento_id];
        
        } catch (PDOException $e) {
            return ['status' => 500, 'message' => 'Erro de banco de dados ao criar evento: ' . $e->getMessage()];
        }
    }

    /**
     * Atualiza um evento existente.
     * $data = dados do formulário (incluindo $data['evento_id'])
     * $user_id / $user_role = dados do usuário logado
     */
    public function update($data, $user_id, $user_role) {
        try {
            // 1. Verifica se o evento existe
            $evento_id = $data['evento_id'];
            $evento = $this->eventoModel->findById($evento_id);
            if (!$evento) {
                return ['status' => 404, 'message' => 'Evento não encontrado.'];
            }

            // 2. Verifica Permissão
            // Somente admin/organizador ou o PRÓPRIO criador podem editar
            if ($user_role != 'administrador' && $user_role != 'organizador' && $evento['criador_id'] != $user_id) {
                return ['status' => 403, 'message' => 'Você não tem permissão para editar este evento.'];
            }

            // 3. Aplica Regras de Negócio (se os campos relevantes foram alterados)
            
            // Regra: Descrição (se foi alterada)
            if (isset($data['descricao']) && strlen($data['descricao']) < 100) {
                 return ['status' => 400, 'message' => 'Descrição deve ter no mínimo 100 caracteres.'];
            }
            
            // Regra: Conflito de horário (se data/duração mudou)
            $inicio_check = $data['data_hora_inicio'] ?? $evento['data_hora_inicio'];
            $duracao_check = $data['duracao'] ?? $evento['duracao'];
            
            // Checa conflito, ignorando o próprio evento que estamos editando
            if ($this->eventoModel->checkConflict($inicio_check, $duracao_check, $evento_id)) {
                return ['status' => 409, 'message' => 'Conflito de horário! Já existe outro evento neste período.'];
            }

            // (Não vamos re-validar limite de vagas aqui, para simplificar)

            // 4. Se passou, atualiza
            $success = $this->eventoModel->update($evento_id, $data);
            
            if ($success) {
                return ['status' => 200, 'message' => 'Evento atualizado com sucesso.'];
            }
            return ['status' => 200, 'message' => 'Nenhuma alteração detectada.'];

        } catch (PDOException $e) {
            return ['status' => 500, 'message' => 'Erro ao atualizar evento: ' . $e->getMessage()];
        }
    }

    /**
     * Deleta um evento.
     * $evento_id = ID do evento a deletar
     * $user_id / $user_role = dados do usuário logado
     */
    public function delete($evento_id, $user_id, $user_role) {
         try {
            // 1. Verifica se o evento existe
            $evento = $this->eventoModel->findById($evento_id);
            if (!$evento) {
                return ['status' => 404, 'message' => 'Evento não encontrado.'];
            }

            // 2. Verifica Permissão
            // Somente admin/organizador ou o PRÓPRIO criador podem deletar
            if ($user_role != 'administrador' && $user_role != 'organizador' && $evento['criador_id'] != $user_id) {
                return ['status' => 403, 'message' => 'Você não tem permissão para deletar este evento.'];
            }

            // 3. Deleta
            $this->eventoModel->delete($evento_id);
            return ['status' => 200, 'message' => 'Evento deletado com sucesso.'];

        } catch (PDOException $e) {
            return ['status' => 500, 'message' => 'Erro ao deletar evento: ' . $e->getMessage()];
        }
    }

    /**
     * Aprova um evento pendente.
     * $evento_id = ID do evento
     * $user_role = Nível do usuário logado
     */
    public function approve($evento_id, $user_role) {
        // 1. Verifica Permissão (Somente admin ou organizador podem aprovar)
        if ($user_role != 'administrador' && $user_role != 'organizador') {
            return ['status' => 403, 'message' => 'Você não tem permissão para aprovar eventos.'];
        }

        try {
            // 2. Tenta aprovar
            $success = $this->eventoModel->approve($evento_id);
            if ($success) {
                 return ['status' => 200, 'message' => 'Evento aprovado com sucesso.'];
            }
            return ['status' => 404, 'message' => 'Evento não encontrado ou já estava aprovado.'];

        } catch (PDOException $e) {
             return ['status' => 500, 'message' => 'Erro ao aprovar evento: ' . $e->getMessage()];
        }
    }

    /*
     * NOTA SOBRE UPLOAD DE MATERIAIS:
     * A função 'addMaterial' está no Model, o que é correto.
     * No entanto, o CONTROLLER não lida com uploads de arquivos (multipart/form-data).
     * O upload será gerenciado por uma ROTA específica (ex: 'routes/uploadMaterial.php'),
     * que cuidará do $_FILES, moverá o arquivo e chamará o Model 'addMaterial' diretamente.
     * Por isso, não há uma função 'addMaterial' neste controller.
     */
}
?>