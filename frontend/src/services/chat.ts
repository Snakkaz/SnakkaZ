import { apiClient } from './api';
import type { Message, Room, SendMessageRequest, RoomMember } from '../types/chat.types';
import type { PaginatedResponse } from '../types/api.types';

export const chatService = {
  /**
   * Get all rooms for current user
   */
  async getRooms(): Promise<Room[]> {
    const response = await apiClient.get<any[]>('/chat/rooms.php');
    // API returns {success: true, data: [rooms]} with last_message as string
    const rooms = Array.isArray(response.data) ? response.data : [];
    
    // Transform API response to match Room type
    return rooms.map(room => ({
      room_id: Number(room.room_id),
      room_name: room.room_name,
      room_type: room.room_type,
      description: room.description,
      avatar_url: room.avatar_url,
      created_by: Number(room.created_by || 1),
      created_at: room.created_at,
      updated_at: room.updated_at,
      // Transform last_message from string to Message object if it exists
      last_message: room.last_message ? {
        message_id: 0, // Not available from rooms endpoint
        room_id: Number(room.room_id),
        sender_id: 1, // System user
        content: room.last_message,
        message_type: 'text' as const,
        is_edited: false,
        created_at: room.last_message_time || room.created_at,
        updated_at: room.last_message_time || room.updated_at
      } : undefined,
      unread_count: Number(room.unread_count || 0)
    }));
  },

  /**
   * Create a new room
   */
  async createRoom(roomData: {
    name: string;
    type?: 'direct' | 'group' | 'channel';
    privacy_level?: 'public' | 'private' | 'password';
    password?: string;
    description?: string;
    invite_only?: boolean;
    is_encrypted?: boolean;
    max_members?: number;
  }): Promise<{ room: Room; invite_code?: string }> {
    const response = await apiClient.post<{ room: Room; invite_code?: string }>('/chat/create-room.php', roomData);
    return response.data!;
  },

  /**
   * Join a room
   */
  async joinRoom(roomId: number, password?: string, inviteCode?: string): Promise<{ room: Room }> {
    const response = await apiClient.post<{ room: Room }>('/chat/join-room.php', {
      room_id: roomId,
      password,
      invite_code: inviteCode,
    });
    return response.data!;
  },

  /**
   * Get messages for a specific room
   */
  async getMessages(roomId: number, page = 1, limit = 50): Promise<PaginatedResponse<Message>> {
    const response = await apiClient.get<Message[]>('/chat/messages.php', {
      room_id: roomId,
      page,
      limit,
    });
    // API returns {success: true, data: [messages]}
    const messages = Array.isArray(response.data) ? response.data : [];
    return {
      success: true,
      data: messages,
      pagination: {
        page,
        limit,
        total: messages.length,
        total_pages: 1
      }
    };
  },

  /**
   * Send a message to a room
   */
  async sendMessage(messageData: SendMessageRequest): Promise<Message> {
    const response = await apiClient.post<Message>('/chat/send.php', messageData);
    return response.data!;
  },

  /**
   * Get room members
   */
  async getRoomMembers(roomId: number): Promise<RoomMember[]> {
    const response = await apiClient.get<RoomMember[]>('/chat/rooms.php', {
      room_id: roomId,
      action: 'members',
    });
    return response.data || [];
  },

  /**
   * Add member to room
   */
  async addMember(roomId: number, userId: number): Promise<void> {
    await apiClient.post('/chat/rooms.php', {
      action: 'add_member',
      room_id: roomId,
      user_id: userId,
    });
  },

  /**
   * Remove member from room
   */
  async removeMember(roomId: number, userId: number): Promise<void> {
    await apiClient.post('/chat/rooms.php', {
      action: 'remove_member',
      room_id: roomId,
      user_id: userId,
    });
  },

  /**
   * Mark messages as read
   */
  async markAsRead(roomId: number): Promise<void> {
    await apiClient.post('/chat/messages.php', {
      action: 'mark_read',
      room_id: roomId,
    });
  },

  /**
   * Toggle emoji reaction on a message
   */
  async toggleReaction(messageId: number, emoji: string): Promise<void> {
    await apiClient.post('/chat/reactions.php', {
      message_id: messageId,
      emoji,
    });
  },

  /**
   * Get reactions for a message
   */
  async getReactions(messageId: number) {
    const response = await apiClient.get('/chat/reactions.php', {
      message_id: messageId,
    });
    return response.data || [];
  },

  /**
   * Search messages, users, or rooms
   */
  async searchMessages(query: string, roomId: number | null = null, type: 'all' | 'messages' | 'users' | 'rooms' = 'messages') {
    const params: Record<string, any> = {
      q: query,
      type,
    };
    
    if (roomId) {
      params.room_id = roomId;
    }
    
    const response = await apiClient.get('/chat/search.php', params);
    return response.data;
  },
};
