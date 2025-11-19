# 🎯 SnakkaZ Backend - Komplett Oversikt

## 📊 Hva Er Bygget (457 linjer kode)

### Backend Struktur
```
server/
├── api/
│   ├── auth/                    # Autentisering
│   │   ├── register.php         # Registrer ny bruker
│   │   ├── login.php            # Logg inn
│   │   └── logout.php           # Logg ut
│   ├── chat/                    # Chat funksjoner
│   │   ├── rooms.php            # Hent/opprett rom
│   │   ├── messages.php         # Hent meldinger
│   │   └── send.php             # Send melding
│   └── health.php               # System health check
├── config/
│   └── database.php             # Database config (FERDIG KONFIGURERT)
└── utils/
    ├── Database.php             # PDO database wrapper
    ├── Auth.php                 # Token autentisering
    └── Response.php             # JSON response helper
```

### Database (6 tabeller - allerede importert ✅)
- `users` - Brukere med bcrypt passord
- `rooms` - Chat-rom (private/group)
- `messages` - Meldinger med multimedia
- `room_members` - Rom-medlemskap
- `sessions` - Auth tokens
- `user_recent_room` - View for siste rom

---

## 🔐 Sikkerhet (Enterprise-nivå)

✅ **Password Hashing**: bcrypt (cost 12)  
✅ **SQL Injection**: Prepared statements  
✅ **XSS Protection**: Headers + input sanitization  
✅ **CORS**: Konfigurert for snakkaz.com  
✅ **CSRF**: Token-based sessions  
✅ **Rate Limiting**: 100 req/time  
✅ **HTTPS**: Enforced via .htaccess  

---

## 📡 API Endpoints

### Autentisering
```bash
POST /api/auth/register.php
{
  "username": "john",
  "email": "john@example.com", 
  "password": "SecurePass123",
  "display_name": "John Doe"
}
→ Returns: user object + auth token

POST /api/auth/login.php
{
  "email": "john@example.com",
  "password": "SecurePass123"
}
→ Returns: user object + auth token

POST /api/auth/logout.php
Headers: Authorization: Bearer {token}
→ Invalidates session
```

### Chat
```bash
GET /api/chat/rooms.php
Headers: Authorization: Bearer {token}
→ Returns: Array of user's rooms with last message

POST /api/chat/rooms.php
{
  "name": "General Chat",
  "type": "group",
  "description": "Main discussion"
}
→ Creates new room

GET /api/chat/messages.php?room_id=1&limit=50&offset=0
Headers: Authorization: Bearer {token}
→ Returns: Paginated messages

POST /api/chat/send.php
{
  "room_id": 1,
  "content": "Hello world!",
  "type": "text"
}
→ Sends message, updates room timestamp
```

### System
```bash
GET /api/health.php
→ Returns: System status, DB connection, version
```

---

## 🚀 3 Upload-Alternativer

### Alternativ 1: SSH/SCP (Raskest om vi har tilgang)
```bash
# Test SSH først
ssh snakqsqe@snakkaz.com -p 22

# Hvis SSH virker, upload alt med scp:
scp -r server/* snakqsqe@snakkaz.com:/home/snakqsqe/public_html/api/
scp deployment/.htaccess snakqsqe@snakkaz.com:/home/snakqsqe/public_html/
```

### Alternativ 2: cPanel API (Programmatisk)
```bash
# Bruk cPanel UAPI via curl
curl -H "Authorization: Bearer {token}" \
  https://snakkaz.com:2083/execute/Fileman/upload_files
```

### Alternativ 3: File Manager GUI (Manuelt)
```
1. cPanel → File Manager
2. Upload snakkaz-backend-deploy.zip
3. Extract → Flytt filer
```

---

## 🧪 Test Plan

Etter upload, kjør test-api.html som tester:
1. Health check
2. User registration
3. Login
4. Create room
5. Send message
6. Fetch messages
7. Logout

---

## 📁 Filer Klare Til Upload

✅ `snakkaz-backend-deploy.zip` - Komplett pakke (11 PHP-filer + .htaccess + schema.sql)  
✅ Database credentials: Konfigurert med snakqsqe_SnakkaZ / SnakkaZ123!!  
✅ JWT Secret: Generert sikker 64-tegns nøkkel  

---

## 💡 Neste Steg

La oss teste upload-alternativene i rekkefølge:
1. **SSH først** - raskest og mest robust
2. **cPanel API** - hvis SSH ikke virker  
3. **File Manager** - siste utvei

Hvilken vil du prøve først?
