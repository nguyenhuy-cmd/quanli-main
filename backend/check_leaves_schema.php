<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = getPDO();
    
    // Get table structure
    $stmt = $pdo->query("DESCRIBE leaves");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get sample data
    $stmt = $pdo->query("SELECT * FROM leaves LIMIT 5");
    $sampleData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'columns' => $columns,
        'sample_data' => $sampleData
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch(Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
