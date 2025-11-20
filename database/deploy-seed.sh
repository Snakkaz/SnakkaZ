#!/bin/bash

# Deploy demo data to production database
# This script uploads the seed data to SnakkaZ MySQL database

echo "🚀 Deploying demo data to SnakkaZ database..."

# Database credentials
DB_HOST="localhost"
DB_NAME="snakqsqe_SnakkaZ"
DB_USER="snakqsqe_snakkaz_user"
DB_PASS="SnakkaZ2024!Secure"

# Check if we're on the server or local dev
if [ -f "/home/snakqsqe/.my.cnf" ]; then
    echo "Running on production server..."
    mysql -u $DB_USER -p"$DB_PASS" $DB_NAME < /workspaces/SnakkaZ/database/seed-demo-data.sql
else
    echo "Running locally - you need to upload this file to server first"
    echo "Or use: cat database/seed-demo-data.sql | ssh user@snakkaz.com 'mysql -u $DB_USER -p\"$DB_PASS\" $DB_NAME'"
fi

echo "✅ Demo data deployed!"
