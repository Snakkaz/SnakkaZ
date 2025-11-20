import { useState } from 'react';
import { Settings, User, Bell, Shield, Moon, Volume2, X } from 'lucide-react';
import { Button } from '../Common/Button';
import { useAuthStore } from '../../store/authStore';
import { StatusSelector, type UserStatus } from '../Common/StatusSelector';
import './SettingsModal.css';

interface SettingsModalProps {
  onClose: () => void;
}

export const SettingsModal = ({ onClose }: SettingsModalProps) => {
  const { user } = useAuthStore();
  const [activeTab, setActiveTab] = useState<'profile' | 'privacy' | 'notifications'>('profile');
  
  // Settings state
  const [settings, setSettings] = useState({
    status: (user?.status || 'online') as UserStatus,
    displayName: user?.display_name || user?.username || '',
    bio: '',
    privateMode: false,
    showOnlineStatus: true,
    showReadReceipts: true,
    notifications: true,
    notificationSound: true,
    darkMode: true,
  });

  const handleStatusChange = (status: UserStatus) => {
    setSettings({ ...settings, status });
    // TODO: Update status on server
  };

  const handleSave = () => {
    // TODO: Save settings to server
    console.log('Saving settings:', settings);
    onClose();
  };

  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="settings-modal" onClick={(e) => e.stopPropagation()}>
        <div className="modal-header">
          <h2>
            <Settings size={24} />
            <span>Settings</span>
          </h2>
          <button className="modal-close" onClick={onClose}>
            <X size={24} />
          </button>
        </div>

        <div className="settings-content">
          <div className="settings-tabs">
            <button
              className={`settings-tab ${activeTab === 'profile' ? 'active' : ''}`}
              onClick={() => setActiveTab('profile')}
            >
              <User size={18} />
              <span>Profile</span>
            </button>
            <button
              className={`settings-tab ${activeTab === 'privacy' ? 'active' : ''}`}
              onClick={() => setActiveTab('privacy')}
            >
              <Shield size={18} />
              <span>Privacy</span>
            </button>
            <button
              className={`settings-tab ${activeTab === 'notifications' ? 'active' : ''}`}
              onClick={() => setActiveTab('notifications')}
            >
              <Bell size={18} />
              <span>Notifications</span>
            </button>
          </div>

          <div className="settings-body">
            {activeTab === 'profile' && (
              <div className="settings-section">
                <h3>Profile Settings</h3>
                
                <div className="setting-item">
                  <label>Status</label>
                  <StatusSelector
                    currentStatus={settings.status}
                    onStatusChange={handleStatusChange}
                  />
                </div>

                <div className="setting-item">
                  <label htmlFor="display-name">Display Name</label>
                  <input
                    id="display-name"
                    type="text"
                    value={settings.displayName}
                    onChange={(e) => setSettings({ ...settings, displayName: e.target.value })}
                    placeholder="Your display name"
                  />
                </div>

                <div className="setting-item">
                  <label htmlFor="bio">Bio</label>
                  <textarea
                    id="bio"
                    value={settings.bio}
                    onChange={(e) => setSettings({ ...settings, bio: e.target.value })}
                    placeholder="Tell others about yourself..."
                    rows={3}
                    maxLength={200}
                  />
                  <small>{settings.bio.length}/200</small>
                </div>

                <div className="setting-item">
                  <label>Username</label>
                  <input
                    type="text"
                    value={user?.username || ''}
                    disabled
                    className="disabled-input"
                  />
                  <small>Username cannot be changed</small>
                </div>
              </div>
            )}

            {activeTab === 'privacy' && (
              <div className="settings-section">
                <h3>Privacy & Security</h3>

                <div className="setting-item">
                  <label className="toggle-label">
                    <div>
                      <strong>Private Mode</strong>
                      <p>Hide your online status from others</p>
                    </div>
                    <label className="toggle-switch">
                      <input
                        type="checkbox"
                        checked={settings.privateMode}
                        onChange={(e) => setSettings({ ...settings, privateMode: e.target.checked })}
                      />
                      <span className="toggle-slider"></span>
                    </label>
                  </label>
                </div>

                <div className="setting-item">
                  <label className="toggle-label">
                    <div>
                      <strong>Show Online Status</strong>
                      <p>Let others see when you're online</p>
                    </div>
                    <label className="toggle-switch">
                      <input
                        type="checkbox"
                        checked={settings.showOnlineStatus}
                        onChange={(e) => setSettings({ ...settings, showOnlineStatus: e.target.checked })}
                        disabled={settings.privateMode}
                      />
                      <span className="toggle-slider"></span>
                    </label>
                  </label>
                </div>

                <div className="setting-item">
                  <label className="toggle-label">
                    <div>
                      <strong>Read Receipts</strong>
                      <p>Show checkmarks when you read messages</p>
                    </div>
                    <label className="toggle-switch">
                      <input
                        type="checkbox"
                        checked={settings.showReadReceipts}
                        onChange={(e) => setSettings({ ...settings, showReadReceipts: e.target.checked })}
                      />
                      <span className="toggle-slider"></span>
                    </label>
                  </label>
                </div>
              </div>
            )}

            {activeTab === 'notifications' && (
              <div className="settings-section">
                <h3>Notification Settings</h3>

                <div className="setting-item">
                  <label className="toggle-label">
                    <div>
                      <strong>Enable Notifications</strong>
                      <p>Receive desktop notifications for new messages</p>
                    </div>
                    <label className="toggle-switch">
                      <input
                        type="checkbox"
                        checked={settings.notifications}
                        onChange={(e) => setSettings({ ...settings, notifications: e.target.checked })}
                      />
                      <span className="toggle-slider"></span>
                    </label>
                  </label>
                </div>

                <div className="setting-item">
                  <label className="toggle-label">
                    <div>
                      <Volume2 size={20} />
                      <div>
                        <strong>Notification Sound</strong>
                        <p>Play sound for new messages</p>
                      </div>
                    </div>
                    <label className="toggle-switch">
                      <input
                        type="checkbox"
                        checked={settings.notificationSound}
                        onChange={(e) => setSettings({ ...settings, notificationSound: e.target.checked })}
                        disabled={!settings.notifications}
                      />
                      <span className="toggle-slider"></span>
                    </label>
                  </label>
                </div>

                <div className="setting-item">
                  <label className="toggle-label">
                    <div>
                      <Moon size={20} />
                      <div>
                        <strong>Dark Mode</strong>
                        <p>Matrix theme enabled</p>
                      </div>
                    </div>
                    <label className="toggle-switch">
                      <input
                        type="checkbox"
                        checked={settings.darkMode}
                        onChange={(e) => setSettings({ ...settings, darkMode: e.target.checked })}
                      />
                      <span className="toggle-slider"></span>
                    </label>
                  </label>
                </div>
              </div>
            )}
          </div>
        </div>

        <div className="settings-footer">
          <Button variant="secondary" onClick={onClose}>
            Cancel
          </Button>
          <Button variant="primary" onClick={handleSave}>
            Save Changes
          </Button>
        </div>
      </div>
    </div>
  );
};
