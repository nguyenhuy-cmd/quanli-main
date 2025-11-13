<?php
/**
 * API Endpoint for Production
 * This file helps bypass InfinityFree anti-bot protection
 * by setting proper headers before any output
 */

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

// Now load the actual API
require_once __DIR__ . '/api.php';

// Flush clean JSON output
ob_end_flush();
