<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: index.php'); exit(); }

$dataFile = __DIR__ . '/data/socioeconomico.json';
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
    $id = uniqid('soc_');
    $payload = $_POST;
    $payload['id'] = $id;
    // normalize cpf
    if (isset($payload['cpf'])) { $payload['cpf'] = preg_replace('/\D+/', '', $payload['cpf']); }
    $records[] = $payload;
    saveRecords($dataFile, $records);
    header('Location: socioeconomico_list.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar - Ficha Socioeconômico</title>
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
        form .grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
        label { font-size:14px; color:#354047; font-weight:600; }
        input, select, textarea { padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; }
        textarea { min-height:90px; resize:vertical; }
        .box { background:#fff; border-radius:12px; padding:16px; margin-bottom:16px; }
        .actions { display:flex; gap:10px; justify-content:flex-end; }
        .btn { background:#ff7a00; color:#fff; border:none; padding:10px 14px; border-radius:8px; cursor:pointer; text-decoration:none; }
        .btn.secondary { background:#6b7b84; }
        .radio-group { display:flex; gap:16px; margin:8px 0; }
        .radio-item { display:flex; align-items:center; gap:6px; }
        .table { width:100%; border-collapse:collapse; margin:12px 0; }
        .table th { background:#e6782e; color:#fff; padding:12px; text-align:left; }
        .table td { padding:12px; border-bottom:1px solid #eee; }
        .table input { border:1px solid #ddd; padding:8px; }
        .add-btn { background:#0e2a33; color:#fff; border:none; padding:8px 12px; border-radius:6px; cursor:pointer; }
        .modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; }
        .modal-content { position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:24px; border-radius:12px; width:500px; max-width:90vw; }
        .modal-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; }
        .modal-close { background:none; border:none; font-size:24px; cursor:pointer; }
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
                <div style="font-weight:700; font-size:24px;">Ficha Socioeconômico - Cadastrar</div>
                <div class="actions">
                    <a href="socioeconomico_list.php" class="btn secondary">Voltar</a>
                </div>
            </div>

            <div class="stepper">
                <div class="chip active">1. Dados iniciais</div>
                <div class="chip">2. Domicílio</div>
                <div class="chip">3. Composição familiar</div>
                <div class="chip">4. Despesas e rendas</div>
                <div class="chip">5. Outras informações</div>
            </div>

            <form method="post" id="socioForm">
                <!-- Etapa 1: Dados iniciais -->
                <div class="box">
                    <h3 style="margin-bottom:16px; color:#e6782e;">Dados Iniciais</h3>
                    <div class="grid">
                        <div>
                            <label>Nome do Entrevistado</label>
                            <input type="text" name="entrevistado" required>
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
                            <label>Nome do Menor</label>
                            <input type="text" name="menor" required>
                        </div>
                        <div>
                            <label>Data do Acolh.</label>
                            <input type="text" name="data_acolh" placeholder="dd/mm/aaaa" required>
                        </div>
                        <div>
                            <label>Assistente social responsável</label>
                            <input type="text" name="assistente" required>
                        </div>
                    </div>
                </div>

                <!-- Etapa 2: Características do Domicílio -->
                <div class="box">
                    <h3 style="margin-bottom:16px; color:#e6782e;">Características do Domicílio</h3>
                    
                    <div style="margin-bottom:16px;">
                        <label>Residência:</label>
                        <div class="radio-group">
                            <div class="radio-item"><input type="radio" name="tipo_residencia" value="Própria" id="propia"><label for="propia">Própria</label></div>
                            <div class="radio-item"><input type="radio" name="tipo_residencia" value="Alugada" id="alugada"><label for="alugada">Alugada</label></div>
                            <div class="radio-item"><input type="radio" name="tipo_residencia" value="Cedida" id="cedida"><label for="cedida">Cedida</label></div>
                            <div class="radio-item"><input type="radio" name="tipo_residencia" value="Financiada" id="financiada"><label for="financiada">Financiada</label></div>
                        </div>
                        <div style="margin-top:8px;">
                            <label>Valor aluguel/prestação: R$</label>
                            <input type="text" name="valor_aluguel" placeholder="0,00" style="width:120px;">
                        </div>
                    </div>

                    <div style="margin-bottom:16px;">
                        <label>Número de cômodos:</label>
                        <div class="radio-group">
                            <div class="radio-item"><input type="checkbox" name="comodos[]" value="Quarto" id="quarto"><label for="quarto">Quarto</label></div>
                            <div class="radio-item"><input type="checkbox" name="comodos[]" value="Banheiro" id="banheiro"><label for="banheiro">Banheiro</label></div>
                            <div class="radio-item"><input type="checkbox" name="comodos[]" value="Sala" id="sala"><label for="sala">Sala</label></div>
                            <div class="radio-item"><input type="checkbox" name="comodos[]" value="Cozinha" id="cozinha"><label for="cozinha">Cozinha</label></div>
                        </div>
                        <div style="margin-top:8px;">
                            <label>Outros:</label>
                            <input type="text" name="outros_comodos" placeholder="Digite outros cômodos">
                        </div>
                    </div>

                    <div class="grid-2">
                        <div>
                            <label>Construção:</label>
                            <div class="radio-group">
                                <div class="radio-item"><input type="radio" name="construcao" value="Alvenaria" id="alvenaria"><label for="alvenaria">Alvenaria</label></div>
                                <div class="radio-item"><input type="radio" name="construcao" value="Madeira" id="madeira"><label for="madeira">Madeira</label></div>
                            </div>
                            <input type="text" name="outros_construcao" placeholder="Outros" style="margin-top:8px;">
                        </div>
                        <div>
                            <label>Água:</label>
                            <div class="radio-group">
                                <div class="radio-item"><input type="radio" name="agua" value="Rede Pública" id="rede_agua"><label for="rede_agua">Rede Pública</label></div>
                                <div class="radio-item"><input type="radio" name="agua" value="Fossa" id="fossa_agua"><label for="fossa_agua">Fossa</label></div>
                            </div>
                            <input type="text" name="outros_agua" placeholder="Outros" style="margin-top:8px;">
                        </div>
                        <div>
                            <label>Esgoto:</label>
                            <div class="radio-group">
                                <div class="radio-item"><input type="radio" name="esgoto" value="Rede Pública" id="rede_esgoto"><label for="rede_esgoto">Rede Pública</label></div>
                                <div class="radio-item"><input type="radio" name="esgoto" value="Fossa" id="fossa_esgoto"><label for="fossa_esgoto">Fossa</label></div>
                            </div>
                            <input type="text" name="outros_esgoto" placeholder="Outros" style="margin-top:8px;">
                        </div>
                        <div>
                            <label>Energia Elétrica:</label>
                            <div class="radio-group">
                                <div class="radio-item"><input type="radio" name="energia" value="Relógio Próprio" id="relogio_proprio"><label for="relogio_proprio">Relógio Próprio</label></div>
                                <div class="radio-item"><input type="radio" name="energia" value="Relógio Comunitário" id="relogio_comunitario"><label for="relogio_comunitario">Relógio Comunitário</label></div>
                            </div>
                            <input type="text" name="outros_energia" placeholder="Outros" style="margin-top:8px;">
                        </div>
                        <div>
                            <label>Condições Gerais:</label>
                            <div class="radio-group">
                                <div class="radio-item"><input type="radio" name="condicoes" value="Ótima" id="otima"><label for="otima">Ótima</label></div>
                                <div class="radio-item"><input type="radio" name="condicoes" value="Boa" id="boa"><label for="boa">Boa</label></div>
                                <div class="radio-item"><input type="radio" name="condicoes" value="Regular" id="regular"><label for="regular">Regular</label></div>
                                <div class="radio-item"><input type="radio" name="condicoes" value="Precária" id="precaria"><label for="precaria">Precária</label></div>
                            </div>
                        </div>
                        <div>
                            <label>Veículos:</label>
                            <div class="radio-group">
                                <div class="radio-item"><input type="checkbox" name="veiculos[]" value="Motocicleta" id="moto"><label for="moto">Motocicleta</label></div>
                                <div class="radio-item"><input type="checkbox" name="veiculos[]" value="Automóvel" id="carro"><label for="carro">Automóvel</label></div>
                                <div class="radio-item"><input type="checkbox" name="veiculos[]" value="Caminhonete" id="caminhonete"><label for="caminhonete">Caminhonete</label></div>
                                <div class="radio-item"><input type="checkbox" name="veiculos[]" value="Caminhão" id="caminhao"><label for="caminhao">Caminhão</label></div>
                            </div>
                            <input type="text" name="outros_veiculos" placeholder="Outros" style="margin-top:8px;">
                        </div>
                    </div>
                </div>

                <!-- Etapa 3: Composição Familiar -->
                <div class="box">
                    <h3 style="margin-bottom:16px; color:#e6782e;">Composição Familiar</h3>
                    <table class="table" id="familiaTable">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Parentesco</th>
                                <th>Data Nasc.</th>
                                <th>Formação</th>
                                <th>Renda R$</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody id="familiaBody">
                            <!-- Linhas serão adicionadas dinamicamente -->
                        </tbody>
                    </table>
                    <button type="button" class="add-btn" onclick="openModal()">+ Adicionar integrante</button>
                </div>

                <!-- Etapa 4: Despesas e Rendas -->
                <div class="box">
                    <h3 style="margin-bottom:16px; color:#e6782e;">Despesas</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Despesas</th>
                                <th>Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>Água</td><td><input type="text" name="despesa_agua" value="0,00" placeholder="0,00"></td></tr>
                            <tr><td>Luz</td><td><input type="text" name="despesa_luz" value="0,00" placeholder="0,00"></td></tr>
                            <tr><td>Gás</td><td><input type="text" name="despesa_gas" value="0,00" placeholder="0,00"></td></tr>
                            <tr><td>Telefone</td><td><input type="text" name="despesa_telefone" value="0,00" placeholder="0,00"></td></tr>
                            <tr><td>Celular</td><td><input type="text" name="despesa_celular" value="0,00" placeholder="0,00"></td></tr>
                            <tr><td>Internet</td><td><input type="text" name="despesa_internet" value="0,00" placeholder="0,00"></td></tr>
                            <tr><td>Alimentação</td><td><input type="text" name="despesa_alimentacao" value="0,00" placeholder="0,00"></td></tr>
                        </tbody>
                    </table>
                    <button type="button" class="add-btn">+ Adicionar despesa</button>
                    <div style="text-align:right; margin-top:8px; font-weight:600;">Total: R$ <span id="totalDespesas">0,00</span></div>
                </div>

                <div class="box">
                    <h3 style="margin-bottom:16px; color:#e6782e;">Renda/Benefício</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Renda/Benefício</th>
                                <th>Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>Salário</td><td><input type="text" name="renda_salario" value="0,00" placeholder="0,00"></td></tr>
                            <tr><td>Bolsa Família</td><td><input type="text" name="renda_bolsa_familia" value="0,00" placeholder="0,00"></td></tr>
                        </tbody>
                    </table>
                    <button type="button" class="add-btn">+ Adicionar renda</button>
                    <div style="text-align:right; margin-top:8px; font-weight:600;">Total: R$ <span id="totalRendas">0,00</span></div>
                </div>

                <!-- Etapa 5: Outras informações -->
                <div class="box">
                    <h3 style="margin-bottom:16px; color:#e6782e;">Outras informações</h3>
                    
                    <div style="margin-bottom:16px;">
                        <label>Alguém na família trabalha registrado / CLT?</label>
                        <div class="radio-group">
                            <div class="radio-item"><input type="radio" name="trabalho_clt" value="Sim" id="clt_sim"><label for="clt_sim">Sim</label></div>
                            <div class="radio-item"><input type="radio" name="trabalho_clt" value="Não" id="clt_nao"><label for="clt_nao">Não</label></div>
                        </div>
                        <div style="margin-top:8px;">
                            <label>Qual:</label>
                            <input type="text" name="qual_trabalho" placeholder="Descreva o trabalho">
                        </div>
                    </div>

                    <div style="margin-bottom:16px;">
                        <label>Possui convênio médico?</label>
                        <div class="radio-group">
                            <div class="radio-item"><input type="radio" name="convenio" value="Sim" id="convenio_sim"><label for="convenio_sim">Sim</label></div>
                            <div class="radio-item"><input type="radio" name="convenio" value="Não" id="convenio_nao"><label for="convenio_nao">Não</label></div>
                        </div>
                    </div>

                    <div style="margin-bottom:16px;">
                        <label>Tem Cadastro Único (CadUnico)?</label>
                        <div class="radio-group">
                            <div class="radio-item"><input type="radio" name="cadunico" value="Sim" id="cadunico_sim"><label for="cadunico_sim">Sim</label></div>
                            <div class="radio-item"><input type="radio" name="cadunico" value="Não" id="cadunico_nao"><label for="cadunico_nao">Não</label></div>
                        </div>
                    </div>
                </div>

                <div class="actions">
                    <button class="btn" type="submit">Cadastrar</button>
                </div>
            </form>
        </main>
    </div>

    <!-- Modal para adicionar parente -->
    <div id="parenteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Adicionar Parente</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div>
                <div style="margin-bottom:12px;">
                    <label>Nome</label>
                    <input type="text" id="parenteNome" placeholder="Digite o nome completo">
                </div>
                <div style="margin-bottom:12px;">
                    <label>Parentesco</label>
                    <select id="parenteParentesco">
                        <option value="">Selecionar</option>
                        <option value="Pai">Pai</option>
                        <option value="Mãe">Mãe</option>
                        <option value="Filho(a)">Filho(a)</option>
                        <option value="Avó/Avô">Avó/Avô</option>
                        <option value="Tio(a)">Tio(a)</option>
                        <option value="Irmão(ã)">Irmão(ã)</option>
                        <option value="Outro">Outro</option>
                    </select>
                </div>
                <div style="margin-bottom:12px;">
                    <label>Data Nasc.</label>
                    <input type="text" id="parenteDataNasc" placeholder="dd/mm/aaaa">
                </div>
                <div style="margin-bottom:12px;">
                    <label>Formação</label>
                    <select id="parenteFormacao">
                        <option value="">Selecionar</option>
                        <option value="Ensino Fundamental">Ensino Fundamental</option>
                        <option value="Ensino Médio">Ensino Médio</option>
                        <option value="Ensino Técnico">Ensino Técnico</option>
                        <option value="Superior">Superior</option>
                        <option value="Pós-graduação">Pós-graduação</option>
                    </select>
                </div>
                <div style="margin-bottom:16px;">
                    <label>Renda R$</label>
                    <input type="text" id="parenteRenda" placeholder="0,00">
                </div>
                <div style="display:flex; gap:10px; justify-content:flex-end;">
                    <button class="btn secondary" onclick="closeModal()">Cancelar</button>
                    <button class="btn" onclick="addParente()">Adicionar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let familiaData = [];

        function openModal() {
            document.getElementById('parenteModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('parenteModal').style.display = 'none';
            // Limpar campos
            document.getElementById('parenteNome').value = '';
            document.getElementById('parenteParentesco').value = '';
            document.getElementById('parenteDataNasc').value = '';
            document.getElementById('parenteFormacao').value = '';
            document.getElementById('parenteRenda').value = '';
        }

        function addParente() {
            const nome = document.getElementById('parenteNome').value;
            const parentesco = document.getElementById('parenteParentesco').value;
            const dataNasc = document.getElementById('parenteDataNasc').value;
            const formacao = document.getElementById('parenteFormacao').value;
            const renda = document.getElementById('parenteRenda').value;

            if (!nome || !parentesco) {
                alert('Preencha pelo menos Nome e Parentesco');
                return;
            }

            familiaData.push({ nome, parentesco, dataNasc, formacao, renda });
            updateFamiliaTable();
            closeModal();
        }

        function removeParente(index) {
            familiaData.splice(index, 1);
            updateFamiliaTable();
        }

        function updateFamiliaTable() {
            const tbody = document.getElementById('familiaBody');
            tbody.innerHTML = '';

            familiaData.forEach((item, index) => {
                const row = tbody.insertRow();
                row.innerHTML = `
                    <td>${item.nome}</td>
                    <td>${item.parentesco}</td>
                    <td>${item.dataNasc}</td>
                    <td>${item.formacao}</td>
                    <td>R$ ${item.renda}</td>
                    <td>
                        <button type="button" class="btn delete" onclick="removeParente(${index})" style="padding:4px 8px; font-size:12px;">Excluir</button>
                    </td>
                `;
            });

            // Adicionar campos hidden para envio
            updateHiddenFields();
        }

        function updateHiddenFields() {
            // Remover campos hidden existentes
            const existingHidden = document.querySelectorAll('input[name^="familia_"]');
            existingHidden.forEach(field => field.remove());

            // Adicionar novos campos hidden
            familiaData.forEach((item, index) => {
                const form = document.getElementById('socioForm');
                ['nome', 'parentesco', 'dataNasc', 'formacao', 'renda'].forEach(field => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = `familia_${index}_${field}`;
                    input.value = item[field];
                    form.appendChild(input);
                });
            });
        }

        // Fechar modal clicando fora
        window.onclick = function(event) {
            const modal = document.getElementById('parenteModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
    <!-- Chatbot -->
    <script src="js/chatbot.js"></script>
    <!-- Modo Escuro -->
    <script src="js/theme-toggle.js"></script>
</body>
</html>
