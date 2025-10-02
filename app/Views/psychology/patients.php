<div class="actions" style="display:flex; gap:12px; justify-content:space-between; align-items:center; margin-bottom:24px;">
    <div>
        <a href="psychology.php" class="btn secondary" style="background:#6c757d; color:#fff; border:none; padding:10px 14px; border-radius:8px; cursor:pointer; text-decoration:none;">
            ← Voltar ao Dashboard
        </a>
    </div>
    
    <div style="display:flex; gap:12px; align-items:center;">
        <div class="search-box" style="position:relative;">
            <input type="text" 
                   id="patientSearch" 
                   placeholder="Buscar paciente..." 
                   style="padding:10px 40px 10px 12px; border:2px solid #17a2b8; border-radius:8px; width:250px; font-family:Poppins;">
            <div style="position:absolute; right:12px; top:50%; transform:translateY(-50%); color:#17a2b8; font-size:16px;">🔍</div>
        </div>
        
        <select id="ageFilter" style="padding:10px 12px; border:2px solid #17a2b8; border-radius:8px; font-family:Poppins;">
            <option value="">Todas as idades</option>
            <option value="crianca">Crianças (0-11)</option>
            <option value="adolescente">Adolescentes (12-17)</option>
            <option value="adulto">Adultos (18+)</option>
        </select>
    </div>
</div>

