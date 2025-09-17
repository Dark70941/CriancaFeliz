<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$userName = $_SESSION['user_name'] ?? 'Usuário';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Associação Criança Feliz</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            min-height: 100vh;
        }
        
        .header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .welcome-message {
            font-size: 24px;
            color: #2c3e50;
            font-weight: 600;
        }
        
        .logout-btn {
            background: #e74c3c;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.3s ease;
        }
        
        .logout-btn:hover {
            background: #c0392b;
        }
        
        .content {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .coming-soon {
            font-size: 48px;
            color: #27ae60;
            margin-bottom: 20px;
            font-weight: 700;
        }
        
        .description {
            font-size: 18px;
            color: #7f8c8d;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="header">
            <div class="welcome-message">
                Bem-vindo, <?php echo htmlspecialchars($userName); ?>!
            </div>
            <a href="logout.php" class="logout-btn">Sair</a>
        </div>
        
        <div class="content">
            <div class="coming-soon">🚧 Em Desenvolvimento 🚧</div>
            <div class="description">
                O sistema de agendamento e gerenciamento da Associação Criança Feliz está sendo desenvolvido.<br>
                Em breve você terá acesso a todas as funcionalidades para:<br><br>
                
                📅 <strong>Agendamentos</strong><br>
                👶 <strong>Informações das Crianças</strong><br>
                📊 <strong>Relatórios</strong><br>
                ⚙️ <strong>Configurações</strong>
            </div>
        </div>
    </div>
</body>
</html>
