<?php
/**
 * Authentication Controller
 * Handles login, register, logout
 */

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/UserModel.php';

class AuthController extends Controller {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new UserModel();
    }

    /**
     * Handle authentication requests
     */
    public function handle() {
        $method = $this->getMethod();
        $action = $_GET['action'] ?? '';
        
        switch ($action) {
            case 'login':
                if ($method === 'POST') {
                    $this->login();
                }
                break;
            
            case 'register':
                if ($method === 'POST') {
                    $this->register();
                }
                break;
            
            case 'logout':
                $this->logout();
                break;
            
            case 'me':
                if ($method === 'GET') {
                    $this->getCurrentUser();
                }
                break;
            
            default:
                $this->sendError('Invalid action', 404);
        }
    }

    /**
     * Login user
     */
    private function login() {
        try {
            $data = $this->getJsonInput();
            
            // Validate required fields
            $this->validateRequired($data, ['email', 'password']);
            
            // Validate email format
            if (!$this->validateEmail($data['email'])) {
                $this->sendError('Invalid email format');
            }
            
            // Verify credentials
            $user = $this->userModel->verifyCredentials($data['email'], $data['password']);
            
            if (!$user) {
                $this->sendError('Invalid email or password', 401);
            }
            
            // Generate JWT token
            $token = $this->encodeJWT([
                'id' => $user['id'],
                'email' => $user['email'],
                'role' => $user['role']
            ]);
            
            $this->sendSuccess([
                'user' => $user,
                'token' => $token
            ], 'Login successful');
            
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    /**
     * Register new user
     */
    private function register() {
        try {
            $data = $this->getJsonInput();
            
            // Validate required fields
            $this->validateRequired($data, ['name', 'email', 'password']);
            
            // Validate email format
            if (!$this->validateEmail($data['email'])) {
                $this->sendError('Invalid email format');
            }
            
            // Check if email already exists
            $existingUser = $this->userModel->findByEmail($data['email']);
            if ($existingUser) {
                $this->sendError('Email already registered');
            }
            
            // Sanitize input
            $data = $this->sanitize($data);
            
            // Register user
            $userId = $this->userModel->register([
                'name' => $data['name'],  // Use 'name' to match DB column
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => $data['role'] ?? 'employee'
            ]);
            
            // Get user data
            $user = $this->userModel->getById($userId);
            unset($user['password']);
            
            // Generate JWT token
            $token = $this->encodeJWT([
                'id' => $user['id'],
                'email' => $user['email'],
                'role' => $user['role']
            ]);
            
            $this->sendSuccess([
                'user' => $user,
                'token' => $token
            ], 'Registration successful');
            
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    /**
     * Logout user (client-side token removal)
     */
    private function logout() {
        $this->sendSuccess(null, 'Logout successful');
    }

    /**
     * Get current authenticated user
     */
    private function getCurrentUser() {
        try {
            $user = $this->requireAuth();
            
            // Get fresh user data from database
            $userData = $this->userModel->getById($user['id']);
            unset($userData['password']);
            
            $this->sendSuccess($userData);
            
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }
}
