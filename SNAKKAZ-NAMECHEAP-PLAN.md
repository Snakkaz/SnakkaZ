# 🚀 SNAKKAZ CHAT - NAMECHEAP HOSTING PLAN

**Dato:** 19. November 2025  
**Domain:** www.SnakkaZ.com  
**Hosting:** Namecheap Stellar  
**Server:** cPanel + phpMyAdmin + SSH

---

## 🎯 MÅL

Lag en **profesjonell, rask, sikker** chat-plattform inspirert av Telegram, optimalisert for Namecheap shared hosting.

### Funksjoner:
- ✅ Real-time chat (WebSocket eller long-polling)
- ✅ Private & gruppe samtaler
- ✅ Fil/bilde upload
- ✅ Brukerautentisering (JWT)
- ✅ Responsiv design (mobil + desktop)
- ✅ Sikkerhet (SQL injection, XSS, CSRF protection)

---

## 🗂️ ARKITEKTUR

### Frontend
- **React 18** + TypeScript + Vite
- **TailwindCSS** for styling
- **Socket.io-client** eller long-polling
- **Deploy:** Build til `dist/` → Upload til `public_html/`

### Backend
- **Option A (PHP):**
  - PHP 8.x REST API
  - Composer for dependencies
  - MySQL PDO for database
  - Simple WebSocket fallback
  
- **Option B (Node.js):**
  - Express + Socket.io
  - MySQL2 library
  - Requires Node.js in cPanel

### Database
- **MySQL 8.x**
- Tables: `users`, `rooms`, `messages`, `room_members`, `sessions`
- Optimized indexes for performance

---

## 📊 DATABASE SCHEMA

```sql
-- Users Table
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  display_name VARCHAR(100),
  avatar_url VARCHAR(255),
  status ENUM('online', 'offline', 'away') DEFAULT 'offline',
  last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_username (username),
  INDEX idx_email (email)
);

-- Rooms Table
CREATE TABLE rooms (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  type ENUM('private', 'group') DEFAULT 'private',
  creator_id INT NOT NULL,
  avatar_url VARCHAR(255),
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (creator_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_type (type),
  INDEX idx_creator (creator_id)
);

-- Messages Table
CREATE TABLE messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  room_id INT NOT NULL,
  user_id INT NOT NULL,
  content TEXT NOT NULL,
  message_type ENUM('text', 'image', 'file', 'audio') DEFAULT 'text',
  file_url VARCHAR(255),
  is_read BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_room (room_id),
  INDEX idx_user (user_id),
  INDEX idx_created (created_at)
);

-- Room Members Table
CREATE TABLE room_members (
  id INT AUTO_INCREMENT PRIMARY KEY,
  room_id INT NOT NULL,
  user_id INT NOT NULL,
  role ENUM('admin', 'member') DEFAULT 'member',
  joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY unique_member (room_id, user_id),
  INDEX idx_room (room_id),
  INDEX idx_user (user_id)
);

-- Sessions Table (JWT alternative for shared hosting)
CREATE TABLE sessions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  token VARCHAR(255) UNIQUE NOT NULL,
  expires_at TIMESTAMP NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_token (token),
  INDEX idx_user (user_id)
);
```

---

## 🚀 DEPLOYMENT PROSESS

### 1. Local Development
```bash
cd /workspaces/SnakkaZ
npm run dev          # Frontend
php -S localhost:8000  # Backend (for testing)
```

### 2. Build Production
```bash
cd client
npm run build       # Creates dist/ folder
```

### 3. Deploy via FTP
```bash
# Upload client/dist/ → public_html/
# Upload server/ → public_html/api/
# Upload .htaccess → public_html/
```

### 4. Database Setup
- Opprett database i cPanel
- Import `database/schema.sql` via phpMyAdmin
- Oppdater `server/config/database.php` med credentials

---

## 📝 TODO LIST

- [ ] Få cPanel credentials
- [ ] Sjekk PHP/Node.js versjon på server
- [ ] Opprett MySQL database
- [ ] Setup project structure
- [ ] Implement backend API
- [ ] Build frontend interface
- [ ] Test local development
- [ ] Deploy to production
- [ ] SSL certificate setup
- [ ] Performance testing

---

## 🔐 SIKKERHET

- Password hashing (bcrypt/argon2)
- SQL injection prevention (Prepared statements)
- XSS protection (Input sanitization)
- CSRF tokens
- Rate limiting
- HTTPS only (Let's Encrypt SSL)
- Secure file uploads (validation, size limits)

---

## 📞 NESTE STEG

Venter på:
1. cPanel login detaljer
2. Database credentials
3. FTP info
4. Valg: PHP eller Node.js backend?
