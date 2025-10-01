<div class="stepper" style="display:flex; gap:8px; margin:16px 0 24px;">
    <div class="chip active" style="padding:8px 12px; border-radius:20px; background:#ffdab9; font-weight:600;">1. Dados iniciais</div>
    <div class="chip" style="padding:8px 12px; border-radius:20px; background:#cfd8dc; font-weight:600;">2. Endereço</div>
    <div class="chip" style="padding:8px 12px; border-radius:20px; background:#cfd8dc; font-weight:600;">3. Responsável</div>
    <div class="chip" style="padding:8px 12px; border-radius:20px; background:#cfd8dc; font-weight:600;">4. Documentos</div>
</div>

<form method="post" enctype="multipart/form-data" style="max-width: 1200px;">
    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
    
    <!-- Etapa 1 -->
    <div class="box" style="background:#fff; border-radius:12px; padding:16px; margin-bottom:16px;">
        <div class="grid" style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px;">
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600;">Nome Completo *</label>
                <input type="text" name="nome_completo" style="padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;" required>
            </div>
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600;">RG *</label>
                <input type="text" name="rg" style="padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;" required>
            </div>
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600;">CPF *</label>
                <input type="text" name="cpf" style="padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;" required>
            </div>
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600;">Data de Nascimento *</label>
                <input type="text" name="data_nascimento" placeholder="dd/mm/aaaa" style="padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;" required>
            </div>
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600;">Data de Acolhimento *</label>
                <input type="text" name="data_acolhimento" placeholder="dd/mm/aaaa" style="padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;" required>
            </div>
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600;">Encaminhado por</label>
                <input type="text" name="encaminha_por" style="padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;">
            </div>
            <div style="grid-column:1 / 2;">
                <label style="font-size:14px; color:#354047; font-weight:600;">Foto 3x4</label>
                <input type="file" name="foto" accept="image/*" style="padding: 8px; border: 2px dashed #f0a36b; background: #fff; width:100%; box-sizing:border-box; border-radius:8px;">
                <small style="color: #666; font-size: 12px; display: block; margin-top: 4px;">Formatos aceitos: JPG, PNG, GIF (máx. 2MB)</small>
            </div>
            <div style="grid-column:1 / -1;">
                <label style="font-size:14px; color:#354047; font-weight:600;">Queixa Principal *</label>
                <textarea name="queixa_principal" style="min-height:90px; resize:vertical; padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;" required></textarea>
            </div>
        </div>
    </div>

    <!-- Etapa 2 -->
    <div class="box" style="background:#fff; border-radius:12px; padding:16px; margin-bottom:16px;">
        <div class="grid" style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px;">
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600;">Endereço *</label>
                <input type="text" name="endereco" style="padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;" required>
            </div>
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600;">Número *</label>
                <input type="text" name="numero" style="padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;" required>
            </div>
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600;">CEP *</label>
                <input type="text" name="cep" style="padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;" required>
            </div>
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600;">Bairro *</label>
                <input type="text" name="bairro" style="padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;" required>
            </div>
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600;">Cidade *</label>
                <input type="text" name="cidade" style="padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;" required>
            </div>
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600;">Complemento</label>
                <input type="text" name="complemento" style="padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;">
            </div>
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600;">Ponto de Referência</label>
                <input type="text" name="ponto_referencia" style="padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;">
            </div>
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600;">Escola</label>
                <input type="text" name="escola" style="padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;">
            </div>
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600;">Período</label>
                <select name="periodo" style="padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;">
                    <option value="">Selecionar</option>
                    <option>Manhã</option>
                    <option>Tarde</option>
                    <option>Noite</option>
                </select>
            </div>
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600;">CRAS de Referência</label>
                <input type="text" name="cras" style="padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;">
            </div>
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600;">UBS de Referência</label>
                <input type="text" name="ubs" style="padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;">
            </div>
        </div>
    </div>

    <!-- Etapa 3 -->
    <div class="box" style="background:#fff; border-radius:12px; padding:16px; margin-bottom:16px;">
        <div class="grid" style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px;">
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600;">Nome do Responsável *</label>
                <input type="text" name="nome_responsavel" style="padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;" required>
            </div>
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600;">RG do Responsável *</label>
                <input type="text" name="rg_responsavel" style="padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;" required>
            </div>
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600;">CPF do Responsável *</label>
                <input type="text" name="cpf_responsavel" style="padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;" required>
            </div>
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600;">Grau de Parentesco *</label>
                <input type="text" name="grau_parentesco" style="padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;" required>
            </div>
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600;">Contato 1 (obrigatório) *</label>
                <input type="text" name="contato_1" placeholder="11999998888 (apenas números)" style="padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;" required>
            </div>
        </div>
    </div>

    <!-- Etapa 4 -->
    <div class="box" style="background:#fff; border-radius:12px; padding:16px; margin-bottom:16px;">
        <div class="grid" style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px;">
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600;">Cadastro Único</label>
                <select name="cad_unico" style="padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;">
                    <option value="">Selecionar</option>
                    <option>Possuo</option>
                    <option>Não Possuo</option>
                </select>
            </div>
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600;">Responsável pelo Acolhimento</label>
                <input type="text" name="acolhimento_responsavel" style="padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;">
            </div>
            <div>
                <label style="font-size:14px; color:#354047; font-weight:600;">Função/Carimbo</label>
                <input type="text" name="acolhimento_funcao" style="padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;">
            </div>
        </div>
    </div>

    <div class="actions" style="display:flex; gap:10px; justify-content:flex-end;">
        <a href="prontuarios.php" class="btn secondary" style="background:#6b7b84; color:#fff; border:none; padding:10px 14px; border-radius:8px; cursor:pointer; text-decoration:none;">Voltar</a>
        <button class="btn" type="submit" style="background:#ff7a00; color:#fff; border:none; padding:10px 14px; border-radius:8px; cursor:pointer;">Cadastrar</button>
    </div>
</form>

<script src="js/acolhimento-form.js"></script>

<style>
    /* Responsividade */
    @media (max-width: 768px) {
        .grid {
            grid-template-columns: 1fr !important;
        }
        
        .stepper {
            flex-wrap: wrap;
            gap: 4px !important;
        }
        
        .chip {
            font-size: 12px !important;
            padding: 6px 10px !important;
        }
        
        .actions {
            flex-direction: column;
        }
        
        .btn {
            width: 100%;
            text-align: center;
        }
    }
    
    @media (max-width: 480px) {
        .box {
            padding: 12px !important;
        }
        
        .grid {
            gap: 8px !important;
        }
        
        input, select, textarea {
            font-size: 16px; /* Evita zoom no iOS */
        }
    }
</style>
