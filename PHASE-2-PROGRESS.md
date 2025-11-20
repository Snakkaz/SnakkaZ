# 🚀 SnakkaZ Phase 2 - UX & Feature Implementation

**Status:** 🔄 IN PROGRESS  
**Start Date:** 19. November 2025  
**Theme:** Matrix Dark Edition er live! ✅

---

## 📋 PHASE 2 GOALS

### 1. UX Forbedringer (Smooth & Fast)
- ✅ **Custom React Hooks** - `useUX.ts` created
- [ ] **Room Switching Animations** - Fade in/out transitions
- [ ] **Message Stagger Animations** - Messages appear one by one
- [ ] **Auto-scroll to Bottom** - New messages scroll smoothly
- [ ] **Typing Indicators** - Show when users are typing
- [ ] **Online/Offline Status** - Real-time connection status
- [ ] **Copy to Clipboard** - Copy messages with one click

### 2. Emoji & Reactions
- ✅ **Emoji Picker Library** - emoji-picker-react installed
- ✅ **Backend API** - `/api/chat/reactions.php` ready
- [ ] **UI Integration** - Connect emoji picker to messages
- [ ] **Reaction Display** - Show emoji reactions under messages
- [ ] **Reaction Counter** - Count how many users reacted

### 3. File Upload & Sharing
- ✅ **Backend Upload** - `/api/upload.php` ready (10MB limit)
- ✅ **Thumbnail Generation** - Automatic for images
- [ ] **Upload UI Component** - Drag & drop + button
- [ ] **File Preview** - Show images inline
- [ ] **Download Links** - Secure file downloads
- [ ] **Progress Bar** - Upload progress indicator

### 4. Search & Discovery
- ✅ **Backend Search** - `/api/chat/search.php` ready
- [ ] **Search UI Component** - Search bar in header
- [ ] **Search Results** - Display messages/users/rooms
- [ ] **Highlight Matches** - Highlight search terms
- [ ] **Search History** - Recent searches

### 5. User Profiles & Settings
- ✅ **Backend APIs** - Profile & settings endpoints ready
- [ ] **Profile Modal** - Click avatar to view profile
- [ ] **Edit Profile** - Change username, bio, avatar
- [ ] **Settings Panel** - Preferences (theme, notifications)
- [ ] **Privacy Settings** - Block users, hide status

### 6. Real-time Features (WebSocket)
- ✅ **WebSocket Server** - `ChatServer.php` ready
- [ ] **Server Deployment** - Start WebSocket on server
- [ ] **Message Broadcasting** - Real-time message delivery
- [ ] **Typing Indicators** - Live typing status
- [ ] **Read Receipts** - Mark messages as read
- [ ] **User Presence** - Online/offline status

---

## 🎯 CURRENT FOCUS: UX Hooks

### ✅ Completed Hooks

**1. useRoomTransition**
```typescript
const { isTransitioning, startTransition } = useRoomTransition();

await startTransition(); // Smooth fade effect
// Switch room here
```

**2. useSmoothScroll**
```typescript
const { scrollToBottom } = useSmoothScroll(messageListRef);

scrollToBottom(); // Smooth scroll to latest message
```

**3. useMessageAnimation**
```typescript
const { visibleMessages, animateMessage } = useMessageAnimation();

messages.forEach((msg, idx) => animateMessage(msg.id, idx));
```

**4. useTypingIndicator**
```typescript
const { handleTyping, stopTyping } = useTypingIndicator(
  (isTyping) => sendTypingStatus(isTyping)
);

<input onChange={handleTyping} onBlur={stopTyping} />
```

**5. useAutoResize**
```typescript
useAutoResize(textareaRef, message);
// Textarea grows as you type (max 120px)
```

**6. useDebounce**
```typescript
const debouncedSearch = useDebounce(searchTerm, 300);
// Only search after 300ms of no typing
```

**7. useIntersectionObserver**
```typescript
useIntersectionObserver(loaderRef, loadMoreMessages);
// Infinite scroll - load more when reaching top
```

**8. useOnlineStatus**
```typescript
const isOnline = useOnlineStatus();
// true/false based on network connection
```

**9. useClipboard**
```typescript
const { copy, isCopied } = useClipboard();

copy('Message text here');
// isCopied = true for 2 seconds
```

---

## 🔨 NEXT IMPLEMENTATIONS

### 1. Integrate UX Hooks into Components

**MessageList.tsx:**
```typescript
// Add smooth scrolling
const messageListRef = useRef<HTMLDivElement>(null);
const { scrollToBottom } = useSmoothScroll(messageListRef);

useEffect(() => {
  scrollToBottom();
}, [messages]);
```

