<?php

/**
 * Model para fichas socioeconômicas
 */
class Socioeconomico extends BaseModel {
    
    public function __construct() {
        parent::__construct('socioeconomico.json');
    }
    
    /**
     * Cria nova ficha socioeconômica com validação
     */
    public function createFicha($data) {
        // Validações obrigatórias básicas
        $required = [
            'nome_completo' => 'Nome completo é obrigatório',
            'cpf' => 'CPF é obrigatório',
            'data_nascimento' => 'Data de nascimento é obrigatória'
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
     * Atualiza ficha socioeconômica
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
        
        // Normalizar RG (apenas números)
        if (isset($data['rg'])) {
            $data['rg'] = preg_replace('/\D+/', '', $data['rg']);
        }
        
        // Normalizar CEP (apenas números)
        if (isset($data['cep'])) {
            $data['cep'] = preg_replace('/\D+/', '', $data['cep']);
        }
        
        // Normalizar telefones (apenas números)
        $telefoneFields = ['telefone', 'celular', 'contato_emergencia'];
        foreach ($telefoneFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = preg_replace('/\D+/', '', $data[$field]);
            }
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
            'endereco',
            'bairro',
            'cidade',
            'profissao',
            'empresa'
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
     * Calcula renda familiar total
     */
    public function calculateRendaFamiliar($data) {
        $renda = 0;
        
        // Somar rendas dos membros da família
        for ($i = 1; $i <= 10; $i++) {
            $rendaMembro = $data["renda_membro_$i"] ?? 0;
            $renda += floatval(str_replace(['.', ','], ['', '.'], $rendaMembro));
        }
        
        return $renda;
    }
    
    /**
     * Categoriza situação socioeconômica
     */
    public function categorizeSituacao($rendaFamiliar, $numeroMembros = 1) {
        $rendaPerCapita = $rendaFamiliar / max($numeroMembros, 1);
        $salarioMinimo = 1320; // Valor aproximado
        
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
     * Estatísticas das fichas socioeconômicas
     */
    public function getStatistics() {
        $total = count($this->data);
        $ativas = count($this->findBy('status', 'Ativo'));
        $inativas = $total - $ativas;
        
        $situacoes = [
            'Extrema Pobreza' => 0,
            'Pobreza' => 0,
            'Baixa Renda' => 0,
            'Média Renda' => 0,
            'Alta Renda' => 0
        ];
        
        $rendaTotal = 0;
        $contadorRenda = 0;
        
        foreach ($this->data as $record) {
            $rendaFamiliar = $this->calculateRendaFamiliar($record);
            $numeroMembros = intval($record['numero_membros'] ?? 1);
            $situacao = $this->categorizeSituacao($rendaFamiliar, $numeroMembros);
            
            if (isset($situacoes[$situacao])) {
                $situacoes[$situacao]++;
            }
            
            if ($rendaFamiliar > 0) {
                $rendaTotal += $rendaFamiliar;
                $contadorRenda++;
            }
        }
        
        $rendaMedia = $contadorRenda > 0 ? $rendaTotal / $contadorRenda : 0;
        
        return [
            'total' => $total,
            'ativas' => $ativas,
            'inativas' => $inativas,
            'situacoes' => $situacoes,
            'renda_media' => $rendaMedia
        ];
    }
}
