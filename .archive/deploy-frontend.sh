#!/bin/bash
set -e

echo "🚀 SnakkaZ Frontend Deployment Script"
echo "======================================"
echo ""

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
FRONTEND_DIR="/workspaces/SnakkaZ/frontend"
BUILD_DIR="${FRONTEND_DIR}/dist"
FTP_HOST="ftp.snakkaz.com"
FTP_USER="admin@snakkaz.com"
FTP_PASS="SnakkaZ123!!"
REMOTE_PATH="/public_html"

echo -e "${BLUE}Step 1: Building Frontend...${NC}"
cd "$FRONTEND_DIR"

# Install dependencies if needed
if [ ! -d "node_modules" ]; then
    echo "Installing dependencies..."
    npm install
fi

# Build production
echo "Building production bundle..."
npm run build

if [ ! -d "$BUILD_DIR" ]; then
    echo -e "${YELLOW}⚠️  Build failed - dist folder not created${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Build successful!${NC}"
echo ""

# Check build size
BUILD_SIZE=$(du -sh "$BUILD_DIR" | cut -f1)
echo "Build size: $BUILD_SIZE"
echo ""

echo -e "${BLUE}Step 2: Deploying to snakkaz.com...${NC}"

# Create upload list (exclude .htaccess from frontend, we use backend's)
cd "$BUILD_DIR"

# Upload using lftp
lftp -c "
set ssl:verify-certificate no
open -u $FTP_USER,$FTP_PASS $FTP_HOST
lcd $BUILD_DIR
cd $REMOTE_PATH
mirror --reverse --verbose --delete --exclude-glob .htaccess ./
bye
"

if [ $? -eq 0 ]; then
    echo ""
    echo -e "${GREEN}✅ Deployment successful!${NC}"
    echo ""
    echo "🌐 Your app is now live at: https://snakkaz.com"
    echo ""
    echo "Test the following:"
    echo "  • https://snakkaz.com - Main app"
    echo "  • https://snakkaz.com/login - Login page"
    echo "  • https://snakkaz.com/register - Register page"
    echo ""
else
    echo -e "${YELLOW}⚠️  Deployment failed${NC}"
    exit 1
fi

echo "🎉 Deployment complete!"
