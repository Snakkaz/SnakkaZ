import { apiClient } from './api';
import type { Message, Room, SendMessageRequest, RoomMember } from '../types/chat.types';
import type { PaginatedResponse } from '../types/api.types';

export const chatService = {
  /**
   * Get all rooms for current user
   */
  async getRooms(): Promise<Room[]> {
    const response = await apiClient.get<Room[]>('/chat/rooms.php');
    return response.data || [];
  },

  /**
   * Create a new room
   */
  async createRoom(roomData: {
    room_name: string;
    room_type: 'direct' | 'group' | 'channel';
    description?: string;
  }): Promise<Room> {
    const response = await apiClient.post<Room>('/chat/rooms.php', roomData);
    return response.data!;
  },

  /**
   * Get messages for a specific room
   */
  async getMessages(roomId: number, page = 1, limit = 50): Promise<PaginatedResponse<Message>> {
    const response = await apiClient.get<PaginatedResponse<Message>>('/chat/messages.php', {
      room_id: roomId,
      page,
      limit,
    });
    return response.data as PaginatedResponse<Message>;
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
};
