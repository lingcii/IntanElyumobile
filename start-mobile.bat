@echo off
setlocal enabledelayedexpansion

echo ==========================================================
echo Starting Intan Elyu - MOBILE APP SYSTEM
echo ==========================================================
echo.

:: 1. Check for cloudflared
echo Checking for cloudflared...
set "CF_PATH="
where cloudflared >nul 2>&1
if %errorlevel% equ 0 (
    for /f "delims=" %%i in ('where cloudflared') do set "CF_PATH=%%i"
) else (
    if exist "%USERPROFILE%\.cloudflare\cloudflared.exe" (
        set "CF_PATH=%USERPROFILE%\.cloudflare\cloudflared.exe"
    )
)

if "!CF_PATH!"=="" (
    echo [!] cloudflared not found. Downloading automatically...
    md "%USERPROFILE%\.cloudflare" 2>nul
    powershell -Command "Invoke-WebRequest -Uri 'https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-windows-amd64.exe' -OutFile '%USERPROFILE%\.cloudflare\cloudflared.exe'" >nul 2>&1
    if exist "%USERPROFILE%\.cloudflare\cloudflared.exe" (
        set "CF_PATH=%USERPROFILE%\.cloudflare\cloudflared.exe"
        echo Installed cloudflared to !CF_PATH!
    ) else (
        echo [!] Download failed. Please check internet connection.
        pause
        exit /b
    )
)
echo Found: !CF_PATH!
echo.

:: 2. Clean old logs
del "%TEMP%\cf-frontend.log" 2>nul
del "%TEMP%\cf-backend.log" 2>nul

:: 3. Start Backend
echo Starting Laravel Backend (port 8000)...
start "Laravel Backend" cmd /k "cd /d "%~dp0backend" && php artisan serve --host=0.0.0.0"

:: 4. Start Mobile Frontend
echo Starting Mobile Frontend (port 3000)...
start "Mobile Frontend" cmd /k "cd /d "%~dp0Frontend\Mobile" && npm run start"

:: 5. Start Tunnel
if exist "%USERPROFILE%\.cloudflared\cert.pem" (
    echo Starting Named Cloudflare Tunnel (intan-elyu.online)...
    start "CF Named Tunnel" cmd /k ""!CF_PATH!" tunnel --config "%~dp0cloudflare-tunnel-config.yml" run intan-elyu-tunnel"
    echo.
    echo ==========================================================
    echo SYSTEM IS RUNNING LIVE!
    echo Mobile PWA:  https://app.intan-elyu.online
    echo Backend API: https://api.intan-elyu.online
    echo ==========================================================
) else (
    echo Starting Instant Cloudflare Tunnels...
    start "CF Frontend" cmd /c ""!CF_PATH!" tunnel --url http://localhost:3000 > "%TEMP%\cf-frontend.log" 2>&1"
    start "CF Backend" cmd /c ""!CF_PATH!" tunnel --url http://localhost:8000 > "%TEMP%\cf-backend.log" 2>&1"
    
    echo Waiting for tunnels to establish (10 seconds)...
    ping 127.0.0.1 -n 10 > nul
    
    echo Running Auto-Configurator to inject URLs into project...
    node "%~dp0cloudflare-configurator.js"
)

echo.
pause
