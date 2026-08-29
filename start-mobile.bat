@echo off
setlocal enabledelayedexpansion

echo ==========================================================
echo  Intan Elyu - MOBILE APP System
echo  Backend  : https://api.intan-elyu.online
echo  Frontend : https://app.intan-elyu.online
echo ==========================================================
echo.

:: Pre-set paths to avoid nested quotes inside start commands
set "BACKEND_DIR=%~dp0backend"
set "MOBILE_DIR=%~dp0frontend\Mobile"
set "TUNNEL_CONFIG=%~dp0cloudflare-tunnel-config.yml"
set "CREDS_FILE=%USERPROFILE%\.cloudflared\85cd9abd-8a80-41b9-822f-395765017bc4.json"

:: 1. Check for cloudflared
echo Checking for cloudflared...
set "CF_PATH="
where cloudflared >nul 2>&1
if %errorlevel% equ 0 (
    for /f "delims=" %%i in ('where cloudflared') do set "CF_PATH=%%i"
) else (
    if exist "%USERPROFILE%\.cloudflare\cloudflared.exe" (
        set "CF_PATH=%USERPROFILE%\.cloudflare\cloudflared.exe"
    ) else if exist "C:\Program Files\cloudflared\cloudflared.exe" (
        set "CF_PATH=C:\Program Files\cloudflared\cloudflared.exe"
    )
)

if "!CF_PATH!"=="" (
    echo [!] cloudflared not found. Downloading automatically...
    md "%USERPROFILE%\.cloudflare" 2>nul
    powershell -Command "Invoke-WebRequest -Uri 'https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-windows-amd64.exe' -OutFile '%USERPROFILE%\.cloudflare\cloudflared.exe'"
    if exist "%USERPROFILE%\.cloudflare\cloudflared.exe" (
        set "CF_PATH=%USERPROFILE%\.cloudflare\cloudflared.exe"
        echo [OK] Installed cloudflared to !CF_PATH!
    ) else (
        echo [!] Automatic download failed. Please install cloudflared manually.
        pause
        exit /b 1
    )
)
echo Found: !CF_PATH!
echo.

:: 2. Check for tunnel credentials
if not exist "!CREDS_FILE!" (
    echo [!] Tunnel credentials not found at: !CREDS_FILE!
    echo.
    echo To authenticate, run this command ONCE in a separate terminal:
    echo   "!CF_PATH!" login
    echo.
    echo Then re-run this script.
    pause
    exit /b 1
)
echo [OK] Tunnel credentials found.
echo.

:: 3. Start Backend
echo [1/3] Starting Laravel Backend (http://localhost:8000)...
start "Laravel Backend" cmd /k "cd /d ""!BACKEND_DIR!"" && php artisan serve --host=0.0.0.0 --port=8000"
ping -n 3 127.0.0.1 >nul 2>&1

:: 4. Start Mobile Frontend
echo [2/3] Starting Mobile Frontend (http://localhost:3000)...
start "Mobile Frontend" cmd /k "cd /d ""!MOBILE_DIR!"" && npm run start"
ping -n 3 127.0.0.1 >nul 2>&1

:: 5. Start Named Cloudflare Tunnel via helper script (handles spaces in path)
echo [3/3] Starting Cloudflare Named Tunnel...
echo @echo off > "%TEMP%\run-tunnel.bat"
echo "!CF_PATH!" tunnel --config "!TUNNEL_CONFIG!" run >> "%TEMP%\run-tunnel.bat"
start "Cloudflare Tunnel" cmd /k "%TEMP%\run-tunnel.bat"

echo.
echo ==========================================================
echo  MOBILE SYSTEM IS RUNNING
echo.
echo  Frontend : https://app.intan-elyu.online
echo  Backend  : https://api.intan-elyu.online
echo.
echo  Open the Frontend URL on your mobile device.
echo ==========================================================
echo.
pause
