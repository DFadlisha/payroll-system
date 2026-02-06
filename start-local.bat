@echo off
REM ======================================
REM MI-NES Payroll System - Local Server
REM ======================================

echo.
echo ========================================
echo   MI-NES PAYROLL SYSTEM - LOCAL MODE
echo ========================================
echo.

REM Get Local IP Address
set IP=localhost
for /f "tokens=14" %%a in ('ipconfig ^| findstr IPv4') do set IP=%%a

echo Starting development server...
echo.
echo [1] On this PC:      http://localhost:8000
echo [2] On Other Devices: http://%IP%:8000
echo.
echo Note: Ensure Windows Firewall allows PHP to communicate on Private networks.
echo.
echo Press CTRL+C to stop the server
echo ========================================
echo.

cd /d "%~dp0"
php -S 0.0.0.0:8000

pause
