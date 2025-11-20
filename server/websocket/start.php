<?php
require __DIR__ . '/../vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use SnakkaZ\WebSocket\ChatServer;

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Starting SnakkaZ WebSocket server...\n";
echo "Listening on 0.0.0.0:8080\n";
echo "Press Ctrl+C to stop\n\n";

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new ChatServer()
        )
    ),
    8080,
    '0.0.0.0'
);

$server->run();
