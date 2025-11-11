<?php
/**
 * Database Schema Checker
 * Quick script to verify actual column names
 */

require_once __DIR__ . '/backend/config/config.php';

$tables = ['leaves', 'performance_reviews'];

try {
    foreach ($tables as $table) {
        echo "\n========== TABLE: $table ==========\n";
        $stmt = $pdo->query("DESCRIBE $table");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($columns as $col) {
            echo sprintf("%-30s %-15s %s\n", 
                $col['Field'], 
                $col['Type'], 
                $col['Null'] === 'NO' ? 'NOT NULL' : 'NULL'
            );
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
