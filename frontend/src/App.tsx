import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { useEffect } from 'react';
import { useAuthStore } from './store/authStore';
import { useChatStore } from './store/chatStore';
import { AuthLayout } from './components/Auth/AuthLayout';
import { LoginForm } from './components/Auth/LoginForm';
import { RegisterForm } from './components/Auth/RegisterForm';
import { ChatWindow } from './components/Chat/ChatWindow';
import { RoomSidebar } from './components/Chat/RoomSidebar';
import './App.css';

function ProtectedRoute({ children }: Readonly<{ children: React.ReactNode }>) {
  const { token } = useAuthStore();

  if (!token) {
    return <Navigate to="/login" replace />;
  }

  return <>{children}</>;
}

function ChatLayout() {
  const { rooms, activeRoomId, fetchRooms, setActiveRoom, fetchMessages } = useChatStore();

  useEffect(() => {
    fetchRooms();
  }, [fetchRooms]);

  const handleRoomSelect = (roomId: number) => {
    setActiveRoom(roomId);
    fetchMessages(roomId);
  };

  return (
    <div className="chat-layout">
      <RoomSidebar 
        rooms={rooms}
        activeRoomId={activeRoomId}
        onRoomSelect={handleRoomSelect}
      />
      <ChatWindow roomId={activeRoomId} />
    </div>
  );
}

function App() {
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
