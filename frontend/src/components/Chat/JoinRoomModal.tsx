import { useState } from 'react';
import { Lock, Key } from 'lucide-react';
import { Button } from '../Common/Button';
import { Input } from '../Common/Input';
import './JoinRoomModal.css';

interface JoinRoomModalProps {
  room: {
    room_id: number;
    room_name: string;
    privacy_level: 'public' | 'private' | 'password';
  };
  onClose: () => void;
  onJoinRoom: (roomId: number, password?: string, inviteCode?: string) => Promise<void>;
}

export const JoinRoomModal = ({ room, onClose, onJoinRoom }: JoinRoomModalProps) => {
  const [password, setPassword] = useState('');
  const [inviteCode, setInviteCode] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState('');

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');

    if (room.privacy_level === 'password' && !password) {
      setError('Password is required');
      return;
    }

    if (room.privacy_level === 'private' && !inviteCode) {
      setError('Invite code is required');
      return;
    }

    setIsLoading(true);

    try {
      await onJoinRoom(room.room_id, password || undefined, inviteCode || undefined);
      onClose();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to join room');
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="join-room-modal" onClick={(e) => e.stopPropagation()}>
        <div className="modal-header">
          <h2>Join Room</h2>
          <button className="modal-close" onClick={onClose}>×</button>
        </div>

        <form onSubmit={handleSubmit} className="modal-body">
          <div className="room-info">
            <h3>{room.room_name}</h3>
            <div className="privacy-badge">
              {room.privacy_level === 'password' && (
                <>
                  <Lock size={16} />
                  <span>Password Protected</span>
                </>
              )}
              {room.privacy_level === 'private' && (
                <>
                  <Key size={16} />
                  <span>Private - Invite Only</span>
                </>
              )}
            </div>
          </div>

          {error && <div className="error-message">{error}</div>}

          {room.privacy_level === 'password' && (
            <div className="form-group">
              <label htmlFor="room-password">Password</label>
              <Input
                id="room-password"
                type="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                placeholder="Enter room password..."
                required
                autoFocus
              />
            </div>
          )}

          {room.privacy_level === 'private' && (
            <div className="form-group">
              <label htmlFor="invite-code">Invite Code</label>
              <Input
                id="invite-code"
                type="text"
                value={inviteCode}
                onChange={(e) => setInviteCode(e.target.value)}
                placeholder="Enter invite code..."
                required
                autoFocus
              />
            </div>
          )}

          <div className="modal-actions">
            <Button type="button" variant="secondary" onClick={onClose}>
              Cancel
            </Button>
            <Button type="submit" variant="primary" disabled={isLoading}>
              {isLoading ? 'Joining...' : 'Join Room'}
            </Button>
          </div>
        </form>
      </div>
    </div>
  );
};
