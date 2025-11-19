import React from 'react';
import './AuthLayout.css';

interface AuthLayoutProps {
  children: React.ReactNode;
}

export const AuthLayout: React.FC<AuthLayoutProps> = ({ children }) => {
  return (
    <div className="auth-layout">
      <div className="auth-layout-background">
        <div className="auth-layout-gradient"></div>
      </div>
      
      <div className="auth-layout-content">
        <div className="auth-layout-logo">
          <h1 className="auth-logo-text">SnakkaZ</h1>
          <p className="auth-logo-tagline">Connect. Chat. Collaborate.</p>
        </div>
        
        <div className="auth-layout-form">
          {children}
        </div>
        
        <div className="auth-layout-footer">
          <p>&copy; 2025 SnakkaZ. All rights reserved.</p>
        </div>
      </div>
    </div>
  );
};
