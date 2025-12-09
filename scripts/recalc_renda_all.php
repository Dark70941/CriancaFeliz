<?php
// Script para recalcular e persistir renda_familiar para todas as fichas
// Execute com: php scripts/recalc_renda_all.php

require_once __DIR__ . '/../bootstrap.php';

echo "Iniciando recalculo de renda_familiar para todas as fichas...\n";

$db = Database::getConnection();
$log = [];

try {
    $stmt = $db->query("SELECT idficha, id_atendido FROM Ficha_Socioeconomico");
    $fichas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($fichas as $row) {
        $idficha = $row['idficha'] ?? null;
        $id_atendido = $row['id_atendido'] ?? null;
        if (empty($idficha)) continue;

        // Somar rendas da tabela Familia
        $sumStmt = $db->prepare("SELECT COALESCE(SUM(renda),0) as total FROM Familia WHERE id_ficha = ?");
        $sumStmt->execute([$idficha]);
        $sumRow = $sumStmt->fetch(PDO::FETCH_ASSOC);
        $familySum = floatval($sumRow['total'] ?? 0);

        // Obter qtd_pessoas atual
        $qtd = 0;
        try {
            $qtdStmt = $db->prepare("SELECT qtd_pessoas FROM Ficha_Socioeconomico WHERE idficha = ?");
            $qtdStmt->execute([$idficha]);
            $qtdRow = $qtdStmt->fetch(PDO::FETCH_ASSOC);
            $qtd = intval($qtdRow['qtd_pessoas'] ?? 0);
        } catch (Exception $e) {
            // ignore
        }

        $rendaPerCapita = ($qtd > 0) ? ($familySum / max(1, $qtd)) : $familySum;

        // Atualizar Ficha_Socioeconomico
        try {
            $upd = $db->prepare("UPDATE Ficha_Socioeconomico SET renda_familiar = ?, renda_per_capita = ? WHERE idficha = ?");
            $upd->execute([$familySum, $rendaPerCapita, $idficha]);
            echo "Atualizado idficha={$idficha} (id_atendido={$id_atendido}) => renda_familiar={$familySum}\n";
            $log[] = [
                'idficha' => $idficha,
                'id_atendido' => $id_atendido,
                'renda_familiar' => $familySum,
                'qtd_pessoas' => $qtd,
                'renda_per_capita' => $rendaPerCapita,
                'timestamp' => date('c')
            ];
        } catch (Exception $e) {
            echo "Erro ao atualizar idficha={$idficha}: " . $e->getMessage() . "\n";
            $log[] = [
                'idficha' => $idficha,
                'id_atendido' => $id_atendido,
                'error' => $e->getMessage(),
                'timestamp' => date('c')
            ];
        }
    }

    // Gravar log
    $logDir = defined('DATA_PATH') ? DATA_PATH : __DIR__ . '/../data';
    if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
    $logFile = rtrim($logDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'renda_fix_log.json';
    file_put_contents($logFile, json_encode($log, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    echo "Recalculo finalizado. Log salvo em: {$logFile}\n";

} catch (Exception $e) {
    echo "Erro fatal: " . $e->getMessage() . "\n";
    exit(1);
}

exit(0);
