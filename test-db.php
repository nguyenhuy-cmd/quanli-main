<?php
// Simple database connection test for production
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Connection Test</h1>";
echo "<pre>";

// Detect environment
$isProduction = (strpos($_SERVER['HTTP_HOST'], 'huy12345.click') !== false);

if ($isProduction) {
    echo "Environment: PRODUCTION\n\n";
    $host = 'sql209.infinityfree.com';
    $dbname = 'if0_40315513_hrm_db';
    $user = 'if0_40315513';
    $pass = 'Huy140923';
} else {
    echo "Environment: DEVELOPMENT\n\n";
    $host = 'localhost';
    $dbname = 'hrm_system';
    $user = 'root';
    $pass = '';
}

echo "Connecting to:\n";
echo "Host: $host\n";
echo "Database: $dbname\n";
echo "User: $user\n";
echo "Password: " . str_repeat('*', strlen($pass)) . "\n\n";

try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    echo "✅ CONNECTION SUCCESSFUL!\n\n";
    
    // Test query
    $stmt = $pdo->query("SELECT DATABASE() as db, VERSION() as version");
    $result = $stmt->fetch();
    
    echo "Current Database: " . $result['db'] . "\n";
    echo "MySQL Version: " . $result['version'] . "\n\n";
    
    // List tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Tables in database (" . count($tables) . "):\n";
    if (count($tables) > 0) {
        foreach ($tables as $table) {
            echo "  - $table\n";
        }
    } else {
        echo "  (No tables found - you need to import init.sql)\n";
    }
    
} catch (PDOException $e) {
    echo "❌ CONNECTION FAILED!\n\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n";
}

echo "</pre>";
?>
