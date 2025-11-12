-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
<<<<<<< HEAD
-- Tempo de geração: 11/11/2025 às 13:39
=======
-- Tempo de geração: 11/11/2025 às 12:20
>>>>>>> 9182ad2510c01346c2d1fc0c97a7df826f49ffc5
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `fita-bd`
--

-- --------------------------------------------------------

--


-- Tabela para check-ins
CREATE TABLE IF NOT EXISTS `checkins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `evento_id` int(11) NOT NULL,
  `qr_code_token` varchar(255) NOT NULL UNIQUE,
  `data_hora_checkin` datetime DEFAULT NULL,
  `data_hora_checkout` datetime DEFAULT NULL,
  `status` enum('presente','ausente','atrasado') DEFAULT 'ausente',
  `minutos_presente` int(11) DEFAULT 0,
  `criado_em` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- Tabela para configurações de certificado
CREATE TABLE IF NOT EXISTS `configuracoes_certificado` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `evento_id` int(11) NOT NULL,
  `presenca_minima` decimal(5,2) DEFAULT 75.00,
  `tolerancia_atraso` int(11) DEFAULT 10,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

<<<<<<< HEAD
-- Estrutura para tabela `eventos`
--

CREATE TABLE `eventos` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descricao` text NOT NULL,
  `tipo` enum('palestra','oficina','minicurso') NOT NULL,
  `categoria` enum('industrial','tecnologica','agronegocio') NOT NULL,
  `data_hora_inicio` datetime NOT NULL,
  `duracao` int(11) NOT NULL,
  `pre_requisitos` text DEFAULT NULL,
  `limite_vagas` int(11) NOT NULL,
  `status_aprovacao` enum('pendente','aprovado','rejeitado') NOT NULL DEFAULT 'pendente',
  `criador_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `materiais_evento`
--

CREATE TABLE `materiais_evento` (
  `id` int(11) NOT NULL,
  `evento_id` int(11) NOT NULL,
  `nome_arquivo` varchar(255) NOT NULL,
  `caminho_arquivo` varchar(512) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `perfis`
--

CREATE TABLE `perfis` (
=======
-- Estrutura para tabela `perfis`
--

CREATE TABLE IF NOT EXISTS `perfis` (
>>>>>>> 9182ad2510c01346c2d1fc0c97a7df826f49ffc5
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `instituicao` varchar(255) DEFAULT NULL,
  `perfil_completo` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `perfis`
--

INSERT INTO `perfis` (`id`, `usuario_id`, `telefone`, `instituicao`, `perfil_completo`) VALUES
(2, 2, NULL, NULL, 0);

<<<<<<< HEAD
=======

--
-- Estrutura para tabela `atividades`
--

CREATE TABLE IF NOT EXISTS atividades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT,
    data_hora_inicio DATETIME,
    data_hora_fim DATETIME,
    local VARCHAR(255),
    palestrante_nome VARCHAR(255),
    vagas_disponiveis INT,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Índices de tabela `atividades`
--
ALTER TABLE `atividades`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de tabela `atividades`
--
ALTER TABLE `atividades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
>>>>>>> 9182ad2510c01346c2d1fc0c97a7df826f49ffc5
-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

<<<<<<< HEAD
CREATE TABLE `usuarios` (
=======
CREATE TABLE IF NOT EXISTS `usuarios` (
>>>>>>> 9182ad2510c01346c2d1fc0c97a7df826f49ffc5
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `nivel_acesso` enum('participante','palestrante','organizador','administrador') NOT NULL DEFAULT 'participante',
  `tentativas_falhas` int(11) DEFAULT 0,
  `bloqueado_ate` datetime DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `nivel_acesso`, `tentativas_falhas`, `bloqueado_ate`, `criado_em`) VALUES
(2, 'felipe gabriel', 'felipezica7000@gmail.com', '$2y$10$wbbHC8tGKv8z1D/3bnUJCujaS5aE1fQ/SGWACRV/CAPhM7uIvgboa', 'participante', 0, NULL, '2025-11-11 11:12:57');

--
-- Índices para tabelas despejadas
--

--
<<<<<<< HEAD
-- Índices de tabela `eventos`
--
ALTER TABLE `eventos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `criador_id` (`criador_id`);

--
-- Índices de tabela `materiais_evento`
--
ALTER TABLE `materiais_evento`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evento_id` (`evento_id`);

--
=======
>>>>>>> 9182ad2510c01346c2d1fc0c97a7df826f49ffc5
-- Índices de tabela `perfis`
--
ALTER TABLE `perfis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
<<<<<<< HEAD
-- AUTO_INCREMENT de tabela `eventos`
--
ALTER TABLE `eventos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `materiais_evento`
--
ALTER TABLE `materiais_evento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
=======
>>>>>>> 9182ad2510c01346c2d1fc0c97a7df826f49ffc5
-- AUTO_INCREMENT de tabela `perfis`
--
ALTER TABLE `perfis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restrições para tabelas despejadas
--

--
<<<<<<< HEAD
-- Restrições para tabelas `eventos`
--
ALTER TABLE `eventos`
  ADD CONSTRAINT `eventos_ibfk_1` FOREIGN KEY (`criador_id`) REFERENCES `usuarios` (`id`);

--
-- Restrições para tabelas `materiais_evento`
--
ALTER TABLE `materiais_evento`
  ADD CONSTRAINT `materiais_evento_ibfk_1` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON DELETE CASCADE;

--
=======
>>>>>>> 9182ad2510c01346c2d1fc0c97a7df826f49ffc5
-- Restrições para tabelas `perfis`
--
ALTER TABLE `perfis`
  ADD CONSTRAINT `perfis_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
