<div class="actions" style="display:flex; gap:10px; justify-content:flex-end; margin-bottom:20px;">
    <a href="prontuarios.php" class="btn secondary" style="background:#6b7b84; color:#fff; border:none; padding:10px 14px; border-radius:8px; cursor:pointer; text-decoration:none;">← Voltar</a>
    <a href="socioeconomico_form.php?edit=<?php echo urlencode($ficha['id']); ?>" class="btn" style="background:#f0a36b; color:#fff; border:none; padding:10px 14px; border-radius:8px; cursor:pointer; text-decoration:none;">✏️ Editar</a>
</div>

<!-- Dados Pessoais -->
<div class="section" style="background:#fff; border-radius:12px; padding:20px; margin-bottom:20px; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
    <h3 style="margin:0 0 16px 0; color:#495057; border-bottom:2px solid #f0a36b; padding-bottom:8px;">👤 Dados Pessoais</h3>
    
    <div class="fields-grid" style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:16px;">
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Nome Completo</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;"><?php echo htmlspecialchars($ficha['nome_completo'] ?? ''); ?></div>
        </div>
        
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">CPF</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;"><?php echo htmlspecialchars($ficha['cpf'] ?? ''); ?></div>
        </div>
        
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">RG</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;"><?php echo htmlspecialchars($ficha['rg'] ?? 'Não informado'); ?></div>
        </div>
        
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Data de Nascimento</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;"><?php echo htmlspecialchars($ficha['data_nascimento'] ?? ''); ?></div>
        </div>
        
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Idade</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;"><?php echo $ficha['idade'] ?? 'N/A'; ?> anos</div>
        </div>
        
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Estado Civil</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;"><?php echo htmlspecialchars($ficha['estado_civil'] ?? 'Não informado'); ?></div>
        </div>
        
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Escolaridade</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;"><?php echo htmlspecialchars($ficha['escolaridade'] ?? 'Não informado'); ?></div>
        </div>
        
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Profissão</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;"><?php echo htmlspecialchars($ficha['profissao'] ?? 'Não informado'); ?></div>
        </div>
        
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Telefone</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;"><?php echo htmlspecialchars($ficha['telefone'] ?? ''); ?></div>
        </div>
    </div>
</div>

<!-- Composição Familiar -->
<div class="section" style="background:#fff; border-radius:12px; padding:20px; margin-bottom:20px; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
    <h3 style="margin:0 0 16px 0; color:#495057; border-bottom:2px solid #f0a36b; padding-bottom:8px;">👨‍👩‍👧‍👦 Composição Familiar</h3>
    
    <div class="fields-grid" style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:16px;">
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Pessoas na Casa</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;"><?php echo $ficha['pessoas_casa'] ?? 'Não informado'; ?></div>
        </div>
        
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Crianças (0-12 anos)</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;"><?php echo $ficha['criancas'] ?? '0'; ?></div>
        </div>
        
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Adolescentes (13-17 anos)</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;"><?php echo $ficha['adolescentes'] ?? '0'; ?></div>
        </div>
        
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Adultos (18-59 anos)</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;"><?php echo $ficha['adultos'] ?? '0'; ?></div>
        </div>
        
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Idosos (60+ anos)</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;"><?php echo $ficha['idosos'] ?? '0'; ?></div>
        </div>
        
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Pessoas com Deficiência</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;"><?php echo $ficha['pessoas_deficiencia'] ?? '0'; ?></div>
        </div>
    </div>
</div>

