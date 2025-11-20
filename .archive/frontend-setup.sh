#!/bin/bash
echo "🚀 Setting up SnakkaZ Frontend..."

cd /workspaces/SnakkaZ

# Create React app with Vite
npm create vite@latest frontend -- --template react-ts

cd frontend

# Install dependencies
echo "📦 Installing dependencies..."
npm install

# Core dependencies
npm install axios react-router-dom zustand
npm install @tanstack/react-query
npm install socket.io-client
npm install date-fns lucide-react
npm install clsx tailwind-merge

# Dev dependencies
npm install -D tailwindcss postcss autoprefixer
npm install -D @types/node

# Initialize Tailwind
npx tailwindcss init -p

echo "✅ Frontend setup complete!"
echo "📂 Directory: /workspaces/SnakkaZ/frontend"
echo "🔧 Next: npm run dev"
