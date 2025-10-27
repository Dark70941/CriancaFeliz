<?php

/**
 * Controller para Sistema de Desligamento
 */
class DesligamentoController extends BaseController {
    
    private $desligamentoDB;
    private $frequenciaDiaDB;
    
    public function __construct() {
        parent::__construct();
        $this->desligamentoDB = new DesligamentoDB();
        $this->frequenciaDiaDB = new FrequenciaDiaDB();
    }
    
    /**
     * Listar desligamentos
     */
    public function index() {
        $this->requireAuth();
        
        try {
            $filtros = [
                'tipo_motivo' => $this->getParam('tipo_motivo', '')
            ];
            
            $desligamentos = $this->desligamentoDB->listar($filtros);
            $estatisticas = $this->desligamentoDB->getEstatisticas();
            
            $viewData = [
                'title' => 'Desligamentos',
                'pageTitle' => 'Gerenciar Desligamentos',
                'desligamentos' => $desligamentos,
                'estatisticas' => $estatisticas,
                'filtros' => $filtros,
                'csrf_token' => $this->generateCSRF(),
                'messages' => $this->getFlashMessages()
            ];
            
            $this->renderWithLayout('main', 'desligamento/index', $viewData);
            
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * Formulário de desligamento
     */
    public function novo($id = null) {
        $this->requireAuth();
        $this->requirePermission('manage_users');
        
        try {
            // Se não veio ID pela função, pega da URL
            if ($id === null) {
                $id = $this->getParam('id', '');
            }
            
            // Se ainda não tem ID, mostrar lista de atendidos para escolher
            if (empty($id)) {
                $this->selecionarAtendido();
                return;
            }
            
            $acolhimentoModel = new AcolhimentoDB();
            $atendido = $acolhimentoModel->findById($id);
            
            if (!$atendido) {
                throw new Exception('Atendido não encontrado');
            }
            
            if ($this->desligamentoDB->isDesligado($id)) {
                throw new Exception('Atendido já está desligado');
            }
            
            // Buscar estatísticas de faltas
            $estatsFaltas = $this->frequenciaDiaDB->getEstatisticas($id);
            
            $viewData = [
                'title' => 'Desligar Atendido',
                'pageTitle' => 'Desligamento de Atendido',
                'atendido' => $atendido,
                'estatsFaltas' => $estatsFaltas,
                'csrf_token' => $this->generateCSRF(),
                'messages' => $this->getFlashMessages()
            ];
            
            $this->renderWithLayout('main', 'desligamento/novo', $viewData);
            
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * Salvar desligamento
     */
    public function salvar() {
        $this->requireAuth();
        $this->requirePermission('manage_users');
        
        if (!$this->isPost()) {
            $this->json(['error' => 'Método não permitido'], 405);
        }
        
        try {
            $this->validateCSRF();
            
            $idAtendido = $this->getParam('id_atendido', '');
            $data = [
                'motivo' => $this->getParam('motivo', ''),
                'tipo_motivo' => $this->getParam('tipo_motivo', 'outros'),
                'observacao' => $this->getParam('observacao', ''),
                'pode_retornar' => $this->getParam('pode_retornar', '1') === '1',
                'automatico' => false
            ];
            
            if (empty($idAtendido)) {
                throw new Exception('ID do atendido é obrigatório');
            }
            
            if (empty($data['motivo'])) {
                throw new Exception('Motivo do desligamento é obrigatório');
            }
            
            $this->desligamentoDB->registrarDesligamento($idAtendido, $data);
            
            if ($this->isAjaxRequest()) {
                $this->json(['success' => true, 'message' => 'Atendido desligado com sucesso']);
            } else {
                $this->redirectWithSuccess('desligamento.php', 'Atendido desligado com sucesso!');
            }
            
        } catch (Exception $e) {
            if ($this->isAjaxRequest()) {
                $this->json(['error' => $e->getMessage()], 400);
            } else {
                $this->redirectWithError('desligamento.php', $e->getMessage());
            }
        }
    }
    
    /**
     * Reativar atendido
     */
    public function reativar() {
        $this->requireAuth();
        $this->requirePermission('manage_users');
        
        if (!$this->isPost()) {
            $this->json(['error' => 'Método não permitido'], 405);
        }
        
        try {
            $this->validateCSRF();
            
            $idAtendido = $this->getParam('id_atendido', '');
            
            if (empty($idAtendido)) {
                throw new Exception('ID do atendido é obrigatório');
            }
            
            $this->desligamentoDB->cancelarDesligamento($idAtendido);
            
            if ($this->isAjaxRequest()) {
                $this->json(['success' => true, 'message' => 'Atendido reativado com sucesso']);
            } else {
                $this->redirectWithSuccess('desligamento.php', 'Atendido reativado com sucesso!');
            }
            
        } catch (Exception $e) {
            if ($this->isAjaxRequest()) {
                $this->json(['error' => $e->getMessage()], 400);
            } else {
                $this->redirectWithError('desligamento.php', $e->getMessage());
            }
        }
    }
    
    /**
     * Desligamento automático por excesso de faltas
     */
    public function automatico() {
        $this->requireAuth();
        $this->requirePermission('manage_users');
        
        if (!$this->isPost()) {
            $this->json(['error' => 'Método não permitido'], 405);
        }
        
        try {
            $this->validateCSRF();
            
            $desligados = $this->desligamentoDB->desligarPorExcessoFaltas();
            
            $mensagem = count($desligados) > 0 
                ? count($desligados) . ' atendido(s) desligado(s) automaticamente por excesso de faltas'
                : 'Nenhum atendido para desligar automaticamente';
            
            if ($this->isAjaxRequest()) {
                $this->json(['success' => true, 'message' => $mensagem, 'desligados' => $desligados]);
            } else {
                $this->redirectWithSuccess('desligamento.php', $mensagem);
            }
            
        } catch (Exception $e) {
            if ($this->isAjaxRequest()) {
                $this->json(['error' => $e->getMessage()], 400);
            } else {
                $this->redirectWithError('desligamento.php', $e->getMessage());
            }
        }
    }
    
    /**
     * Selecionar atendido para desligar
     */
    private function selecionarAtendido() {
        try {
            $search = $this->getParam('search', '');
            
            // Buscar atendidos ativos
            $acolhimentoModel = new AcolhimentoDB();
            $atendidos = $acolhimentoModel->findAll();
            
            // Filtrar apenas não desligados
            $atendidosAtivos = [];
            foreach ($atendidos as $atendido) {
                $id = $atendido['idatendido'] ?? $atendido['id'];
                if (!$this->desligamentoDB->isDesligado($id)) {
                    $atendidosAtivos[] = $atendido;
                }
            }
            
            // Filtrar por busca
            if (!empty($search)) {
                $atendidosAtivos = array_filter($atendidosAtivos, function($a) use ($search) {
                    return stripos($a['nome'], $search) !== false || 
                           stripos($a['cpf'] ?? '', $search) !== false;
                });
            }
            
            $viewData = [
                'title' => 'Selecionar Atendido',
                'pageTitle' => 'Selecionar Atendido para Desligar',
                'atendidos' => array_values($atendidosAtivos),
                'search' => $search,
                'messages' => $this->getFlashMessages()
            ];
            
            $this->renderWithLayout('main', 'desligamento/selecionar', $viewData);
            
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
}
