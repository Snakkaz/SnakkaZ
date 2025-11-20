#!/usr/bin/env python3
"""
Complete deployment script for SnakkaZ
Deploys frontend, backend, and database changes
"""
import ftplib
import os
import sys
from pathlib import Path

# FTP Configuration
FTP_HOST = "ftp.snakkaz.com"
FTP_USER = "admin@snakkaz.com"
FTP_PASS = "SnakkaZ123!!"
FTP_ROOT = "/"

def connect_ftp():
    """Connect to FTP server"""
    print(f"📡 Connecting to {FTP_HOST}...")
    ftp = ftplib.FTP(FTP_HOST)
    ftp.login(FTP_USER, FTP_PASS)
    print(f"✅ Connected as {FTP_USER}")
    return ftp

def upload_file(ftp, local_path, remote_path):
    """Upload a single file"""
    try:
        with open(local_path, 'rb') as f:
            ftp.storbinary(f'STOR {remote_path}', f)
        size = os.path.getsize(local_path)
        print(f"  ✓ {remote_path} ({size:,} bytes)")
        return True
    except Exception as e:
        print(f"  ✗ {remote_path}: {e}")
        return False

def create_remote_dir(ftp, path):
    """Create remote directory if it doesn't exist"""
    try:
        ftp.mkd(path)
        print(f"  📁 Created {path}")
    except:
        pass  # Directory might already exist

def deploy_frontend(ftp):
    """Deploy frontend build"""
    print("\n🎨 Deploying frontend...")
    
    dist_dir = Path("/workspaces/SnakkaZ/frontend/dist")
    if not dist_dir.exists():
        print("❌ Build directory not found. Run 'npm run build' first!")
        return False
    
    # Upload index.html
    upload_file(ftp, dist_dir / "index.html", "/index.html")
    
    # Upload assets
    assets_dir = dist_dir / "assets"
    if assets_dir.exists():
        create_remote_dir(ftp, "/assets")
        for file in assets_dir.iterdir():
            if file.is_file():
                upload_file(ftp, file, f"/assets/{file.name}")
    
    print("✅ Frontend deployed")
    return True

def deploy_backend(ftp):
    """Deploy backend PHP files"""
    print("\n🔧 Deploying backend...")
    
    server_dir = Path("/workspaces/SnakkaZ/server")
    
    # Directories to upload
    dirs_to_upload = [
        ("api", "/api"),
        ("config", "/config"),
        ("utils", "/utils"),
        ("websocket", "/server/websocket")
    ]
    
    for local_dir, remote_dir in dirs_to_upload:
        local_path = server_dir / local_dir
        if not local_path.exists():
            continue
            
        print(f"\n  📁 Uploading {local_dir}/...")
        
        # Create remote directory
        create_remote_dir(ftp, remote_dir)
        
        # Upload all PHP files recursively
        for root, dirs, files in os.walk(local_path):
            # Create subdirectories
            for dir_name in dirs:
                rel_dir = os.path.relpath(os.path.join(root, dir_name), local_path)
                remote_subdir = f"{remote_dir}/{rel_dir}".replace('\\', '/')
                create_remote_dir(ftp, remote_subdir)
            
            # Upload files
            for file_name in files:
                # Upload all file types for websocket, only PHP for others
                if local_dir == "websocket" or file_name.endswith('.php'):
                    local_file = os.path.join(root, file_name)
                    rel_file = os.path.relpath(local_file, local_path)
                    remote_file = f"{remote_dir}/{rel_file}".replace('\\', '/')
                    upload_file(ftp, local_file, remote_file)
    
    print("✅ Backend deployed")
    return True

def deploy_database_seed(ftp):
    """Upload database seed file"""
    print("\n🗄️  Uploading database seed...")
    
    seed_file = Path("/workspaces/SnakkaZ/database/seed-demo-data.sql")
    if seed_file.exists():
        upload_file(ftp, seed_file, "/database/seed-demo-data.sql")
        print("✅ Database seed uploaded")
        print("⚠️  Run this SQL manually in phpMyAdmin!")
    else:
        print("⚠️  Seed file not found")
    
    return True

def main():
    """Main deployment function"""
    print("🚀 SnakkaZ Complete Deployment")
    print("=" * 50)
    
    try:
        ftp = connect_ftp()
        
        # Deploy components
        deploy_frontend(ftp)
        deploy_backend(ftp)
        deploy_database_seed(ftp)
        
        ftp.quit()
        
        print("\n" + "=" * 50)
        print("✅ DEPLOYMENT COMPLETE!")
        print("\n📋 Next steps:")
        print("  1. Run database seed in phpMyAdmin")
        print("  2. Install Composer dependencies:")
        print("     cd ~/public_html/server && composer install")
        print("  3. Start WebSocket server:")
        print("     php ~/public_html/server/websocket/start.php")
        print("\n🌐 Visit: https://snakkaz.com")
        
    except Exception as e:
        print(f"\n❌ Deployment failed: {e}")
        sys.exit(1)

if __name__ == "__main__":
    main()
