#!/usr/bin/env pwsh
# CRM development launcher. start-crm.bat delegates here so both entry points
# share duplicate detection and PID tracking.
[CmdletBinding()]
param([switch]$WhatIf)

$ErrorActionPreference = 'Stop'
$scriptDir = [IO.Path]::GetFullPath((Split-Path -Parent $MyInvocation.MyCommand.Definition))
Set-Location -LiteralPath $scriptDir
$pidFile = Join-Path $scriptDir '.crm-launcher-pids.txt'
$lockPath = Join-Path $scriptDir '.crm-launcher.lock'

function Write-Status([string]$Message, [ConsoleColor]$Color = [ConsoleColor]::Gray) {
    Write-Host $Message -ForegroundColor $Color
}

function Get-TrackedEntries {
    $entries = @()
    if (-not (Test-Path -LiteralPath $pidFile)) { return $entries }

    $legacyNames = @('laravel', 'queue', 'reverb', 'scheduler', 'vite')
    $legacySignatures = @('artisan serve', 'artisan queue:work', 'artisan reverb:start', 'artisan schedule:work', 'npm run dev')
    $legacyIndex = 0
    foreach ($line in Get-Content -LiteralPath $pidFile) {
        if ([string]::IsNullOrWhiteSpace($line)) { continue }
        $parts = $line.Split('|')
        if ($parts.Count -ge 3 -and $parts[1] -match '^\d+$' -and $parts[2] -match '^\d+$') {
            $entries += [pscustomobject]@{ Name = $parts[0]; Pid = [int]$parts[1]; StartTicks = [long]$parts[2] }
        } elseif ($parts[0] -match '^\d+$') {
            # Upgrade the previous launcher's plain PID list in its known order.
            $name = if ($legacyIndex -lt $legacyNames.Count) { $legacyNames[$legacyIndex] } else { "legacy$legacyIndex" }
            $signature = if ($legacyIndex -lt $legacySignatures.Count) { $legacySignatures[$legacyIndex] } else { '' }
            $legacyIndex++
            try {
                $proc = Get-Process -Id ([int]$parts[0]) -ErrorAction Stop
                $identity = Get-CimInstance Win32_Process -Filter ('ProcessId=' + $proc.Id) -ErrorAction Stop
                if (-not $signature -or -not $identity.CommandLine -or -not $identity.CommandLine.Contains($signature)) { continue }
                $entries += [pscustomobject]@{ Name = $name; Pid = $proc.Id; StartTicks = $proc.StartTime.ToUniversalTime().Ticks }
            } catch { }
        }
    }
    return $entries
}

function Test-TrackedProcess($Entry) {
    try {
        $proc = Get-Process -Id $Entry.Pid -ErrorAction Stop
        return ($proc.StartTime.ToUniversalTime().Ticks -eq $Entry.StartTicks)
    } catch { return $false }
}

function Get-ReverbPort {
    $port = 8080
    $envFile = Join-Path $scriptDir '.env'
    if (Test-Path -LiteralPath $envFile) {
        $line = Get-Content -LiteralPath $envFile | Where-Object { $_ -match '^\s*REVERB_SERVER_PORT\s*=' } | Select-Object -Last 1
        if ($line -and $line -match '=\s*"?(\d+)') { $port = [int]$Matches[1] }
    }
    return $port
}

function Test-ListeningPort([int]$Port) {
    try { return [bool](Get-NetTCPConnection -State Listen -LocalPort $Port -ErrorAction Stop | Select-Object -First 1) }
    catch {
        $client = $null
        try {
            $client = [Net.Sockets.TcpClient]::new()
            $result = $client.BeginConnect('127.0.0.1', $Port, $null, $null)
            return ($result.AsyncWaitHandle.WaitOne(250) -and $client.Connected)
        } catch { return $false }
        finally { if ($client) { $client.Dispose() } }
    }
}

