<?php
/**
 * API Router
 * Routes requests to appropriate controllers
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load config
require_once __DIR__ . '/config/config.php';

// Get resource and action from query string
$resource = $_GET['resource'] ?? '';
$action = $_GET['action'] ?? '';

try {
    // Route to appropriate controller
    switch ($resource) {
        case 'auth':
            require_once __DIR__ . '/controllers/AuthController.php';
            $controller = new AuthController();
            $controller->handle();
            break;
        
        case 'employees':
            require_once __DIR__ . '/controllers/EmployeeController.php';
            $controller = new EmployeeController();
            $controller->handle();
            break;
        
        case 'departments':
            require_once __DIR__ . '/controllers/DepartmentController.php';
            $controller = new DepartmentController();
            $controller->handle();
            break;
        
        case 'positions':
            require_once __DIR__ . '/controllers/PositionController.php';
            $controller = new PositionController();
            $controller->handle();
            break;
        
        case 'salaries':
            require_once __DIR__ . '/controllers/SalaryController.php';
            $controller = new SalaryController();
            $controller->handle();
            break;
        
        case 'attendance':
            require_once __DIR__ . '/controllers/AttendanceController.php';
            $controller = new AttendanceController();
            $controller->handle();
            break;
        
        case 'leaves':
            require_once __DIR__ . '/controllers/LeaveController.php';
            $controller = new LeaveController();
            $controller->handle();
            break;
        
        case 'performance':
            require_once __DIR__ . '/controllers/PerformanceController.php';
            $controller = new PerformanceController();
            $controller->handle();
            break;
        
        case 'dashboard':
            require_once __DIR__ . '/controllers/DashboardController.php';
            $controller = new DashboardController();
            $controller->handle();
            break;
        
        default:
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Resource not found'
            ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
