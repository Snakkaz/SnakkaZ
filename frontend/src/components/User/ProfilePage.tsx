import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { ArrowLeft, Mail, Calendar, Edit2, Save, X } from 'lucide-react';
import { useAuthStore } from '../../store/authStore';
import { Avatar } from '../Common/Avatar';
import { Button } from '../Common/Button';
import { Input } from '../Common/Input';
import './ProfilePage.css';

export const ProfilePage = () => {
  const { userId } = useParams<{ userId: string }>();
  const navigate = useNavigate();
  const { user: currentUser } = useAuthStore();
  
  const [isEditing, setIsEditing] = useState(false);
  const [isSaving, setIsSaving] = useState(false);
  const [profile, setProfile] = useState({
    user_id: 0,
    username: '',
    display_name: '',
    email: '',
    bio: '',
    avatar_url: '',
    status: 'offline' as 'online' | 'offline' | 'away',
    created_at: '',
  });
  
  const [editForm, setEditForm] = useState({
    display_name: '',
    bio: '',
  });

  const isOwnProfile = !userId || String(profile.user_id) === String(currentUser?.user_id);

  useEffect(() => {
    // Load profile data
    // For now, using current user data
    if (currentUser && currentUser.user_id) {
      const profileData = {
        user_id: currentUser.user_id,
        username: currentUser.username,
        display_name: currentUser.display_name || currentUser.username,
        email: currentUser.email || '',
        bio: 'SnakkaZ enthusiast 🚀',
        avatar_url: currentUser.avatar_url || '',
        status: 'online' as const,
        created_at: new Date().toISOString(),
      };
      setProfile(profileData);
      setEditForm({
        display_name: profileData.display_name,
        bio: profileData.bio,
      });
    }
  }, [userId, currentUser]);

  const handleEdit = () => {
    setIsEditing(true);
  };

  const handleCancel = () => {
    setIsEditing(false);
    setEditForm({
      display_name: profile.display_name,
      bio: profile.bio,
    });
  };

  const handleSave = async () => {
    setIsSaving(true);
    try {
      // TODO: Call API to update profile
      await new Promise(resolve => setTimeout(resolve, 1000));
      
      setProfile({
        ...profile,
        display_name: editForm.display_name,
        bio: editForm.bio,
      });
      setIsEditing(false);
    } catch (error) {
      console.error('Failed to save profile:', error);
    } finally {
      setIsSaving(false);
    }
  };

  const formatDate = (dateString: string) => {
    try {
      return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
      });
    } catch {
      return '';
    }
  };

  return (
    <div className="profile-page">
      <div className="profile-header">
        <button className="profile-back-btn" onClick={() => navigate('/chat')}>
          <ArrowLeft size={20} />
          Back to Chat
        </button>
      </div>

      <div className="profile-container">
        <div className="profile-banner">
          <div className="profile-avatar-section">
            <Avatar
              src={profile.avatar_url}
              alt={profile.username}
              size="xl"
              status={profile.status}
              fallback={profile.username}
            />
            {isOwnProfile && !isEditing && (
              <button className="profile-edit-avatar-btn" title="Change avatar">
                <Edit2 size={16} />
              </button>
            )}
          </div>
        </div>

        <div className="profile-content">
          <div className="profile-info-header">
            {!isEditing ? (
              <>
                <div>
                  <h1 className="profile-display-name">{profile.display_name}</h1>
                  <p className="profile-username">@{profile.username}</p>
                </div>
                {isOwnProfile && (
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={handleEdit}
                  >
                    <Edit2 size={16} />
                    Edit Profile
                  </Button>
                )}
              </>
            ) : (
              <div className="profile-edit-actions">
                <Button
                  variant="primary"
                  size="sm"
                  onClick={handleSave}
                  disabled={isSaving}
                >
                  <Save size={16} />
                  {isSaving ? 'Saving...' : 'Save'}
                </Button>
                <Button
                  variant="outline"
                  size="sm"
                  onClick={handleCancel}
                  disabled={isSaving}
                >
                  <X size={16} />
                  Cancel
                </Button>
              </div>
            )}
          </div>

          <div className="profile-details">
            {!isEditing ? (
              <>
                <div className="profile-detail-item">
                  <Mail size={18} />
                  <span>{profile.email || 'No email set'}</span>
                </div>
                <div className="profile-detail-item">
                  <Calendar size={18} />
                  <span>Joined {formatDate(profile.created_at)}</span>
                </div>
              </>
            ) : null}
          </div>

          <div className="profile-section">
            <h3>Display Name</h3>
            {!isEditing ? (
              <p>{profile.display_name}</p>
            ) : (
              <Input
                value={editForm.display_name}
                onChange={(e) => setEditForm({ ...editForm, display_name: e.target.value })}
                placeholder="Your display name"
              />
            )}
          </div>

          <div className="profile-section">
            <h3>Bio</h3>
            {!isEditing ? (
              <p>{profile.bio || 'No bio yet'}</p>
            ) : (
              <textarea
                className="profile-bio-textarea"
                value={editForm.bio}
                onChange={(e) => setEditForm({ ...editForm, bio: e.target.value })}
                placeholder="Tell us about yourself..."
                rows={4}
                maxLength={500}
              />
            )}
          </div>
        </div>
      </div>
    </div>
  );
};
