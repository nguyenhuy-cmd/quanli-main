<?php
// Simple helper to fetch logs endpoint and print
$u = 'http://127.0.0.1:8000/backend/api.php?resource=logs';
$ctx = stream_context_create(['http' => ['timeout' => 5]]);
$body = @file_get_contents($u, false, $ctx);
if($body === false){
    echo "FAILED\n";
    exit(1);
}
echo $body;