<div class="patients-grid" style="display:grid; gap:20px;">
    <?php if (empty($patients)): ?>
        <div class="empty-state" style="text-align:center; padding:60px; background:#fff; border-radius:12px; color:#6c757d;">
            <div style="font-size:64px; margin-bottom:20px;">👥</div>
            <div style="font-size:24px; font-weight:600; margin-bottom:12px;">Nenhum paciente encontrado</div>
            <div style="font-size:16px; line-height:1.5;">
                Os pacientes aparecerão aqui automaticamente quando<br>
                fichas de acolhimento forem cadastradas no sistema.
            </div>
        </div>
    <?php else: ?>
        <div class="patients-table" style="background:#fff; border-radius:12px; overflow:hidden; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
            <table style="width:100%; border-collapse:collapse;" id="patientsTable">
                <thead>
                    <tr style="background:#f8f9fa;">
                        <th style="padding:16px; text-align:left; font-weight:600; border-bottom:1px solid #dee2e6;">Paciente</th>
                        <th style="padding:16px; text-align:left; font-weight:600; border-bottom:1px solid #dee2e6;">Idade</th>
                        <th style="padding:16px; text-align:left; font-weight:600; border-bottom:1px solid #dee2e6;">Responsável</th>
                        <th style="padding:16px; text-align:left; font-weight:600; border-bottom:1px solid #dee2e6;">Acolhimento</th>
                        <th style="padding:16px; text-align:left; font-weight:600; border-bottom:1px solid #dee2e6;">Última Anotação</th>
                        <th style="padding:16px; text-align:center; font-weight:600; border-bottom:1px solid #dee2e6; width:120px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($patients as $patient): ?>
                        <tr style="border-bottom:1px solid #f0f0f0;" class="patient-row" 
                            data-name="<?php echo strtolower($patient['nome_completo']); ?>"
                            data-cpf="<?php echo $patient['cpf']; ?>"
                            data-age-group="<?php 
                                if ($patient['idade'] < 12) echo 'crianca';
                                elseif ($patient['idade'] < 18) echo 'adolescente';
                                else echo 'adulto';
                            ?>">
                            <td style="padding:16px;">
                                <div style="display:flex; align-items:center; gap:12px;">
                                    <div class="avatar" style="width:48px; height:48px; border-radius:50%; background:linear-gradient(135deg, #17a2b8, #20c997); display:flex; align-items:center; justify-content:center; font-weight:600; color:white; font-size:18px;">
                                        <?php echo strtoupper(substr($patient['nome_completo'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <div style="font-weight:600; color:#212529; margin-bottom:2px;">
                                            <?php echo htmlspecialchars($patient['nome_completo']); ?>
                                        </div>
                                        <div style="font-size:12px; color:#6c757d; font-family:monospace;">
                                            CPF: <?php echo htmlspecialchars($patient['cpf']); ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            
                            <td style="padding:16px;">
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <span style="font-weight:600; font-size:18px; color:#17a2b8;">
                                        <?php echo $patient['idade']; ?>
                                    </span>
                                    <span style="font-size:12px; color:#6c757d;">anos</span>
                                </div>
                                <div style="font-size:11px; color:#6c757d; margin-top:2px;">
                                    <?php echo date('d/m/Y', strtotime(str_replace('/', '-', $patient['data_nascimento']))); ?>
                                </div>
                            </td>
                            
                            <td style="padding:16px;">
                                <div style="font-size:14px; color:#495057;">
                                    <?php echo htmlspecialchars($patient['responsavel']); ?>
                                </div>
                            </td>
                            
                            <td style="padding:16px;">
                                <div style="font-size:14px; color:#6c757d;">
                                    <?php echo date('d/m/Y', strtotime(str_replace('/', '-', $patient['data_acolhimento']))); ?>
                                </div>
                            </td>
                            
                            <td style="padding:16px;">
                                <?php if ($patient['last_note']): ?>
                                    <div style="font-size:14px; color:#28a745; font-weight:500;">
                                        <?php echo date('d/m/Y', strtotime($patient['last_note'])); ?>
                                    </div>
                                    <div style="font-size:11px; color:#6c757d;">
                                        <?php 
                                        $days = floor((time() - strtotime($patient['last_note'])) / (60 * 60 * 24));
                                        echo $days === 0 ? 'Hoje' : ($days === 1 ? 'Ontem' : "$days dias atrás");
                                        ?>
                                    </div>
                                <?php else: ?>
                                    <span style="color:#dc3545; font-size:14px; font-weight:500;">
                                        Sem anotações
                                    </span>
                                <?php endif; ?>
                            </td>
                            
                            <td style="padding:16px; text-align:center;">
                                <a href="psychology.php?action=patient&cpf=<?php echo urlencode($patient['cpf']); ?>" 
                                   class="btn-action" 
                                   style="background:#17a2b8; color:white; border:none; padding:8px 12px; border-radius:6px; cursor:pointer; text-decoration:none; font-size:12px; display:inline-flex; align-items:center; gap:4px;"
                                   title="Ver Prontuário Psicológico">
                                    🧠 Abrir
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="patients-summary" style="background:#f8f9fa; border-radius:12px; padding:20px; margin-top:20px; text-align:center;">
            <div style="font-size:16px; color:#495057;">
                <strong id="visibleCount"><?php echo count($patients); ?></strong> de <strong><?php echo count($patients); ?></strong> pacientes
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('patientSearch');
    const ageFilter = document.getElementById('ageFilter');
    const table = document.getElementById('patientsTable');
    const visibleCount = document.getElementById('visibleCount');
    
    if (!table) return;
    
    const rows = table.querySelectorAll('.patient-row');
    const totalCount = rows.length;
    
    function filterPatients() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedAge = ageFilter.value;
        let visibleRows = 0;
        
        rows.forEach(row => {
            const name = row.dataset.name;
            const cpf = row.dataset.cpf;
            const ageGroup = row.dataset.ageGroup;
            
            const matchesSearch = !searchTerm || 
                                name.includes(searchTerm) || 
                                cpf.includes(searchTerm);
            const matchesAge = !selectedAge || ageGroup === selectedAge;
            
            if (matchesSearch && matchesAge) {
                row.style.display = '';
                visibleRows++;
            } else {
                row.style.display = 'none';
            }
        });
        
        if (visibleCount) {
            visibleCount.textContent = visibleRows;
        }
    }
    
    searchInput.addEventListener('input', filterPatients);
    ageFilter.addEventListener('change', filterPatients);
    
    // Highlight de busca
    searchInput.addEventListener('input', function() {
        const term = this.value.toLowerCase();
        
        rows.forEach(row => {
            const nameCell = row.querySelector('td:first-child .font-weight-600');
            const cpfCell = row.querySelector('td:first-child .font-family-monospace');
            
            if (nameCell && cpfCell) {
                // Remover highlights anteriores
                nameCell.innerHTML = nameCell.textContent;
                cpfCell.innerHTML = cpfCell.textContent;
                
                if (term && term.length > 1) {
                    // Adicionar highlight
                    const nameText = nameCell.textContent;
                    const cpfText = cpfCell.textContent;
                    
                    if (nameText.toLowerCase().includes(term)) {
                        const regex = new RegExp(`(${term})`, 'gi');
                        nameCell.innerHTML = nameText.replace(regex, '<mark style="background:#fff3cd; padding:1px 2px; border-radius:2px;">$1</mark>');
                    }
                    
                    if (cpfText.toLowerCase().includes(term)) {
                        const regex = new RegExp(`(${term})`, 'gi');
                        cpfCell.innerHTML = cpfText.replace(regex, '<mark style="background:#fff3cd; padding:1px 2px; border-radius:2px;">$1</mark>');
                    }
                }
            }
        });
    });
});
</script>

<style>
.btn-action:hover {
    opacity: 0.9;
    transform: translateY(-1px);
    transition: all 0.2s ease;
}

.patient-row:hover {
    background-color: #f8f9fa;
}

.search-box input:focus {
    outline: none;
    border-color: #20c997;
    box-shadow: 0 0 0 3px rgba(32, 201, 151, 0.1);
}

@media (max-width: 768px) {
    .actions {
        flex-direction: column !important;
        gap: 16px !important;
    }
    
    .patients-table {
        overflow-x: auto;
    }
    
    .patients-table table {
        min-width: 800px;
    }
    
    .search-box input {
        width: 200px !important;
    }
}
</style>
