@echo off
REM ======================================
REM MI-NES Payroll System - Local Server
REM ======================================

echo.
echo ========================================
echo   MI-NES PAYROLL SYSTEM - LOCAL MODE
echo ========================================
echo.
echo Starting development server...
echo.
echo Access the app at: http://localhost:8000
echo.
echo Press CTRL+C to stop the server
echo ========================================
echo.

cd /d "%~dp0"
php -S localhost:8000

pause
