<?php

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use MyApp\Chat;  // Correct namespace based on your composer.json

require __DIR__ . '/../../vendor/autoload.php'; // Correct path to the autoload.php file

$chatServer = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Chat()
        )
    ),
    8080
);

$chatServer->run();

