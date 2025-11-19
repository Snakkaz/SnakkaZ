# 🚀 SnakkaZ Chat - DEPLOY TIL SNAKKAZ.COM

**Server:** premium123 (StellarPlus)  
**Domain:** snakkaz.com  
**Database:** snakqsqe_snakkaz  
**User:** snakqsqe

---

## ✅ STEG 1: IMPORTER DATABASE (5 minutter)

### Via phpMyAdmin:

1. **Gå til phpMyAdmin** i cPanel
2. **Velg database:** `snakqsqe_snakkaz` (fra venstre sidebar)
3. **Klikk "Import" tab**
4. **Choose File:** Velg `/database/schema.sql` fra prosjektet
5. **Klikk "Go"** (nederst på siden)
6. **Vent 5 sekunder** - Du skal se meldingen "Import has been successfully finished"

### Sjekk at alt er OK:
```sql
-- Gå til "SQL" tab i phpMyAdmin og kjør:
SHOW TABLES;

-- Du skal se:
-- ✓ users
-- ✓ rooms  
-- ✓ messages
-- ✓ room_members
-- ✓ sessions
```

---

## ✅ STEG 2: OPPDATER DATABASE PASSORD (1 minutt)

**⚠️ VIKTIG:** Du må sette database passord i konfig-filen.

### Finn ditt database passord:
```
1. I cPanel -> MySQL Databases
2. Under "Current Databases" ser du passordet du satte
   (eller reset det hvis du glemte det)
```

### Oppdater konfigurasjon:
```bash
# Åpne filen: server/config/database.php
# Linje 10, endre:

define('DB_PASS', 'YOUR_DATABASE_PASSWORD_HERE');
# til:
define('DB_PASS', 'ditt_faktiske_passord');
```

### Generer JWT Secret:
```bash
# Linje 18, endre til en tilfeldig string (min 64 tegn):

define('JWT_SECRET', 'skriv_en_veldig_lang_tilfeldig_string_her_min_64_tegn_abc123xyz');
```

---

## ✅ STEG 3: UPLOAD FILER VIA FTP (10 minutter)

### FTP Credentials:
```
Host:     ftp.snakkaz.com
Username: snakqsqe
Password: [ditt cPanel passord]
Port:     21
```

### FileZilla Oppsett:
```
1. Åpne FileZilla
2. File -> Site Manager -> New Site
3. Protocol: FTP
4. Host: ftp.snakkaz.com
5. Port: 21
6. Encryption: Use explicit FTP over TLS
7. Logon Type: Normal
8. User: snakqsqe
9. Password: [ditt passord]
10. Click "Connect"
```

### Upload Struktur:
```
Local (din PC)                    Remote (server)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
server/                      →    /public_html/api/
  ├── api/                   →    /public_html/api/
  ├── config/                →    /public_html/api/config/
  └── utils/                 →    /public_html/api/utils/

deployment/.htaccess         →    /public_html/.htaccess

(opprett mappe hvis ikke eksisterer)
                                  /public_html/uploads/
                                  /public_html/logs/
```

### Detaljerte instruksjoner:
```
1. I FileZilla, naviger til: /public_html/

2. Høyreklikk -> Create directory -> "api"
3. Høyreklikk -> Create directory -> "uploads" 
4. Høyreklikk -> Create directory -> "logs"

5. Gå inn i "api" mappen på serveren

6. Fra din PC, dra:
   - Hele server/api/ mappen inn i /public_html/api/
   - Hele server/config/ mappen inn i /public_html/api/config/
   - Hele server/utils/ mappen inn i /public_html/api/utils/

7. Tilbake til /public_html/
   - Upload deployment/.htaccess til /public_html/.htaccess
```

---

## ✅ STEG 4: SETT RIKTIGE PERMISSIONS (2 minutter)

### Via cPanel File Manager:

```
1. Gå til: cPanel -> File Manager
2. Naviger til: public_html/

3. Høyreklikk på "uploads" -> Change Permissions
   - Sett til: 755
   - ☑ Apply to subdirectories

4. Høyreklikk på "logs" -> Change Permissions  
   - Sett til: 755

5. Høyreklikk på "api" -> Change Permissions
   - Sett til: 755

6. Gå inn i api/config/
   - Høyreklikk "database.php" -> Change Permissions
   - Sett til: 644 (for sikkerhet)
```

