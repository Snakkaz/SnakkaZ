#!/bin/bash
set -e

echo "🚀 Deploying SnakkaZ Frontend..."
echo "================================"

# Configuration
FTP_HOST="ftp.snakkaz.com"
FTP_USER="admin@snakkaz.com"
FTP_PASS="SnakkaZ123!!"
LOCAL_DIR="/workspaces/SnakkaZ/frontend/dist"
REMOTE_DIR="/public_html"

# Check if build exists
if [ ! -d "$LOCAL_DIR" ]; then
    echo "❌ Build not found! Run 'npm run build' first"
    exit 1
fi

echo "📦 Build found at: $LOCAL_DIR"
echo ""

# Upload files using curl
cd "$LOCAL_DIR"

echo "📤 Uploading index.html..."
curl -T index.html "ftp://$FTP_HOST$REMOTE_DIR/" --user "$FTP_USER:$FTP_PASS" --ftp-create-dirs

echo "📤 Uploading vite.svg..."
curl -T vite.svg "ftp://$FTP_HOST$REMOTE_DIR/" --user "$FTP_USER:$FTP_PASS" 2>/dev/null || true

echo "📤 Uploading assets..."
for file in assets/*; do
    if [ -f "$file" ]; then
        filename=$(basename "$file")
        echo "  Uploading $filename..."
        curl -T "$file" "ftp://$FTP_HOST$REMOTE_DIR/assets/" --user "$FTP_USER:$FTP_PASS" --ftp-create-dirs -s
    fi
done

echo ""
echo "✅ Deployment complete!"
echo ""
echo "🌐 Your app is live at: https://snakkaz.com"
echo ""
echo "Test it:"
echo "  • https://snakkaz.com - Homepage"
echo "  • https://snakkaz.com/login - Login page"
echo "  • https://snakkaz.com/register - Register page"
echo ""
