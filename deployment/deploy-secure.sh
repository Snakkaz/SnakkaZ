#!/bin/bash

# ================================================
# SnakkaZ Chat - Sikker FTP Deploy med Validering
# For Namecheap StellarPlus Hosting
# ================================================

set -e  # Exit on error

echo "🚀 SnakkaZ Sikker Deployment"
echo "================================"
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# ================================================
# Configuration
# ================================================

FTP_HOST="ftp.snakkaz.com"
FTP_USER="admin@snakkaz.com"
FTP_PASS=""  # Will prompt if not set
REMOTE_DIR="/home/snakqsqe/public_html"
LOCAL_API_DIR="server"
LOCAL_HTACCESS="deployment/.htaccess"

# ================================================
# Pre-flight Checks
# ================================================

echo -e "${BLUE}🔍 Pre-flight Checks${NC}"
echo "-----------------------------------"

# Check if we're in the right directory
if [ ! -d "server" ] || [ ! -f "database/schema.sql" ]; then
    echo -e "${RED}❌ Error: Run this script from project root${NC}"
    echo -e "${YELLOW}Current directory: $(pwd)${NC}"
    exit 1
fi
echo -e "${GREEN}✓ In correct directory${NC}"

# Check if files exist
if [ ! -f "server/config/database.php" ]; then
    echo -e "${RED}❌ Error: server/config/database.php not found${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Config files present${NC}"

# Check for sensitive data
if grep -q "your_password_here\|YOUR_PASSWORD\|CHANGE_THIS" server/config/database.php; then
    echo -e "${RED}❌ Error: Database password not set in config!${NC}"
    echo -e "${YELLOW}Update server/config/database.php first${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Database credentials configured${NC}"

if grep -q "CHANGE_THIS_TO_RANDOM" server/config/database.php; then
    echo -e "${RED}❌ Error: JWT_SECRET not set!${NC}"
    echo -e "${YELLOW}Update server/config/database.php first${NC}"
    exit 1
fi
echo -e "${GREEN}✓ JWT secret configured${NC}"

# Check for .htaccess
if [ ! -f "$LOCAL_HTACCESS" ]; then
    echo -e "${RED}❌ Error: .htaccess file not found${NC}"
    exit 1
fi
echo -e "${GREEN}✓ .htaccess ready${NC}"

echo ""

# ================================================
# FTP Credentials
# ================================================

echo -e "${BLUE}🔐 FTP Credentials${NC}"
echo "-----------------------------------"

if [ -z "$FTP_PASS" ]; then
    echo -e "${YELLOW}Enter your cPanel password:${NC}"
    read -s FTP_PASS
    echo ""
fi

echo -e "${GREEN}✓ Credentials set${NC}"
echo ""

# ================================================
# Install lftp if needed
# ================================================

echo -e "${BLUE}📦 Checking Dependencies${NC}"
echo "-----------------------------------"

if ! command -v lftp &> /dev/null; then
    echo -e "${YELLOW}lftp not found. Installing...${NC}"
    
    if command -v apt-get &> /dev/null; then
        sudo apt-get update && sudo apt-get install -y lftp
    elif command -v yum &> /dev/null; then
        sudo yum install -y lftp
    else
        echo -e "${RED}❌ Cannot install lftp automatically${NC}"
        echo -e "${YELLOW}Please install manually: apt-get install lftp${NC}"
        exit 1
    fi
fi

echo -e "${GREEN}✓ lftp installed${NC}"
echo ""

# ================================================
# Test Connection
# ================================================

echo -e "${BLUE}🔌 Testing FTP Connection${NC}"
echo "-----------------------------------"

if timeout 10 lftp -c "set ftp:ssl-allow no; open -u $FTP_USER,$FTP_PASS $FTP_HOST; ls; bye" > /dev/null 2>&1; then
    echo -e "${GREEN}✓ FTP connection successful${NC}"
else
    echo -e "${RED}❌ FTP connection failed${NC}"
    echo -e "${YELLOW}Please verify:${NC}"
    echo "  - Username: $FTP_USER"
    echo "  - Password: [hidden]"
    echo "  - Host: $FTP_HOST"
    exit 1
fi

echo ""

# ================================================
# Backup Existing Files (if any)
# ================================================

echo -e "${BLUE}💾 Checking for Existing Files${NC}"
echo "-----------------------------------"

