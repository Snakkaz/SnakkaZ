import { useEffect } from 'react';
import { MoreVertical, Phone, Video } from 'lucide-react';
import { useChatStore } from '../../store/chatStore';
import { useAuthStore } from '../../store/authStore';
import { MessageList } from './MessageList';
import { MessageInput } from './MessageInput';
import { TypingIndicator } from './TypingIndicator';
import { Avatar } from '../Common/Avatar';
import { websocketService } from '../../services/websocket';
import './ChatWindow.css';

interface ChatWindowProps {
  roomId: number | null;
}

export const ChatWindow = ({ roomId }: ChatWindowProps) => {
  const { user } = useAuthStore();
  const { rooms, messages, isTyping, typingUsers, sendMessage, markAsRead, fetchMessages } = useChatStore();
  
  const room = roomId ? rooms.find(r => r.room_id === roomId) : null;
  const roomMessages = roomId ? (messages[roomId] || []) : [];
  const typingUserIds = roomId ? (isTyping[roomId] || []) : [];
  
  // Filter out current user from typing list and get usernames
  const typingUsernames = typingUserIds
    .filter(userId => userId !== user?.user_id)
    .map(userId => typingUsers[userId] || `User ${userId}`);

  useEffect(() => {
    if (roomId) {
      fetchMessages(roomId);
      markAsRead(roomId);
    }
  }, [roomId, fetchMessages, markAsRead]);

  const handleSendMessage = async (content: string, fileUrl?: string) => {
    if (!user || !roomId) return;
    
    try {
      await sendMessage({
        room_id: roomId,
        content,
        message_type: fileUrl ? 'file' : 'text',
        file_url: fileUrl,
      });
    } catch (error) {
      console.error('Failed to send message:', error);
    }
  };

  const handleTyping = () => {
    if (roomId) websocketService.startTyping(roomId);
  };

  const handleStopTyping = () => {
    if (roomId) websocketService.stopTyping(roomId);
  };

  if (!roomId || !room) {
    return (
      <div className="chat-window-empty">
        <h2>Welcome to SnakkaZ</h2>
        <p>Select a room to start chatting</p>
      </div>
    );
  }

  return (
    <div className="chat-window">
      <div className="chat-window-header">
        <div className="chat-header-info">
          <Avatar
            src={room.avatar_url}
            alt={room.room_name}
            size="md"
          />
          <div className="chat-header-text">
            <h2>{room.room_name}</h2>
            <div className="chat-header-meta">
              {room.description && (
                <p className="chat-description">{room.description}</p>
              )}
              <span className="room-members-count">
                {room.members?.length || 0} members
              </span>
            </div>
          </div>
        </div>

        <div className="chat-header-actions">
          <button className="chat-action-btn" title="Voice call">
            <Phone size={20} />
          </button>
          <button className="chat-action-btn" title="Video call">
            <Video size={20} />
          </button>
          <button className="chat-action-btn" title="More options">
            <MoreVertical size={20} />
          </button>
        </div>
      </div>

      <MessageList messages={roomMessages} />
      
      <TypingIndicator usernames={typingUsernames} />

      <MessageInput
        onSend={handleSendMessage}
        onTyping={handleTyping}
        onStopTyping={handleStopTyping}
        placeholder={`Message ${room.room_name}...`}
      />
    </div>
  );
};
