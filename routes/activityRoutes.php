<?php
// /routes/activityRoutes.php

header("Content-Type: application/json");

// Inclui (importa) as classes necessárias
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/activityModel.php';
require_once __DIR__ . '/../controllers/activityController.php';

// Obtém a conexão com o banco
$pdo = Database::getConnection();

// "Injeção de Dependência":
$model = new ActivityModel($pdo);
$controller = new ActivityController($model);

// Pede ao Controller para processar a requisição
$controller->handleRequest();
?>