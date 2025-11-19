#!/usr/bin/env python3
import ftplib
import os

FTP_HOST = "ftp.snakkaz.com"
FTP_USER = "admin@snakkaz.com"
FTP_PASS = "SnakkaZ123!!"
LOCAL_DIR = "/workspaces/SnakkaZ/frontend/dist"

ftp = ftplib.FTP(FTP_HOST)
ftp.login(FTP_USER, FTP_PASS)

# Upload index.html to root (/)
ftp.cwd("/")
with open(os.path.join(LOCAL_DIR, "index.html"), 'rb') as file:
    ftp.storbinary('STOR index.html', file)
print("✅ Uploaded index.html to /")

# Upload assets to root
try:
    ftp.mkd("assets")
except:
    pass
ftp.cwd("assets")
assets_dir = os.path.join(LOCAL_DIR, "assets")
for filename in os.listdir(assets_dir):
    filepath = os.path.join(assets_dir, filename)
    if os.path.isfile(filepath):
        with open(filepath, 'rb') as file:
            ftp.storbinary(f'STOR {filename}', file)
        print(f"✅ Uploaded assets/{filename}")

ftp.quit()
print("\n✅ Files uploaded to root directory!")
