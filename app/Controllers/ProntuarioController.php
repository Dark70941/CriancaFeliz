<?php

/**
 * Controller para prontuários
 */
class ProntuarioController extends BaseController {
    private $acolhimentoService;
    private $socioeconomicoService;
    
    public function __construct() {
        parent::__construct();
        $this->acolhimentoService = new AcolhimentoService();
        $this->socioeconomicoService = new SocioeconomicoService();
    }
    
    /**
     * Lista prontuários
     */
    public function index() {
        $this->requireAuth();
        
        try {
            // Buscar fichas de acolhimento e socioeconômicas
            $acolhimentos = $this->acolhimentoService->listFichas(1, 100);
            $socioeconomicos = $this->socioeconomicoService->listFichas(1, 100);
            
            $data = [
                'title' => 'Prontuários - Associação Criança Feliz',
                'pageTitle' => 'Prontuários',
                'acolhimentos' => $acolhimentos['data'] ?? [],
                'socioeconomicos' => $socioeconomicos['data'] ?? [],
                'messages' => $this->getFlashMessages()
            ];
            
            $this->renderWithLayout('main', 'prontuarios/index', $data);
            
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * Visualiza prontuário específico
     */
    public function show($cpf) {
        $this->requireAuth();
        
        try {
            // Buscar fichas pelo CPF
            $acolhimento = null;
            $socioeconomico = null;
            
            // Buscar ficha de acolhimento
            $acolhimentos = $this->acolhimentoService->listFichas(1, 1000);
            foreach ($acolhimentos['data'] as $ficha) {
                if ($ficha['cpf'] === $cpf) {
                    $acolhimento = $ficha;
                    break;
                }
            }
            
            // Buscar ficha socioeconômica
            $socioeconomicos = $this->socioeconomicoService->listFichas(1, 1000);
            foreach ($socioeconomicos['data'] as $ficha) {
                if ($ficha['cpf'] === $cpf) {
                    $socioeconomico = $ficha;
                    break;
                }
            }
            
            if (!$acolhimento && !$socioeconomico) {
                throw new Exception('Prontuário não encontrado');
            }
            
            $data = [
                'title' => 'Prontuário - ' . ($acolhimento['nome_completo'] ?? $socioeconomico['nome_completo'] ?? 'Não informado'),
                'pageTitle' => 'Prontuário',
                'acolhimento' => $acolhimento,
                'socioeconomico' => $socioeconomico,
                'cpf' => $cpf
            ];
            
            $this->renderWithLayout('main', 'prontuarios/show', $data);
            
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
}
