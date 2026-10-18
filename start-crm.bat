@echo off
setlocal enabledelayedexpansion

:: ============================================
:: CRM Launcher - Start Development Environment
:: Project: C:\xampp\htdocs\crm
:: ============================================

:: Set project directory (directory where this script lives)
set CRM_DIR=%~dp0

:: Check PHP availability
where php >nul 2>&1
if errorlevel 1 (
    echo.
    echo ERROR: PHP not found in PATH.
    echo Please add PHP to your PATH or ensure XAMPP PHP is accessible.
    echo.
    exit /b 1
)

:: Check Node.js availability
where node >nul 2>&1
if errorlevel 1 (
    echo.
    echo ERROR: Node.js not found in PATH.
    echo.
    exit /b 1
)

:: Check npm availability
where npm >nul 2>&1
if errorlevel 1 (
    echo.
    echo ERROR: npm not found in PATH.
    echo.
    exit /b 1
)

:: Detect Node.js version
for /f "tokens=*" %%v in ('node -v 2^>nul') do set NODE_VER=%%v
echo Detected Node.js version: %NODE_VER%

:: Change to project directory
cd /d "%CRM_DIR%"

:: Check composer dependencies
if not exist "vendor\autoload.php" (
    echo.
    echo ERROR: Composer dependencies not installed.
    echo Run 'composer install' first.
    echo.
    exit /b 1
)

:: Check node_modules (warning only)
if not exist "node_modules" (
    echo.
    echo WARNING: node_modules not found. Run 'npm install' first.
    echo.
)

:: Clear any stale PID file
if exist ".crm-launcher-pids.txt" del ".crm-launcher-pids.txt" 2>nul

echo.
echo ============================================
echo  CRM Launcher - Starting Development Environment
echo ============================================
echo Project: %CRM_DIR%
echo PHP: Found
echo Node: %NODE_VER%
echo ============================================
echo.

:: --- Service 1: Laravel development server ---
echo [1/4] Starting Laravel development server (http://127.0.0.1:8000)...
powershell -NoProfile -ExecutionPolicy Bypass -Command "Start-Process -FilePath 'php' -ArgumentList 'artisan','serve' -WorkingDirectory '%CRM_DIR%' -WindowStyle Normal -PassThru | Select-Object -ExpandProperty Id >> '%CRM_DIR%.crm-launcher-pids.txt'"
timeout /t 2 >nul

:: --- Service 2: Queue worker ---
echo [2/4] Starting Queue worker...
powershell -NoProfile -ExecutionPolicy Bypass -Command "Start-Process -FilePath 'php' -ArgumentList 'artisan','queue:work' -WorkingDirectory '%CRM_DIR%' -WindowStyle Normal -PassThru | Select-Object -ExpandProperty Id >> '%CRM_DIR%.crm-launcher-pids.txt'"
timeout /t 2 >nul

:: --- Service 3: Laravel Reverb ---
echo [3/4] Starting Laravel Reverb server...
powershell -NoProfile -ExecutionPolicy Bypass -Command "Start-Process -FilePath 'php' -ArgumentList 'artisan','reverb:start' -WorkingDirectory '%CRM_DIR%' -WindowStyle Normal -PassThru | Select-Object -ExpandProperty Id >> '%CRM_DIR%.crm-launcher-pids.txt'"
timeout /t 2 >nul

:: --- Service 4: Vite dev server ---
echo [4/4] Starting Vite dev server (http://localhost:5173)...
powershell -NoProfile -ExecutionPolicy Bypass -Command "Start-Process -FilePath 'npm' -ArgumentList 'run','dev' -WorkingDirectory '%CRM_DIR%' -WindowStyle Normal -PassThru | Select-Object -ExpandProperty Id >> '%CRM_DIR%.crm-launcher-pids.txt'"
timeout /t 2 >nul

echo.
echo ============================================
echo  All services started successfully!
echo ============================================
echo Laravel Server: http://127.0.0.1:8000
echo Vite Dev Server: http://localhost:5173
echo ============================================
echo.
echo PIDs saved to .crm-launcher-pids.txt
echo Run 'stop-crm.bat' to stop all CRM services.
echo ============================================