### Via cPanel Terminal (alternativ):
```bash
cd /home/snakqsqe/public_html
chmod 755 uploads
chmod 755 logs
chmod 755 api
chmod 644 api/config/database.php
```

---

## ✅ STEG 5: TEST API (3 minutter)

### 1. Health Check:
```bash
# I browser eller terminal:
curl https://snakkaz.com/api/health.php
```

**Forventet respons:**
```json
{
  "status": "ok",
  "database": "connected",
  "uploads": "writable",
  "timestamp": "2025-11-19 14:30:00",
  "version": "1.0.0"
}
```

### 2. Test Registrering:
```bash
curl -X POST https://snakkaz.com/api/auth/register.php \
  -H "Content-Type: application/json" \
  -d '{
    "username": "testuser",
    "email": "test@snakkaz.com",
    "password": "test12345",
    "display_name": "Test User"
  }'
```

**Forventet respons:**
```json
{
  "success": true,
  "message": "Registration successful",
  "data": {
    "token": "abc123...",
    "user": {
      "id": 1,
      "username": "testuser",
      "email": "test@snakkaz.com",
      "display_name": "Test User"
    }
  }
}
```

### 3. Test Login:
```bash
curl -X POST https://snakkaz.com/api/auth/login.php \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@snakkaz.com",
    "password": "test12345"
  }'
```

### 4. Test Chat Rooms (med token):
```bash
# Erstatt YOUR_TOKEN med token fra registrering/login
curl -X GET https://snakkaz.com/api/chat/rooms.php \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## 🔍 FEILSØKING

### Problem: "Database connection failed"
```bash
✓ Sjekk at database eksisterer: snakqsqe_snakkaz
✓ Sjekk at brukeren har tilgang
✓ Sjekk passord i api/config/database.php
✓ Test connection i phpMyAdmin
```

### Problem: "404 Not Found" på /api/
```bash
✓ Sjekk at .htaccess er uploaded til /public_html/
✓ Sjekk at api/ mappen eksisterer
✓ Sjekk file permissions (755 på mapper)
```

### Problem: "500 Internal Server Error"
```bash
✓ Sjekk PHP error log: /home/snakqsqe/public_html/logs/php_errors.log
✓ Åpne File Manager -> logs/php_errors.log
✓ Les siste feilmeldinger
```

### Problem: "Syntax error" i PHP filer
```bash
✓ Sjekk at alle filer er uploaded korrekt
✓ Sjekk at det ikke er BOM characters (UTF-8 uten BOM)
✓ Re-upload filene i BINARY mode (ikke ASCII)
```

### Sjekk PHP error log via cPanel:
```
1. File Manager -> public_html/logs/
2. Høyreklikk "php_errors.log" -> View
3. Les siste feilmeldinger
```

---

## ✅ VERIFISERING CHECKLIST

- [ ] Database importert: `snakqsqe_snakkaz`
- [ ] Passord oppdatert i `api/config/database.php`
- [ ] JWT_SECRET generert og satt
- [ ] Filer uploaded via FTP
- [ ] Permissions satt (755/644)
- [ ] Health check fungerer: `https://snakkaz.com/api/health.php`
- [ ] Registrering fungerer
- [ ] Login fungerer
- [ ] Token auth fungerer

---

## 🎉 NESTE STEG

Når alt over fungerer:

1. **Frontend Development**
   - React app med Telegram design
   - Koble til API endepunkter
   
2. **Real-time Chat**
   - WebSocket eller polling
   - Live meldinger
   
3. **Production Deploy**
   - Upload React build
   - SSL sertifikat (Let's Encrypt)
   - Performance optimization

---

## 📞 TRENGER HJELP?

### Quick Commands:
```bash
# Sjekk PHP versjon
php -v

# Sjekk database connection
mysql -u snakqsqe_snakkaz -p snakqsqe_snakkaz

# Tail error log
tail -f /home/snakqsqe/public_html/logs/php_errors.log
```

### cPanel Quick Links:
- **phpMyAdmin:** cPanel → Databases → phpMyAdmin
- **File Manager:** cPanel → Files → File Manager
- **Terminal:** cPanel → Advanced → Terminal
- **Error Logs:** cPanel → Metrics → Errors

---

**Lykke til!** 🚀

Hvis du får noen feilmeldinger, send meg:
1. URL som feiler
2. Feilmelding fra browser
3. php_errors.log innhold
