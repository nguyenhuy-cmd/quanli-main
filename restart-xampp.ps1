# Auto Restart XAMPP Script
Write-Host "==================================" -ForegroundColor Cyan
Write-Host "  AUTO RESTART XAMPP SERVICES" -ForegroundColor Cyan
Write-Host "==================================" -ForegroundColor Cyan
Write-Host ""

# Stop Apache
Write-Host "[1/6] Stopping Apache..." -ForegroundColor Yellow
taskkill /F /IM httpd.exe 2>$null | Out-Null
Start-Sleep -Seconds 1

# Stop MySQL
Write-Host "[2/6] Stopping MySQL..." -ForegroundColor Yellow
taskkill /F /IM mysqld.exe 2>$null | Out-Null
Start-Sleep -Seconds 2

# Start MySQL
Write-Host "[3/6] Starting MySQL..." -ForegroundColor Green
Start-Process "C:\xampp\mysql\bin\mysqld.exe" -WindowStyle Hidden
Start-Sleep -Seconds 3

# Wait for MySQL to be ready
Write-Host "[4/6] Waiting for MySQL to be ready..." -ForegroundColor Green
$maxAttempts = 10
$attempt = 0
$mysqlReady = $false

while ($attempt -lt $maxAttempts -and -not $mysqlReady) {
    try {
        $result = & "C:\xampp\mysql\bin\mysql.exe" -u root -e "SELECT 1;" 2>$null
        if ($LASTEXITCODE -eq 0) {
            $mysqlReady = $true
            Write-Host "   MySQL is ready!" -ForegroundColor Green
        }
    } catch {
        # Silent
    }
    
    if (-not $mysqlReady) {
        Write-Host "   Attempt $($attempt + 1)/$maxAttempts..." -ForegroundColor Gray
        Start-Sleep -Seconds 1
        $attempt++
    }
}

if (-not $mysqlReady) {
    Write-Host "   WARNING: MySQL may not be fully ready" -ForegroundColor Red
}

# Start Apache
Write-Host "[5/6] Starting Apache..." -ForegroundColor Green
Start-Process "C:\xampp\apache\bin\httpd.exe" -WindowStyle Hidden
Start-Sleep -Seconds 2

# Test connection
Write-Host "[6/6] Testing connection..." -ForegroundColor Green
try {
    $response = Invoke-WebRequest -Uri "http://localhost/quanli-main/simple-test.php" -UseBasicParsing -TimeoutSec 3 -ErrorAction Stop
    if ($response.StatusCode -eq 200) {
        Write-Host ""
        Write-Host "SUCCESS! Services are running!" -ForegroundColor Green
        Write-Host ""
        Write-Host "You can now access:" -ForegroundColor Cyan
        Write-Host "  - Application: http://localhost/quanli-main" -ForegroundColor White
        Write-Host "  - Test Page:   http://localhost/quanli-main/test-post.html" -ForegroundColor White
        Write-Host ""
    }
} catch {
    Write-Host ""
    Write-Host "WARNING: HTTP test failed, but services may still be starting..." -ForegroundColor Yellow
    Write-Host "Please wait 10 seconds and try: http://localhost/quanli-main" -ForegroundColor Yellow
    Write-Host ""
}

Write-Host "==================================" -ForegroundColor Cyan
Write-Host "Press any key to exit..." -ForegroundColor Gray
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
