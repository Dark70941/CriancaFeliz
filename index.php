<?php
session_start();

// Verificar se o usuário já está logado
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Recuperar erros e dados do formulário
$errors = $_SESSION['login_errors'] ?? [];
$formData = $_SESSION['form_data'] ?? [];

// Limpar dados da sessão
unset($_SESSION['login_errors']);
unset($_SESSION['form_data']);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Associação Criança Feliz - Login</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="login-container">
            <!-- Lado esquerdo com a imagem -->
            <div class="image-section">
                <img src="img/84ee2f859c98cde210228f9cf472d03b4932ff8c.jpg" alt="Crianças felizes" class="children-image">
            </div>
            
            <!-- Lado direito com o formulário -->
            <div class="form-section">
                <div class="logo-section">
                    <img src="img/logo.png" alt="Associação Criança Feliz" class="logo-img">
                </div>
                
                <div class="login-form">
                    <h2 class="login-title">LOGIN</h2>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="error-messages">
                            <?php foreach ($errors as $error): ?>
                                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form id="loginForm" action="login.php" method="POST">
                        <div class="input-group">
                            <input type="email" id="email" name="email" placeholder="Digite seu email" value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="input-group">
                            <input type="password" id="password" name="password" placeholder="Digite sua senha" required>
                        </div>
                        
                        <div class="forgot-password">
                            <a href="forgot.php" id="forgotPassword">Esqueceu a senha?</a>
                        </div>
                        
                        <button type="submit" class="login-btn">Entrar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="js/script.js"></script>
    <!-- Chatbot -->
    <script src="js/chatbot.js"></script>
</body>
</html>
