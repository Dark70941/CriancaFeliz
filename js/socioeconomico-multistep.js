// Estado local em memória apenas para montar inputs do formulário
let familyMembers = [];
let despesasCount = 0;

// Navegação entre etapas
function nextStep() {
    const form = document.getElementById('socioeconomicoForm');
    if (!form) return;
    const currentStep = parseInt((new URLSearchParams(window.location.search)).get('step') || '1');
    if (!validateCurrentStep(currentStep)) return;
    const stepInput = form.querySelector('input[name="step"]');
    if (stepInput) stepInput.value = String(currentStep + 1);
    form.action = 'socioeconomico_form.php';
    form.method = 'post';
    form.submit();
}

function prevStep() {
    const form = document.getElementById('socioeconomicoForm');
    if (!form) return;
    const currentStep = parseInt((new URLSearchParams(window.location.search)).get('step') || '1');
    const stepInput = form.querySelector('input[name="step"]');
    if (stepInput) stepInput.value = String(Math.max(1, currentStep - 1));
    form.action = 'socioeconomico_form.php';
    form.method = 'post';
    form.submit();
}

function validateCurrentStep(step) {
    const form = document.getElementById('socioeconomicoForm');
    
    if (!form) {
        console.error('Formulário não encontrado');
        return true; // Permitir navegação se formulário não existir
    }
    
    // Validação específica por etapa
    if (step === 1) {
        const atendidoSelect = form.querySelector('#id_atendido');

        console.log('Validando etapa 1:', {
            id_atendido: atendidoSelect?.value
        });

        if (!atendidoSelect || !atendidoSelect.value) {
            alert('❌ Campo obrigatório:\n\nSelecione a criança/atendido antes de continuar.');
            if (atendidoSelect) {
                atendidoSelect.focus();
                atendidoSelect.style.borderColor = '#e74c3c';
            }
            return false;
        }

        atendidoSelect.style.borderColor = '#4a7c8f';
    }
    
    return true;
}

// Removido: não usamos armazenamento no navegador

/**
 * Consolida todos os dados das etapas anteriores em campos hidden
 * para serem enviados junto com o formulário
 */
// Removido: consolidação via sessionStorage

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
    
    // Gerar inputs reais (arrays) para submit
    const container = document.getElementById('familia_fields');
    const index = familyMembers.length - 1;
    const wrapper = document.createElement('div');
    wrapper.id = `familia_group_${member.id}`;
    wrapper.innerHTML = `
        <input type="hidden" name="familia[${index}][nome]" value="${nome}">
        <input type="hidden" name="familia[${index}][parentesco]" value="${parentesco}">
        <input type="hidden" name="familia[${index}][data_nasc]" value="${dataNasc}">
        <input type="hidden" name="familia[${index}][formacao]" value="${formacao}">
        <input type="hidden" name="familia[${index}][renda]" value="${parseFloat(renda)||0}">
    `;
    container && container.appendChild(wrapper);
}

function removeFamilyMember(id) {
    if (!confirm('Deseja remover este integrante?')) {
        return;
    }
    
    familyMembers = familyMembers.filter(m => m.id !== id);
    updateFamilyList();
    
    // Remover inputs do grupo
    const g = document.getElementById(`familia_group_${id}`);
    if (g && g.parentNode) g.parentNode.removeChild(g);
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
            <input type="text" name="despesas[${despesasCount}][tipo]" placeholder="Nome da despesa" style="flex: 1; padding: 10px; border: 2px solid #4a7c8f; border-radius: 6px;">
            <input type="number" name="despesas[${despesasCount}][valor]" class="despesa-input" step="0.01" placeholder="Valor" style="width: 120px; padding: 10px; border: 2px solid #4a7c8f; border-radius: 6px;">
            <input type="number" name="despesas[${despesasCount}][renda]" step="0.01" placeholder="Renda relacionada (opcional)" style="width: 140px; padding: 10px; border: 2px solid #4a7c8f; border-radius: 6px;">
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
    }
    
    applyMasks();
    
    const urlParams = new URLSearchParams(window.location.search);
    const currentStep = parseInt(urlParams.get('step') || '1');
    
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
    
    // Validação da etapa 1
    if (currentStep === 1) {
        const atendidoSelect = document.getElementById('id_atendido');
        if (atendidoSelect) {
            atendidoSelect.required = true;
        }
    }
});

