<?php

/**
 * Service para lógica de negócio das fichas socioeconômicas
 */
class SocioeconomicoService {
    private $socioeconomicoModel;
    
    public function __construct() {
        $this->socioeconomicoModel = App::getSocioeconomicoModel();
    }
    
    /**
     * Lista todas as fichas com paginação
     */
    public function listFichas($page = 1, $perPage = 10) {
        // Chamar o método específico do SocioeconomicoDB
        if (method_exists($this->socioeconomicoModel, 'listFichas')) {
            return $this->socioeconomicoModel->listFichas($page, $perPage);
        }
        // Fallback para método genérico
        return $this->socioeconomicoModel->paginate($page, $perPage);
    }
    
    /**
     * Busca ficha por ID
     */
    public function getFicha($id) {
        if (method_exists($this->socioeconomicoModel, 'getFicha')) {
            $ficha = $this->socioeconomicoModel->getFicha($id);
        } else {
            $ficha = $this->socioeconomicoModel->findById($id);
        }

        if (!$ficha) {
            throw new Exception('Ficha não encontrada');
        }

        return $this->enrichFicha($ficha);
    }
    
    /**
     * Cria nova ficha
     */
    public function createFicha($data) {
        // Sanitizar dados
        $data = sanitizeInput($data);
        
        // Validações específicas
        $this->validateFichaData($data);
        
        // Criar ficha
        $ficha = $this->socioeconomicoModel->createFicha($data);
        
        // Log da ação
        $this->logAction('create', $ficha['id'], 'Ficha socioeconômica criada');
        
        return $ficha;
    }
    
    /**
     * Atualiza ficha existente
     */
    public function updateFicha($id, $data) {
        // Sanitizar dados
        $data = sanitizeInput($data);
        
        // Validações específicas
        $this->validateFichaData($data, $id);
        
        // Atualizar ficha
        $ficha = $this->socioeconomicoModel->updateFicha($id, $data);
        
        // Log da ação
        $this->logAction('update', $id, 'Ficha socioeconômica atualizada');
        
        return $ficha;
    }
    
    /**
     * Exclui ficha
     */
    public function deleteFicha($id) {
        $ficha = $this->socioeconomicoModel->findById($id);
        
        if (!$ficha) {
            throw new Exception('Ficha não encontrada');
        }
        
        $result = $this->socioeconomicoModel->delete($id);
        
        if ($result) {
            // Log da ação
            $this->logAction('delete', $id, 'Ficha socioeconômica excluída');
        }
        
        return $result;
    }
    
    /**
     * Busca avançada
     */

    
    public function searchFichas($query, $filters = []) {
    $results = $this->socioeconomicoModel->searchByName($query);
        
        // Aplicar filtros adicionais
        if (!empty($filters)) {
            $results = $this->applyFilters($results, $filters);
        }
        
        // Adicionar dados calculados
        foreach ($results as &$ficha) {
            $ficha = $this->enrichFicha($ficha);
        }
        
        return $results;
    }

    /**
     * Lista atendidos para seleção (quando disponível no model DB)
     */
    public function listAtendidos($limit = 50) {
        if (method_exists($this->socioeconomicoModel, 'listAtendidos')) {
            return $this->socioeconomicoModel->listAtendidos($limit);
        }
        return [];
    }

    /**
     * Busca atendidos (autocomplete) quando suportado pelo model DB
     */
    public function searchAtendidos($term, $limit = 20) {
        if (method_exists($this->socioeconomicoModel, 'searchAtendidos')) {
            return $this->socioeconomicoModel->searchAtendidos($term, $limit);
        }
        return [];
    }
    
    /**
     * Aplica filtros aos resultados
     */
    private function applyFilters($results, $filters) {
        return array_filter($results, function($ficha) use ($filters) {
            foreach ($filters as $field => $value) {
                if (!empty($value) && isset($ficha[$field])) {
                    if (stripos($ficha[$field], $value) === false) {
                        return false;
                    }
                }
            }
            return true;
        });
    }
    
    /**
     * Valida dados da ficha
     */
    private function validateFichaData($data, $excludeId = null) {
        // Validar CPF
        if (!empty($data['cpf'])) {
            $cpf = preg_replace('/\D+/', '', $data['cpf']);
            if (!$this->isValidCPF($cpf)) {
                throw new Exception('CPF inválido');
            }
        }
        
        // Validar datas
        if (!empty($data['data_nascimento'])) {
            if (!$this->isValidDate($data['data_nascimento'])) {
                throw new Exception('Data de nascimento inválida');
            }
        }
        
        // Validar CEP
        if (!empty($data['cep'])) {
            $cep = preg_replace('/\D+/', '', $data['cep']);
            if (strlen($cep) !== 8) {
                throw new Exception('CEP deve ter 8 dígitos');
            }
        }
        
        // Validar telefones
        $telefoneFields = ['telefone', 'celular', 'contato_emergencia'];
        foreach ($telefoneFields as $field) {
            if (!empty($data[$field])) {
                $telefone = preg_replace('/\D+/', '', $data[$field]);
                if (strlen($telefone) < 10 || strlen($telefone) > 11) {
                    throw new Exception("$field deve ter 10 ou 11 dígitos");
                }
            }
        }
        
        // Validar valores monetários
        $rendaFields = [];
        for ($i = 1; $i <= 10; $i++) {
            $rendaFields[] = "renda_membro_$i";
        }
        
        foreach ($rendaFields as $field) {
            if (!empty($data[$field])) {
                $valor = str_replace(['.', ','], ['', '.'], $data[$field]);
                if (!is_numeric($valor) || floatval($valor) < 0) {
                    throw new Exception("$field deve ser um valor monetário válido");
                }
            }
        }
    }
    
