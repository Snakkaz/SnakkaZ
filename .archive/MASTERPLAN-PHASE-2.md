# 🚀 SnakkaZ MASTERPLAN - Phase 2 (LIVE Forbedringer)

**Status:** ✅ LIVE på https://snakkaz.com  
**Dato:** 19. November 2025  
**Server:** premium123 (StellarPlus)  
**Path:** /home/snakqsqe/public_html

---

## 🎯 NÅVÆRENDE STATUS

### ✅ Hva Fungerer (Phase 1 Complete)
- [x] Backend API (8 endpoints) @ https://snakkaz.com/api
- [x] MySQL Database (6 tabeller, 11.4.8-MariaDB)
- [x] React Frontend (deployed)
- [x] Authentication (register/login)
- [x] Basic chat struktur
- [x] HTTPS (SSL aktiv)
- [x] cPanel tilgang
- [x] FTP tilgang

### ⚠️ Hva Mangler
- [ ] WebSocket for real-time chat
- [ ] Demo chat-rom med data
- [ ] File upload funksjonalitet
- [ ] Emoji picker
- [ ] Typing indicators (live)
- [ ] Online status (live)
- [ ] Push notifications
- [ ] Message reactions
- [ ] Search i meldinger
- [ ] User profiles med avatars
- [ ] Admin panel

---

## 📋 PHASE 2 - MASTERPLAN

### 🎯 Hovedmål
1. **Gjøre appen 100% funksjonell** (ikke bare UI)
2. **Telegram-lignende features** (real-time, smooth UX)
3. **Open-source integrasjoner** (MCP, beste praksis)
4. **Production-ready** (sikkerhet, performance, skalerbarhet)

---

## 🔥 SPRINT 1: Real-time Chat (Uke 1)

### Mål: Få real-time meldinger til å fungere

#### 1.1 WebSocket Backend (PHP Ratchet)
**Tools/Libraries:**
- Ratchet (PHP WebSocket library)
- Redis for pub/sub (optional)
- Supervisor for process management

**Tasks:**
```bash
# Install Ratchet via Composer
composer require cboden/ratchet

# Create WebSocket server
server/websocket/
├── ChatServer.php       # Main WebSocket handler
├── ConnectionManager.php # Track active connections
└── start.php            # Server startup script
```

**Files to Create:**
- [ ] `server/websocket/ChatServer.php`
- [ ] `server/websocket/ConnectionManager.php`
- [ ] `server/websocket/start.php`
- [ ] Supervisor config for process management

**Implementation:**
```php
// ChatServer.php - Real-time message broadcasting
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class ChatServer implements MessageComponentInterface {
    protected $clients;
    protected $rooms;
    
    public function onMessage(ConnectionInterface $from, $msg) {
        // Parse message
        // Broadcast to room members
        // Save to database
    }
}
```

#### 1.2 Frontend WebSocket Integration
**Tasks:**
- [ ] Implementer reconnection logic
- [ ] Handle connection states (connecting, connected, disconnected)
- [ ] Message queue for offline sending
- [ ] Optimize for mobile (battery-friendly)

**Files to Update:**
- `frontend/src/services/websocket.ts` ✅ (already exists, needs enhancement)
- `frontend/src/store/chatStore.ts` ✅ (add WebSocket listeners)

#### 1.3 Testing & Deployment
- [ ] Test multiple users i samme rom
- [ ] Test connection drops og reconnection
- [ ] Load testing (100+ concurrent users)
- [ ] Deploy WebSocket server til dedicated port (8080)

**Deployment:**
```bash
# Via Supervisor (on cPanel)
[program:snakkaz-websocket]
command=/usr/bin/php /home/snakqsqe/server/websocket/start.php
autostart=true
autorestart=true
```

---

## 🎨 SPRINT 2: UI/UX Forbedringer (Uke 2)

### Mål: Telegram-kvalitet på design og interaksjon

#### 2.1 Demo Data & Initial Setup
**Tasks:**
- [ ] SQL script for demo rooms
- [ ] Auto-create "General" rom ved registrering
- [ ] Seed messages for testing
- [ ] Default avatars (identicons eller placeholders)

