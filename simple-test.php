<?php
// Super simple test - no includes
ini_set('max_execution_time', 5);
ini_set('default_socket_timeout', 5);

echo "Test 1: PHP OK<br>\n";

try {
    $pdo = new PDO('mysql:host=127.0.0.1:3306;dbname=hrm_system', 'root', '', [
        PDO::ATTR_TIMEOUT => 3,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "Test 2: PDO Connected<br>\n";
    
    $stmt = $pdo->query("SELECT * FROM users WHERE email='admin@hrm.com'");
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "Test 3: User found<br>\n";
        echo "Email: " . $user['email'] . "<br>\n";
        echo "Name: " . $user['name'] . "<br>\n";
    } else {
        echo "Test 3: User NOT found<br>\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "<br>\n";
}
?>