function Add-TrackedProcess([string]$Name, [int]$Id, [System.Collections.Generic.List[object]]$Entries) {
    $proc = Get-Process -Id $Id -ErrorAction Stop
    $startTicks = $proc.StartTime.ToUniversalTime().Ticks
    $Entries.Add([pscustomobject]@{ Name = $Name; Pid = $Id; StartTicks = $startTicks })
    if (-not $WhatIf) {
        $Entries | ForEach-Object { '{0}|{1}|{2}' -f $_.Name, $_.Pid, $_.StartTicks } | Set-Content -LiteralPath $pidFile -Encoding ascii
    }
    Write-Status "  RUNNING: $Name (PID $Id)" Green
}

function Start-CrmService([string]$Name, [string]$FilePath, [string[]]$Arguments, [string]$Description, [string]$Signature, [System.Collections.Generic.List[object]]$Entries) {
    $existing = $Entries | Where-Object { $_.Name -eq $Name -and (Test-TrackedProcess $_) } | Select-Object -First 1
    if ($existing) {
        Write-Status "  ALREADY RUNNING: $Description (PID $($existing.Pid))" Yellow
        return
    }

    # Catch services started outside this launcher when their command line identifies this checkout.
    try {
        $candidate = Get-CimInstance Win32_Process -ErrorAction Stop | Where-Object {
            $_.Name -match '^(php|php\d+|node|cmd)\.?(exe)?$' -and
            $_.CommandLine -and $_.CommandLine.Contains($scriptDir) -and $_.CommandLine.Contains($Signature)
        } | Select-Object -First 1
        if ($candidate) {
            Add-TrackedProcess $Name ([int]$candidate.ProcessId) $Entries
            Write-Status "  ALREADY RUNNING: $Description (found by process command line)" Yellow
            return
        }
    } catch { }

    if ($WhatIf) {
        Write-Status "  WOULD START: $Description" Cyan
        return
    }

    Write-Status "  STARTING: $Description" Cyan
    $safeName = $Name -replace '[^a-zA-Z0-9_-]', '_'
    $stdout = Join-Path $scriptDir "storage\logs\crm-$safeName.out.log"
    $stderr = Join-Path $scriptDir "storage\logs\crm-$safeName.err.log"
    $process = Start-Process -FilePath $FilePath -ArgumentList $Arguments -WorkingDirectory $scriptDir -WindowStyle Hidden -PassThru -RedirectStandardOutput $stdout -RedirectStandardError $stderr
    Start-Sleep -Milliseconds 500
    try { $null = Get-Process -Id $process.Id -ErrorAction Stop }
    catch { throw "$Description exited during startup. Check $stderr" }
    Add-TrackedProcess $Name $process.Id $Entries
}

$lock = $null
try {
    $lock = [IO.File]::Open($lockPath, [IO.FileMode]::OpenOrCreate, [IO.FileAccess]::ReadWrite, [IO.FileShare]::None)
} catch {
    Write-Status 'Another CRM launcher run is already in progress; no duplicate services were started.' Yellow
    exit 0
}

