<?php
/**
 * Test Auth API directly
 */

// Test 1: Check if config loads
echo "<h2>Test 1: Config Check</h2>";
require_once __DIR__ . '/backend/config/config.php';
echo "✅ Config loaded successfully<br>";
echo "DB_HOST: " . DB_HOST . "<br>";
echo "DB_NAME: " . DB_NAME . "<br>";
echo "JWT_SECRET: " . (defined('JWT_SECRET') ? 'Defined' : 'NOT DEFINED') . "<br>";
echo "JWT_EXPIRY: " . (defined('JWT_EXPIRY') ? JWT_EXPIRY : 'NOT DEFINED') . "<br>";

// Test 2: Check database connection
echo "<h2>Test 2: Database Connection</h2>";
try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    echo "✅ Database connected successfully<br>";
    
    // Check users table
    $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "Users count: " . $result['count'] . "<br>";
    
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

// Test 3: Test API endpoint
echo "<h2>Test 3: Test Auth API Endpoint</h2>";
$testUrl = 'http://localhost/quanli-main/backend/api.php?resource=auth&action=me';
echo "Testing URL: " . $testUrl . "<br>";

// Test with cURL
$ch = curl_init($testUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: " . $httpCode . "<br>";
echo "Response: " . htmlspecialchars($response) . "<br>";

// Test 4: Simulate login request
echo "<h2>Test 4: Simulate Login Request</h2>";
$_SERVER['REQUEST_METHOD'] = 'POST';
$_GET['resource'] = 'auth';
$_GET['action'] = 'me';

// Capture output
ob_start();
try {
    require_once __DIR__ . '/backend/controllers/AuthController.php';
    $controller = new AuthController();
    // Don't call handle, just check if class loads
    echo "✅ AuthController class loaded successfully<br>";
} catch (Exception $e) {
    echo "❌ Error loading AuthController: " . $e->getMessage() . "<br>";
}
ob_end_flush();

echo "<h2>✅ All basic tests completed</h2>";
