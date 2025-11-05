<?php
require_once __DIR__ . '/../models/BaseModel.php';
require_once __DIR__ . '/BaseController.php';

class AuthController extends BaseController {
    private $pdo;
    public function __construct(){
        $this->pdo = getPDO();
    }
    public function handle($method, $id=null, $body=null){
        try{
            if($method==='POST'){
                $path = $_GET['action'] ?? '';
                if($path==='login'){
                    $this->login($body);
                }elseif($path==='register'){
                    $this->register($body);
                }elseif($path==='logout'){
                    $this->logout();
                }
            }
            $this->jsonError('Chức năng chưa được triển khai',501);
        }catch(Exception $e){
            // log and return friendly message
            @file_put_contents(__DIR__ . '/../logs/error.log', date('c') . " - AuthController error: " . $e->getMessage() . "\n", FILE_APPEND);
            $this->jsonError('Lỗi hệ thống, vui lòng thử lại sau', 500);
        }
    }
    private function login($data){
        if(empty($data['email']) || empty($data['password'])){
            $this->jsonError('Thông tin đăng nhập không hợp lệ', 401);
        }
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email'=>$data['email']]);
        $user = $stmt->fetch();
        if(!$user || !password_verify($data['password'],$user['password'])){
            $this->jsonError('Thông tin đăng nhập không hợp lệ',401);
        }
        // Very simple token - in production use JWT or session
        $token = bin2hex(random_bytes(16));
        // persist token
        try{
            // support optional remember flag to set longer expiry
            $expires = null;
            if(!empty($data['remember'])){
                // remember => 30 days
                $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
            }else{
                // default short expiry (1 day)
                $expires = date('Y-m-d H:i:s', strtotime('+1 day'));
            }
            $ins = $this->pdo->prepare("INSERT INTO tokens (user_id, token, expires_at) VALUES (:uid, :token, :expires_at)");
            $ins->execute([':uid'=>$user['id'], ':token'=>$token, ':expires_at'=>$expires]);
        }catch(PDOException $e){
            // non-fatal, continue
        }
        $this->jsonSuccess(['token'=>$token,'user'=>['id'=>$user['id'],'email'=>$user['email']]]);
    }
    private function register($data){
        // basic validation
        $details = [];
    if(empty($data['email'])) $details['email'] = 'Email là bắt buộc';
    if(!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $details['email'] = 'Email không hợp lệ';
    if(empty($data['password'])) $details['password'] = 'Mật khẩu là bắt buộc';
    if(!empty($details)) $this->jsonError('Kiểm tra dữ liệu thất bại', 400, $details);
        try{
            // check if email exists first to avoid duplicate key errors
            $chk = $this->pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
            $chk->execute([':email'=>$data['email']]);
            $exists = $chk->fetch();
            if($exists){
                $this->jsonError('Đăng ký thất bại: email đã tồn tại', 400, ['email'=>'Email đã tồn tại']);
            }

            $stmt = $this->pdo->prepare("INSERT INTO users (email,password,name) VALUES (:email,:password,:name)");
            $hash = password_hash($data['password'], PASSWORD_DEFAULT);
            $stmt->execute([':email'=>$data['email'],':password'=>$hash,':name'=>$data['name']??null]);
            $this->jsonSuccess(['success'=>true],201);
        }catch(PDOException $e){
            // log exception for debugging
            $msg = $e->getMessage();
            @file_put_contents(__DIR__ . '/../logs/error.log', date('c') . " - Register error: " . $msg . "\n", FILE_APPEND);
            // handle duplicate email (unique constraint) defensively
            if(stripos($msg,'duplicate') !== false || stripos($msg,'Duplicate') !== false){
                $this->jsonError('Đăng ký thất bại: email đã tồn tại', 400, ['email'=>'Email đã tồn tại']);
            }
            $this->jsonError('Không thể tạo tài khoản, vui lòng thử lại sau', 500);
        }
    }
    // Logout: invalidate the bearer token sent in Authorization header
    private function logout(){
        // read Authorization header
        $headers = null;
        if(isset($_SERVER['HTTP_AUTHORIZATION'])) $headers = $_SERVER['HTTP_AUTHORIZATION'];
        elseif(isset($_SERVER['Authorization'])) $headers = $_SERVER['Authorization'];
        else $headers = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? null;
        if(!$headers || !preg_match('/Bearer\s+(\S+)/i', $headers, $m)){
            $this->jsonError('Token không được cung cấp', 400);
        }
        $token = $m[1];
        try{
            $del = $this->pdo->prepare("DELETE FROM tokens WHERE token = :token");
            $del->execute([':token'=>$token]);
            $this->jsonSuccess(['success'=>true]);
        }catch(PDOException $e){
            @file_put_contents(__DIR__ . '/../logs/error.log', date('c') . " - Logout error: " . $e->getMessage() . "\n", FILE_APPEND);
            $this->jsonError('Không thể đăng xuất, vui lòng thử lại sau', 500);
        }
    }
}
