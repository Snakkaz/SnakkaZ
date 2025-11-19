import type { User } from './auth.types';

export interface Message {
  message_id: number;
  room_id: number;
  sender_id: number | string; // Backend may return string
  content: string;
  message_type: 'text' | 'image' | 'file' | 'system';
  file_url?: string;
  is_edited: boolean;
  created_at: string;
  updated_at: string;
  sender?: User;
}

export interface Room {
  room_id: number;
  room_name: string;
  room_type: 'direct' | 'group' | 'channel';
  description?: string;
  avatar_url?: string;
  created_by: number;
  created_at: string;
  updated_at: string;
  last_message?: Message;
  unread_count?: number;
  members?: RoomMember[];
}

export interface RoomMember {
  member_id: number;
  room_id: number;
  user_id: number;
  role: 'admin' | 'moderator' | 'member';
  joined_at: string;
  user?: User;
}

export interface SendMessageRequest {
  room_id: number;
  content: string;
  message_type?: 'text' | 'image' | 'file';
  file_url?: string;
}

export interface ChatState {
  rooms: Room[];
  messages: Record<number, Message[]>;
  activeRoomId: number | null;
  isTyping: Record<number, number[]>;
  isLoading: boolean;
  error: string | null;
}
