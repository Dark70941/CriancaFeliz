<?php
session_start();
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
    <title>Prontuários - Associação Criança Feliz</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { background:#121a1f; margin:0; }
        .app { display:grid; grid-template-columns:80px 1fr; gap:20px; width:100vw; height:100vh; padding:20px; box-sizing:border-box; }
        .sidebar { background:#0e2a33; border-radius:16px; padding:16px 8px; display:flex; flex-direction:column; align-items:center; gap:18px; color:#fff; }
        .sidebar .logo { width:44px; height:auto; margin-bottom:6px; }
        .nav-icon { width:44px; height:44px; border-radius:10px; display:grid; place-items:center; background:#153945; color:#fff; font-weight:700; text-decoration:none; }
        .nav-icon.active { background:#ff7a00; }
        .content { background:#edf1f3; border-radius:16px; padding:20px; }
        .topbar { display:flex; justify-content:space-between; align-items:center; }
        .user { display:flex; align-items:center; gap:10px; }
        .user .avatar { width:44px; height:44px; border-radius:50%; background:#cfd8dc; }
        .folders { display:flex; gap:30px; margin-top:24px; flex-wrap:wrap; }
        .folder { width:260px; height:180px; background:#ff7a00; border-radius:16px; box-shadow:0 8px 20px rgba(0,0,0,.12); padding:18px; color:#fff; position:relative; cursor:pointer; text-decoration:none; }
        .folder:after { content:""; position:absolute; top:-12px; left:24px; width:56px; height:26px; background:#ff7a00; border-top-left-radius:8px; border-top-right-radius:8px; }
        .folder-title { font-weight:800; font-size:28px; letter-spacing:0.5px; }
        .folder-sub { margin-top:6px; font-size:18px; opacity:.95; }
    </style>
</head>
<body>
    <div class="app">
        <aside class="sidebar">
            <img src="img/logo.png" class="logo" alt="logo">
            <a class="nav-icon" href="dashboard.php" title="Início">🏠</a>
            <div class="nav-icon active" title="Prontuários">👥</div>
            <div class="nav-icon" title="Relatórios">📈</div>
            <div class="nav-icon" title="Usuários">👤❌</div>
            <div class="nav-icon" title="Permissões">👤🔒</div>
            <div class="nav-icon" title="Ideias">💡</div>
            <div class="nav-icon" title="Documentos">📋</div>
            <div class="nav-icon" title="Editar">📝</div>
            <div class="nav-icon" title="Administrador">🤵</div>
        </aside>
        <main class="content">
            <div class="topbar">
                <div>
                    <div style="font-weight:700">Olá <?php echo htmlspecialchars($userName); ?> - Administrador</div>
                    <div style="font-size:14px;color:#6b7b84">Prontuário digital dos assistidos</div>
                </div>
                <div class="user">
                    <div class="avatar"></div>
                    <div><?php echo htmlspecialchars($_SESSION['user_email']); ?></div>
                    <a href="logout.php" class="logout-btn" style="background:#e74c3c;color:#fff;padding:10px 16px;border-radius:8px;text-decoration:none;">Sair</a>
                </div>
            </div>

            <div style="margin-top:18px;color:#344248">Aqui você pode acessar e registrar informações essenciais para o acompanhamento individual.</div>

            <div class="folders">
                <a class="folder" href="acolhimento_list.php">
                    <div class="folder-title">FICHA</div>
                    <div class="folder-sub">de Acolhimento</div>
                </a>
                <a class="folder" href="socioeconomico_list.php">
                    <div class="folder-title">FICHA</div>
                    <div class="folder-sub">Socioeconômico</div>
                </a>
            </div>
        </main>
    </div>
    <!-- Chatbot -->
    <script src="js/chatbot.js"></script>
</body>
</html>


