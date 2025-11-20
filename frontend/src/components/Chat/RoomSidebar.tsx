import type { Room } from '../../types/chat.types';
import { Avatar } from '../Common/Avatar';
import { Lock, Key, Globe, Plus, Settings } from 'lucide-react';
import { format } from 'date-fns';
import './RoomSidebar.css';

interface RoomSidebarProps {
  rooms: Room[];
  activeRoomId: number | null;
  onRoomSelect: (roomId: number) => void;
  onCreateRoom?: () => void;
  onOpenSettings?: () => void;
}

export const RoomSidebar = ({ rooms, activeRoomId, onRoomSelect, onCreateRoom, onOpenSettings }: RoomSidebarProps) => {
  const formatLastMessageTime = (timestamp?: string) => {
    if (!timestamp) return '';
    
    try {
      const date = new Date(timestamp);
      const today = new Date();
      
      if (date.toDateString() === today.toDateString()) {
        return format(date, 'HH:mm');
      } else {
        return format(date, 'MMM dd');
      }
    } catch {
      return '';
    }
  };

  const truncateMessage = (text: string, maxLength = 50) => {
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength) + '...';
  };

  const getPrivacyIcon = (privacyLevel?: string) => {
    switch (privacyLevel) {
      case 'password':
        return <Lock size={14} className="privacy-icon" />;
      case 'private':
        return <Key size={14} className="privacy-icon" />;
      case 'public':
      default:
        return <Globe size={14} className="privacy-icon" />;
    }
  };

  return (
    <div className="room-sidebar">
      <div className="room-sidebar-header">
        <h2>Chats</h2>
        <div className="header-actions">
          {onOpenSettings && (
            <button className="settings-btn" onClick={onOpenSettings} title="Settings">
              <Settings size={20} />
            </button>
          )}
          {onCreateRoom && (
            <button className="create-room-btn" onClick={onCreateRoom} title="Create new room">
              <Plus size={20} />
            </button>
          )}
        </div>
      </div>

      <div className="room-list">
        {rooms.length === 0 ? (
          <div className="room-list-empty">
            <p>No conversations yet</p>
          </div>
        ) : (
          rooms.map((room) => (
            <div
              key={room.room_id}
              className={`room-item ${room.room_id === activeRoomId ? 'room-item-active' : ''}`}
              onClick={() => onRoomSelect(room.room_id)}
            >
              <Avatar
                src={room.avatar_url}
                alt={room.room_name}
                size="md"
              />

              <div className="room-info">
                <div className="room-header">
                  <div className="room-name-container">
                    <h3 className="room-name">{room.room_name}</h3>
                    {room.privacy_level && getPrivacyIcon(room.privacy_level)}
                  </div>
                  {room.last_message && (
                    <span className="room-time">
                      {formatLastMessageTime(room.last_message.created_at)}
                    </span>
                  )}
                </div>

                <div className="room-footer">
                  <p className="room-last-message">
                    {room.last_message
                      ? truncateMessage(room.last_message.content)
                      : 'No messages yet'}
                  </p>
                  
                  {room.unread_count && room.unread_count > 0 ? (
                    <span className="room-unread-badge">{room.unread_count}</span>
                  ) : null}
                </div>
              </div>
            </div>
          ))
        )}
      </div>
    </div>
  );
};
