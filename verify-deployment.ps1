#!/usr/bin/env pwsh
<#
.SYNOPSIS
    Zeabur Deployment Verification Script for MI-NES Payroll System
.DESCRIPTION
    This script automates the verification of your Zeabur deployment.
    It checks the diagnostics endpoint and provides clear feedback.
.NOTES
    Run this after deploying to Zeabur to verify everything works.
#>

param(
    [string]$Url = "https://mi-nes.zeabur.app",
    [switch]$Detailed
)

Write-Host ""
Write-Host "=================================" -ForegroundColor Cyan
Write-Host "  ZEABUR DEPLOYMENT VERIFICATION" -ForegroundColor Cyan
Write-Host "=================================" -ForegroundColor Cyan
Write-Host ""

# Function to test endpoint
function Test-Endpoint {
    param(
        [string]$EndpointUrl,
        [string]$Name
    )
    
    Write-Host "Testing: $Name..." -NoNewline
    
    try {
        $response = Invoke-WebRequest -Uri $EndpointUrl -TimeoutSec 10 -UseBasicParsing
        
        if ($response.StatusCode -eq 200) {
            Write-Host " ✓ OK" -ForegroundColor Green
            return $true
        } else {
            Write-Host " ✗ FAILED (Status: $($response.StatusCode))" -ForegroundColor Red
            return $false
        }
    }
    catch {
        Write-Host " ✗ ERROR" -ForegroundColor Red
        if ($Detailed) {
            Write-Host "  Error: $($_.Exception.Message)" -ForegroundColor Yellow
        }
        return $false
    }
}

# Test endpoints
Write-Host "1. ENDPOINT TESTS" -ForegroundColor Yellow
Write-Host "─────────────────────────────────" -ForegroundColor DarkGray
Write-Host ""

$healthOk = Test-Endpoint -EndpointUrl "$Url/health.php" -Name "Health Check"
$diagnosticsOk = Test-Endpoint -EndpointUrl "$Url/diagnostics.php" -Name "Diagnostics Page"
$loginOk = Test-Endpoint -EndpointUrl "$Url/auth/login.php" -Name "Login Page"
$homeOk = Test-Endpoint -EndpointUrl "$Url/index.php" -Name "Home Page"

Write-Host ""
Write-Host "2. DIAGNOSTICS PAGE ANALYSIS" -ForegroundColor Yellow
Write-Host "─────────────────────────────────" -ForegroundColor DarkGray
Write-Host ""

if ($diagnosticsOk) {
    try {
        $diagResponse = Invoke-WebRequest -Uri "$Url/diagnostics.php" -UseBasicParsing
        $content = $diagResponse.Content
        
        # Check for environment variables
        if ($content -match "All environment variables are set") {
            Write-Host "Environment Variables: ✓ All Set" -ForegroundColor Green
        } elseif ($content -match "MISSING VARIABLES") {
            Write-Host "Environment Variables: ✗ Missing Variables!" -ForegroundColor Red
            Write-Host "  → Action: Set DB credentials in Zeabur Dashboard" -ForegroundColor Yellow
        }
        
        # Check for database connection
        if ($content -match "Database connection successful") {
            Write-Host "Database Connection:   ✓ Connected" -ForegroundColor Green
        } elseif ($content -match "Database (connection )?[Ee]rror|Connection failed") {
            Write-Host "Database Connection:   ✗ Failed" -ForegroundColor Red
            Write-Host "  → Action: Verify Supabase credentials" -ForegroundColor Yellow
        }
        
        # Check for PHP extensions
        if ($content -match "pdo.*LOADED" -and $content -match "pdo_pgsql.*LOADED") {
            Write-Host "PHP Extensions:        ✓ All Loaded" -ForegroundColor Green
        }
        
    } catch {
        Write-Host "Could not analyze diagnostics page" -ForegroundColor Red
    }
} else {
    Write-Host "Diagnostics page is not accessible" -ForegroundColor Red
}

Write-Host ""
Write-Host "3. OVERALL STATUS" -ForegroundColor Yellow
Write-Host "─────────────────────────────────" -ForegroundColor DarkGray
Write-Host ""

$allPassed = $healthOk -and $diagnosticsOk -and $loginOk -and $homeOk

if ($allPassed) {
    Write-Host "┌────────────────────────────────┐" -ForegroundColor Green
    Write-Host "│   ✓ DEPLOYMENT SUCCESSFUL! ✓   │" -ForegroundColor Green
    Write-Host "└────────────────────────────────┘" -ForegroundColor Green
    Write-Host ""
    Write-Host "Your application is live at:" -ForegroundColor White
    Write-Host "  $Url/auth/login.php" -ForegroundColor Cyan
} else {
    Write-Host "┌────────────────────────────────┐" -ForegroundColor Red
    Write-Host "│  ✗ DEPLOYMENT HAS ISSUES! ✗    │" -ForegroundColor Red
    Write-Host "└────────────────────────────────┘" -ForegroundColor Red
    Write-Host ""
    Write-Host "NEXT STEPS:" -ForegroundColor Yellow
    Write-Host "1. Visit diagnostics page: $Url/diagnostics.php" -ForegroundColor White
    Write-Host "2. Follow the instructions shown there" -ForegroundColor White
    Write-Host "3. Re-run this script to verify fixes" -ForegroundColor White
}

Write-Host ""
Write-Host "4. QUICK LINKS" -ForegroundColor Yellow
Write-Host "─────────────────────────────────" -ForegroundColor DarkGray
Write-Host ""
Write-Host "Diagnostics:  $Url/diagnostics.php" -ForegroundColor Cyan
Write-Host "Login:        $Url/auth/login.php" -ForegroundColor Cyan
Write-Host "Health Check: $Url/health.php" -ForegroundColor Cyan
Write-Host ""

# Return exit code
if ($allPassed) {
    exit 0
} else {
    exit 1
}
