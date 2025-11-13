<?php
/**
 * API Endpoint for Production
 * This file helps bypass InfinityFree anti-bot protection
 * by setting proper headers before any output
 */

// Set error handler to catch all errors
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    // Clear any output
    if (ob_get_level()) ob_clean();
    
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $errstr,
        'error' => [
            'file' => basename($errfile),
            'line' => $errline
        ]
    ]);
    exit;
});

// Set exception handler
set_exception_handler(function($exception) {
    // Clear any output
    if (ob_get_level()) ob_clean();
    
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'message' => 'Server exception: ' . $exception->getMessage(),
        'error' => [
            'file' => basename($exception->getFile()),
            'line' => $exception->getLine()
        ]
    ]);
    exit;
});

// Start output buffering FIRST
ob_start();

// Remove any previous headers
header_remove();

// Set JSON content type IMMEDIATELY
header('Content-Type: application/json; charset=utf-8', true);
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-cache, must-revalidate');

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Clean any output buffer that might have HTML
if (ob_get_level()) {
    ob_clean();
}

try {
    // Now load the actual API
    require_once __DIR__ . '/api.php';
} catch (Exception $e) {
    // Clear buffer
    if (ob_get_level()) ob_clean();
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'API Error: ' . $e->getMessage()
    ]);
}

// Flush clean JSON output
ob_end_flush();
