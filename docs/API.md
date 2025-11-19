# 📡 SnakkaZ Chat - API Documentation

**Base URL:** `https://www.snakkaz.com/api`  
**Version:** 1.0.0

---

## 🔐 Authentication

All endpoints except `/auth/register` and `/auth/login` require authentication.

**Header:**
```
Authorization: Bearer <your_token>
```

---

## 📍 Endpoints

### Health Check

#### `GET /health.php`
Check API and database status.

**Response:**
```json
{
  "status": "ok",
  "database": "connected",
  "uploads": "writable",
  "timestamp": "2025-11-19 12:00:00",
  "version": "1.0.0"
}
```

---

## 🔑 Authentication Endpoints

### Register User

#### `POST /auth/register.php`

**Request:**
```json
{
  "username": "johndoe",
  "email": "john@example.com",
  "password": "secure_password123",
  "display_name": "John Doe"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Registration successful",
  "data": {
    "token": "abc123...",
    "user": {
      "id": 1,
      "username": "johndoe",
      "email": "john@example.com",
      "display_name": "John Doe",
      "avatar_url": null,
      "status": "offline",
      "created_at": "2025-11-19 12:00:00"
    }
  }
}
```

**Errors:**
- `422` - Validation failed
- `409` - Username or email already exists

---

### Login

#### `POST /auth/login.php`

**Request:**
```json
{
  "email": "john@example.com",
  "password": "secure_password123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "xyz789...",
    "user": {
      "id": 1,
      "username": "johndoe",
      "email": "john@example.com",
      "display_name": "John Doe",
      "avatar_url": null,
      "status": "online"
    }
  }
}
```

**Errors:**
- `401` - Invalid email or password

---

### Logout

#### `POST /auth/logout.php`
**Headers:** `Authorization: Bearer <token>`

**Response:**
```json
{
  "success": true,
  "message": "Logout successful",
  "data": []
}
```

---

## 💬 Chat Endpoints

### Get Rooms

#### `GET /chat/rooms.php`
**Headers:** `Authorization: Bearer <token>`

Get all chat rooms user is member of.

**Response:**
```json
{
  "success": true,
  "message": "Success",
  "data": [
    {
      "id": 1,
      "name": "General Chat",
      "type": "group",
      "avatar_url": null,
      "description": "Welcome to SnakkaZ",
      "last_message": "Hello everyone!",
      "last_message_time": "2025-11-19 12:30:00",
      "message_count": 42,
      "member_count": 5,
      "created_at": "2025-11-19 10:00:00"
    }
  ]
}
```

---

### Create Room

#### `POST /chat/rooms.php`
**Headers:** `Authorization: Bearer <token>`

**Request:**
```json
{
  "name": "Project Team",
  "type": "group",
  "description": "Team collaboration",
  "members": [2, 3, 4]
}
```

**Response:**
```json
{
  "success": true,
  "message": "Room created successfully",
  "data": {
    "id": 5,
    "name": "Project Team",
    "type": "group",
    "creator_id": 1,
    "description": "Team collaboration",
    "created_at": "2025-11-19 13:00:00"
  }
}
```

---

### Get Messages

#### `GET /chat/messages.php?room_id=1&limit=50&offset=0`
**Headers:** `Authorization: Bearer <token>`

**Parameters:**
- `room_id` (required) - Chat room ID
- `limit` (optional, default: 50, max: 100)
- `offset` (optional, default: 0)

**Response:**
```json
{
  "success": true,
  "message": "Success",
  "data": [
    {
      "id": 1,
      "room_id": 1,
      "user_id": 1,
      "content": "Hello everyone!",
      "message_type": "text",
      "file_url": null,
      "reply_to_id": null,
      "is_edited": false,
      "created_at": "2025-11-19 12:30:00",
      "username": "johndoe",
      "display_name": "John Doe",
      "avatar_url": null
    }
  ]
}
```

**Errors:**
- `400` - room_id is required
- `403` - Not a member of this room

---

### Send Message

#### `POST /chat/send.php`
**Headers:** `Authorization: Bearer <token>`

**Request:**
```json
{
  "room_id": 1,
  "content": "Hello everyone!",
  "message_type": "text",
  "reply_to_id": null
}
```

**Response:**
```json
{
  "success": true,
  "message": "Message sent successfully",
  "data": {
    "id": 123,
    "room_id": 1,
    "user_id": 1,
    "content": "Hello everyone!",
    "message_type": "text",
    "created_at": "2025-11-19 13:30:00",
    "username": "johndoe",
    "display_name": "John Doe",
    "avatar_url": null
  }
}
```

**Errors:**
- `400` - Missing required fields
- `403` - Not a member of this room

---

## ❌ Error Responses

All errors follow this format:

```json
{
  "success": false,
  "error": "Error message",
  "errors": {}
}
```

**Status Codes:**
- `200` - Success
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

---

## 🔧 Rate Limiting

- **100 requests per hour** per IP
- Response header: `X-RateLimit-Remaining`

---

## 🧪 Testing

### cURL Examples

**Register:**
```bash
curl -X POST https://www.snakkaz.com/api/auth/register.php \
  -H "Content-Type: application/json" \
  -d '{"username":"test","email":"test@test.com","password":"test1234"}'
```

**Login:**
```bash
curl -X POST https://www.snakkaz.com/api/auth/login.php \
  -H "Content-Type: application/json" \
  -d '{"email":"test@test.com","password":"test1234"}'
```

**Get Rooms:**
```bash
curl -X GET https://www.snakkaz.com/api/chat/rooms.php \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

**Last Updated:** November 2025
