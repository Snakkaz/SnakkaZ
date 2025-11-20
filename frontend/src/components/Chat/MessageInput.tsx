import { useState, useEffect, useRef } from 'react';
import { Send, Paperclip, X } from 'lucide-react';
import { Button } from '../Common/Button';
import { EmojiPickerButton } from '../Common/EmojiPickerButton';
import './MessageInput.css';

interface MessageInputProps {
  onSend: (message: string, fileUrl?: string) => void;
  onTyping?: () => void;
  onStopTyping?: () => void;
  disabled?: boolean;
  placeholder?: string;
}

export const MessageInput = ({
  onSend,
  onTyping,
  onStopTyping,
  disabled = false,
  placeholder = 'Type a message...',
}: MessageInputProps) => {
  const [message, setMessage] = useState('');
  const [isTyping, setIsTyping] = useState(false);
  const [selectedFile, setSelectedFile] = useState<File | null>(null);
  const [isUploading, setIsUploading] = useState(false);
  const typingTimeoutRef = useRef<ReturnType<typeof setTimeout> | null>(null);
  const textareaRef = useRef<HTMLTextAreaElement>(null);
  const fileInputRef = useRef<HTMLInputElement>(null);

  useEffect(() => {
    // Cleanup timeout on unmount
    return () => {
      if (typingTimeoutRef.current) {
        clearTimeout(typingTimeoutRef.current);
      }
    };
  }, []);

  const handleChange = (e: React.ChangeEvent<HTMLTextAreaElement>) => {
    setMessage(e.target.value);
    
    // Auto-resize textarea
    if (textareaRef.current) {
      textareaRef.current.style.height = 'auto';
      textareaRef.current.style.height = `${textareaRef.current.scrollHeight}px`;
    }
    
    // Typing indicator logic
    if (!isTyping && e.target.value) {
      setIsTyping(true);
      onTyping?.();
    }
    
    // Clear previous timeout
    if (typingTimeoutRef.current) {
      clearTimeout(typingTimeoutRef.current);
    }
    
    // Set new timeout to stop typing
    typingTimeoutRef.current = setTimeout(() => {
      setIsTyping(false);
      onStopTyping?.();
    }, 2000);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if ((!message.trim() && !selectedFile) || disabled || isUploading) return;
    
    let fileUrl: string | undefined;
    
    // Upload file if selected
    if (selectedFile) {
      setIsUploading(true);
      try {
        const formData = new FormData();
        formData.append('file', selectedFile);
        
        const token = localStorage.getItem('auth_token');
        const response = await fetch('https://snakkaz.com/api/upload.php', {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${token}`,
          },
          body: formData,
        });
        
        const data = await response.json();
        
        if (data.success && data.data.file_url) {
          fileUrl = data.data.file_url;
        } else {
          throw new Error('Upload failed');
        }
      } catch (error) {
        console.error('File upload error:', error);
        alert('Failed to upload file');
        setIsUploading(false);
        return;
      }
      setIsUploading(false);
    }
    
    onSend(message.trim() || 'Sent a file', fileUrl);
    setMessage('');
    setSelectedFile(null);
    
    // Reset textarea height
    if (textareaRef.current) {
      textareaRef.current.style.height = 'auto';
    }
    
    // Stop typing indicator
    setIsTyping(false);
    onStopTyping?.();
    
    if (typingTimeoutRef.current) {
      clearTimeout(typingTimeoutRef.current);
    }
  };

  const handleKeyDown = (e: React.KeyboardEvent<HTMLTextAreaElement>) => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      handleSubmit(e as React.FormEvent<HTMLTextAreaElement>);
    }
  };

  const handleEmojiSelect = (emoji: string) => {
    setMessage(prev => prev + emoji);
    textareaRef.current?.focus();
  };

  const handleFileSelect = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      // Validate file size (max 10MB)
      if (file.size > 10 * 1024 * 1024) {
        alert('File too large. Maximum size is 10MB');
        return;
      }
      setSelectedFile(file);
    }
  };

  const handleFileRemove = () => {
    setSelectedFile(null);
    if (fileInputRef.current) {
      fileInputRef.current.value = '';
    }
  };

  return (
    <form className="message-input-container" onSubmit={handleSubmit}>
      {selectedFile && (
        <div className="file-preview">
          <div className="file-preview-info">
            <Paperclip size={16} />
            <span>{selectedFile.name}</span>
            <span className="file-size">
              {(selectedFile.size / 1024).toFixed(1)} KB
            </span>
          </div>
          <button
            type="button"
            className="file-remove-btn"
            onClick={handleFileRemove}
            title="Remove file"
          >
            <X size={16} />
          </button>
        </div>
      )}
      
      <div className="message-input-wrapper">
        <div className="message-input-actions">
          <EmojiPickerButton onEmojiSelect={handleEmojiSelect} />
          
          <input
            ref={fileInputRef}
            type="file"
            className="file-input-hidden"
            onChange={handleFileSelect}
            accept="image/*,.pdf,.doc,.docx,.xls,.xlsx"
            disabled={disabled}
          />
          
          <button
            type="button"
            className="attachment-button"
            title="Attach file"
            disabled={disabled || isUploading}
            onClick={() => fileInputRef.current?.click()}
          >
            <Paperclip size={20} />
          </button>
        </div>
        
        <textarea
          ref={textareaRef}
          className="message-input"
          value={message}
          onChange={handleChange}
          onKeyDown={handleKeyDown}
          placeholder={placeholder}
          disabled={disabled}
          rows={1}
        />
        
        <Button
          type="submit"
          variant="primary"
          size="sm"
          disabled={(!message.trim() && !selectedFile) || disabled || isUploading}
          className="message-send-btn"
        >
          {isUploading ? '...' : <Send size={20} />}
        </Button>
      </div>
    </form>
  );
};