try {
    Write-Status '============================================' Cyan
    Write-Status ' CRM Launcher - Starting Development Environment' Cyan
    Write-Status " Project: $scriptDir" Gray
    Write-Status '============================================' Cyan

    $php = Get-Command php -ErrorAction SilentlyContinue
    $node = Get-Command node -ErrorAction SilentlyContinue
    $npm = Get-Command npm.cmd -ErrorAction SilentlyContinue
    if (-not $php) { throw 'PHP not found in PATH. Add PHP or XAMPP PHP to PATH.' }
    if (-not $node) { throw 'Node.js not found in PATH.' }
    if (-not $npm) { throw 'npm.cmd not found in PATH.' }
    if (-not (Test-Path (Join-Path $scriptDir 'vendor\autoload.php'))) { throw "Composer dependencies not installed. Run 'composer install' first." }
    if (-not (Test-Path (Join-Path $scriptDir 'node_modules'))) { Write-Status "WARNING: node_modules is missing; Vite may fail. Run 'npm install'." Yellow }
    Write-Status "PHP: $($php.Source) | Node: $(node -v)" Gray
    Write-Status 'Apache/MySQL: left unchanged; keep XAMPP services running as before.' Gray

    $entries = [System.Collections.Generic.List[object]]::new()
    foreach ($entry in (Get-TrackedEntries)) {
        if (Test-TrackedProcess $entry) { $entries.Add($entry) }
    }
    if (-not $WhatIf) {
        $entries | ForEach-Object { '{0}|{1}|{2}' -f $_.Name, $_.Pid, $_.StartTicks } | Set-Content -LiteralPath $pidFile -Encoding ascii
    }

    $artisan = Join-Path $scriptDir 'artisan'
    $reverbPort = Get-ReverbPort
    $vitePort = 5173
    $hotFile = Join-Path $scriptDir 'public\hot'
    if (Test-Path $hotFile) {
        try { $uri = [uri](Get-Content -Raw $hotFile); if ($uri.Port -gt 0) { $vitePort = $uri.Port } } catch { }
    }

    Write-Status '[1/5] Laravel application' Cyan
    if (Test-ListeningPort 8000) { Write-Status '  ALREADY LISTENING: http://127.0.0.1:8000' Yellow }
    else { Start-CrmService 'laravel' $php.Source @($artisan, 'serve', '--host=127.0.0.1', '--port=8000') 'Laravel development server' 'serve --host=127.0.0.1' $entries }

    Write-Status '[2/5] Queue worker' Cyan
    Start-CrmService 'queue' $php.Source @($artisan, 'queue:work') 'Laravel queue worker' 'queue:work' $entries

    Write-Status '[3/5] Scheduler' Cyan
    Start-CrmService 'scheduler' $php.Source @($artisan, 'schedule:work') 'Laravel scheduler' 'schedule:work' $entries

    Write-Status '[4/5] Reverb WebSocket server' Cyan
    if (Test-ListeningPort $reverbPort) { Write-Status "  ALREADY LISTENING: Reverb on port $reverbPort" Yellow }
    else { Start-CrmService 'reverb' $php.Source @($artisan, 'reverb:start') 'Laravel Reverb server' 'reverb:start' $entries }

    Write-Status '[5/5] Vite assets' Cyan
    if (Test-ListeningPort $vitePort) { Write-Status "  ALREADY LISTENING: Vite on port $vitePort" Yellow }
    else { Start-CrmService 'vite' $env:ComSpec @('/d', '/s', '/c', '""' + $npm.Source + '" run dev"') 'Vite development server' 'npm run dev' $entries }

    if (-not $WhatIf) {
        $entries | ForEach-Object { '{0}|{1}|{2}' -f $_.Name, $_.Pid, $_.StartTicks } | Set-Content -LiteralPath $pidFile -Encoding ascii
        $ready = $false
        for ($i = 0; $i -lt 20; $i++) {
            try { $response = Invoke-WebRequest -Uri 'http://127.0.0.1:8000/up' -TimeoutSec 2 -UseBasicParsing; if ($response.StatusCode -ge 200 -and $response.StatusCode -lt 500) { $ready = $true; break } } catch { Start-Sleep -Milliseconds 500 }
        }
        if ($ready) {
            Write-Status 'Laravel health endpoint responded; opening CRM in the default browser.' Green
            Start-Process 'http://127.0.0.1:8000'
        } else { Write-Status 'Laravel did not respond on port 8000 yet. Check storage/logs/crm-laravel.err.log.' Yellow }
    } else {
        Write-Status 'WhatIf complete; no services were started and no browser was opened.' Yellow
    }

    Write-Status '' Gray
    Write-Status 'CRM launcher flow: Laravel -> Queue -> Scheduler -> Reverb -> Vite -> Browser' Green
    Write-Status 'Tracked services: .crm-launcher-pids.txt | Stop with stop-crm.bat' Gray
} catch {
    Write-Status "ERROR: $($_.Exception.Message)" Red
    exit 1
} finally {
    if ($lock) { $lock.Dispose() }
}
