// Armazenar dados da família
let familyMembers = [];
let despesasCount = 0;

// Navegação entre etapas
function nextStep() {
    const urlParams = new URLSearchParams(window.location.search);
    const currentStep = parseInt(urlParams.get('step') || '1');
    const editId = urlParams.get('id');
    
    console.log('⟳ Tentando avançar da etapa', currentStep);
    
    // SEMPRE salvar dados antes de validar (para não perder informações)
    saveStepData(currentStep);
    
    // Validar campos obrigatórios da etapa atual
    if (!validateCurrentStep(currentStep)) {
        console.log('✗ Validação falhou na etapa', currentStep);
        return;
    }
    
    console.log('✓ Validação OK, avançando para etapa', currentStep + 1);
    
    // Ir para próxima etapa, preservando o ID se existir
    let url = `socioeconomico_form.php?step=${currentStep + 1}`;
    if (editId) {
        url += `&id=${editId}`;
    }
    window.location.href = url;
}

function prevStep() {
    const urlParams = new URLSearchParams(window.location.search);
    const currentStep = parseInt(urlParams.get('step') || '1');
    const editId = urlParams.get('id');
    
    // Voltar para etapa anterior, preservando o ID se existir
    let url = `socioeconomico_form.php?step=${currentStep - 1}`;
    if (editId) {
        url += `&id=${editId}`;
    }
    window.location.href = url;
}

function validateCurrentStep(step) {
    const form = document.getElementById('socioeconomicoForm');
    
    if (!form) {
        console.error('Formulário não encontrado');
        return true; // Permitir navegação se formulário não existir
    }
    
    // Validação específica por etapa
    if (step === 1) {
        // Campos obrigatórios da Etapa 1
        const nomeEntrevistado = form.querySelector('[name="nome_entrevistado"]');
        const nomeMenor = form.querySelector('[name="nome_menor"]');
        const rg = form.querySelector('[name="rg"]');
        const cpf = form.querySelector('[name="cpf"]');
        
        console.log('Validando etapa 1:', {
            nomeEntrevistado: nomeEntrevistado?.value,
            nomeMenor: nomeMenor?.value,
            rg: rg?.value,
            cpf: cpf?.value
        });
        
        // Nome do Entrevistado - OBRIGATÓRIO
        if (!nomeEntrevistado || !nomeEntrevistado.value.trim()) {
            alert('❌ Campo obrigatório:\n\nPor favor, preencha o Nome do Entrevistado');
            if (nomeEntrevistado) {
                nomeEntrevistado.focus();
                nomeEntrevistado.style.borderColor = '#e74c3c';
            }
            return false;
        }
        
        // Nome do Menor - OBRIGATÓRIO
        if (!nomeMenor || !nomeMenor.value.trim()) {
            alert('❌ Campo obrigatório:\n\nPor favor, preencha o Nome do Menor');
            if (nomeMenor) {
                nomeMenor.focus();
                nomeMenor.style.borderColor = '#e74c3c';
            }
            return false;
        }
        
        // RG - OBRIGATÓRIO
        if (!rg || !rg.value.trim()) {
            alert('❌ Campo obrigatório:\n\nPor favor, preencha o RG do Entrevistado');
            if (rg) {
                rg.focus();
                rg.style.borderColor = '#e74c3c';
            }
            return false;
        }
        
        // CPF - OBRIGATÓRIO
        if (!cpf || !cpf.value.trim()) {
            alert('❌ Campo obrigatório:\n\nPor favor, preencha o CPF do Entrevistado');
            if (cpf) {
                cpf.focus();
                cpf.style.borderColor = '#e74c3c';
            }
            return false;
        }
        
        // Validar CPF (deve ter 11 dígitos)
        const cpfDigits = cpf.value.replace(/\D/g, '');
        if (cpfDigits.length !== 11) {
            alert('❌ CPF inválido:\n\nO CPF deve ter 11 dígitos');
            cpf.focus();
            cpf.style.borderColor = '#e74c3c';
            return false;
        }
        
        // Validar RG (deve ter 9 dígitos)
        const rgDigits = rg.value.replace(/\D/g, '');
        if (rgDigits.length !== 9) {
            alert('❌ RG inválido:\n\nO RG deve ter 9 dígitos');
            rg.focus();
            rg.style.borderColor = '#e74c3c';
            return false;
        }
        
        // Resetar bordas se tudo estiver OK
        nomeEntrevistado.style.borderColor = '#4a7c8f';
        nomeMenor.style.borderColor = '#4a7c8f';
        rg.style.borderColor = '#4a7c8f';
        cpf.style.borderColor = '#4a7c8f';
    }
    
    return true;
}

