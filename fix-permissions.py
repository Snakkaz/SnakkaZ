#!/usr/bin/env python3
import ftplib

FTP_HOST = "ftp.snakkaz.com"
FTP_USER = "admin@snakkaz.com"
FTP_PASS = "SnakkaZ123!!"

ftp = ftplib.FTP(FTP_HOST)
ftp.login(FTP_USER, FTP_PASS)
ftp.cwd("/public_html")

# Check permissions
files = []
ftp.retrlines('LIST', files.append)
for f in files:
    if 'index' in f or 'assets' in f or '.htaccess' in f:
        print(f)

# Try to set permissions on index.html
try:
    ftp.sendcmd('SITE CHMOD 644 index.html')
    print("\n✅ Set permissions 644 on index.html")
except Exception as e:
    print(f"\n⚠️  Could not set permissions: {e}")

try:
    ftp.sendcmd('SITE CHMOD 755 assets')
    print("✅ Set permissions 755 on assets/")
except Exception as e:
    print(f"⚠️  Could not set permissions on assets: {e}")

ftp.quit()
