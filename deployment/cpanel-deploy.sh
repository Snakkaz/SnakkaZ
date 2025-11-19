#!/bin/bash
# SnakkaZ Backend Deploy - cPanel Terminal Script
# Kjør dette i cPanel → Advanced → Terminal

set -e

echo "🚀 SnakkaZ Backend Deploy"
echo "=========================="
echo ""

# Farger
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Gå til riktig directory
cd ~/public_html || exit 1
echo -e "${GREEN}✓${NC} I public_html/"
echo ""

# Sjekk om api/ allerede eksisterer
if [ -d "api" ]; then
    echo -e "${YELLOW}⚠${NC} api/ eksisterer allerede"
    echo "Lager backup..."
    BACKUP_DIR="api-backup-$(date +%Y%m%d-%H%M%S)"
    mv api "$BACKUP_DIR"
    echo -e "${GREEN}✓${NC} Backup: $BACKUP_DIR"
    echo ""
fi

# Opprett directories
echo -e "${BLUE}📁 Oppretter mapper...${NC}"
mkdir -p api/auth
mkdir -p api/chat
mkdir -p api/config
mkdir -p api/utils
mkdir -p uploads
mkdir -p logs
echo -e "${GREEN}✓${NC} Mapper opprettet"
echo ""

# Finn og pakk ut zip-fil
echo -e "${BLUE}📦 Sjekker etter zip-fil...${NC}"
if [ -f "snakkaz-backend-deploy.zip" ]; then
    echo -e "${GREEN}✓${NC} Funnet: snakkaz-backend-deploy.zip"
    echo "Pakker ut..."
    unzip -q snakkaz-backend-deploy.zip
    
    # Flytt filer til riktig plass
    echo -e "${BLUE}📤 Flytter filer...${NC}"
    
    # Flytt server/* til api/
    if [ -d "server/api/auth" ]; then
        cp -r server/api/auth/* api/auth/ 2>/dev/null || true
        echo -e "${GREEN}✓${NC} Auth endpoints"
    fi
    
    if [ -d "server/api/chat" ]; then
        cp -r server/api/chat/* api/chat/ 2>/dev/null || true
        echo -e "${GREEN}✓${NC} Chat endpoints"
    fi
    
    if [ -f "server/api/health.php" ]; then
        cp server/api/health.php api/
        echo -e "${GREEN}✓${NC} Health check"
    fi
    
    if [ -d "server/config" ]; then
        cp -r server/config/* api/config/ 2>/dev/null || true
        echo -e "${GREEN}✓${NC} Config"
    fi
    
    if [ -d "server/utils" ]; then
        cp -r server/utils/* api/utils/ 2>/dev/null || true
        echo -e "${GREEN}✓${NC} Utils"
    fi
    
    # Flytt .htaccess
    if [ -f "deployment/.htaccess" ]; then
        cp deployment/.htaccess .htaccess
        echo -e "${GREEN}✓${NC} .htaccess"
    fi
    
    # Rydd opp
    rm -rf server deployment database
    rm snakkaz-backend-deploy.zip
    echo -e "${GREEN}✓${NC} Ryddet temp-filer"
else
    echo -e "${YELLOW}⚠${NC} Fant ikke snakkaz-backend-deploy.zip"
    echo "Last opp filen via File Manager først, så kjør dette scriptet på nytt"
    exit 1
fi

echo ""

# Sett rettigheter
echo -e "${BLUE}🔒 Setter rettigheter...${NC}"
chmod 755 api
chmod 755 api/auth
chmod 755 api/chat
chmod 755 api/config
chmod 755 api/utils
chmod 755 uploads
chmod 755 logs
chmod 644 api/config/database.php
chmod 644 .htaccess
echo -e "${GREEN}✓${NC} Rettigheter satt"
echo ""

# Verifiser
echo -e "${BLUE}✅ Verifiserer...${NC}"
FILE_COUNT=$(find api -type f -name "*.php" | wc -l)
echo -e "${GREEN}✓${NC} $FILE_COUNT PHP-filer installert"

if [ -f "api/health.php" ]; then
    echo -e "${GREEN}✓${NC} health.php"
fi
if [ -f "api/config/database.php" ]; then
    echo -e "${GREEN}✓${NC} database.php"
fi
if [ -f ".htaccess" ]; then
    echo -e "${GREEN}✓${NC} .htaccess"
fi

echo ""

# Test med curl
echo -e "${BLUE}🧪 Tester API...${NC}"
echo "Venter 2 sekunder..."
sleep 2

if command -v curl &> /dev/null; then
    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "https://snakkaz.com/api/health.php" 2>/dev/null || echo "000")
    
    if [ "$HTTP_CODE" = "200" ]; then
        echo -e "${GREEN}✓${NC} API responderer (HTTP 200)"
        echo ""
        echo "Respons:"
        curl -s "https://snakkaz.com/api/health.php" 2>/dev/null | head -20
    elif [ "$HTTP_CODE" = "500" ]; then
        echo -e "${YELLOW}⚠${NC} Server error (HTTP 500)"
        echo "Sjekk: ~/public_html/logs/php_errors.log"
    else
        echo -e "${YELLOW}⚠${NC} HTTP $HTTP_CODE - kan ta noen minutter før API er tilgjengelig"
    fi
else
    echo -e "${YELLOW}⚠${NC} curl ikke tilgjengelig, test manuelt:"
    echo "https://snakkaz.com/api/health.php"
fi

echo ""
echo -e "${GREEN}================================${NC}"
echo -e "${GREEN}✅ Deploy Fullført!${NC}"
echo -e "${GREEN}================================${NC}"
echo ""
echo "📊 Struktur:"
echo "  ~/public_html/"
echo "  ├── api/           (Backend API)"
echo "  ├── uploads/       (Filopplasting)"
echo "  ├── logs/          (Error logs)"
echo "  └── .htaccess      (Sikkerhet)"
echo ""
echo "🧪 Test API:"
echo "  https://snakkaz.com/api/health.php"
echo ""
echo "📖 Neste steg:"
echo "  1. Test alle endpoints i test-api.html"
echo "  2. Sjekk logs: tail -f ~/public_html/logs/php_errors.log"
echo "  3. Bygg frontend (React)"
echo ""
