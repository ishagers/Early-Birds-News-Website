#!/usr/bin/php
<?php

require_once __DIR__ . '/../rabbitmqphp_example/path.inc';
require_once __DIR__ . '/../rabbitmqphp_example/get_host_info.inc';
require_once __DIR__ . '/../rabbitmqphp_example/rabbitMQLib.inc';
require_once __DIR__ . '/../rabbitmqphp_example/databaseFunctions.php'; // Assumes you have a script for DB operations



function processMessage($msg) {
    echo "Processing: {$msg->body}\n";
    $data = json_decode($msg->body, true);
    
    // Validate/process data here. For now, we'll assume it's always valid and simply print it.
    // Example: if valid, save to database
    $saved = saveToDatabase($data); // Implement this function in databaseFunctions.php
    
    if ($saved) {
        echo "Data saved to database\n";
    } else {
        echo "Failed to save data\n";
    }
    
    $msg->ack(); // Acknowledge message processing is complete
}

function saveToDatabase($data) {
    // Placeholder for database save operation
    // You'll implement the actual database interaction here.
    // Returning true to simulate successful save
    return true;
}

$client = new rabbitMQClient($iniFilePath, "testServer");

// Setup consumer
$connection = new AMQPStreamConnection(
    $client->get('BROKER_HOST'),
    $client->get('BROKER_PORT'),
    $client->get('USER'),
    $client->get('PASSWORD'),
    $client->get('VHOST')
);
$channel = $connection->channel();

$channel->queue_declare('testQueue', false, true, false, false);
echo " [*] Waiting for messages. To exit press CTRL+C\n";

$callback = function($msg) {
    processMessage($msg);
};

$channel->basic_consume('testQueue', '', false, false, false, false, $callback);

while ($channel->is_consuming()) {
    $channel->wait();
}

$channel->close();
$connection->close();

