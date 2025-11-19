import { forwardRef } from 'react';
import type { InputHTMLAttributes } from 'react';
import './Input.css';

interface InputProps extends InputHTMLAttributes<HTMLInputElement> {
  label?: string;
  error?: string;
  helperText?: string;
  leftIcon?: React.ReactNode;
  rightIcon?: React.ReactNode;
}

export const Input = forwardRef<HTMLInputElement, InputProps>(
  ({ label, error, helperText, leftIcon, rightIcon, className = '', ...props }, ref) => {
    return (
      <div className="input-wrapper">
        {label && (
          <label className="input-label" htmlFor={props.id}>
            {label}
            {props.required && <span className="input-required">*</span>}
          </label>
        )}
        
        <div className="input-container">
          {leftIcon && <div className="input-icon input-icon-left">{leftIcon}</div>}
          
          <input
            ref={ref}
            className={[
              'input',
              leftIcon ? 'input-with-left-icon' : '',
              rightIcon ? 'input-with-right-icon' : '',
              error ? 'input-error' : '',
              className,
            ].filter(Boolean).join(' ')}
            {...props}
          />
          
          {rightIcon && <div className="input-icon input-icon-right">{rightIcon}</div>}
        </div>
        
        {error && <span className="input-error-text">{error}</span>}
        {helperText && !error && <span className="input-helper-text">{helperText}</span>}
      </div>
    );
  }
);

Input.displayName = 'Input';
