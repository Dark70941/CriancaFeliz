<div class="search-container" style="background:#fff; border-radius:12px; padding:20px; margin-bottom:20px; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
    <h3 style="margin:0 0 16px 0; color:#495057;">🔍 Sistema de Busca Avançada</h3>
    
    <div class="search-form" style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
        <input type="text" id="searchInput" placeholder="Buscar por nome, CPF, RG..." 
               style="flex:1; min-width:300px; padding:12px; border:1px solid #ddd; border-radius:8px; font-size:14px;">
        
        <select id="categoryFilter" style="padding:12px; border:1px solid #ddd; border-radius:8px; font-size:14px;">
            <option value="">Todas as categorias</option>
            <option value="crianca">Criança (0-12 anos)</option>
            <option value="adolescente">Adolescente (13-17 anos)</option>
            <option value="adulto">Adulto (18+ anos)</option>
        </select>
        
        <button id="searchBtn" class="btn" style="background:#6fb64f; color:#fff; border:none; padding:12px 20px; border-radius:8px; cursor:pointer;">
            Buscar
        </button>
        
        <button id="clearBtn" class="btn secondary" style="background:#6b7b84; color:#fff; border:none; padding:12px 20px; border-radius:8px; cursor:pointer;">
            Limpar
        </button>
    </div>
</div>

<div id="searchResults" style="display:none;">
    <div class="results-header" style="background:#fff; border-radius:12px; padding:16px; margin-bottom:20px; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
        <h3 style="margin:0; color:#495057;">📋 Resultados da Busca</h3>
        <div id="resultsCount" style="color:#6c757d; font-size:14px; margin-top:4px;"></div>
    </div>
    
    <div id="resultsContainer"></div>
</div>

