import React from 'react';
import './Avatar.css';

interface AvatarProps {
  src?: string;
  alt?: string;
  size?: 'sm' | 'md' | 'lg' | 'xl';
  status?: 'online' | 'offline' | 'away';
  fallback?: string;
}

export const Avatar: React.FC<AvatarProps> = ({
  src,
  alt = 'Avatar',
  size = 'md',
  status,
  fallback,
}) => {
  const [imageError, setImageError] = React.useState(false);
  
  const getInitials = () => {
    if (fallback) {
      return fallback.substring(0, 2).toUpperCase();
    }
    return alt.substring(0, 2).toUpperCase();
  };

  return (
    <div className={`avatar avatar-${size}`}>
      {src && !imageError ? (
        <img
          src={src}
          alt={alt}
          className="avatar-image"
          onError={() => setImageError(true)}
        />
      ) : (
        <div className="avatar-fallback">
          {getInitials()}
        </div>
      )}
      
      {status && (
        <span className={`avatar-status avatar-status-${status}`}></span>
      )}
    </div>
  );
};
