import { useState, useRef, useEffect } from 'react';
import { Search, X } from 'lucide-react';
import { chatService } from '../../services/chat';
import './SearchBar.css';

interface SearchResult {
  messages?: Array<{
    message_id: number;
    content: string;
    created_at: string;
    room_id: number;
    room_name: string;
    username: string;
  }>;
}

interface SearchBarProps {
  roomId: number | null;
  onMessageSelect?: (messageId: number) => void;
}

export const SearchBar = ({ roomId, onMessageSelect }: SearchBarProps) => {
  const [query, setQuery] = useState('');
  const [results, setResults] = useState<SearchResult | null>(null);
  const [isOpen, setIsOpen] = useState(false);
  const [isSearching, setIsSearching] = useState(false);
  const searchRef = useRef<HTMLDivElement>(null);
  const inputRef = useRef<HTMLInputElement>(null);

  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (searchRef.current && !searchRef.current.contains(event.target as Node)) {
        setIsOpen(false);
      }
    };

    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  const handleSearch = async (searchQuery: string) => {
    if (!searchQuery.trim()) {
      setResults(null);
      return;
    }

    setIsSearching(true);
    try {
      const response = await chatService.searchMessages(searchQuery, roomId);
      setResults(response as SearchResult);
      setIsOpen(true);
    } catch (error) {
      console.error('Search failed:', error);
    } finally {
      setIsSearching(false);
    }
  };

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const value = e.target.value;
    setQuery(value);
    
    // Debounce search
    const timeoutId = setTimeout(() => {
      handleSearch(value);
    }, 300);

    return () => clearTimeout(timeoutId);
  };

  const handleClear = () => {
    setQuery('');
    setResults(null);
    setIsOpen(false);
    inputRef.current?.focus();
  };

  const handleResultClick = (messageId: number) => {
    onMessageSelect?.(messageId);
    setIsOpen(false);
  };

  const highlightMatch = (text: string, query: string) => {
    if (!query) return text;
    
    const parts = text.split(new RegExp(`(${query})`, 'gi'));
    return parts.map((part, index) => 
      part.toLowerCase() === query.toLowerCase() 
        ? <mark key={index}>{part}</mark>
        : part
    );
  };

  return (
    <div className="search-bar" ref={searchRef}>
      <div className="search-input-wrapper">
        <Search size={18} className="search-icon" />
        <input
          ref={inputRef}
          type="text"
          className="search-input"
          placeholder="Search messages..."
          value={query}
          onChange={handleInputChange}
          onFocus={() => query && setIsOpen(true)}
        />
        {query && (
          <button
            className="search-clear-btn"
            onClick={handleClear}
            title="Clear search"
          >
            <X size={18} />
          </button>
        )}
      </div>

      {isOpen && results && (
        <div className="search-results">
          {isSearching ? (
            <div className="search-loading">Searching...</div>
          ) : results.messages && results.messages.length > 0 ? (
            <div className="search-results-list">
              <div className="search-results-header">
                Messages ({results.messages.length})
              </div>
              {results.messages.map((message) => (
                <div
                  key={message.message_id}
                  className="search-result-item"
                  onClick={() => handleResultClick(message.message_id)}
                >
                  <div className="search-result-meta">
                    <span className="search-result-user">{message.username}</span>
                    <span className="search-result-room">#{message.room_name}</span>
                  </div>
                  <div className="search-result-content">
                    {highlightMatch(message.content, query)}
                  </div>
                </div>
              ))}
            </div>
          ) : (
            <div className="search-no-results">
              No messages found for "{query}"
            </div>
          )}
        </div>
      )}
    </div>
  );
};
