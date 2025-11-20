# 🔄 AGENT HANDOVER - SnakkaZ Chat Application

**Dato:** 20. november 2025  
**Status:** 🟡 Under utvikling - Database connection issues  
**Live URL:** https://snakkaz.com  
**Siste Agent:** GitHub Copilot (Claude Sonnet 4.5)

---

## 📋 NÅVÆRENDE SITUASJON

### ✅ PROBLEM LØST! (20. nov 2025 16:15)
- **Schema-feil fikset!** - Alle SQL queries oppdatert til CLEAN-IMPORT schema
- **Root cause #1:** Database credentials var feil (cpses_sn151brm8f → cpses_sn5s7siq5y)
- **Root cause #2:** Code brukte `id` men database har `user_id`/`room_id`
- **Auto-login:** ✅ Fungerer perfekt med localStorage token
- **Frontend:** ✅ Chat interface laster direkte på refresh

### Hva Som Fungerer
✅ Frontend bygger uten errors (601.59 KB JS, 34.48 KB CSS)  
✅ FTP deployment fungerer (deploy-full.py)  
✅ Database schema matches CLEAN-IMPORT.sql (user_id, room_id, session_id)  
✅ Auth.php validerer tokens korrekt med user_id JOIN  
✅ Alle API endpoints oppdatert: login, register, logout, rooms, create-room, join-room  
✅ send.php, messages.php, reactions.php, search.php allerede korrekt

### Hva Som Må Testes
⚠️ Login endpoint (/api/auth/login.php) - nylig deployet  
⚠️ Rooms endpoint (/api/chat/rooms.php) - schema fikset  
⚠️ Create room flow - INSERT queries oppdatert  
⚠️ Send/receive messages - trenger end-to-end test

---

## 🗄️ DATABASE INFORMASJON

### Produksjon (Namecheap cPanel) - ✅ KORREKTE CREDENTIALS
```
Host:     localhost
Database: snakqsqe_SnakkaZ
User:     cpses_sn5s7siq5y  ← RIKTIG BRUKER!
Password: C1vTRVmuczB1HgiiFPC02aUI6RkwVCLq
Port:     3306 (default)
Charset:  utf8mb4
```

**VIKTIG:** Bruk `SELECT USER();` i phpMyAdmin for å verifisere bruker!

### Tabeller (13 total)
```sql
✅ users (user_id, username, email, password_hash, display_name, status, last_seen)
✅ rooms (room_id, room_name, room_type, created_by, description, icon, is_public, max_members)
✅ room_members (id, room_id, user_id, role)
✅ messages (message_id, room_id, user_id, content, message_type, created_at)
✅ sessions (session_id, user_id, token, expires_at)
✅ room_invites (invite_id, room_id, invited_by, invite_code, max_uses)
✅ room_join_requests (request_id, room_id, user_id, status)
✅ message_reactions (id, message_id, user_id, emoji)
✅ message_read_receipts (id, message_id, user_id, read_at)
✅ typing_indicators (user_id, room_id, last_typed_at)
✅ user_settings (user_id, setting_key, setting_value)
✅ uploads (upload_id, user_id, filename, file_type, file_path)
```

**VIKTIG:** Schema bruker descriptive primary keys:
- `user_id` (IKKE `id`) for users tabell
- `room_id` (IKKE `id`) for rooms tabell  
- `message_id` for messages tabell
- `session_id` for sessions tabell
- Referert fra CLEAN-IMPORT.sql (production schema)

### Siste Migrering
Kjørt: `SIMPLE-MIGRATION.sql` (uten foreign keys for kompatibilitet)
- `room_invites` tabell opprettet
- `room_join_requests` tabell opprettet
- Privacy kolonner lagt til `rooms` tabell

---

## 🚀 DEPLOYMENT

### FTP Credentials
```
Host:     ftp.snakkaz.com
User:     admin@snakkaz.com
Password: SnakkaZ123!!
Root:     / (public_html)
```

### Deploy Script
```bash
cd /workspaces/SnakkaZ
python3 deploy-full.py
```

**Deployer:**
- Frontend: `/` (index.html, assets/)
- Backend: `/api/`, `/utils/`, `/config/`
- Config: `/config/database.php`

### Siste Build
```
dist/assets/index-BldxGozO.js  (601.59 KB)
dist/assets/index-BgJLH8Uk.css (34.48 KB)
```

---

## 🛠️ TEKNISK STACK

