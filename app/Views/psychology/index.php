<div class="psychology-header" style="background: linear-gradient(135deg, #17a2b8, #20c997); border-radius: 16px; padding: 24px; margin-bottom: 24px; color: white;">
    <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 16px;">
        <div style="font-size: 48px;">🧠</div>
        <div>
            <h1 style="margin: 0; font-size: 28px; font-weight: 700;">Área Psicológica</h1>
            <p style="margin: 4px 0 0 0; opacity: 0.9; font-size: 16px;">Acompanhamento e avaliação psicológica especializada</p>
        </div>
    </div>
    
    <div style="background: rgba(255,255,255,0.1); border-radius: 12px; padding: 16px;">
        <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">🔒 Área Confidencial e Exclusiva</div>
        <div style="font-size: 13px; line-height: 1.4; opacity: 0.8;">
            Esta área é privada e exclusiva para psicólogos. Todas as informações aqui são confidenciais e protegidas pelo sigilo profissional.
        </div>
    </div>
</div>

<div class="actions" style="display:flex; gap:12px; justify-content:flex-end; margin-bottom:24px;">
    <a href="psychology.php?action=patients" class="btn" style="background:#17a2b8; color:#fff; border:none; padding:12px 16px; border-radius:8px; cursor:pointer; text-decoration:none; display:flex; align-items:center; gap:8px;">
        👥 Ver Pacientes
    </a>
    <a href="psychology.php?action=report" class="btn" style="background:#6f42c1; color:#fff; border:none; padding:12px 16px; border-radius:8px; cursor:pointer; text-decoration:none; display:flex; align-items:center; gap:8px;">
        📊 Relatórios
    </a>
</div>

<!-- Estatísticas -->
<div class="stats-grid" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(250px, 1fr)); gap:20px; margin-bottom:32px;">
    <div class="stat-card" style="background:#fff; border-radius:12px; padding:20px; box-shadow: 0 2px 10px rgba(0,0,0,.08); border-left:4px solid #17a2b8;">
        <div style="display:flex; align-items:center; gap:12px;">
            <div style="font-size:32px;">👥</div>
            <div>
                <div style="font-size:24px; font-weight:700; color:#17a2b8;"><?php echo $stats['total_patients']; ?></div>
                <div style="color:#6c757d; font-size:14px;">Total de Pacientes</div>
            </div>
        </div>
    </div>
    
    <div class="stat-card" style="background:#fff; border-radius:12px; padding:20px; box-shadow: 0 2px 10px rgba(0,0,0,.08); border-left:4px solid #28a745;">
        <div style="display:flex; align-items:center; gap:12px;">
            <div style="font-size:32px;">📝</div>
            <div>
                <div style="font-size:24px; font-weight:700; color:#28a745;"><?php echo $stats['total_notes']; ?></div>
                <div style="color:#6c757d; font-size:14px;">Total de Anotações</div>
            </div>
        </div>
    </div>
    
    <div class="stat-card" style="background:#fff; border-radius:12px; padding:20px; box-shadow: 0 2px 10px rgba(0,0,0,.08); border-left:4px solid #ffc107;">
        <div style="display:flex; align-items:center; gap:12px;">
            <div style="font-size:32px;">📅</div>
            <div>
                <div style="font-size:24px; font-weight:700; color:#ffc107;"><?php echo $stats['notes_this_month']; ?></div>
                <div style="color:#6c757d; font-size:14px;">Anotações este Mês</div>
            </div>
        </div>
    </div>
    
    <div class="stat-card" style="background:#fff; border-radius:12px; padding:20px; box-shadow: 0 2px 10px rgba(0,0,0,.08); border-left:4px solid #dc3545;">
        <div style="display:flex; align-items:center; gap:12px;">
            <div style="font-size:32px;">🎯</div>
            <div>
                <div style="font-size:24px; font-weight:700; color:#dc3545;"><?php echo $stats['active_treatments']; ?></div>
                <div style="color:#6c757d; font-size:14px;">Acompanhamentos Ativos</div>
            </div>
        </div>
    </div>
</div>

