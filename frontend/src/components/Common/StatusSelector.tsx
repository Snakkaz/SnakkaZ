import { useState, useRef, useEffect } from 'react';
import { Circle, ChevronDown } from 'lucide-react';
import './StatusSelector.css';

export type UserStatus = 'online' | 'busy' | 'offline' | 'away';

interface StatusSelectorProps {
  currentStatus: UserStatus;
  onStatusChange: (status: UserStatus) => void;
}

const statusConfig = {
  online: { label: 'Online', color: '#00ff41', icon: '🟢' },
  busy: { label: 'Busy', color: '#ff4444', icon: '🔴' },
  away: { label: 'Away', color: '#ffa500', icon: '🟡' },
  offline: { label: 'Offline', color: '#666', icon: '⚫' },
};

export const StatusSelector = ({ currentStatus, onStatusChange }: StatusSelectorProps) => {
  const [isOpen, setIsOpen] = useState(false);
  const dropdownRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target as Node)) {
        setIsOpen(false);
      }
    };

    if (isOpen) {
      document.addEventListener('mousedown', handleClickOutside);
    }

    return () => {
      document.removeEventListener('mousedown', handleClickOutside);
    };
  }, [isOpen]);

  const handleStatusSelect = (status: UserStatus) => {
    onStatusChange(status);
    setIsOpen(false);
  };

  const current = statusConfig[currentStatus];

  return (
    <div className="status-selector" ref={dropdownRef}>
      <button
        className="status-selector-button"
        onClick={() => setIsOpen(!isOpen)}
        title="Change status"
      >
        <Circle
          size={12}
          fill={current.color}
          color={current.color}
          className="status-indicator"
        />
        <span className="status-label">{current.label}</span>
        <ChevronDown size={16} className={`status-chevron ${isOpen ? 'open' : ''}`} />
      </button>

      {isOpen && (
        <div className="status-dropdown">
          {(Object.keys(statusConfig) as UserStatus[]).map((status) => {
            const config = statusConfig[status];
            return (
              <button
                key={status}
                className={`status-option ${status === currentStatus ? 'active' : ''}`}
                onClick={() => handleStatusSelect(status)}
              >
                <Circle
                  size={12}
                  fill={config.color}
                  color={config.color}
                  className="status-indicator"
                />
                <span className="status-option-label">{config.label}</span>
                {status === currentStatus && <span className="status-check">✓</span>}
              </button>
            );
          })}
        </div>
      )}
    </div>
  );
};
