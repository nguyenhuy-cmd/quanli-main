<?php
/**
 * Direct Login Test - No CORS, no complex routing
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

echo json_encode([
    'step' => 1,
    'message' => 'PHP is working'
]) . "\n";

try {
    require_once __DIR__ . '/backend/config/config.php';
    echo json_encode([
        'step' => 2,
        'message' => 'Config loaded'
    ]) . "\n";
    
    require_once __DIR__ . '/backend/models/UserModel.php';
    echo json_encode([
        'step' => 3,
        'message' => 'UserModel loaded'
    ]) . "\n";
    
    $userModel = new UserModel();
    echo json_encode([
        'step' => 4,
        'message' => 'UserModel instantiated'
    ]) . "\n";
    
    $user = $userModel->verifyCredentials('admin@hrm.com', 'password');
    
    if ($user) {
        echo json_encode([
            'step' => 5,
            'success' => true,
            'message' => 'Login successful!',
            'user' => $user
        ]) . "\n";
    } else {
        echo json_encode([
            'step' => 5,
            'success' => false,
            'message' => 'Invalid credentials'
        ]) . "\n";
    }
    
} catch (Exception $e) {
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]) . "\n";
}
