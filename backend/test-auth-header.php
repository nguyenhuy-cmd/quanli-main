<?php
// Test file to check Authorization header
header('Content-Type: application/json; charset=utf-8');

echo json_encode([
    'HTTP_AUTHORIZATION' => $_SERVER['HTTP_AUTHORIZATION'] ?? null,
    'Authorization' => $_SERVER['Authorization'] ?? null,
    'REDIRECT_HTTP_AUTHORIZATION' => $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? null,
    'apache_request_headers' => function_exists('apache_request_headers') ? apache_request_headers() : 'Not available',
    'getallheaders' => function_exists('getallheaders') ? getallheaders() : 'Not available',
    'all_SERVER' => $_SERVER
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