**Files:**
```sql
-- database/seed-demo-data.sql
INSERT INTO rooms (room_name, room_type, description) VALUES
('General', 'group', 'Welcome to SnakkaZ! Discuss anything here.'),
('Random', 'group', 'Random thoughts and memes'),
('Tech Talk', 'group', 'Discuss technology and coding');

INSERT INTO messages (room_id, user_id, content) VALUES
(1, 1, 'Welcome to SnakkaZ! 👋'),
(1, 1, 'This is a modern chat platform built with React and PHP');
```

#### 2.2 Emoji Picker
**Tools:**
- emoji-picker-react (open-source)
- emoji-mart (alternative)

**Installation:**
```bash
npm install emoji-picker-react
```

**Implementation:**
```tsx
// components/Chat/EmojiPicker.tsx
import EmojiPicker from 'emoji-picker-react';

export const EmojiPickerButton = ({ onEmojiSelect }) => {
  const [showPicker, setShowPicker] = useState(false);
  
  return (
    <div className="emoji-picker-container">
      <button onClick={() => setShowPicker(!showPicker)}>😊</button>
      {showPicker && <EmojiPicker onEmojiClick={onEmojiSelect} />}
    </div>
  );
};
```

#### 2.3 File Upload & Preview
**Backend:**
- [ ] Upload endpoint `/api/upload.php`
- [ ] Image compression (GD or Imagick)
- [ ] File type validation
- [ ] Virus scanning (ClamAV integration)
- [ ] Storage in `/uploads/` directory

**Frontend:**
- [ ] Drag & drop zone
- [ ] Image preview before send
- [ ] Progress bar
- [ ] Multiple file upload

**Libraries:**
```bash
npm install react-dropzone
npm install react-image-crop (for cropping avatars)
```

#### 2.4 Typing Indicators
**Backend:**
- WebSocket event: `user:typing` og `user:stopped_typing`
- Throttle til max 1 event per 2 sekunder

**Frontend:**
```tsx
// Display "John is typing..." in chat footer
const TypingIndicator = ({ typingUsers }) => {
  if (typingUsers.length === 0) return null;
  
  return (
    <div className="typing-indicator">
      <span>{typingUsers.join(', ')} is typing</span>
      <span className="dots">...</span>
    </div>
  );
};
```

#### 2.5 Online Status
**Implementation:**
- Heartbeat ping every 30 sekunder via WebSocket
- Update `users.status` og `last_seen`
- Show green/gray dot på avatarer

---

## 🔧 SPRINT 3: Advanced Features (Uke 3)

### Mål: Konkurransedyktige features

#### 3.1 Message Reactions
**UI:**
- Click on message → show reaction picker
- Display reactions under message
- Animate when added

**Database:**
```sql
CREATE TABLE message_reactions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  message_id INT NOT NULL,
  user_id INT NOT NULL,
  emoji VARCHAR(10) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (message_id) REFERENCES messages(message_id),
  FOREIGN KEY (user_id) REFERENCES users(user_id),
  UNIQUE KEY (message_id, user_id, emoji)
);
```

#### 3.2 Search Functionality
**Backend:**
```php
// api/chat/search.php
GET /api/chat/search.php?q=hello&room_id=1
```

**Implementation:**
- Full-text search i messages
- Search i users
- Search i rooms
- Highlight results

**Frontend:**
```tsx
// components/Chat/SearchBar.tsx
const SearchBar = () => {
  const [query, setQuery] = useState('');
  const [results, setResults] = useState([]);
  
  // Debounced search
  useEffect(() => {
    const timer = setTimeout(() => {
      if (query) searchMessages(query);
    }, 300);
    return () => clearTimeout(timer);
  }, [query]);
};
```

#### 3.3 User Profiles & Settings
**Pages:**
- `/profile/:userId` - View user profile
- `/settings` - Edit your profile

**Features:**
- [ ] Avatar upload
- [ ] Bio/status message
- [ ] Privacy settings
- [ ] Notification preferences
- [ ] Dark mode toggle

#### 3.4 Notifications
**Push Notifications:**
- Service Worker for PWA
- Web Push API
- Desktop notifications

**Implementation:**
```bash
npm install web-push
```

