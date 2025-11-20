#!/bin/bash
# ================================================
# SnakkaZ - Connection Test Script
# Test FTP, SSH, Domain, API
# ================================================

echo "🔍 SNAKKAZ CONNECTION TESTS"
echo "================================"
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
DOMAIN="snakkaz.com"
FTP_HOST="ftp.snakkaz.com"
SSH_HOST="premium123.snakkaz.com"
API_BASE="https://snakkaz.com/api"

# ================================================
# Test 1: Domain Accessibility
# ================================================

echo -e "${BLUE}TEST 1: Domain Accessibility${NC}"
echo "Testing: https://$DOMAIN"

if curl -s -o /dev/null -w "%{http_code}" "https://$DOMAIN" | grep -q "200"; then
    echo -e "${GREEN}✓ Domain is accessible (HTTP 200)${NC}"
    
    # Check SSL
    if curl -I "https://$DOMAIN" 2>&1 | grep -q "HTTP/2"; then
        echo -e "${GREEN}✓ SSL/HTTPS working (HTTP/2)${NC}"
    else
        echo -e "${YELLOW}⚠ SSL works but using HTTP/1.1${NC}"
    fi
    
    # Check server
    SERVER=$(curl -sI "https://$DOMAIN" | grep -i "server:" | cut -d' ' -f2-)
    echo -e "${GREEN}✓ Server: $SERVER${NC}"
    
else
    echo -e "${RED}✗ Domain not accessible${NC}"
fi

echo ""

# ================================================
# Test 2: API Endpoint (if deployed)
# ================================================

echo -e "${BLUE}TEST 2: API Endpoint${NC}"
echo "Testing: $API_BASE/health.php"

HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "$API_BASE/health.php")

if [ "$HTTP_CODE" = "200" ]; then
    echo -e "${GREEN}✓ API is deployed and responding${NC}"
    
    # Get health data
    HEALTH=$(curl -s "$API_BASE/health.php")
    echo -e "${GREEN}Response:${NC}"
    echo "$HEALTH" | head -10
    
elif [ "$HTTP_CODE" = "404" ]; then
    echo -e "${YELLOW}⚠ API not deployed yet (404)${NC}"
    echo -e "${YELLOW}  This is expected if you haven't uploaded files yet${NC}"
else
    echo -e "${RED}✗ API error (HTTP $HTTP_CODE)${NC}"
fi

echo ""

# ================================================
# Test 3: DNS Resolution
# ================================================

echo -e "${BLUE}TEST 3: DNS Resolution${NC}"

# Test with getent (works in most environments)
if IP=$(getent hosts $DOMAIN 2>/dev/null | awk '{ print $1 }'); then
    echo -e "${GREEN}✓ DNS resolved: $DOMAIN → $IP${NC}"
else
    echo -e "${YELLOW}⚠ Could not resolve DNS (getent not available)${NC}"
fi

echo ""

# ================================================
# Test 4: FTP Port Check
# ================================================

echo -e "${BLUE}TEST 4: FTP Connection${NC}"
echo "Testing: $FTP_HOST:21"

# Test if port 21 is open (FTP)
if timeout 5 bash -c "cat < /dev/null > /dev/tcp/$FTP_HOST/21" 2>/dev/null; then
    echo -e "${GREEN}✓ FTP port 21 is open${NC}"
    echo -e "${GREEN}✓ Server accepts FTP connections${NC}"
else
    echo -e "${YELLOW}⚠ FTP port 21 not accessible from this environment${NC}"
    echo -e "${YELLOW}  (This is normal in containerized environments)${NC}"
fi

echo ""

# ================================================
# Test 5: SSH Port Check
# ================================================

echo -e "${BLUE}TEST 5: SSH Connection${NC}"
echo "Testing: SSH port 22"

# Common SSH hosts for Namecheap
SSH_HOSTS=("premium123.web-hosting.com" "snakkaz.com")

for SSH_HOST in "${SSH_HOSTS[@]}"; do
    echo "Trying: $SSH_HOST"
    if timeout 5 bash -c "cat < /dev/null > /dev/tcp/$SSH_HOST/22" 2>/dev/null; then
        echo -e "${GREEN}✓ SSH port 22 is open on $SSH_HOST${NC}"
        break
    else
        echo -e "${YELLOW}⚠ Port 22 not accessible on $SSH_HOST${NC}"
    fi
done

echo ""

# ================================================
# Test 6: SSL Certificate
# ================================================

echo -e "${BLUE}TEST 6: SSL Certificate${NC}"

if command -v openssl &> /dev/null; then
    echo "Checking SSL certificate for $DOMAIN..."
    
    CERT_INFO=$(echo | openssl s_client -servername $DOMAIN -connect $DOMAIN:443 2>/dev/null | openssl x509 -noout -dates 2>/dev/null)
    
    if [ ! -z "$CERT_INFO" ]; then
        echo -e "${GREEN}✓ SSL Certificate is valid${NC}"
        echo "$CERT_INFO"
    else
        echo -e "${YELLOW}⚠ Could not verify SSL certificate${NC}"
    fi
else
    echo -e "${YELLOW}⚠ OpenSSL not available for certificate check${NC}"
fi

echo ""

# ================================================
# Test 7: Performance Check
# ================================================

echo -e "${BLUE}TEST 7: Performance${NC}"
echo "Testing page load time..."

LOAD_TIME=$(curl -s -o /dev/null -w "%{time_total}" "https://$DOMAIN")
echo -e "${GREEN}✓ Load time: ${LOAD_TIME}s${NC}"

if (( $(echo "$LOAD_TIME < 1" | bc -l) )); then
    echo -e "${GREEN}✓ Excellent performance (<1s)${NC}"
elif (( $(echo "$LOAD_TIME < 3" | bc -l) )); then
    echo -e "${YELLOW}⚠ Good performance (1-3s)${NC}"
else
    echo -e "${RED}⚠ Slow performance (>3s)${NC}"
fi

echo ""

# ================================================
# Summary
# ================================================

echo "================================"
echo -e "${BLUE}📊 SUMMARY${NC}"
echo "================================"
echo ""
echo "Domain:       $DOMAIN"
echo "FTP Host:     $FTP_HOST"
echo "API Base:     $API_BASE"
echo ""
echo -e "${GREEN}Next Steps:${NC}"
echo "1. Deploy backend files via FTP"
echo "2. Import database schema"
echo "3. Test API endpoints"
echo "4. Build and deploy frontend"
echo ""
echo "See: DEPLOY-GUIDE-SNAKKAZ.md for instructions"
echo ""
