#!/bin/bash
# Zeabur Deployment Verification Script
# MI-NES Payroll System

URL="${1:-https://mi-nes.zeabur.app}"

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

echo ""
echo -e "${CYAN}=================================${NC}"
echo -e "${CYAN}  ZEABUR DEPLOYMENT VERIFICATION${NC}"
echo -e "${CYAN}=================================${NC}"
echo ""

# Test endpoint function
test_endpoint() {
    local url=$1
    local name=$2
    
    echo -n "Testing: $name... "
    
    response=$(curl -s -o /dev/null -w "%{http_code}" "$url" --max-time 10)
    
    if [ "$response" = "200" ]; then
        echo -e "${GREEN}✓ OK${NC}"
        return 0
    else
        echo -e "${RED}✗ FAILED (Status: $response)${NC}"
        return 1
    fi
}

# Test endpoints
echo -e "${YELLOW}1. ENDPOINT TESTS${NC}"
echo "─────────────────────────────────"
echo ""

test_endpoint "$URL/health.php" "Health Check"
health_status=$?

test_endpoint "$URL/diagnostics.php" "Diagnostics Page"
diag_status=$?

test_endpoint "$URL/auth/login.php" "Login Page"
login_status=$?

test_endpoint "$URL/index.php" "Home Page"
home_status=$?

echo ""
echo -e "${YELLOW}2. DIAGNOSTICS PAGE ANALYSIS${NC}"
echo "─────────────────────────────────"
echo ""

if [ $diag_status -eq 0 ]; then
    content=$(curl -s "$URL/diagnostics.php")
    
    if echo "$content" | grep -q "All environment variables are set"; then
        echo -e "Environment Variables: ${GREEN}✓ All Set${NC}"
    elif echo "$content" | grep -q "MISSING VARIABLES"; then
        echo -e "Environment Variables: ${RED}✗ Missing Variables!${NC}"
        echo -e "  ${YELLOW}→ Action: Set DB credentials in Zeabur Dashboard${NC}"
    fi
    
    if echo "$content" | grep -q "Database connection successful"; then
        echo -e "Database Connection:   ${GREEN}✓ Connected${NC}"
    elif echo "$content" | grep -qE "Database (connection )?[Ee]rror|Connection failed"; then
        echo -e "Database Connection:   ${RED}✗ Failed${NC}"
        echo -e "  ${YELLOW}→ Action: Verify Supabase credentials${NC}"
    fi
    
    if echo "$content" | grep -q "pdo.*LOADED" && echo "$content" | grep -q "pdo_pgsql.*LOADED"; then
        echo -e "PHP Extensions:        ${GREEN}✓ All Loaded${NC}"
    fi
else
    echo -e "${RED}Diagnostics page is not accessible${NC}"
fi

echo ""
echo -e "${YELLOW}3. OVERALL STATUS${NC}"
echo "─────────────────────────────────"
echo ""

if [ $health_status -eq 0 ] && [ $diag_status -eq 0 ] && [ $login_status -eq 0 ] && [ $home_status -eq 0 ]; then
    echo -e "${GREEN}┌────────────────────────────────┐${NC}"
    echo -e "${GREEN}│   ✓ DEPLOYMENT SUCCESSFUL! ✓   │${NC}"
    echo -e "${GREEN}└────────────────────────────────┘${NC}"
    echo ""
    echo "Your application is live at:"
    echo -e "${CYAN}  $URL/auth/login.php${NC}"
    exit_code=0
else
    echo -e "${RED}┌────────────────────────────────┐${NC}"
    echo -e "${RED}│  ✗ DEPLOYMENT HAS ISSUES! ✗    │${NC}"
    echo -e "${RED}└────────────────────────────────┘${NC}"
    echo ""
    echo -e "${YELLOW}NEXT STEPS:${NC}"
    echo "1. Visit diagnostics page: $URL/diagnostics.php"
    echo "2. Follow the instructions shown there"
    echo "3. Re-run this script to verify fixes"
    exit_code=1
fi

echo ""
echo -e "${YELLOW}4. QUICK LINKS${NC}"
echo "─────────────────────────────────"
echo ""
echo -e "${CYAN}Diagnostics:  $URL/diagnostics.php${NC}"
echo -e "${CYAN}Login:        $URL/auth/login.php${NC}"
echo -e "${CYAN}Health Check: $URL/health.php${NC}"
echo ""

exit $exit_code
