#!/usr/bin/env python3
import ftplib
import os
from pathlib import Path

# FTP Configuration
FTP_HOST = "ftp.snakkaz.com"
FTP_USER = "admin@snakkaz.com"
FTP_PASS = "SnakkaZ123!!"
LOCAL_DIR = "/workspaces/SnakkaZ/frontend/dist"
REMOTE_DIR = "/public_html"

def upload_file(ftp, local_path, remote_path):
    """Upload a single file"""
    try:
        with open(local_path, 'rb') as file:
            ftp.storbinary(f'STOR {remote_path}', file)
        print(f"✅ Uploaded: {os.path.basename(local_path)}")
        return True
    except Exception as e:
        print(f"❌ Failed {os.path.basename(local_path)}: {e}")
        return False

def main():
    print("🚀 Deploying SnakkaZ Frontend via FTP...")
    print("=" * 50)
    
    # Connect to FTP
    try:
        ftp = ftplib.FTP(FTP_HOST)
        ftp.login(FTP_USER, FTP_PASS)
        ftp.cwd(REMOTE_DIR)
        print(f"✅ Connected to {FTP_HOST}")
    except Exception as e:
        print(f"❌ Connection failed: {e}")
        return
    
    # Upload index.html
    index_file = os.path.join(LOCAL_DIR, "index.html")
    if os.path.exists(index_file):
        upload_file(ftp, index_file, "index.html")
    
    # Upload vite.svg
    vite_svg = os.path.join(LOCAL_DIR, "vite.svg")
    if os.path.exists(vite_svg):
        upload_file(ftp, vite_svg, "vite.svg")
    
    # Create assets directory
    try:
        ftp.mkd("assets")
    except:
        pass  # Directory might already exist
    
    # Upload assets
    assets_dir = os.path.join(LOCAL_DIR, "assets")
    if os.path.exists(assets_dir):
        ftp.cwd("assets")
        for filename in os.listdir(assets_dir):
            filepath = os.path.join(assets_dir, filename)
            if os.path.isfile(filepath):
                upload_file(ftp, filepath, filename)
        ftp.cwd("..")
    
    ftp.quit()
    print("\n✅ Deployment complete!")
    print("🌐 Visit: https://snakkaz.com")

if __name__ == "__main__":
    main()
