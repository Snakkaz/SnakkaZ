import { Users } from 'lucide-react';
import { Avatar } from '../Common/Avatar';
import './OnlineUsers.css';

export interface OnlineUser {
  user_id: number;
  username: string;
  status?: 'online' | 'offline' | 'away';
}

interface OnlineUsersProps {
  users: OnlineUser[];
  isCollapsed?: boolean;
}

export const OnlineUsers = ({ users, isCollapsed = false }: OnlineUsersProps) => {
  if (isCollapsed) {
    return (
      <div className="online-users-collapsed">
        <div className="online-count">
          <Users size={16} />
          <span>{users.length}</span>
        </div>
      </div>
    );
  }

  return (
    <div className="online-users">
      <div className="online-users-header">
        <h3>Online — {users.length}</h3>
      </div>

      <div className="online-users-list">
        {users.length === 0 ? (
          <div className="online-users-empty">
            <p>No one online</p>
          </div>
        ) : (
          users.map((user) => (
            <div key={user.user_id} className="online-user-item">
              <Avatar
                src={undefined}
                alt={user.username}
                size="sm"
                status="online"
                fallback={user.username}
              />
              <span className="online-user-name">{user.username}</span>
            </div>
          ))
        )}
      </div>
    </div>
  );
};