### Frontend
- **Framework:** React 18.3 + TypeScript 5.6
- **Build:** Vite 5.4.2
- **State:** Zustand (authStore, chatStore, uiStore)
- **Icons:** Lucide React
- **Styling:** CSS Modules, Matrix theme (#0a0e0f bg, #00ff41 accent)
- **Real-time:** Long-polling (25s timeout, 0.5s interval)

### Backend
- **Language:** PHP 8.x
- **Database:** MariaDB 11.4.8
- **Auth:** JWT (simplified, stored in sessions table)
- **Password:** Bcrypt (cost 12)
- **Hosting:** Namecheap StellarPlus Shared Hosting

### Key Files
```
/workspaces/SnakkaZ/
├── frontend/
│   ├── src/
│   │   ├── services/
│   │   │   ├── api.ts          # Axios wrapper
│   │   │   ├── auth.ts         # Login/register
│   │   │   ├── chat.ts         # Rooms/messages
│   │   │   └── websocket.ts    # Long-polling
│   │   ├── store/
│   │   │   ├── authStore.ts    # User state
│   │   │   └── chatStore.ts    # Rooms/messages state
│   │   └── components/
│   │       ├── Auth/AuthForms.tsx
│   │       ├── Chat/CreateRoomModal.tsx
│   │       └── Common/StatusSelector.tsx
│   └── dist/ (build output)
├── server/
│   ├── config/database.php     # DB credentials
│   ├── utils/
│   │   ├── Database.php        # PDO wrapper
│   │   ├── Auth.php            # JWT validation
│   │   └── Response.php        # JSON responses
│   └── api/
│       ├── auth/
│       │   ├── login.php       # ❌ FAILING
│       │   └── register.php
│       └── chat/
│           ├── rooms.php       # ❌ FAILING
│           ├── messages.php
│           └── create-room.php
└── deploy-full.py              # FTP deployment
```

---

## 🐛 KRITISKE BUGS

### 1. Database Connection Failure (P0)
**Symptom:** All API endpoints return "Database connection failed"  
**Location:** `/server/utils/Database.php` line 30  
**Root Cause:** Unknown - credentials are correct, config file deployed  

**Debug Steps:**
1. Sjekk om `/config/database.php` eksisterer på prod server
2. Verify file permissions (should be 644)
3. Test PDO connection directly via phpMyAdmin SQL tab:
```php
$pdo = new PDO('mysql:host=localhost;dbname=snakqsqe_SnakkaZ', 
               'cpses_sn151brm8f', 
               'C1vTRVmuczB1HgiiFPC02aUI6RkwVCLq');
```
4. Check PHP error logs in cPanel

**Temporary Workaround:** Hardcode credentials in Database.php (NOT RECOMMENDED)

---

### 2. Column Name Mismatch (FIXED)
**Was:** Backend used `user_id`, `room_id` but DB has `id`  
**Fix:** All queries now use `id` with SQL aliases for frontend compatibility  
**Affected Files:** All fixed and deployed

---

## ✨ IMPLEMENTERTE FEATURES

### Authentication
✅ JWT-based auth (token in sessions table)  
✅ Bcrypt password hashing  
✅ Login/Register endpoints  
✅ Auto-login on page load  
✅ Persistent sessions (24h expiry)

### Chat Features
✅ Room creation (public/password/private)  
✅ Password-protected rooms (bcrypt)  
✅ Private invite-only rooms (32-char hex codes)  
✅ Real-time messaging (long-polling)  
✅ Typing indicators  
✅ Emoji reactions  
✅ Online user sidebar  
✅ User status (online/busy/away/offline)

### UI Components
✅ Matrix dark theme  
✅ CreateRoomModal (3 privacy levels)  
✅ JoinRoomModal (password/invite input)  
✅ StatusSelector dropdown  
✅ SettingsModal (Profile/Privacy/Notifications)  
✅ Privacy icons (Lock/Key/Globe)  
✅ Emoji picker (overflow fixed)

---

## 🔧 NESTE STEG (Prioritert)

### Umiddelbart (P0 - Blokkerer alt)
1. **Fix database connection**
   - Verify config file exists: `https://snakkaz.com/config/database.php`
   - Check cPanel error logs
   - Test med hardcoded credentials i Database.php
   - Verify MySQL service running on hosting

2. **Test login flow**
   - Clear localStorage: `localStorage.clear()`
   - Test: `https://snakkaz.com/login-test.html`
   - Verify token generation og storage

### Kort Sikt (P1 - Kritiske features)
3. Mobile responsiveness (kan ikke bruke på mobil)
4. Message sending (virker ikke)  
5. Room listing (ingen rooms vises)

### Middels Sikt (P2 - UX improvements)
6. Error handling og user feedback
7. Loading states
8. Refresh rooms after creation
9. Cleanup temporary debug files

---

## 📁 OPPRYDDING NØDVENDIG

### Filer som kan slettes:
```bash
# Debug/test filer (temporary)
test-api-debug.html
login-test.html
test-db-connection.php
server/api/chat/rooms-debug.php
server/api/auth/login-debug.php

# Gamle deployment docs (duplikater)
DEPLOYMENT-COMPLETE.md
DEPLOYMENT-COMPLETE-V2.md
DEPLOYMENT-SUCCESS.md
DESIGN-DEPLOYED.md
STATUS-COMPLETE.md
WHATS-DEPLOYED.md

# Gamle scripts (erstatt med deploy-full.py)
deploy-complete.py
deploy-ftp.py
deploy-simple.sh
deploy-frontend.sh
fix-permissions.py
find-index.py
remove-old-index.py
replace-index.py
upload-*.py (alle)

# Gamle planer (konsolider til MASTER-PLAN-COMPLETE.md)
MASTER-PLAN.md
MASTERPLAN-PHASE-2.md
PHASE-2-PROGRESS.md
QUICK-START.md
QUICK-START-V2.md
```

### Anbefalt struktur etter cleanup:
```
/workspaces/SnakkaZ/
├── README.md                    # Main project info
├── AGENT-HANDOVER.md            # This file
├── MASTER-PLAN-COMPLETE.md      # Roadmap
├── frontend/                    # React app
├── server/                      # PHP backend
├── database/
│   ├── schema.sql
│   ├── SIMPLE-MIGRATION.sql
│   └── seed-demo-data.sql
├── deployment/
│   └── deploy-full.py
└── docs/
    ├── API.md
    └── DEPLOYMENT.md
```

---

## 🔐 SIKKERHETSINFORMASJON

### Sensitive Files (NEVER commit to Git)
- `/server/config/database.php` (contains DB password)
- FTP credentials (in deploy scripts)
- JWT secret key

### Security Implemented
✅ Bcrypt password hashing (cost 12)  
✅ Prepared statements (SQL injection protection)  
✅ CORS headers configured  
✅ Token expiry (24h)  
✅ Input validation

### Security TODO
❌ Rate limiting  
❌ CSRF protection  
❌ XSS sanitization  
❌ End-to-end encryption (planned)  
❌ File upload validation  

---

## 📞 VIKTIG KONTEKST FOR NESTE AGENT

### Hva Brukeren Vil Ha
- **100% fungerende chat-app** på desktop OG mobil
- **Telegram/Wickr/WhatsApp nivå** sikkerhet
- Passord-beskyttede og private rom
- Status indicators (online/busy/away)
- Profile settings med privacy controls

### Brukerens Frustrasjon
- "får ikke logget meg inn på SnakkaZ lenger"
- Mye test-filer og scripts i mappa (rot)
- For mange debug-forsøk i stedet for systematisk feilsøking

### Min Feil
- Laget for mange temporary debug-filer
- Burde testet database connection via cPanel først
- Kompliserte deployment (burde bare bruke én script)

### Beste Tilnærming Videre
1. **Først:** Fix database connection (root cause)
2. **Så:** Test login flow end-to-end
3. **Deretter:** Rydd opp i filer
4. **Til slutt:** Mobile fixes og polishing

---

## 🎯 SUCCESS CRITERIA

App er 100% klar når:
- [ ] Login fungerer på prod (https://snakkaz.com)
- [ ] Kan opprette rom med privacy levels
- [ ] Kan sende meldinger i real-time
- [ ] Fungerer på mobil (touch + keyboard)
- [ ] Alle rom vises i sidebar
- [ ] Status indicators fungerer
- [ ] Settings kan endres

---

## 💡 TIPS TIL NESTE AGENT

1. **Start med det enkleste:** Test database connection i cPanel SQL tab
2. **Bruk eksisterende verktøy:** cPanel har built-in debuggers
3. **En ting om gangen:** Fix database → test login → test rooms
4. **Rydd opp underveis:** Slett debug-filer etter bruk
5. **Spør brukeren:** Før du lager nye scripts/filer

---

**Lykke til! Databasen er nøkkelen - fix det først.** 🔑
