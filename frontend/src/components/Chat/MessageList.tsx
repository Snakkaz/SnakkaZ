import { useEffect, useRef, useState } from 'react';
import { format } from 'date-fns';
import { FileText, Download } from 'lucide-react';
import type { Message } from '../../types/chat.types';
import { useAuthStore } from '../../store/authStore';
import { Avatar } from '../Common/Avatar';
import { MessageReactions } from './MessageReactions';
import { useSmoothScroll } from '../../hooks/useUX';
import { chatService } from '../../services/chat';
import './MessageList.css';

interface MessageListProps {
  messages: Message[];
  isLoading?: boolean;
}

export const MessageList = ({ messages, isLoading }: MessageListProps) => {
  const { user } = useAuthStore();
  const messageListRef = useRef<HTMLDivElement>(null);
  const messagesEndRef = useRef<HTMLDivElement>(null);
  const { scrollToBottom } = useSmoothScroll(messageListRef);
  const [reactions, setReactions] = useState<Record<number, any[]>>({});

  // Auto-scroll to bottom on new messages
  useEffect(() => {
    if (messages.length > 0) {
      scrollToBottom();
    }
  }, [messages.length, scrollToBottom]);

  const handleReaction = async (messageId: number, emoji: string) => {
    try {
      await chatService.toggleReaction(messageId, emoji);
      // Refresh reactions for this message
      const updatedReactions = await chatService.getReactions(messageId);
      setReactions(prev => ({
        ...prev,
        [messageId]: Array.isArray(updatedReactions) ? updatedReactions : []
      }));
    } catch (error) {
      console.error('Failed to toggle reaction:', error);
    }
  };

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
    <div className="message-list" ref={messageListRef}>
      {Object.entries(groupedMessages).map(([date, msgs]) => (
        <div key={date}>
          <div className="message-date-divider">
            <span>{formatDate(msgs[0].created_at)}</span>
          </div>
          
          {msgs.map((message) => {
            const currentUserId = user?.user_id || user?.id;
            const messageSenderId = typeof message.sender_id === 'string'
              ? Number(message.sender_id)
              : message.sender_id;
            const isOwn = messageSenderId === currentUserId;
            
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
                    {message.file_url && message.message_type === 'image' && (
                      <div className="message-image">
                        <img 
                          src={`https://snakkaz.com${message.file_url}`} 
                          alt="Shared image"
                          loading="lazy"
                        />
                      </div>
                    )}
                    
                    {message.file_url && message.message_type === 'file' && (
                      <div className="message-file">
                        <FileText size={24} />
                        <div className="message-file-info">
                          <span className="message-file-name">{message.content}</span>
                          <a 
                            href={`https://snakkaz.com${message.file_url}`}
                            download
                            className="message-file-download"
                          >
                            <Download size={16} />
                            Download
                          </a>
                        </div>
                      </div>
                    )}
                    
                    {message.message_type === 'text' && (
                      <p className="message-text">{message.content}</p>
                    )}
                    
                    <div className="message-meta">
                      <span className="message-time">{formatTime(message.created_at)}</span>
                      {message.is_edited && (
                        <span className="message-edited">edited</span>
                      )}
                    </div>
                  </div>

                  {/* Message Reactions */}
                  <MessageReactions
                    messageId={message.message_id}
                    reactions={message.reactions || reactions[message.message_id] || []}
                    onReact={(emoji) => handleReaction(message.message_id, emoji)}
                  />
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
