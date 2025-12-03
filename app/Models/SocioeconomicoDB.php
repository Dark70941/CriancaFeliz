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
            
            if (empty($data['id_atendido'])) {
                throw new Exception('Selecione a criança/atendido antes de continuar.');
            }

            $atendidoId = (int)$data['id_atendido'];

            // Verificar se o atendido existe
            $atendido = $this->query('SELECT idatendido FROM Atendido WHERE idatendido = ?', [$atendidoId])->fetch();
            if (!$atendido) {
                throw new Exception('Atendido selecionado não encontrado');
            }

            // Impedir duplicidade de ficha para a mesma criança
            $fichaExistente = $this->query('SELECT idficha FROM Ficha_Socioeconomico WHERE id_atendido = ?', [$atendidoId])->fetch();
            if ($fichaExistente) {
                throw new Exception('Esta criança já possui uma ficha socioeconômica cadastrada.');
            }
            
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
                    !empty($data['agua']) ? $data['agua'] : null,
                    !empty($data['esgoto']) ? $data['esgoto'] : null,
                    !empty($data['energia']) ? $data['energia'] : null,
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
            
            // 3. Salvar Família (se houver) - via arrays do POST
            if (!empty($data['familia']) && is_array($data['familia'])) {
                foreach ($data['familia'] as $membro) {
                    $this->query(
                        "INSERT INTO Familia (id_ficha, nome, parentesco, data_nasc, formacao, renda) VALUES (?, ?, ?, ?, ?, ?)",
                        [
                            $fichaId,
                            $membro['nome'] ?? '',
                            $membro['parentesco'] ?? '',
                            $this->convertDate($membro['data_nasc'] ?? ''),
                            $membro['formacao'] ?? '',
                            isset($membro['renda']) ? floatval($membro['renda']) : 0
                        ]
                    );
                }
            }
            
            // 4. Salvar Despesas (se houver) - via arrays do POST
            if (!empty($data['despesas']) && is_array($data['despesas'])) {
                foreach ($data['despesas'] as $despesa) {
                    $this->query(
                        "INSERT INTO Despesas (id_ficha, valor_despesa, tipo_renda, valor_renda) VALUES (?, ?, ?, ?)",
                        [
                            $fichaId,
                            isset($despesa['valor']) ? floatval($despesa['valor']) : 0,
                            $despesa['tipo'] ?? '',
                            isset($despesa['renda']) ? floatval($despesa['renda']) : 0
                        ]
                    );
                }
            }
            
            Database::commit();
            
            return $this->getFicha($atendidoId);
            
        } catch (Exception $e) {
            Database::rollback();
            error_log('Erro ao criar ficha socioeconômica: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            
            // Mensagens mais claras para erros comuns
            $message = $e->getMessage();
            if (strpos($message, 'Data truncated') !== false || strpos($message, 'Incorrect string value') !== false) {
                $message = 'Erro: Os campos água, esgoto ou energia precisam ser alterados no banco de dados. Execute o script: database/alter_socioeconomico_agua_esgoto_energia.sql';
            }
            
            throw new Exception($message);
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
        
        if (!$ficha) {
            return null;
        }
        
        // Mapear campos
        $ficha['id'] = $ficha['idatendido'];
        $ficha['id_atendido'] = $ficha['idatendido'];
        $ficha['nome_entrevistado'] = $ficha['nome'];
        $ficha['nome_completo'] = $ficha['nome'];
        $ficha['nome_menor'] = $ficha['nome'];
        
        // Formatar data de nascimento
        if (!empty($ficha['data_nascimento'])) {
            $ficha['data_nascimento'] = $this->formatDate($ficha['data_nascimento']);
        }
        
        // Calcular idade
        $ficha['idade'] = $this->calculateAge($ficha['data_nascimento'] ?? '');
        $ficha['categoria'] = $this->categorizeByAge($ficha['idade']);
        
        // Mapear campos da ficha socioeconômica
        if (!empty($ficha['idficha'])) {
            // Buscar família
            $stmt = $this->query("SELECT * FROM Familia WHERE id_ficha = ?", [$ficha['idficha']]);
            $familia = $stmt->fetchAll();
            $ficha['familia'] = $familia;
            
            // Converter família para formato JSON esperado
            $familiaJson = [];
            foreach ($familia as $membro) {
                $familiaJson[] = [
                    'nome' => $membro['nome'] ?? '',
                    'parentesco' => $membro['parentesco'] ?? '',
                    'data_nasc' => $this->formatDate($membro['data_nasc'] ?? ''),
                    'formacao' => $membro['formacao'] ?? '',
                    'renda' => floatval($membro['renda'] ?? 0)
                ];
            }
            $ficha['familia_json'] = json_encode($familiaJson);
            
            // Buscar despesas
            $stmt = $this->query("SELECT * FROM Despesas WHERE id_ficha = ?", [$ficha['idficha']]);
            $despesas = $stmt->fetchAll();
            $ficha['despesas'] = $despesas;
            
            // Mapear campos de habitação
            $ficha['residencia'] = $ficha['residencia'] ?? null;
            $ficha['numero_comodos'] = $ficha['nr_comodos'] ?? null;
            $ficha['num_comodos'] = $ficha['nr_comodos'] ?? null;
            $ficha['construcao'] = $ficha['construcao'] ?? null;
            $ficha['agua'] = $ficha['agua'] ?? null;
            $ficha['esgoto'] = $ficha['esgoto'] ?? null;
            $ficha['energia'] = $ficha['energia'] ?? null;
            $ficha['tipo_moradia'] = $ficha['moradia'] ?? null;
            $ficha['situacao_moradia'] = $ficha['cond_residencia'] ?? null;
            $ficha['qtd_pessoas'] = $ficha['qtd_pessoas'] ?? 0;
            $ficha['pessoas_casa'] = $ficha['qtd_pessoas'] ?? 0;
            $ficha['renda_familiar'] = floatval($ficha['renda_familiar'] ?? 0);
            $ficha['observacoes'] = $ficha['observacoes'] ?? null;
        } else {
            // Se não tem ficha ainda, inicializar arrays vazios
            $ficha['familia'] = [];
            $ficha['familia_json'] = '[]';
            $ficha['despesas'] = [];
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
            
            // Verificar existência da ficha para o atendido informado
            $ficha = $this->query('SELECT idficha FROM Ficha_Socioeconomico WHERE id_atendido = ?', [$id])->fetch();
            if (!$ficha) {
                throw new Exception('Ficha socioeconômica não encontrada para o atendido informado.');
            }
            
            // Atualizar Ficha Socioeconômica
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
                    nr_veiculos = ?, observacoes = ?, entrevistado = ?, 
                    residencia = ?, nr_comodos = ?, construcao = ?
                WHERE id_atendido = ?",
                [
                    !empty($data['agua']) ? $data['agua'] : null,
                    !empty($data['esgoto']) ? $data['esgoto'] : null,
                    !empty($data['energia']) ? $data['energia'] : null,
                    $rendaFamiliar,
                    $data['pessoas_casa'] ?? $data['qtd_pessoas'] ?? 0,
                    $data['situacao_moradia'] ?? $data['cond_residencia'] ?? null,
                    $data['tipo_moradia'] ?? $data['moradia'] ?? null,
                    $data['nr_veiculos'] ?? 0,
                    $data['observacoes'] ?? null,
                    $data['nome_entrevistado'] ?? $data['nome_completo'] ?? '',
                    $data['residencia'] ?? null,
                    $data['numero_comodos'] ?? $data['nr_comodos'] ?? 0,
                    $data['construcao'] ?? null,
                    $id
                ]
            );
            
            $fichaId = $ficha['idficha'];
            
            // Sincronizar Família - sempre sincronizar para manter dados atualizados
            // Apagar todos os antigos primeiro
            $this->query("DELETE FROM Familia WHERE id_ficha = ?", [$fichaId]);
            
            // Inserir novos se houver
            if (!empty($data['familia']) && is_array($data['familia'])) {
                foreach ($data['familia'] as $membro) {
                    // Validar que o membro tem pelo menos nome ou parentesco
                    if (!empty($membro['nome']) || !empty($membro['parentesco'])) {
                        $this->query(
                            "INSERT INTO Familia (id_ficha, nome, parentesco, data_nasc, formacao, renda) VALUES (?, ?, ?, ?, ?, ?)",
                            [
                                $fichaId,
                                $membro['nome'] ?? '',
                                $membro['parentesco'] ?? '',
                                $this->convertDate($membro['data_nasc'] ?? ''),
                                $membro['formacao'] ?? '',
                                isset($membro['renda']) ? floatval($membro['renda']) : 0
                            ]
                        );
                    }
                }
            }
            
            // Sincronizar Despesas - sempre sincronizar para manter dados atualizados
            // Apagar todas as antigas primeiro
            $this->query("DELETE FROM Despesas WHERE id_ficha = ?", [$fichaId]);
            
            // Inserir novas se houver
            if (!empty($data['despesas']) && is_array($data['despesas'])) {
                foreach ($data['despesas'] as $despesa) {
                    // Validar que a despesa tem pelo menos tipo ou valor
                    if (!empty($despesa['tipo']) || !empty($despesa['valor'])) {
                        $this->query(
                            "INSERT INTO Despesas (id_ficha, valor_despesa, tipo_renda, valor_renda) VALUES (?, ?, ?, ?)",
                            [
                                $fichaId,
                                isset($despesa['valor']) ? floatval($despesa['valor']) : 0,
                                $despesa['tipo'] ?? '',
                                isset($despesa['renda']) ? floatval($despesa['renda']) : 0
                            ]
                        );
                    }
                }
            }
            
            Database::commit();
            
            return $this->getFicha($id);
            
        } catch (Exception $e) {
            Database::rollback();
            error_log('Erro ao atualizar ficha: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            
            // Mensagens mais claras para erros comuns
            $message = $e->getMessage();
            if (strpos($message, 'Data truncated') !== false || strpos($message, 'Incorrect string value') !== false) {
                $message = 'Erro: Os campos água, esgoto ou energia precisam ser alterados no banco de dados. Execute o script: database/alter_socioeconomico_agua_esgoto_energia.sql';
            }
            
            throw new Exception($message);
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
            SELECT 
                a.idatendido AS id,
                a.nome AS nome_completo,
                a.cpf,
                a.rg,
                a.data_nascimento,
                f.renda_familiar,
                f.qtd_pessoas
            FROM Atendido a
            LEFT JOIN Ficha_Socioeconomico f ON a.idatendido = f.id_atendido
            WHERE a.nome LIKE :nome
            ORDER BY a.nome ASC
            LIMIT 30
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':nome', '%' . $nome . '%');
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lista atendidos (para popular seletores)
     */
    public function listAtendidos($limit = 50)
    {
        $stmt = $this->pdo->prepare("SELECT idatendido AS id, nome, cpf FROM Atendido ORDER BY data_cadastro DESC LIMIT ?");
        $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca atendidos por nome/CPF (autocomplete)
     */
    public function searchAtendidos($term, $limit = 20)
    {
        $sql = "SELECT idatendido AS id, nome, cpf FROM Atendido WHERE nome LIKE :t OR cpf LIKE :t ORDER BY nome ASC LIMIT :lim";
        $stmt = $this->pdo->prepare($sql);
        $like = '%' . $term . '%';
        $stmt->bindValue(':t', $like, PDO::PARAM_STR);
        $stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
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
