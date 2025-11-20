import { create } from 'zustand';
import type { AuthState, User, LoginRequest, RegisterRequest } from '../types/auth.types';
import { authService } from '../services/auth';
import { websocketService } from '../services/websocket';

interface AuthStore extends AuthState {
  // Actions
  login: (credentials: LoginRequest) => Promise<void>;
  register: (userData: RegisterRequest) => Promise<void>;
  logout: () => Promise<void>;
  setUser: (user: User | null) => void;
  setError: (error: string | null) => void;
  clearError: () => void;
  initAuth: () => Promise<void>;
}

export const useAuthStore = create<AuthStore>((set) => ({
  // Initial state
  user: null,
  token: null,
  isAuthenticated: false,
  isLoading: false,
  error: null,

  // Initialize auth from localStorage
  initAuth: async () => {
    const token = authService.getToken();
    const user = authService.getCurrentUser();
    
    console.log('🔐 Init auth:', { token: !!token, user: !!user });
    
    if (token && user) {
      console.log('🔐 Token found, validating with backend...');
      
      // Validate token with backend
      const validUser = await authService.validateToken();
      
      if (validUser) {
        console.log('✅ Token valid, setting authenticated state');
        set({
          user: validUser,
          token,
          isAuthenticated: true,
        });
        
        // Connect WebSocket (non-blocking)
        try {
          websocketService.connect(token);
        } catch (error) {
          console.error('⚠️ WebSocket connect failed:', error);
        }
      } else {
        console.warn('❌ Token invalid/expired, clearing auth');
        set({
          user: null,
          token: null,
          isAuthenticated: false,
        });
      }
    } else {
      console.log('ℹ️ No saved auth found');
    }
  },

  // Login action
  login: async (credentials: LoginRequest) => {
    set({ isLoading: true, error: null });
    
    try {
      const response = await authService.login(credentials);
      
      if (response.success && response.data) {
        set({
          user: response.data.user,
          token: response.data.token,
          isAuthenticated: true,
          isLoading: false,
          error: null,
        });
        
        // Connect WebSocket
        websocketService.connect(response.data.token);
      } else {
        throw new Error(response.message || 'Login failed');
      }
    } catch (error) {
      const errorMessage = error instanceof Error ? error.message : 'Login failed';
      set({
        error: errorMessage,
        isLoading: false,
      });
      throw error;
    }
  },

  // Register action
  register: async (userData: RegisterRequest) => {
    set({ isLoading: true, error: null });
    
    try {
      const response = await authService.register(userData);
      
      if (response.success && response.data) {
        set({
          user: response.data.user,
          token: response.data.token,
          isAuthenticated: true,
          isLoading: false,
          error: null,
        });
        
        // Connect WebSocket
        websocketService.connect(response.data.token);
      } else {
        throw new Error(response.message || 'Registration failed');
      }
    } catch (error) {
      const errorMessage = error instanceof Error ? error.message : 'Registration failed';
      set({
        error: errorMessage,
        isLoading: false,
      });
      throw error;
    }
  },

  // Logout action
  logout: async () => {
    try {
      await authService.logout();
    } finally {
      // Disconnect WebSocket
      websocketService.disconnect();
      
      set({
        user: null,
        token: null,
        isAuthenticated: false,
        error: null,
      });
    }
  },

  // Set user
  setUser: (user: User | null) => {
    set({ user });
  },

  // Set error
  setError: (error: string | null) => {
    set({ error });
  },

  // Clear error
  clearError: () => {
    set({ error: null });
  },
}));
