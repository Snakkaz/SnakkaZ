# 🚀 SnakkaZ Deployment Guide - Namecheap Hosting

## 📋 Pre-Deployment Checklist

- [ ] cPanel access
- [ ] FTP credentials
- [ ] MySQL database created
- [ ] Domain DNS configured
- [ ] SSL certificate installed (Let's Encrypt)

---

## 🔧 Step-by-Step Deployment

### 1. Setup Database

#### A. Create Database in cPanel
```
1. Log into cPanel
2. Go to "MySQL Databases"
3. Create new database: snakkaz_db
4. Create user: snakkaz_user
5. Add user to database with ALL PRIVILEGES
6. Note the credentials
```

#### B. Import Schema
```
1. Go to phpMyAdmin
2. Select your database (snakkaz_db)
3. Click "Import"
4. Choose file: database/schema.sql
5. Click "Go"
```

---

### 2. Build Frontend

```bash
cd client
npm install
npm run build
```

This creates a `client/dist/` folder with your production files.

---

### 3. Upload Files via FTP

#### Option A: Using FileZilla (Recommended for beginners)

```
1. Download FileZilla: https://filezilla-project.org/

2. Connect:
   - Host: ftp.snakkaz.com (or your FTP hostname)
   - Username: [your cPanel username]
   - Password: [your cPanel password]
   - Port: 21

3. Upload Structure:
   Local                           →  Remote
   ├── client/dist/*               →  /public_html/
   ├── server/*                    →  /public_html/api/
   ├── deployment/.htaccess        →  /public_html/.htaccess
   └── server/uploads/             →  /public_html/uploads/
```

#### Option B: Using Deployment Script

```bash
# Update FTP credentials in deployment/deploy.sh
nano deployment/deploy.sh

# Run deployment
./deployment/deploy.sh
```

#### Option C: Using cPanel File Manager

```
1. Log into cPanel
2. Go to "File Manager"
3. Navigate to public_html
4. Click "Upload"
5. Upload files manually
```

---

### 4. Configure Backend

#### Update Database Credentials

```bash
# Edit: public_html/api/config/database.php
# Via FTP or cPanel File Manager

<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'snakkaz_db');           # Your database name
define('DB_USER', 'snakkaz_user');          # Your database user
define('DB_PASS', 'your_secure_password');  # Your database password
define('DB_CHARSET', 'utf8mb4');
?>
```

---

### 5. Set Permissions

```bash
# Via SSH or cPanel Terminal

# Make uploads directory writable
chmod 755 /home/username/public_html/uploads

# Protect config files
chmod 644 /home/username/public_html/api/config/*.php

# Make logs directory
mkdir -p /home/username/public_html/logs
chmod 755 /home/username/public_html/logs
```

---

### 6. Configure .htaccess

The `.htaccess` file should already be uploaded. Verify:

```apache
# Location: public_html/.htaccess

# Force HTTPS
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# API Routes
RewriteCond %{REQUEST_URI} ^/api/
RewriteRule ^api/(.*)$ api/$1 [L]

# Frontend Routes (React Router)
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.html [L]
```

---

### 7. SSL Certificate (HTTPS)

#### Via cPanel (Free Let's Encrypt)

```
1. Log into cPanel
2. Go to "SSL/TLS Status"
3. Find your domain (snakkaz.com)
4. Click "Run AutoSSL"
5. Wait for installation (5-10 minutes)
6. Verify: https://www.snakkaz.com
```

---

### 8. Test Deployment

#### A. Frontend Test
```
Visit: https://www.snakkaz.com
Expected: React app loads with login page
```

#### B. API Test
```bash
# Test database connection
curl https://www.snakkaz.com/api/health.php

# Expected response:
{
  "status": "ok",
  "database": "connected",
  "timestamp": "2025-11-19 12:00:00"
}
```

#### C. Registration Test
```
1. Go to: https://www.snakkaz.com/register
2. Create test account
3. Verify you can login
4. Check database in phpMyAdmin
```

---

## 🔍 Troubleshooting

### Problem: White screen (500 error)

```bash
# Check PHP error log
tail -f /home/username/public_html/logs/php_errors.log

# Common causes:
- Wrong file permissions
- Missing PHP extensions
- Syntax errors in PHP files
```

### Problem: Database connection failed

```bash
# Verify credentials in:
public_html/api/config/database.php

# Test MySQL connection via SSH:
mysql -u snakkaz_user -p snakkaz_db
```

### Problem: API endpoints return 404

```bash
# Check .htaccess is uploaded
# Check mod_rewrite is enabled (usually is on shared hosting)

# Test direct access:
https://www.snakkaz.com/api/health.php
```

### Problem: File uploads not working

```bash
# Check directory exists and is writable
mkdir -p /home/username/public_html/uploads
chmod 755 /home/username/public_html/uploads

# Check PHP upload settings in .htaccess
php_value upload_max_filesize 10M
php_value post_max_size 10M
```

---

## 📊 Post-Deployment Monitoring

### Check Logs Regularly

```bash
# PHP errors
tail -f /home/username/public_html/logs/php_errors.log

# Apache errors (if accessible)
tail -f /var/log/apache2/error.log

# MySQL slow queries
# Via cPanel -> MySQL Databases -> Check Slow Query Log
```

### Performance Monitoring

```
1. Use Google PageSpeed Insights:
   https://pagespeed.web.dev/
   
2. Check loading times with browser DevTools

3. Monitor database size in phpMyAdmin

4. Check disk usage in cPanel
```

---

## 🔄 Updates & Maintenance

### Deploy Updates

```bash
# 1. Make changes locally
# 2. Build frontend
cd client
npm run build

# 3. Upload only changed files via FTP
# Or run full deployment:
./deployment/deploy.sh
```

### Database Migrations

```bash
# 1. Create migration file
database/migrations/002_add_user_preferences.sql

# 2. Import via phpMyAdmin
# Or via SSH:
mysql -u snakkaz_user -p snakkaz_db < database/migrations/002_add_user_preferences.sql
```

### Backup Strategy

```
1. Database Backup (Weekly):
   - cPanel -> Backup -> Download MySQL Database
   
2. Files Backup (Monthly):
   - cPanel -> Backup -> Download Home Directory
   
3. Automated Backups:
   - Enable cPanel automatic backups (if available)
```

---

## 🆘 Emergency Rollback

If something goes wrong:

```bash
# 1. Restore from FTP backup
# Download previous version from local git

# 2. Restore database
# Import previous SQL dump via phpMyAdmin

# 3. Clear cache
# Delete files in /tmp/ directory via cPanel
```

---

## ✅ Production Checklist

- [ ] SSL certificate installed
- [ ] Database imported and configured
- [ ] All files uploaded correctly
- [ ] File permissions set
- [ ] .htaccess configured
- [ ] Error logging enabled
- [ ] Test registration/login
- [ ] Test chat functionality
- [ ] Test file uploads
- [ ] Performance optimization (Gzip, caching)
- [ ] Backup strategy in place
- [ ] Monitoring setup

---

## 📞 Support

**Issues?** Check common problems above or:
- Review server error logs
- Check cPanel error messages
- Verify all credentials
- Test API endpoints individually

**Need Help?**
- GitHub Issues: https://github.com/Snakkaz/SnakkaZ/issues
- Email: support@snakkaz.com

---

**Last Updated:** November 2025  
**Version:** 1.0.0
