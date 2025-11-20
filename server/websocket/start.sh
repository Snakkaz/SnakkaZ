#!/bin/bash
# SnakkaZ WebSocket Server Startup Script

cd /home/snakqsqe/server/websocket

# Kill existing server if running
pkill -f "php.*server.php"

# Start WebSocket server in background
nohup php server.php > websocket.log 2>&1 &

echo "✅ WebSocket server started"
echo "📋 PID: $!"
echo "📝 Logs: websocket.log"
