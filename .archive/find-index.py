#!/usr/bin/env python3
import ftplib

FTP_HOST = "ftp.snakkaz.com"
FTP_USER = "admin@snakkaz.com"
FTP_PASS = "SnakkaZ123!!"

ftp = ftplib.FTP(FTP_HOST)
ftp.login(FTP_USER, FTP_PASS)

# Check home directory
print("📁 Files in / (home):")
print("=" * 50)
ftp.cwd("/")
files = []
ftp.retrlines('LIST', files.append)
for f in files:
    if 'index' in f.lower() or 'public' in f.lower():
        print(f)

print("\n📁 Checking for index files:")
try:
    size = ftp.size("/index.html")
    print(f"Found /index.html - {size} bytes")
except:
    print("No /index.html")

try:
    size = ftp.size("/public_html/index.html")  
    print(f"Found /public_html/index.html - {size} bytes")
except:
    print("No /public_html/index.html")

ftp.quit()
