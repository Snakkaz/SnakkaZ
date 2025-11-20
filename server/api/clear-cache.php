<?php
header('Content-Type: text/plain');

if (function_exists('opcache_reset')) {
    if (opcache_reset()) {
        echo "✅ OPcache cleared successfully!\n";
    } else {
        echo "❌ Failed to clear OPcache\n";
    }
    
    echo "\nOPcache status:\n";
    $status = opcache_get_status();
    echo "Enabled: " . ($status !== false ? "YES" : "NO") . "\n";
    if ($status) {
        echo "Cached scripts: " . $status['opcache_statistics']['num_cached_scripts'] . "\n";
        echo "Hits: " . $status['opcache_statistics']['hits'] . "\n";
        echo "Misses: " . $status['opcache_statistics']['misses'] . "\n";
    }
} else {
    echo "❌ opcache_reset() function not available\n";
}

echo "\nTimestamp: " . date('Y-m-d H:i:s') . "\n";
