<?php

/**
 * Model para fichas socioeconômicas - MYSQL COMPLETO
 */
class SocioeconomicoDB extends BaseModelDB {
    
    public function __construct() {
        parent::__construct('Atendido', 'idatendido');
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
     * Normalizar dados
     */
    private function normalizeData($data) {
        // Normalizar CPF
        if (isset($data['cpf'])) {
            $data['cpf'] = preg_replace('/\D+/', '', $data['cpf']);
        }
        
        // Normalizar RG
        if (isset($data['rg'])) {
            $data['rg'] = preg_replace('/\D+/', '', $data['rg']);
        }
        
        // Normalizar CEP
        if (isset($data['cep'])) {
            $data['cep'] = preg_replace('/\D+/', '', $data['cep']);
        }
        
        // Normalizar telefones
        $telefoneFields = ['telefone', 'celular', 'contato_emergencia'];
        foreach ($telefoneFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = preg_replace('/\D+/', '', $data[$field]);
            }
        }
        
        return $data;
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
     * Categorizar por idade
     */
    public function categorizeByAge($age) {
        if ($age < 12) return 'Criança';
        if ($age < 18) return 'Adolescente';
        return 'Adulto';
    }
    
    /**
     * Criar ficha socioeconômica
     */
    public function createFicha($data) {
        try {
            Database::beginTransaction();
            
            // Normalizar dados
            $data = $this->normalizeData($data);
            
            // 1. Criar Atendido (se não existir)
            $atendidoData = [
                'nome' => $data['nome_entrevistado'] ?? $data['nome_completo'] ?? '',
                'cpf' => $data['cpf'] ?? '',
                'rg' => $data['rg'] ?? '',
                'data_nascimento' => $this->convertDate($data['data_nascimento'] ?? ''),
                'data_cadastro' => date('Y-m-d'),
                'endereco' => $data['endereco'] ?? null,
                'numero' => $data['numero'] ?? null,
                'complemento' => $data['complemento'] ?? null,
                'bairro' => $data['bairro'] ?? null,
                'cidade' => $data['cidade'] ?? null,
                'cep' => $data['cep'] ?? null,
                'status' => 'Ativo',
                'faixa_etaria' => $this->calculateAge($data['data_nascimento'] ?? '')
            ];
            
            $atendido = $this->create($atendidoData);
            $atendidoId = $atendido['idatendido'];
            
            // 2. Criar Ficha Socioeconômica
            // Converter renda_familiar para número (remover R$, pontos e converter vírgula)
            $rendaFamiliar = 0;
            if (!empty($data['renda_familiar'])) {
                $renda = $data['renda_familiar'];
                $renda = str_replace(['R$', '.', ','], ['', '', '.'], $renda);
                $rendaFamiliar = floatval($renda);
            }
            
            $this->query(
                "INSERT INTO Ficha_Socioeconomico (
                    id_atendido, agua, esgoto, energia, renda_familiar, 
                    qtd_pessoas, cond_residencia, moradia, nr_veiculos, 
                    observacoes, entrevistado, residencia, nr_comodos, construcao
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $atendidoId,
                    isset($data['agua']) ? 1 : 0,
                    isset($data['esgoto']) ? 1 : 0,
                    isset($data['energia']) ? 1 : 0,
                    $rendaFamiliar,
                    $data['pessoas_casa'] ?? $data['qtd_pessoas'] ?? 0,
                    $data['situacao_moradia'] ?? $data['cond_residencia'] ?? null,
                    $data['tipo_moradia'] ?? $data['moradia'] ?? null,
                    $data['nr_veiculos'] ?? 0,
                    $data['observacoes'] ?? null,
                    $data['nome_entrevistado'] ?? $data['nome_completo'] ?? '',
                    $data['residencia'] ?? null,
                    $data['numero_comodos'] ?? $data['nr_comodos'] ?? 0,
                    $data['construcao'] ?? null
                ]
            );
            
            $fichaId = Database::lastInsertId();
            
            // 3. Salvar Família (se houver)
            if (!empty($data['familia_json'])) {
                $familia = json_decode($data['familia_json'], true);
                if (is_array($familia)) {
                    foreach ($familia as $membro) {
                        $this->query(
                            "INSERT INTO Familia (id_ficha, nome, parentesco, data_nasc, formacao, renda) VALUES (?, ?, ?, ?, ?, ?)",
                            [
                                $fichaId,
                                $membro['nome'] ?? '',
                                $membro['parentesco'] ?? '',
                                $this->convertDate($membro['dataNasc'] ?? ''),
                                $membro['formacao'] ?? '',
                                $membro['renda'] ?? 0
                            ]
                        );
                    }
                }
            }
            
            // 4. Salvar Despesas (se houver)
            if (!empty($data['despesas'])) {
                foreach ($data['despesas'] as $despesa) {
                    $this->query(
                        "INSERT INTO Despesas (id_ficha, valor_despesa, tipo_renda, valor_renda) VALUES (?, ?, ?, ?)",
                        [
                            $fichaId,
                            $despesa['valor'] ?? 0,
                            $despesa['tipo'] ?? '',
                            $despesa['renda'] ?? 0
                        ]
                    );
                }
            }
            
            Database::commit();
            
            return $this->getFicha($atendidoId);
            
        } catch (Exception $e) {
            Database::rollback();
            error_log('Erro ao criar ficha socioeconômica: ' . $e->getMessage());
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
                f.*
            FROM Atendido a
            LEFT JOIN Ficha_Socioeconomico f ON a.idatendido = f.id_atendido
            WHERE a.idatendido = ?
        ", [$id]);
        
        $ficha = $stmt->fetch();
        
        if ($ficha) {
            // Mapear campos
            $ficha['id'] = $ficha['idatendido'];
            $ficha['nome_entrevistado'] = $ficha['nome'];
            $ficha['data_nascimento'] = $this->formatDate($ficha['data_nascimento']);
            $ficha['idade'] = $this->calculateAge($ficha['data_nascimento']);
            $ficha['categoria'] = $this->categorizeByAge($ficha['idade']);
            
            // Buscar família
            $stmt = $this->query("SELECT * FROM Familia WHERE id_ficha = ?", [$ficha['idficha']]);
            $ficha['familia'] = $stmt->fetchAll();
            
            // Buscar despesas
            $stmt = $this->query("SELECT * FROM Despesas WHERE id_ficha = ?", [$ficha['idficha']]);
            $ficha['despesas'] = $stmt->fetchAll();
        }
        
        return $ficha;
    }
    
    /**
     * Listar todas as fichas
     */
    public function listFichas($page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        
        // Contar total
        $countStmt = $this->query("
            SELECT COUNT(*) as total 
            FROM Atendido a
            INNER JOIN Ficha_Socioeconomico f ON a.idatendido = f.id_atendido
        ");
        $countResult = $countStmt->fetch();
        
        $stmt = $this->query("
            SELECT 
                a.idatendido as id,
                a.idatendido,
                a.nome,
                a.nome as nome_entrevistado,
                a.nome as nome_completo,
                a.cpf,
                a.data_nascimento,
                a.status,
                f.renda_familiar,
                f.qtd_pessoas
            FROM Atendido a
            INNER JOIN Ficha_Socioeconomico f ON a.idatendido = f.id_atendido
            ORDER BY a.data_cadastro DESC
            LIMIT ? OFFSET ?
        ", [$perPage, $offset]);
        
        $fichas = $stmt->fetchAll();
        
        // Formatar datas e adicionar dados calculados
        foreach ($fichas as &$ficha) {
            $ficha['data_nascimento'] = $this->formatDate($ficha['data_nascimento']);
            $ficha['idade'] = $this->calculateAge($ficha['data_nascimento']);
            $ficha['categoria'] = $this->categorizeByAge($ficha['idade']);
            
            // Garantir que nome_completo existe (compatibilidade com view)
            if (empty($ficha['nome_completo'])) {
                $ficha['nome_completo'] = $ficha['nome_entrevistado'];
            }
        }
        
        // Contar total
        $stmt = $this->query("
            SELECT COUNT(*) as total 
            FROM Atendido a
            INNER JOIN Ficha_Socioeconomico f ON a.idatendido = f.id_atendido
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
                'nome' => $data['nome_entrevistado'] ?? $data['nome_completo'] ?? '',
                'cpf' => $data['cpf'] ?? '',
                'rg' => $data['rg'] ?? '',
                'data_nascimento' => $this->convertDate($data['data_nascimento'] ?? '')
            ];
            
            $this->update($id, $atendidoData);
            
            // 2. Atualizar Ficha Socioeconômica
            // Converter renda_familiar para número (remover R$, pontos e converter vírgula)
            $rendaFamiliar = 0;
            if (!empty($data['renda_familiar'])) {
                $renda = $data['renda_familiar'];
                $renda = str_replace(['R$', '.', ','], ['', '', '.'], $renda);
                $rendaFamiliar = floatval($renda);
            }
            
            $this->query(
                "UPDATE Ficha_Socioeconomico SET 
                    agua = ?, esgoto = ?, energia = ?, renda_familiar = ?,
                    qtd_pessoas = ?, cond_residencia = ?, moradia = ?,
                    nr_veiculos = ?, observacoes = ?, nr_comodos = ?, construcao = ?
                WHERE id_atendido = ?",
                [
                    isset($data['agua']) ? 1 : 0,
                    isset($data['esgoto']) ? 1 : 0,
                    isset($data['energia']) ? 1 : 0,
                    $rendaFamiliar,
                    $data['pessoas_casa'] ?? $data['qtd_pessoas'] ?? 0,
                    $data['situacao_moradia'] ?? $data['cond_residencia'] ?? null,
                    $data['tipo_moradia'] ?? $data['moradia'] ?? null,
                    $data['nr_veiculos'] ?? 0,
                    $data['observacoes'] ?? null,
                    $data['numero_comodos'] ?? $data['nr_comodos'] ?? 0,
                    $data['construcao'] ?? null,
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
            
            // Deletar (CASCADE deletará automaticamente)
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

    public function searchByName($nome)
    {
        $sql = "
            SELECT *
            FROM ficha_socioeconomico
            WHERE nome_completo LIKE :nome
            ORDER BY nome_completo ASC
            LIMIT 30
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':nome', '%' . $nome . '%');
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function searchAdvanced($query) {
        $stmt = $this->query("
            SELECT 
                a.idatendido as id,
                a.nome as nome_entrevistado,
                a.cpf,
                a.rg
            FROM Atendido a
            INNER JOIN Ficha_Socioeconomico f ON a.idatendido = f.id_atendido
            WHERE 
                a.nome LIKE ? OR
                a.cpf LIKE ? OR
                a.rg LIKE ?
        ", ["%$query%", "%$query%", "%$query%"]);
        
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
                INNER JOIN Ficha_Socioeconomico f ON a.idatendido = f.id_atendido
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
                INNER JOIN Ficha_Socioeconomico f ON a.idatendido = f.id_atendido
                GROUP BY categoria
            ");
            $porCategoria = $stmt->fetchAll();
            
            // Por status
            $stmt = $this->query("
                SELECT 
                    a.status,
                    COUNT(*) as total
                FROM Atendido a
                INNER JOIN Ficha_Socioeconomico f ON a.idatendido = f.id_atendido
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
