<?php
// Script para verificar e corrigir renda_familiar para todas as fichas
// Ele tenta, em ordem: (1) somar tabela Familia, (2) usar familia_json em Ficha_Socioeconomico, (3) somar campos renda_membro_1..10
// Atualiza Ficha_Socioeconomico quando o valor calculado difere do armazenado.
// Execute: php scripts/recalc_renda_fix_all.php

require_once __DIR__ . '/../bootstrap.php';

echo "Iniciando verificação e correção de renda_familiar...\n";

$db = Database::getConnection();
$log = [];
try {
    // Detectar se existe coluna familia_json na tabela
    $colsStmt = $db->query("SHOW COLUMNS FROM Ficha_Socioeconomico");
    $cols = array_column($colsStmt->fetchAll(PDO::FETCH_ASSOC), 'Field');
    $has_familia_json = in_array('familia_json', $cols);

    // Buscar todas as fichas
    $stmt = $db->query("SELECT idficha, id_atendido, renda_familiar, qtd_pessoas, renda_per_capita FROM Ficha_Socioeconomico");
    $fichas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($fichas as $row) {
        $idficha = $row['idficha'];
        $id_atendido = $row['id_atendido'];
        $stored = floatval($row['renda_familiar'] ?? 0);
        $qtd = intval($row['qtd_pessoas'] ?? 0);

        $calculated = 0;
        $source = null;

        // 1) Somar da tabela Familia
        try {
            $s = $db->prepare("SELECT COALESCE(SUM(renda),0) as total FROM Familia WHERE id_ficha = ?");
            $s->execute([$idficha]);
            $r = $s->fetch(PDO::FETCH_ASSOC);
            $sumFam = floatval($r['total'] ?? 0);
            if ($sumFam > 0) {
                $calculated = $sumFam;
                $source = 'table_Familia';
            }
        } catch (Exception $e) {
            // ignore
        }

        // 2) Se zero e houver familia_json, tentar decodificar e somar
        if ($calculated == 0 && $has_familia_json) {
            try {
                $s = $db->prepare("SELECT familia_json FROM Ficha_Socioeconomico WHERE idficha = ?");
                $s->execute([$idficha]);
                $r = $s->fetch(PDO::FETCH_ASSOC);
                $fj = $r['familia_json'] ?? null;
                if (!empty($fj)) {
                    $decoded = json_decode($fj, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $sum = 0;
                        foreach ($decoded as $m) {
                            $v = $m['renda'] ?? $m['renda_membro'] ?? 0;
                            if (is_string($v)) {
                                $v = str_replace(['R$', ' ', '.'], ['', '', ''], $v);
                                $v = str_replace(',', '.', $v);
                            }
                            $sum += floatval($v);
                        }
                        if ($sum > 0) {
                            $calculated = $sum;
                            $source = 'familia_json';
                        }
                    }
                }
            } catch (Exception $e) {
                // ignore
            }
        }

        // 3) Fallback: somar campos renda_membro_1..10 na própria ficha
        if ($calculated == 0) {
            try {
                $s = $db->prepare("SELECT ");
                // construir select dinâmico para renda_membro_1..10
                $fields = [];
                for ($i=1;$i<=10;$i++) $fields[] = "renda_membro_{$i}";
                $sel = implode(',', $fields);
                $s = $db->prepare("SELECT $sel FROM Ficha_Socioeconomico WHERE idficha = ?");
                $s->execute([$idficha]);
                $r = $s->fetch(PDO::FETCH_ASSOC);
                $sum = 0;
                if ($r) {
                    foreach ($fields as $f) {
                        $v = $r[$f] ?? 0;
                        if (is_string($v)) {
                            $v = str_replace(['R$', ' ', '.'], ['', '', ''], $v);
                            $v = str_replace(',', '.', $v);
                        }
                        $sum += floatval($v);
                    }
                }
                if ($sum > 0) {
                    $calculated = $sum;
                    $source = 'renda_membro_fields';
                }
            } catch (Exception $e) {
                // ignore
            }
        }

        // 4) última tentativa: somar valores em Despesas.valor_renda (alguns podem ter renda ali)
        if ($calculated == 0) {
            try {
                $s = $db->prepare("SELECT COALESCE(SUM(valor_renda),0) as total FROM Despesas WHERE id_ficha = ?");
                $s->execute([$idficha]);
                $r = $s->fetch(PDO::FETCH_ASSOC);
                $sum = floatval($r['total'] ?? 0);
                if ($sum > 0) {
                    $calculated = $sum;
                    $source = 'despesas_valor_renda';
                }
            } catch (Exception $e) {
                // ignore
            }
        }

        // Comparar e atualizar se diferente (com tolerância pequena)
        if (abs($calculated - $stored) > 0.001) {
            $rendaPerCapita = ($qtd > 0) ? ($calculated / max(1, $qtd)) : $calculated;
            try {
                $upd = $db->prepare("UPDATE Ficha_Socioeconomico SET renda_familiar = ?, renda_per_capita = ? WHERE idficha = ?");
                $upd->execute([$calculated, $rendaPerCapita, $idficha]);
                echo "Corrigido idficha={$idficha} (id_atendido={$id_atendido}) stored={$stored} -> calculated={$calculated} source={$source}\n";
                $log[] = ['idficha'=>$idficha,'id_atendido'=>$id_atendido,'old'=>$stored,'new'=>$calculated,'source'=>$source,'qtd'=>$qtd,'timestamp'=>date('c')];
            } catch (Exception $e) {
                echo "Erro atualizando idficha={$idficha}: " . $e->getMessage() . "\n";
                $log[] = ['idficha'=>$idficha,'id_atendido'=>$id_atendido,'old'=>$stored,'error'=>$e->getMessage(),'timestamp'=>date('c')];
            }
        } else {
            // sem alteração
            $log[] = ['idficha'=>$idficha,'id_atendido'=>$id_atendido,'old'=>$stored,'new'=>$calculated,'source'=>$source,'qtd'=>$qtd,'timestamp'=>date('c')];
        }
    }

    // salvar log
    $logDir = defined('DATA_PATH') ? DATA_PATH : __DIR__ . '/../data';
    if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
    $logFile = rtrim($logDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'renda_fix_all_log.json';
    file_put_contents($logFile, json_encode($log, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    echo "Verificação finalizada. Log: {$logFile}\n";
} catch (Exception $e) {
    echo "Erro fatal: " . $e->getMessage() . "\n";
    exit(1);
}

exit(0);
