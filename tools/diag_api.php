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
$email = 'diag_'.rand(1000,9999).'@example.com';
$pass = 'pass123';
echo "Registering $email\n";
list($c, $b) = http('POST', $base . '?resource=auth/register', ['name'=>'Diag','email'=>$email,'password'=>$pass]);
echo "Register: $c\n$b\n";
echo "Login...\n";
list($c, $b) = http('POST', $base . '?resource=auth/login', ['email'=>$email,'password'=>$pass]);
echo "Login: $c\n$b\n";
$resp = json_decode($b, true);
$token = $resp['token'] ?? null;
if(!$token){ echo "No token, aborting\n"; exit(1); }
$hdr = ["Authorization: Bearer $token"];
list($c,$b) = http('GET', $base.'?resource=leaves', null, $hdr);
echo "GET leaves: $c\n$b\n";
list($c,$b) = http('GET', $base.'?resource=salaries', null, $hdr);
echo "GET salaries: $c\n$b\n";
