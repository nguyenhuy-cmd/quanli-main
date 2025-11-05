<?php

// Simple API front controller - routes requests to controllers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if($_SERVER['REQUEST_METHOD']==='OPTIONS'){ exit; }

// parse resource and optional action (e.g. resource=auth/login)
$rawResource = $_GET['resource'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];
$resource = $rawResource;
$action = null;
if(strpos($rawResource, '/') !== false){
    [$resourceRoot, $rest] = explode('/', $rawResource, 2);
    $resource = $resourceRoot;
    // expose action to controllers via GET param for backward compatibility
    $_GET['action'] = $rest;
    $action = $rest;
}else{
    $_GET['action'] = $_GET['action'] ?? null;
}

$body = null;
if(in_array($method,['POST','PUT','DELETE'])){
    $raw = file_get_contents('php://input');
    $body = json_decode($raw, true) ?: [];
}

// --- Auth middleware: require token for protected resources (all except auth)
require_once __DIR__ . '/config.php';
// read bearer token
function getBearerToken(){
    $headers = null;
    
    // Try multiple sources for Authorization header
    if(isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $headers = $_SERVER['HTTP_AUTHORIZATION'];
    } elseif(isset($_SERVER['Authorization'])) {
        $headers = $_SERVER['Authorization'];
    } elseif(isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $headers = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    } elseif(function_exists('apache_request_headers')) {
        // Use apache_request_headers if available
        $apacheHeaders = apache_request_headers();
        if(isset($apacheHeaders['Authorization'])) {
            $headers = $apacheHeaders['Authorization'];
        } elseif(isset($apacheHeaders['authorization'])) {
            $headers = $apacheHeaders['authorization'];
        }
    }
    
    // Also try getallheaders() as fallback
    if(!$headers && function_exists('getallheaders')) {
        $allHeaders = getallheaders();
        if(isset($allHeaders['Authorization'])) {
            $headers = $allHeaders['Authorization'];
        } elseif(isset($allHeaders['authorization'])) {
            $headers = $allHeaders['authorization'];
        }
    }
    
    if(!$headers) return null;
    if(preg_match('/Bearer\s+(\S+)/i', $headers, $m)) return $m[1];
    return null;
}

// if resource is not auth or logs, validate token
// allow unauthenticated logging to `logs` so frontend can report errors
if($resource !== 'auth' && $resource !== 'logs'){
    $token = getBearerToken();
    if(!$token){
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error'=>'Yêu cầu đăng nhập: token bị thiếu']);
        exit;
    }
    // validate token in DB (also check expiry)
    try{
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT u.id as user_id, u.email, t.expires_at FROM tokens t JOIN users u ON u.id = t.user_id WHERE t.token = :token LIMIT 1");
        $stmt->execute([':token'=>$token]);
        $u = $stmt->fetch();
        if(!$u){
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error'=>'Yêu cầu đăng nhập: token không hợp lệ']);
            exit;
        }
        // check expiry if present
        if(!empty($u['expires_at']) && strtotime($u['expires_at']) < time()){
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error'=>'Phiên đăng nhập đã hết hạn']);
            exit;
        }
        // expose current user id for controllers
        define('CURRENT_USER_ID', $u['user_id']);
        define('CURRENT_USER_EMAIL', $u['email']);
    }catch(PDOException $e){
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error'=>'Server error: '.$e->getMessage()]);
        exit;
    }
}

// router
switch(true){
    case preg_match('#^employees$#', $resource):
        require_once __DIR__ . '/controllers/EmployeeController.php';
        $ctrl = new EmployeeController();
        $id = $_GET['id'] ?? null;
        $ctrl->handle($method, $id, $body);
        break;
    case preg_match('#^departments$#', $resource):
        require_once __DIR__ . '/controllers/DepartmentController.php';
        $ctrl = new DepartmentController();
        $ctrl->handle($method, null, $body);
        break;
    case preg_match('#^positions$#', $resource):
        require_once __DIR__ . '/controllers/PositionController.php';
        $ctrl = new PositionController();
        $ctrl->handle($method, null, $body);
        break;
    case preg_match('#^auth$#', $resource):
        require_once __DIR__ . '/controllers/AuthController.php';
        $ctrl = new AuthController();
        $ctrl->handle($method, null, $body);
        break;
    case preg_match('#^salaries$#', $resource):
        require_once __DIR__ . '/controllers/SalaryController.php';
        $ctrl = new SalaryController();
        $ctrl->handle($method, null, $body);
        break;
    case preg_match('#^attendance$#', $resource):
        require_once __DIR__ . '/controllers/AttendanceController.php';
        $ctrl = new AttendanceController();
        $ctrl->handle($method, null, $body);
        break;
    case preg_match('#^leaves$#', $resource):
        require_once __DIR__ . '/controllers/LeaveController.php';
        $ctrl = new LeaveController();
        $ctrl->handle($method, null, $body);
        break;
    case preg_match('#^reviews$#', $resource):
        require_once __DIR__ . '/controllers/ReviewController.php';
        $ctrl = new ReviewController();
        $ctrl->handle($method, null, $body);
        break;
    default:
        http_response_code(404);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error'=>'Unknown resource']);
}

    // lightweight logging endpoint (POST only) - write JSON lines to backend/logs/error.log
    if(preg_match('#^logs$#', $resource)){
        // support POST for appending logs and GET for reading recent logs (dev helper)
        if($method === 'POST'){
            $payload = $body ?: json_decode(file_get_contents('php://input'), true) ?: [];
            $entry = [
                'ts' => date('c'),
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'cli',
                'user' => defined('CURRENT_USER_EMAIL') ? CURRENT_USER_EMAIL : null,
                'payload' => $payload
            ];
            // ensure logs directory exists
            @mkdir(__DIR__ . '/logs', 0755, true);
            @file_put_contents(__DIR__ . '/logs/error.log', json_encode($entry, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success'=>true]);
            exit;
        }elseif($method === 'GET'){
            $limit = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 50;
            $path = __DIR__ . '/logs/error.log';
            if(!file_exists($path)){
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([]);
                exit;
            }
            $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
            $tail = array_slice($lines, -$limit);
            $out = array_map(function($l){ return json_decode($l, true); }, $tail);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($out, JSON_UNESCAPED_UNICODE);
            exit;
        }else{
            http_response_code(405);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error'=>'Method not allowed']);
            exit;
        }
    }
