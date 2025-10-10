<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Sistema Criança Feliz'; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }
        
        /* Exceção para ícones do Font Awesome */
        .fa, .fas, .far, .fal, .fab {
            font-family: "Font Awesome 6 Free" !important;
        }
        
        body { 
            background: #3E6475; 
            margin: 0; 
            padding: 0; 
            font-family: 'Poppins', sans-serif;
        }
        .app {
            display: grid;
            grid-template-columns: 80px 1fr;
            gap: 20px;
            width: 100vw;
            height: 100vh;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }
        .sidebar {
            background: #0e2a33;
            border-radius: 16px;
            padding: 16px 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 18px;
            color: #fff;
        }
        .sidebar .logo { width: 44px; height: auto; margin-bottom: 6px; }
        .nav-icon {
            width: 44px; height: 44px; border-radius: 10px; display: grid; place-items: center;
            background: #153945; color: #fff; font-weight: 700; text-decoration: none;
            font-size: 18px;
        }
        .nav-icon.active { background: #ff7a00; }
        .nav-icon i { font-size: 18px; }
        .content {
            background: #edf1f3;
            border-radius: 16px;
            padding: 20px;
            overflow: auto;
        }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .user { display: flex; align-items: center; gap: 10px; }
        .user .avatar { width: 44px; height: 44px; border-radius: 50%; background: #cfd8dc; }
        .btn { 
            background: #ff7a00; color: #fff; border: none; padding: 10px 14px; 
            border-radius: 8px; cursor: pointer; text-decoration: none; display: inline-block;
            font-family: 'Poppins', sans-serif;
        }
        .btn.secondary { background: #6b7b84; }
        .btn:hover { opacity: 0.9; }
        
        input, select, textarea, button {
            font-family: 'Poppins', sans-serif;
        }
        
        h1, h2, h3, h4, h5, h6, p, span, div, a, label {
            font-family: 'Poppins', sans-serif;
        }
        
        /* Garantir que ícones do Font Awesome funcionem */
        i.fa, i.fas, i.far, i.fal, i.fab {
            font-family: "Font Awesome 6 Free" !important;
            font-style: normal;
        }
        
        /* Ícones do menu lateral em cinza claro */
        .nav-icon i {
            color: #b0bec5 !important; /* Cinza claro */
        }
        
        /* Ícone ativo mantém cor branca */
        .nav-icon.active i {
            color: #fff !important;
        }
        .actions { display: flex; gap: 10px; justify-content: flex-end; }
        
        /* Flash Messages */
        .flash-messages { margin-bottom: 20px; }
        .flash-message {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 10px;
            font-weight: 500;
        }
        .flash-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .flash-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .flash-info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        
        /* Responsividade */
        @media (max-width: 768px) {
            .app { 
                grid-template-columns: 1fr; 
                padding: 10px; 
                gap: 10px; 
            }
            .sidebar { 
                grid-row: 2;
                flex-direction: row; 
                padding: 8px 16px; 
                gap: 12px;
                overflow-x: auto;
            }
            .content { 
                grid-row: 1;
                padding: 15px; 
            }
            .topbar { 
                flex-direction: column; 
                gap: 10px; 
                align-items: flex-start; 
            }
            .user { 
                align-self: flex-end; 
            }
        }
    </style>
</head>
<body>
    <div class="app">
        <aside class="sidebar">
            <img src="img/logo.png" class="logo" alt="logo">
            <a class="nav-icon <?php echo (basename($_SERVER['PHP_SELF']) === 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php" title="Início"><i class="fas fa-home"></i></a>
            <a class="nav-icon <?php echo (strpos($_SERVER['PHP_SELF'], 'prontuarios') !== false) ? 'active' : ''; ?>" href="prontuarios.php" title="Prontuários"><i class="fas fa-users"></i></a>
            <a class="nav-icon <?php echo (strpos($_SERVER['PHP_SELF'], 'acolhimento') !== false) ? 'active' : ''; ?>" href="acolhimento_list.php" title="Acolhimento"><i class="fas fa-clipboard-list"></i></a>
            <a class="nav-icon <?php echo (strpos($_SERVER['PHP_SELF'], 'socioeconomico') !== false) ? 'active' : ''; ?>" href="socioeconomico_list.php" title="Socioeconômico"><i class="fas fa-home-lg-alt"></i></a>
            <a class="nav-icon <?php echo (strpos($_SERVER['PHP_SELF'], 'attendance') !== false) ? 'active' : ''; ?>" href="attendance.php" title="Controle de Faltas"><i class="fas fa-calendar-check"></i></a>
            <?php if ($currentUser['role'] === 'psicologo'): ?>
                <a class="nav-icon <?php echo (strpos($_SERVER['PHP_SELF'], 'psychology') !== false) ? 'active' : ''; ?>" href="psychology.php" title="Área Psicológica"><i class="fas fa-brain"></i></a>
            <?php endif; ?>
            <a class="nav-icon <?php echo (isset($_GET['action']) && $_GET['action'] === 'relatorios') ? 'active' : ''; ?>" href="attendance.php?action=relatorios" title="Relatórios de Frequência"><i class="fas fa-chart-line"></i></a>
            <?php if ($currentUser['role'] === 'admin'): ?>
                <a class="nav-icon <?php echo (strpos($_SERVER['PHP_SELF'], 'users') !== false) ? 'active' : ''; ?>" href="users.php" title="Gerenciar Usuários"><i class="fas fa-user-cog"></i></a>
            <?php endif; ?>
            <div class="nav-icon" title="Configurações"><i class="fas fa-cog"></i></div>
        </aside>
        
        <main class="content">
            <div class="topbar">
                <div>
                    <div style="font-weight:700; font-size:24px;"><?php echo $pageTitle ?? $title ?? 'Sistema Criança Feliz'; ?></div>
                </div>
                <div class="user">
                    <div class="avatar"></div>
                    <div><?php echo $currentUser['email'] ?? 'Usuário'; ?></div>
                    <a href="logout.php" class="btn secondary">Sair</a>
                </div>
            </div>
            
            <!-- Flash Messages -->
            <?php if (!empty($messages)): ?>
                <div class="flash-messages">
                    <?php foreach ($messages as $type => $message): ?>
                        <div class="flash-message flash-<?php echo $type; ?>">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <!-- Conteúdo da página -->
            <?php echo $content; ?>
        </main>
    </div>
    
    <!-- Scripts -->
    <script src="js/script.js"></script>
    <script src="js/chatbot.js"></script>
    <script src="js/theme-toggle.js"></script>
    <script src="js/notifications.js"></script>
    
    <?php if (isset($additionalScripts)): ?>
        <?php foreach ($additionalScripts as $script): ?>
            <script src="<?php echo $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
