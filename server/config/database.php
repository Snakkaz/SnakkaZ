<?php
/**
 * SnakkaZ Chat - Database Configuration
 * 
 * Update these values with your actual Namecheap cPanel credentials
 */

// Database Configuration
define('DB_HOST', 'localhost');                    // Namecheap shared hosting
define('DB_NAME', 'snakqsqe_SnakkaZ');             // Database name
define('DB_USER', 'cpses_sn151brm8f');             // phpMyAdmin user
define('DB_PASS', 'C1vTRVmuczB1HgiiFPC02aUI6RkwVCLq');  // phpMyAdmin password
define('DB_CHARSET', 'utf8mb4');

// Application Settings
define('APP_NAME', 'SnakkaZ Chat');
define('APP_URL', 'https://snakkaz.com');  // Your actual domain
define('API_VERSION', '1.0.0');

// Security
define('JWT_SECRET', '964797dd20b381c575536ae35e2d139873ff5a55b14796b5b863172a311f1730');
define('JWT_EXPIRY', 86400);  // 24 hours in seconds
define('BCRYPT_COST', 12);    // Password hashing cost

// File Upload
define('UPLOAD_DIR', __DIR__ . '/../../uploads/');
define('UPLOAD_MAX_SIZE', 10 * 1024 * 1024);  // 10MB
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'application/pdf']);

// Rate Limiting
define('RATE_LIMIT_REQUESTS', 100);   // Max requests per window
define('RATE_LIMIT_WINDOW', 3600);    // Window in seconds (1 hour)

// Timezone
date_default_timezone_set('Europe/Oslo');

// Error Reporting (Set to 0 in production!)
error_reporting(E_ALL);
ini_set('display_errors', '0');  // Set to '0' in production
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../logs/php_errors.log');
