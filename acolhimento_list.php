<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: index.php'); exit(); }

$dataFile = __DIR__ . '/data/acolhimento.json';
if (!is_dir(__DIR__ . '/data')) { mkdir(__DIR__ . '/data', 0777, true); }
if (!file_exists($dataFile)) { file_put_contents($dataFile, json_encode([])); }

function loadRecords($file) {
    $json = file_get_contents($file);
    $arr = json_decode($json, true);
    return is_array($arr) ? $arr : [];
}

function saveRecords($file, $records) {
    file_put_contents($file, json_encode(array_values($records), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
}

// Delete handler
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $records = loadRecords($dataFile);
    $records = array_filter($records, function($r) use ($id) { return $r['id'] !== $id; });
    saveRecords($dataFile, $records);
    header('Location: acolhimento_list.php?deleted=1');
    exit();
}

$q = trim($_GET['q'] ?? '');
$cpf = preg_replace('/\D+/', '', $_GET['cpf'] ?? '');

$records = loadRecords($dataFile);

// filter by name
if ($q !== '') {
    $records = array_filter($records, function($r) use ($q) {
        return stripos($r['nome_completo'] ?? '', $q) !== false;
    });
}

// filter by cpf
if ($cpf !== '') {
    $records = array_filter($records, function($r) use ($cpf) {
        return preg_replace('/\D+/', '', $r['cpf'] ?? '') === $cpf;
    });
}

// simple order by name
usort($records, function($a, $b) {
    return strcasecmp($a['nome_completo'] ?? '', $b['nome_completo'] ?? '');
});
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha de Acolhimento</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { background:#121a1f; margin:0; }
        .app { display:grid; grid-template-columns:80px 1fr; gap:20px; width:100vw; height:100vh; padding:20px; box-sizing:border-box; }
        .sidebar { background:#0e2a33; border-radius:16px; padding:16px 8px; display:flex; flex-direction:column; align-items:center; gap:18px; color:#fff; }
        .sidebar .logo { width:44px; height:auto; margin-bottom:6px; }
        .nav-icon { width:44px; height:44px; border-radius:10px; display:grid; place-items:center; background:#153945; color:#fff; font-weight:700; text-decoration:none; }
        .nav-icon.active { background:#ff7a00; }
        .content { background:#edf1f3; border-radius:16px; padding:20px; overflow:auto; }
        .topbar { display:flex; justify-content:space-between; align-items:center; }
        .user { display:flex; align-items:center; gap:10px; }
        .user .avatar { width:44px; height:44px; border-radius:50%; background:#cfd8dc; }
        .actions { display:flex; gap:10px; }
        .btn { background:#ff7a00; color:#fff; border:none; padding:10px 14px; border-radius:8px; cursor:pointer; text-decoration:none; }
        .btn.secondary { background:#6b7b84; }
        .btn.delete { background:#e74c3c; }
        .btn.back { background:#6fb64f; color:#fff; display:flex; align-items:center; gap:6px; }
        .searchbar { display:flex; gap:10px; margin:16px 0; }
        .searchbar input { padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; }
        table { width:100%; border-collapse:collapse; background:#fff; border-radius:12px; overflow:hidden; }
        thead { background:#e6782e; color:#fff; }
        th, td { padding:14px 12px; border-bottom:1px solid #eee; text-align:left; }
        tr:last-child td { border-bottom:none; }
    </style>
</head>
<body>
    <div class="app">
        <aside class="sidebar">
            <img src="img/logo.png" class="logo" alt="logo">
            <a class="nav-icon" href="dashboard.php" title="Início">🏠</a>
            <a class="nav-icon active" href="prontuarios.php" title="Prontuários">👥</a>
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
                    <div style="font-weight:700; font-size:24px;">Ficha de Acolhimento</div>
                </div>
                <div class="actions">
                    <a class="btn back" href="prontuarios.php">← Voltar</a>
                    <a class="btn" href="acolhimento_form.php">+ Cadastrar</a>
                </div>
            </div>

            <form method="get" class="searchbar">
                <input type="text" name="q" placeholder="Buscar por nome" value="<?php echo htmlspecialchars($q); ?>">
                <input type="text" name="cpf" placeholder="Buscar por CPF" value="<?php echo htmlspecialchars($cpf); ?>">
                <button class="btn" type="submit">Buscar</button>
                <a class="btn secondary" href="acolhimento_list.php">Limpar</a>
            </form>

            <table>
                <thead>
                    <tr>
                        <th>Nº</th>
                        <th>Nome</th>
                        <th>CPF</th>
                        <th>Nascimento</th>
                        <th>Data do Acolh.</th>
                        <th>Status</th>
                        <th style="width:160px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i=1; foreach ($records as $r): ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo htmlspecialchars($r['nome_completo'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($r['cpf'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($r['data_nasc'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($r['data_acolh'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($r['status'] ?? 'Ativo'); ?></td>
                            <td>
                                <a class="btn secondary" href="acolhimento_view.php?id=<?php echo urlencode($r['id']); ?>">Ver</a>
                                <a class="btn delete" href="?delete=<?php echo urlencode($r['id']); ?>" onclick="return confirm('Excluir este registro?');">Excluir</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($records)): ?>
                        <tr><td colspan="7" style="text-align:center; color:#6b7b84;">Nenhum registro encontrado.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </main>
    </div>
    
    <!-- Chatbot -->
    <script src="js/chatbot.js"></script>
    <!-- Modo Escuro -->
    <script src="js/theme-toggle.js"></script>
    <!-- Sistema de Notificações -->
    <script src="js/notifications.js"></script>
    
    <script>
        // Mostrar notificações baseadas nos parâmetros da URL
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            
            if (urlParams.get('saved') === '1') {
                setTimeout(() => {
                    window.notificationSystem.save('Ficha de acolhimento cadastrada com sucesso!');
                }, 500);
                
                // Limpar parâmetro da URL
                const newUrl = window.location.pathname;
                window.history.replaceState({}, document.title, newUrl);
            }
            
            if (urlParams.get('deleted') === '1') {
                setTimeout(() => {
                    window.notificationSystem.delete('Ficha de acolhimento excluída com sucesso!');
                }, 500);
                
                // Limpar parâmetro da URL
                const newUrl = window.location.pathname;
                window.history.replaceState({}, document.title, newUrl);
            }
            
            if (urlParams.get('edited') === '1') {
                setTimeout(() => {
                    window.notificationSystem.edit('Ficha de acolhimento editada com sucesso!');
                }, 500);
                
                // Limpar parâmetro da URL
                const newUrl = window.location.pathname;
                window.history.replaceState({}, document.title, newUrl);
            }
        });
    </script>
</body>
</html>


