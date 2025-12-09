<?php
require_once __DIR__ . '/../app/Config/Database.php';

$db = Database::getConnection();

echo "=== COLUNAS DE Atendido ===\n";
$result = $db->query('SHOW COLUMNS FROM Atendido');
$cols = $result->fetchAll(PDO::FETCH_ASSOC);
foreach($cols as $col) {
    echo $col['Field'] . "\n";
}

echo "\n=== COLUNAS DE Ficha_Socioeconomico ===\n";
$result = $db->query('SHOW COLUMNS FROM Ficha_Socioeconomico');
$cols = $result->fetchAll(PDO::FETCH_ASSOC);
foreach($cols as $col) {
    echo $col['Field'] . "\n";
}
