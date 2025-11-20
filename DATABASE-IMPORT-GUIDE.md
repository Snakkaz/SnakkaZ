# 📝 Database Import Guide

## ✅ TRINN-FOR-TRINN

### 1. Kopier SQL-filen
Åpne: `IMPORT-THIS.sql` i VS Code og kopier **ALT** (Ctrl+A, Ctrl+C)

### 2. Gå til phpMyAdmin
Du er allerede der! 👍

### 3. Velg Database
I venstre sidebar:
- Klikk på databasen som starter med `snakqsqe_`
- Sannsynligvis: `snakqsqe_SnakkaZ`

### 4. Import SQL
1. Klikk på **"SQL"** tab øverst (ikke "Import")
2. Lim inn hele SQL-filen i tekstboksen
3. Scroll ned
4. Klikk **"Utfør"** / **"Go"**

### 5. Verifiser
Du skal se:
- ✅ "Query OK" meldinger
- ✅ En success-melding: "✅ Database setup complete!"
- ✅ 11 nye tabeller i venstre sidebar:
  - users
  - sessions
  - rooms
  - room_members
  - messages
  - uploads
  - message_reactions
  - typing_indicators
  - user_settings
  - message_read_receipts
  - (+ 3 views)

### 6. Sjekk Data
Klikk på `rooms` tabellen → Du skal se 5 rooms:
- 👋 General
- 🎲 Random
- 💻 Tech Talk
- 🎮 Gaming
- 🎵 Music

Klikk på `messages` → Du skal se 7 velkomstmeldinger

---

## 🚨 HVIS DET FEILER

### "Table already exists"
Det er OK! Tabellene finnes allerede. SQL-en bruker `CREATE TABLE IF NOT EXISTS`.

### "Foreign key constraint fails"
Kjør denne først for å slette alt:
```sql
DROP TABLE IF EXISTS message_read_receipts;
DROP TABLE IF EXISTS message_reactions;
DROP TABLE IF EXISTS typing_indicators;
DROP TABLE IF EXISTS user_settings;
DROP TABLE IF EXISTS uploads;
DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS room_members;
DROP TABLE IF EXISTS sessions;
DROP TABLE IF EXISTS rooms;
DROP TABLE IF EXISTS users;
```

Deretter lim inn `IMPORT-THIS.sql` igjen.

### "Access denied"
Du må ha riktige rettigheter. Sjekk at du er logget inn som:
- User: `snakqsqe_snakkaz_user` (eller lignende)
- Har tilgang til databasen

---

## 📊 HVA SKJER NÅR DU IMPORTERER?

1. **Lager 11 tabeller** (hvis de ikke finnes)
2. **Lager 1 system bruker** (ID = 1, "SnakkaZ Bot")
3. **Lager 5 demo rooms** (General, Random, etc.)
4. **Lager 7 velkomstmeldinger**
5. **Lager 3 views** for bedre queries
6. **Viser success melding**

---

## ✅ ETTER IMPORT

### Test API
```bash
curl https://snakkaz.com/api/health.php
```

Skal returnere:
```json
{
  "success": true,
  "data": {
    "status": "healthy",
    "database": "connected",
    "timestamp": "2025-11-19T16:00:00Z"
  }
}
```

### Test Rooms API
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  https://snakkaz.com/api/chat/rooms.php
```

Skal returnere 5 rooms (etter du har logget inn og fått token).

---

## 🎯 NESTE STEG

1. ✅ Import SQL (dette steget)
2. Registrer en bruker på https://snakkaz.com
3. Login og se de 5 demo rooms
4. Send en melding i General room
5. Fortsett med Phase 2 features!

---

*Lykke til! 🚀*
