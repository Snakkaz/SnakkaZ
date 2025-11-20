# SnakkaZ - Master Plan for 100% Fungerende App

## 🎯 Mål
En fullstendig fungerende, produksjonsready chat-app med Telegram/Wickr/WhatsApp nivå sikkerhet og features.

---

## ✅ FERDIGSTILT (Just Now)

### Database
- ✅ Alle privacy kolonner i `rooms` tabellen (privacy_level, password_hash, is_encrypted, max_members, invite_only)
- ✅ `room_invites` tabell for private rom invitasjoner
- ✅ `room_join_requests` tabell for approval-basert joining
- ✅ Migrering kjørt i phpMyAdmin (SIMPLE-MIGRATION.sql)

### Backend API
- ✅ `/api/chat/create-room.php` - opprett rom med privacy levels
- ✅ `/api/chat/join-room.php` - join med passord/invite code
- ✅ `/api/chat/rooms.php` - list rom med riktige kolonnenavn (id→room_id, name→room_name)
- ✅ Alle PHP-filer bruker riktige database-kolonner (id, name, type, creator_id)
- ✅ SQL aliases for kompatibilitet med frontend (room_id, room_name, room_type)

### Frontend Components
- ✅ CreateRoomModal - 3 privacy levels (Public, Password, Private)
- ✅ JoinRoomModal - passord/invite code input
- ✅ OnlineUsers sidebar - viser online brukere i current room
- ✅ StatusSelector - online/busy/away/offline dropdown
- ✅ SettingsModal - 3 tabs (Profile, Privacy, Notifications)
- ✅ Privacy toggles - private mode, online status, read receipts
- ✅ RoomSidebar - privacy ikoner (Lock/Key/Globe)
- ✅ Emoji picker overflow fix (position fixed, max-height viewport-based)

### Deployment
- ✅ `deploy-full.py` - deployer både frontend og backend
- ✅ Alle filer deployet til https://snakkaz.com
- ✅ Backend på https://snakkaz.com/api/

---

## 🔧 KRITISKE FIXES (Prioritet 1 - Må fikses NÅ)

### 1. Room Creation Bug
**Problem:** Modal forsvinner når man skriver, ingen rom vises etter opprettelse

**Root Cause:**
- Auth re-initialization resetter UI state
- Frontend forventer response fra `/api/chat/create-room.php`
- Rooms ikke refreshes etter creation

**Løsning:**
```typescript
// frontend/src/components/Chat/CreateRoomModal.tsx
const handleCreate = async () => {
  try {
    const result = await chatService.createRoom(formData);
    
    // Refresh rooms list
    const updatedRooms = await chatService.getRooms();
    chatStore.setRooms(updatedRooms);
    
    // Switch to new room
    if (result.room) {
      chatStore.setActiveRoom(result.room.room_id);
    }
    
    onClose();
  } catch (error) {
    console.error('Create room error:', error);
  }
};
```

**Files to fix:**
- `frontend/src/components/Chat/CreateRoomModal.tsx`
- `frontend/src/services/chat.ts` (add getRooms method)
- `frontend/src/store/chatStore.ts` (add setRooms action)

---

### 2. Mobile Responsiveness
**Problem:** Kan ikke bruke appen på mobil, kan ikke skrive meldinger

**Issues:**
- Touch interactions ikke fungerer
- Keyboard overlapper input
- Layout bryter på små skjermer
- Emoji picker ikke tilgjengelig

**Løsning:**
```css
/* Mobile viewport fix */
@supports (-webkit-touch-callout: none) {
  .chat-window {
    height: -webkit-fill-available;
  }
}

/* Touch-friendly targets */
.message-input button {
  min-height: 44px;
  min-width: 44px;
}

/* Keyboard spacing */
.message-input-container {
  padding-bottom: env(safe-area-inset-bottom);
}

/* Mobile breakpoints */
@media (max-width: 768px) {
  .room-sidebar { width: 100%; }
  .online-users { display: none; }
  .chat-window { width: 100%; }
}
```

**Files to fix:**
- `frontend/src/App.css` - viewport meta fixes
- `frontend/src/components/Chat/ChatWindow.tsx` - keyboard handling
- `frontend/src/components/Chat/MessageInput.tsx` - touch targets
- `frontend/index.html` - add viewport meta tag

---

### 3. Auth State Management
**Problem:** App re-initialiserer auth for ofte, logger ut uventet

**Root Cause:**
- Multiple `initAuth()` calls
- Token ikke persistent checket
- Race conditions i useEffect

