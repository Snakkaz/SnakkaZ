#!/usr/bin/env python3
import ftplib

FTP_HOST = "ftp.snakkaz.com"
FTP_USER = "admin@snakkaz.com"
FTP_PASS = "SnakkaZ123!!"

ftp = ftplib.FTP(FTP_HOST)
ftp.login(FTP_USER, FTP_PASS)
ftp.cwd("/public_html")

print("📁 Files in /public_html:")
print("=" * 50)
files = []
ftp.retrlines('LIST', files.append)
for f in files:
    print(f)

ftp.quit()
