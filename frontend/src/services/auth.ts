import { apiClient } from './api';
import type { AuthResponse, LoginRequest, RegisterRequest, User } from '../types/auth.types';

export const authService = {
  /**
   * Login user
   */
  async login(credentials: LoginRequest): Promise<AuthResponse> {
    const response = await apiClient.post<AuthResponse['data']>('/auth/login.php', credentials);
    
    if (response.success && response.data) {
      // Normalize user data - ensure user_id is a number
      const userData = response.data.user;
      const normalizedUser = {
        ...userData,
        user_id: Number(userData.user_id || userData.id),
        id: Number(userData.user_id || userData.id),
      };
      
      // Store token and user data
      localStorage.setItem('auth_token', response.data.token);
      localStorage.setItem('user', JSON.stringify(normalizedUser));
      
      console.log('✅ Login stored:', { token: response.data.token.substring(0, 10) + '...', user: normalizedUser.username });
      
      return {
        success: response.success,
        message: response.message,
        data: {
          ...response.data,
          user: normalizedUser,
        },
      };
    }
    
    return {
      success: response.success,
      message: response.message,
      data: response.data,
    };
  },

  /**
   * Register new user
   */
  async register(userData: RegisterRequest): Promise<AuthResponse> {
    const response = await apiClient.post<AuthResponse['data']>('/auth/register.php', userData);
    
    if (response.success && response.data) {
      // Normalize user data - backend returns 'id', we need 'user_id'
      const normalizedUser = {
        ...response.data.user,
        user_id: Number(response.data.user.id || response.data.user.user_id),
      };
      
      // Store token and user data
      localStorage.setItem('auth_token', response.data.token);
      localStorage.setItem('user', JSON.stringify(normalizedUser));
      
      return {
        success: response.success,
        message: response.message,
        data: {
          ...response.data,
          user: normalizedUser,
        },
      };
    }
    
    return {
      success: response.success,
      message: response.message,
      data: response.data,
    };
  },

  /**
   * Logout user
   */
  async logout(): Promise<void> {
    try {
      await apiClient.post('/auth/logout.php');
    } finally {
      // Clear local storage regardless of API response
      localStorage.removeItem('auth_token');
      localStorage.removeItem('user');
    }
  },

  /**
   * Get current user from localStorage
   */
  getCurrentUser(): User | null {
    const userStr = localStorage.getItem('user');
    if (!userStr) return null;
    
    try {
      return JSON.parse(userStr);
    } catch {
      return null;
    }
  },

  /**
   * Get auth token
   */
  getToken(): string | null {
    return localStorage.getItem('auth_token');
  },

  /**
   * Check if user is authenticated
   */
  isAuthenticated(): boolean {
    return !!this.getToken();
  },

  /**
   * Validate token with backend
   * Returns user data if valid, null if invalid/expired
   */
  async validateToken(): Promise<User | null> {
    const token = this.getToken();
    if (!token) return null;

    try {
      // Try to fetch user's rooms - if token is valid, this will succeed
      // This doubles as both token validation and initial data fetch
      const response = await apiClient.get<any>('/chat/rooms.php');
      
      if (response.success) {
        // Token is valid, return cached user
        return this.getCurrentUser();
      }
      
      // Token invalid
      return null;
    } catch (error) {
      // Token expired or invalid - clear storage
      console.warn('⚠️ Token validation failed:', error);
      localStorage.removeItem('auth_token');
      localStorage.removeItem('user');
      return null;
    }
  },
};
