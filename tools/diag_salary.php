<?php
function http($method,$url,$data=null,$headers=[]){
    $ch=curl_init();
    $opts=[CURLOPT_URL=>$url,CURLOPT_RETURNTRANSFER=>true,CURLOPT_CUSTOMREQUEST=>$method];
    if($data!==null){ $opts[CURLOPT_POSTFIELDS]=is_string($data)?$data:json_encode($data); $headers[]='Content-Type: application/json'; }
    if(!empty($headers)) $opts[CURLOPT_HTTPHEADER]=$headers;
    curl_setopt_array($ch,$opts);
    $b=curl_exec($ch); $i=curl_getinfo($ch); curl_close($ch);
    return [$i['http_code'],$b];
}
$base='http://127.0.0.1:8000/backend/api.php';
// register
$email='diag_emp_'.rand(1000,9999).'@example.com'; $pass='pass123';
list($c,$b)=http('POST',$base.'?resource=auth/register',['name'=>'EmpDiag','email'=>$email,'password'=>$pass]); echo "reg $c $b\n";
list($c,$b)=http('POST',$base.'?resource=auth/login',['email'=>$email,'password'=>$pass]); echo "login $c $b\n";
$r=json_decode($b,true); $token=$r['token']??null; if(!$token){ echo "no token\n"; exit(1); }
$hdr=["Authorization: Bearer $token"];
// create employee
list($c,$b)=http('POST',$base.'?resource=employees',['name'=>'EmpForSalary','email'=>'empsalary'.rand(10,99).'@example.com'], $hdr); echo "create emp $c $b\n";
$emp=json_decode($b,true); $empId=$emp['id']??null; if(!$empId){ echo "no emp id\n"; exit(1); }
// create salary
list($c,$b)=http('POST',$base.'?resource=salaries',['employee_id'=>$empId,'amount'=>5000,'month'=>'2025-11'],$hdr); echo "create sal $c $b\n";
// get salaries
list($c,$b)=http('GET',$base.'?resource=salaries',null,$hdr); echo "get sal $c $b\n";
?>