# API de Gerenciamento de Participantes (PHP/MySQL)

Este é um projeto de API backend construído em PHP puro para gerenciar o cadastro de participantes de eventos e usuários. Ele utiliza uma arquitetura MVC (Model-Controller) e protege as rotas de criação, atualização e deleção (POST, PUT, DELETE) com autenticação baseada em Sessão PHP.

## Estrutura de Endpoints

O projeto possui 3 CRUDs principais e 3 rotas de autenticação:

* **Autenticação:**
    * `routes/registerRoutes.php` (POST)
    * `routes/loginRoutes.php` (POST)
    * `routes/logoutRoutes.php` (GET/POST)
* **CRUDs:**
    * `routes/fatecStudentRoutes.php`
    * `routes/highSchoolRoutes.php`
    * `routes/generalCommunityRoutes.php`

## Pré-requisitos

* PHP 7.4 ou superior
* Servidor MySQL
* **XAMPP** (ou WAMP, MAMP)
* Um cliente de API como Postman (recomendado para testar rotas com sessão)

---

## 1. Instalação e Configuração

### Passo 1: Banco de Dados

1.  Inicie o **MySQL** pelo painel do XAMPP.
2.  Abra o **phpMyAdmin** (geralmente em `http://localhost/phpmyadmin`).
3.  Crie um novo banco de dados chamado `fita-bd` (conforme `config/db.php`).
4.  Clique no banco `fita-bd` e vá para a aba "SQL".
5.  Execute o script abaixo para criar as tabelas:

```sql
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    nivel_acesso VARCHAR(50) DEFAULT 'participante',
    tentativas_falhas INT DEFAULT 0,
    bloqueado_ate DATETIME NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS perfis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL UNIQUE,
    perfil_completo BOOLEAN DEFAULT false,
    -- Adicione outros campos de perfil aqui (foto, bio, etc.)
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS alunos_fatec (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_completo VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    nacionalidade VARCHAR(100),
    cidade VARCHAR(100),
    telefone VARCHAR(20),
    curso VARCHAR(100),
    semestre VARCHAR(20),
    data_nascimento DATE,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS participantes_ensino_medio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_completo VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    nacionalidade VARCHAR(100),
    telefone VARCHAR(20),
    serie VARCHAR(50),
    cidade VARCHAR(100),
    instituicao VARCHAR(255),
    data_nascimento DATE,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS participantes_comunidade_geral (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_completo VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    nacionalidade VARCHAR(100),
    telefone VARCHAR(20),
    grau_escolaridade VARCHAR(100),
    cidade VARCHAR(100),
    data_nascimento DATE,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);