```tsx
// Register service worker
if ('serviceWorker' in navigator) {
  navigator.serviceWorker.register('/sw.js')
    .then(registration => {
      // Subscribe to push
    });
}
```

---

## 🔐 SPRINT 4: Sikkerhet & Performance (Uke 4)

### Mål: Production-hardening

#### 4.1 Security Enhancements
**Tasks:**
- [ ] Rate limiting (per user, per IP)
- [ ] Input sanitization (all endpoints)
- [ ] SQL injection testing
- [ ] XSS prevention audit
- [ ] CSRF token implementation
- [ ] Content Security Policy (CSP)
- [ ] HTTP security headers

**Implementation:**
```php
// utils/RateLimiter.php
class RateLimiter {
    public function check($userId, $action, $maxAttempts = 10, $window = 60) {
        // Redis-based rate limiting
    }
}
```

#### 4.2 Performance Optimization
**Backend:**
- [ ] Database query optimization
- [ ] Add indexes på frequently queried columns
- [ ] Implement caching (Redis/Memcached)
- [ ] API response compression
- [ ] Lazy loading for old messages

**Frontend:**
- [ ] Code splitting
- [ ] Lazy load komponenter
- [ ] Virtualized lists for lange message lists
- [ ] Image lazy loading
- [ ] Service worker caching

**Libraries:**
```bash
npm install react-window (virtualized lists)
npm install react-lazy-load-image-component
```

#### 4.3 Database Optimization
**Tasks:**
```sql
-- Add indexes
CREATE INDEX idx_messages_room_created ON messages(room_id, created_at DESC);
CREATE INDEX idx_messages_user ON messages(user_id);
CREATE INDEX idx_room_members_user ON room_members(user_id);
CREATE INDEX idx_sessions_token ON sessions(token);

-- Partition messages table by date (optional, for scale)
-- Archive old messages to separate table
```

#### 4.4 Monitoring & Logging
**Tools:**
- Sentry for error tracking
- Google Analytics eller Plausible
- Custom logging for API calls
- Database slow query log

---

## 🎁 SPRINT 5: Polish & Launch (Uke 5)

### Mål: Public launch ready

#### 5.1 Admin Panel
**Features:**
- [ ] User management (ban, delete)
- [ ] Room management
- [ ] Message moderation
- [ ] Analytics dashboard
- [ ] System logs viewer

**Route:** `/admin` (protected)

#### 5.2 Landing Page
**Create:**
- [ ] Marketing landing page
- [ ] Features showcase
- [ ] Screenshots/demos
- [ ] Call-to-action
- [ ] FAQ section

**Route:** Separate from app, or `/landing`

#### 5.3 Documentation
**Create:**
- [ ] User guide
- [ ] API documentation
- [ ] Developer docs (for contributors)
- [ ] Privacy policy
- [ ] Terms of service

#### 5.4 Testing
**Types:**
- [ ] Unit tests (Jest)
- [ ] Integration tests
- [ ] E2E tests (Playwright)
- [ ] Load testing (k6 eller Artillery)
- [ ] Security audit

#### 5.5 Deployment Automation
**CI/CD:**
```yaml
# .github/workflows/deploy.yml
name: Deploy to Production

on:
  push:
    branches: [main]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Build frontend
        run: cd frontend && npm install && npm run build
      - name: Deploy via FTP
        uses: SamKirkland/FTP-Deploy-Action@4.0.0
        with:
          server: ftp.snakkaz.com
          username: ${{ secrets.FTP_USERNAME }}
          password: ${{ secrets.FTP_PASSWORD }}
```

---

## 🌟 BONUS FEATURES (Optional)

### Voice/Video Calls
**Tools:**
- WebRTC
- SimpleWebRTC eller PeerJS
- TURN/STUN servers

### Bots & Integrations
**Ideas:**
- Welcome bot
- Weather bot
- News bot
- GitHub integration
- Giphy integration

### Gamification
- User levels/badges
- Achievements
- Leaderboards
- Daily streaks

### AI Features (via MCP)
**Use MCP Server:**
- AI message suggestions
- Auto-translate messages
- Sentiment analysis
- Smart replies
- Content moderation (auto-flag spam)

