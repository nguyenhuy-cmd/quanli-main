<?php
use PHPUnit\Framework\TestCase;

final class AuthApiTest extends TestCase {
    private $base = 'http://127.0.0.1:8000/backend/api.php';

    private function request($resource, $method='GET', $data=null, $headers=[]){
        $url = $this->base . '?resource=' . urlencode($resource);
        $opts = [
            'http' => [
                'method' => $method,
                'header' => "Content-Type: application/json\r\n" . implode("\r\n", $headers),
                'timeout' => 10
            ]
        ];
        if($data!==null) $opts['http']['content'] = json_encode($data);
        $ctx = stream_context_create($opts);
        $res = @file_get_contents($url, false, $ctx);
        $info = $http_response_header ?? [];
        return [$res, $info];
    }

    public function testRegisterAndLogin(){
        $email = 'phpunit_' . rand(1000,9999) . '@example.com';
        $password = 'testpass';
        [$r,$h] = $this->request('auth/register','POST',['name'=>'phpunit','email'=>$email,'password'=>$password]);
        $this->assertNotFalse($r, 'Register request failed');
        // login
        [$r2,$h2] = $this->request('auth/login','POST',['email'=>$email,'password'=>$password]);
        $this->assertNotFalse($r2, 'Login request failed');
        $body = json_decode($r2, true);
        $this->assertArrayHasKey('token',$body);
    }
}
