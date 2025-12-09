<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../app/Config/Database.php';

echo "Iniciando diagnóstico completo de renda_familiar...\n";

try {
    // Obter conexão do banco de dados
    $db = Database::getConnection();
    
    // Buscar todas as fichas
    $query = "SELECT 
                f.idficha,
                f.id_atendido,
                a.nome,
                f.renda_familiar AS stored_renda
            FROM Ficha_Socioeconomico f
            LEFT JOIN Atendido a ON a.idatendido = f.id_atendido
            ORDER BY f.idficha";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $fichas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $report = [];
    
    foreach ($fichas as $ficha) {
        $idficha = $ficha['idficha'];
        $id_atendido = $ficha['id_atendido'];
        $nome = $ficha['nome'] ?? '(sem nome)';
        $stored_renda = floatval($ficha['stored_renda'] ?? 0);
        
        $sources = [];
        
        // Fonte 1: Tabela Familia (prioridade máxima)
        $famQuery = "SELECT SUM(CAST(REPLACE(REPLACE(renda, '.', ''), ',', '.') AS DECIMAL(10,2))) as total,
                            COUNT(*) as qtd_membros
                     FROM Familia
                     WHERE idficha = ?";
        $famStmt = $db->prepare($famQuery);
        $famStmt->execute([$idficha]);
        $famResult = $famStmt->fetch(PDO::FETCH_ASSOC);
        $familia_sum = floatval($famResult['total'] ?? 0);
        $qtd_membros = intval($famResult['qtd_membros'] ?? 0);
        if ($familia_sum > 0 || $qtd_membros > 0) {
            $sources['Familia_table'] = [
                'valor' => $familia_sum,
                'qtd_membros' => $qtd_membros
            ];
        }
        
        // Fonte 2: Despesas.valor_renda (fallback)
        $despQuery = "SELECT valor_renda FROM Despesas WHERE idficha = ? LIMIT 1";
        $despStmt = $db->prepare($despQuery);
        $despStmt->execute([$idficha]);
        $despResult = $despStmt->fetch(PDO::FETCH_ASSOC);
        $desp_renda = floatval($despResult['valor_renda'] ?? 0);
        if ($desp_renda > 0) {
            $sources['Despesas_valor_renda'] = $desp_renda;
        }
        
        // Calcular renda esperada (prioridade: Familia > Despesas)
        $expected_renda = 0;
        $best_source = null;
        if (!empty($sources['Familia_table']) && $sources['Familia_table']['valor'] > 0) {
            $expected_renda = $sources['Familia_table']['valor'];
            $best_source = 'Familia_table';
        } elseif (!empty($sources['Despesas_valor_renda'])) {
            $expected_renda = $sources['Despesas_valor_renda'];
            $best_source = 'Despesas_valor_renda';
        }
        
        $report[] = [
            'idficha' => $idficha,
            'id_atendido' => $id_atendido,
            'nome' => $nome,
            'stored_renda' => $stored_renda,
            'expected_renda' => $expected_renda,
            'best_source' => $best_source,
            'discrepancia' => abs($expected_renda - $stored_renda) > 0.01,
            'all_sources' => $sources,
            'qtd_fontes' => count($sources)
        ];
    }
    
    // Salvar relatório completo
    file_put_contents(
        __DIR__ . '/../data/diagnostico_renda_completo.json',
        json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );
    
    // Mostrar resumo no console
    echo "\n=== RESUMO DO DIAGNÓSTICO ===\n";
    echo "Total de fichas analisadas: " . count($report) . "\n";
    
    $com_discrepancia = array_filter($report, fn($r) => $r['discrepancia']);
    echo "Fichas com DISCREPÂNCIA (expected ≠ stored): " . count($com_discrepancia) . "\n";
    
    $sem_fonte = array_filter($report, fn($r) => empty($r['all_sources']));
    echo "Fichas SEM fonte de renda (sem Familia, sem Despesas): " . count($sem_fonte) . "\n\n";
    
    if (!empty($com_discrepancia)) {
        echo "=== TOP 20 DISCREPÂNCIAS (maior diferença) ===\n";
        usort($com_discrepancia, fn($a, $b) => 
            abs($b['expected_renda'] - $b['stored_renda']) <=> abs($a['expected_renda'] - $a['stored_renda'])
        );
        
        foreach (array_slice($com_discrepancia, 0, 20) as $r) {
            $diff = $r['expected_renda'] - $r['stored_renda'];
            echo sprintf(
                "\nidficha=%2d | id_atendido=%2d | nome: %-25s\n",
                $r['idficha'],
                $r['id_atendido'],
                substr($r['nome'], 0, 25)
            );
            echo sprintf(
                "  Armazenado: %.2f | Esperado: %.2f | Diferença: %+.2f\n",
                $r['stored_renda'],
                $r['expected_renda'],
                $diff
            );
            if (!empty($r['all_sources'])) {
                echo "  Fontes encontradas:\n";
                foreach ($r['all_sources'] as $src => $data) {
                    if (is_array($data)) {
                        echo sprintf("    • %s: %.2f (qtd membros: %d)\n", $src, $data['valor'], $data['qtd_membros']);
                    } else {
                        echo sprintf("    • %s: %.2f\n", $src, $data);
                    }
                }
            } else {
                echo "  ❌ Nenhuma fonte de renda encontrada\n";
            }
        }
    }
    
    if (!empty($sem_fonte)) {
        echo "\n=== FICHAS SEM FONTE DE RENDA (primeiras 15) ===\n";
        foreach (array_slice($sem_fonte, 0, 15) as $r) {
            echo sprintf(
                "idficha=%2d | id_atendido=%2d | nome: %-25s | stored: %.2f\n",
                $r['idficha'],
                $r['id_atendido'],
                substr($r['nome'], 0, 25),
                $r['stored_renda']
            );
        }
    }
    
    echo "\n✅ Relatório completo salvo em: " . __DIR__ . "/../data/diagnostico_renda_completo.json\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    error_log($e->getMessage());
}