**ChatWindow.tsx:**
```typescript
// Add room switching animation
const { isTransitioning, startTransition } = useRoomTransition();

const handleRoomSwitch = async (roomId: number) => {
  await startTransition();
  setActiveRoom(roomId);
};
```

**MessageInput.tsx:**
```typescript
// Add typing indicator
const { handleTyping, stopTyping } = useTypingIndicator(
  (isTyping) => {
    if (websocket.connected) {
      websocket.send({ type: 'typing', isTyping });
    }
  }
);

// Add auto-resize
const textareaRef = useRef<HTMLTextAreaElement>(null);
useAutoResize(textareaRef, message);
```

### 2. Create Emoji Reaction Component

**File:** `frontend/src/components/Chat/MessageReactions.tsx`

```typescript
interface MessageReactionsProps {
  messageId: number;
  reactions: Reaction[];
  onReact: (emoji: string) => void;
}

export function MessageReactions({ messageId, reactions, onReact }: MessageReactionsProps) {
  const [showPicker, setShowPicker] = useState(false);
  
  // Group reactions by emoji
  const grouped = reactions.reduce((acc, r) => {
    acc[r.emoji] = (acc[r.emoji] || 0) + 1;
    return acc;
  }, {} as Record<string, number>);

  return (
    <div className="message-reactions">
      {Object.entries(grouped).map(([emoji, count]) => (
        <button
          key={emoji}
          className="reaction-pill"
          onClick={() => onReact(emoji)}
        >
          {emoji} {count}
        </button>
      ))}
      
      <button
        className="add-reaction-btn"
        onClick={() => setShowPicker(!showPicker)}
      >
        ➕
      </button>
      
      {showPicker && (
        <EmojiPicker
          onEmojiClick={(e) => {
            onReact(e.emoji);
            setShowPicker(false);
          }}
        />
      )}
    </div>
  );
}
```

**CSS:** `MessageReactions.css`
```css
.message-reactions {
  display: flex;
  gap: 0.25rem;
  margin-top: 0.5rem;
  flex-wrap: wrap;
}

.reaction-pill {
  padding: 0.25rem 0.5rem;
  background: var(--bg-tertiary);
  border: 1px solid var(--border);
  border-radius: var(--border-radius);
  font-size: 0.875rem;
  cursor: pointer;
  transition: var(--transition);
  font-family: 'Courier New', monospace;
}

.reaction-pill:hover {
  background: var(--hover);
  border-color: var(--primary-color);
  box-shadow: var(--glow);
}

.add-reaction-btn {
  padding: 0.25rem 0.5rem;
  background: transparent;
  border: 1px dashed var(--border);
  border-radius: var(--border-radius);
  cursor: pointer;
  transition: var(--transition);
  opacity: 0.5;
}

.add-reaction-btn:hover {
  opacity: 1;
  border-color: var(--primary-color);
  box-shadow: var(--glow);
}
```

### 3. Create File Upload Component

**File:** `frontend/src/components/Chat/FileUpload.tsx`

```typescript
interface FileUploadProps {
  onUpload: (file: File) => Promise<void>;
  disabled?: boolean;
}

export function FileUpload({ onUpload, disabled }: FileUploadProps) {
  const [isDragging, setIsDragging] = useState(false);
  const [uploading, setUploading] = useState(false);
  const [progress, setProgress] = useState(0);
  const fileInputRef = useRef<HTMLInputElement>(null);

  const handleDrop = async (e: React.DragEvent) => {
    e.preventDefault();
    setIsDragging(false);
    
    const files = Array.from(e.dataTransfer.files);
    if (files.length > 0) {
      await uploadFile(files[0]);
    }
  };

  const uploadFile = async (file: File) => {
    if (file.size > 10 * 1024 * 1024) {
      alert('File too large! Max 10MB');
      return;
    }

    setUploading(true);
    setProgress(0);

    try {
      const formData = new FormData();
      formData.append('file', file);

      const xhr = new XMLHttpRequest();
      
      xhr.upload.addEventListener('progress', (e) => {
        if (e.lengthComputable) {
          const percent = (e.loaded / e.total) * 100;
          setProgress(percent);
        }
      });

      xhr.addEventListener('load', () => {
        if (xhr.status === 200) {
          const response = JSON.parse(xhr.responseText);
          onUpload(response.data);
        }
        setUploading(false);
      });

      xhr.open('POST', '/api/upload.php');
      xhr.send(formData);
    } catch (error) {
      console.error('Upload failed:', error);
      setUploading(false);
    }
  };

  return (
    <div
      className={`file-upload ${isDragging ? 'dragging' : ''}`}
      onDragOver={(e) => { e.preventDefault(); setIsDragging(true); }}
      onDragLeave={() => setIsDragging(false)}
      onDrop={handleDrop}
    >
      <input
        ref={fileInputRef}
        type="file"
        onChange={(e) => {
          const file = e.target.files?.[0];
          if (file) uploadFile(file);
        }}
        style={{ display: 'none' }}
      />
      
      <button
        className="upload-btn"
        onClick={() => fileInputRef.current?.click()}
        disabled={disabled || uploading}
      >
        📎 Upload
      </button>

      {uploading && (
        <div className="upload-progress">
          <div
            className="progress-bar"
            style={{ width: `${progress}%` }}
          />
        </div>
      )}
    </div>
  );
}
```

