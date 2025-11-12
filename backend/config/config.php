<?php
/**
 * Database Configuration
 * PDO connection setup for MySQL
 */

// Detect environment
$isProduction = (strpos($_SERVER['HTTP_HOST'], 'infinityfreeapp.com') !== false || 
                 strpos($_SERVER['HTTP_HOST'], '.rf.gd') !== false ||
                 strpos($_SERVER['HTTP_HOST'], '.epizy.com') !== false);

// Database credentials
if ($isProduction) {
    // Production (InfinityFree)
    define('DB_HOST', 'sql209.infinityfree.com');
    define('DB_NAME', 'if0_40315513_hrm_db');
    define('DB_USER', 'if0_40315513');
    define('DB_PASS', 'Huy140923');
} else {
    // Development (XAMPP Local)
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'hrm_system');
    define('DB_USER', 'root');
    define('DB_PASS', '');
}
define('DB_CHARSET', 'utf8mb4');

// Application settings
define('APP_NAME', 'HRM System');
if ($isProduction) {
    define('APP_URL', 'https://huy12345.click');  // ⚠️ THAY ĐỔI URL CỦA BẠN
    define('API_URL', APP_URL . '/backend/api.php');
} else {
    define('APP_URL', 'http://localhost/quanli-main');
    define('API_URL', APP_URL . '/backend/api.php');
}

// Security
define('JWT_SECRET', 'your-secret-key-change-this-in-production');
define('JWT_EXPIRY', 86400); // 24 hours

// CORS settings
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json; charset=utf-8');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Error reporting
if ($isProduction) {
    // Production: Log errors, don't display
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../../error_log.txt');
} else {
    // Development: Display errors
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');

/**
 * Database class
 * Singleton pattern for PDO connection
 */
class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        try {
            // Use DB_HOST constant directly - it will be correct for each environment
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
            error_log('Database connected successfully to: ' . DB_HOST);
            
        } catch (PDOException $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            throw new Exception('Database connection failed: ' . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    // Prevent cloning
    private function __clone() {}

    // Prevent unserialization
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}
