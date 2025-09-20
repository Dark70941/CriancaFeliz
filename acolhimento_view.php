<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: index.php'); exit(); }

$dataFile = __DIR__ . '/data/acolhimento.json';
$id = $_GET['id'] ?? '';
$record = null;
if (file_exists($dataFile)) {
    $items = json_decode(file_get_contents($dataFile), true) ?: [];
    foreach ($items as $it) { if (($it['id'] ?? '') === $id) { $record = $it; break; } }
}
if (!$record) { header('Location: acolhimento_list.php'); exit(); }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizar Acolhimento</title>
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
        .box { background:#fff; border-radius:12px; padding:16px; margin-bottom:16px; }
        .grid { display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; }
        .field { background:#f7f9fa; border-radius:8px; padding:10px 12px; }
        .label { font-size:12px; color:#6b7b84; }
        .value { font-weight:600; color:#333; }
        .btn { background:#6b7b84; color:#fff; padding:10px 14px; border-radius:8px; text-decoration:none; }
        
        /* Campo de foto */
        .photo-field {
            grid-column: 1 / 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }
        
        .photo-placeholder {
            width: 120px;
            height: 160px;
            background: #e9ecef;
            border: 2px dashed #6c757d;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            font-size: 14px;
            text-align: center;
        }
        
        .photo-image {
            width: 120px;
            height: 160px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #dee2e6;
        }
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
                <div style="font-weight:700; font-size:24px;">Visualizar - Ficha de Acolhimento</div>
                <div class="actions">
                    <a class="btn" href="acolhimento_list.php">Voltar</a>
                </div>
            </div>

            <div class="box">
                <div class="grid">
                    <!-- Campo de Foto -->
                    <div class="photo-field">
                        <div class="label">Foto 3x4</div>
                        <?php if (!empty($record['foto'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($record['foto']); ?>" alt="Foto" class="photo-image">
                        <?php else: ?>
                            <div class="photo-placeholder">Sem foto</div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Dados Pessoais -->
                    <div class="field"><div class="label">Nome Completo</div><div class="value"><?php echo htmlspecialchars($record['nome_completo'] ?? ''); ?></div></div>
                    <div class="field"><div class="label">RG</div><div class="value"><?php echo htmlspecialchars($record['rg'] ?? ''); ?></div></div>
                    <div class="field"><div class="label">CPF</div><div class="value"><?php echo htmlspecialchars($record['cpf'] ?? ''); ?></div></div>
                    <div class="field"><div class="label">Data de Nascimento</div><div class="value"><?php echo htmlspecialchars($record['data_nascimento'] ?? $record['data_nasc'] ?? ''); ?></div></div>
                    <div class="field"><div class="label">Data de Acolhimento</div><div class="value"><?php echo htmlspecialchars($record['data_acolhimento'] ?? $record['data_acolh'] ?? ''); ?></div></div>
                    <div class="field"><div class="label">Encaminhado por</div><div class="value"><?php echo htmlspecialchars($record['encaminha_por'] ?? ''); ?></div></div>
                    <div class="field" style="grid-column:1 / -1;"><div class="label">Queixa Principal</div><div class="value"><?php echo nl2br(htmlspecialchars($record['queixa_principal'] ?? $record['queixa'] ?? '')); ?></div></div>
                </div>
            </div>

            <div class="box">
                <div class="grid">
                    <div class="field"><div class="label">Endereço</div><div class="value"><?php echo htmlspecialchars($record['endereco'] ?? ''); ?></div></div>
                    <div class="field"><div class="label">Número</div><div class="value"><?php echo htmlspecialchars($record['numero'] ?? ''); ?></div></div>
                    <div class="field"><div class="label">CEP</div><div class="value"><?php echo htmlspecialchars($record['cep'] ?? ''); ?></div></div>
                    <div class="field"><div class="label">Bairro</div><div class="value"><?php echo htmlspecialchars($record['bairro'] ?? ''); ?></div></div>
                    <div class="field"><div class="label">Cidade</div><div class="value"><?php echo htmlspecialchars($record['cidade'] ?? ''); ?></div></div>
                    <div class="field"><div class="label">Complemento</div><div class="value"><?php echo htmlspecialchars($record['complemento'] ?? ''); ?></div></div>
                    <div class="field"><div class="label">Ponto de Referência</div><div class="value"><?php echo htmlspecialchars($record['ponto_referencia'] ?? ''); ?></div></div>
                    <div class="field"><div class="label">Escola</div><div class="value"><?php echo htmlspecialchars($record['escola'] ?? ''); ?></div></div>
                    <div class="field"><div class="label">Período</div><div class="value"><?php echo htmlspecialchars($record['periodo'] ?? ''); ?></div></div>
                    <div class="field"><div class="label">CRAS</div><div class="value"><?php echo htmlspecialchars($record['cras'] ?? ''); ?></div></div>
                    <div class="field"><div class="label">UBS</div><div class="value"><?php echo htmlspecialchars($record['ubs'] ?? ''); ?></div></div>
                </div>
            </div>

            <div class="box">
                <div class="grid">
                    <div class="field"><div class="label">Nome do Responsável</div><div class="value"><?php echo htmlspecialchars($record['nome_responsavel'] ?? $record['responsavel_nome'] ?? ''); ?></div></div>
                    <div class="field"><div class="label">RG do Responsável</div><div class="value"><?php echo htmlspecialchars($record['rg_responsavel'] ?? ''); ?></div></div>
                    <div class="field"><div class="label">CPF do Responsável</div><div class="value"><?php echo htmlspecialchars($record['cpf_responsavel'] ?? ''); ?></div></div>
                    <div class="field"><div class="label">Grau de Parentesco</div><div class="value"><?php echo htmlspecialchars($record['grau_parentesco'] ?? $record['responsavel_parentesco'] ?? ''); ?></div></div>
                    <div class="field"><div class="label">Contato 1</div><div class="value"><?php echo htmlspecialchars($record['contato_1'] ?? $record['contato1'] ?? ''); ?></div></div>
                    <div class="field"><div class="label">Contato 2</div><div class="value"><?php echo htmlspecialchars($record['contato_2'] ?? $record['contato2'] ?? ''); ?></div></div>
                    <div class="field"><div class="label">Contato 3</div><div class="value"><?php echo htmlspecialchars($record['contato_3'] ?? $record['contato3'] ?? ''); ?></div></div>
                    <div class="field"><div class="label">Contato 4</div><div class="value"><?php echo htmlspecialchars($record['contato_4'] ?? $record['contato4'] ?? ''); ?></div></div>
                </div>
            </div>

            <div class="box">
                <div class="grid">
                    <div class="field"><div class="label">Cadastro Único</div><div class="value"><?php echo htmlspecialchars($record['cad_unico'] ?? ''); ?></div></div>
                    <div class="field"><div class="label">Resp. pelo Acolhimento</div><div class="value"><?php echo htmlspecialchars($record['acolhimento_responsavel'] ?? ''); ?></div></div>
                    <div class="field"><div class="label">Função/Carimbo</div><div class="value"><?php echo htmlspecialchars($record['acolhimento_funcao'] ?? ''); ?></div></div>
                </div>
            </div>
        </main>
    </div>
    <!-- Chatbot -->
    <script src="js/chatbot.js"></script>
    <!-- Modo Escuro -->
    <script src="js/theme-toggle.js"></script>
    <!-- Sistema de Notificações -->
    <script src="js/notifications.js"></script>
</body>
</html>


