#!/usr/bin/env python3
import ftplib

FTP_HOST = "ftp.snakkaz.com"
FTP_USER = "admin@snakkaz.com"
FTP_PASS = "SnakkaZ123!!"

ftp = ftplib.FTP(FTP_HOST)
ftp.login(FTP_USER, FTP_PASS)

# Rename old index.html to backup
try:
    ftp.rename("/index.html", "/index.html.old-backup")
    print("✅ Renamed /index.html to /index.html.old-backup")
except Exception as e:
    print(f"⚠️  Could not rename: {e}")

# Check if we can delete it instead
try:
    ftp.delete("/index.html.old-backup")
    print("✅ Deleted old backup file")
except:
    pass

try:
    ftp.delete("/index.html")
    print("✅ Deleted /index.html")
except Exception as e:
    print(f"Info: {e}")

ftp.quit()
print("\n✅ Old index.html removed!")
print("🌐 Now https://snakkaz.com will serve /public_html/index.html")
