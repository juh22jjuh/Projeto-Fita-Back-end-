<?php
// /routes/generalCommunityRoutes.php

header("Content-Type: application/json");

// Inclui (importa) as classes necessárias
// Note os caminhos corretos:
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/generalCommunityParticipantModel.php';
require_once __DIR__ . '/../controllers/generalCommunityParticipantController.php';

// Obtém a conexão com o banco
$pdo = Database::getConnection();

// "Injeção de Dependência":
// 1. Cria o Model (dando a ele a conexão com o banco)
$model = new GeneralCommunityParticipantModel($pdo);
// 2. Cria o Controller (dando a ele o Model)
$controller = new GeneralCommunityParticipantController($model);

// 3. Pede ao Controller para processar a requisição
$controller->handleRequest();
?>