    /**
     * Valida CPF
     */
    private function isValidCPF($cpf) {
        $cpf = preg_replace('/\D+/', '', $cpf);
        
        if (strlen($cpf) !== 11) {
            return false;
        }
        
        // Verificar sequências iguais
        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }
        
        return true; // Validação simplificada
    }
    
    /**
     * Valida data no formato dd/mm/aaaa
     */
    private function isValidDate($date) {
        $parts = explode('/', $date);
        if (count($parts) !== 3) {
            return false;
        }
        
        $day = intval($parts[0]);
        $month = intval($parts[1]);
        $year = intval($parts[2]);
        
        return checkdate($month, $day, $year);
    }
    
    /**
     * Obtém estatísticas
     */
    public function getStatistics() {
        return $this->socioeconomicoModel->getStatistics();
    }
    
    /**
     * Gera relatório socioeconômico
     */
    public function generateReport($filters = []) {
        $fichas = $this->socioeconomicoModel->findAll();
        
        if (!empty($filters)) {
            $fichas = $this->applyFilters($fichas, $filters);
        }
        
        $fichas = array_map(fn($ficha) => $this->enrichFicha($ficha), $fichas);
        
        $report = [
            'total_fichas' => count($fichas),
            'situacoes' => [
                'Extrema Pobreza' => 0,
                'Pobreza' => 0,
                'Baixa Renda' => 0,
                'Média Renda' => 0,
                'Alta Renda' => 0
            ],
            'faixa_etaria' => [
                'Criança (0-11)' => 0,
                'Adolescente (12-17)' => 0,
                'Adulto (18+)' => 0
            ],
            'renda_media' => 0,
            'membros_media' => 0
        ];
        
        $rendaTotal = 0;
        $membrosTotal = 0;
        $contadorRenda = 0;
        
        foreach ($fichas as $fichaRaw) {
            $ficha = $this->enrichFicha($fichaRaw);
            // Situação econômica
            $rendaFamiliar = floatval($ficha['renda_familiar'] ?? 0);
            $numeroMembros = intval($ficha['numero_membros'] ?? $ficha['qtd_pessoas'] ?? 1);
            $situacao = $this->categorizeSituacao($rendaFamiliar, $numeroMembros);
            
            if (isset($report['situacoes'][$situacao])) {
                $report['situacoes'][$situacao]++;
            }
            
            // Faixa etária
            $idade = $this->calculateAge($ficha['data_nascimento'] ?? '');
            if ($idade !== null) {
                if ($idade < 12) {
                    $report['faixa_etaria']['Criança (0-11)']++;
                } elseif ($idade < 18) {
                    $report['faixa_etaria']['Adolescente (12-17)']++;
                } else {
                    $report['faixa_etaria']['Adulto (18+)']++;
                }
            }
            
            // Médias
            if ($rendaFamiliar > 0) {
                $rendaTotal += $rendaFamiliar;
                $contadorRenda++;
            }
            
            $membrosTotal += $numeroMembros;
        }
        
        if ($contadorRenda > 0) {
            $report['renda_media'] = $rendaTotal / $contadorRenda;
        }
        
        if (count($fichas) > 0) {
            $report['membros_media'] = $membrosTotal / count($fichas);
        }
        
        return $report;
    }
    
    /**
     * Exporta fichas para CSV
     */
    public function exportToCSV($filters = []) {
        $fichas = $this->socioeconomicoModel->findAll();
        
        if (!empty($filters)) {
            $fichas = $this->applyFilters($fichas, $filters);
        }
        
        $csv = "Nome,CPF,RG,Data Nascimento,Idade,Renda Familiar,Situação Econômica,Membros Família,Status\n";
        
        foreach ($fichas as $ficha) {
            $idade = $this->calculateAge($ficha['data_nascimento'] ?? '');
            $rendaFamiliar = floatval($ficha['renda_familiar'] ?? 0);
            $situacao = $this->categorizeSituacao(
                $rendaFamiliar, 
                intval($ficha['numero_membros'] ?? $ficha['qtd_pessoas'] ?? 1)
            );
            
            $csv .= sprintf(
                '"%s","%s","%s","%s","%s","%.2f","%s","%s","%s"' . "\n",
                $ficha['nome_completo'] ?? '',
                $ficha['cpf'] ?? '',
                $ficha['rg'] ?? '',
                $ficha['data_nascimento'] ?? '',
                $idade ?? '',
                $rendaFamiliar,
                $situacao,
                $ficha['numero_membros'] ?? '',
                $ficha['status'] ?? ''
            );
        }
        
        return $csv;
    }

    /**
     * Adiciona campos derivados a uma ficha
     */
    private function enrichFicha($ficha) {
        if (!$ficha) {
            return $ficha;
        }

        $ficha['idade'] = $this->calculateAge($ficha['data_nascimento'] ?? '');

        if (!isset($ficha['renda_familiar']) || $ficha['renda_familiar'] === null) {
            $ficha['renda_familiar'] = $this->calculateRendaFamiliar($ficha);
        }

        $numeroMembros = intval($ficha['numero_membros'] ?? $ficha['qtd_pessoas'] ?? 1);
        $ficha['situacao_economica'] = $this->categorizeSituacao(
            floatval($ficha['renda_familiar'] ?? 0),
            $numeroMembros
        );

        return $ficha;
    }

    private function calculateAge($dataNascimento) {
        if (empty($dataNascimento)) {
            return null;
        }

        if (strpos($dataNascimento, '-') !== false) {
            $date = DateTime::createFromFormat('Y-m-d', $dataNascimento);
        } else {
            $date = DateTime::createFromFormat('d/m/Y', $dataNascimento);
        }

        if (!$date) {
            return null;
        }

        $now = new DateTime();
        return $now->diff($date)->y;
    }

    private function calculateRendaFamiliar($ficha) {
        if (!empty($ficha['renda_familiar'])) {
            return floatval($ficha['renda_familiar']);
        }

        $total = 0;

        if (!empty($ficha['familia_json'])) {
            $familia = json_decode($ficha['familia_json'], true);
            if (is_array($familia)) {
                foreach ($familia as $membro) {
                    if (!empty($membro['renda'])) {
                        $total += floatval(str_replace(['.', ','], ['', '.'], $membro['renda']));
                    }
                }
            }
        }

        if ($total > 0) {
            return $total;
        }

        for ($i = 1; $i <= 10; $i++) {
            $campo = "renda_membro_$i";
            if (!empty($ficha[$campo])) {
                $total += floatval(str_replace(['.', ','], ['', '.'], $ficha[$campo]));
            }
        }

        return $total;
    }

    private function categorizeSituacao($rendaFamiliar, $numeroMembros = 1) {
        $rendaPerCapita = $numeroMembros > 0 ? ($rendaFamiliar / $numeroMembros) : $rendaFamiliar;
        $salarioMinimo = 1320;

        if ($rendaPerCapita < $salarioMinimo * 0.5) {
            return 'Extrema Pobreza';
        }

        if ($rendaPerCapita < $salarioMinimo) {
            return 'Pobreza';
        }

        if ($rendaPerCapita < $salarioMinimo * 2) {
            return 'Baixa Renda';
        }

        if ($rendaPerCapita < $salarioMinimo * 5) {
            return 'Média Renda';
        }

        return 'Alta Renda';
    }
    
    /**
     * Log de ações (MySQL)
     */
    private function logAction($action, $fichaId, $description) {
        try {
            $usuarioId = $_SESSION['user_id'] ?? null;
            $ip = $_SERVER['REMOTE_ADDR'] ?? null;
            $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;
            
            Database::query(
                "INSERT INTO Log_Sistema (nivel, acao, descricao, tabela_afetada, registro_id, usuario_id, ip_address, user_agent, dados_novos)
                 VALUES ('INFO', ?, ?, 'Ficha_Socioeconomico', ?, ?, ?, ?, ?)",
                [
                    strtoupper($action),
                    $description,
                    $fichaId,
                    $usuarioId,
                    $ip,
                    $ua,
                    json_encode(['ficha_id' => $fichaId], JSON_UNESCAPED_UNICODE)
                ]
            );
        } catch (Exception $e) {
            error_log('Erro ao gravar log no MySQL: ' . $e->getMessage());
        }
    }
    
    /**
     * Obtém logs de ações
     */
    public function getLogs($limit = 50) {
        try {
            $stmt = Database::query(
                "SELECT data_hora, nivel, acao, descricao, registro_id, usuario_id, ip_address
                 FROM Log_Sistema
                 WHERE tabela_afetada = 'Ficha_Socioeconomico'
                 ORDER BY data_hora DESC
                 LIMIT ?",
                [(int)$limit]
            );
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log('Erro ao ler logs no MySQL: ' . $e->getMessage());
            return [];
        }
    }
}
