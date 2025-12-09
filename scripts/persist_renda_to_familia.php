<?php
/**
 * Script para persistir renda_familiar em fichas órfãs (sem membros na tabela Familia)
 * Cada ficha sem membros terá um registro genérico criado representando a renda total
 */

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../app/Config/Database.php';

echo "=== PERSISTÊNCIA DE RENDA EM FICHAS ÓRFÃS ===\n\n";

try {
    $db = Database::getConnection();
    
    // Encontrar fichas que:
    // 1. Têm renda_familiar > 0
    // 2. NÃO têm membros na tabela Familia
    
    $stmt = $db->prepare("
        SELECT f.idficha, f.id_atendido, a.nome, f.renda_familiar
        FROM Ficha_Socioeconomico f
        LEFT JOIN Atendido a ON f.id_atendido = a.idatendido
        WHERE f.renda_familiar > 0
        AND f.idficha NOT IN (SELECT DISTINCT id_ficha FROM Familia)
        ORDER BY f.renda_familiar DESC
    ");
    $stmt->execute();
    $fichas_orfas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Fichas órfãs encontradas: " . count($fichas_orfas) . "\n\n";
    
    if (empty($fichas_orfas)) {
        echo "✅ Nenhuma ficha órfã com renda > 0. Trabalho concluído!\n";
        exit;
    }
    
    // Para cada ficha órfã, criar um registro genérico na tabela Familia
    $criados = 0;
    $erros = 0;
    
    foreach ($fichas_orfas as $ficha) {
        try {
            $stmt = $db->prepare("
                INSERT INTO Familia (id_ficha, nome, parentesco, renda)
                VALUES (?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $ficha['idficha'],
                'Renda Total da Família',  // Nome genérico
                'Responsável',
                floatval($ficha['renda_familiar'])
            ]);
            
            $criados++;
            echo sprintf(
                "✓ Ficha %2d (%-30s): R$ %.2f → Persistida\n",
                $ficha['idficha'],
                substr($ficha['nome'] ?? 'N/A', 0, 30),
                $ficha['renda_familiar']
            );
            
        } catch (Exception $e) {
            $erros++;
            echo sprintf(
                "✗ Ficha %2d: Erro - %s\n",
                $ficha['idficha'],
                $e->getMessage()
            );
        }
    }
    
    echo "\n=== RESULTADO ===\n";
    echo "Criados com sucesso: $criados\n";
    echo "Erros: $erros\n";
    
    // Salvar log
    $log = [
        'timestamp' => date('Y-m-d H:i:s'),
        'fichas_orfas_total' => count($fichas_orfas),
        'criados' => $criados,
        'erros' => $erros,
        'fichas' => $fichas_orfas
    ];
    
    file_put_contents(
        __DIR__ . '/../data/persistencia_renda_log.json',
        json_encode($log, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );
    
    echo "\n✅ Log salvo em: data/persistencia_renda_log.json\n";
    
} catch (Exception $e) {
    echo "❌ Erro fatal: " . $e->getMessage() . "\n";
}
