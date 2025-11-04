<?php
// /routes/fatecStudentRoutes.php

header("Content-Type: application/json");

// Inclui (importa) as classes necessárias
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/fatecStudentModel.php';
require_once __DIR__ . '/../controllers/fatecStudentController.php';

// Obtém a conexão com o banco
$pdo = Database::getConnection();

// "Injeção de Dependência":
$model = new FatecStudentModel($pdo);
$controller = new FatecStudentController($model);

// Pede ao Controller para processar a requisição
$controller->handleRequest();
?>