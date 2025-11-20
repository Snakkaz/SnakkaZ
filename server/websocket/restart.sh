#!/bin/bash
# SnakkaZ WebSocket Server Restart Script

cd /home/snakqsqe/server/websocket

./stop.sh
sleep 2
./start.sh

echo "✅ WebSocket server restarted"
