<?php
/**
 * Database Configuration
 * PDO connection setup for MySQL
 */

// Database credentials
define('DB_HOST', 'localhost');       // Use localhost (will use pipe/socket)
define('DB_NAME', 'hrm_system');      // Local database
define('DB_USER', 'root');            // XAMPP default user
define('DB_PASS', '');                // XAMPP default: no password
define('DB_CHARSET', 'utf8mb4');

// Application settings
define('APP_NAME', 'HRM System');
define('APP_URL', 'http://localhost/quanli-main');
define('API_URL', APP_URL . '/backend/api.php');

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

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
            // Set default socket timeout to avoid hanging
            ini_set('default_socket_timeout', 2);
            ini_set('mysql.connect_timeout', 2);
            
            // Try different connection methods
            $connectionAttempts = [
                // Method 1: Direct IP without port in DSN
                "mysql:host=127.0.0.1;dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
                // Method 2: Localhost
                "mysql:host=localhost;dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
            ];
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 2,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];
            
            $lastError = null;
            foreach ($connectionAttempts as $dsn) {
                try {
                    $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
                    // If successful, break the loop
                    error_log('Database connected using: ' . $dsn);
                    return;
                } catch (PDOException $e) {
                    $lastError = $e;
                    continue;
                }
            }
            
            // If all attempts failed, throw the last error
            throw $lastError;
            
        } catch (PDOException $e) {
            // Log error instead of die
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
