<?php
/**
 * Base Controller Class
 * Provides common methods for all controllers
 */

abstract class Controller {
    /**
     * Send JSON response
     * @param bool $success
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     */
    protected function sendResponse($success, $data = null, $message = '', $statusCode = 200) {
        http_response_code($statusCode);
        
        $response = [
            'success' => $success,
            'message' => $message
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Send success response
     * @param mixed $data
     * @param string $message
     */
    protected function sendSuccess($data = null, $message = 'Success') {
        $this->sendResponse(true, $data, $message, 200);
    }

    /**
     * Send error response
     * @param string $message
     * @param int $statusCode
     */
    protected function sendError($message = 'Error', $statusCode = 400) {
        $this->sendResponse(false, null, $message, $statusCode);
    }

    /**
     * Get request method
     * @return string
     */
    protected function getMethod() {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Get JSON input data
     * @return array
     */
    protected function getJsonInput() {
        $input = file_get_contents('php://input');
        return json_decode($input, true) ?? [];
    }

    /**
     * Get query parameters
     * @return array
     */
    protected function getQueryParams() {
        return $_GET;
    }

    /**
     * Validate required fields
     * @param array $data
     * @param array $requiredFields
     * @return bool
     */
    protected function validateRequired($data, $requiredFields) {
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $this->sendError("Field '$field' is required", 400);
                return false;
            }
        }
        return true;
    }

    /**
     * Validate email format
     * @param string $email
     * @return bool
     */
    protected function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Sanitize input data
     * @param array $data
     * @return array
     */
    protected function sanitize($data) {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
            } else {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }

    /**
     * Check if user is authenticated
     * @return array|false User data or false
     */
    protected function checkAuth() {
        $headers = getallheaders();
        $token = $headers['Authorization'] ?? '';
        
        if (empty($token)) {
            return false;
        }
        
        // Remove 'Bearer ' prefix if present
        $token = str_replace('Bearer ', '', $token);
        
        try {
            // Simple JWT verification (in production, use a proper JWT library)
            $decoded = $this->decodeJWT($token);
            return $decoded;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Require authentication
     */
    protected function requireAuth() {
        $user = $this->checkAuth();
        if (!$user) {
            $this->sendError('Unauthorized', 401);
        }
        return $user;
    }

    /**
     * Simple JWT encode
     * @param array $payload
     * @return string
     */
    protected function encodeJWT($payload) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload['exp'] = time() + JWT_EXPIRY;
        $payload = json_encode($payload);
        
        $base64UrlHeader = $this->base64UrlEncode($header);
        $base64UrlPayload = $this->base64UrlEncode($payload);
        
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, JWT_SECRET, true);
        $base64UrlSignature = $this->base64UrlEncode($signature);
        
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    /**
     * Simple JWT decode
     * @param string $jwt
     * @return array
     */
    protected function decodeJWT($jwt) {
        $parts = explode('.', $jwt);
        
        if (count($parts) !== 3) {
            throw new Exception('Invalid token');
        }
        
        list($base64UrlHeader, $base64UrlPayload, $base64UrlSignature) = $parts;
        
        // Verify signature
        $signature = $this->base64UrlDecode($base64UrlSignature);
        $expectedSignature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, JWT_SECRET, true);
        
        if ($signature !== $expectedSignature) {
            throw new Exception('Invalid signature');
        }
        
        $payload = json_decode($this->base64UrlDecode($base64UrlPayload), true);
        
        // Check expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            throw new Exception('Token expired');
        }
        
        return $payload;
    }

    /**
     * Base64 URL encode
     * @param string $data
     * @return string
     */
    private function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Base64 URL decode
     * @param string $data
     * @return string
     */
    private function base64UrlDecode($data) {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}
