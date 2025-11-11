<?php
// Database configuration
// - If you're developing locally with XAMPP, use the local DB settings below.
// - If deploying to a hosting provider (e.g. InfinityFree), replace the values
//   with the credentials from your hosting control panel.
// Local (XAMPP) defaults:
define('DB_HOST', '127.0.0.1');   // MySQL Hostname (local)
define('DB_NAME', 'hrm_db');       // MySQL DB Name
define('DB_USER', 'root');              // MySQL Username (local)
define('DB_PASS', '');   // MySQL password (local XAMPP usually empty)

// Production / hosting example (commented) - replace with real values when used:
// define('DB_HOST', 'sql209.infinityfree.com');
// define('DB_NAME', 'if0_40315513_hrm_db');
// define('DB_USER', 'if0_40315513');
// define('DB_PASS', 'your_infintyfree_password');

function getPDO(){
    static $pdo = null;
    if($pdo) return $pdo;
    $dsn = 'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4';
    try{
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $pdo;
    }catch(PDOException $e){
        http_response_code(500);
        echo json_encode(['error'=>'DB connection failed: '.$e->getMessage()]);
        exit;
    }
}
