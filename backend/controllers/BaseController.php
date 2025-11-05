<?php
class BaseController {
    protected function json($data, $code=200){
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    // Standard error response: { error: string, details?: object }
    protected function jsonError($message, $code=400, $details=null){
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        $payload = ['error'=>$message];
        if($details) $payload['details'] = $details;
        echo json_encode($payload);
        exit;
    }

    // Success wrapper (keeps previous behavior but useful for consistency)
    protected function jsonSuccess($data, $code=200){
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }
}
