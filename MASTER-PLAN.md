# 🚀 SnakkaZ Chat Platform - Master Plan

**Status:** Backend LIVE ✅ | Frontend: Planning  
**Updated:** 19. November 2025

---

## 📊 Nåværende Status

### ✅ Fullført (Backend)
- **API:** https://snakkaz.com/api/ (LIVE)
- **MCP Server:** https://mcp.snakkaz.com (LIVE)
- **Database:** snakqsqe_SnakkaZ (6 tabeller)
- **Sikkerhet:** HTTPS, bcrypt, prepared statements
- **Endpoints:** 8 fungerende REST endpoints

### 🎯 Teknisk Stack

**Backend (LIVE):**
- PHP 8.1.33 + LiteSpeed
- MariaDB 11.4.8
- Token-based auth
- RESTful API

**Frontend (Planlagt):**
- React 18+ med TypeScript
- Telegram-inspirert UI/UX
- WebSocket for realtime
- Progressive Web App (PWA)

**Infrastructure:**
- MCP Server (Model Context Protocol)
- CDN for assets
- Redis caching (optional)

---

## 🏗️ Arkitektur Oversikt

```
┌─────────────────────────────────────────────────────────┐
│                    SnakkaZ Platform                      │
└─────────────────────────────────────────────────────────┘

┌──────────────┐      ┌──────────────┐      ┌──────────────┐
│   Frontend   │◄────►│   Backend    │◄────►│   Database   │
│   (React)    │      │   (PHP API)  │      │   (MariaDB)  │
│              │      │              │      │              │
│ - Chat UI    │      │ - REST API   │      │ - Users      │
│ - Auth       │      │ - WebSocket  │      │ - Messages   │
│ - Realtime   │      │ - Auth       │      │ - Rooms      │
└──────────────┘      └──────────────┘      └──────────────┘
       │                     │                      │
       └─────────────────────┴──────────────────────┘
                             │
                    ┌────────▼────────┐
                    │   MCP Server    │
                    │  (Integration)  │
                    │                 │
                    │ - AI Tools      │
                    │ - Extensions    │
                    │ - Automation    │
                    └─────────────────┘
```

---

## 📋 Fase 1: Frontend Development (Nå)

### 1.1 React Setup
```bash
# Opprett React app
npm create vite@latest frontend -- --template react-ts
cd frontend
npm install

# Dependencies
npm install axios react-router-dom zustand
npm install @tanstack/react-query
npm install socket.io-client
npm install date-fns lucide-react
```

### 1.2 Komponent-struktur
```
frontend/
├── src/
│   ├── components/
│   │   ├── Auth/
│   │   │   ├── LoginForm.tsx
│   │   │   ├── RegisterForm.tsx
│   │   │   └── AuthLayout.tsx
│   │   ├── Chat/
│   │   │   ├── ChatWindow.tsx
│   │   │   ├── MessageList.tsx
│   │   │   ├── MessageInput.tsx
│   │   │   └── RoomSidebar.tsx
│   │   └── Common/
│   │       ├── Avatar.tsx
│   │       ├── Button.tsx
│   │       └── Input.tsx
│   ├── hooks/
│   │   ├── useAuth.ts
│   │   ├── useChat.ts
│   │   └── useWebSocket.ts
│   ├── services/
│   │   ├── api.ts
│   │   ├── auth.ts
│   │   └── websocket.ts
│   ├── store/
│   │   ├── authStore.ts
│   │   ├── chatStore.ts
│   │   └── uiStore.ts
│   └── types/
│       ├── auth.types.ts
│       ├── chat.types.ts
│       └── api.types.ts
```

