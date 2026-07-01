@echo off
REM ============================================================
REM  Intan-Elyu Tourism Management System — Dev Server Launcher
REM  Run this from the project root (where this file lives).
REM
REM  Starts:
REM    Backend  : http://127.0.0.1:8000  (Laravel — php artisan serve)
REM    Frontend : http://localhost:8080  (PHP built-in server)
REM
REM  React (optional):
REM    Open a third terminal and run:  cd React && npm run dev
REM    React dev server → http://localhost:5173
REM ============================================================

echo.
echo  [1/2] Starting Laravel backend on http://127.0.0.1:8000 ...
start "Laravel Backend" cmd /k "cd /d %~dp0backend && php artisan serve --host=127.0.0.1 --port=8000"

REM Give Laravel a moment to boot before starting the frontend
timeout /t 2 /nobreak >nul

echo  [2/2] Starting PHP Frontend on http://localhost:8080 ...
start "PHP Frontend" cmd /k "cd /d %~dp0Frontend\Website\Frontend && php -S localhost:8080"

echo.
echo  ✔  Both servers are starting in separate windows.
echo.
echo     Backend  → http://127.0.0.1:8000
echo     Frontend → http://localhost:8080
echo     Login    → http://localhost:8080/login.php
echo.
echo  To also start the React app open a new terminal and run:
echo     cd React
echo     npm run dev
echo     React → http://localhost:5173
echo.
pause
