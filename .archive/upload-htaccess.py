#!/usr/bin/env python3
import ftplib
from io import BytesIO

FTP_HOST = "ftp.snakkaz.com"
FTP_USER = "admin@snakkaz.com"
FTP_PASS = "SnakkaZ123!!"

# .htaccess content for React SPA
htaccess_content = """# Enable rewrite engine
RewriteEngine On

# If an existing asset or directory is requested, serve it
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d

# Otherwise, route all requests to index.html for client-side routing
RewriteRule ^ index.html [L]

# Enable CORS
Header set Access-Control-Allow-Origin "*"
Header set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
Header set Access-Control-Allow-Headers "Content-Type, Authorization"

# Enable compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/json
</IfModule>

# Browser caching
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/html "access plus 0 seconds"
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"
    ExpiresByType image/svg+xml "access plus 1 year"
</IfModule>
"""

ftp = ftplib.FTP(FTP_HOST)
ftp.login(FTP_USER, FTP_PASS)
ftp.cwd("/public_html")

# Upload .htaccess
bio = BytesIO(htaccess_content.encode('utf-8'))
ftp.storbinary('STOR .htaccess', bio)
print("✅ Uploaded .htaccess to /public_html")

ftp.quit()
print("✅ React SPA routing configured!")
