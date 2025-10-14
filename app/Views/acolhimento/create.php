<link rel="stylesheet" href="css/acolhimento-form.css">

<div class="acolhimento-stepper">
    <div class="step active">1. Dados iniciais</div>
    <div class="step">2. Endereço</div>
    <div class="step">3. Responsável</div>
    <div class="step">4. Documentos</div>
</div>

<form method="post" enctype="multipart/form-data" class="acolhimento-form">
    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
    
    <!-- Etapa 1: Dados Iniciais -->
    <div class="form-section">
        <h3 class="form-section-title">1. Dados Iniciais</h3>
        <div class="form-grid">
            <div class="form-field">
                <label>Nome Completo <span class="required">*</span></label>
                <input type="text" name="nome_completo" required>
            </div>
            <div class="form-field">
                <label>RG <span class="required">*</span></label>
                <input type="text" name="rg" required>
            </div>
            <div class="form-field">
                <label>CPF <span class="required">*</span></label>
                <input type="text" name="cpf" required>
            </div>
            <div class="form-field">
                <label>Data de Nascimento <span class="required">*</span></label>
                <input type="text" name="data_nascimento" placeholder="dd/mm/aaaa" required>
            </div>
            <div class="form-field">
                <label>Data de Acolhimento <span class="required">*</span></label>
                <input type="text" name="data_acolhimento" placeholder="dd/mm/aaaa" required>
            </div>
            <div class="form-field">
                <label>Encaminhado por</label>
                <input type="text" name="encaminha_por">
            </div>
            <div class="form-field" style="grid-column: 1 / 2;">
                <label>Foto 3x4</label>
                <input type="file" name="foto" accept="image/*">
                <small>Formatos aceitos: JPG, PNG, GIF (máx. 2MB)</small>
            </div>
            <div class="form-field" style="grid-column: 1 / -1;">
                <label>Queixa Principal <span class="required">*</span></label>
                <textarea name="queixa_principal" required></textarea>
            </div>
        </div>
    </div>

    <!-- Etapa 2: Endereço -->
    <div class="form-section">
        <h3 class="form-section-title">2. Endereço</h3>
        <div class="form-grid">
            <div class="form-field">
                <label>Endereço <span class="required">*</span></label>
                <input type="text" name="endereco" required>
            </div>
            <div class="form-field">
                <label>Número <span class="required">*</span></label>
                <input type="text" name="numero" required>
            </div>
            <div class="form-field">
                <label>CEP <span class="required">*</span></label>
                <input type="text" name="cep" required>
            </div>
            <div class="form-field">
                <label>Bairro <span class="required">*</span></label>
                <input type="text" name="bairro" required>
            </div>
            <div class="form-field">
                <label>Cidade <span class="required">*</span></label>
                <input type="text" name="cidade" required>
            </div>
            <div class="form-field">
                <label>Complemento</label>
                <input type="text" name="complemento">
            </div>
            <div class="form-field">
                <label>Ponto de Referência</label>
                <input type="text" name="ponto_referencia">
            </div>
            <div class="form-field">
                <label>Escola</label>
                <input type="text" name="escola">
            </div>
            <div class="form-field">
                <label>Período</label>
                <select name="periodo">
                    <option value="">Selecionar</option>
                    <option>Manhã</option>
                    <option>Tarde</option>
                    <option>Noite</option>
                </select>
            </div>
            <div class="form-field">
                <label>CRAS de Referência</label>
                <input type="text" name="cras">
            </div>
            <div class="form-field">
                <label>UBS de Referência</label>
                <input type="text" name="ubs">
            </div>
        </div>
    </div>

    <!-- Etapa 3: Responsável -->
    <div class="form-section">
        <h3 class="form-section-title">3. Responsável</h3>
        <div class="form-grid">
            <div class="form-field">
                <label>Nome do Responsável <span class="required">*</span></label>
                <input type="text" name="nome_responsavel" required>
            </div>
            <div class="form-field">
                <label>RG do Responsável <span class="required">*</span></label>
                <input type="text" name="rg_responsavel" required>
            </div>
            <div class="form-field">
                <label>CPF do Responsável <span class="required">*</span></label>
                <input type="text" name="cpf_responsavel" required>
            </div>
            <div class="form-field">
                <label>Grau de Parentesco <span class="required">*</span></label>
                <input type="text" name="grau_parentesco" required>
            </div>
            <div class="form-field">
                <label>Contato 1 (obrigatório) <span class="required">*</span></label>
                <input type="text" name="contato_1" placeholder="11999998888 (apenas números)" required>
            </div>
        </div>
    </div>

    <!-- Etapa 4: Documentos -->
    <div class="form-section">
        <h3 class="form-section-title">4. Documentos</h3>
        <div class="form-grid">
            <div class="form-field">
                <label>Cadastro Único</label>
                <select name="cad_unico">
                    <option value="">Selecionar</option>
                    <option>Possuo</option>
                    <option>Não Possuo</option>
                </select>
            </div>
            <div class="form-field">
                <label>Responsável pelo Acolhimento</label>
                <input type="text" name="acolhimento_responsavel">
            </div>
            <div class="form-field">
                <label>Função</label>
                <input type="text" name="acolhimento_funcao">
            </div>
            <div class="form-field" style="grid-column: 1 / -1;">
                <label>Carimbo/Assinatura</label>
                <input type="file" name="carimbo" accept="image/*">
                <small>Envie uma imagem do carimbo ou assinatura (JPG, PNG, GIF - máx. 2MB)</small>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <a href="prontuarios.php" class="btn-secondary">Voltar</a>
        <button class="btn-primary" type="submit">Cadastrar</button>
    </div>
</form>

<script src="js/acolhimento-form.js"></script>
