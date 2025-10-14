<div class="actions" style="display:flex; gap:10px; justify-content:flex-end; margin-bottom:20px;">
    <a href="prontuarios.php" class="btn secondary" style="background:#6b7b84; color:#fff; border:none; padding:10px 14px; border-radius:8px; cursor:pointer; text-decoration:none;">← Voltar</a>
    <a href="socioeconomico_form.php" class="btn" style="background:#ff7a00; color:#fff; border:none; padding:10px 14px; border-radius:8px; cursor:pointer; text-decoration:none;">+ Cadastrar</a>
</div>

<!-- Filtros de busca -->
<div class="search-filters" style="background:#fff; border-radius:12px; padding:16px; margin-bottom:20px; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
    <form method="GET" style="display:grid; grid-template-columns: 1fr 200px 120px; gap:12px; align-items:end;">
        <div>
            <label style="font-size:14px; color:#354047; font-weight:600; display:block; margin-bottom:4px;">Buscar por nome</label>
            <input type="text" name="q" placeholder="Digite o nome..." value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>" 
                   style="padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;">
        </div>
        <div>
            <label style="font-size:14px; color:#354047; font-weight:600; display:block; margin-bottom:4px;">CPF</label>
            <input type="text" name="cpf" placeholder="000.000.000-00" value="<?php echo htmlspecialchars($_GET['cpf'] ?? ''); ?>" 
                   style="padding:10px 12px; border:2px solid #f0a36b; border-radius:8px; font-family:Poppins; background:#fff; width:100%; box-sizing:border-box;">
        </div>
        <div>
            <button type="submit" class="btn" style="background:#6fb64f; color:#fff; border:none; padding:10px 14px; border-radius:8px; cursor:pointer; width:100%;">Buscar</button>
        </div>
    </form>
</div>

