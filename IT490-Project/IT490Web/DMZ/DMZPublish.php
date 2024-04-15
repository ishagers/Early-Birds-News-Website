#!/usr/bin/php
<?php

require_once __DIR__ . '/../rabbitmqphp_example/path.inc';
require_once __DIR__ . '/../rabbitmqphp_example/get_host_info.inc';
require_once __DIR__ . '/../rabbitmqphp_example/rabbitMQLib.inc';

$iniFilePath = __DIR__ . '/DMZServer.ini';

function publishToQueue($message)
{
    global $iniFilePath; // Use the global INI file path

    // Load configuration
    $config = parse_ini_file($iniFilePath, true)['DMZServer'];
    if (!$config) {
        echo "Failed to load configuration from {$iniFilePath}\n";
        return;
    }

    // TLS/SSL Options
    $ssl_options = [
        'cafile' => __DIR__ . '/../rabbitmqphp_example/SSL/ca_cert.pem',
        'local_cert' => __DIR__ . '/../rabbitmqphp_example/SSL/server_cert.pem',
        'local_key' => __DIR__ . '/../rabbitmqphp_example/SSL/server_key.pem',
        'verify_peer' => true,
    ];

    try {
        // Use AMQPSSLConnection for a TLS/SSL connection
        $connection = new AMQPSSLConnection(
            $config['BROKER_HOST'],
            $config['BROKER_PORT'],
            $config['USER'],
            $config['PASSWORD'],
            $config['VHOST'],
            $ssl_options
        );
    } catch (Exception $e) {
        echo "Connection failed: ",  $e->getMessage(), "\n";
        return;
    }

    $channel = $connection->channel();

    // Ensure the queue exists before trying to publish
    $channel->queue_declare($config['QUEUE'], false, true, false, $config['AUTO_DELETE']);

    // Prepare and publish the message
    $amqpMessage = new AMQPMessage($message, ['delivery_mode' => 2]); // Marking message as persistent
    $channel->basic_publish($amqpMessage, '', $config['QUEUE']);

    echo " [x] Sent ", $message, "\n";

    // Clean up
    $channel->close();
    $connection->close();
}

// Example of what rabbit server sees as the command.
$command = ['command' => 'fetchNews'];
publishToQueue(json_encode($command));

