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
            
            // Obter ID gerado (PK é idficha na tabela Ficha_Socioeconomico)
            $fichaId = (int)Database::lastInsertId();
            error_log('Ficha criada com idficha: ' . $fichaId);
            
            // 3. Salvar Família (se houver)
            // Aceitar tanto familia_json quanto familia array
            $familia = [];
            if (!empty($data['familia_json'])) {
                $familia = json_decode($data['familia_json'], true);
                if (json_last_error() !== JSON_ERROR_NONE || !is_array($familia)) {
                    error_log('ERRO ao decodificar familia_json: ' . json_last_error_msg());
                    $familia = [];
                }
            } elseif (!empty($data['familia']) && is_array($data['familia'])) {
                $familia = $data['familia'];
            }
            
            if (!empty($familia) && is_array($familia)) {
                $familiaInseridos = 0;
                foreach ($familia as $membro) {
                    // Validar membro antes de inserir
                    if (empty($membro['nome']) || empty($membro['parentesco'])) {
                        error_log('Membro da família ignorado - falta nome ou parentesco');
                        continue;
                    }
                    
                    $renda = 0;
                    if (!empty($membro['renda'])) {
                        // Converter renda para float
                        $renda = is_numeric($membro['renda']) ? floatval($membro['renda']) : 0;
                    }
                    
                    $this->query(
                        "INSERT INTO Familia (id_ficha, nome, parentesco, data_nasc, formacao, renda) VALUES (?, ?, ?, ?, ?, ?)",
                        [
                            $fichaId, // id_ficha (FK) recebe idficha (PK)
                            trim($membro['nome'] ?? ''),
                            trim($membro['parentesco'] ?? ''),
                            $this->convertDate($membro['dataNasc'] ?? $membro['data_nasc'] ?? ''),
                            trim($membro['formacao'] ?? ''),
                            $renda
                        ]
                    );
                    $familiaInseridos++;
                }
                error_log("Família: {$familiaInseridos} membros inseridos");
            }
            
            // 4. Salvar Despesas (se houver)
            // Aceitar tanto despesas_json quanto despesas array
            $despesas = [];
            if (!empty($data['despesas_json'])) {
                $despesas = json_decode($data['despesas_json'], true);
                if (json_last_error() !== JSON_ERROR_NONE || !is_array($despesas)) {
                    error_log('ERRO ao decodificar despesas_json: ' . json_last_error_msg());
                    $despesas = [];
                }
            } elseif (!empty($data['despesas']) && is_array($data['despesas'])) {
                $despesas = $data['despesas'];
            }
            
            if (!empty($despesas) && is_array($despesas)) {
                $despesasInseridas = 0;
                foreach ($despesas as $despesa) {
                    // Normalizar valor
                    $valor = 0;
                    if (!empty($despesa['valor'])) {
                        $valorStr = is_string($despesa['valor']) ? str_replace(',', '.', $despesa['valor']) : $despesa['valor'];
                        $valor = floatval($valorStr);
                    } elseif (!empty($despesa['valor_despesa'])) {
                        $valorStr = is_string($despesa['valor_despesa']) ? str_replace(',', '.', $despesa['valor_despesa']) : $despesa['valor_despesa'];
                        $valor = floatval($valorStr);
                    }
                    
                    // Normalizar tipo/nome
                    $tipo = trim($despesa['tipo'] ?? $despesa['tipo_renda'] ?? $despesa['nome'] ?? '');
                    
                    // Normalizar renda
                    $renda = 0;
                    if (!empty($despesa['renda'])) {
                        $rendaStr = is_string($despesa['renda']) ? str_replace(',', '.', $despesa['renda']) : $despesa['renda'];
                        $renda = floatval($rendaStr);
                    } elseif (!empty($despesa['valor_renda'])) {
                        $rendaStr = is_string($despesa['valor_renda']) ? str_replace(',', '.', $despesa['valor_renda']) : $despesa['valor_renda'];
                        $renda = floatval($rendaStr);
                    }
                    
                    // Inserir se tiver pelo menos valor ou tipo
                    if ($valor > 0 || !empty($tipo)) {
                        $this->query(
                            "INSERT INTO Despesas (id_ficha, valor_despesa, tipo_renda, valor_renda) VALUES (?, ?, ?, ?)",
                            [$fichaId, $valor, $tipo, $renda] // id_ficha (FK) recebe idficha (PK)
                        );
                        $despesasInseridas++;
                    }
                }
                error_log("Despesas: {$despesasInseridas} itens inseridos");
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
            $ficha['nome_completo'] = $ficha['nome'];
            $ficha['data_nascimento'] = $this->formatDate($ficha['data_nascimento']);
            $ficha['idade'] = $this->calculateAge($ficha['data_nascimento']);
            $ficha['categoria'] = $this->categorizeByAge($ficha['idade']);
            
            // Usar idficha corretamente (pode ser idficha ou id_ficha dependendo do schema)
            $fichaId = $ficha['idficha'] ?? $ficha['id_ficha'] ?? null;
            
            // Buscar família se ficha existe
            if ($fichaId) {
                $stmt = $this->query("SELECT * FROM Familia WHERE id_ficha = ?", [$fichaId]);
                $ficha['familia'] = $stmt->fetchAll();
                
                // Buscar despesas
                $stmt = $this->query("SELECT * FROM Despesas WHERE id_ficha = ?", [$fichaId]);
                $ficha['despesas'] = $stmt->fetchAll();
            } else {
                $ficha['familia'] = [];
                $ficha['despesas'] = [];
            }
            
            // Garantir campos numéricos
            $ficha['renda_familiar'] = floatval($ficha['renda_familiar'] ?? 0);
            $ficha['qtd_pessoas'] = intval($ficha['qtd_pessoas'] ?? 0);
            $ficha['numero_membros'] = $ficha['qtd_pessoas'];
            $ficha['nr_comodos'] = intval($ficha['nr_comodos'] ?? 0);
            $ficha['numero_comodos'] = $ficha['nr_comodos'];
            $ficha['nr_veiculos'] = intval($ficha['nr_veiculos'] ?? 0);
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
            
            // 3. Atualizar Família e Despesas (deletar existentes e recriar)
            // Buscar fichaId primeiro (PK é idficha)
            $fichaIdStmt = $this->query("SELECT idficha FROM Ficha_Socioeconomico WHERE id_atendido = ?", [$id]);
            $fichaExistente = $fichaIdStmt->fetch();
            $fichaId = $fichaExistente['idficha'] ?? null;
            
            if ($fichaId) {
                error_log('Atualizando família e despesas para ficha idficha: ' . $fichaId);
                
                // Deletar família e despesas existentes
                $this->query("DELETE FROM Familia WHERE id_ficha = ?", [$fichaId]);
                $this->query("DELETE FROM Despesas WHERE id_ficha = ?", [$fichaId]);
                
                // Salvar nova família (se houver)
                $familia = [];
                if (!empty($data['familia_json'])) {
                    $familia = json_decode($data['familia_json'], true);
                    if (json_last_error() !== JSON_ERROR_NONE || !is_array($familia)) {
                        error_log('ERRO ao decodificar familia_json no update: ' . json_last_error_msg());
                        $familia = [];
                    }
                } elseif (!empty($data['familia']) && is_array($data['familia'])) {
                    $familia = $data['familia'];
                }
                
                if (!empty($familia) && is_array($familia)) {
                    $familiaInseridos = 0;
                    foreach ($familia as $membro) {
                        // Validar membro antes de inserir
                        if (empty($membro['nome']) || empty($membro['parentesco'])) {
                            continue;
                        }
                        
                        $renda = 0;
                        if (!empty($membro['renda'])) {
                            $renda = is_numeric($membro['renda']) ? floatval($membro['renda']) : 0;
                        }
                        
                        $this->query(
                            "INSERT INTO Familia (id_ficha, nome, parentesco, data_nasc, formacao, renda) VALUES (?, ?, ?, ?, ?, ?)",
                            [
                                $fichaId, // id_ficha (FK) recebe idficha (PK)
                                trim($membro['nome'] ?? ''),
                                trim($membro['parentesco'] ?? ''),
                                $this->convertDate($membro['dataNasc'] ?? $membro['data_nasc'] ?? ''),
                                trim($membro['formacao'] ?? ''),
                                $renda
                            ]
                        );
                        $familiaInseridos++;
                    }
                    error_log("Update - Família: {$familiaInseridos} membros inseridos");
                }
                
                // Salvar novas despesas (se houver)
                $despesas = [];
                if (!empty($data['despesas_json'])) {
                    $despesas = json_decode($data['despesas_json'], true);
                    if (json_last_error() !== JSON_ERROR_NONE || !is_array($despesas)) {
                        error_log('ERRO ao decodificar despesas_json no update: ' . json_last_error_msg());
                        $despesas = [];
                    }
                } elseif (!empty($data['despesas']) && is_array($data['despesas'])) {
                    $despesas = $data['despesas'];
                }
                
                if (!empty($despesas) && is_array($despesas)) {
                    $despesasInseridas = 0;
                    foreach ($despesas as $despesa) {
                        // Normalizar valor
                        $valor = 0;
                        if (!empty($despesa['valor'])) {
                            $valorStr = is_string($despesa['valor']) ? str_replace(',', '.', $despesa['valor']) : $despesa['valor'];
                            $valor = floatval($valorStr);
                        } elseif (!empty($despesa['valor_despesa'])) {
                            $valorStr = is_string($despesa['valor_despesa']) ? str_replace(',', '.', $despesa['valor_despesa']) : $despesa['valor_despesa'];
                            $valor = floatval($valorStr);
                        }
                        
                        // Normalizar tipo/nome
                        $tipo = trim($despesa['tipo'] ?? $despesa['tipo_renda'] ?? $despesa['nome'] ?? '');
                        
                        // Normalizar renda
                        $renda = 0;
                        if (!empty($despesa['renda'])) {
                            $rendaStr = is_string($despesa['renda']) ? str_replace(',', '.', $despesa['renda']) : $despesa['renda'];
                            $renda = floatval($rendaStr);
                        } elseif (!empty($despesa['valor_renda'])) {
                            $rendaStr = is_string($despesa['valor_renda']) ? str_replace(',', '.', $despesa['valor_renda']) : $despesa['valor_renda'];
                            $renda = floatval($rendaStr);
                        }
                        
                        // Inserir se tiver pelo menos valor ou tipo
                        if ($valor > 0 || !empty($tipo)) {
                            $this->query(
                                "INSERT INTO Despesas (id_ficha, valor_despesa, tipo_renda, valor_renda) VALUES (?, ?, ?, ?)",
                                [$fichaId, $valor, $tipo, $renda] // id_ficha (FK) recebe idficha (PK)
                            );
                            $despesasInseridas++;
                        }
                    }
                    error_log("Update - Despesas: {$despesasInseridas} itens inseridos");
                }
            } else {
                error_log('ATENÇÃO: Ficha não encontrada para id_atendido: ' . $id);
            }
            
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
     * Busca por nome (compatibilidade)
     */
    public function searchByName($nome) {
        return $this->searchAdvanced($nome);
    }

    /**
     * Busca avançada
     */
    public function searchAdvanced($query) {
        $stmt = $this->query("
            SELECT 
                a.idatendido as id,
                a.idatendido,
                a.nome,
                a.nome as nome_entrevistado,
                a.nome as nome_completo,
                a.cpf,
                a.rg,
                a.data_nascimento,
                a.status,
                f.renda_familiar,
                f.qtd_pessoas as numero_membros
            FROM Atendido a
            INNER JOIN Ficha_Socioeconomico f ON a.idatendido = f.id_atendido
            WHERE 
                a.nome LIKE ? OR
                a.cpf LIKE ? OR
                a.rg LIKE ?
            ORDER BY a.data_cadastro DESC
            LIMIT 100
        ", ["%$query%", "%$query%", "%$query%"]);
        
        $results = $stmt->fetchAll();
        
        // Formatar datas e adicionar dados calculados
        foreach ($results as &$result) {
            $result['data_nascimento'] = $this->formatDate($result['data_nascimento']);
            $result['idade'] = $this->calculateAge($result['data_nascimento']);
            $result['categoria'] = $this->categorizeByAge($result['idade']);
        }
        
        return $results;
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
