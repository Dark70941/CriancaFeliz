<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: index.php'); exit(); }

$dataFile = __DIR__ . '/data/socioeconomico.json';
$id = $_GET['id'] ?? '';
$record = null;
if (file_exists($dataFile)) {
    $items = json_decode(file_get_contents($dataFile), true) ?: [];
    foreach ($items as $it) { if (($it['id'] ?? '') === $id) { $record = $it; break; } }
}
if (!$record) { header('Location: socioeconomico_list.php'); exit(); }

// Processar dados da família
$familia = [];
$i = 0;
while (isset($record["familia_{$i}_nome"])) {
    $familia[] = [
        'nome' => $record["familia_{$i}_nome"] ?? '',
        'parentesco' => $record["familia_{$i}_parentesco"] ?? '',
        'dataNasc' => $record["familia_{$i}_dataNasc"] ?? '',
        'formacao' => $record["familia_{$i}_formacao"] ?? '',
        'renda' => $record["familia_{$i}_renda"] ?? ''
    ];
    $i++;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizar Ficha Socioeconômico</title>
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
        .grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
        .field { background:#f7f9fa; border-radius:8px; padding:10px 12px; }
        .label { font-size:12px; color:#6b7b84; }
        .value { font-weight:600; }
        .btn { background:#6b7b84; color:#fff; padding:10px 14px; border-radius:8px; text-decoration:none; }
        .table { width:100%; border-collapse:collapse; margin:12px 0; }
        .table th { background:#e6782e; color:#fff; padding:12px; text-align:left; }
        .table td { padding:12px; border-bottom:1px solid #eee; }
        .section-title { color:#e6782e; font-weight:700; margin-bottom:16px; font-size:18px; }
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
                <div style="font-weight:700; font-size:24px;">Visualizar - Ficha Socioeconômico</div>
                <a class="btn" href="socioeconomico_list.php">Voltar</a>
            </div>

            <!-- Dados Iniciais -->
            <div class="box">
                <div class="section-title">Dados Iniciais</div>
                <div class="grid">
                    <div class="field"><div class="label">Nome do Entrevistado</div><div class="value"><?php echo htmlspecialchars($record['entrevistado'] ?? ''); ?></div></div>
                    <div class="field"><div class="label">RG</div><div class="value"><?php echo htmlspecialchars($record['rg'] ?? ''); ?></div></div>
                    <div class="field"><div class="label">CPF</div><div class="value"><?php echo htmlspecialchars($record['cpf'] ?? ''); ?></div></div>
                    <div class="field"><div class="label">Nome do Menor</div><div class="value"><?php echo htmlspecialchars($record['menor'] ?? ''); ?></div></div>
                    <div class="field"><div class="label">Data do Acolh.</div><div class="value"><?php echo htmlspecialchars($record['data_acolh'] ?? ''); ?></div></div>
                    <div class="field"><div class="label">Assistente Social</div><div class="value"><?php echo htmlspecialchars($record['assistente'] ?? ''); ?></div></div>
                </div>
            </div>

            <!-- Características do Domicílio -->
            <div class="box">
                <div class="section-title">Características do Domicílio</div>
                <div class="grid-2">
                    <div class="field"><div class="label">Tipo de Residência</div><div class="value"><?php echo htmlspecialchars($record['tipo_residencia'] ?? ''); ?></div></div>
                    <div class="field"><div class="label">Valor Aluguel/Prestação</div><div class="value">R$ <?php echo htmlspecialchars($record['valor_aluguel'] ?? ''); ?></div></div>
                    <div class="field"><div class="label">Cômodos</div><div class="value"><?php 
                        $comodos = [];
                        if (isset($record['comodos']) && is_array($record['comodos'])) {
                            $comodos = $record['comodos'];
                        }
                        if (isset($record['outros_comodos']) && $record['outros_comodos']) {
                            $comodos[] = $record['outros_comodos'];
                        }
                        echo htmlspecialchars(implode(', ', $comodos));
                    ?></div></div>
                    <div class="field"><div class="label">Construção</div><div class="value"><?php echo htmlspecialchars($record['construcao'] ?? ''); ?></div></div>
                    <div class="field"><div class="label">Água</div><div class="value"><?php echo htmlspecialchars($record['agua'] ?? ''); ?></div></div>
                    <div class="field"><div class="label">Esgoto</div><div class="value"><?php echo htmlspecialchars($record['esgoto'] ?? ''); ?></div></div>
                    <div class="field"><div class="label">Energia Elétrica</div><div class="value"><?php echo htmlspecialchars($record['energia'] ?? ''); ?></div></div>
                    <div class="field"><div class="label">Condições Gerais</div><div class="value"><?php echo htmlspecialchars($record['condicoes'] ?? ''); ?></div></div>
                    <div class="field"><div class="label">Veículos</div><div class="value"><?php 
                        $veiculos = [];
                        if (isset($record['veiculos']) && is_array($record['veiculos'])) {
                            $veiculos = $record['veiculos'];
                        }
                        if (isset($record['outros_veiculos']) && $record['outros_veiculos']) {
                            $veiculos[] = $record['outros_veiculos'];
                        }
                        echo htmlspecialchars(implode(', ', $veiculos));
                    ?></div></div>
                </div>
            </div>

            <!-- Composição Familiar -->
            <?php if (!empty($familia)): ?>
            <div class="box">
                <div class="section-title">Composição Familiar</div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Parentesco</th>
                            <th>Data Nasc.</th>
                            <th>Formação</th>
                            <th>Renda R$</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($familia as $membro): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($membro['nome']); ?></td>
                            <td><?php echo htmlspecialchars($membro['parentesco']); ?></td>
                            <td><?php echo htmlspecialchars($membro['dataNasc']); ?></td>
                            <td><?php echo htmlspecialchars($membro['formacao']); ?></td>
                            <td>R$ <?php echo htmlspecialchars($membro['renda']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <!-- Despesas -->
            <div class="box">
                <div class="section-title">Despesas</div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Despesas</th>
                            <th>Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>Água</td><td>R$ <?php echo htmlspecialchars($record['despesa_agua'] ?? '0,00'); ?></td></tr>
                        <tr><td>Luz</td><td>R$ <?php echo htmlspecialchars($record['despesa_luz'] ?? '0,00'); ?></td></tr>
                        <tr><td>Gás</td><td>R$ <?php echo htmlspecialchars($record['despesa_gas'] ?? '0,00'); ?></td></tr>
                        <tr><td>Telefone</td><td>R$ <?php echo htmlspecialchars($record['despesa_telefone'] ?? '0,00'); ?></td></tr>
                        <tr><td>Celular</td><td>R$ <?php echo htmlspecialchars($record['despesa_celular'] ?? '0,00'); ?></td></tr>
                        <tr><td>Internet</td><td>R$ <?php echo htmlspecialchars($record['despesa_internet'] ?? '0,00'); ?></td></tr>
                        <tr><td>Alimentação</td><td>R$ <?php echo htmlspecialchars($record['despesa_alimentacao'] ?? '0,00'); ?></td></tr>
                    </tbody>
                </table>
            </div>

            <!-- Rendas -->
            <div class="box">
                <div class="section-title">Renda/Benefício</div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Renda/Benefício</th>
                            <th>Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>Salário</td><td>R$ <?php echo htmlspecialchars($record['renda_salario'] ?? '0,00'); ?></td></tr>
                        <tr><td>Bolsa Família</td><td>R$ <?php echo htmlspecialchars($record['renda_bolsa_familia'] ?? '0,00'); ?></td></tr>
                    </tbody>
                </table>
            </div>

            <!-- Outras Informações -->
            <div class="box">
                <div class="section-title">Outras Informações</div>
                <div class="grid-2">
                    <div class="field"><div class="label">Trabalha registrado/CLT?</div><div class="value"><?php echo htmlspecialchars($record['trabalho_clt'] ?? ''); ?></div></div>
                    <div class="field"><div class="label">Qual trabalho</div><div class="value"><?php echo htmlspecialchars($record['qual_trabalho'] ?? ''); ?></div></div>
                    <div class="field"><div class="label">Possui convênio médico?</div><div class="value"><?php echo htmlspecialchars($record['convenio'] ?? ''); ?></div></div>
                    <div class="field"><div class="label">Tem Cadastro Único?</div><div class="value"><?php echo htmlspecialchars($record['cadunico'] ?? ''); ?></div></div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
