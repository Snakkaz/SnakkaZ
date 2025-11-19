import { create } from 'zustand';
import type { ChatState, Message, SendMessageRequest } from '../types/chat.types';
import { chatService } from '../services/chat';
import { websocketService } from '../services/websocket';

interface ChatStore extends ChatState {
  // Actions
  fetchRooms: () => Promise<void>;
  fetchMessages: (roomId: number) => Promise<void>;
  sendMessage: (messageData: SendMessageRequest) => Promise<void>;
  setActiveRoom: (roomId: number | null) => void;
  addMessage: (message: Message) => void;
  addTypingUser: (roomId: number, userId: number) => void;
  removeTypingUser: (roomId: number, userId: number) => void;
  markAsRead: (roomId: number) => void;
  initWebSocket: () => void;
  cleanupWebSocket: () => void;
}

export const useChatStore = create<ChatStore>((set, get) => ({
  // Initial state
  rooms: [],
  messages: {},
  activeRoomId: null,
  isTyping: {},
  isLoading: false,
  error: null,

  // Fetch all rooms
  fetchRooms: async () => {
    set({ isLoading: true, error: null });
    
    try {
      const rooms = await chatService.getRooms();
      set({ rooms, isLoading: false });
    } catch (error) {
      const errorMessage = error instanceof Error ? error.message : 'Failed to fetch rooms';
      set({
        error: errorMessage,
        isLoading: false,
      });
    }
  },

  // Fetch messages for a room
  fetchMessages: async (roomId: number) => {
    set({ isLoading: true, error: null });
    
    try {
      const response = await chatService.getMessages(roomId);
      const messages = get().messages;
      
      set({
        messages: {
          ...messages,
          [roomId]: response.data,
        },
        isLoading: false,
      });
      
      // Join room via WebSocket
      websocketService.joinRoom(roomId);
    } catch (error) {
      const errorMessage = error instanceof Error ? error.message : 'Failed to fetch messages';
      set({
        error: errorMessage,
        isLoading: false,
      });
    }
  },

  // Send a message
  sendMessage: async (messageData: SendMessageRequest) => {
    try {
      const message = await chatService.sendMessage(messageData);
      
      // Add message to local state
      const messages = get().messages;
      const roomMessages = messages[messageData.room_id] || [];
      
      set({
        messages: {
          ...messages,
          [messageData.room_id]: [...roomMessages, message],
        },
      });
      
      // Also send via WebSocket for realtime
      websocketService.sendMessage(message);
    } catch (error) {
      const errorMessage = error instanceof Error ? error.message : 'Failed to send message';
      set({ error: errorMessage });
      throw error;
    }
  },

  // Set active room
  setActiveRoom: (roomId: number | null) => {
    const currentRoomId = get().activeRoomId;
    
    // Leave current room
    if (currentRoomId !== null) {
      websocketService.leaveRoom(currentRoomId);
    }
    
    set({ activeRoomId: roomId });
    
    // Join new room and fetch messages
    if (roomId !== null) {
      get().fetchMessages(roomId);
    }
  },

  // Add message (from WebSocket)
  addMessage: (message: Message) => {
    const messages = get().messages;
    const roomMessages = messages[message.room_id] || [];
    
    // Check if message already exists
    const exists = roomMessages.some(m => m.message_id === message.message_id);
    if (exists) return;
    
    set({
      messages: {
        ...messages,
        [message.room_id]: [...roomMessages, message],
      },
    });
    
    // Update room's last message
    const rooms = get().rooms;
    const updatedRooms = rooms.map(room => {
      if (room.room_id === message.room_id) {
        return {
          ...room,
          last_message: message,
          unread_count: room.room_id === get().activeRoomId 
            ? room.unread_count 
            : (room.unread_count || 0) + 1,
        };
      }
      return room;
    });
    
    set({ rooms: updatedRooms });
  },

  // Add typing user
  addTypingUser: (roomId: number, userId: number) => {
    const isTyping = get().isTyping;
    const typingUsers = isTyping[roomId] || [];
    
    if (!typingUsers.includes(userId)) {
      set({
        isTyping: {
          ...isTyping,
          [roomId]: [...typingUsers, userId],
        },
      });
    }
  },

  // Remove typing user
  removeTypingUser: (roomId: number, userId: number) => {
    const isTyping = get().isTyping;
    const typingUsers = isTyping[roomId] || [];
    
    set({
      isTyping: {
        ...isTyping,
        [roomId]: typingUsers.filter(id => id !== userId),
      },
    });
  },

  // Mark messages as read
  markAsRead: async (roomId: number) => {
    try {
      await chatService.markAsRead(roomId);
      
      // Update room's unread count
      const rooms = get().rooms;
      const updatedRooms = rooms.map(room => {
        if (room.room_id === roomId) {
          return { ...room, unread_count: 0 };
        }
        return room;
      });
      
      set({ rooms: updatedRooms });
    } catch (error) {
      console.error('Failed to mark as read:', error);
    }
  },

  // Initialize WebSocket listeners
  initWebSocket: () => {
    websocketService.onMessageReceived((message: Message) => {
      get().addMessage(message);
    });
    
    websocketService.onUserTyping(({ room_id, user_id }) => {
      get().addTypingUser(room_id, user_id);
    });
    
    websocketService.onUserStoppedTyping(({ room_id, user_id }) => {
      get().removeTypingUser(room_id, user_id);
    });
  },

  // Cleanup WebSocket listeners
  cleanupWebSocket: () => {
    websocketService.disconnect();
  },
}));
