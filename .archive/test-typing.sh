#!/bin/bash

# Test typing indicator
AUTH_TOKEN="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJzbmFra2F6IiwiYXVkIjoic25ha2theiIsImlhdCI6MTczODMzNTY3OCwiZXhwIjoxNzM4NDIyMDc4LCJ1c2VyX2lkIjoiNSJ9.1zI2GH-FhQe-bVIh8TonmfPJI8bMMZBDiXW8_NB0pXI"
ROOM_ID=1

echo "🔥 Starting typing indicator..."
curl -X POST "https://snakkaz.com/api/realtime/typing.php" \
  -H "Authorization: Bearer $AUTH_TOKEN" \
  -H "Content-Type: application/json" \
  -d "{\"room_id\": $ROOM_ID, \"is_typing\": true}"

echo -e "\n\n⏰ Waiting 3 seconds..."
sleep 3

echo -e "\n📋 Polling for typing users..."
curl -X GET "https://snakkaz.com/api/realtime/poll.php?last_message_id=0" \
  -H "Authorization: Bearer $AUTH_TOKEN" | jq

echo -e "\n\n🛑 Stopping typing indicator..."
curl -X POST "https://snakkaz.com/api/realtime/typing.php" \
  -H "Authorization: Bearer $AUTH_TOKEN" \
  -H "Content-Type: application/json" \
  -d "{\"room_id\": $ROOM_ID, \"is_typing\": false}"

