@echo off
setlocal enabledelayedexpansion

:: ============================================
:: CRM Launcher - Stop Development Environment
:: Only stops processes tracked by start-crm.bat / start-crm.ps1
:: ============================================

set CRM_DIR=%~dp0

echo.
echo ============================================
echo  CRM Launcher - Stopping Services
echo ============================================

:: Check if PID file exists
if not exist "%CRM_DIR%.crm-launcher-pids.txt" (
    echo No PID file found.
    echo No CRM services are tracked by this launcher.
    echo.
    exit /b 0
)

set /a count=0

:: Read each PID and stop only that specific process
for /f "tokens=*" %%p in (%CRM_DIR%.crm-launcher-pids.txt) do (
    set pid=%%p
    if defined pid (
        echo Stopping PID !pid!...
        taskkill /PID !pid! /F >nul 2>&1
        if !errorlevel! equ 0 (
            echo   [!pid!] Stopped.
        ) else (
            echo   [!pid!] Not running or already stopped.
        )
        set /a count+=1
    )
)

:: Clean up PID file
del "%CRM_DIR%.crm-launcher-pids.txt" 2>nul

echo.
echo Stopped %count% CRM service(s).
echo ============================================