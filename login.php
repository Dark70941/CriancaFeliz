<?php
session_start();

// Configurações de segurança
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Função para sanitizar dados de entrada
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
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

// Verificar se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = sanitizeInput($_POST['password'] ?? '');
    
    $errors = [];
    
    // Validações
    if (empty($email)) {
        $errors[] = "Email é obrigatório.";
    } elseif (!validateEmail($email)) {
        $errors[] = "Email inválido.";
    }
    
    if (empty($password)) {
        $errors[] = "Senha é obrigatória.";
    } elseif (!validatePassword($password)) {
        $errors[] = "A senha deve ter pelo menos 6 caracteres.";
    }
    
    // Se não há erros, processar o login
    if (empty($errors)) {
        // Aqui você implementaria a verificação no banco de dados
        // Por enquanto, vamos usar credenciais de teste
        $validEmail = "admin@criancafeliz.org";
        $validPassword = "123456";
        
        if ($email === $validEmail && $password === $validPassword) {
            // Login bem-sucedido
            $_SESSION['user_id'] = 1;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_name'] = "Administrador";
            $_SESSION['login_time'] = time();
            
            // Regenerar ID da sessão por segurança
            session_regenerate_id(true);
            
            // Redirecionar para dashboard (quando criado)
            header("Location: dashboard.php");
            exit();
        } else {
            $errors[] = "Email ou senha incorretos.";
        }
    }
    
    // Se há erros, armazenar na sessão e redirecionar de volta
    if (!empty($errors)) {
        $_SESSION['login_errors'] = $errors;
        $_SESSION['form_data'] = ['email' => $email];
        header("Location: index.php");
        exit();
    }
} else {
    // Se não é POST, redirecionar para a página de login
    header("Location: index.php");
    exit();
}
?>
