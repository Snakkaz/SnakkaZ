# 🎯 cPanel Terminal Deploy - Steg-for-Steg

## ✅ Forberedelse (5 min)

### Steg 1: Last ned deployment-pakke
Fra dette workspace:
```
📁 snakkaz-backend-deploy.zip (17KB)
```

### Steg 2: Åpne cPanel
1. Gå til Namecheap cPanel
2. Finn **"Advanced"** eller **"Terminal"**
3. Klikk **"Terminal"**

---

## 📤 Upload & Deploy (3 min)

### Steg 3: Last opp zip-fil
1. I cPanel, gå til **File Manager**
2. Naviger til `/home/snakqsqe/public_html/`
3. Klikk **Upload** (øverst til høyre)
4. Last opp `snakkaz-backend-deploy.zip`
5. Vent til upload er ferdig (grønn checkmark)

### Steg 4: Kjør deploy-script
1. Gå tilbake til **Terminal**
2. Kopier og lim inn disse kommandoene:

```bash
cd ~/public_html

# Last ned deploy-scriptet (eller kopier innholdet manuelt)
cat > deploy.sh << 'EOFSCRIPT'
[INNHOLD FRA cpanel-deploy.sh]
EOFSCRIPT

# Kjør scriptet
chmod +x deploy.sh
./deploy.sh
```

**ELLER enklere - kjør disse kommandoene direkte:**

```bash
cd ~/public_html
unzip -q snakkaz-backend-deploy.zip
mkdir -p api/auth api/chat api/config api/utils uploads logs
cp -r server/api/auth/* api/auth/
cp -r server/api/chat/* api/chat/
cp server/api/health.php api/
cp -r server/config/* api/config/
cp -r server/utils/* api/utils/
cp deployment/.htaccess .htaccess
rm -rf server deployment database snakkaz-backend-deploy.zip
chmod 755 api api/auth api/chat api/config api/utils uploads logs
chmod 644 api/config/database.php .htaccess
echo "✅ Deploy ferdig! Test: https://snakkaz.com/api/health.php"
```

---

## 🧪 Testing (2 min)

### Steg 5: Verifiser deployment
I Terminal, kjør:
```bash
curl https://snakkaz.com/api/health.php
```

**Forventet output:**
```json
{
  "status": "ok",
  "timestamp": "2025-11-19 12:34:56",
  "version": "1.0.0",
  "database": "connected",
  "uploads": "writable"
}
```

### Steg 6: Sjekk filstruktur
```bash
cd ~/public_html
tree -L 3 api
```

**Forventet:**
```
api/
├── auth/
│   ├── login.php
│   ├── logout.php
│   └── register.php
├── chat/
│   ├── messages.php
│   ├── rooms.php
│   └── send.php
├── config/
│   └── database.php
├── utils/
│   ├── Auth.php
│   ├── Database.php
│   └── Response.php
└── health.php
```

---

## 🔍 Troubleshooting

### Hvis API returnerer 500:
```bash
# Sjekk error log
tail -20 ~/public_html/logs/php_errors.log

# Eller PHP error log
tail -20 ~/error_log
```

### Hvis API returnerer 404:
```bash
# Sjekk at .htaccess finnes
ls -la ~/public_html/.htaccess

# Sjekk at health.php finnes
ls -la ~/public_html/api/health.php
```

### Hvis database error:
```bash
# Test database connection
php -r "
\$db = new PDO('mysql:host=localhost;dbname=snakqsqe_snakkaz', 'snakqsqe_SnakkaZ', 'SnakkaZ123!!');
echo 'Database OK';
"
```

---

## ✅ Suksess-kriterier

Du er ferdig når:
- ✅ `https://snakkaz.com/api/health.php` returnerer JSON med status "ok"
- ✅ Database viser "connected"
- ✅ Uploads viser "writable"
- ✅ Ingen errors i logs

---

## 📱 Neste Steg

1. **Test alle endpoints**: Åpne `test-api.html` i browser
2. **Sjekk logs**: Overvåk `tail -f ~/public_html/logs/php_errors.log`
3. **Bygg frontend**: React chat-interface (neste fase)

---

**Estimert total tid: 10 minutter**