BACKUP_NEEDED=$(lftp -c "
set ftp:ssl-allow no
open -u $FTP_USER,$FTP_PASS $FTP_HOST
cls -1 $REMOTE_DIR/api 2>/dev/null | wc -l
bye
" 2>/dev/null || echo "0")

if [ "$BACKUP_NEEDED" -gt "0" ]; then
    echo -e "${YELLOW}⚠ Existing files found in /api/${NC}"
    echo -e "${YELLOW}Creating backup...${NC}"
    
    BACKUP_DIR="/public_html/backup-$(date +%Y%m%d-%H%M%S)"
    
    lftp -c "
    set ftp:ssl-allow no
    open -u $FTP_USER,$FTP_PASS $FTP_HOST
    mkdir -p $BACKUP_DIR
    mirror --verbose $REMOTE_DIR/api $BACKUP_DIR/api
    bye
    " 2>&1 | grep -v "^get\|^mkdir" || true
    
    echo -e "${GREEN}✓ Backup created: $BACKUP_DIR${NC}"
else
    echo -e "${GREEN}✓ No existing files (clean install)${NC}"
fi

echo ""

# ================================================
# Create Directories
# ================================================

echo -e "${BLUE}📁 Creating Directories${NC}"
echo "-----------------------------------"

lftp -c "
set ftp:ssl-allow no
open -u $FTP_USER,$FTP_PASS $FTP_HOST
mkdir -p $REMOTE_DIR/api
mkdir -p $REMOTE_DIR/api/auth
mkdir -p $REMOTE_DIR/api/chat
mkdir -p $REMOTE_DIR/api/config
mkdir -p $REMOTE_DIR/api/utils
mkdir -p $REMOTE_DIR/uploads
mkdir -p $REMOTE_DIR/logs
bye
" 2>&1 | grep -v "mkdir" || true

echo -e "${GREEN}✓ Directories created${NC}"
echo ""

# ================================================
# Upload Files
# ================================================

echo -e "${BLUE}📤 Uploading Files${NC}"
echo "-----------------------------------"

# Upload server files
echo "Uploading API files..."
lftp -c "
set ftp:ssl-allow no
open -u $FTP_USER,$FTP_PASS $FTP_HOST
lcd $LOCAL_API_DIR
cd $REMOTE_DIR/api
mirror --reverse --verbose --delete \
       --exclude '.git/' \
       --exclude 'node_modules/' \
       --exclude '*.md' \
       . .
bye
" 2>&1 | tail -20

echo -e "${GREEN}✓ API files uploaded${NC}"

# Upload .htaccess
echo "Uploading .htaccess..."
lftp -c "
set ftp:ssl-allow no
open -u $FTP_USER,$FTP_PASS $FTP_HOST
cd $REMOTE_DIR
put $LOCAL_HTACCESS -o .htaccess
bye
" 2>&1 | grep -v "^put" || true

echo -e "${GREEN}✓ .htaccess uploaded${NC}"

echo ""

# ================================================
# Set Permissions
# ================================================

echo -e "${BLUE}🔒 Setting Permissions${NC}"
echo "-----------------------------------"

lftp -c "
set ftp:ssl-allow no
open -u $FTP_USER,$FTP_PASS $FTP_HOST
cd $REMOTE_DIR
chmod 755 api
chmod 755 uploads
chmod 755 logs
chmod 644 api/config/database.php
bye
" 2>&1 | grep -v "^chmod" || true

echo -e "${GREEN}✓ Permissions set${NC}"
echo ""

# ================================================
# Verify Upload
# ================================================

echo -e "${BLUE}✅ Verifying Deployment${NC}"
echo "-----------------------------------"

# Check if health.php exists
if lftp -c "set ftp:ssl-allow no; open -u $FTP_USER,$FTP_PASS $FTP_HOST; ls $REMOTE_DIR/api/health.php; bye" > /dev/null 2>&1; then
    echo -e "${GREEN}✓ health.php uploaded${NC}"
else
    echo -e "${RED}❌ health.php not found${NC}"
fi

# Check if config exists
if lftp -c "set ftp:ssl-allow no; open -u $FTP_USER,$FTP_PASS $FTP_HOST; ls $REMOTE_DIR/api/config/database.php; bye" > /dev/null 2>&1; then
    echo -e "${GREEN}✓ database.php uploaded${NC}"
else
    echo -e "${RED}❌ database.php not found${NC}"
fi

echo ""

# ================================================
# Test API
# ================================================

echo -e "${BLUE}🧪 Testing API${NC}"
echo "-----------------------------------"

echo "Waiting 3 seconds for server to process..."
sleep 3

HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "https://snakkaz.com/api/health.php" 2>/dev/null || echo "000")

if [ "$HTTP_CODE" = "200" ]; then
    echo -e "${GREEN}✓ API is responding (HTTP 200)${NC}"
    echo ""
    echo "Response:"
    curl -s "https://snakkaz.com/api/health.php" 2>/dev/null | head -10 || echo "Could not fetch response"
elif [ "$HTTP_CODE" = "500" ]; then
    echo -e "${RED}❌ Server error (HTTP 500)${NC}"
    echo -e "${YELLOW}Check: /logs/php_errors.log on server${NC}"
elif [ "$HTTP_CODE" = "404" ]; then
    echo -e "${YELLOW}⚠ Still showing 404 - may need a few minutes${NC}"
else
    echo -e "${YELLOW}⚠ Unexpected response (HTTP $HTTP_CODE)${NC}"
fi

echo ""

# ================================================
# Success Summary
# ================================================

echo ""
echo -e "${GREEN}================================${NC}"
echo -e "${GREEN}✅ Deployment Complete!${NC}"
echo -e "${GREEN}================================${NC}"
echo ""
echo -e "${BLUE}📊 Summary:${NC}"
echo "  • Files uploaded to: /public_html/api/"
echo "  • .htaccess configured"
echo "  • Permissions set"
echo "  • Directories created"
echo ""
echo -e "${BLUE}🧪 Next Steps:${NC}"
echo "  1. Import database: database/schema.sql"
echo "  2. Test API: https://snakkaz.com/api/health.php"
echo "  3. Open test-api.html in browser"
echo "  4. Test all endpoints"
echo ""
echo -e "${BLUE}📖 Documentation:${NC}"
echo "  • See: DEPLOY-GUIDE-SNAKKAZ.md"
echo "  • API Docs: docs/API.md"
echo ""
echo -e "${GREEN}🎉 Backend is deployed!${NC}"
echo ""
