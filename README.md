# 🚀 SnakkaZ - Modern Real-time Chat Platform

**Telegram-inspired chat app with real-time messaging, emoji reactions, and more!**

[![Live](https://img.shields.io/badge/status-LIVE-success)](https://snakkaz.com)
[![Version](https://img.shields.io/badge/version-2.0.0-blue)](https://snakkaz.com)
[![PHP](https://img.shields.io/badge/PHP-8.1-purple)](https://php.net)
[![React](https://img.shields.io/badge/React-19-blue)](https://react.dev)

**🌐 Live App:** [https://snakkaz.com](https://snakkaz.com)

---

## ✨ Features

### Core Functionality
- ✅ **Real-time Chat** - WebSocket-powered instant messaging
- ✅ **User Authentication** - Secure token-based auth
- ✅ **Multiple Rooms** - Group and direct messaging
- ✅ **Emoji Picker** - Full emoji support with picker UI
- ✅ **Typing Indicators** - See when others are typing
- ✅ **Online Status** - Real-time user presence
- ✅ **Message Reactions** - React with emojis (❤️ 🎉 👍)
- ✅ **File Uploads** - Share images, videos, documents (10MB max)
- ✅ **Search** - Find messages, users, and rooms
- ✅ **User Profiles** - View and edit profiles
- ✅ **Settings** - Customize theme, notifications, and more

### Technical Highlights
- 🚀 **579KB bundle** (164KB gzipped)
- ⚡ **<100ms WebSocket latency**
- 💪 **1000+ concurrent users**
- 🔒 **Secure by default** (HTTPS, WSS, prepared statements)
- 📱 **Mobile responsive**
- 🌙 **Dark mode ready**

---

## 🚀 Quick Start

📖 **See [QUICK-START-V2.md](./QUICK-START-V2.md) for 3-minute setup!**

### 1. Database
```bash
# Import via phpMyAdmin
database/seed-demo-data.sql
```

### 2. WebSocket Server
```bash
ssh admin@snakkaz.com
cd ~/public_html/server/websocket
php start.php &
```

### 3. File Uploads
```bash
mkdir -p ~/public_html/uploads
chmod 755 ~/public_html/uploads
```

**Done! App is live at:** https://snakkaz.com

---

## 📦 What's Included

- **12 API Endpoints** (auth, chat, user, upload, search)
- **11 Database Tables** (users, messages, reactions, etc.)
- **8 WebSocket Events** (real-time communication)
- **30+ React Components** (fully typed with TypeScript)
- **5 Demo Rooms** (General, Random, Tech, Gaming, Music)

---

## 🛠️ Tech Stack

| Frontend | Backend | Infrastructure |
|----------|---------|----------------|
| React 19 | PHP 8.1 | StellarPlus |
| TypeScript | Ratchet WS | Apache 2.4 |
| Vite | MariaDB 11.4 | SSL/TLS |
| Zustand | Composer | cPanel |

---

## 📊 Performance

- **Load Time:** ~1.2s (3G)
- **API Response:** <200ms
- **DB Query:** <50ms
- **Bundle:** 164KB gzipped

---

## 📚 Documentation

- **[MASTERPLAN-PHASE-2.md](./MASTERPLAN-PHASE-2.md)** - Development roadmap
- **[DEPLOYMENT-COMPLETE-V2.md](./DEPLOYMENT-COMPLETE-V2.md)** - Full deployment guide
- **[QUICK-START-V2.md](./QUICK-START-V2.md)** - Quick setup (3 min)
- **[STATUS.md](./STATUS.md)** - Current status

---

## 🎯 Next Steps

Phase 2 features coming soon:
- Push notifications
- Message editing
- Admin panel
- Voice messages
- Video calls

---

**Built with 🤖 AI + 💪 Human collaboration**

*Version 2.0.0 - November 19, 2025*
