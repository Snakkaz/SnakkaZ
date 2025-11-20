#!/usr/bin/env python3
import ftplib
import os
from pathlib import Path

# FTP Configuration
FTP_HOST = "ftp.snakkaz.com"
FTP_USER = "admin@snakkaz.com"
FTP_PASS = "SnakkaZ123!!"
FRONTEND_DIR = "/workspaces/SnakkaZ/frontend/dist"
BACKEND_DIR = "/workspaces/SnakkaZ/server"

def upload_file(ftp, local_path, remote_path):
    """Upload a single file"""
    try:
        with open(local_path, 'rb') as file:
            ftp.storbinary(f'STOR {remote_path}', file)
        print(f"✅ Uploaded: {remote_path}")
        return True
    except Exception as e:
        print(f"❌ Failed {remote_path}: {e}")
        return False

def ensure_dir(ftp, path):
    """Ensure directory exists"""
    try:
        ftp.mkd(path)
    except:
        pass

def main():
    print("🚀 Deploying SnakkaZ (Frontend + Backend) via FTP...")
    print("=" * 50)
    
    # Connect to FTP
    try:
        ftp = ftplib.FTP(FTP_HOST)
        ftp.login(FTP_USER, FTP_PASS)
        print(f"✅ Connected to {FTP_HOST}")
    except Exception as e:
        print(f"❌ Connection failed: {e}")
        return
    
    # === FRONTEND ===
    print("\n📁 Deploying Frontend...")
    ftp.cwd("/")
    
    # Upload index.html
    index_file = os.path.join(FRONTEND_DIR, "index.html")
    if os.path.exists(index_file):
        upload_file(ftp, index_file, "index.html")
    
    # Upload vite.svg
    vite_svg = os.path.join(FRONTEND_DIR, "vite.svg")
    if os.path.exists(vite_svg):
        upload_file(ftp, vite_svg, "vite.svg")
    
    # Upload assets
    ensure_dir(ftp, "assets")
    assets_dir = os.path.join(FRONTEND_DIR, "assets")
    if os.path.exists(assets_dir):
        ftp.cwd("assets")
        for filename in os.listdir(assets_dir):
            filepath = os.path.join(assets_dir, filename)
            if os.path.isfile(filepath):
                upload_file(ftp, filepath, filename)
        ftp.cwd("/")
    
    # === BACKEND ===
    print("\n📁 Deploying Backend...")
    
    # Create backend directory structure
    ensure_dir(ftp, "api")
    ensure_dir(ftp, "api/auth")
    ensure_dir(ftp, "api/chat")
    ensure_dir(ftp, "api/realtime")
    ensure_dir(ftp, "api/user")
    ensure_dir(ftp, "utils")
    ensure_dir(ftp, "config")
    
    # Upload config
    config_files = ['database.php']
    for filename in config_files:
        local_path = os.path.join(BACKEND_DIR, 'config', filename)
        if os.path.exists(local_path):
            ftp.cwd("/config")
            upload_file(ftp, local_path, filename)
    
    # Upload utils
    utils_files = ['Database.php', 'Auth.php', 'Response.php']
    for filename in utils_files:
        local_path = os.path.join(BACKEND_DIR, 'utils', filename)
        if os.path.exists(local_path):
            ftp.cwd("/utils")
            upload_file(ftp, local_path, filename)
    
    # Upload API files
    api_files = {
        'auth': ['login.php', 'register.php', 'logout.php'],
        'chat': ['rooms.php', 'messages.php', 'send.php', 'create-room.php', 'join-room.php', 'reactions.php'],
        'realtime': ['poll.php', 'typing.php'],
        'user': ['profile.php', 'settings.php']
    }
    
    for folder, files in api_files.items():
        for filename in files:
            local_path = os.path.join(BACKEND_DIR, 'api', folder, filename)
            if os.path.exists(local_path):
                ftp.cwd(f"/api/{folder}")
                upload_file(ftp, local_path, filename)
    
    ftp.quit()
    print("\n✅ Full Deployment complete!")
    print("🌐 Frontend: https://snakkaz.com")
    print("🔧 Backend: https://snakkaz.com/api/")

if __name__ == "__main__":
    main()