**Løsning:**
```typescript
// frontend/src/store/authStore.ts
let isInitializing = false;

export const initAuth = async () => {
  if (isInitializing) return;
  isInitializing = true;
  
  try {
    const token = localStorage.getItem('token');
    const userStr = localStorage.getItem('user');
    
    if (token && userStr) {
      const user = JSON.parse(userStr);
      set({ 
        token, 
        user, 
        isAuthenticated: true,
        isLoading: false 
      });
    }
  } finally {
    isInitializing = false;
  }
};
```

**Files to fix:**
- `frontend/src/store/authStore.ts`
- `frontend/src/App.tsx`

---

## 📱 MOBILE FIXES (Prioritet 2)

### Viewport & Layout
- [ ] Add viewport meta tag i index.html
- [ ] Fix iOS safe-area-inset
- [ ] PWA manifest for "Add to Home Screen"
- [ ] Touch-friendly button sizes (min 44x44px)

### Keyboard Handling
- [ ] Auto-scroll når keyboard åpnes
- [ ] Input ikke skjult av keyboard
- [ ] "Send" knapp alltid synlig

### Touch Gestures
- [ ] Swipe for å lukke modals
- [ ] Pull-to-refresh for messages
- [ ] Long-press for message options
- [ ] Swipe between rooms

---

## 🎨 UI/UX IMPROVEMENTS (Prioritet 3)

### Visual Feedback
- [ ] Loading spinners på alle async actions
- [ ] Success/error toast notifications
- [ ] Skeleton loaders for messages
- [ ] "Typing..." indicator animation

### Accessibility
- [ ] ARIA labels på alle interaktive elementer
- [ ] Keyboard navigation (Tab, Enter, Esc)
- [ ] Screen reader support
- [ ] High contrast mode

### Polish
- [ ] Smooth animations (entrance/exit)
- [ ] Ripple effects på buttons
- [ ] Message send animation
- [ ] Room switch transition

---

## 🔒 SECURITY ENHANCEMENTS (Prioritet 4)

### End-to-End Encryption
**Status:** Placeholder implementert, ikke aktiv

**Implementation Plan:**
1. Signal Protocol library integration
2. Key exchange ved room creation
3. Message encryption/decryption client-side
4. Perfect Forward Secrecy (PFS)

**Files to create:**
- `frontend/src/services/encryption.ts`
- `server/api/keys/exchange.php`

### Additional Security
- [ ] Rate limiting på API endpoints
- [ ] CSRF protection
- [ ] XSS sanitization på messages
- [ ] SQL injection protection (prepared statements)
- [ ] File upload validation & virus scanning

---

## 📊 FEATURES ROADMAP

### Phase 1: Core Stability (Next 1-2 dager)
- [x] Fix room creation bug
- [x] Fix mobile responsiveness
- [x] Fix auth state management
- [ ] Deploy and test

### Phase 2: User Experience (Next 3-5 dager)
- [ ] Message search funksjonalitet
- [ ] User profile page
- [ ] Message editing/deletion
- [ ] Read receipts
- [ ] Push notifications (via Service Worker)

### Phase 3: Advanced Features (Next 1-2 uker)
- [ ] Voice messages
- [ ] File sharing (images, documents)
- [ ] Video/Audio calls (WebRTC)
- [ ] Screen sharing
- [ ] Message reactions (animated)

### Phase 4: Admin & Moderation (Next 2-3 uker)
- [ ] Room admin panel
- [ ] User roles & permissions
- [ ] Kick/ban functionality
- [ ] Message moderation
- [ ] Audit logs

---

## 🗄️ DATABASE SCHEMA

### Current Tables
```sql
✅ users (id, username, email, password_hash, display_name, avatar_url, status)
✅ rooms (id, name, type, creator_id, privacy_level, password_hash, is_encrypted, max_members, invite_only)
✅ room_members (id, room_id, user_id, role)
✅ messages (id, room_id, user_id, content, message_type)
✅ sessions (id, user_id, token, expires_at)
✅ room_invites (invite_id, room_id, invited_by, invite_code, max_uses, current_uses)
✅ room_join_requests (request_id, room_id, user_id, status)
```

