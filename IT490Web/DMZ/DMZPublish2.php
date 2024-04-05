#!/usr/bin/php
<?php

require_once __DIR__ . '/../rabbitmqphp_example/path.inc';
require_once __DIR__ . '/../rabbitmqphp_example/get_host_info.inc';
require_once __DIR__ . '/../rabbitmqphp_example/rabbitMQLib.inc';

$iniFilePath = __DIR__ . '/DMZServer.ini'; // Path adjusted for DMZServer.ini

function publishToQueue($message, $iniFilePath) {
    $client = new rabbitMQClient($iniFilePath, "DMZServer");
    $response = $client->publish($message, $client->get('EXCHANGE'));
    echo " [x] Sent ", $message, "\n";
}

// Simulate fetching data
$data = ["title" => "Example Title", "content" => "Example Content", "author" => "Author Name"];
// Publish fetched data to the queue
publishToQueue(json_encode($data), $iniFilePath);

