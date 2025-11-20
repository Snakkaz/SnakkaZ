import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { useEffect, useState } from 'react';
import { useAuthStore } from './store/authStore';
import { useChatStore } from './store/chatStore';
import { AuthLayout } from './components/Auth/AuthLayout';
import { LoginForm } from './components/Auth/LoginForm';
import { RegisterForm } from './components/Auth/RegisterForm';
import { ChatWindow } from './components/Chat/ChatWindow';
import { RoomSidebar } from './components/Chat/RoomSidebar';
import { OnlineUsers } from './components/Chat/OnlineUsers';
import { CreateRoomModal, type RoomCreateData } from './components/Chat/CreateRoomModal';
import { SettingsModal } from './components/User/SettingsModal';
import { chatService } from './services/chat';
import './App.css';

function ProtectedRoute({ children }: Readonly<{ children: React.ReactNode }>) {
  const { isAuthenticated, user } = useAuthStore();

  console.log('🛡️ ProtectedRoute check:', { isAuthenticated, user: user?.username });

  if (!isAuthenticated) {
    console.warn('🚫 Not authenticated - redirecting to login');
    return <Navigate to="/login" replace />;
  }

  return <>{children}</>;
}

function ChatLayout() {
  const { rooms, activeRoomId, onlineUsers, fetchRooms, setActiveRoom, fetchMessages, initWebSocket } = useChatStore();
  const [showCreateRoom, setShowCreateRoom] = useState(false);
  const [showSettings, setShowSettings] = useState(false);

  useEffect(() => {
    fetchRooms();
    initWebSocket(); // Initialize WebSocket listeners
  }, [fetchRooms, initWebSocket]);

  const handleRoomSelect = (roomId: number) => {
    setActiveRoom(roomId);
    fetchMessages(roomId);
  };

  const handleCreateRoom = async (roomData: RoomCreateData) => {
    try {
      await chatService.createRoom(roomData);
      await fetchRooms(); // Refresh room list
      setShowCreateRoom(false);
    } catch (error) {
      console.error('Failed to create room:', error);
      throw error;
    }
  };

  const currentRoomOnlineUsers = activeRoomId ? (onlineUsers[activeRoomId] || []) : [];

  return (
    <>
      <div className="chat-layout">
        <RoomSidebar
          rooms={rooms}
          activeRoomId={activeRoomId}
          onRoomSelect={handleRoomSelect}
          onCreateRoom={() => setShowCreateRoom(true)}
          onOpenSettings={() => setShowSettings(true)}
        />
        <ChatWindow roomId={activeRoomId} />
        <OnlineUsers users={currentRoomOnlineUsers} />
      </div>

      {showCreateRoom && (
        <CreateRoomModal
          onClose={() => setShowCreateRoom(false)}
          onCreateRoom={handleCreateRoom}
        />
      )}

      {showSettings && (
        <SettingsModal onClose={() => setShowSettings(false)} />
      )}
    </>
  );
}function App() {
  const { initAuth, isAuthenticated } = useAuthStore();
  const [isInitialized, setIsInitialized] = useState(false);

  // Initialize authentication from localStorage on app start - SYNCHRONOUSLY
  useEffect(() => {
    console.log('🚀 App mounting - initializing auth...');
    initAuth();
    setIsInitialized(true);
  }, []);

  // Don't render routes until auth is initialized
  if (!isInitialized) {
    console.log('⏳ Waiting for auth initialization...');
    return null; // or a loading spinner
  }

  console.log('✅ App initialized, isAuthenticated:', isAuthenticated);

  return (
    <BrowserRouter>
      <Routes>
        <Route path="/login" element={<AuthLayout><LoginForm /></AuthLayout>} />
        <Route path="/register" element={<AuthLayout><RegisterForm /></AuthLayout>} />
        <Route
          path="/chat"
          element={
            <ProtectedRoute>
              <ChatLayout />
            </ProtectedRoute>
          }
        />
        <Route path="/" element={<Navigate to="/chat" replace />} />
      </Routes>
    </BrowserRouter>
  );
}

export default App;
