<?php

/**
 * Service para área psicológica
 */
class PsychologyService {
    private $psychologyModel;
    private $acolhimentoModel;
    
    public function __construct() {
        $this->psychologyModel = new PsychologyNote();
        $this->acolhimentoModel = new Acolhimento();
    }
    
    /**
     * Obtém estatísticas da área psicológica
     */
    public function getStatistics() {
        $notes = $this->psychologyModel->getAll();
        $patients = $this->getAllPatients();
        
        $stats = [
            'total_patients' => count($patients),
            'total_notes' => count($notes),
            'notes_this_month' => 0,
            'active_treatments' => 0,
            'by_age_group' => [
                'crianca' => 0,      // 0-11 anos
                'adolescente' => 0,  // 12-17 anos
                'adulto' => 0        // 18+ anos
            ],
            'by_note_type' => [
                'consulta' => 0,
                'avaliacao' => 0,
                'evolucao' => 0,
                'observacao' => 0
            ]
        ];
        
        // Contar anotações do mês atual
        $currentMonth = date('Y-m');
        foreach ($notes as $note) {
            if (strpos($note['created_at'], $currentMonth) === 0) {
                $stats['notes_this_month']++;
            }
            
            // Contar por tipo
            if (isset($stats['by_note_type'][$note['note_type']])) {
                $stats['by_note_type'][$note['note_type']]++;
            }
        }
        
        // Contar por faixa etária
        foreach ($patients as $patient) {
            $age = $this->calculateAge($patient['data_nascimento']);
            if ($age < 12) {
                $stats['by_age_group']['crianca']++;
            } elseif ($age < 18) {
                $stats['by_age_group']['adolescente']++;
            } else {
                $stats['by_age_group']['adulto']++;
            }
            
            // Verificar se tem acompanhamento ativo (anotação nos últimos 30 dias)
            $hasRecentNote = false;
            $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));
            foreach ($notes as $note) {
                if ($note['patient_cpf'] === $patient['cpf'] && $note['created_at'] >= $thirtyDaysAgo) {
                    $hasRecentNote = true;
                    break;
                }
            }
            if ($hasRecentNote) {
                $stats['active_treatments']++;
            }
        }
        
        return $stats;
    }
    
    /**
     * Obtém todos os pacientes (crianças com fichas de acolhimento)
     */
    public function getAllPatients() {
        try {
            // Obter todas as fichas de acolhimento
            $fichas = $this->acolhimentoModel->getAll();
            
            error_log("Número total de fichas encontradas: " . count($fichas));
            
            $patients = [];
            
            foreach ($fichas as $ficha) {
                // Verificar se o registro tem um ID de atendido (indicando que está ativo)
                if (isset($ficha['idatendido']) && $ficha['idatendido']) {
                    $patients[] = [
                        'cpf' => $ficha['cpf'] ?? 'Não informado',
                        'nome_completo' => $ficha['nome'] ?? 'Não informado',
                        'data_nascimento' => $ficha['data_nascimento'] ?? '',
                        'idade' => isset($ficha['data_nascimento']) ? $this->calculateAge($ficha['data_nascimento']) : 0,
                        'responsavel' => $ficha['nome_responsavel'] ?? 'Não informado',
                        'data_acolhimento' => $ficha['data_cadastro'] ?? '',
                        'last_note' => $this->getLastNoteDate($ficha['cpf'] ?? '')
                    ];
                }
            }
            
            error_log("Número de pacientes processados: " . count($patients));
            
            // Ordenar por nome
            usort($patients, function($a, $b) {
                return strcmp($a['nome_completo'], $b['nome_completo']);
            });
            
            return $patients;
            
        } catch (Exception $e) {
            error_log("Erro ao buscar pacientes: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtém paciente específico
     */
   public function getPatient($cpf) {
    try {
        error_log("Buscando paciente com CPF: " . $cpf);
        
        // Busca o atendido pelo CPF
        $atendido = $this->acolhimentoModel->findByCpf($cpf);
        
        if (!$atendido) {
            error_log("Paciente não encontrado com CPF: " . $cpf);
            
            // Tenta buscar qualquer paciente para depuração
            $sql = "SELECT * FROM Atendido WHERE cpf IS NOT NULL LIMIT 1";
            $stmt = $this->acolhimentoModel->query($sql);
            $teste = $stmt->fetch();
            error_log("Teste de busca: " . ($teste ? "Encontrado" : "Nenhum registro"));
            
            return null;
        }
        
        // Formata os dados para retorno
        return [
            'cpf' => $atendido['cpf'] ?? 'Não informado',
            'nome_completo' => $atendido['nome'] ?? 'Não informado',
            'data_nascimento' => $atendido['data_nascimento'] ?? '',
            'idade' => isset($atendido['data_nascimento']) ? $this->calculateAge($atendido['data_nascimento']) : 0,
            'responsavel' => $atendido['nome_responsavel'] ?? 'Não informado',
            'contato' => $atendido['contato_1'] ?? 'Não informado',
            'endereco' => $this->formatAddress($atendido),
            'data_acolhimento' => $atendido['data_cadastro'] ?? '',
            'queixa_principal' => $atendido['queixa_principal'] ?? 'Não informado',
            'encaminhado_por' => $atendido['encaminha_por'] ?? 'Não informado'
        ];
        
    } catch (Exception $e) {
        error_log("Erro ao buscar paciente: " . $e->getMessage());
        return null;
    }
}
    
    /**
     * Obtém anotações de um paciente
     */
    public function getPatientNotes($cpf) {
        return $this->psychologyModel->findByPatient($cpf);
    }
    
    /**
     * Obtém avaliações de um paciente
     */
    public function getPatientAssessments($cpf) {
        // Por enquanto, retorna as anotações do tipo 'avaliacao'
        $notes = $this->psychologyModel->findByPatient($cpf);
        $assessments = [];
        
        foreach ($notes as $note) {
            if ($note['note_type'] === 'avaliacao') {
                $assessments[] = $note;
            }
        }
        
        return $assessments;
    }
    
    /**
     * Obtém uma anotação específica
     */
    public function getNote($id) {
        $notes = $this->psychologyModel->getAll();
        
        foreach ($notes as $note) {
            if ($note['id'] === $id) {
                return $note;
            }
        }
        
        return null;
    }
    
    /**
     * Atualiza uma anotação
     */
    public function updateNote($id, $data) {
        // Preparar dados
        $noteData = [
            'patient_cpf' => sanitizeInput($data['patient_cpf']),
            'psychologist_id' => $_SESSION['user_id'],
            'psychologist_name' => $_SESSION['user_name'],
            'note_type' => sanitizeInput($data['note_type']),
            'title' => sanitizeInput($data['title'] ?? ''),
            'content' => sanitizeInput($data['content']),
            'mood_assessment' => $data['mood_assessment'] ?? '',
            'behavior_notes' => sanitizeInput($data['behavior_notes'] ?? ''),
            'recommendations' => sanitizeInput($data['recommendations'] ?? ''),
            'next_session' => $data['next_session'] ?? '',
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $this->psychologyModel->update($id, $noteData);
        
        return $id;
    }
    
    /**
     * Obtém anotações recentes
     */
    public function getRecentNotes($limit = 10) {
        $notes = $this->psychologyModel->getAll();
        
        // Ordenar por data decrescente
        usort($notes, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return array_slice($notes, 0, $limit);
    }
    
    /**
     * Salva anotação psicológica
     */
 public function saveNote($data) {
    try {
        // Log inicial detalhado
        error_log("=== INÍCIO DO SAVE NOTE ===");
        error_log("Dados recebidos: " . print_r($data, true));
        
        // Garante que o id_psicologo está presente
        if (empty($_SESSION['user_id'])) {
            $errorMsg = "Usuário não autenticado";
            error_log($errorMsg);
            return ['success' => false, 'message' => $errorMsg];
        }

        // Validação dos dados obrigatórios
        $required = ['id_atendido', 'tipo', 'conteudo'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $errorMsg = "Campo obrigatório faltando: " . $field;
                error_log($errorMsg);
                return ['success' => false, 'message' => $errorMsg];
            }
        }

        // Prepara os dados para inserção
        $noteData = [
            'id_atendido' => (int)$data['id_atendido'],
            'id_psicologo' => (int)$_SESSION['user_id'], // Usa o ID do usuário logado
            'tipo' => $data['tipo'],
            'conteudo' => $data['conteudo'],
            'data_anotacao' => $data['data_anotacao'] ?? date('Y-m-d H:i:s'),
            'titulo' => $data['titulo'] ?? 'Sem título',
            'humor' => isset($data['humor']) ? (int)$data['humor'] : null,
            'observacoes_comportamentais' => $data['observacoes_comportamentais'] ?? null,
            'recomendacoes' => $data['recomendacoes'] ?? null,
            'proxima_sessao' => !empty($data['proxima_sessao']) ? $data['proxima_sessao'] : null
        ];

        error_log("Dados preparados para inserção: " . print_r($noteData, true));

        // Tenta inserir
        $result = $this->acolhimentoModel->insert('anotacao_psicologica', $noteData);
        
        if (!$result) {
            $error = "Falha ao inserir no banco de dados";
            error_log($error);
            $errorInfo = $this->acolhimentoModel->getLastError();
            error_log("Erro do banco: " . print_r($errorInfo, true));
            return ['success' => false, 'message' => $error . ': ' . print_r($errorInfo, true)];
        }

        error_log("Anotação salva com sucesso! ID: " . $result);
        return ['success' => true, 'message' => 'Anotação salva com sucesso!', 'id' => $result];

    } catch (Exception $e) {
        $errorMsg = "Erro ao salvar anotação: " . $e->getMessage() . "\n" . $e->getTraceAsString();
        error_log($errorMsg);
        return ['success' => false, 'message' => 'Erro ao salvar anotação: ' . $e->getMessage()];
    }
}
    /**
     * Exclui anotação
     */
    public function deleteNote($id) {
        return $this->psychologyModel->delete($id);
    }
    
    /**
     * Busca pacientes
     */
    public function searchPatients($query) {
        if (empty($query)) {
            return [];
        }
        
        $patients = $this->getAllPatients();
        $results = [];
        
        $query = strtolower($query);
        
        foreach ($patients as $patient) {
            if (strpos(strtolower($patient['nome_completo']), $query) !== false ||
                strpos($patient['cpf'], $query) !== false) {
                $results[] = $patient;
            }
        }
        
        return $results;
    }
    
    /**
     * Calcula idade
     */
    private function calculateAge($birthDate) {
        if (empty($birthDate)) {
            return 0;
        }
        
        // Tentar diferentes formatos de data
        $formats = ['d/m/Y', 'Y-m-d', 'd-m-Y'];
        $birth = null;
        
        foreach ($formats as $format) {
            $birth = DateTime::createFromFormat($format, $birthDate);
            if ($birth !== false) {
                break;
            }
        }
        
        if (!$birth) {
            return 0;
        }
        
        $today = new DateTime();
        return $today->diff($birth)->y;
    }
    
    /**
     * Formata endereço
     */
    private function formatAddress($ficha) {
        $parts = [];
        
        if (!empty($ficha['endereco'])) {
            $parts[] = $ficha['endereco'];
        }
        if (!empty($ficha['numero'])) {
            $parts[] = 'nº ' . $ficha['numero'];
        }
        if (!empty($ficha['bairro'])) {
            $parts[] = $ficha['bairro'];
        }
        if (!empty($ficha['cidade'])) {
            $parts[] = $ficha['cidade'];
        }
        
        return !empty($parts) ? implode(', ', $parts) : 'Não informado';
    }
    
    /**
     * Obtém data da última anotação
     */
    private function getLastNoteDate($cpf) {
        if (empty($cpf)) {
            return null;
        }
        
        $notes = $this->psychologyModel->findByPatient($cpf);
        
        if (empty($notes)) {
            return null;
        }
        
        // Ordenar por data decrescente
        usort($notes, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return $notes[0]['created_at'];
    }
    
    /**
     * Obtém tipos de anotação disponíveis
     */
    public function getNoteTypes() {
        return [
            'consulta' => [
                'name' => 'Consulta',
                'description' => 'Sessão de atendimento psicológico',
                'icon' => '💬'
            ],
            'avaliacao' => [
                'name' => 'Avaliação',
                'description' => 'Avaliação psicológica inicial ou periódica',
                'icon' => '📋'
            ],
            'evolucao' => [
                'name' => 'Evolução',
                'description' => 'Acompanhamento da evolução do paciente',
                'icon' => '📈'
            ],
            'observacao' => [
                'name' => 'Observação',
                'description' => 'Observações comportamentais e clínicas',
                'icon' => '👁️'
            ]
        ];
    }
    
    /**
     * Obtém escalas de humor
     */
    public function getMoodScales() {
        return [
            1 => ['label' => 'Muito Triste', 'color' => '#dc3545'],
            2 => ['label' => 'Triste', 'color' => '#fd7e14'],
            3 => ['label' => 'Neutro', 'color' => '#ffc107'],
            4 => ['label' => 'Alegre', 'color' => '#20c997'],
            5 => ['label' => 'Muito Alegre', 'color' => '#28a745']
        ];
    }
}
