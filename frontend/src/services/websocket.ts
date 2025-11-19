import { io, Socket } from 'socket.io-client';
import type { Message } from '../types/chat.types';

const WEBSOCKET_URL = import.meta.env.VITE_WS_URL || 'wss://snakkaz.com:8080';

export const SocketEvent = {
  // Connection
  CONNECT: 'connect',
  DISCONNECT: 'disconnect',
  
  // Messages
  MESSAGE_SENT: 'message:sent',
  MESSAGE_RECEIVED: 'message:received',
  MESSAGE_READ: 'message:read',
  
  // Typing
  USER_TYPING: 'user:typing',
  USER_STOPPED_TYPING: 'user:stopped_typing',
  
  // Room
  USER_JOINED: 'room:user_joined',
  USER_LEFT: 'room:user_left',
  
  // Status
  USER_ONLINE: 'user:online',
  USER_OFFLINE: 'user:offline',
} as const;

export type SocketEventType = typeof SocketEvent[keyof typeof SocketEvent];

class WebSocketService {
  private socket: Socket | null = null;
  private reconnectAttempts = 0;
  private readonly maxReconnectAttempts = 5;

  connect(token: string) {
    if (this.socket?.connected) {
      console.log('WebSocket already connected');
      return;
    }

    this.socket = io(WEBSOCKET_URL, {
      auth: { token },
      transports: ['websocket', 'polling'],
      reconnection: true,
      reconnectionDelay: 1000,
      reconnectionDelayMax: 5000,
      reconnectionAttempts: this.maxReconnectAttempts,
    });

    this.setupEventHandlers();
  }

  private setupEventHandlers() {
    if (!this.socket) return;

    this.socket.on(SocketEvent.CONNECT, () => {
      console.log('WebSocket connected');
      this.reconnectAttempts = 0;
    });

    this.socket.on(SocketEvent.DISCONNECT, (reason) => {
      console.log('WebSocket disconnected:', reason);
      if (reason === 'io server disconnect') {
        // Server disconnected, attempt manual reconnection
        this.socket?.connect();
      }
    });

    this.socket.on('connect_error', (error) => {
      console.error('WebSocket connection error:', error);
      this.reconnectAttempts++;
    });
  }

  disconnect() {
    if (this.socket) {
      this.socket.disconnect();
      this.socket = null;
    }
  }

  // Join a room
  joinRoom(roomId: number) {
    this.socket?.emit('join_room', { room_id: roomId });
  }

  // Leave a room
  leaveRoom(roomId: number) {
    this.socket?.emit('leave_room', { room_id: roomId });
  }

  // Send message (via WebSocket)
  sendMessage(message: Partial<Message>) {
    this.socket?.emit(SocketEvent.MESSAGE_SENT, message);
  }

  // Typing indicators
  startTyping(roomId: number) {
    this.socket?.emit(SocketEvent.USER_TYPING, { room_id: roomId });
  }

  stopTyping(roomId: number) {
    this.socket?.emit(SocketEvent.USER_STOPPED_TYPING, { room_id: roomId });
  }

  // Event listeners
  onMessageReceived(callback: (message: Message) => void) {
    this.socket?.on(SocketEvent.MESSAGE_RECEIVED, callback);
  }

  onUserTyping(callback: (data: { room_id: number; user_id: number }) => void) {
    this.socket?.on(SocketEvent.USER_TYPING, callback);
  }

  onUserStoppedTyping(callback: (data: { room_id: number; user_id: number }) => void) {
    this.socket?.on(SocketEvent.USER_STOPPED_TYPING, callback);
  }

  onUserJoined(callback: (data: { room_id: number; user_id: number }) => void) {
    this.socket?.on(SocketEvent.USER_JOINED, callback);
  }

  onUserLeft(callback: (data: { room_id: number; user_id: number }) => void) {
    this.socket?.on(SocketEvent.USER_LEFT, callback);
  }

  // Remove event listeners
  off(event: string, callback?: (...args: unknown[]) => void) {
    if (callback) {
      this.socket?.off(event, callback);
    } else {
      this.socket?.off(event);
    }
  }

  // Check connection status
  isConnected(): boolean {
    return this.socket?.connected ?? false;
  }
}

export const websocketService = new WebSocketService();
