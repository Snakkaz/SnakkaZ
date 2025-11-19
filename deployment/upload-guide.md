# 📤 Manuell Upload Guide - SnakkaZ Backend

FTP-autentisering feiler. Her er alternative metoder:

## Metode 1: cPanel File Manager (Anbefalt) ✅

### Steg 1: Last opp API-filer
1. Gå til cPanel → **File Manager**
2. Naviger til `/home/snakqsqe/public_html/`
3. Opprett mappe: `api`
4. Gå inn i `api/` mappen
5. Klikk **Upload** (topp høyre)
6. Last opp alle filer fra `server/` mappen:
   ```
   server/api/auth/register.php
   server/api/auth/login.php
   server/api/auth/logout.php
   server/api/chat/rooms.php
   server/api/chat/messages.php
   server/api/chat/send.php
   server/api/health.php
   server/config/database.php
   server/utils/Database.php
   server/utils/Auth.php
   server/utils/Response.php
   ```

### Steg 2: Last opp .htaccess
1. Naviger til `/home/snakqsqe/public_html/`
2. Last opp `deployment/.htaccess`
3. Rename til `.htaccess` (hvis nødvendig)

### Steg 3: Opprett mapper
1. I `/home/snakqsqe/public_html/`:
   - Opprett `uploads/` (chmod 755)
   - Opprett `logs/` (chmod 755)

### Steg 4: Sett rettigheter
1. Høyreklikk `api/config/database.php` → **Change Permissions** → `644`
2. Høyreklikk `uploads/` → **Change Permissions** → `755`
3. Høyreklikk `logs/` → **Change Permissions** → `755`

---

## Metode 2: FTP Client (FileZilla)

### Download FileZilla
https://filezilla-project.org/

### Innstillinger:
- **Host**: ftp.snakkaz.com
- **Port**: 21
- **Protocol**: FTP (Explicit TLS hvis tilgjengelig)
- **Username**: admin@snakkaz.com
- **Password**: SnakkaZ123!!

### Upload samme filer som i Metode 1

---

## Metode 3: Zip Upload (Raskest for mange filer)

### Steg 1: Pakk filer
```bash
cd /workspaces/SnakkaZ
zip -r backend.zip server/ deployment/.htaccess
```

### Steg 2: Last opp via File Manager
1. cPanel → File Manager
2. Naviger til `/home/snakqsqe/public_html/`
3. Upload `backend.zip`
4. Høyreklikk → **Extract**
5. Flytt innhold av `server/` til `api/`
6. Flytt `.htaccess` til root

---

## Verifiser Upload

Etter opplasting, test API:
```
https://snakkaz.com/api/health.php
```

Forventet respons:
```json
{
  "status": "success",
  "message": "SnakkaZ Chat API is running",
  "version": "1.0.0",
  "timestamp": "2025-11-19 12:34:56",
  "database": "connected"
}
```

---

## Troubleshooting FTP

Hvis FTP fortsatt ikke virker:
1. Sjekk om FTP er aktivert i cPanel
2. Opprett ny FTP-bruker i cPanel → FTP Accounts
3. Sjekk firewall-innstillinger
4. Kontakt Namecheap support
