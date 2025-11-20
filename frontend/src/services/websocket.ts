import { apiClient } from './api';

// Use long-polling for real-time updates (better for shared hosting)
const POLL_TIMEOUT = 25; // seconds

export const SocketEvent = {
  // Connection
  CONNECT: 'connection',
  DISCONNECT: 'disconnect',
  AUTHENTICATED: 'authenticated',
  
  // Messages
  MESSAGE: 'message',
  
  // Typing
  TYPING: 'typing',
  
  // Room
  JOIN_ROOM: 'join_room',
  LEAVE_ROOM: 'leave_room',
  ROOM_JOINED: 'room_joined',
  USER_JOINED: 'user_joined',
  USER_LEFT: 'user_left',
  
  // Status
  USER_STATUS: 'user_status',
  
  // Reactions
  REACTION: 'reaction',
  
  // Read receipts
  READ_RECEIPT: 'read_receipt',
  
  // Ping/Pong
  PING: 'ping',
  PONG: 'pong',
} as const;

export type SocketEventType = typeof SocketEvent[keyof typeof SocketEvent];

class PollingService {
  private isPolling = false;
  private currentRoomId: number | null = null;
  private lastMessageId = 0;
  private eventHandlers: Map<string, Set<Function>> = new Map();
  private pollingAbortController: AbortController | null = null;
  private typingTimeout: number | null = null;

  connect(token: string) {
    console.log('✅ Polling service connected (token authenticated)');
    this.trigger('authenticated', { user: { token } });
  }

  disconnect() {
    this.stopPolling();
    console.log('❌ Polling service disconnected');
  }

  joinRoom(roomId: number) {
    if (this.currentRoomId === roomId) {
      return;
    }

    this.currentRoomId = roomId;
    this.lastMessageId = 0;
    this.startPolling();
    
    this.trigger('room_joined', { roomId });
    console.log(`📥 Joined room ${roomId}`);
  }

  leaveRoom(roomId: number) {
    if (this.currentRoomId === roomId) {
      this.stopPolling();
      this.currentRoomId = null;
      this.lastMessageId = 0;
      console.log(`📤 Left room ${roomId}`);
    }
  }

  private async startPolling() {
    if (this.isPolling || !this.currentRoomId) {
      return;
    }

    this.isPolling = true;

    while (this.isPolling && this.currentRoomId) {
      try {
        this.pollingAbortController = new AbortController();
        
        const response = await fetch(
          `https://snakkaz.com/api/realtime/poll.php?room_id=${this.currentRoomId}&last_message_id=${this.lastMessageId}&timeout=${POLL_TIMEOUT}`,
          {
            method: 'GET',
            headers: {
              'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
            },
            signal: this.pollingAbortController.signal,
          }
        );

        if (!response.ok) {
          throw new Error(`Poll failed: ${response.status}`);
        }

        const data = await response.json();

        if (data.success && data.data) {
          const { messages, typing, online_users } = data.data;

          // Handle new messages
          if (messages && messages.length > 0) {
            messages.forEach((message: any) => {
              this.trigger('message', { message });
              this.lastMessageId = Math.max(this.lastMessageId, message.message_id);
            });
          }

          // Handle typing indicators
          if (typing && typing.length > 0) {
            console.log('👀 Typing users:', typing);
            typing.forEach((user: any) => {
              this.trigger('typing', {
                roomId: this.currentRoomId,
                userId: user.user_id,
                username: user.display_name || user.username,
                isTyping: true
              });
            });
          } else if (this.currentRoomId) {
            // No one typing - clear typing indicators
            this.trigger('typing_clear', {
              roomId: this.currentRoomId
            });
          }

          // Handle online users
          if (online_users) {
            this.trigger('online_users', {
              roomId: this.currentRoomId,
              users: online_users.map((user: any) => ({
                user_id: user.user_id,
                username: user.username,
                status: user.status || 'online'
              }))
            });
          }
        }

      } catch (error: unknown) {
        const err = error as Error;
        if (err.name !== 'AbortError') {
          console.error('❌ Polling error:', err);
          // Wait before retrying
          await new Promise(resolve => setTimeout(resolve, 5000));
        }
      }
    }
  }

  private stopPolling() {
    this.isPolling = false;
    if (this.pollingAbortController) {
      this.pollingAbortController.abort();
      this.pollingAbortController = null;
    }
  }

  // Send message (via HTTP API, not socket)
  async sendMessage(roomId: number, content: string) {
    try {
      await apiClient.post('/chat/send.php', {
        room_id: roomId,
        content
      });
    } catch (error) {
      console.error('❌ Failed to send message:', error);
      throw error;
    }
  }

  // Typing indicators
  async startTyping(roomId: number) {
    // Clear existing timeout
    if (this.typingTimeout) {
      clearTimeout(this.typingTimeout);
    }

    try {
      await apiClient.post('/realtime/typing.php', {
        room_id: roomId,
        is_typing: true
      });

      // Auto-stop typing after 3 seconds
      this.typingTimeout = window.setTimeout(() => {
        this.stopTyping(roomId);
      }, 3000);

    } catch (error) {
      console.error('❌ Failed to send typing indicator:', error);
    }
  }

  async stopTyping(roomId: number) {
    if (this.typingTimeout) {
      clearTimeout(this.typingTimeout);
      this.typingTimeout = null;
    }

    try {
      await apiClient.post('/realtime/typing.php', {
        room_id: roomId,
        is_typing: false
      });
    } catch (error) {
      console.error('❌ Failed to stop typing indicator:', error);
    }
  }

  // Reactions
  async sendReaction(messageId: number, emoji: string) {
    try {
      await apiClient.post('/chat/reactions.php', {
        message_id: messageId,
        emoji,
        action: 'add'
      });
    } catch (error) {
      console.error('❌ Failed to send reaction:', error);
    }
  }

  // Read receipts (fire and forget)
  markAsRead(messageId: number) {
    // Implement if needed
    console.log('📖 Mark as read:', messageId);
  }

  // Event handlers
  on(event: string, callback: (data: unknown) => void) {
    if (!this.eventHandlers.has(event)) {
      this.eventHandlers.set(event, new Set());
    }
    this.eventHandlers.get(event)!.add(callback);
  }

  off(event: string, callback?: Function) {
    const handlers = this.eventHandlers.get(event);
    if (handlers) {
      if (callback) {
        handlers.delete(callback);
      } else {
        handlers.clear();
      }
    }
  }

  private trigger(event: string, data: unknown) {
    const handlers = this.eventHandlers.get(event);
    if (handlers) {
      handlers.forEach(handler => {
        try {
          handler(data);
        } catch (error) {
          console.error(`Error in ${event} handler:`, error);
        }
      });
    }
  }

  isConnected(): boolean {
    return this.isPolling;
  }

  getState(): string {
    return this.isPolling ? 'connected' : 'disconnected';
  }
}

export const websocketService = new PollingService();