<!-- Renda e Benefícios -->
<div class="section" style="background:#fff; border-radius:12px; padding:20px; margin-bottom:20px; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
    <h3 style="margin:0 0 16px 0; color:#495057; border-bottom:2px solid #f0a36b; padding-bottom:8px;">💰 Renda e Benefícios</h3>
    
    <div class="fields-grid" style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:16px;">
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Renda Familiar</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;">
                <?php 
                $renda = $ficha['renda_familiar'] ?? 0;
                echo $renda > 0 ? 'R$ ' . number_format($renda, 2, ',', '.') : 'Não informado';
                ?>
            </div>
        </div>
        
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Renda Per Capita</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;">
                <?php 
                $rendaPerCapita = $ficha['renda_per_capita'] ?? 0;
                echo $rendaPerCapita > 0 ? 'R$ ' . number_format($rendaPerCapita, 2, ',', '.') : 'Não calculado';
                ?>
            </div>
        </div>
        
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Cadastro Único</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;"><?php echo htmlspecialchars($ficha['cadastro_unico'] ?? 'Não informado'); ?></div>
        </div>
    </div>
    
    <div style="margin-top:16px;">
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:8px;">Benefícios Sociais</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;">
                <?php 
                $beneficios = [];
                if (!empty($ficha['bolsa_familia'])) $beneficios[] = 'Bolsa Família';
                if (!empty($ficha['auxilio_brasil'])) $beneficios[] = 'Auxílio Brasil';
                if (!empty($ficha['bpc'])) $beneficios[] = 'BPC';
                if (!empty($ficha['auxilio_emergencial'])) $beneficios[] = 'Auxílio Emergencial';
                if (!empty($ficha['seguro_desemprego'])) $beneficios[] = 'Seguro Desemprego';
                if (!empty($ficha['aposentadoria'])) $beneficios[] = 'Aposentadoria';
                
                if (!empty($beneficios)) {
                    foreach ($beneficios as $beneficio) {
                        echo '<span class="badge" style="display:inline-block; padding:4px 8px; margin:2px 4px 2px 0; border-radius:12px; font-size:12px; font-weight:500; background:#e8f6ea; color:#6fb64f;">' . $beneficio . '</span>';
                    }
                } else {
                    echo '<span style="color:#6c757d; font-style:italic;">Nenhum benefício informado</span>';
                }
                ?>
            </div>
        </div>
    </div>
</div>

<!-- Habitação -->
<div class="section" style="background:#fff; border-radius:12px; padding:20px; margin-bottom:20px; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
    <h3 style="margin:0 0 16px 0; color:#495057; border-bottom:2px solid #f0a36b; padding-bottom:8px;">🏠 Habitação</h3>
    
    <div class="fields-grid" style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:16px;">
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Tipo de Moradia</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;"><?php echo htmlspecialchars($ficha['tipo_moradia'] ?? 'Não informado'); ?></div>
        </div>
        
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Situação da Moradia</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;"><?php echo htmlspecialchars($ficha['situacao_moradia'] ?? 'Não informado'); ?></div>
        </div>
        
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Número de Cômodos</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;"><?php echo $ficha['numero_comodos'] ?? 'Não informado'; ?></div>
        </div>
        
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Água</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;"><?php echo htmlspecialchars($ficha['agua'] ?? 'Não informado'); ?></div>
        </div>
        
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Esgoto</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;"><?php echo htmlspecialchars($ficha['esgoto'] ?? 'Não informado'); ?></div>
        </div>
        
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Energia Elétrica</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500;"><?php echo htmlspecialchars($ficha['energia'] ?? 'Não informado'); ?></div>
        </div>
    </div>
    
    <?php if (!empty($ficha['observacoes'])): ?>
    <div style="margin-top:16px;">
        <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
            <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Observações</div>
            <div class="value" style="color:var(--text-primary, #212529); font-weight:500; line-height:1.5;"><?php echo nl2br(htmlspecialchars($ficha['observacoes'])); ?></div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Status -->
<div class="section" style="background:#fff; border-radius:12px; padding:20px; margin-bottom:20px; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
    <h3 style="margin:0 0 16px 0; color:#495057; border-bottom:2px solid #f0a36b; padding-bottom:8px;">📋 Status</h3>
    
    <div class="field" style="background:var(--card-bg, #f8f9fa); padding:12px; border-radius:8px; transition:background-color 0.3s ease;">
        <div class="label" style="font-size:12px; color:var(--text-muted, #6c757d); font-weight:600; margin-bottom:4px;">Status da Ficha</div>
        <div class="value" style="color:var(--text-primary, #212529); font-weight:500;">
            <span class="status" style="padding:4px 8px; border-radius:12px; font-size:12px; font-weight:500;
                                   <?php echo ($ficha['status'] ?? 'Ativo') === 'Ativo' ? 'background:#e8f6ea; color:#6fb64f;' : 'background:#f8d7da; color:#721c24;'; ?>">
                <?php echo $ficha['status'] ?? 'Ativo'; ?>
            </span>
        </div>
    </div>
</div>

<style>
    /* Responsividade */
    @media (max-width: 768px) {
        .fields-grid {
            grid-template-columns: 1fr !important;
            gap: 12px !important;
        }
        
        .actions {
            flex-direction: column;
        }
        
        .btn {
            width: 100%;
            text-align: center;
        }
        
        .section {
            padding: 16px !important;
        }
    }
    
    @media (max-width: 480px) {
        .section {
            padding: 12px !important;
        }
        
        .field {
            padding: 10px !important;
        }
        
        .label {
            font-size: 11px !important;
        }
        
        .value {
            font-size: 14px !important;
        }
        
        .badge {
            font-size: 11px !important;
            padding: 3px 6px !important;
        }
    }
</style>
