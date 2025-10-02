<?php

/**
 * Model para anotações psicológicas
 */
class PsychologyNote extends BaseModel {
    
    public function __construct() {
        parent::__construct('psychology_notes.json');
    }
    
    /**
     * Busca anotações por paciente (CPF)
     */
    public function findByPatient($cpf) {
        $notes = [];
        
        foreach ($this->data as $note) {
            if ($note['patient_cpf'] === $cpf) {
                $notes[] = $note;
            }
        }
        
        // Ordenar por data decrescente
        usort($notes, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return $notes;
    }
    
    /**
     * Busca anotações por psicólogo
     */
    public function findByPsychologist($psychologistId) {
        $notes = [];
        
        foreach ($this->data as $note) {
            if ($note['psychologist_id'] === $psychologistId) {
                $notes[] = $note;
            }
        }
        
        // Ordenar por data decrescente
        usort($notes, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return $notes;
    }
    
    /**
     * Busca anotações por tipo
     */
    public function findByType($type) {
        $notes = [];
        
        foreach ($this->data as $note) {
            if ($note['note_type'] === $type) {
                $notes[] = $note;
            }
        }
        
        return $notes;
    }
    
    /**
     * Busca anotações por período
     */
    public function findByDateRange($startDate, $endDate) {
        $notes = [];
        
        foreach ($this->data as $note) {
            $noteDate = date('Y-m-d', strtotime($note['created_at']));
            if ($noteDate >= $startDate && $noteDate <= $endDate) {
                $notes[] = $note;
            }
        }
        
        return $notes;
    }
    
    /**
     * Obtém estatísticas por psicólogo
     */
    public function getStatsByPsychologist($psychologistId) {
        $notes = $this->findByPsychologist($psychologistId);
        
        $stats = [
            'total' => count($notes),
            'this_month' => 0,
            'by_type' => [
                'consulta' => 0,
                'avaliacao' => 0,
                'evolucao' => 0,
                'observacao' => 0
            ],
            'unique_patients' => []
        ];
        
        $currentMonth = date('Y-m');
        
        foreach ($notes as $note) {
            // Contar por mês atual
            if (strpos($note['created_at'], $currentMonth) === 0) {
                $stats['this_month']++;
            }
            
            // Contar por tipo
            if (isset($stats['by_type'][$note['note_type']])) {
                $stats['by_type'][$note['note_type']]++;
            }
            
            // Contar pacientes únicos
            if (!in_array($note['patient_cpf'], $stats['unique_patients'])) {
                $stats['unique_patients'][] = $note['patient_cpf'];
            }
        }
        
        $stats['unique_patients_count'] = count($stats['unique_patients']);
        
        return $stats;
    }
    
    /**
     * Sobrescreve create para adicionar campos padrão
     */
    public function create($data) {
        if (!isset($data['id'])) {
            $data['id'] = uniqid('note_');
        }
        
        if (!isset($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        
        if (!isset($data['updated_at'])) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        
        if (!isset($data['confidential'])) {
            $data['confidential'] = true;
        }
        
        return parent::create($data);
    }
    
    /**
     * Sobrescreve update para atualizar timestamp
     */
    public function update($id, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return parent::update($id, $data);
    }
    
    /**
     * Verifica se psicólogo tem acesso à anotação
     */
    public function canAccess($noteId, $psychologistId) {
        $note = $this->findById($noteId);
        
        if (!$note) {
            return false;
        }
        
        // Apenas o psicólogo que criou pode acessar
        return $note['psychologist_id'] === $psychologistId;
    }
    
    /**
     * Obtém últimas anotações
     */
    public function getRecent($limit = 10) {
        $notes = $this->getAll();
        
        // Ordenar por data decrescente
        usort($notes, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return array_slice($notes, 0, $limit);
    }
    
    /**
     * Busca anotações por termo
     */
    public function search($query, $psychologistId = null) {
        $notes = $this->getAll();
        $results = [];
        
        $query = strtolower($query);
        
        foreach ($notes as $note) {
            // Se especificado psicólogo, filtrar apenas suas anotações
            if ($psychologistId && $note['psychologist_id'] !== $psychologistId) {
                continue;
            }
            
            // Buscar no título, conteúdo e observações
            $searchFields = [
                $note['title'] ?? '',
                $note['content'] ?? '',
                $note['behavior_notes'] ?? '',
                $note['recommendations'] ?? ''
            ];
            
            $found = false;
            foreach ($searchFields as $field) {
                if (strpos(strtolower($field), $query) !== false) {
                    $found = true;
                    break;
                }
            }
            
            if ($found) {
                $results[] = $note;
            }
        }
        
        return $results;
    }
}