<!-- Distribuição por Faixa Etária -->
<div class="charts-grid" style="display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-bottom:32px;">
    <div class="chart-card" style="background:#fff; border-radius:12px; padding:24px; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
        <h3 style="margin:0 0 20px 0; color:#495057; display:flex; align-items:center; gap:8px;">
            👶 Distribuição por Faixa Etária
        </h3>
        
        <div class="age-groups">
            <div class="age-group" style="display:flex; justify-content:space-between; align-items:center; padding:12px 0; border-bottom:1px solid #f0f0f0;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:12px; height:12px; border-radius:50%; background:#17a2b8;"></div>
                    <span>Crianças (0-11 anos)</span>
                </div>
                <span style="font-weight:600; color:#17a2b8;"><?php echo $stats['by_age_group']['crianca']; ?></span>
            </div>
            
            <div class="age-group" style="display:flex; justify-content:space-between; align-items:center; padding:12px 0; border-bottom:1px solid #f0f0f0;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:12px; height:12px; border-radius:50%; background:#28a745;"></div>
                    <span>Adolescentes (12-17 anos)</span>
                </div>
                <span style="font-weight:600; color:#28a745;"><?php echo $stats['by_age_group']['adolescente']; ?></span>
            </div>
            
            <div class="age-group" style="display:flex; justify-content:space-between; align-items:center; padding:12px 0;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:12px; height:12px; border-radius:50%; background:#ffc107;"></div>
                    <span>Adultos (18+ anos)</span>
                </div>
                <span style="font-weight:600; color:#ffc107;"><?php echo $stats['by_age_group']['adulto']; ?></span>
            </div>
        </div>
    </div>
    
    <div class="chart-card" style="background:#fff; border-radius:12px; padding:24px; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
        <h3 style="margin:0 0 20px 0; color:#495057; display:flex; align-items:center; gap:8px;">
            📊 Tipos de Anotações
        </h3>
        
        <div class="note-types">
            <div class="note-type" style="display:flex; justify-content:space-between; align-items:center; padding:12px 0; border-bottom:1px solid #f0f0f0;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <span>💬</span>
                    <span>Consultas</span>
                </div>
                <span style="font-weight:600; color:#17a2b8;"><?php echo $stats['by_note_type']['consulta']; ?></span>
            </div>
            
            <div class="note-type" style="display:flex; justify-content:space-between; align-items:center; padding:12px 0; border-bottom:1px solid #f0f0f0;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <span>📋</span>
                    <span>Avaliações</span>
                </div>
                <span style="font-weight:600; color:#28a745;"><?php echo $stats['by_note_type']['avaliacao']; ?></span>
            </div>
            
            <div class="note-type" style="display:flex; justify-content:space-between; align-items:center; padding:12px 0; border-bottom:1px solid #f0f0f0;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <span>📈</span>
                    <span>Evoluções</span>
                </div>
                <span style="font-weight:600; color:#ffc107;"><?php echo $stats['by_note_type']['evolucao']; ?></span>
            </div>
            
            <div class="note-type" style="display:flex; justify-content:space-between; align-items:center; padding:12px 0;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <span>👁️</span>
                    <span>Observações</span>
                </div>
                <span style="font-weight:600; color:#dc3545;"><?php echo $stats['by_note_type']['observacao']; ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Anotações Recentes -->
<div class="recent-notes" style="background:#fff; border-radius:12px; padding:24px; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
    <h3 style="margin:0 0 20px 0; color:#495057; display:flex; align-items:center; gap:8px;">
        🕒 Anotações Recentes
    </h3>
    
    <?php if (empty($recentNotes)): ?>
        <div class="empty-state" style="text-align:center; padding:40px; color:#6c757d;">
            <div style="font-size:48px; margin-bottom:16px;">📝</div>
            <div style="font-size:18px; font-weight:600; margin-bottom:8px;">Nenhuma anotação ainda</div>
            <div>Comece criando anotações para seus pacientes</div>
        </div>
    <?php else: ?>
        <div class="notes-list">
            <?php foreach ($recentNotes as $note): ?>
                <div class="note-item" style="border-bottom:1px solid #f0f0f0; padding:16px 0; display:flex; gap:16px;">
                    <div class="note-icon" style="font-size:24px; margin-top:4px;">
                        <?php
                        $icons = [
                            'consulta' => '💬',
                            'avaliacao' => '📋',
                            'evolucao' => '📈',
                            'observacao' => '👁️'
                        ];
                        echo $icons[$note['note_type']] ?? '📝';
                        ?>
                    </div>
                    
                    <div style="flex:1;">
                        <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:8px;">
                            <div>
                                <div style="font-weight:600; color:#212529; margin-bottom:4px;">
                                    <?php echo htmlspecialchars($note['title'] ?: ucfirst($note['note_type'])); ?>
                                </div>
                                <div style="font-size:14px; color:#6c757d;">
                                    Paciente: <strong><?php echo htmlspecialchars($note['patient_cpf']); ?></strong>
                                </div>
                            </div>
                            <div style="font-size:12px; color:#6c757d; text-align:right;">
                                <?php echo date('d/m/Y H:i', strtotime($note['created_at'])); ?>
                            </div>
                        </div>
                        
                        <div style="font-size:14px; color:#495057; line-height:1.4;">
                            <?php 
                            $content = htmlspecialchars($note['content']);
                            echo strlen($content) > 150 ? substr($content, 0, 150) . '...' : $content;
                            ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div style="text-align:center; margin-top:20px;">
            <a href="psychology.php?action=patients" class="btn secondary" style="background:#6c757d; color:#fff; border:none; padding:10px 16px; border-radius:8px; cursor:pointer; text-decoration:none;">
                Ver Todos os Pacientes
            </a>
        </div>
    <?php endif; ?>
</div>

<style>
@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr !important;
    }
    
    .charts-grid {
        grid-template-columns: 1fr !important;
    }
    
    .psychology-header {
        padding: 16px !important;
    }
    
    .psychology-header h1 {
        font-size: 24px !important;
    }
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(0,0,0,.12);
    transition: all 0.3s ease;
}

.chart-card:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 20px rgba(0,0,0,.12);
    transition: all 0.3s ease;
}
</style>
