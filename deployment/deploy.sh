#!/bin/bash

# ================================================
# SnakkaZ Chat - Deployment Script
# Deploy to Namecheap Hosting via FTP
# ================================================

echo "🚀 SnakkaZ Deployment Script"
echo "================================"

# Configuration (UPDATE THESE!)
FTP_HOST="ftp.snakkaz.com"
FTP_USER="your_cpanel_username"
FTP_PASS="your_cpanel_password"
REMOTE_DIR="/public_html"

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# ================================================
# Step 1: Build Frontend
# ================================================

echo -e "${YELLOW}📦 Building Frontend...${NC}"
cd client

if [ ! -d "node_modules" ]; then
    echo "Installing npm dependencies..."
    npm install
fi

echo "Building production bundle..."
npm run build

if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Frontend build failed!${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Frontend built successfully${NC}"
cd ..

# ================================================
# Step 2: Prepare Files
# ================================================

echo -e "${YELLOW}📋 Preparing files for deployment...${NC}"

# Create temp deployment folder
rm -rf deployment/temp
mkdir -p deployment/temp

# Copy frontend build
cp -r client/dist/* deployment/temp/

# Copy backend
mkdir -p deployment/temp/api
cp -r server/* deployment/temp/api/

# Copy .htaccess
cp deployment/.htaccess deployment/temp/

# Copy uploads folder (if exists)
if [ -d "server/uploads" ]; then
    mkdir -p deployment/temp/uploads
    cp -r server/uploads/* deployment/temp/uploads/
fi

echo -e "${GREEN}✅ Files prepared${NC}"

# ================================================
# Step 3: Upload via FTP
# ================================================

echo -e "${YELLOW}📤 Uploading to server...${NC}"

# Check if lftp is installed
if ! command -v lftp &> /dev/null; then
    echo -e "${RED}❌ lftp not found. Installing...${NC}"
    
    # Try to install lftp
    if command -v apt-get &> /dev/null; then
        sudo apt-get install -y lftp
    elif command -v yum &> /dev/null; then
        sudo yum install -y lftp
    else
        echo -e "${RED}Cannot install lftp. Please install manually.${NC}"
        exit 1
    fi
fi

# Upload using lftp
lftp -c "
set ftp:ssl-allow no
open -u $FTP_USER,$FTP_PASS $FTP_HOST
mirror -R --verbose --delete deployment/temp $REMOTE_DIR
bye
"

if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Upload failed!${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Files uploaded successfully${NC}"

# ================================================
# Step 4: Cleanup
# ================================================

echo -e "${YELLOW}🧹 Cleaning up...${NC}"
rm -rf deployment/temp

# ================================================
# Step 5: Post-Deployment Tasks
# ================================================

echo ""
echo -e "${GREEN}================================${NC}"
echo -e "${GREEN}✅ Deployment Complete!${NC}"
echo -e "${GREEN}================================${NC}"
echo ""
echo "Next steps:"
echo "1. Import database/schema.sql via phpMyAdmin"
echo "2. Update api/config/database.php with DB credentials"
echo "3. Test at: https://www.snakkaz.com"
echo ""
echo "Need help? Check docs/DEPLOYMENT.md"
echo ""
