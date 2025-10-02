<?php
/**
 * Bootstrap do Sistema Criança Feliz
 * Inicialização da estrutura MVC
 */

// Iniciar sessão se não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Definir constantes do sistema
define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . '/app');
define('DATA_PATH', BASE_PATH . '/data');
define('CSS_PATH', BASE_PATH . '/css');
define('JS_PATH', BASE_PATH . '/js');
define('IMG_PATH', BASE_PATH . '/img');

// Configurações de segurança
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Configurações de erro para desenvolvimento
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Autoloader simples para as classes
spl_autoload_register(function ($class) {
    $paths = [
        BASE_PATH . '/app/Controllers/' . $class . '.php',
        BASE_PATH . '/app/Models/' . $class . '.php',
        BASE_PATH . '/app/Services/' . $class . '.php'
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Função para sanitizar dados de entrada
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Função para validar email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Função para validar senha
function validatePassword($password) {
    return strlen($password) >= 6;
}

// Função para verificar se usuário está logado
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Função para redirecionar
function redirect($url) {
    header("Location: $url");
    exit();
}

// Função para incluir view
function view($viewName, $data = []) {
    extract($data);
    $viewPath = APP_PATH . '/Views/' . $viewName . '.php';
    
    if (file_exists($viewPath)) {
        include $viewPath;
    } else {
        throw new Exception("View não encontrada: $viewName");
    }
}

// Função para incluir layout
function layout($layoutName, $content, $data = []) {
    extract($data);
    $layoutPath = APP_PATH . '/Views/layouts/' . $layoutName . '.php';
    
    if (file_exists($layoutPath)) {
        include $layoutPath;
    } else {
        throw new Exception("Layout não encontrado: $layoutName");
    }
}

// Criar diretório de dados se não existir
if (!is_dir(DATA_PATH)) {
    mkdir(DATA_PATH, 0777, true);
}
