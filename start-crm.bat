@echo off
setlocal

:: Keep this existing BAT entry point; PowerShell contains the shared launcher logic.
set "CRM_DIR=%~dp0"
where powershell.exe >nul 2>&1
if errorlevel 1 (
    echo ERROR: Windows PowerShell was not found.
    exit /b 1
)
if not exist "%CRM_DIR%start-crm.ps1" (
    echo ERROR: start-crm.ps1 was not found in "%CRM_DIR%".
    exit /b 1
)

powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%CRM_DIR%start-crm.ps1" %*
exit /b %errorlevel%
