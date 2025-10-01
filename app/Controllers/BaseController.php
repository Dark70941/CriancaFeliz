<?php

/**
 * Controller base com funcionalidades comuns
 */
class BaseController {
    protected $authService;
    
    public function __construct() {
        $this->authService = new AuthService();
    }
    
    /**
     * Renderiza uma view
     */
    protected function render($view, $data = []) {
        // Adicionar dados globais
        $data['currentUser'] = $this->authService->getCurrentUser();
        $data['isLoggedIn'] = $this->authService->isLoggedIn();
        
        view($view, $data);
    }
    
    /**
     * Renderiza uma view com layout
     */
    protected function renderWithLayout($layout, $view, $data = []) {
        // Adicionar dados globais
        $data['currentUser'] = $this->authService->getCurrentUser();
        $data['isLoggedIn'] = $this->authService->isLoggedIn();
        
        // Capturar conteúdo da view
        ob_start();
        view($view, $data);
        $content = ob_get_clean();
        
        // Renderizar layout com conteúdo
        layout($layout, $content, $data);
    }
    
    /**
     * Redireciona com mensagem de sucesso
     */
    protected function redirectWithSuccess($url, $message) {
        $_SESSION['flash_success'] = $message;
        redirect($url);
    }
    
    /**
     * Redireciona com mensagem de erro
     */
    protected function redirectWithError($url, $message) {
        $_SESSION['flash_error'] = $message;
        redirect($url);
    }
    
    /**
     * Retorna JSON
     */
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Valida se é requisição POST
     */
    protected function isPost() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
    
    /**
     * Valida se é requisição GET
     */
    protected function isGet() {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }
    
    /**
     * Obtém dados POST sanitizados
     */
    protected function getPostData() {
        return sanitizeInput($_POST);
    }
    
    /**
     * Obtém dados GET sanitizados
     */
    protected function getGetData() {
        return sanitizeInput($_GET);
    }
    
    /**
     * Obtém parâmetro específico
     */
    protected function getParam($key, $default = null) {
        return $_REQUEST[$key] ?? $default;
    }
    
    /**
     * Verifica se usuário está autenticado
     */
    protected function requireAuth() {
        $this->authService->requireAuth();
    }
    
    /**
     * Verifica permissão específica
     */
    protected function requirePermission($permission) {
        $this->authService->requirePermission($permission);
    }
    
    /**
     * Obtém mensagens flash
     */
    protected function getFlashMessages() {
        $messages = [];
        
        if (isset($_SESSION['flash_success'])) {
            $messages['success'] = $_SESSION['flash_success'];
            unset($_SESSION['flash_success']);
        }
        
        if (isset($_SESSION['flash_error'])) {
            $messages['error'] = $_SESSION['flash_error'];
            unset($_SESSION['flash_error']);
        }
        
        if (isset($_SESSION['flash_info'])) {
            $messages['info'] = $_SESSION['flash_info'];
            unset($_SESSION['flash_info']);
        }
        
        return $messages;
    }
    
    /**
     * Valida CSRF token
     */
    protected function validateCSRF() {
        if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token'])) {
            throw new Exception('Token CSRF inválido');
        }
        
        if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            throw new Exception('Token CSRF inválido');
        }
    }
    
    /**
     * Gera CSRF token
     */
    protected function generateCSRF() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Trata exceções
     */
    protected function handleException(Exception $e) {
        error_log("Erro no controller: " . $e->getMessage());
        
        if ($this->isAjaxRequest()) {
            $this->json(['error' => $e->getMessage()], 500);
        } else {
            $this->redirectWithError($_SERVER['HTTP_REFERER'] ?? 'index.php', $e->getMessage());
        }
    }
    
    /**
     * Verifica se é requisição AJAX
     */
    protected function isAjaxRequest() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Upload de arquivo
     */
    protected function uploadFile($fileKey, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'], $maxSize = 2097152) {
        if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        
        $file = $_FILES[$fileKey];
        
        // Verificar tamanho
        if ($file['size'] > $maxSize) {
            throw new Exception('Arquivo muito grande. Máximo: ' . ($maxSize / 1024 / 1024) . 'MB');
        }
        
        // Verificar tipo
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedTypes)) {
            throw new Exception('Tipo de arquivo não permitido. Permitidos: ' . implode(', ', $allowedTypes));
        }
        
        // Gerar nome único
        $fileName = uniqid() . '.' . $extension;
        $uploadDir = BASE_PATH . '/uploads/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $uploadPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return 'uploads/' . $fileName;
        }
        
        throw new Exception('Erro ao fazer upload do arquivo');
    }
}