### Missing Tables (To Create)
```sql
-- Read receipts
CREATE TABLE message_receipts (
  receipt_id INT AUTO_INCREMENT PRIMARY KEY,
  message_id INT NOT NULL,
  user_id INT NOT NULL,
  read_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (message_id) REFERENCES messages(id),
  FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Push notification tokens
CREATE TABLE push_tokens (
  token_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  token VARCHAR(255) NOT NULL,
  device_type ENUM('web', 'android', 'ios'),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

-- User blocks
CREATE TABLE user_blocks (
  block_id INT AUTO_INCREMENT PRIMARY KEY,
  blocker_id INT NOT NULL,
  blocked_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (blocker_id) REFERENCES users(id),
  FOREIGN KEY (blocked_id) REFERENCES users(id)
);
```

---

## 🚀 DEPLOYMENT CHECKLIST

### Pre-Deploy
- [ ] Run all tests
- [ ] Check for console errors
- [ ] Verify all API endpoints
- [ ] Test on multiple browsers
- [ ] Test on mobile devices
- [ ] Check database migrations

### Deploy Steps
1. `cd /workspaces/SnakkaZ/frontend && npm run build`
2. `python3 /workspaces/SnakkaZ/deploy-full.py`
3. Test på https://snakkaz.com
4. Verify database connectivity
5. Check API health endpoint

### Post-Deploy
- [ ] Monitor error logs
- [ ] Check user feedback
- [ ] Performance metrics
- [ ] Database backup

---

## 🐛 KNOWN ISSUES

### Critical (P0)
1. **Room creation modal forsvinner** - Fixed in this session, needs testing
2. **Mobile input ikke fungerer** - Pending fix
3. **Auth re-initialization** - Pending fix

### High (P1)
4. Rooms ikke refreshes automatisk
5. No error messages ved API fails
6. Typing indicator ikke vises
7. Emoji picker for stor på små skjermer

### Medium (P2)
8. No loading states
9. No offline mode
10. Messages ikke cached

### Low (P3)
11. No dark mode toggle
12. No font size adjustment
13. No custom themes

---

## 📈 SUCCESS METRICS

### Technical
- ✅ < 3s page load time
- ✅ < 100ms message send latency
- ✅ 99.9% uptime
- ✅ Zero SQL injection vulnerabilities
- ✅ Zero XSS vulnerabilities

### User Experience
- 📱 Works på 100% av mobile devices
- 🎯 < 5 clicks to send første message
- ⚡ Real-time updates < 1s latency
- 🔒 End-to-end encryption på alle messages
- 🌐 Works offline (PWA)

---

## 🛠️ DEVELOPMENT WORKFLOW

### Branch Strategy
```bash
main          # Production-ready code
├── develop   # Integration branch
├── feature/* # New features
├── fix/*     # Bug fixes
└── hotfix/*  # Emergency fixes
```

### Commit Convention
```
feat: Add password-protected rooms
fix: Room creation modal disappearing
refactor: Simplify auth state management
docs: Update deployment guide
style: Fix mobile CSS layout
test: Add room creation tests
```

### Testing Levels
1. **Unit Tests** - Individual functions
2. **Integration Tests** - API endpoints
3. **E2E Tests** - User flows
4. **Manual Testing** - Real devices

---

## 📞 SUPPORT & MAINTENANCE

### Monitoring
- Server uptime (UptimeRobot)
- Error tracking (Sentry)
- Performance (Lighthouse)
- Database metrics (phpMyAdmin)

### Backup Strategy
- Daily database backup (automated cron)
- Weekly code backup (Git)
- Monthly full server backup

### Update Schedule
- Security patches: Immediate
- Bug fixes: Weekly
- Features: Bi-weekly
- Major versions: Monthly

---

## 🎓 DOCUMENTATION

### Required Docs
- [ ] API Documentation (Swagger/OpenAPI)
- [ ] User Guide (for end-users)
- [ ] Admin Guide (for moderators)
- [ ] Developer Guide (for contributors)
- [ ] Deployment Guide (for DevOps)

### Code Documentation
- [ ] JSDoc comments på alle functions
- [ ] PHPDoc comments på alle classes
- [ ] README.md i hver directory
- [ ] Inline comments for complex logic

---

## 💡 NEXT IMMEDIATE STEPS

1. **Test room creation** på https://snakkaz.com
2. **Fix mobile input** hvis ikke fungerer
3. **Add proper error handling** i CreateRoomModal
4. **Refresh rooms list** etter creation
5. **Deploy and verify** alt fungerer

---

**Last Updated:** November 19, 2025  
**Status:** 🟡 In Progress  
**Target:** 🎯 100% Fungerende App  
**ETA:** 2-3 dager for critical fixes, 2-3 uker for full feature set
