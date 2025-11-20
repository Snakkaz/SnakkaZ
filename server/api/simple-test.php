<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

echo json_encode([
    'success' => true,
    'message' => 'Simple test works',
    'php_version' => phpversion(),
    'cwd' => getcwd(),
    'dir' => __DIR__
]);
