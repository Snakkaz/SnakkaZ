<?php
/**
 * Quick test to debug rooms.php 500 error
 * Timestamp: <?php echo date('Y-m-d H:i:s'); ?>
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

echo json_encode([
    'timestamp' => date('Y-m-d H:i:s'),
    'message' => 'Test file created at ' . date('H:i:s') . ' - if you see this, cache is working'
]);
