#!/usr/bin/env python3
import ftplib
import os

FTP_HOST = "ftp.snakkaz.com"
FTP_USER = "admin@snakkaz.com"
FTP_PASS = "SnakkaZ123!!"
LOCAL_DIR = "/workspaces/SnakkaZ/frontend/dist"

ftp = ftplib.FTP(FTP_HOST)
ftp.login(FTP_USER, FTP_PASS)
ftp.cwd("/public_html")

# Delete old index.html
try:
    ftp.delete("index.html")
    print("✅ Deleted old index.html")
except:
    print("⚠️  No old index.html to delete")

# Upload new index.html
with open(os.path.join(LOCAL_DIR, "index.html"), 'rb') as file:
    ftp.storbinary('STOR index.html', file)
    print("✅ Uploaded new index.html")

# Verify file size
size = ftp.size("index.html")
print(f"📊 File size on server: {size} bytes")

local_size = os.path.getsize(os.path.join(LOCAL_DIR, "index.html"))
print(f"📊 Local file size: {local_size} bytes")

if size == local_size:
    print("✅ Sizes match!")
else:
    print("❌ Size mismatch!")

ftp.quit()
