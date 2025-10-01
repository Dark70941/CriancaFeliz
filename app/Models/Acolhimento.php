<?php

/**
 * Model para fichas de acolhimento
 */
class Acolhimento extends BaseModel {
    
    public function __construct() {
        parent::__construct('acolhimento.json');
    }
    
    /**
     * Cria nova ficha de acolhimento com validação
     */
    public function createFicha($data) {
        // Validações obrigatórias
        $required = [
            'nome_completo' => 'Nome completo é obrigatório',
            'rg' => 'RG é obrigatório',
            'cpf' => 'CPF é obrigatório',
            'data_nascimento' => 'Data de nascimento é obrigatória',
            'data_acolhimento' => 'Data de acolhimento é obrigatória',
            'queixa_principal' => 'Queixa principal é obrigatória',
            'endereco' => 'Endereço é obrigatório',
            'numero' => 'Número é obrigatório',
            'cep' => 'CEP é obrigatório',
            'bairro' => 'Bairro é obrigatório',
            'cidade' => 'Cidade é obrigatória',
            'nome_responsavel' => 'Nome do responsável é obrigatório',
            'rg_responsavel' => 'RG do responsável é obrigatório',
            'cpf_responsavel' => 'CPF do responsável é obrigatório',
            'grau_parentesco' => 'Grau de parentesco é obrigatório',
            'contato_1' => 'Contato é obrigatório'
        ];
        
        foreach ($required as $field => $message) {
            if (empty($data[$field])) {
                throw new Exception($message);
            }
        }
        
        // Normalizar dados
        $data = $this->normalizeData($data);
        
        // Verificar se CPF já existe
        if ($this->cpfExists($data['cpf'])) {
            throw new Exception('CPF já cadastrado no sistema');
        }
        
        // Definir status padrão
        $data['status'] = $data['status'] ?? 'Ativo';
        
        return $this->create($data);
    }
    
    /**
     * Atualiza ficha de acolhimento
     */
    public function updateFicha($id, $data) {
        $ficha = $this->findById($id);
        if (!$ficha) {
            throw new Exception('Ficha não encontrada');
        }
        
        // Normalizar dados
        $data = $this->normalizeData($data);
        
        // Verificar se CPF já existe (excluindo o próprio registro)
        if (isset($data['cpf']) && $this->cpfExists($data['cpf'], $id)) {
            throw new Exception('CPF já cadastrado no sistema');
        }
        
        return $this->update($id, $data);
    }
    
    /**
     * Normaliza dados da ficha
     */
    private function normalizeData($data) {
        // Normalizar CPF (apenas números)
        if (isset($data['cpf'])) {
            $data['cpf'] = preg_replace('/\D+/', '', $data['cpf']);
        }
        
        if (isset($data['cpf_responsavel'])) {
            $data['cpf_responsavel'] = preg_replace('/\D+/', '', $data['cpf_responsavel']);
        }
        
        // Normalizar RG (apenas números)
        if (isset($data['rg'])) {
            $data['rg'] = preg_replace('/\D+/', '', $data['rg']);
        }
        
        if (isset($data['rg_responsavel'])) {
            $data['rg_responsavel'] = preg_replace('/\D+/', '', $data['rg_responsavel']);
        }
        
        // Normalizar CEP (apenas números)
        if (isset($data['cep'])) {
            $data['cep'] = preg_replace('/\D+/', '', $data['cep']);
        }
        
        // Normalizar telefones (apenas números)
        if (isset($data['contato_1'])) {
            $data['contato_1'] = preg_replace('/\D+/', '', $data['contato_1']);
        }
        
        return $data;
    }
    
    /**
     * Verifica se CPF já existe
     */
    public function cpfExists($cpf, $excludeId = null) {
        $cpf = preg_replace('/\D+/', '', $cpf);
        
        foreach ($this->data as $record) {
            if ($record['cpf'] === $cpf && $record['id'] !== $excludeId) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Busca por CPF
     */
    public function findByCpf($cpf) {
        $cpf = preg_replace('/\D+/', '', $cpf);
        return $this->findBy('cpf', $cpf);
    }
    
    /**
     * Busca avançada
     */
    public function searchAdvanced($query) {
        $searchFields = [
            'nome_completo',
            'cpf',
            'rg',
            'nome_responsavel',
            'cpf_responsavel',
            'endereco',
            'bairro',
            'cidade'
        ];
        
        return $this->search($query, $searchFields);
    }
    
    /**
     * Calcula idade baseada na data de nascimento
     */
    public function calculateAge($dataNascimento) {
        if (empty($dataNascimento)) {
            return null;
        }
        
        // Converter formato brasileiro para DateTime
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
     * Categoriza por idade
     */
    public function categorizeByAge($age) {
        if ($age === null) return 'Indefinido';
        if ($age < 12) return 'Criança';
        if ($age < 18) return 'Adolescente';
        return 'Adulto';
    }
    
    /**
     * Estatísticas das fichas
     */
    public function getStatistics() {
        $total = count($this->data);
        $ativas = count($this->findBy('status', 'Ativo'));
        $inativas = $total - $ativas;
        
        $categorias = [
            'Criança' => 0,
            'Adolescente' => 0,
            'Adulto' => 0,
            'Indefinido' => 0
        ];
        
        foreach ($this->data as $record) {
            $age = $this->calculateAge($record['data_nascimento'] ?? '');
            $categoria = $this->categorizeByAge($age);
            $categorias[$categoria]++;
        }
        
        return [
            'total' => $total,
            'ativas' => $ativas,
            'inativas' => $inativas,
            'categorias' => $categorias
        ];
    }
}
