<?php

/**
 * Controller para o dashboard
 */
class DashboardController extends BaseController {
    private $acolhimentoService;
    private $socioeconomicoService;
    
    public function __construct() {
        parent::__construct();
        $this->acolhimentoService = new AcolhimentoService();
        $this->socioeconomicoService = new SocioeconomicoService();
    }
    
    /**
     * Exibe o dashboard principal
     */
    public function index() {
        $this->requireAuth();
        
        try {
            // Obter estatísticas
            $statsAcolhimento = $this->acolhimentoService->getStatistics();
            $statsSocioeconomico = $this->socioeconomicoService->getStatistics();
            
            // Obter alertas
            $alertas = $this->getAlertas();
            
            // Obter anotações do calendário
            $anotacoes = $this->getAnotacoesCalendario();
            
            $data = [
                'title' => 'Dashboard - Associação Criança Feliz',
                'userName' => $_SESSION['user_name'] ?? 'Usuário',
                'userEmail' => $_SESSION['user_email'] ?? '',
                'userRole' => $_SESSION['user_role'] ?? 'user',
                'statsAcolhimento' => $statsAcolhimento,
                'statsSocioeconomico' => $statsSocioeconomico,
                'alertas' => $alertas,
                'anotacoes' => $anotacoes,
                'messages' => $this->getFlashMessages()
            ];
            
            $this->renderWithLayout('main', 'dashboard/index', $data);
            
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * API para obter estatísticas
     */
    public function getStats() {
        $this->requireAuth();
        
        try {
            $statsAcolhimento = $this->acolhimentoService->getStatistics();
            $statsSocioeconomico = $this->socioeconomicoService->getStatistics();
            
            $stats = [
                'acolhimento' => $statsAcolhimento,
                'socioeconomico' => $statsSocioeconomico,
                'totais' => [
                    'fichas_ativas' => $statsAcolhimento['ativas'] + $statsSocioeconomico['ativas'],
                    'fichas_inativas' => $statsAcolhimento['inativas'] + $statsSocioeconomico['inativas'],
                    'total_geral' => $statsAcolhimento['total'] + $statsSocioeconomico['total']
                ]
            ];
            
            $this->json($stats);
            
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Salva anotação do calendário
     */
    public function saveCalendarNote() {
        $this->requireAuth();
        
        if (!$this->isPost()) {
            $this->json(['error' => 'Método não permitido'], 405);
        }
        
        try {
            $date = $this->getParam('date', '');
            $note = $this->getParam('note', '');
            
            if (empty($date)) {
                throw new Exception('Data é obrigatória');
            }
            
            if (empty($note)) {
                throw new Exception('Anotação é obrigatória');
            }
            
            // Validar formato da data
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                throw new Exception('Formato de data inválido');
            }
            
            $this->saveNote($date, $note);
            
            $this->json(['success' => 'Anotação salva com sucesso']);
            
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 400);
        }
    }
    
    /**
     * Remove anotação do calendário
     */
    public function deleteCalendarNote() {
        $this->requireAuth();
        
        if (!$this->isPost()) {
            $this->json(['error' => 'Método não permitido'], 405);
        }
        
        try {
            $date = $this->getParam('date', '');
            
            if (empty($date)) {
                throw new Exception('Data é obrigatória');
            }
            
            $this->deleteNote($date);
            
            $this->json(['success' => 'Anotação removida com sucesso']);
            
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 400);
        }
    }
    
    /**
     * Obtém anotações do calendário
     */
    public function getCalendarNotes() {
        $this->requireAuth();
        
        try {
            $month = $this->getParam('month', date('Y-m'));
            $notes = $this->getAnotacoesPorMes($month);
            
            $this->json($notes);
            
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Obtém alertas do sistema
     */
    private function getAlertas() {
        $alertas = [];
        
        try {
            // Alertas de fichas incompletas
            $acolhimentos = $this->acolhimentoService->listFichas(1, 100);
            $socioeconomicos = $this->socioeconomicoService->listFichas(1, 100);
            
            $fichasIncompletas = 0;
            $fichasVencidas = 0;
            
            // Verificar fichas de acolhimento
            foreach ($acolhimentos['data'] as $ficha) {
                if (empty($ficha['cpf']) || empty($ficha['nome_completo'])) {
                    $fichasIncompletas++;
                }
                
                // Verificar se ficha tem mais de 6 meses
                if (!empty($ficha['data_acolhimento'])) {
                    $dataAcolhimento = DateTime::createFromFormat('d/m/Y', $ficha['data_acolhimento']);
                    if ($dataAcolhimento && $dataAcolhimento->diff(new DateTime())->days > 180) {
                        $fichasVencidas++;
                    }
                }
            }
            
            if ($fichasIncompletas > 0) {
                $alertas[] = [
                    'tipo' => 'warning',
                    'titulo' => 'Fichas Incompletas',
                    'mensagem' => "$fichasIncompletas ficha(s) com dados incompletos",
                    'icone' => '⚠️'
                ];
            }
            
            if ($fichasVencidas > 0) {
                $alertas[] = [
                    'tipo' => 'info',
                    'titulo' => 'Fichas para Revisão',
                    'mensagem' => "$fichasVencidas ficha(s) com mais de 6 meses",
                    'icone' => '📅'
                ];
            }
            
            // Alertas de sistema
            $alertas[] = [
                'tipo' => 'success',
                'titulo' => 'Sistema Funcionando',
                'mensagem' => 'Todas as funcionalidades operacionais',
                'icone' => '✅'
            ];
            
        } catch (Exception $e) {
            $alertas[] = [
                'tipo' => 'error',
                'titulo' => 'Erro no Sistema',
                'mensagem' => 'Erro ao carregar alertas: ' . $e->getMessage(),
                'icone' => '❌'
            ];
        }
        
        return $alertas;
    }
    
    /**
     * Obtém anotações do calendário
     */
    private function getAnotacoesCalendario() {
        $notesFile = DATA_PATH . '/calendar_notes.json';
        
        if (!file_exists($notesFile)) {
            return [];
        }
        
        $notes = json_decode(file_get_contents($notesFile), true) ?: [];
        
        // Filtrar anotações do mês atual
        $currentMonth = date('Y-m');
        $monthNotes = [];
        
        foreach ($notes as $date => $note) {
            if (strpos($date, $currentMonth) === 0) {
                $monthNotes[] = [
                    'date' => $date,
                    'note' => $note,
                    'formatted_date' => date('d/m/Y', strtotime($date))
                ];
            }
        }
        
        // Ordenar por data
        usort($monthNotes, function($a, $b) {
            return strcmp($a['date'], $b['date']);
        });
        
        return $monthNotes;
    }
    
    /**
     * Obtém anotações por mês
     */
    private function getAnotacoesPorMes($month) {
        $notesFile = DATA_PATH . '/calendar_notes.json';
        
        if (!file_exists($notesFile)) {
            return [];
        }
        
        $notes = json_decode(file_get_contents($notesFile), true) ?: [];
        
        $monthNotes = [];
        foreach ($notes as $date => $note) {
            if (strpos($date, $month) === 0) {
                $monthNotes[$date] = $note;
            }
        }
        
        return $monthNotes;
    }
    
    /**
     * Salva anotação
     */
    private function saveNote($date, $note) {
        $notesFile = DATA_PATH . '/calendar_notes.json';
        
        if (!file_exists($notesFile)) {
            file_put_contents($notesFile, json_encode([]));
        }
        
        $notes = json_decode(file_get_contents($notesFile), true) ?: [];
        
        if (trim($note) === '') {
            unset($notes[$date]);
        } else {
            $notes[$date] = trim($note);
        }
        
        file_put_contents($notesFile, json_encode($notes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    
    /**
     * Remove anotação
     */
    private function deleteNote($date) {
        $notesFile = DATA_PATH . '/calendar_notes.json';
        
        if (!file_exists($notesFile)) {
            return;
        }
        
        $notes = json_decode(file_get_contents($notesFile), true) ?: [];
        unset($notes[$date]);
        
        file_put_contents($notesFile, json_encode($notes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
