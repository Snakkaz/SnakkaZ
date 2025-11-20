#!/usr/bin/env python3
"""
Run database migration via HTTP request to phpMyAdmin or custom endpoint
"""
import requests
import os

# Read migration SQL
migration_file = '/workspaces/SnakkaZ/database/migrations/001_add_room_privacy.sql'

with open(migration_file, 'r') as f:
    sql_content = f.read()

print("📋 Migration SQL Content:")
print("=" * 60)
print(sql_content)
print("=" * 60)

print("\n⚠️  Manual Migration Required!")
print("\nPlease run this SQL in phpMyAdmin:")
print(f"https://premium123.premium123.dnssecure.xyz:2083/cpsess{os.getenv('SESSION', 'XXXXXXX')}/3rdparty/phpMyAdmin/")
print("\nOr copy-paste the SQL above into phpMyAdmin SQL tab")

# Alternative: Create a PHP script to run the migration
php_migration_script = """<?php
// Migration Runner - Run via browser: https://snakkaz.com/migrate.php
require_once 'server/config/database.php';

$sql = <<<'SQL'
""" + sql_content + """
SQL;

$db = Database::getInstance();
$statements = array_filter(array_map('trim', explode(';', $sql)));

echo "<h2>Running Migration: 001_add_room_privacy.sql</h2>";
echo "<pre>";

foreach ($statements as $statement) {
    if (empty($statement)) continue;
    
    try {
        $db->execute($statement);
        echo "✅ " . substr($statement, 0, 100) . "...\\n";
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\\n";
        echo "   SQL: " . substr($statement, 0, 200) . "...\\n";
    }
}

echo "</pre>";
echo "<h3>Migration Complete!</h3>";
?>
"""

# Save PHP migration script
php_file = '/workspaces/SnakkaZ/migrate.php'
with open(php_file, 'w') as f:
    f.write(php_migration_script)

print(f"\n✅ Created migration script: {php_file}")
print("\nTo run migration:")
print("1. Deploy migrate.php to server")
print("2. Visit: https://snakkaz.com/migrate.php")
print("3. Delete migrate.php after running for security")
