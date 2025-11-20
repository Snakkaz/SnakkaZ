import { useState } from 'react';
import { Lock, Globe, Key, Shield } from 'lucide-react';
import { Button } from '../Common/Button';
import { Input } from '../Common/Input';
import './CreateRoomModal.css';

interface CreateRoomModalProps {
  onClose: () => void;
  onCreateRoom: (roomData: RoomCreateData) => Promise<void>;
}

export interface RoomCreateData {
  name: string;
  type: 'direct' | 'group' | 'channel';
  privacy_level: 'public' | 'private' | 'password';
  password?: string;
  description?: string;
  invite_only?: boolean;
  is_encrypted?: boolean;
  max_members?: number;
}

export const CreateRoomModal = ({ onClose, onCreateRoom }: CreateRoomModalProps) => {
  const [formData, setFormData] = useState<RoomCreateData>({
    name: '',
    type: 'group',
    privacy_level: 'public',
    description: '',
    max_members: 100,
  });
  const [password, setPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState('');

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');

    if (!formData.name.trim()) {
      setError('Room name is required');
      return;
    }

    if (formData.privacy_level === 'password') {
      if (!password) {
        setError('Password is required for password-protected rooms');
        return;
      }
      if (password !== confirmPassword) {
        setError('Passwords do not match');
        return;
      }
      if (password.length < 6) {
        setError('Password must be at least 6 characters');
        return;
      }
    }

    setIsLoading(true);

    try {
      await onCreateRoom({
        ...formData,
        password: formData.privacy_level === 'password' ? password : undefined,
      });
      onClose();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to create room');
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="create-room-modal" onClick={(e) => e.stopPropagation()}>
        <div className="modal-header">
          <h2>Create New Room</h2>
          <button className="modal-close" onClick={onClose}>×</button>
        </div>

        <form onSubmit={handleSubmit} className="modal-body">
          {error && <div className="error-message">{error}</div>}

          <div className="form-group">
            <label htmlFor="room-name">Room Name *</label>
            <Input
              id="room-name"
              type="text"
              value={formData.name}
              onChange={(e) => setFormData({ ...formData, name: e.target.value })}
              placeholder="Enter room name..."
              maxLength={50}
              required
            />
          </div>

          <div className="form-group">
            <label htmlFor="room-desc">Description</label>
            <textarea
              id="room-desc"
              value={formData.description}
              onChange={(e) => setFormData({ ...formData, description: e.target.value })}
              placeholder="What's this room about?"
              rows={3}
              maxLength={200}
            />
          </div>

          <div className="form-group">
            <label>Privacy Level</label>
            <div className="privacy-options">
              <button
                type="button"
                className={`privacy-option ${formData.privacy_level === 'public' ? 'active' : ''}`}
                onClick={() => setFormData({ ...formData, privacy_level: 'public' })}
              >
                <Globe size={20} />
                <span>Public</span>
                <small>Anyone can join</small>
              </button>

              <button
                type="button"
                className={`privacy-option ${formData.privacy_level === 'password' ? 'active' : ''}`}
                onClick={() => setFormData({ ...formData, privacy_level: 'password' })}
              >
                <Lock size={20} />
                <span>Password</span>
                <small>Requires password</small>
              </button>

              <button
                type="button"
                className={`privacy-option ${formData.privacy_level === 'private' ? 'active' : ''}`}
                onClick={() => setFormData({ ...formData, privacy_level: 'private' })}
              >
                <Key size={20} />
                <span>Private</span>
                <small>Invite only</small>
              </button>
            </div>
          </div>

          {formData.privacy_level === 'password' && (
            <>
              <div className="form-group">
                <label htmlFor="room-password">Password *</label>
                <Input
                  id="room-password"
                  type="password"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  placeholder="Enter password..."
                  minLength={6}
                  required
                />
              </div>

              <div className="form-group">
                <label htmlFor="confirm-password">Confirm Password *</label>
                <Input
                  id="confirm-password"
                  type="password"
                  value={confirmPassword}
                  onChange={(e) => setConfirmPassword(e.target.value)}
                  placeholder="Confirm password..."
                  minLength={6}
                  required
                />
              </div>
            </>
          )}

          <div className="form-group">
            <label className="checkbox-label">
              <input
                type="checkbox"
                checked={formData.is_encrypted || false}
                onChange={(e) => setFormData({ ...formData, is_encrypted: e.target.checked })}
              />
              <Shield size={16} />
              <span>Enable end-to-end encryption</span>
            </label>
            <small className="help-text">Messages will be encrypted on your device</small>
          </div>

          <div className="form-group">
            <label htmlFor="max-members">Maximum Members</label>
            <Input
              id="max-members"
              type="number"
              value={formData.max_members || 100}
              onChange={(e) => setFormData({ ...formData, max_members: parseInt(e.target.value) })}
              min={2}
              max={1000}
            />
          </div>

          <div className="modal-actions">
            <Button type="button" variant="secondary" onClick={onClose}>
              Cancel
            </Button>
            <Button type="submit" variant="primary" disabled={isLoading}>
              {isLoading ? 'Creating...' : 'Create Room'}
            </Button>
          </div>
        </form>
      </div>
    </div>
  );
};
