export interface User {
  id?: number | string; // Backend returns string ID
  user_id?: number; // For compatibility
  username: string;
  email: string;
  display_name?: string;
  avatar_url?: string;
  status: 'online' | 'offline' | 'away';
  bio?: string;
  last_seen?: string;
  created_at: string;
}

export interface LoginRequest {
  email: string;
  password: string;
}

export interface RegisterRequest {
  username: string;
  email: string;
  password: string;
  display_name?: string;
}

export interface AuthResponse {
  success: boolean;
  message: string;
  data?: {
    user: User;
    token: string;
  };
}

export interface AuthState {
  user: User | null;
  token: string | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  error: string | null;
}
