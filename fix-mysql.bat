@echo off
echo ========================================
echo   FIX MYSQL - QUICK SOLUTION
echo ========================================
echo.

echo [1/6] Stopping MySQL...
taskkill /F /IM mysqld.exe >nul 2>&1
timeout /t 2 /nobreak >nul

echo [2/6] Backing up current data...
set BACKUP_DIR=C:\xampp\mysql\data_backup_%date:~-4%%date:~-10,2%%date:~-7,2%_%time:~0,2%%time:~3,2%
xcopy "C:\xampp\mysql\data" "%BACKUP_DIR%\" /E /I /Q >nul
echo    Backup: %BACKUP_DIR%

echo [3/6] Removing corrupted mysql folder...
rmdir /S /Q "C:\xampp\mysql\data\mysql" 2>nul

echo [4/6] Restoring fresh mysql folder from backup...
xcopy "C:\xampp\mysql\backup\mysql" "C:\xampp\mysql\data\mysql\" /E /I /Q >nul

echo [5/6] Starting MySQL...
start "" "C:\xampp\mysql\bin\mysqld.exe"
timeout /t 5 /nobreak >nul

echo [6/6] Testing connection...
"C:\xampp\mysql\bin\mysql.exe" -u root -e "SELECT 'MySQL is FIXED!' as status;"

if %errorlevel% equ 0 (
    echo.
    echo ========================================
    echo   SUCCESS! MySQL is working!
    echo ========================================
    echo.
    echo Now recreate hrm_system database:
    echo   1. cd C:\xampp\htdocs\quanli-main
    echo   2. C:\xampp\mysql\bin\mysql.exe -u root ^< backend\init.sql
    echo.
) else (
    echo.
    echo ERROR: MySQL still not working.
    echo Try reinstalling XAMPP.
    echo.
)

pause
