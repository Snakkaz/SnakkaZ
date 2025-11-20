import { useState, useRef, useEffect } from 'react';
import EmojiPicker, { type EmojiClickData, Theme } from 'emoji-picker-react';
import './EmojiPickerButton.css';

interface EmojiPickerButtonProps {
  onEmojiSelect: (emoji: string) => void;
  theme?: 'light' | 'dark' | 'auto';
}

export const EmojiPickerButton = ({ onEmojiSelect, theme = 'auto' }: EmojiPickerButtonProps) => {
  const [showPicker, setShowPicker] = useState(false);
  const pickerRef = useRef<HTMLDivElement>(null);

  const handleEmojiClick = (emojiData: EmojiClickData) => {
    onEmojiSelect(emojiData.emoji);
    setShowPicker(false);
  };

  // Close picker when clicking outside
  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (pickerRef.current && !pickerRef.current.contains(event.target as Node)) {
        setShowPicker(false);
      }
    };

    if (showPicker) {
      document.addEventListener('mousedown', handleClickOutside);
    }

    return () => {
      document.removeEventListener('mousedown', handleClickOutside);
    };
  }, [showPicker]);

  return (
    <div className="emoji-picker-container" ref={pickerRef}>
      <button
        type="button"
        className="emoji-picker-button"
        onClick={() => setShowPicker(!showPicker)}
        title="Add emoji"
      >
        😊
      </button>
      
      {showPicker && (
        <div className="emoji-picker-popup">
          <EmojiPicker
            onEmojiClick={handleEmojiClick}
            theme={theme === 'auto' ? Theme.AUTO : theme === 'dark' ? Theme.DARK : Theme.LIGHT}
            searchDisabled={false}
            skinTonesDisabled={false}
            width={320}
            height={400}
          />
        </div>
      )}
    </div>
  );
};
