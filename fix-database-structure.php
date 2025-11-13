<?php
/**
 * Fix Database Structure on Hosting
 * This script will check and repair the database structure
 */

require_once __DIR__ . '/backend/config/config.php';

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>
<html lang='vi'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Fix Database Structure</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 3px solid #007bff; padding-bottom: 10px; }
        .section { margin: 20px 0; padding: 20px; background: #f8f9fa; border-left: 4px solid #007bff; border-radius: 5px; }
        .success { background: #d4edda; border-color: #28a745; color: #155724; }
        .error { background: #f8d7da; border-color: #dc3545; color: #721c24; }
        .warning { background: #fff3cd; border-color: #ffc107; color: #856404; }
        pre { background: #2d2d30; color: #d4d4d4; padding: 15px; border-radius: 5px; overflow-x: auto; }
        button { background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; margin: 5px; }
        button:hover { background: #0056b3; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 10px; text-align: left; border: 1px solid #ddd; }
        th { background: #007bff; color: white; }
    </style>
</head>
<body>
<div class='container'>
<h1>🔧 Fix Database Structure</h1>";

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    echo "<div class='section success'>✅ Connected to database: <strong>" . DB_NAME . "</strong></div>";
    
    // Get current tables
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<div class='section'>";
    echo "<h2>📊 Current Tables (" . count($tables) . ")</h2>";
    echo "<table><tr><th>#</th><th>Table Name</th><th>Status</th></tr>";
    
    $requiredTables = [
        'users', 'employees', 'departments', 'positions', 
        'salaries', 'attendance', 'leaves', 'performance_reviews'
    ];
    
    $missingTables = [];
    $i = 1;
    foreach ($requiredTables as $tableName) {
        $exists = in_array($tableName, $tables);
        echo "<tr>";
        echo "<td>$i</td>";
        echo "<td><strong>$tableName</strong></td>";
        echo "<td>" . ($exists ? "✅ Exists" : "❌ Missing") . "</td>";
        echo "</tr>";
        if (!$exists) $missingTables[] = $tableName;
        $i++;
    }
    echo "</table>";
    
    if (count($missingTables) > 0) {
        echo "<div class='warning'>⚠️ Missing tables: " . implode(', ', $missingTables) . "</div>";
    }
    echo "</div>";
    
    // Check employees table structure
    if (in_array('employees', $tables)) {
        echo "<div class='section'>";
        echo "<h2>👥 Employees Table Structure</h2>";
        
        $stmt = $conn->query("DESCRIBE employees");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        foreach ($columns as $col) {
            echo "<tr>";
            echo "<td><strong>{$col['Field']}</strong></td>";
            echo "<td>{$col['Type']}</td>";
            echo "<td>{$col['Null']}</td>";
            echo "<td>{$col['Key']}</td>";
            echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Check for missing columns
        $existingCols = array_column($columns, 'Field');
        $requiredCols = ['id', 'user_id', 'employee_code', 'full_name', 'email', 'phone', 
                         'date_of_birth', 'gender', 'address', 'hire_date', 'employment_status',
                         'department_id', 'position_id'];
        
        $missingCols = array_diff($requiredCols, $existingCols);
        
        if (count($missingCols) > 0) {
            echo "<div class='error'><strong>❌ Missing columns:</strong> " . implode(', ', $missingCols) . "</div>";
        } else {
            echo "<div class='success'>✅ All required columns exist!</div>";
        }
        echo "</div>";
    }
    
    // SQL to recreate all tables
    echo "<div class='section'>";
    echo "<h2>🔨 Fix Options</h2>";
    echo "<p><strong>Option 1:</strong> Download the SQL file and import manually in phpMyAdmin</p>";
    echo "<a href='backend/init.sql' download><button>📥 Download init.sql</button></a>";
    
    echo "<p><strong>Option 2:</strong> Run fix automatically (will DROP existing tables!)</p>";
    echo "<form method='post'>";
    echo "<input type='hidden' name='action' value='fix'>";
    echo "<button type='submit' style='background: #dc3545;' onclick='return confirm(\"This will DROP all tables and recreate them. All data will be lost! Continue?\")'>⚠️ Drop & Recreate All Tables</button>";
    echo "</form>";
    echo "</div>";
    
    // Handle fix action
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'fix') {
        echo "<div class='section'>";
        echo "<h2>🔄 Executing Fix...</h2>";
        
        // Read and execute init.sql
        $sqlFile = __DIR__ . '/backend/init.sql';
        if (file_exists($sqlFile)) {
            $sql = file_get_contents($sqlFile);
            
            // Remove CREATE DATABASE and USE statements for hosting
            $sql = preg_replace('/CREATE DATABASE.*?;/i', '', $sql);
            $sql = preg_replace('/USE .*?;/i', '', $sql);
            
            // Split by semicolon and execute each statement
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            
            $success = 0;
            $errors = 0;
            
            foreach ($statements as $statement) {
                if (empty($statement)) continue;
                
                try {
                    $conn->exec($statement);
                    $success++;
                    echo "<div class='success'>✅ Executed: " . substr($statement, 0, 50) . "...</div>";
                } catch (PDOException $e) {
                    $errors++;
                    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
                    echo "<pre>" . htmlspecialchars($statement) . "</pre>";
                }
            }
            
            echo "<div class='section " . ($errors > 0 ? 'warning' : 'success') . "'>";
            echo "<h3>Summary</h3>";
            echo "<p>✅ Success: $success statements</p>";
            echo "<p>❌ Errors: $errors statements</p>";
            if ($errors == 0) {
                echo "<p><strong>🎉 Database structure fixed successfully!</strong></p>";
                echo "<p><a href='index.html'><button>Go to Application</button></a></p>";
            }
            echo "</div>";
            
        } else {
            echo "<div class='error'>❌ init.sql file not found!</div>";
        }
        
        echo "</div>";
    }
    
    // Show SQL preview
    echo "<div class='section'>";
    echo "<h2>📄 SQL Preview (from init.sql)</h2>";
    $sqlFile = __DIR__ . '/backend/init.sql';
    if (file_exists($sqlFile)) {
        $sql = file_get_contents($sqlFile);
        echo "<pre>" . htmlspecialchars(substr($sql, 0, 2000)) . "\n\n... (showing first 2000 characters)</pre>";
    } else {
        echo "<div class='error'>❌ init.sql file not found at: $sqlFile</div>";
    }
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='section error'>";
    echo "<h2>❌ Error</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "</div></body></html>";
