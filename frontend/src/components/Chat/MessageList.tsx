import { useEffect, useRef } from 'react';
import { format } from 'date-fns';
import type { Message } from '../../types/chat.types';
import { useAuthStore } from '../../store/authStore';
import { Avatar } from '../Common/Avatar';
import './MessageList.css';

interface MessageListProps {
  messages: Message[];
  isLoading?: boolean;
}

export const MessageList = ({ messages, isLoading }: MessageListProps) => {
  const { user } = useAuthStore();
  const messagesEndRef = useRef<HTMLDivElement>(null);

  // Auto-scroll to bottom on new messages
  useEffect(() => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  }, [messages]);

  const formatTime = (timestamp: string) => {
    try {
      return format(new Date(timestamp), 'HH:mm');
    } catch {
      return '';
    }
  };

  const formatDate = (timestamp: string) => {
    try {
      const date = new Date(timestamp);
      const today = new Date();
      const yesterday = new Date(today);
      yesterday.setDate(yesterday.getDate() - 1);

      if (date.toDateString() === today.toDateString()) {
        return 'Today';
      } else if (date.toDateString() === yesterday.toDateString()) {
        return 'Yesterday';
      } else {
        return format(date, 'MMM dd, yyyy');
      }
    } catch {
      return '';
    }
  };

  // Group messages by date
  const groupedMessages = messages.reduce((groups, message) => {
    const date = new Date(message.created_at).toDateString();
    if (!groups[date]) {
      groups[date] = [];
    }
    groups[date].push(message);
    return groups;
  }, {} as Record<string, Message[]>);

  if (isLoading) {
    return (
      <div className="message-list-loading">
        <div className="spinner"></div>
        <p>Loading messages...</p>
      </div>
    );
  }

  if (messages.length === 0) {
    return (
      <div className="message-list-empty">
        <p>No messages yet. Start the conversation!</p>
      </div>
    );
  }

  return (
    <div className="message-list">
      {Object.entries(groupedMessages).map(([date, msgs]) => (
        <div key={date}>
          <div className="message-date-divider">
            <span>{formatDate(msgs[0].created_at)}</span>
          </div>
          
          {msgs.map((message) => {
            const isOwn = message.sender_id === user?.user_id;
            
            return (
              <div
                key={message.message_id}
                className={`message ${isOwn ? 'message-own' : 'message-other'}`}
              >
                {!isOwn && (
                  <Avatar
                    src={message.sender?.avatar_url}
                    alt={message.sender?.display_name || message.sender?.username}
                    size="sm"
                    status={message.sender?.status}
                  />
                )}
                
                <div className="message-content">
                  {!isOwn && (
                    <div className="message-sender">
                      {message.sender?.display_name || message.sender?.username}
                    </div>
                  )}
                  
                  <div className={`message-bubble ${isOwn ? 'message-bubble-own' : ''}`}>
                    <p className="message-text">{message.content}</p>
                    
                    <div className="message-meta">
                      <span className="message-time">{formatTime(message.created_at)}</span>
                      {message.is_edited && (
                        <span className="message-edited">edited</span>
                      )}
                    </div>
                  </div>
                </div>
              </div>
            );
          })}
        </div>
      ))}
      
      <div ref={messagesEndRef} />
    </div>
  );
};
