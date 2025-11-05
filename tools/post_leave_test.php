<?php
function http($method, $url, $data=null, $headers=[]){
    $ch = curl_init();
    $opts = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
    ];
    if($data !== null){
        $opts[CURLOPT_POSTFIELDS] = is_string($data) ? $data : json_encode($data);
        $opts[CURLOPT_HTTPHEADER] = array_merge($opts[CURLOPT_HTTPHEADER], ['Content-Type: application/json']);
    }
    curl_setopt_array($ch, $opts);
    $body = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    return [$info['http_code'] ?? 0, $body];
}
$base = 'http://127.0.0.1:8000/backend/api.php';
$email = 'postdiag_'.rand(1000,9999).'@example.com';
$pass = 'pass123';
list($c,$b) = http('POST', $base . '?resource=auth/register', ['name'=>'PostDiag','email'=>$email,'password'=>$pass]);
echo "Register: $c\n$b\n";
list($c,$b) = http('POST', $base . '?resource=auth/login', ['email'=>$email,'password'=>$pass]);
echo "Login: $c\n$b\n";
$resp = json_decode($b, true);
$token = $resp['token'] ?? null;
if(!$token){ echo "No token, aborting\n"; exit(1); }
$hdr = ["Authorization: Bearer $token"];
list($c,$b) = http('GET', $base . '?resource=employees', null, $hdr);
echo "GET employees: $c\n$b\n";
$emps = json_decode($b, true) ?: [];
if(!isset($emps[0]['id'])){ echo "No employees available to test. Aborting.\n"; exit(1); }
$empid = $emps[0]['id'];
$payload = ['employee_id' => $empid, 'start_date' => '2025-11-10', 'end_date' => '2025-11-12', 'status' => 'pending'];
list($c,$b) = http('POST', $base . '?resource=leaves', $payload, $hdr);
echo "Create leave: $c\n$b\n";

// GET leaves to verify
list($c,$b) = http('GET', $base . '?resource=leaves', null, $hdr);
echo "GET leaves after create: $c\n$b\n";