### 1.3 Design System (Telegram-inspirert)
- **Colors:** Blå gradient (#2481cc), hvit, grå
- **Typography:** -apple-system, SF Pro, Segoe UI
- **Layout:** Sidebar + hovedvindu (responsive)
- **Animations:** Smooth transitions, message bubbles

---

## 📋 Fase 2: MCP Integration

### 2.1 MCP Server Setup
**URL:** https://mcp.snakkaz.com

**Funksjoner:**
- AI-assistert chat
- Kode-snippets deling
- Automatisk oversettelse
- Sentiment analysis
- Smart notifications

### 2.2 VS Code Extensions
```vscode-extensions
anthropic.claude-code,automatalabs.copilot-mcp,ms-python.vscode-pylance,bmewburn.vscode-intelephense-client,ms-mssql.mssql,sonarsource.sonarlint-vscode,postman.postman-for-vscode
```

**Installerte:**
- ✅ Claude Code for VS Code
- ✅ Copilot MCP
- ✅ Pylance (Python)
- ✅ PHP Intelephense
- ✅ SQL Server
- ✅ SonarQube
- ✅ Postman

### 2.3 MCP Capabilities
```typescript
// MCP Tools for SnakkaZ
interface MCPTools {
  // AI Chat Enhancement
  analyzeMessage: (text: string) => Promise<Sentiment>;
  suggestReply: (context: Message[]) => Promise<string>;
  translateMessage: (text: string, lang: string) => Promise<string>;
  
  // Code Sharing
  formatCode: (code: string, language: string) => Promise<string>;
  highlightSyntax: (code: string) => Promise<HTML>;
  
  // Automation
  scheduleMessage: (message: Message, time: Date) => Promise<void>;
  createReminder: (text: string, time: Date) => Promise<void>;
  
  // Analytics
  getChatStats: (roomId: number) => Promise<Stats>;
  getUserActivity: (userId: number) => Promise<Activity>;
}
```

---

## 📋 Fase 3: Realtime Features

### 3.1 WebSocket Implementation
```php
// backend/websocket/server.php
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new ChatServer()
        )
    ),
    8080
);

$server->run();
```

### 3.2 Events
```typescript
// Socket Events
enum SocketEvent {
  // Connection
  CONNECT = 'connect',
  DISCONNECT = 'disconnect',
  
  // Messages
  MESSAGE_SENT = 'message:sent',
  MESSAGE_RECEIVED = 'message:received',
  MESSAGE_READ = 'message:read',
  
  // Typing
  USER_TYPING = 'user:typing',
  USER_STOPPED_TYPING = 'user:stopped_typing',
  
  // Room
  USER_JOINED = 'room:user_joined',
  USER_LEFT = 'room:user_left',
  
  // Status
  USER_ONLINE = 'user:online',
  USER_OFFLINE = 'user:offline'
}
```

---

## 📋 Fase 4: Advanced Features

### 4.1 File Sharing
- **Bilder:** JPEG, PNG, GIF (max 10MB)
- **Dokumenter:** PDF, DOCX (max 20MB)
- **Video:** MP4, WebM (max 50MB)
- **Lyd:** MP3, WAV (max 10MB)

### 4.2 Voice/Video Chat
- **WebRTC:** Peer-to-peer video
- **TURN Server:** Fallback for firewalls
- **Screen Sharing:** Desktop/window sharing

### 4.3 Bot Integration
```typescript
// SnakkaZ Bot API
interface BotAPI {
  sendMessage(roomId: number, text: string): Promise<void>;
  onCommand(command: string, handler: Function): void;
  onMessage(handler: Function): void;
  
  // MCP Integration
  useAI(prompt: string): Promise<string>;
  analyzeImage(imageUrl: string): Promise<Analysis>;
}
```

### 4.4 Admin Dashboard
- User management
- Room moderation
- Analytics/stats
- System logs
- Backup/restore

---

## 📋 Fase 5: Deployment & Optimization

### 5.1 Frontend Build
```bash
# Production build
npm run build

# Deploy til snakkaz.com
scp -r dist/* admin@snakkaz.com:/home/snakqsqe/public_html/
```

### 5.2 Performance
- **Code Splitting:** Lazy loading
- **Image Optimization:** WebP format
- **Caching:** Service worker PWA
- **CDN:** CloudFlare eller Namecheap CDN

### 5.3 Monitoring
```typescript
// Sentry for error tracking
import * as Sentry from "@sentry/react";

Sentry.init({
  dsn: "YOUR_SENTRY_DSN",
  integrations: [new Sentry.BrowserTracing()],
  tracesSampleRate: 1.0,
});
```

### 5.4 Security
- **Rate Limiting:** 100 req/minute per user
- **CSRF Protection:** Token validation
- **XSS Prevention:** Sanitize all inputs
- **SQL Injection:** Prepared statements ✅
- **Password Policy:** Min 8 chars, bcrypt ✅

---

## 📋 Fase 6: Testing & QA

### 6.1 Unit Tests
```typescript
// Jest + React Testing Library
describe('ChatWindow', () => {
  it('sends message on enter', async () => {
    render(<ChatWindow roomId={1} />);
    const input = screen.getByRole('textbox');
    
    await userEvent.type(input, 'Hello{enter}');
    
    expect(mockSendMessage).toHaveBeenCalledWith('Hello');
  });
});
```

### 6.2 E2E Tests
```typescript
// Playwright
test('user can send and receive messages', async ({ page }) => {
  await page.goto('https://snakkaz.com');
  await page.fill('[name="email"]', 'test@snakkaz.com');
  await page.fill('[name="password"]', 'Test123456');
  await page.click('button[type="submit"]');
  
  await page.fill('.message-input', 'Test message');
  await page.press('.message-input', 'Enter');
  
  await expect(page.locator('.message-bubble').last())
    .toHaveText('Test message');
});
```

### 6.3 Performance Testing
- **Lighthouse:** >90 score
- **WebPageTest:** <2s load time
- **Load Testing:** Artillery eller k6

---

## 📋 Milestones & Timeline

### Sprint 1 (Uke 1-2): Frontend Foundation
- ✅ Backend deployed
- [ ] React app setup
- [ ] Auth pages (login/register)
- [ ] Chat UI components
- [ ] API integration

### Sprint 2 (Uke 3-4): Core Features
- [ ] Message sending/receiving
- [ ] Room management
- [ ] User profiles
- [ ] File upload

### Sprint 3 (Uke 5-6): Realtime & MCP
- [ ] WebSocket implementation
- [ ] MCP integration
- [ ] AI features
- [ ] Notifications

### Sprint 4 (Uke 7-8): Polish & Deploy
- [ ] UI/UX refinement
- [ ] Testing
- [ ] Performance optimization
- [ ] Production deployment

---

## 🛠️ Development Tools

### Required Extensions
```vscode-extensions
anthropic.claude-code,automatalabs.copilot-mcp,ms-python.vscode-pylance,bmewburn.vscode-intelephense-client,ms-mssql.mssql,sonarsource.sonarlint-vscode,postman.postman-for-vscode,ritwickdey.liveserver,ms-vscode.live-server
```

### Recommended Tools
- **VS Code:** Primary IDE
- **Postman:** API testing (extension installed ✅)
- **phpMyAdmin:** Database management
- **Git:** Version control
- **GitHub:** Code hosting

---

## 📚 Resources

### Documentation
- **API Docs:** `/workspaces/SnakkaZ/docs/API.md`
- **Database Schema:** `/workspaces/SnakkaZ/database/schema.sql`
- **Deployment Guide:** `/workspaces/SnakkaZ/DEPLOYMENT-SUCCESS.md`

### External Resources
- **React Docs:** https://react.dev
- **TypeScript:** https://www.typescriptlang.org
- **Telegram Design:** https://telegram.org/blog/new-design
- **MCP Spec:** https://modelcontextprotocol.io

---

## 🎯 Success Metrics

### Technical KPIs
- **API Uptime:** >99.9%
- **Response Time:** <200ms (95th percentile)
- **Error Rate:** <0.1%
- **Test Coverage:** >80%

### User KPIs
- **Active Users:** Target 1000+ MAU
- **Message Volume:** 10,000+ daily
- **Retention:** >60% (30-day)
- **NPS Score:** >50

---

## 🔄 Next Immediate Actions

1. **Start Frontend Development** ✅
2. **Connect to MCP Server** ✅
3. **Build Chat UI Components**
4. **Implement WebSocket**
5. **Deploy Frontend**

---

**Ready to start Sprint 1? Let's build SnakkaZ Chat! 🚀**
