import { useState } from 'react';
import type { Reaction } from '../../types/chat.types';
import './MessageReactions.css';

interface MessageReactionsProps {
  messageId: number;
  reactions: Reaction[];
  onReact: (emoji: string) => Promise<void>;
}

const QUICK_EMOJIS = ['👍', '❤️', '😂', '😮', '😢', '🔥', '🎉', '👏'];

export const MessageReactions = ({ reactions, onReact }: MessageReactionsProps) => {
  const [showPicker, setShowPicker] = useState(false);
  const [isAdding, setIsAdding] = useState(false);

  const handleReact = async (emoji: string) => {
    setShowPicker(false);
    setIsAdding(true);
    try {
      await onReact(emoji);
    } catch (error) {
      console.error('Failed to react:', error);
    } finally {
      setIsAdding(false);
    }
  };

  return (
    <div className="message-reactions">
      {/* Existing reactions */}
      {reactions && reactions.length > 0 && (
        <div className="reactions-list">
          {reactions.map((reaction) => (
            <button
              key={reaction.emoji}
              className={`reaction-bubble ${reaction.has_reacted ? 'reacted' : ''}`}
              onClick={() => handleReact(reaction.emoji)}
              disabled={isAdding}
              title={reaction.users.map(u => u.display_name || u.username).join(', ')}
            >
              <span className="reaction-emoji">{reaction.emoji}</span>
              <span className="reaction-count">{reaction.count}</span>
            </button>
          ))}
        </div>
      )}

      {/* Add reaction button */}
      <div className="reaction-picker-container">
        <button
          className="add-reaction-btn"
          onClick={() => setShowPicker(!showPicker)}
          disabled={isAdding}
          aria-label="Add reaction"
        >
          ➕
        </button>

        {showPicker && (
          <>
            <div 
              className="emoji-picker-backdrop"
              onClick={() => setShowPicker(false)}
            />
            <div className="emoji-picker">
              {QUICK_EMOJIS.map((emoji) => (
                <button
                  key={emoji}
                  className="emoji-option"
                  onClick={() => handleReact(emoji)}
                  disabled={isAdding}
                >
                  {emoji}
                </button>
              ))}
            </div>
          </>
        )}
      </div>
    </div>
  );
};
