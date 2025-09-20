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

// Handle submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $records = loadRecords($dataFile);
    $id = uniqid('ac_');
    $payload = $_POST;
    $payload['id'] = $id;
    $payload['status'] = 'Ativo';
    // normalize cpf
    if (isset($payload['cpf'])) { $payload['cpf'] = preg_replace('/\D+/', '', $payload['cpf']); }
    $records[] = $payload;
    saveRecords($dataFile, $records);
    header('Location: acolhimento_list.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar - Ficha de Acolhimento</title>
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
        .stepper { display:flex; gap:8px; margin:16px 0 24px; }
        .chip { padding:8px 12px; border-radius:20px; background:#cfd8dc; font-weight:600; }
        .chip.active { background:#ffdab9; }
        form .grid { display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; }
        label { font-size:14px; color:#354047; font-weight:600; }
        input, select, textarea { padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; }
        textarea { min-height:90px; resize:vertical; }
        .box { background:#fff; border-radius:12px; padding:16px; margin-bottom:16px; }
        .actions { display:flex; gap:10px; justify-content:flex-end; }
        .btn { background:#ff7a00; color:#fff; border:none; padding:10px 14px; border-radius:8px; cursor:pointer; text-decoration:none; }
        .btn.secondary { background:#6b7b84; }
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
                <div style="font-weight:700; font-size:24px;">Ficha de Acolhimento - Cadastrar</div>
                <a href="acolhimento_list.php" class="btn secondary">Voltar</a>
            </div>

            <div class="stepper">
                <div class="chip active">1. Dados iniciais</div>
                <div class="chip">2. Endereço</div>
                <div class="chip">3. Responsável</div>
                <div class="chip">4. Documentos</div>
            </div>

            <form method="post">
                <!-- Etapa 1 -->
                <div class="box">
                    <div class="grid">
                        <div>
                            <label>Nome Completo</label>
                            <input type="text" name="nome_completo" required>
                        </div>
                        <div>
                            <label>RG</label>
                            <input type="text" name="rg">
                        </div>
                        <div>
                            <label>CPF</label>
                            <input type="text" name="cpf" required>
                        </div>
                        <div>
                            <label>Data de Nasc.</label>
                            <input type="text" name="data_nasc" placeholder="dd/mm/aaaa" required>
                        </div>
                        <div>
                            <label>Data do Acolh.</label>
                            <input type="text" name="data_acolh" placeholder="dd/mm/aaaa" required>
                        </div>
                        <div>
                            <label>Encaminha por</label>
                            <input type="text" name="encaminha_por">
                        </div>
                        <div style="grid-column:1 / -1;">
                            <label>Queixa Principal</label>
                            <textarea name="queixa"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Etapa 2 -->
                <div class="box">
                    <div class="grid">
                        <div>
                            <label>Endereço</label>
                            <input type="text" name="endereco">
                        </div>
                        <div>
                            <label>Nº</label>
                            <input type="text" name="numero">
                        </div>
                        <div>
                            <label>CEP</label>
                            <input type="text" name="cep">
                        </div>
                        <div>
                            <label>Bairro</label>
                            <input type="text" name="bairro">
                        </div>
                        <div>
                            <label>Cidade</label>
                            <input type="text" name="cidade">
                        </div>
                        <div>
                            <label>Complemento</label>
                            <input type="text" name="complemento">
                        </div>
                        <div>
                            <label>Ponto de Referência</label>
                            <input type="text" name="ponto_referencia">
                        </div>
                        <div>
                            <label>Escola</label>
                            <input type="text" name="escola">
                        </div>
                        <div>
                            <label>Período</label>
                            <select name="periodo">
                                <option value="">Selecionar</option>
                                <option>Manhã</option>
                                <option>Tarde</option>
                                <option>Noite</option>
                            </select>
                        </div>
                        <div>
                            <label>CRAS de Referência</label>
                            <input type="text" name="cras">
                        </div>
                        <div>
                            <label>UBS de Referência</label>
                            <input type="text" name="ubs">
                        </div>
                    </div>
                </div>

                <!-- Etapa 3 -->
                <div class="box">
                    <div class="grid">
                        <div>
                            <label>Nome do Responsável</label>
                            <input type="text" name="responsavel_nome">
                        </div>
                        <div>
                            <label>Grau de Parentesco</label>
                            <input type="text" name="responsavel_parentesco">
                        </div>
                        <div>
                            <label>Contato 1</label>
                            <input type="text" name="contato1">
                        </div>
                        <div>
                            <label>Contato 2</label>
                            <input type="text" name="contato2">
                        </div>
                        <div>
                            <label>Contato 3</label>
                            <input type="text" name="contato3">
                        </div>
                        <div>
                            <label>Contato 4</label>
                            <input type="text" name="contato4">
                        </div>
                    </div>
                </div>

                <!-- Etapa 4 -->
                <div class="box">
                    <div class="grid">
                        <div>
                            <label>Cadastro Único</label>
                            <select name="cad_unico">
                                <option value="">Selecionar</option>
                                <option>Possuo</option>
                                <option>Não Possuo</option>
                            </select>
                        </div>
                        <div>
                            <label>Responsável pelo Acolhimento</label>
                            <input type="text" name="acolhimento_responsavel">
                        </div>
                        <div>
                            <label>Função/Carimbo</label>
                            <input type="text" name="acolhimento_funcao">
                        </div>
                    </div>
                </div>

                <div class="actions">
                    <button class="btn" type="submit">Cadastrar</button>
                </div>
            </form>
        </main>
    </div>
    <!-- Chatbot -->
    <script src="js/chatbot.js"></script>
</body>
</html>