**MCP Tools to Use:**
```typescript
// Via MCP server at mcp.snakkaz.com
{
  "analyzeMessage": "Check if message is spam/toxic",
  "translateMessage": "Auto-translate to user's language",
  "suggestReply": "AI-powered smart replies",
  "summarizeThread": "Summarize long conversations"
}
```

---

## 📦 OPEN SOURCE LIBRARIES TO USE

### Frontend
```json
{
  "emoji-picker-react": "^4.5.0",
  "react-dropzone": "^14.2.0",
  "react-window": "^1.8.10",
  "react-intersection-observer": "^9.5.0",
  "date-fns": "^2.30.0", // ✅ Already installed
  "socket.io-client": "^4.8.1", // ✅ Already installed
  "zustand": "^5.0.8", // ✅ Already installed
  "react-hot-toast": "^2.4.0", // For notifications
  "react-markdown": "^9.0.0", // For message formatting
  "highlight.js": "^11.9.0" // Code syntax highlighting
}
```

### Backend (Composer)
```json
{
  "cboden/ratchet": "^0.4", // WebSocket
  "predis/predis": "^2.0", // Redis client
  "phpmailer/phpmailer": "^6.8", // Email notifications
  "league/flysystem": "^3.0", // File management
  "intervention/image": "^2.7" // Image processing
}
```

---

## 📊 SUCCESS METRICS

### Technical KPIs
- [ ] Message delivery < 100ms
- [ ] WebSocket reconnection < 2s
- [ ] API response time < 200ms
- [ ] 99.9% uptime
- [ ] Support 1000+ concurrent users

### User KPIs
- [ ] 100+ registered users (month 1)
- [ ] 1000+ messages sent (month 1)
- [ ] 50+ daily active users
- [ ] < 5% bounce rate
- [ ] > 5 min average session time

---

## 🎯 NESTE STEG - START NÅ!

### Aller Først (i dag):
1. **Lag demo-rom i database**
   ```sql
   INSERT INTO rooms ...
   ```

2. **Implementer basic WebSocket**
   ```bash
   composer require cboden/ratchet
   ```

3. **Test at meldinger går gjennom**
   - Send melding fra frontend
   - Se at den kommer opp real-time

### Denne uken:
- [ ] Sprint 1: Real-time chat fungerer
- [ ] Demo data i database
- [ ] WebSocket server kjører på port 8080

### Neste uke:
- [ ] Sprint 2: Emoji picker, typing indicators
- [ ] File upload
- [ ] Online status

---

## 🛠️ DEVELOPMENT WORKFLOW

### Daily Routine:
```bash
# 1. Pull latest
git pull origin main

# 2. Start dev environment
cd frontend && npm run dev

# 3. Work on feature
# ... code ...

# 4. Test locally
npm run build
python3 /workspaces/SnakkaZ/deploy-ftp.py

# 5. Commit & push
git add .
git commit -m "feat: add emoji picker"
git push origin main
```

### Testing Checklist:
- [ ] Test på Chrome
- [ ] Test på Firefox
- [ ] Test på Safari
- [ ] Test på mobile (Chrome mobile)
- [ ] Test med slow 3G
- [ ] Test med multiple users

---

## 📞 SUPPORT & RESOURCES

### Server Info:
- **cPanel:** https://snakkaz.com:2083
- **FTP:** ftp.snakkaz.com
- **Database:** MariaDB 11.4.8
- **PHP:** 8.1.33 (FPM)
- **Home:** /home/snakqsqe/public_html

### Tools:
- **MCP Server:** https://mcp.snakkaz.com (klar for integrasjon)
- **GitHub:** github.com/Snakkaz/SnakkaZ
- **Documentation:** /workspaces/SnakkaZ/docs/

---

## 🎉 VI ER LIVE! HVA NÅ?

**Alt er på plass for å bygge verdens beste chat-app! 🚀**

**Neste move:**
Velg sprint og start! Hva vil du fokusere på først?

1. **Real-time chat** (WebSocket) - Most important!
2. **UI polish** (Emoji, typing indicators)
3. **File upload** (Images, documents)
4. **Advanced features** (Search, reactions)

**Si fra hvilken retning du vil, så bygger vi det! 💪**

---

*Laget med ❤️ av GitHub Copilot*  
*19. November 2025*
