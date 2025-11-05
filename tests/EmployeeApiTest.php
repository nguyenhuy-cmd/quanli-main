<?php
use PHPUnit\Framework\TestCase;

final class EmployeeApiTest extends TestCase {
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

    public function testCreateEmployeeRequiresAuth(){
        // create without token should fail
        [$r,$h] = $this->request('employees','POST',['name'=>'Test Emp','email'=>'t@example.com']);
        $this->assertFalse($r === false, 'Request executed');
        $json = json_decode($r,true);
        $this->assertArrayHasKey('error',$json);
    }
}
