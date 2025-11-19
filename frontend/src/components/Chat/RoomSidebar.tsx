import type { Room } from '../../types/chat.types';
import { Avatar } from '../Common/Avatar';
import { format } from 'date-fns';
import './RoomSidebar.css';

interface RoomSidebarProps {
  rooms: Room[];
  activeRoomId: number | null;
  onRoomSelect: (roomId: number) => void;
}

export const RoomSidebar = ({ rooms, activeRoomId, onRoomSelect }: RoomSidebarProps) => {
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

  return (
    <div className="room-sidebar">
      <div className="room-sidebar-header">
        <h2>Chats</h2>
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
                  <h3 className="room-name">{room.room_name}</h3>
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
