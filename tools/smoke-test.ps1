# tools/smoke-test.ps1 - automated smoke test for HRM API
# Usage: powershell -ExecutionPolicy Bypass -File .\tools\smoke-test.ps1
$ErrorActionPreference = 'Stop'
$base = 'http://127.0.0.1:8000/backend/api.php'

function ExitFail($msg){ Write-Host "FAIL: $msg"; exit 1 }

try{
    $email = "smoke_$(Get-Random)@example.com"
    $password = 'pass123'
    Write-Host "Registering $email"
    $regBody = @{ name='Smoke Test'; email=$email; password=$password } | ConvertTo-Json -Compress
    $r = Invoke-WebRequest -Uri ($base + '?resource=auth/register') -Method Post -Body $regBody -ContentType 'application/json' -UseBasicParsing -TimeoutSec 15
    if($r.StatusCode -ne 201) { ExitFail "Register returned $($r.StatusCode) - $($r.Content)" }
    Write-Host "OK: registered"

    Write-Host "Logging in"
    $loginBody = @{ email=$email; password=$password } | ConvertTo-Json -Compress
    $r = Invoke-WebRequest -Uri ($base + '?resource=auth/login') -Method Post -Body $loginBody -ContentType 'application/json' -UseBasicParsing -TimeoutSec 15
    if($r.StatusCode -ne 200) { ExitFail "Login returned $($r.StatusCode) - $($r.Content)" }
    $token = ($r.Content | ConvertFrom-Json).token
    if(-not $token){ ExitFail "No token returned on login" }
    Write-Host "OK: login token acquired"
    # With token, attempt to GET employees and create employee
    $hdr = @{ Authorization = "Bearer $token" }

    Write-Host "GET employees..."
    $r = Invoke-WebRequest -Uri ($base + '?resource=employees') -Method Get -Headers $hdr -UseBasicParsing -TimeoutSec 15
    if($r.StatusCode -ne 200) { ExitFail "GET employees returned $($r.StatusCode) - $($r.Content)" }
    Write-Host "OK: employees list retrieved"
    Write-Host "Ensuring at least one position and department exist (creating if needed)"
    # ensure position
    $posRes = Invoke-WebRequest -Uri ($base + '?resource=positions') -Method Get -Headers $hdr -UseBasicParsing -TimeoutSec 15
    $positions = ($posRes.Content | ConvertFrom-Json)
    if($positions -and $positions.Count -gt 0){ $posId = $positions[0].id } else {
        $p = @{ name='General' } | ConvertTo-Json -Compress
        $rpos = Invoke-WebRequest -Uri ($base + '?resource=positions') -Method Post -Body $p -ContentType 'application/json' -Headers $hdr -UseBasicParsing -TimeoutSec 15
        $posId = ($rpos.Content | ConvertFrom-Json).id
    }
    # ensure department
    $depRes = Invoke-WebRequest -Uri ($base + '?resource=departments') -Method Get -Headers $hdr -UseBasicParsing -TimeoutSec 15
    $deps = ($depRes.Content | ConvertFrom-Json)
    if($deps -and $deps.Count -gt 0){ $depId = $deps[0].id } else {
        $d = @{ name='General' } | ConvertTo-Json -Compress
        $rdep = Invoke-WebRequest -Uri ($base + '?resource=departments') -Method Post -Body $d -ContentType 'application/json' -Headers $hdr -UseBasicParsing -TimeoutSec 15
        $depId = ($rdep.Content | ConvertFrom-Json).id
    }

    Write-Host "Creating employee"
    $emp = @{ name='Smoke Emp'; email="emp_$((Get-Random))@example.com"; position_id=$posId; department_id=$depId } | ConvertTo-Json -Compress
    $r = Invoke-WebRequest -Uri ($base + '?resource=employees') -Method Post -Body $emp -ContentType 'application/json' -Headers $hdr -UseBasicParsing -TimeoutSec 15
    if($r.StatusCode -ne 201 -and $r.StatusCode -ne 200) { ExitFail "Create employee returned $($r.StatusCode) - $($r.Content)" }
    Write-Host "OK: employee created"

    Write-Host "Smoke tests passed"
    exit 0
}catch{
    Write-Host "Exception: $($_.Exception.Message)"
    exit 1
}
