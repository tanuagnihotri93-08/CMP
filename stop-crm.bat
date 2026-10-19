@echo off
setlocal

:: Stop only processes recorded by the CRM launcher, including each child process.
set "CRM_DIR=%~dp0"
if not exist "%CRM_DIR%.crm-launcher-pids.txt" (
    echo No tracked CRM service PID file found. Nothing to stop.
    exit /b 0
)

echo.
echo ============================================
echo  CRM Launcher - Stopping Services
echo ============================================
powershell.exe -NoProfile -ExecutionPolicy Bypass -Command "$file=Join-Path '%CRM_DIR%' '.crm-launcher-pids.txt'; $legacyNames=@('laravel','queue','reverb','scheduler','vite'); $legacySignatures=@('artisan serve','artisan queue:work','artisan reverb:start','artisan schedule:work','npm run dev'); $legacyIndex=0; $stopped=0; foreach($row in (Get-Content -LiteralPath $file)){ if([string]::IsNullOrWhiteSpace($row)){continue}; $parts=$row.Split('|'); $legacy=$false; if($parts.Count -ge 3 -and $parts[1] -match '^\d+$' -and $parts[2] -match '^\d+$'){ $name=$parts[0]; $procId=[int]$parts[1]; $ticks=[long]$parts[2] } elseif($parts[0] -match '^\d+$'){ $legacy=$true; $procId=[int]$parts[0]; $name=if($legacyIndex -lt $legacyNames.Count){$legacyNames[$legacyIndex]}else{'legacy'}; $signature=if($legacyIndex -lt $legacySignatures.Count){$legacySignatures[$legacyIndex]}else{''}; $legacyIndex++ } else { Write-Host ('Skipping malformed PID entry: '+$row) -ForegroundColor Yellow; continue }; try { $proc=Get-Process -Id $procId -ErrorAction Stop; if(-not $legacy -and $proc.StartTime.ToUniversalTime().Ticks -ne $ticks){ Write-Host ('SKIP '+$name+': PID '+$procId+' was reused by another process.') -ForegroundColor Yellow; continue }; if($legacy){ $match=Get-CimInstance Win32_Process -Filter ('ProcessId='+$procId) -ErrorAction Stop; if(-not $signature -or -not $match.CommandLine -or -not $match.CommandLine.Contains($signature)){ Write-Host ('SKIP legacy PID '+$procId+': process identity could not be confirmed.') -ForegroundColor Yellow; continue } }; Write-Host ('Stopping '+$name+' (PID '+$procId+')...') -ForegroundColor Cyan; $kill=Start-Process -FilePath 'taskkill.exe' -ArgumentList @('/PID',([string]$procId),'/T','/F') -Wait -PassThru -NoNewWindow; if($kill.ExitCode -eq 0){ Write-Host ('STOPPED '+$name) -ForegroundColor Green; $stopped++ } else { Write-Host ('ALREADY STOPPED '+$name) -ForegroundColor DarkGray } } catch { Write-Host ('ALREADY STOPPED or inaccessible: '+$name+' (PID '+$procId+')') -ForegroundColor DarkGray } }; Remove-Item -LiteralPath $file -Force -ErrorAction SilentlyContinue; Write-Host ('Stopped '+$stopped+' tracked CRM service(s).') -ForegroundColor Green"
set "STOP_RESULT=%errorlevel%"
echo.
if not "%STOP_RESULT%"=="0" echo ERROR: Could not complete the stop operation.
exit /b %STOP_RESULT%
