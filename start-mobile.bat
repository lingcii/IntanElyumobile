@echo off
setlocal enabledelayedexpansion

echo ==========================================================
echo Starting Intan Elyu - MOBILE APP System
echo (Backend + Mobile Frontend + Cloudflare Tunnel)
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
    echo [!] cloudflared not found.
    echo.
    echo Download from: https://developers.cloudflare.com/cloudflare-one/connections/connect-networks/downloads/
    echo.
    set /p INSTALL="Download and install now? (y/n): "
    if /i "!INSTALL!"=="y" (
        echo Downloading cloudflared...
        md "%USERPROFILE%\.cloudflare" 2>nul
        powershell -Command "Invoke-WebRequest -Uri 'https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-windows-amd64.exe' -OutFile '%USERPROFILE%\.cloudflare\cloudflared.exe'" >nul 2>&1
        if exist "%USERPROFILE%\.cloudflare\cloudflared.exe" (
            set "CF_PATH=%USERPROFILE%\.cloudflare\cloudflared.exe"
            echo cloudflared installed to !CF_PATH!
        ) else (
            echo Download failed. Install manually, then re-run.
            pause
            exit /b
        )
    ) else (
        echo Install it manually, then re-run this script.
        pause
        exit /b
    )
)
echo Found: !CF_PATH!
echo.

:: 2. Clean old Cloudflare logs
del "%TEMP%\cf-frontend.log" 2>nul
del "%TEMP%\cf-backend.log" 2>nul

echo ----------------------------------------------------------
echo Select Backend Target:
echo [1] Local Backend (via Cloudflare Tunnel)
echo [2] Deployed Railway Backend (https://intanelyumobile-production.up.railway.app)
echo ----------------------------------------------------------
set /p TARGET="Choose target (1 or 2, default is 1): "

if "!TARGET!"=="2" (
    set "USE_RAILWAY=1"
    echo Mode: Deployed Railway Backend
) else (
    set "USE_RAILWAY=0"
    echo Mode: Local Backend
)
echo.

:: 3. Start Backend (if using local backend)
if "!USE_RAILWAY!"=="0" (
    echo Starting Backend (port 8000)...
    start "Laravel Backend" cmd /k "cd /d "%~dp0backend" && php artisan serve --host=0.0.0.0"
)

:: 4. Start Mobile Frontend
echo Starting Mobile Frontend (port 3000)...
start "Mobile Frontend" cmd /k "cd /d "%~dp0Frontend\Mobile" && npm run start"

:: 5. Start Cloudflare Tunnels
echo Starting Cloudflare Tunnels...
start "CF Frontend" cmd /c ""!CF_PATH!" tunnel --url http://localhost:3000 > "%TEMP%\cf-frontend.log" 2>&1"
if "!USE_RAILWAY!"=="0" (
    start "CF Backend" cmd /c ""!CF_PATH!" tunnel --url http://localhost:8000 > "%TEMP%\cf-backend.log" 2>&1"
)

:: 6. Wait and configure
echo.
echo Waiting for tunnels to establish (10 seconds)...
ping 127.0.0.1 -n 10 > nul

:: 7. Run Configurator to inject URLs into your project...
if "!USE_RAILWAY!"=="1" (
    node "%~dp0cloudflare-configurator.js" railway
) else (
    node "%~dp0cloudflare-configurator.js"
)

echo.
echo ==========================================================
echo MOBILE SYSTEM IS RUNNING via Cloudflare!
echo.
echo Open the FRONTEND URL (printed above) on your phone.
echo No request limits!
echo ==========================================================
echo.
pause
