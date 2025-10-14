<?php

/**
 * Model para fichas de acolhimento - MYSQL
 */
class Acolhimento extends BaseModelDB {
    
    public function __construct() {
        parent::__construct('Atendido', 'idatendido');
    }
    
    /**
     * Cria nova ficha de acolhimento com validação
     */
    public function createFicha($data) {
        try {
            Database::beginTransaction();
            
            // Normalizar dados
            $data = $this->normalizeData($data);
            
            // 1. Criar/Buscar Responsável
            $responsavelId = $this->createOrGetResponsavel($data);
            
            // 2. Criar Atendido
            $atendidoData = [
                'nome' => $data['nome_completo'],
                'cpf' => $data['cpf'],
                'rg' => $data['rg'],
                'data_nascimento' => $this->convertDate($data['data_nascimento']),
                'data_cadastro' => date('Y-m-d'),
                'endereco' => $data['endereco'],
                'numero' => $data['numero'],
                'complemento' => $data['complemento'] ?? null,
                'bairro' => $data['bairro'],
                'cidade' => $data['cidade'],
                'cep' => $data['cep'],
                'foto' => $data['foto'] ?? null,
                'status' => 'Ativo',
                'id_responsavel' => $responsavelId,
                'faixa_etaria' => $this->calculateAge($data['data_nascimento'])
            ];
            
            $atendido = $this->create($atendidoData);
            $atendidoId = $atendido['idatendido'];
            
            // 3. Criar Ficha de Acolhimento
            $fichaData = [
                'id_atendido' => $atendidoId,
                'data_acolhimento' => $this->convertDate($data['data_acolhimento']),
                'encaminha_por' => $data['encaminha_por'] ?? null,
                'queixa_principal' => $data['queixa_principal'],
                'escola' => $data['escola'] ?? null,
                'periodo' => $data['periodo'] ?? null,
                'ponto_referencia' => $data['ponto_referencia'] ?? null,
                'cras' => $data['cras'] ?? null,
                'ubs' => $data['ubs'] ?? null,
                'cad_unico' => $data['cad_unico'] ?? null,
                'acolhimento_responsavel' => $data['acolhimento_responsavel'] ?? null,
                'acolhimento_funcao' => $data['acolhimento_funcao'] ?? null,
                'carimbo' => $data['carimbo'] ?? null
            ];
            
            $this->query(
                "INSERT INTO Ficha_Acolhimento (id_atendido, data_acolhimento, encaminha_por, queixa_principal, escola, periodo, ponto_referencia, cras, ubs, cad_unico, acolhimento_responsavel, acolhimento_funcao, carimbo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                array_values($fichaData)
            );
            
            Database::commit();
            
            return $this->getFicha($atendidoId);
            
        } catch (Exception $e) {
            Database::rollback();
            throw $e;
        }
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
        
        foreach ($this->data as $ficha) {
            $fichaCpf = preg_replace('/\D+/', '', $ficha['cpf'] ?? '');
            if ($fichaCpf === $cpf) {
                return $ficha;
            }
        }
        
        return null;
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
