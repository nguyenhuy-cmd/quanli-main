<?php
// Database configuration - nhập đúng theo cPanel
define('DB_HOST', 'localhost');   // MySQL Hostname
define('DB_NAME', 'hrm_db');       // MySQL DB Name
define('DB_USER', 'root');              // MySQL Username
define('DB_PASS', '');   // mật khẩu đăng nhập cPanel/InfinityFree

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
