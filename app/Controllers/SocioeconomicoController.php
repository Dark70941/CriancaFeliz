<?php

/**
 * Controller para fichas socioeconômicas
 */
class SocioeconomicoController extends BaseController {
    private $socioeconomicoService;
    
    public function __construct() {
        parent::__construct();
        $this->socioeconomicoService = new SocioeconomicoService();
    }
    
    /**
     * Lista fichas socioeconômicas
     */
    public function index() {
        $this->requireAuth();
        
        try {
            $page = intval($this->getParam('page', 1));
            $perPage = 10;
            
            $query = trim($this->getParam('q', ''));
            $cpf = trim($this->getParam('cpf', ''));
            
            if ($query !== '' || $cpf !== '') {
                // Busca sem paginação estrita (limite interno no Model)
                $filters = [];
                if ($cpf !== '') { $filters['cpf'] = preg_replace('/\D+/', '', $cpf); }
                $items = $this->socioeconomicoService->searchFichas($query !== '' ? $query : ($filters['cpf'] ?? ''), $filters);
                $result = [
                    'data' => $items,
                    'total' => count($items),
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => count($items)
                ];
            } else {
                // Listagem padrão paginada
                $result = $this->socioeconomicoService->listFichas($page, $perPage);
            }
            
            // Adicionar dados calculados
            foreach ($result['data'] as &$ficha) {
                $ficha['idade'] = $this->calculateAge($ficha['data_nascimento'] ?? '');
                $ficha['renda_familiar'] = $this->calculateRendaFamiliar($ficha);
                $ficha['situacao_economica'] = $this->categorizeSituacao(
                    $ficha['renda_familiar'], 
                    intval($ficha['numero_membros'] ?? 1)
                );
            }
            
            $data = [
                'title' => 'Fichas Socioeconômicas',
                'fichas' => $result['data'] ?? [],
                'pagination' => [
                    'current_page' => $result['current_page'] ?? 1,
                    'last_page' => $result['last_page'] ?? 1,
                    'total' => $result['total'] ?? 0,
                    'per_page' => $result['per_page'] ?? 10
                ],
                'current_page' => $result['current_page'] ?? 1,
                'last_page' => $result['last_page'] ?? 1,
                'per_page' => $result['per_page'] ?? 10,
                'messages' => $this->getFlashMessages()
            ];
            
            $this->renderWithLayout('main', 'socioeconomico/index', $data);
            
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * Exibe formulário de criação (multi-step)
     */
    public function create() {
        $this->requireAuth();
        $this->requirePermission('create_records');
        
        // Verificar se é edição (GET ou POST)
        $id = $this->getParam('id');
        $step = intval($this->getParam('step') ?? 1);
        $ficha = null;
        
        // Se há dados POST de navegação entre etapas (não é finalização), preservar em sessão
        if ($this->isPost() && !empty($_POST['step']) && empty($_POST['finalizar'])) {
            $step = intval($_POST['step']);
            $id = $id ?? ($_POST['id'] ?? null);
            
            // Salvar dados do POST em sessão temporária para preservar entre etapas
            if (!isset($_SESSION['socioeconomico_temp'])) {
                $_SESSION['socioeconomico_temp'] = [];
            }
            // Mesclar dados do POST com dados já salvos (preservar ID)
            $tempData = $_POST;
            if ($id) {
                $tempData['id'] = $id;
            }
            $_SESSION['socioeconomico_temp'] = array_merge($_SESSION['socioeconomico_temp'], $tempData);
        }
        
        // Se há ID, carregar dados existentes
        if ($id) {
            try {
                $ficha = $this->socioeconomicoService->getFicha($id);
                if (!$ficha) {
                    throw new Exception('Ficha não encontrada');
                }
                // Mesclar com dados temporários se houver
                if (isset($_SESSION['socioeconomico_temp'])) {
                    $ficha = array_merge($ficha, $_SESSION['socioeconomico_temp']);
                    $ficha['id'] = $id; // Garantir que o ID não seja sobrescrito
                }
            } catch (Exception $e) {
                // Limpar sessão temporária em caso de erro
                if (isset($_SESSION['socioeconomico_temp'])) {
                    unset($_SESSION['socioeconomico_temp']);
                }
                $this->redirectWithError('socioeconomico_list.php', 'Ficha não encontrada');
                return;
            }
        } else {
            // Se não há ID mas há dados temporários, usar eles
            if (isset($_SESSION['socioeconomico_temp'])) {
                $ficha = $_SESSION['socioeconomico_temp'];
            }
        }
        
        // Lista de atendidos para seleção (quando disponível via DB)
        $atendidos = $this->socioeconomicoService->listAtendidos(100);
        
        $data = [
            'title' => ($id && $ficha) ? 'Editar Ficha Socioeconômica' : 'Cadastrar Ficha Socioeconômica',
            'csrf_token' => $this->generateCSRF(),
            'messages' => $this->getFlashMessages(),
            'ficha' => $ficha,
            'atendidos' => $atendidos
        ];
        
        $this->renderWithLayout('main', 'socioeconomico/create_multistep', $data);
    }
    
    /**
     * Processa criação ou atualização da ficha
     */
    public function store() {
        $this->requireAuth();
        $this->requirePermission('create_records');
        
        if (!$this->isPost()) {
            redirect('socioeconomico_form.php');
        }
        
        // Se é apenas navegação entre etapas, salvar dados na sessão e redirecionar para create()
        if (!empty($_POST['step']) && empty($_POST['finalizar'])) {
            $id = $_POST['id'] ?? null;
            $step = $_POST['step'];
            
            // Salvar dados do POST em sessão temporária para preservar entre etapas
            if (!isset($_SESSION['socioeconomico_temp'])) {
                $_SESSION['socioeconomico_temp'] = [];
            }
            // Mesclar dados do POST com dados já salvos (preservar ID)
            $tempData = $this->getPostData();
            if ($id) {
                $tempData['id'] = $id;
            }
            $_SESSION['socioeconomico_temp'] = array_merge($_SESSION['socioeconomico_temp'], $tempData);
            
            $url = 'socioeconomico_form.php?step=' . $step;
            if ($id) {
                $url .= '&id=' . urlencode($id);
            }
            redirect($url);
            return;
        }
        
        try {
            $this->validateCSRF();
            
            // Combinar dados do POST com dados da sessão temporária
            $data = $this->getPostData();
            if (isset($_SESSION['socioeconomico_temp'])) {
                // Mesclar: dados da sessão primeiro, depois dados do POST (POST sobrescreve)
                $sessionData = $_SESSION['socioeconomico_temp'];
                $data = array_merge($sessionData, $data);
            }
            
            // Debug: Log dos dados recebidos
            error_log('=== SOCIOECONOMICO STORE (FINALIZAR) ===');
            error_log('ID recebido: ' . ($data['id'] ?? 'NENHUM'));
            error_log('Dados: ' . print_r($data, true));
            
            // Limpar sessão temporária após usar
            if (isset($_SESSION['socioeconomico_temp'])) {
                unset($_SESSION['socioeconomico_temp']);
            }
            
            // Verificar se é edição ou criação
            if (!empty($data['id'])) {
                // Edição
                $id = $data['id'];
                unset($data['id']); // Remover ID dos dados
                error_log('EDITANDO ficha ID: ' . $id);
                $ficha = $this->socioeconomicoService->updateFicha($id, $data);
                $this->redirectWithSuccess('socioeconomico_list.php', 'Ficha socioeconômica atualizada com sucesso!');
                return;
            } else {
                // Criação
                error_log('CRIANDO nova ficha');
                $ficha = $this->socioeconomicoService->createFicha($data);
                $this->redirectWithSuccess('socioeconomico_list.php', 'Ficha socioeconômica cadastrada com sucesso!');
                return;
            }
            
        } catch (Exception $e) {
            error_log('ERRO: ' . $e->getMessage());
            // Preservar sessão temporária em caso de erro
            $id = $_POST['id'] ?? null;
            $url = 'socioeconomico_form.php';
            if ($id) {
                $url .= '?id=' . urlencode($id);
            }
            $this->redirectWithError($url, $e->getMessage());
        }
    }
    
    /**
     * Exibe ficha específica
     */
    public function show($id) {
        $this->requireAuth();
        
        try {
            $ficha = $this->socioeconomicoService->getFicha($id);
            
            $data = [
                'title' => 'Visualizar Ficha Socioeconômica',
                'ficha' => $ficha
            ];
            
            $this->renderWithLayout('main', 'socioeconomico/show', $data);
            
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * Exibe formulário de edição
     */
    public function edit($id) {
        $this->requireAuth();
        
        try {
            $ficha = $this->socioeconomicoService->getFicha($id);
            
            $data = [
                'title' => 'Editar Ficha Socioeconômica',
                'ficha' => $ficha,
                'csrf_token' => $this->generateCSRF(),
                'messages' => $this->getFlashMessages()
            ];
            
            $this->renderWithLayout('main', 'socioeconomico/edit', $data);
            
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * Processa atualização da ficha
     */
    public function update($id) {
        $this->requireAuth();
        
        if (!$this->isPost()) {
            redirect("socioeconomico_view.php?id=$id");
        }
        
        try {
            $this->validateCSRF();
            
            $data = $this->getPostData();
            
            $ficha = $this->socioeconomicoService->updateFicha($id, $data);
            
            $this->redirectWithSuccess('socioeconomico_list.php', 'Ficha socioeconômica atualizada com sucesso!');
            
        } catch (Exception $e) {
            $this->redirectWithError("socioeconomico_view.php?id=$id", $e->getMessage());
        }
    }
    
    /**
     * Exclui ficha
     */
    public function delete($id) {
        $this->requireAuth();
        
        if (!$this->isPost()) {
            $this->json(['error' => 'Método não permitido'], 405);
        }
        
        try {
            $this->validateCSRF();
            
            $result = $this->socioeconomicoService->deleteFicha($id);
            
            if ($this->isAjaxRequest()) {
                $this->json(['success' => 'Ficha excluída com sucesso']);
            } else {
                $this->redirectWithSuccess('socioeconomico_list.php', 'Ficha excluída com sucesso!');
            }
            
        } catch (Exception $e) {
            if ($this->isAjaxRequest()) {
                $this->json(['error' => $e->getMessage()], 400);
            } else {
                $this->redirectWithError('socioeconomico_list.php', $e->getMessage());
            }
        }
    }
    
    /**
     * Busca fichas
     */
    public function search() {
        $this->requireAuth();
        
        try {
            $query = $this->getParam('q', '');
            $filters = $this->getGetData();
            
            if (empty($query)) {
                $this->json([]);
                return;
            }
            
            $results = $this->socioeconomicoService->searchFichas($query, $filters);
            
            // Formatar resultados para JSON
            $formattedResults = [];
            foreach ($results as $ficha) {
                $formattedResults[] = [
                    'id' => $ficha['id'],
                    'nome_completo' => $ficha['nome_completo'] ?? '',
                    'cpf' => $this->formatCPF($ficha['cpf'] ?? ''),
                    'rg' => $this->formatRG($ficha['rg'] ?? ''),
                    'idade' => $ficha['idade'],
                    'renda_familiar' => number_format($ficha['renda_familiar'], 2, ',', '.'),
                    'situacao_economica' => $ficha['situacao_economica'],
                    'numero_membros' => $ficha['numero_membros'] ?? 1,
                    'status' => $ficha['status'] ?? 'Ativo'
                ];
            }
            
            $this->json($formattedResults);
            
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Exporta fichas para CSV
     */
    public function export() {
        $this->requireAuth();
        
        try {
            $filters = $this->getGetData();
            $csv = $this->socioeconomicoService->exportToCSV($filters);
            
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="fichas_socioeconomico_' . date('Y-m-d') . '.csv"');
            
            echo "\xEF\xBB\xBF"; // BOM para UTF-8
            echo $csv;
            exit;
            
        } catch (Exception $e) {
            $this->redirectWithError('socioeconomico_list.php', $e->getMessage());
        }
    }
    
    /**
     * Obtém estatísticas
     */
    public function stats() {
        $this->requireAuth();
        
        try {
            $stats = $this->socioeconomicoService->getStatistics();
            $this->json($stats);
            
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Gera relatório socioeconômico
     */
    public function report() {
        $this->requireAuth();
        
        try {
            $filters = $this->getGetData();
            $report = $this->socioeconomicoService->generateReport($filters);
            
            if ($this->isAjaxRequest()) {
                $this->json($report);
            } else {
                $data = [
                    'title' => 'Relatório Socioeconômico',
                    'report' => $report,
                    'filters' => $filters
                ];
                
                $this->renderWithLayout('main', 'socioeconomico/report', $data);
            }
            
        } catch (Exception $e) {
            if ($this->isAjaxRequest()) {
                $this->json(['error' => $e->getMessage()], 500);
            } else {
                $this->handleException($e);
            }
        }
    }
    
    /**
     * Calcula idade
     */
    private function calculateAge($dataNascimento) {
        if (empty($dataNascimento)) {
            return null;
        }
        
        $parts = explode('/', $dataNascimento);
        if (count($parts) === 3) {
            $date = DateTime::createFromFormat('d/m/Y', $dataNascimento);
            if ($date) {
                $now = new DateTime();
                return $now->diff($date)->y;
            }
        }
        
        return null;
    }
    
    /**
     * Calcula renda familiar
     */
    private function calculateRendaFamiliar($data) {
        $renda = 0;
        
        // Tentar calcular a partir do JSON da família
        if (!empty($data['familia_json'])) {
            $familia = json_decode($data['familia_json'], true);
            if (is_array($familia)) {
                foreach ($familia as $membro) {
                    if (!empty($membro['renda'])) {
                        $renda += floatval(str_replace(['.', ','], ['', '.'], $membro['renda']));
                    }
                }
            }
        }
        
        // Fallback: tentar calcular a partir de campos individuais
        if ($renda == 0) {
            for ($i = 1; $i <= 10; $i++) {
                $rendaMembro = $data["renda_membro_$i"] ?? 0;
                $renda += floatval(str_replace(['.', ','], ['', '.'], $rendaMembro));
            }
        }
        
        return $renda;
    }
    
    /**
     * Categoriza situação econômica
     */
    private function categorizeSituacao($rendaFamiliar, $numeroMembros = 1) {
        $rendaPerCapita = $rendaFamiliar / max($numeroMembros, 1);
        $salarioMinimo = 1320;
        
        if ($rendaPerCapita < $salarioMinimo * 0.5) {
            return 'Extrema Pobreza';
        } elseif ($rendaPerCapita < $salarioMinimo) {
            return 'Pobreza';
        } elseif ($rendaPerCapita < $salarioMinimo * 3) {
            return 'Baixa Renda';
        } elseif ($rendaPerCapita < $salarioMinimo * 6) {
            return 'Média Renda';
        } else {
            return 'Alta Renda';
        }
    }
    
    /**
     * Formata CPF
     */
    private function formatCPF($cpf) {
        $cpf = preg_replace('/\D/', '', $cpf);
        if (strlen($cpf) === 11) {
            return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
        }
        return $cpf;
    }
    
    /**
     * Formata RG
     */
    private function formatRG($rg) {
        $rg = preg_replace('/\D/', '', $rg);
        if (strlen($rg) === 9) {
            return substr($rg, 0, 2) . '.' . substr($rg, 2, 3) . '.' . substr($rg, 5, 3) . '-' . substr($rg, 8, 1);
        }
        return $rg;
    }
}
