#!/usr/bin/env python3
import ftplib
from io import BytesIO

FTP_HOST = "ftp.snakkaz.com"
FTP_USER = "admin@snakkaz.com"
FTP_PASS = "SnakkaZ123!!"

# Create test.php to see what directory we're in
test_php = """<?php
echo "Current directory: " . getcwd() . "\\n";
echo "Files:\\n";
print_r(scandir('.'));
?>"""

ftp = ftplib.FTP(FTP_HOST)
ftp.login(FTP_USER, FTP_PASS)
ftp.cwd("/public_html")

bio = BytesIO(test_php.encode('utf-8'))
ftp.storbinary('STOR test.php', bio)
print("✅ Uploaded test.php")

ftp.quit()
print("🌐 Visit: https://snakkaz.com/test.php")
