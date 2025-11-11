<?php
require_once 'backend/config/config.php';

echo "\n=== PERFORMANCE_REVIEWS TABLE ===\n";
$stmt = $pdo->query("DESCRIBE performance_reviews");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
    echo $col['Field'] . " (" . $col['Type'] . ")\n";
}
