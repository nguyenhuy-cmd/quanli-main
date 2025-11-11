<?php
$_SERVER['REQUEST_METHOD'] = 'GET';
require_once 'config/config.php';

$pdo = Database::getInstance()->getConnection();

$tables = ['attendance', 'leaves'];

foreach ($tables as $table) {
    echo "\n=== Table: $table ===\n";
    try {
        $stmt = $pdo->prepare("DESCRIBE $table");
        $stmt->execute();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo $row['Field'] . " (" . $row['Type'] . ")\n";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
