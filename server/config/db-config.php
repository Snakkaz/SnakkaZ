<?php
/**
 * SnakkaZ Database Configuration - PRODUCTION
 * FORCE LOADED: <?= date('Y-m-d H:i:s') ?> 
 */

// DISABLE ALL CACHING
if (function_exists('opcache_invalidate')) {
    opcache_invalidate(__FILE__, true);
}
if (function_exists('opcache_reset')) {
    opcache_reset();
}

// Database Configuration - VERIFIED CORRECT
define('DB_HOST', 'localhost');
define('DB_NAME', 'snakqsqe_SnakkaZ');
define('DB_USER', 'snakqsqe_roun765');  // CORRECT USER!
define('DB_PASS', 'sNAKKAz2025!');      // CORRECT PASSWORD!
define('DB_CHARSET', 'utf8mb4');

// Application Settings
define('APP_NAME', 'SnakkaZ Chat');
define('APP_URL', 'https://snakkaz.com');
define('API_VERSION', '1.0.0');

// Security
define('JWT_SECRET', '964797dd20b381c575536ae35e2d139873ff5a55b14796b5b863172a311f1730');
define('JWT_EXPIRY', 86400);
define('BCRYPT_COST', 12);

// File Upload
define('UPLOAD_DIR', __DIR__ . '/../../uploads/');
define('UPLOAD_MAX_SIZE', 10 * 1024 * 1024);
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'application/pdf']);

// Rate Limiting
define('RATE_LIMIT_REQUESTS', 100);
define('RATE_LIMIT_WINDOW', 3600);

// Timezone
date_default_timezone_set('Europe/Oslo');

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../logs/php_errors.log');
