<?php

/**
 * Controller para área psicológica (exclusiva para psicólogos)
 */
class PsychologyController extends BaseController {
    private $psychologyService;
    
    public function __construct() {
        parent::__construct();
        $this->psychologyService = new PsychologyService();
    }
    
    /**
     * Dashboard da área psicológica
     */
    public function index() {
        $this->requireAuth();
        $this->requirePermission('view_psychological_area');
        
        try {
            $stats = $this->psychologyService->getStatistics();
            $recentNotes = $this->psychologyService->getRecentNotes(5);
            
            $data = [
                'title' => 'Área Psicológica',
                'pageTitle' => 'Área Psicológica - Dashboard',
                'stats' => $stats,
                'recentNotes' => $recentNotes,
                'messages' => $this->getFlashMessages()
            ];
            
            $this->renderWithLayout('main', 'psychology/index', $data);
            
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * Lista crianças para acompanhamento psicológico
     */
    public function patients() {
        $this->requireAuth();
        $this->requirePermission('view_psychological_area');
        
        try {
            $patients = $this->psychologyService->getAllPatients();
            
            $data = [
                'title' => 'Pacientes',
                'pageTitle' => 'Acompanhamento Psicológico',
                'patients' => $patients,
                'messages' => $this->getFlashMessages()
            ];
            
            $this->renderWithLayout('main', 'psychology/patients', $data);
            
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * Visualiza prontuário psicológico de uma criança
     */
    public function patient($cpf) {
        $this->requireAuth();
        $this->requirePermission('view_psychological_area');
        
        try {
            error_log("Buscando paciente com CPF: " . $cpf);
            
            $patient = $this->psychologyService->getPatient($cpf);
            
            if (!$patient) {
                error_log("Paciente não encontrado no serviço para o CPF: " . $cpf);
                throw new Exception('Paciente não encontrado');
            }
            
            error_log("Paciente encontrado: " . $patient['nome_completo']);
            
            $notes = $this->psychologyService->getPatientNotes($cpf);
            error_log("Total de anotações encontradas: " . count($notes));
            
            $assessments = $this->psychologyService->getPatientAssessments($cpf);
            error_log("Total de avaliações encontradas: " . count($assessments));
            
            $data = [
                'title' => 'Prontuário Psicológico',
                'pageTitle' => 'Prontuário Psicológico - ' . $patient['nome_completo'],
                'patient' => $patient,
                'notes' => $notes,
                'assessments' => $assessments,
                'csrf_token' => $this->generateCSRF(),
                'messages' => $this->getFlashMessages()
            ];
            
            $this->renderWithLayout('main', 'psychology/patient', $data);
            
        } catch (Exception $e) {
            error_log("Erro no método patient: " . $e->getMessage());
            $this->handleException($e);
        }
    }
    
    /**
     * Salva anotação psicológica
     */
 public function saveNote() {
    $this->requireAuth();
    $this->requirePermission('add_psychological_note');
    
    try {
        // Verifica se é uma requisição POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception('Método não permitido');
        }

        // Valida CSRF
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception('Token CSRF inválido');
        }

        // Obtém os dados do formulário
        $data = [
            'id_anotacao' => $_POST['id_anotacao'] ?? null,
            'id_atendido' => $_POST['id_atendido'] ?? null,
            'id_psicologo' => $_SESSION['user_id'],
            'tipo' => $_POST['tipo'] ?? 'consulta',
            'titulo' => $_POST['titulo'] ?? 'Sem título',
            'conteudo' => $_POST['conteudo'] ?? '',
            'data_anotacao' => $_POST['data_anotacao'] ?? date('Y-m-d H:i:s'),
            'humor' => $_POST['humor'] ?? null,
            'observacoes_comportamentais' => $_POST['observacoes_comportamentais'] ?? null,
            'recomendacoes' => $_POST['recomendacoes'] ?? null,
            'proxima_sessao' => !empty($_POST['proxima_sessao']) ? $_POST['proxima_sessao'] : null
        ];

        // Validação básica
        if (empty($data['id_atendido'])) {
            throw new Exception('ID do paciente não informado');
        }

        if (empty($data['conteudo'])) {
            throw new Exception('O conteúdo da anotação é obrigatório');
        }

        // Salva a anotação
        $result = $this->psychologyService->saveNote($data);

        if ($result['success']) {
            $_SESSION['success_message'] = $result['message'];
        } else {
            throw new Exception($result['message']);
        }

        // Redireciona de volta para a página do paciente
        header('Location: psychology.php?action=patient&cpf=' . urlencode($_POST['cpf_paciente']));
        exit;

    } catch (Exception $e) {
        error_log("Erro no saveNote: " . $e->getMessage());
        $_SESSION['error_message'] = $e->getMessage();
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
}
    
    /**
     * Obtém uma anotação específica
     */
    public function getNote() {
        $this->requireAuth();
        $this->requirePermission('psychological_notes');
        
        try {
            $id = $_GET['id'] ?? null;
            
            if (empty($id)) {
                throw new Exception('ID da anotação é obrigatório');
            }
            
            $note = $this->psychologyService->getNote($id);
            
            if (!$note) {
                throw new Exception('Anotação não encontrada');
            }
            
            $this->json([
                'success' => true,
                'note' => $note
            ]);
            
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 400);
        }
    }
    
    /**
     * Salva avaliação psicológica
     */
    public function saveAssessment() {
        $this->requireAuth();
        $this->requirePermission('psychological_notes');
        
        if (!$this->isPost()) {
            $this->json(['error' => 'Método não permitido'], 405);
        }
        
        try {
            $this->validateCSRF();
            $data = $this->getPostData();
            
            // Validações
            if (empty($data['patient_cpf'])) {
                throw new Exception('CPF do paciente é obrigatório');
            }
            
            if (empty($data['assessment_type'])) {
                throw new Exception('Tipo de avaliação é obrigatório');
            }
            
            $assessmentId = $this->psychologyService->saveAssessment($data);
            
            $this->json([
                'success' => true,
                'message' => 'Avaliação salva com sucesso',
                'assessment_id' => $assessmentId
            ]);
            
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 400);
        }
    }
    
    /**
     * Exclui anotação psicológica
     */
    public function deleteNote($id) {
        $this->requireAuth();
        $this->requirePermission('psychological_notes');
        
        if (!$this->isPost()) {
            $this->json(['error' => 'Método não permitido'], 405);
        }
        
        try {
            $currentUser = $this->authService->getCurrentUser();
            
            // Verificar se a anotação pertence ao psicólogo atual
            $note = $this->psychologyService->getNote($id);
            if (!$note || $note['psychologist_id'] !== $currentUser['id']) {
                throw new Exception('Anotação não encontrada ou sem permissão');
            }
            
            $this->psychologyService->deleteNote($id);
            
            $this->json([
                'success' => true,
                'message' => 'Anotação excluída com sucesso'
            ]);
            
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 400);
        }
    }
    
    /**
     * Busca pacientes
     */
    public function search() {
        $this->requireAuth();
        $this->requirePermission('view_psychological_area');
        
        try {
            $query = $this->getParam('q', '');
            $results = $this->psychologyService->searchPatients($query);
            
            $this->json([
                'success' => true,
                'results' => $results
            ]);
            
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 400);
        }
    }
    
    /**
     * Relatório psicológico
     */
    public function report() {
        $this->requireAuth();
        $this->requirePermission('view_psychological_area');
        
        try {
            $filters = $this->getGetData();
            $report = $this->psychologyService->generateReport($filters);
            
            $data = [
                'title' => 'Relatório Psicológico',
                'pageTitle' => 'Relatório de Acompanhamento Psicológico',
                'report' => $report,
                'filters' => $filters,
                'messages' => $this->getFlashMessages()
            ];
            
            $this->renderWithLayout('main', 'psychology/report', $data);
            
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
}
