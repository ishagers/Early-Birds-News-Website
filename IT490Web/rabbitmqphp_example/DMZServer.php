#!/usr/bin/php
<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'vendor/autoload.php';
require_once 'path.inc';
require_once 'get_host_info.inc';
require_once 'rabbitMQLib.inc';

use React\Http\HttpServer;
use React\Http\Message\Response;
use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Factory;
use React\Socket\Server as ReactServer;

$client = new rabbitMQClient("DMZServer.ini", "DMZServer");

$allowedApiUrl = "https://gnews.io/api/v4/search";

function processApiRequest($url) {
    global $client;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    $data = json_decode($result, true);
    curl_close($ch);

    if (!isset($data['articles']) || !is_array($data['articles'])) {
        echo "No articles found or error in API response.\n";
        return;
    }

    foreach ($data['articles'] as $article) {
        $message = json_encode($article);
        $client->publish($message, 'EXCHANGE');  // Assuming 'EXCHANGE' is defined in your RabbitMQ config
    }
}

$loop = Factory::create();
$server = new HttpServer($loop, function (ServerRequestInterface $request) use ($allowedApiUrl) {
    $requestUri = $request->getUri()->getPath();
    if (strpos($requestUri, $allowedApiUrl) === 0) {
        processApiRequest($requestUri);
        return new Response(200, ['Content-Type' => 'text/plain'], 'API request processed');
    }
    return new Response(403, ['Content-Type' => 'text/plain'], 'Access denied');
});

$host = '0.0.0.0'; // Listen on all interfaces
$port = 8000;
$socket = new ReactServer("$host:$port", $loop);
$server->listen($socket);

echo "DMZ Server started at http://$host:$port\n";
$loop->run();

