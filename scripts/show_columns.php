<?php
require_once __DIR__ . '/../app/Config/Database.php';

$db = Database::getConnection();
$result = $db->query('SHOW COLUMNS FROM Ficha_Socioeconomico');
$cols = $result->fetchAll(PDO::FETCH_ASSOC);

echo "=== COLUNAS DE Ficha_Socioeconomico ===\n";
foreach($cols as $col) {
    echo $col['Field'] . "\n";
}
