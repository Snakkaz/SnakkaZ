import { StrictMode, useEffect } from 'react'
import { createRoot } from 'react-dom/client'
import './index.css'
import App from './App.tsx'
import { useAuthStore } from './store/authStore'
import { useChatStore } from './store/chatStore'

function AppWrapper() {
  const initAuth = useAuthStore((state) => state.initAuth)
  const initWebSocket = useChatStore((state) => state.initWebSocket)
  const cleanupWebSocket = useChatStore((state) => state.cleanupWebSocket)

  useEffect(() => {
    // Initialize auth from localStorage
    initAuth()
    
    // Initialize WebSocket listeners
    initWebSocket()
    
    // Cleanup on unmount
    return () => {
      cleanupWebSocket()
    }
  }, [initAuth, initWebSocket, cleanupWebSocket])

  return <App />
}

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <AppWrapper />
  </StrictMode>,
)
