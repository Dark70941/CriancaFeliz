<?php
/**
 * Sistema de Desligamento
 */

require_once 'bootstrap.php';

// Instanciar controller
$controller = new DesligamentoController();

// Obter ação da URL
$action = $_GET['action'] ?? 'index';

// Mapear ações para métodos
$actions = [
    'index' => 'index',
    'novo' => 'novo',
    'salvar' => 'salvar',
    'reativar' => 'reativar',
    'automatico' => 'automatico'
];

// Executar ação
if (isset($actions[$action])) {
    $method = $actions[$action];
    
    // Se a ação precisa de ID
    if ($action === 'novo' && isset($_GET['id'])) {
        $controller->$method($_GET['id']);
    } else {
        $controller->$method();
    }
} else {
    // Ação não encontrada
    header('Location: desligamento.php');
    exit;
}
