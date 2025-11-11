# MySQL Database Repair Script
Write-Host "========================================" -ForegroundColor Red
Write-Host "  MYSQL DATABASE REPAIR" -ForegroundColor Red  
Write-Host "========================================" -ForegroundColor Red
Write-Host ""

Write-Host "WARNING: MySQL system tables are corrupted!" -ForegroundColor Yellow
Write-Host "This script will backup and reinitialize MySQL." -ForegroundColor Yellow
Write-Host ""

$confirm = Read-Host "Do you want to continue? (YES/no)"
if ($confirm -ne "YES") {
    Write-Host "Cancelled." -ForegroundColor Gray
    exit
}

# Stop MySQL
Write-Host "[1/5] Stopping MySQL..." -ForegroundColor Yellow
taskkill /F /IM mysqld.exe 2>$null | Out-Null
Start-Sleep -Seconds 2

# Backup current data folder
Write-Host "[2/5] Backing up data folder..." -ForegroundColor Yellow
$backupFolder = "C:\xampp\mysql\data_backup_$(Get-Date -Format 'yyyyMMdd_HHmmss')"
Copy-Item "C:\xampp\mysql\data" -Destination $backupFolder -Recurse -Force
Write-Host "   Backup created at: $backupFolder" -ForegroundColor Green

# Reinitialize MySQL data directory
Write-Host "[3/5] Reinitializing MySQL..." -ForegroundColor Yellow
Remove-Item "C:\xampp\mysql\data\mysql" -Recurse -Force -ErrorAction SilentlyContinue
& "C:\xampp\mysql\bin\mysql_install_db.exe" --datadir="C:\xampp\mysql\data"

# Start MySQL
Write-Host "[4/5] Starting MySQL..." -ForegroundColor Green
Start-Process "C:\xampp\mysql\bin\mysqld.exe" --WindowStyle Hidden
Start-Sleep -Seconds 5

# Test connection
Write-Host "[5/5] Testing connection..." -ForegroundColor Green
try {
    & "C:\xampp\mysql\bin\mysql.exe" -u root -e "SELECT 'MySQL is working!' as status;"
    Write-Host ""
    Write-Host "SUCCESS! MySQL has been repaired!" -ForegroundColor Green
    Write-Host "Now you need to recreate the hrm_system database." -ForegroundColor Cyan
    Write-Host ""
} catch {
    Write-Host "ERROR: MySQL still not working. Check XAMPP Control Panel." -ForegroundColor Red
}

Write-Host "Press any key to exit..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