<!-- Tabela de resultados -->
<div class="table-container" style="background:#fff; border-radius:12px; overflow:hidden; box-shadow: 0 2px 10px rgba(0,0,0,.08);">
    <?php if (!empty($fichas)): ?>
        <table style="width:100%; border-collapse:collapse;">
            <thead style="background:#f8f9fa;">
                <tr>
                    <th style="padding:12px; text-align:left; border-bottom:1px solid #dee2e6; font-weight:600; color:#495057;">Nome</th>
                    <th style="padding:12px; text-align:left; border-bottom:1px solid #dee2e6; font-weight:600; color:#495057;">CPF</th>
                    <th style="padding:12px; text-align:left; border-bottom:1px solid #dee2e6; font-weight:600; color:#495057;">Idade</th>
                    <th style="padding:12px; text-align:left; border-bottom:1px solid #dee2e6; font-weight:600; color:#495057;">Renda Familiar</th>
                    <th style="padding:12px; text-align:left; border-bottom:1px solid #dee2e6; font-weight:600; color:#495057;">Benefícios</th>
                    <th style="padding:12px; text-align:left; border-bottom:1px solid #dee2e6; font-weight:600; color:#495057;">Status</th>
                    <th style="padding:12px; text-align:center; border-bottom:1px solid #dee2e6; font-weight:600; color:#495057; width:160px;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($fichas as $ficha): ?>
                    <tr style="border-bottom:1px solid #dee2e6;">
                        <td style="padding:12px; color:#212529;"><?php echo htmlspecialchars($ficha['nome_entrevistado'] ?? $ficha['nome_completo'] ?? ''); ?></td>
                        <td style="padding:12px; color:#212529;">
                            <?php 
                            $cpf = $ficha['cpf'] ?? '';
                            if ($cpf && strlen($cpf) == 11) {
                                echo substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
                            } else {
                                echo $cpf;
                            }
                            ?>
                        </td>
                        <td style="padding:12px; color:#212529;">
                            <?php 
                            $idade = 'N/A';
                            if (!empty($ficha['data_nascimento'])) {
                                $parts = explode('/', $ficha['data_nascimento']);
                                if (count($parts) == 3) {
                                    $date = DateTime::createFromFormat('d/m/Y', $ficha['data_nascimento']);
                                    if ($date) {
                                        $now = new DateTime();
                                        $idade = $now->diff($date)->y;
                                    }
                                }
                            }
                            echo $idade;
                            ?> anos
                        </td>
                        <td style="padding:12px; color:#212529;">
                            <?php 
                            $renda = $ficha['renda_familiar'] ?? 0;
                            echo $renda > 0 ? 'R$ ' . number_format($renda, 2, ',', '.') : 'Não informado';
                            ?>
                        </td>
                        <td style="padding:12px; color:#212529;">
                            <?php 
                            $beneficios = [];
                            if (!empty($ficha['bolsa_familia'])) $beneficios[] = 'Bolsa Família';
                            if (!empty($ficha['auxilio_brasil'])) $beneficios[] = 'Auxílio Brasil';
                            if (!empty($ficha['bpc'])) $beneficios[] = 'BPC';
                            echo !empty($beneficios) ? implode(', ', $beneficios) : 'Nenhum';
                            ?>
                        </td>
                        <td style="padding:12px; color:#212529;">
                            <span class="status <?php echo strtolower($ficha['status'] ?? 'ativo'); ?>" 
                                  style="padding:4px 8px; border-radius:12px; font-size:12px; font-weight:500;
                                         <?php echo ($ficha['status'] ?? 'Ativo') === 'Ativo' ? 'background:#e8f6ea; color:#6fb64f;' : 'background:#f8d7da; color:#721c24;'; ?>">
                                <?php echo $ficha['status'] ?? 'Ativo'; ?>
                            </span>
                        </td>
                        <td style="padding:12px; text-align:center;">
                            <a href="socioeconomico_view.php?id=<?php echo urlencode($ficha['id']); ?>" 
                               class="btn-icon" 
                               title="Visualizar"
                               style="background:#17a2b8; color:#fff; border:none; padding:8px 10px; border-radius:6px; cursor:pointer; text-decoration:none; font-size:14px; margin:0 4px; display:inline-block;">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="socioeconomico_form.php?id=<?php echo urlencode($ficha['id']); ?>" 
                               class="btn-icon" 
                               title="Editar"
                               style="background:#ffc107; color:#fff; border:none; padding:8px 10px; border-radius:6px; cursor:pointer; text-decoration:none; font-size:14px; margin:0 4px; display:inline-block;">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="socioeconomico_list.php?delete=<?php echo urlencode($ficha['id']); ?>" 
                               class="btn-icon" 
                               title="Excluir"
                               onclick="return confirm('Tem certeza que deseja excluir esta ficha?')"
                               style="background:#e74c3c; color:#fff; border:none; padding:8px 10px; border-radius:6px; cursor:pointer; text-decoration:none; font-size:14px; margin:0 4px; display:inline-block;">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <!-- Paginação -->
        <?php if ($pagination['last_page'] > 1): ?>
            <div class="pagination" style="padding:16px; text-align:center; border-top:1px solid #dee2e6;">
                <?php if ($pagination['current_page'] > 1): ?>
                    <a href="?page=<?php echo $pagination['current_page'] - 1; ?>&q=<?php echo urlencode($_GET['q'] ?? ''); ?>&cpf=<?php echo urlencode($_GET['cpf'] ?? ''); ?>" 
                       class="btn btn-sm secondary" style="background:#6b7b84; color:#fff; border:none; padding:6px 12px; border-radius:6px; cursor:pointer; text-decoration:none; margin:0 2px;">
                        ← Anterior
                    </a>
                <?php endif; ?>
                
                <span style="margin:0 10px; color:#495057;">
                    Página <?php echo $pagination['current_page']; ?> de <?php echo $pagination['last_page']; ?>
                    (<?php echo $pagination['total']; ?> registros)
                </span>
                
                <?php if ($pagination['current_page'] < $pagination['last_page']): ?>
                    <a href="?page=<?php echo $pagination['current_page'] + 1; ?>&q=<?php echo urlencode($_GET['q'] ?? ''); ?>&cpf=<?php echo urlencode($_GET['cpf'] ?? ''); ?>" 
                       class="btn btn-sm secondary" style="background:#6b7b84; color:#fff; border:none; padding:6px 12px; border-radius:6px; cursor:pointer; text-decoration:none; margin:0 2px;">
                        Próxima →
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
    <?php else: ?>
        <div style="padding:40px; text-align:center; color:#6c757d;">
            <div style="font-size:48px; margin-bottom:16px;">
                <i class="fas fa-home" style="color:#6c757d;"></i>
            </div>
            <h3 style="margin:0 0 8px 0; color:#495057;">Nenhuma ficha encontrada</h3>
            <p style="margin:0; color:#6c757d;">
                <?php if (!empty($_GET['q']) || !empty($_GET['cpf'])): ?>
                    Nenhum resultado para os filtros aplicados.
                    <br><a href="socioeconomico_list.php" style="color:#ff7a00;">Ver todas as fichas</a>
                <?php else: ?>
                    Comece cadastrando sua primeira ficha socioeconômica.
                    <br><a href="socioeconomico_form.php" style="color:#ff7a00;">Cadastrar primeira ficha</a>
                <?php endif; ?>
            </p>
        </div>
    <?php endif; ?>
</div>

<style>
    /* Responsividade para tabela */
    @media (max-width: 768px) {
        .search-filters form {
            grid-template-columns: 1fr !important;
            gap: 8px !important;
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        table {
            min-width: 900px;
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
        .search-filters {
            padding: 12px !important;
        }
        
        table th,
        table td {
            padding: 8px !important;
            font-size: 14px;
        }
        
        .btn-sm {
            padding: 4px 8px !important;
            font-size: 11px !important;
            min-width: 50px !important;
        }
    }
</style>

<script>
    // Detectar parâmetros de notificação
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.get('saved') === '1') {
        if (window.notificationSystem) {
            window.notificationSystem.save('Ficha socioeconômica cadastrada com sucesso!');
        }
        // Limpar URL
        window.history.replaceState({}, document.title, window.location.pathname);
    }
    
    if (urlParams.get('deleted') === '1') {
        if (window.notificationSystem) {
            window.notificationSystem.delete('Ficha socioeconômica excluída com sucesso!');
        }
        // Limpar URL
        window.history.replaceState({}, document.title, window.location.pathname);
    }
</script>
