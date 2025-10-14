<?php

/**
 * Model para fichas de acolhimento - MYSQL COMPLETO
 */
class AcolhimentoDB extends BaseModelDB {
    
    public function __construct() {
        parent::__construct('Atendido', 'idatendido');
    }
    
    /**
     * Criar ou buscar responsável
     */
    private function createOrGetResponsavel($data) {
        // Verificar se responsável já existe pelo CPF
        $stmt = $this->query(
            "SELECT idresponsavel FROM Responsavel WHERE cpf = ?",
            [$data['cpf_responsavel']]
        );
        $responsavel = $stmt->fetch();
        
        if ($responsavel) {
            return $responsavel['idresponsavel'];
        }
        
        // Criar novo responsável
        $this->query(
            "INSERT INTO Responsavel (nome, cpf, rg, telefone, parentesco) VALUES (?, ?, ?, ?, ?)",
            [
                $data['nome_responsavel'],
                $data['cpf_responsavel'],
                $data['rg_responsavel'] ?? null,
                $data['contato_1'],
                $data['grau_parentesco']
            ]
        );
        
        return Database::lastInsertId();
    }
    
    /**
     * Converter data dd/mm/yyyy para yyyy-mm-dd
     */
    private function convertDate($date) {
        if (empty($date)) return null;
        
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $date, $matches)) {
            return "{$matches[3]}-{$matches[2]}-{$matches[1]}";
        }
        
        return $date;
    }
    
    /**
     * Calcular idade
     */
    public function calculateAge($dataNascimento) {
        if (empty($dataNascimento)) return 0;
        
        $date = $this->convertDate($dataNascimento);
        $birthDate = new DateTime($date);
        $today = new DateTime();
        return $birthDate->diff($today)->y;
    }
    
    /**
     * Normalizar dados
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
     * Criar ficha de acolhimento
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
            $this->query(
                "INSERT INTO Ficha_Acolhimento (id_atendido, data_acolhimento, encaminha_por, queixa_principal, escola, periodo, ponto_referencia, cras, ubs, cad_unico, acolhimento_responsavel, acolhimento_funcao, carimbo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $atendidoId,
                    $this->convertDate($data['data_acolhimento']),
                    $data['encaminha_por'] ?? null,
                    $data['queixa_principal'],
                    $data['escola'] ?? null,
                    $data['periodo'] ?? null,
                    $data['ponto_referencia'] ?? null,
                    $data['cras'] ?? null,
                    $data['ubs'] ?? null,
                    $data['cad_unico'] ?? null,
                    $data['acolhimento_responsavel'] ?? null,
                    $data['acolhimento_funcao'] ?? null,
                    $data['carimbo'] ?? null
                ]
            );
            
            Database::commit();
            
            return $this->getFicha($atendidoId);
            
        } catch (Exception $e) {
            Database::rollback();
            error_log('Erro ao criar ficha: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Buscar ficha completa
     */
    public function getFicha($id) {
        $stmt = $this->query("
            SELECT 
                a.*,
                r.nome as nome_responsavel,
                r.cpf as cpf_responsavel,
                r.rg as rg_responsavel,
                r.telefone as contato_1,
                r.parentesco as grau_parentesco,
                f.data_acolhimento,
                f.encaminha_por,
                f.queixa_principal,
                f.escola,
                f.periodo,
                f.ponto_referencia,
                f.cras,
                f.ubs,
                f.cad_unico,
                f.acolhimento_responsavel,
                f.acolhimento_funcao,
                f.carimbo
            FROM Atendido a
            LEFT JOIN Responsavel r ON a.id_responsavel = r.idresponsavel
            LEFT JOIN Ficha_Acolhimento f ON a.idatendido = f.id_atendido
            WHERE a.idatendido = ?
        ", [$id]);
        
        $ficha = $stmt->fetch();
        
        if ($ficha) {
            // Mapear campos para formato esperado
            $ficha['id'] = $ficha['idatendido'];
            $ficha['nome_completo'] = $ficha['nome'];
            $ficha['data_nascimento'] = $this->formatDate($ficha['data_nascimento']);
            $ficha['data_acolhimento'] = $this->formatDate($ficha['data_acolhimento']);
            $ficha['idade'] = $this->calculateAge($ficha['data_nascimento']);
            $ficha['categoria'] = $this->categorizeByAge($ficha['idade']);
        }
        
        return $ficha;
    }
    
    /**
     * Formatar data yyyy-mm-dd para dd/mm/yyyy
     */
    private function formatDate($date) {
        if (empty($date)) return '';
        
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date, $matches)) {
            return "{$matches[3]}/{$matches[2]}/{$matches[1]}";
        }
        
        return $date;
    }
    
    /**
     * Categorizar por idade
     */
    public function categorizeByAge($age) {
        if ($age < 12) return 'Criança';
        if ($age < 18) return 'Adolescente';
        return 'Adulto';
    }
    
    /**
     * Listar todas as fichas
     */
    public function listFichas($page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        
        $stmt = $this->query("
            SELECT 
                a.idatendido as id,
                a.nome as nome_completo,
                a.cpf,
                a.data_nascimento,
                a.status,
                f.data_acolhimento,
                r.nome as nome_responsavel
            FROM Atendido a
            LEFT JOIN Ficha_Acolhimento f ON a.idatendido = f.id_atendido
            LEFT JOIN Responsavel r ON a.id_responsavel = r.idresponsavel
            WHERE f.id_atendido IS NOT NULL
            ORDER BY a.data_cadastro DESC
            LIMIT ? OFFSET ?
        ", [$perPage, $offset]);
        
        $fichas = $stmt->fetchAll();
        
        // Formatar datas e calcular idade
        foreach ($fichas as &$ficha) {
            $ficha['data_nascimento'] = $this->formatDate($ficha['data_nascimento']);
            $ficha['data_acolhimento'] = $this->formatDate($ficha['data_acolhimento']);
            $ficha['idade'] = $this->calculateAge($ficha['data_nascimento']);
            $ficha['categoria'] = $this->categorizeByAge($ficha['idade']);
        }
        
        // Contar total
        $stmt = $this->query("
            SELECT COUNT(*) as total 
            FROM Atendido a
            INNER JOIN Ficha_Acolhimento f ON a.idatendido = f.id_atendido
        ");
        $result = $stmt->fetch();
        $total = $result['total'];
        
        return [
            'data' => $fichas,
            'total' => $total,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage),
            'per_page' => $perPage,
            // Compatibilidade
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage)
        ];
    }
    
    /**
     * Atualizar ficha
     */
    public function updateFicha($id, $data) {
        try {
            Database::beginTransaction();
            
            // Normalizar dados
            $data = $this->normalizeData($data);
            
            // 1. Atualizar Atendido
            $atendidoData = [
                'nome' => $data['nome_completo'],
                'cpf' => $data['cpf'],
                'rg' => $data['rg'],
                'data_nascimento' => $this->convertDate($data['data_nascimento']),
                'endereco' => $data['endereco'],
                'numero' => $data['numero'],
                'complemento' => $data['complemento'] ?? null,
                'bairro' => $data['bairro'],
                'cidade' => $data['cidade'],
                'cep' => $data['cep']
            ];
            
            $this->update($id, $atendidoData);
            
            // 2. Atualizar Responsável
            $ficha = $this->getFicha($id);
            if ($ficha && isset($ficha['id_responsavel'])) {
                $this->query(
                    "UPDATE Responsavel SET nome = ?, cpf = ?, rg = ?, telefone = ?, parentesco = ? WHERE idresponsavel = ?",
                    [
                        $data['nome_responsavel'],
                        $data['cpf_responsavel'],
                        $data['rg_responsavel'] ?? null,
                        $data['contato_1'],
                        $data['grau_parentesco'],
                        $ficha['id_responsavel']
                    ]
                );
            }
            
            // 3. Atualizar Ficha de Acolhimento
            $this->query(
                "UPDATE Ficha_Acolhimento SET 
                    data_acolhimento = ?,
                    encaminha_por = ?,
                    queixa_principal = ?,
                    escola = ?,
                    periodo = ?,
                    ponto_referencia = ?,
                    cras = ?,
                    ubs = ?,
                    cad_unico = ?,
                    acolhimento_responsavel = ?,
                    acolhimento_funcao = ?
                WHERE id_atendido = ?",
                [
                    $this->convertDate($data['data_acolhimento']),
                    $data['encaminha_por'] ?? null,
                    $data['queixa_principal'],
                    $data['escola'] ?? null,
                    $data['periodo'] ?? null,
                    $data['ponto_referencia'] ?? null,
                    $data['cras'] ?? null,
                    $data['ubs'] ?? null,
                    $data['cad_unico'] ?? null,
                    $data['acolhimento_responsavel'] ?? null,
                    $data['acolhimento_funcao'] ?? null,
                    $id
                ]
            );
            
            Database::commit();
            
            return $this->getFicha($id);
            
        } catch (Exception $e) {
            Database::rollback();
            error_log('Erro ao atualizar ficha: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Deletar ficha
     */
    public function deleteFicha($id) {
        try {
            Database::beginTransaction();
            
            // Deletar Ficha de Acolhimento (CASCADE deletará automaticamente)
            $this->delete($id);
            
            Database::commit();
            
            return true;
            
        } catch (Exception $e) {
            Database::rollback();
            throw $e;
        }
    }
    
    /**
     * Busca avançada
     */
    public function searchAdvanced($query) {
        $stmt = $this->query("
            SELECT 
                a.idatendido as id,
                a.nome as nome_completo,
                a.cpf,
                a.rg,
                a.data_nascimento,
                r.nome as nome_responsavel,
                r.cpf as cpf_responsavel
            FROM Atendido a
            LEFT JOIN Responsavel r ON a.id_responsavel = r.idresponsavel
            LEFT JOIN Ficha_Acolhimento f ON a.idatendido = f.id_atendido
            WHERE f.id_atendido IS NOT NULL
            AND (
                a.nome LIKE ? OR
                a.cpf LIKE ? OR
                a.rg LIKE ? OR
                r.nome LIKE ? OR
                r.cpf LIKE ?
            )
        ", ["%$query%", "%$query%", "%$query%", "%$query%", "%$query%"]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Obter estatísticas
     */
    public function getStatistics() {
        try {
            // Total de fichas
            $stmt = $this->query("
                SELECT COUNT(*) as total 
                FROM Atendido a
                INNER JOIN Ficha_Acolhimento f ON a.idatendido = f.id_atendido
            ");
            $total = $stmt->fetch()['total'];
            
            // Por categoria
            $stmt = $this->query("
                SELECT 
                    CASE 
                        WHEN TIMESTAMPDIFF(YEAR, a.data_nascimento, CURDATE()) < 12 THEN 'Criança'
                        WHEN TIMESTAMPDIFF(YEAR, a.data_nascimento, CURDATE()) < 18 THEN 'Adolescente'
                        ELSE 'Adulto'
                    END as categoria,
                    COUNT(*) as total
                FROM Atendido a
                INNER JOIN Ficha_Acolhimento f ON a.idatendido = f.id_atendido
                GROUP BY categoria
            ");
            $porCategoria = $stmt->fetchAll();
            
            // Por status
            $stmt = $this->query("
                SELECT 
                    a.status,
                    COUNT(*) as total
                FROM Atendido a
                INNER JOIN Ficha_Acolhimento f ON a.idatendido = f.id_atendido
                GROUP BY a.status
            ");
            $porStatus = $stmt->fetchAll();
            
            return [
                'total' => $total,
                'porCategoria' => $porCategoria,
                'porStatus' => $porStatus
            ];
            
        } catch (Exception $e) {
            error_log('Erro ao obter estatísticas: ' . $e->getMessage());
            return [
                'total' => 0,
                'porCategoria' => [],
                'porStatus' => []
            ];
        }
    }
}
