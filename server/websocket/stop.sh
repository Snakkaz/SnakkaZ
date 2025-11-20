#!/bin/bash
# SnakkaZ WebSocket Server Stop Script

pkill -f "php.*server.php"

echo "✅ WebSocket server stopped"
