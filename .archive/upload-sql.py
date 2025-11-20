#!/usr/bin/env python3
"""Upload SQL seed file to server and guide user through phpMyAdmin import"""
import ftplib

# FTP Configuration
FTP_HOST = "ftp.snakkaz.com"
FTP_USER = "admin@snakkaz.com"
FTP_PASS = "SnakkaZ123!!"

print("🗄️  Uploading SQL seed file to server...")
print("=" * 60)

try:
    # Connect
    ftp = ftplib.FTP(FTP_HOST)
    ftp.login(FTP_USER, FTP_PASS)
    print(f"✅ Connected to {FTP_HOST}")
    
    # Upload seed file
    local_file = "/workspaces/SnakkaZ/database/seed-demo-data.sql"
    remote_file = "/public_html/seed-demo-data.sql"
    
    with open(local_file, 'rb') as f:
        ftp.storbinary(f'STOR {remote_file}', f)
    
    print(f"✅ Uploaded: {remote_file}")
    ftp.quit()
    
    print("\n" + "=" * 60)
    print("📋 NEXT STEPS:")
    print("=" * 60)
    print("\n1. Open phpMyAdmin:")
    print("   https://snakkaz.com/phpmyadmin")
    print("\n2. Login with:")
    print("   User: snakqsqe_snakkaz_user")
    print("   Pass: SnakkaZ2024!Secure")
    print("\n3. Select database: snakqsqe_SnakkaZ")
    print("\n4. Click 'Import' tab")
    print("\n5. Choose file from server:")
    print(f"   {remote_file}")
    print("\n6. Click 'Go' button")
    print("\n7. You should see:")
    print("   - 5 demo rooms created")
    print("   - 11 tables total")
    print("   - Indexes added")
    print("   - Success message")
    print("\n✅ Done! Database will be seeded with:")
    print("   - General, Random, Tech Talk, Gaming, Music rooms")
    print("   - Welcome messages")
    print("   - New tables for reactions, uploads, settings")
    print("=" * 60)
    
except Exception as e:
    print(f"❌ Error: {e}")
    print("\n💡 Alternative: Copy SQL manually")
    print("   1. Open: /workspaces/SnakkaZ/database/seed-demo-data.sql")
    print("   2. Copy all content")
    print("   3. Paste in phpMyAdmin SQL tab")
    print("   4. Click 'Go'")
