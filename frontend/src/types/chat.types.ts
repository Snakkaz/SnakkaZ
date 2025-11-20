import type { User } from './auth.types';

export interface ReactionUser {
  user_id: number | string;
  username: string;
  display_name: string;
}

export interface Reaction {
  emoji: string;
  count: number;
  users: ReactionUser[];
  has_reacted: boolean;
}

export interface Message {
  message_id: number;
  room_id: number;
  sender_id: number | string; // Backend may return string
  user_id?: number | string; // Alias for sender_id
  content: string;
  message_type: 'text' | 'image' | 'file' | 'system';
  file_url?: string;
  is_edited: boolean;
  created_at: string;
  updated_at: string;
  username?: string;
  display_name?: string;
  avatar_url?: string;
  sender?: User;
  reactions?: Reaction[];
}

export interface Room {
  room_id: number;
  room_name: string;
  room_type: 'direct' | 'group' | 'channel';
  privacy_level?: 'public' | 'private' | 'password';
  description?: string;
  avatar_url?: string;
  created_by: number;
  created_at: string;
  updated_at: string;
  last_message?: Message;
  unread_count?: number;
  members?: RoomMember[];
  is_encrypted?: boolean;
  invite_only?: boolean;
  max_members?: number;
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

export interface OnlineUser {
  user_id: number;
  username: string;
  status: 'online' | 'offline' | 'away';
}

export interface ChatState {
  rooms: Room[];
  messages: Record<number, Message[]>;
  activeRoomId: number | null;
  isTyping: Record<number, number[]>;
  typingUsers: Record<number, string>; // userId -> username
  onlineUsers: Record<number, OnlineUser[]>; // roomId -> online users
  isLoading: boolean;
  error: string | null;
}