function saveStepData(step) {
    const form = document.getElementById('socioeconomicoForm');
    if (!form) {
        console.error('❌ Formulário não encontrado para salvar dados');
        return;
    }
    
    const formData = new FormData(form);
    const data = {};
    
    for (let [key, value] of formData.entries()) {
        if (value) { // Só salvar se tiver valor
            data[key] = value;
        }
    }
    
    const dataStr = JSON.stringify(data);
    sessionStorage.setItem(`socioeconomico_step${step}`, dataStr);
    
    console.log('💿 Salvando dados da etapa', step, ':', data);
    console.log('📦 Tamanho dos dados:', dataStr.length, 'caracteres');
    
    // Verificar se foi salvo corretamente
    const saved = sessionStorage.getItem(`socioeconomico_step${step}`);
    if (saved) {
        console.log('✓ Dados salvos com sucesso no sessionStorage');
    } else {
        console.error('✗ ERRO: Dados não foram salvos!');
    }
}

/**
 * Consolida todos os dados das etapas anteriores em campos hidden
 * para serem enviados junto com o formulário
 */
function consolidateAllSteps() {
    console.log('📦 Consolidando dados de todas as etapas...');
    
    const form = document.getElementById('socioeconomicoForm');
    if (!form) {
        console.error('❌ Formulário não encontrado');
        return;
    }
    
    // Coletar dados de todas as etapas
    const allData = {};
    
    for (let i = 1; i <= 5; i++) {
        const stepData = sessionStorage.getItem(`socioeconomico_step${i}`);
        if (stepData) {
            try {
                const data = JSON.parse(stepData);
                Object.assign(allData, data); // Mesclar dados
                console.log(`  ✓ Etapa ${i}: ${Object.keys(data).length} campos`);
            } catch (error) {
                console.error(`  ✗ Erro ao ler etapa ${i}:`, error);
            }
        }
    }
    
    console.log('📊 Total de campos consolidados:', Object.keys(allData).length);
    console.log('📋 Dados consolidados:', allData);
    
    // Adicionar campos hidden ao formulário para cada dado
    Object.keys(allData).forEach(key => {
        // Verificar se o campo já existe no formulário
        let field = form.querySelector(`[name="${key}"]`);
        
        if (!field) {
            // Se não existe, criar campo hidden
            field = document.createElement('input');
            field.type = 'hidden';
            field.name = key;
            form.appendChild(field);
            console.log(`  + Criado campo hidden: ${key}`);
        }
        
        // Atualizar valor (se o campo estiver vazio, usar o valor salvo)
        if (!field.value || field.value === '') {
            field.value = allData[key];
            console.log(`  ✓ ${key} = ${allData[key]}`);
        }
    });
    
    console.log('✅ Consolidação completa!');
}

// Gestão de Composição Familiar
function openFamilyModal() {
    document.getElementById('familyModal').classList.add('active');
}

function closeFamilyModal() {
    document.getElementById('familyModal').classList.remove('active');
    clearFamilyForm();
}

function clearFamilyForm() {
    document.getElementById('family_nome').value = '';
    document.getElementById('family_parentesco').value = '';
    document.getElementById('family_data_nasc').value = '';
    document.getElementById('family_formacao').value = '';
    document.getElementById('family_renda').value = '';
}

function addFamilyMember() {
    const nome = document.getElementById('family_nome').value.trim();
    const parentesco = document.getElementById('family_parentesco').value;
    const dataNasc = document.getElementById('family_data_nasc').value;
    const formacao = document.getElementById('family_formacao').value;
    const renda = document.getElementById('family_renda').value;
    
    if (!nome || !parentesco) {
        alert('Por favor, preencha o nome e o parentesco');
        return;
    }
    
    const member = {
        id: Date.now(),
        nome,
        parentesco,
        dataNasc,
        formacao,
        renda: parseFloat(renda) || 0
    };
    
    familyMembers.push(member);
    updateFamilyList();
    closeFamilyModal();
    
    // Salvar no campo hidden
    document.getElementById('familia_json').value = JSON.stringify(familyMembers);
}

function removeFamilyMember(id) {
    if (!confirm('Deseja remover este integrante?')) {
        return;
    }
    
    familyMembers = familyMembers.filter(m => m.id !== id);
    updateFamilyList();
    
    // Atualizar campo hidden
    document.getElementById('familia_json').value = JSON.stringify(familyMembers);
}