### 4. Create Search Component

**File:** `frontend/src/components/Chat/Search.tsx`

```typescript
interface SearchProps {
  onSearch: (query: string) => void;
}

export function Search({ onSearch }: SearchProps) {
  const [query, setQuery] = useState('');
  const debouncedQuery = useDebounce(query, 300);

  useEffect(() => {
    if (debouncedQuery) {
      onSearch(debouncedQuery);
    }
  }, [debouncedQuery, onSearch]);

  return (
    <div className="search-container">
      <input
        type="text"
        className="search-input"
        placeholder="Search messages..."
        value={query}
        onChange={(e) => setQuery(e.target.value)}
      />
      
      {query && (
        <button
          className="clear-search"
          onClick={() => setQuery('')}
        >
          ✕
        </button>
      )}
    </div>
  );
}
```

---

## 📊 PROGRESS TRACKING

### Features Status
| Feature | Backend | Frontend | UI/UX | Status |
|---------|---------|----------|-------|--------|
| **Matrix Theme** | N/A | ✅ | ✅ | 🟢 LIVE |
| **UX Hooks** | N/A | ✅ | ⏳ | 🟡 READY |
| **Emoji Reactions** | ✅ | ⏳ | ⏳ | 🟡 50% |
| **File Upload** | ✅ | ⏳ | ⏳ | 🟡 50% |
| **Search** | ✅ | ⏳ | ⏳ | 🟡 40% |
| **Profiles** | ✅ | ❌ | ❌ | 🔴 30% |
| **WebSocket** | ✅ | ⏳ | ⏳ | 🟡 60% |
| **Typing Indicators** | ✅ | ⏳ | ⏳ | 🟡 50% |

**Legend:**
- 🟢 LIVE - Deployed and working
- 🟡 IN PROGRESS - Partially implemented
- 🔴 NOT STARTED - Backend ready, no frontend
- ✅ Done | ⏳ In Progress | ❌ Not Started

---

## 🎯 NEXT ACTIONS

### Immediate (Today)
1. ✅ Create UX hooks file
2. [ ] Integrate smooth scroll into MessageList
3. [ ] Add room transition to ChatWindow
4. [ ] Implement typing indicator in MessageInput

### Short-term (This Week)
1. [ ] Create MessageReactions component
2. [ ] Create FileUpload component
3. [ ] Create Search component
4. [ ] Deploy WebSocket server

### Medium-term (Next Week)
1. [ ] Profile modal implementation
2. [ ] Settings panel
3. [ ] Infinite scroll for messages
4. [ ] Push notifications

---

## 🔧 TECHNICAL NOTES

### WebSocket Connection
```typescript
// Connect to WebSocket server
const ws = new WebSocket('wss://snakkaz.com:8080');

ws.onmessage = (event) => {
  const data = JSON.parse(event.data);
  
  switch (data.type) {
    case 'message':
      // New message received
      addMessage(data.message);
      break;
      
    case 'typing':
      // User is typing
      setTypingUsers(data.users);
      break;
      
    case 'reaction':
      // Reaction added/removed
      updateReactions(data.messageId, data.reactions);
      break;
  }
};
```

### File Upload Flow
```
1. User selects file
2. Validate size (max 10MB)
3. Show progress bar
4. Upload to /api/upload.php
5. Backend generates thumbnail (if image)
6. Return file URL + thumbnail
7. Send message with attachment
```

### Search Implementation
```
1. User types in search box
2. Debounce 300ms
3. Call /api/chat/search.php
4. Display results grouped by type
5. Highlight matching text
6. Click result → jump to message/room
```

---

## 💡 UX PRINCIPLES

### 1. Performance
- Debounce search/typing events
- Lazy load messages (infinite scroll)
- Optimize animations (GPU-accelerated)
- Cache API responses

### 2. Feedback
- Loading states for all actions
- Success/error notifications
- Progress bars for uploads
- Typing indicators

### 3. Smoothness
- Fade transitions (300ms)
- Stagger animations
- Smooth scrolling
- Easing functions (cubic-bezier)

### 4. Accessibility
- Keyboard shortcuts
- ARIA labels
- Focus management
- Screen reader support

---

*Phase 2 Documentation by GitHub Copilot*  
*Updated: 19. November 2025*
