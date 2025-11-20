// Smooth Room Switching Hook
import { useEffect, useState } from 'react';

interface UseRoomTransitionOptions {
  enabled?: boolean;
  duration?: number;
}

export function useRoomTransition(options: UseRoomTransitionOptions = {}) {
  const { enabled = true, duration = 300 } = options;
  const [isTransitioning, setIsTransitioning] = useState(false);

  const startTransition = () => {
    if (!enabled) return Promise.resolve();
    
    setIsTransitioning(true);
    return new Promise<void>((resolve) => {
      setTimeout(() => {
        setIsTransitioning(false);
        resolve();
      }, duration);
    });
  };

  return {
    isTransitioning,
    startTransition,
  };
}

// Smooth Scroll Hook
export function useSmoothScroll(containerRef: React.RefObject<HTMLElement | null>) {
  const scrollToBottom = (behavior: ScrollBehavior = 'smooth') => {
    if (!containerRef.current) return;
    
    containerRef.current.scrollTo({
      top: containerRef.current.scrollHeight,
      behavior,
    });
  };

  const scrollToTop = (behavior: ScrollBehavior = 'smooth') => {
    if (!containerRef.current) return;
    
    containerRef.current.scrollTo({
      top: 0,
      behavior,
    });
  };

  const scrollToElement = (
    element: HTMLElement,
    behavior: ScrollBehavior = 'smooth'
  ) => {
    if (!containerRef.current) return;
    
    element.scrollIntoView({ behavior, block: 'nearest' });
  };

  return {
    scrollToBottom,
    scrollToTop,
    scrollToElement,
  };
}

// Message Animation Hook
interface UseMessageAnimationOptions {
  enabled?: boolean;
  staggerDelay?: number;
}

export function useMessageAnimation(
  options: UseMessageAnimationOptions = {}
) {
  const { enabled = true, staggerDelay = 50 } = options;
  const [visibleMessages, setVisibleMessages] = useState<Set<string>>(
    new Set()
  );

  const animateMessage = (messageId: string, index: number) => {
    if (!enabled) {
      setVisibleMessages((prev) => new Set(prev).add(messageId));
      return;
    }

    setTimeout(() => {
      setVisibleMessages((prev) => new Set(prev).add(messageId));
    }, index * staggerDelay);
  };

  const resetAnimations = () => {
    setVisibleMessages(new Set());
  };

  return {
    visibleMessages,
    animateMessage,
    resetAnimations,
  };
}

// Typing Indicator Hook
interface UseTypingIndicatorOptions {
  timeout?: number;
}

export function useTypingIndicator(
  onTyping: (isTyping: boolean) => void,
  options: UseTypingIndicatorOptions = {}
) {
  const { timeout = 3000 } = options;
  const [timeoutId, setTimeoutId] = useState<ReturnType<typeof setTimeout> | null>(null);

  const handleTyping = () => {
    // Clear existing timeout
    if (timeoutId) {
      clearTimeout(timeoutId);
    }

    // Notify that user is typing
    onTyping(true);

    // Set new timeout to stop typing indicator
    const newTimeout = setTimeout(() => {
      onTyping(false);
      setTimeoutId(null);
    }, timeout);

    setTimeoutId(newTimeout);
  };

  const stopTyping = () => {
    if (timeoutId) {
      clearTimeout(timeoutId);
      setTimeoutId(null);
    }
    onTyping(false);
  };

  useEffect(() => {
    return () => {
      if (timeoutId) {
        clearTimeout(timeoutId);
      }
    };
  }, [timeoutId]);

  return {
    handleTyping,
    stopTyping,
  };
}

// Auto-resize Textarea Hook
export function useAutoResize(
  textareaRef: React.RefObject<HTMLTextAreaElement>,
  value: string
) {
  useEffect(() => {
    const textarea = textareaRef.current;
    if (!textarea) return;

    // Reset height to auto to get correct scrollHeight
    textarea.style.height = 'auto';
    
    // Set height to scrollHeight
    const newHeight = Math.min(textarea.scrollHeight, 120); // max 120px
    textarea.style.height = `${newHeight}px`;
  }, [value, textareaRef]);
}

// Debounce Hook
export function useDebounce<T>(value: T, delay: number): T {
  const [debouncedValue, setDebouncedValue] = useState<T>(value);

  useEffect(() => {
    const handler = setTimeout(() => {
      setDebouncedValue(value);
    }, delay);

    return () => {
      clearTimeout(handler);
    };
  }, [value, delay]);

  return debouncedValue;
}

// Intersection Observer Hook (for infinite scroll)
export function useIntersectionObserver(
  elementRef: React.RefObject<HTMLElement>,
  callback: () => void,
  options: IntersectionObserverInit = {}
) {
  useEffect(() => {
    const element = elementRef.current;
    if (!element) return;

    const observer = new IntersectionObserver(([entry]) => {
      if (entry.isIntersecting) {
        callback();
      }
    }, options);

    observer.observe(element);

    return () => {
      observer.disconnect();
    };
  }, [elementRef, callback, options]);
}

// Online Status Hook
export function useOnlineStatus() {
  const [isOnline, setIsOnline] = useState(navigator.onLine);

  useEffect(() => {
    const handleOnline = () => setIsOnline(true);
    const handleOffline = () => setIsOnline(false);

    window.addEventListener('online', handleOnline);
    window.addEventListener('offline', handleOffline);

    return () => {
      window.removeEventListener('online', handleOnline);
      window.removeEventListener('offline', handleOffline);
    };
  }, []);

  return isOnline;
}

// Clipboard Hook
export function useClipboard() {
  const [isCopied, setIsCopied] = useState(false);

  const copy = async (text: string) => {
    try {
      await navigator.clipboard.writeText(text);
      setIsCopied(true);
      setTimeout(() => setIsCopied(false), 2000);
      return true;
    } catch (error) {
      console.error('Failed to copy:', error);
      return false;
    }
  };

  return { copy, isCopied };
}
