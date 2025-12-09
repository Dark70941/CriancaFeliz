<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../app/Config/Database.php';

echo "=== ANÁLISE DE DADOS DA TABELA FAMILIA ===\n\n";

try {
    $db = Database::getConnection();
    
    // 1. Contar fichas totais
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM Ficha_Socioeconomico");
    $stmt->execute();
    $totalFichas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // 2. Contar fichas que TEM membros na familia
    $stmt = $db->prepare("SELECT COUNT(DISTINCT id_ficha) as total FROM Familia");
    $stmt->execute();
    $fichasComFamilia = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // 3. Contar fichas que NÃO TEM membros na familia
    $fichasSemFamilia = $totalFichas - $fichasComFamilia;
    
    echo "Total de Fichas: $totalFichas\n";
    echo "Fichas COM membros na Familia: $fichasComFamilia\n";
    echo "Fichas SEM membros na Familia: $fichasSemFamilia\n\n";
    
    // 4. Listar fichas sem família
    if ($fichasSemFamilia > 0) {
        echo "=== FICHAS SEM MEMBROS NA TABELA FAMILIA (primeiras 20) ===\n";
        $stmt = $db->prepare("
            SELECT f.idficha, f.id_atendido, a.nome, f.renda_familiar
            FROM Ficha_Socioeconomico f
            LEFT JOIN Atendido a ON f.id_atendido = a.idatendido
            WHERE f.idficha NOT IN (SELECT DISTINCT id_ficha FROM Familia)
            ORDER BY f.idficha
            LIMIT 20
        ");
        $stmt->execute();
        $fichas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($fichas as $row) {
            echo sprintf(
                "  idficha=%2d | id_atendido=%2d | nome: %-30s | renda_stored: %.2f\n",
                $row['idficha'],
                $row['id_atendido'],
                substr($row['nome'] ?? '', 0, 30),
                $row['renda_familiar'] ?? 0
            );
        }
    }
    
    // 5. Estatísticas de renda
    echo "\n=== ESTATÍSTICAS DE RENDA ===\n";
    
    // Fichas com renda > 0
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM Ficha_Socioeconomico WHERE renda_familiar > 0");
    $stmt->execute();
    $comRenda = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Fichas com renda = 0
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM Ficha_Socioeconomico WHERE renda_familiar = 0 OR renda_familiar IS NULL");
    $stmt->execute();
    $semRenda = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo "Fichas com renda_familiar > 0: $comRenda\n";
    echo "Fichas com renda_familiar = 0 ou NULL: $semRenda\n\n";
    
    // 6. Comparar: fichas que tem membros na Familia mas renda_familiar = 0
    echo "=== FICHAS COM MEMBROS NA FAMILIA MAS renda_familiar = 0 ===\n";
    $stmt = $db->prepare("
        SELECT DISTINCT f.idficha, f.id_atendido, a.nome, f.renda_familiar,
               COUNT(fam.id_familia) as qtd_membros,
               SUM(fam.renda) as soma_renda_familia
        FROM Ficha_Socioeconomico f
        LEFT JOIN Atendido a ON f.id_atendido = a.idatendido
        INNER JOIN Familia fam ON f.idficha = fam.id_ficha
        WHERE f.renda_familiar = 0 OR f.renda_familiar IS NULL
        GROUP BY f.idficha, f.id_atendido, a.nome, f.renda_familiar
        ORDER BY qtd_membros DESC
        LIMIT 15
    ");
    $stmt->execute();
    $fichas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($fichas)) {
        echo "  Nenhuma ficha encontrada\n";
    } else {
        foreach ($fichas as $row) {
            echo sprintf(
                "  idficha=%2d | nome: %-25s | stored: %.2f | qtd_membros: %d | soma_familia: %.2f\n",
                $row['idficha'],
                substr($row['nome'] ?? '', 0, 25),
                $row['renda_familiar'] ?? 0,
                $row['qtd_membros'] ?? 0,
                $row['soma_renda_familia'] ?? 0
            );
        }
    }
    
    // 7. Comparar: fichas que tem membros com renda > 0 mas renda_familiar diferente
    echo "\n=== FICHAS COM DISCREPÂNCIA ENTRE FAMILIA.renda E renda_familiar ===\n";
    $stmt = $db->prepare("
        SELECT f.idficha, f.id_atendido, a.nome, f.renda_familiar,
               SUM(fam.renda) as soma_familia
        FROM Ficha_Socioeconomico f
        LEFT JOIN Atendido a ON f.id_atendido = a.idatendido
        INNER JOIN Familia fam ON f.idficha = fam.id_ficha
        WHERE f.renda_familiar != SUM(fam.renda)
        GROUP BY f.idficha, f.id_atendido, a.nome, f.renda_familiar
        HAVING f.renda_familiar != SUM(fam.renda)
        ORDER BY ABS(f.renda_familiar - SUM(fam.renda)) DESC
        LIMIT 15
    ");
    $stmt->execute();
    $fichas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($fichas)) {
        echo "  Nenhuma discrepância encontrada\n";
    } else {
        foreach ($fichas as $row) {
            $diff = ($row['renda_familiar'] ?? 0) - ($row['soma_familia'] ?? 0);
            echo sprintf(
                "  idficha=%2d | nome: %-25s | stored: %.2f | familia_sum: %.2f | diff: %+.2f\n",
                $row['idficha'],
                substr($row['nome'] ?? '', 0, 25),
                $row['renda_familiar'] ?? 0,
                $row['soma_familia'] ?? 0,
                $diff
            );
        }
    }
    
    echo "\n✅ Análise concluída.\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