<div id="defaultView">
    <!-- Estatísticas -->
    <div class="stats-grid" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(250px, 1fr)); gap:20px; margin-bottom:30px;">
        <div class="stat-card" style="background:#fff; border-radius:12px; padding:20px; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
            <div style="display:flex; align-items:center; gap:12px;">
                <div style="width:48px; height:48px; background:#e8f6ea; border-radius:12px; display:grid; place-items:center; font-size:24px;">📋</div>
                <div>
                    <div style="font-size:24px; font-weight:700; color:#495057;"><?php echo count($acolhimentos); ?></div>
                    <div style="color:#6c757d; font-size:14px;">Fichas de Acolhimento</div>
                </div>
            </div>
        </div>
        
        <div class="stat-card" style="background:#fff; border-radius:12px; padding:20px; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
            <div style="display:flex; align-items:center; gap:12px;">
                <div style="width:48px; height:48px; background:#fff3e0; border-radius:12px; display:grid; place-items:center; font-size:24px;">🏘️</div>
                <div>
                    <div style="font-size:24px; font-weight:700; color:#495057;"><?php echo count($socioeconomicos); ?></div>
                    <div style="color:#6c757d; font-size:14px;">Fichas Socioeconômicas</div>
                </div>
            </div>
        </div>
        
        <div class="stat-card" style="background:#fff; border-radius:12px; padding:20px; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
            <div style="display:flex; align-items:center; gap:12px;">
                <div style="width:48px; height:48px; background:#e3f2fd; border-radius:12px; display:grid; place-items:center; font-size:24px;">👥</div>
                <div>
                    <?php 
                    $totalProntuarios = count(array_unique(array_merge(
                        array_column($acolhimentos, 'cpf'),
                        array_column($socioeconomicos, 'cpf')
                    )));
                    ?>
                    <div style="font-size:24px; font-weight:700; color:#495057;"><?php echo $totalProntuarios; ?></div>
                    <div style="color:#6c757d; font-size:14px;">Total de Prontuários</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Ações Rápidas -->
    <div class="quick-actions" style="background:#fff; border-radius:12px; padding:20px; margin-bottom:30px; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
        <h3 style="margin:0 0 16px 0; color:#495057;">⚡ Ações Rápidas</h3>
        
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px;">
            <a href="acolhimento_form.php" class="action-card" style="display:flex; align-items:center; gap:12px; padding:16px; border:2px solid #e8f6ea; border-radius:12px; text-decoration:none; color:#495057; transition:all 0.3s ease;">
                <div style="width:40px; height:40px; background:#6fb64f; border-radius:10px; display:grid; place-items:center; color:#fff; font-size:20px;">📋</div>
                <div>
                    <div style="font-weight:600;">Nova Ficha de Acolhimento</div>
                    <div style="font-size:12px; color:#6c757d;">Cadastrar nova ficha</div>
                </div>
            </a>
            
            <a href="socioeconomico_form.php" class="action-card" style="display:flex; align-items:center; gap:12px; padding:16px; border:2px solid #fff3e0; border-radius:12px; text-decoration:none; color:#495057; transition:all 0.3s ease;">
                <div style="width:40px; height:40px; background:#f0a36b; border-radius:10px; display:grid; place-items:center; color:#fff; font-size:20px;">🏘️</div>
                <div>
                    <div style="font-weight:600;">Nova Ficha Socioeconômica</div>
                    <div style="font-size:12px; color:#6c757d;">Cadastrar nova ficha</div>
                </div>
            </a>
            
            <a href="acolhimento_list.php" class="action-card" style="display:flex; align-items:center; gap:12px; padding:16px; border:2px solid #e3f2fd; border-radius:12px; text-decoration:none; color:#495057; transition:all 0.3s ease;">
                <div style="width:40px; height:40px; background:#2196f3; border-radius:10px; display:grid; place-items:center; color:#fff; font-size:20px;">📄</div>
                <div>
                    <div style="font-weight:600;">Listar Acolhimentos</div>
                    <div style="font-size:12px; color:#6c757d;">Ver todas as fichas</div>
                </div>
            </a>
            
            <a href="socioeconomico_list.php" class="action-card" style="display:flex; align-items:center; gap:12px; padding:16px; border:2px solid #f3e5f5; border-radius:12px; text-decoration:none; color:#495057; transition:all 0.3s ease;">
                <div style="width:40px; height:40px; background:#9c27b0; border-radius:10px; display:grid; place-items:center; color:#fff; font-size:20px;">📊</div>
                <div>
                    <div style="font-weight:600;">Listar Socioeconômicas</div>
                    <div style="font-size:12px; color:#6c757d;">Ver todas as fichas</div>
                </div>
            </a>
        </div>
    </div>
</div>

<style>
    .action-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    
    .search-form input:focus,
    .search-form select:focus {
        outline: none;
        border-color: #6fb64f;
        box-shadow: 0 0 0 3px rgba(111, 182, 79, 0.1);
    }
    
    .btn:hover {
        opacity: 0.9;
        transform: translateY(-1px);
    }
    
    /* Responsividade */
    @media (max-width: 768px) {
        .search-form {
            flex-direction: column;
            align-items: stretch;
        }
        
        .search-form input,
        .search-form select,
        .search-form button {
            width: 100%;
            min-width: auto;
        }
        
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .quick-actions > div {
            grid-template-columns: 1fr;
        }
    }
</style>

<script>
// Sistema de busca será implementado via API
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const searchBtn = document.getElementById('searchBtn');
    const clearBtn = document.getElementById('clearBtn');
    const searchResults = document.getElementById('searchResults');
    const defaultView = document.getElementById('defaultView');
    
    // Implementar busca quando a API estiver disponível
    searchBtn.addEventListener('click', function() {
        const query = searchInput.value.trim();
        const category = categoryFilter.value;
        
        if (query.length < 2) {
            alert('Digite pelo menos 2 caracteres para buscar');
            return;
        }
        
        // Por enquanto, mostrar mensagem
        alert('Sistema de busca será implementado em breve!');
    });
    
    clearBtn.addEventListener('click', function() {
        searchInput.value = '';
        categoryFilter.value = '';
        searchResults.style.display = 'none';
        defaultView.style.display = 'block';
    });
});
</script>