function updateFamilyList() {
    const container = document.getElementById('familyList');
    
    if (familyMembers.length === 0) {
        container.innerHTML = '<div style="color:#6c757d; font-style:italic; text-align:center; padding:20px;">Nenhum integrante adicionado</div>';
        return;
    }
    
    container.innerHTML = familyMembers.map(member => `
        <div class="family-member">
            <div class="family-member-info">
                <div class="family-member-name">${member.nome}</div>
                <div class="family-member-details">
                    ${member.parentesco} • 
                    ${member.dataNasc || 'Data não informada'} • 
                    ${member.formacao || 'Formação não informada'} • 
                    R$ ${member.renda.toFixed(2)}
                </div>
            </div>
            <button type="button" onclick="removeFamilyMember(${member.id})" class="btn-remove">
                <i class="fas fa-trash"></i> Remover
            </button>
        </div>
    `).join('');
}

// Gestão de Despesas
function addDespesa() {
    despesasCount++;
    const container = document.getElementById('despesasAdicionais');
    
    const div = document.createElement('div');
    div.className = 'form-field';
    div.style.marginBottom = '12px';
    div.innerHTML = `
        <div style="display: flex; gap: 8px; align-items: center;">
            <input type="text" name="despesa_nome_${despesasCount}" placeholder="Nome da despesa" style="flex: 1; padding: 10px; border: 2px solid #4a7c8f; border-radius: 6px;">
            <input type="number" name="despesa_valor_${despesasCount}" class="despesa-input" step="0.01" placeholder="Valor" style="width: 120px; padding: 10px; border: 2px solid #4a7c8f; border-radius: 6px;">
            <button type="button" onclick="this.parentElement.parentElement.remove(); calcularTotais()" style="background: #e74c3c; color: white; border: none; padding: 10px; border-radius: 6px; cursor: pointer;">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    container.appendChild(div);
    
    // Adicionar listener ao novo input
    const newInput = div.querySelector('.despesa-input');
    newInput.addEventListener('input', calcularTotais);
}

function calcularTotais() {
    let totalDespesas = 0;
    let totalRenda = 0;
    
    // Somar todas as despesas
    const despesaInputs = document.querySelectorAll('.despesa-input');
    despesaInputs.forEach(input => {
        const valor = parseFloat(input.value) || 0;
        totalDespesas += valor;
    });
    
    // Somar todas as rendas
    const rendaInputs = document.querySelectorAll('.renda-input');
    rendaInputs.forEach(input => {
        const valor = parseFloat(input.value) || 0;
        totalRenda += valor;
    });
    
    // Atualizar displays
    const totalDespesasEl = document.getElementById('totalDespesas');
    const totalRendaEl = document.getElementById('totalRenda');
    
    if (totalDespesasEl) {
        totalDespesasEl.textContent = `R$ ${totalDespesas.toFixed(2)}`;
    }
    
    if (totalRendaEl) {
        totalRendaEl.textContent = `R$ ${totalRenda.toFixed(2)}`;
    }
}

// Toggle campos condicionais
function toggleCltField(show) {
    const field = document.getElementById('clt_field');
    if (field) {
        field.style.display = show ? 'block' : 'none';
    }
}

// Máscaras
function applyMasks() {
    // CPF
    const cpfInput = document.getElementById('cpf');
    if (cpfInput) {
        cpfInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 11) value = value.substring(0, 11);
            
            if (value.length > 9) {
                value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
            } else if (value.length > 6) {
                value = value.replace(/(\d{3})(\d{3})(\d{3})/, '$1.$2.$3');
            } else if (value.length > 3) {
                value = value.replace(/(\d{3})(\d{3})/, '$1.$2');
            }
            
            e.target.value = value;
        });
    }
    
    // RG
    const rgInput = document.getElementById('rg');
    if (rgInput) {
        rgInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 9) value = value.substring(0, 9);
            
            if (value.length > 8) {
                value = value.replace(/(\d{2})(\d{3})(\d{3})(\d{1})/, '$1.$2.$3-$4');
            } else if (value.length > 5) {
                value = value.replace(/(\d{2})(\d{3})(\d{3})/, '$1.$2.$3');
            } else if (value.length > 2) {
                value = value.replace(/(\d{2})(\d{3})/, '$1.$2');
            }
            
            e.target.value = value;
        });
    }
    
    // Data
    const dataInputs = document.querySelectorAll('#data_acolhimento, #family_data_nasc');
    dataInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 8) value = value.substring(0, 8);
            
            if (value.length > 4) {
                value = value.replace(/(\d{2})(\d{2})(\d{4})/, '$1/$2/$3');
            } else if (value.length > 2) {
                value = value.replace(/(\d{2})(\d{2})/, '$1/$2');
            }
            
            e.target.value = value;
        });
    });
}

// Calcular total automaticamente quando valores mudarem
document.addEventListener('DOMContentLoaded', function() {
    // Desabilitar validação HTML5 do formulário
    const form = document.getElementById('socioeconomicoForm');
    if (form) {
        form.setAttribute('novalidate', 'novalidate');
        
        // Remover atributo required de todos os campos
        const requiredFields = form.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            field.removeAttribute('required');
        });
        
        // Prevenir submit acidental do formulário (exceto na última etapa)
        form.addEventListener('submit', function(e) {
            const currentStep = parseInt(new URLSearchParams(window.location.search).get('step') || '1');
            
            // Permitir submit apenas na última etapa (Etapa 5)
            if (currentStep === 5) {
                console.log('✓ Submit permitido na etapa final');
                // Salvar dados da etapa atual antes de enviar
                saveStepData(5);
                
                // IMPORTANTE: Consolidar todos os dados das etapas anteriores
                consolidateAllSteps();
                
                return true; // Permitir submit
            }
            
            // Bloquear submit em outras etapas
            e.preventDefault();
            console.log('⚠ Submit bloqueado - Use os botões de navegação');
            return false;
        });
        
        console.log('🔧 Validação HTML5 desabilitada e submit bloqueado');
    }
    
    applyMasks();
    
    // Restaurar dados salvos
    const urlParams = new URLSearchParams(window.location.search);
    const currentStep = parseInt(urlParams.get('step') || '1');
    const editId = urlParams.get('id');
    
    // Se for nova ficha (sem ID) E primeira etapa, limpar sessionStorage
    if (!editId && currentStep === 1) {
        console.log('🆕 Nova ficha - Limpando dados salvos');
        for (let i = 1; i <= 5; i++) {
            sessionStorage.removeItem(`socioeconomico_step${i}`);
        }
        sessionStorage.removeItem('familia_json');
    }
    
    const savedData = sessionStorage.getItem(`socioeconomico_step${currentStep}`);
    
    console.log('📄 Etapa atual:', currentStep);
    console.log('🔑 ID de edição:', editId || 'Nenhum (novo cadastro)');
    console.log('💿 Dados salvos encontrados:', savedData ? 'Sim' : 'Não');
    
    // Debug: Mostrar todos os dados salvos
    console.log('📁 Dados salvos em todas as etapas:');
    for (let i = 1; i <= 5; i++) {
        const stepData = sessionStorage.getItem(`socioeconomico_step${i}`);
        if (stepData) {
            console.log(`  Etapa ${i}:`, JSON.parse(stepData));
        } else {
            console.log(`  Etapa ${i}: (vazio)`);
        }
    }
    
    if (savedData) {
        try {
            const data = JSON.parse(savedData);
            const form = document.getElementById('socioeconomicoForm');
            
            if (form) {
                console.log('⟳ Restaurando dados:', data);
                let fieldsRestored = 0;
                
                Object.keys(data).forEach(key => {
                    const field = form.querySelector(`[name="${key}"]`);
                    if (field && data[key]) {
                        field.value = data[key];
                        fieldsRestored++;
                        console.log(`  ✓ ${key}: ${data[key]}`);
                    }
                });
                
                console.log(`✓ ${fieldsRestored} campos restaurados`);
            }
        } catch (error) {
            console.error('✗ Erro ao restaurar dados:', error);
        }
    } else {
        console.log('ℹ Nenhum dado salvo para esta etapa');
    }
    
    // Restaurar família
    const familiaJson = sessionStorage.getItem('familia_json');
    if (familiaJson && currentStep === 3) {
        familyMembers = JSON.parse(familiaJson);
        updateFamilyList();
    }
    
    // Adicionar listeners para cálculo de total
    const despesaInputs = document.querySelectorAll('.despesa-input, .renda-input');
    despesaInputs.forEach(input => {
        input.addEventListener('input', calcularTotais);
    });
    
    // Calcular total inicial
    if (currentStep === 4) {
        calcularTotais();
    }
    
    // Restaurar estado dos campos condicionais
    if (currentStep === 5) {
        const cltRadio = document.querySelector('input[name="trabalho_clt"]:checked');
        if (cltRadio && cltRadio.value === 'Sim') {
            toggleCltField(true);
        }
    }
});

// Salvar família no sessionStorage antes de mudar de etapa
window.addEventListener('beforeunload', function() {
    if (familyMembers.length > 0) {
        sessionStorage.setItem('familia_json', JSON.stringify(familyMembers));
    }
});
