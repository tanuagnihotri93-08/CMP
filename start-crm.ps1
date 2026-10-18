#!/usr/bin/env pwsh
# ============================================
# CRM Launcher - Start Development Environment
# Project: C:\xampp\htdocs\crm
# ============================================

$ErrorActionPreference = "Stop"

# Set project directory to the folder containing this script
$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Definition
Set-Location $scriptDir

Write-Host "============================================" -ForegroundColor Cyan
Write-Host " CRM Launcher - Starting Development Environment" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "Project: $scriptDir" -ForegroundColor Gray
Write-Host ""

# --- Prerequisite checks ---

# Check PHP
if (-not (Get-Command php -ErrorAction SilentlyContinue)) {
    Write-Host "ERROR: PHP not found in PATH." -ForegroundColor Red
    Write-Host "Please add PHP to your PATH or ensure XAMPP PHP is accessible." -ForegroundColor Red
    exit 1
}

# Check Node.js
if (-not (Get-Command node -ErrorAction SilentlyContinue)) {
    Write-Host "ERROR: Node.js not found in PATH." -ForegroundColor Red
    exit 1
}

# Check npm
if (-not (Get-Command npm -ErrorAction SilentlyContinue)) {
    Write-Host "ERROR: npm not found in PATH." -ForegroundColor Red
    exit 1
}

# Detect Node.js version
$nodeVersion = node -v
Write-Host "Detected Node.js version: $nodeVersion" -ForegroundColor Gray

# Check composer dependencies
if (-not (Test-Path "vendor\autoload.php")) {
    Write-Host "ERROR: Composer dependencies not installed." -ForegroundColor Red
    Write-Host "Run 'composer install' first." -ForegroundColor Red
    exit 1
}

# Check node_modules (warning only)
if (-not (Test-Path "node_modules")) {
    Write-Host "WARNING: node_modules not found. Run 'npm install' first." -ForegroundColor Yellow
}

# Clear any stale PID file
$pidFile = Join-Path $scriptDir ".crm-launcher-pids.txt"
if (Test-Path $pidFile) { Remove-Item $pidFile -Force }

$pids = @()

# --- Service 1: Laravel development server ---
Write-Host "[1/4] Starting Laravel development server (http://127.0.0.1:8000)..." -ForegroundColor Green
$proc = Start-Process -FilePath "php" -ArgumentList @("artisan","serve") -WorkingDirectory $scriptDir -WindowStyle Normal -PassThru
$pids += $proc.Id
Start-Sleep -Seconds 2

# --- Service 2: Queue worker ---
Write-Host "[2/4] Starting Queue worker..." -ForegroundColor Green
$proc = Start-Process -FilePath "php" -ArgumentList @("artisan","queue:work") -WorkingDirectory $scriptDir -WindowStyle Normal -PassThru
$pids += $proc.Id
Start-Sleep -Seconds 2

# --- Service 3: Laravel Reverb ---
Write-Host "[3/4] Starting Laravel Reverb server..." -ForegroundColor Green
$proc = Start-Process -FilePath "php" -ArgumentList @("artisan","reverb:start") -WorkingDirectory $scriptDir -WindowStyle Normal -PassThru
$pids += $proc.Id
Start-Sleep -Seconds 2

# --- Service 4: Vite dev server ---
Write-Host "[4/4] Starting Vite dev server (http://localhost:5173)..." -ForegroundColor Green
$proc = Start-Process -FilePath "npm" -ArgumentList @("run","dev") -WorkingDirectory $scriptDir -WindowStyle Normal -PassThru
$pids += $proc.Id
Start-Sleep -Seconds 2

# Save PIDs for the stop script
$pids | Out-File -FilePath $pidFile -Encoding utf8

Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
Write-Host " All services started successfully!" -ForegroundColor Green
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "Laravel Server: http://127.0.0.1:8000" -ForegroundColor Gray
Write-Host "Vite Dev Server: http://localhost:5173" -ForegroundColor Gray
Write-Host ""
Write-Host "PIDs saved to .crm-launcher-pids.txt" -ForegroundColor Gray
Write-Host "Run 'stop-crm.bat' to stop all CRM services." -ForegroundColor Gray
Write-Host "============================================" -ForegroundColor Cyan