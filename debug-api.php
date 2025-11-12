<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

$result = [];

try {
    $result['step1'] = 'PHP is working';
    
    // Step 2: Check if config file exists
    if (file_exists('backend/config/config.php')) {
        require_once 'backend/config/config.php';
        $result['step2'] = 'Config loaded';
        $result['db_host'] = DB_HOST;
        $result['db_name'] = DB_NAME;
        $result['db_user'] = DB_USER;
        $result['is_production'] = $isProduction ? 'yes' : 'no';
    } else {
        $result['error'] = 'Config file not found';
        echo json_encode($result);
        exit;
    }
    
    // Step 3: Test database connection
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $result['step3'] = 'Database connected successfully!';
    
    // Step 4: Test a simple query
    $stmt = $conn->query("SELECT DATABASE() as current_db, VERSION() as mysql_version");
    $dbInfo = $stmt->fetch();
    
    $result['step4'] = 'Query successful';
    $result['current_database'] = $dbInfo['current_db'];
    $result['mysql_version'] = $dbInfo['mysql_version'];
    
    // Step 5: Check if tables exist
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $result['step5'] = 'Tables checked';
    $result['table_count'] = count($tables);
    $result['tables'] = $tables;
    
    $result['success'] = true;
    
} catch (Exception $e) {
    $result['success'] = false;
    $result['error'] = $e->getMessage();
    $result['error_file'] = $e->getFile();
    $result['error_line'] = $e->getLine();
}

echo json_encode($result, JSON_PRETTY_PRINT);
?>
