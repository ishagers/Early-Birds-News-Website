#!/usr/bin/php
<?php

require_once 'path.inc';
require_once 'get_host_info.inc';
require_once 'rabbitMQLib.inc';

$iniFilePath = __DIR__ . DIRECTORY_SEPARATOR . 'DMZServer.ini';

function publishToQueue($message) {
    global $iniFilePath;

    // Check if the configuration file exists
    if (!file_exists($iniFilePath)) {
        echo "Configuration file does not exist at {$iniFilePath}\n";
        return;
    }

    // Load configuration
    $config = parse_ini_file($iniFilePath, true);
    if (!$config || !isset($config['DMZServer'])) {
        echo "Failed to load configuration from {$iniFilePath}\n";
        return;
    }
    $config = $config['DMZServer'];

    // SSL options
    $ssl_options = [
        'cafile' => __DIR__ . DIRECTORY_SEPARATOR . 'SSL' . DIRECTORY_SEPARATOR . 'ca_cert.pem',
        'local_cert' => __DIR__ . DIRECTORY_SEPARATOR . 'SSL' . DIRECTORY_SEPARATOR . 'server_cert.pem',
        'local_key' => __DIR__ . DIRECTORY_SEPARATOR . 'SSL' . DIRECTORY_SEPARATOR . 'server_key.pem',
        'verify_peer' => true,
    ];

    // Establish AMQP SSL Connection
    try {
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

    // Create and configure channel
    $channel = $connection->channel();
    $channel->queue_declare($config['QUEUE'], false, true, false, false);
    
    // Prepare and publish message
    $amqpMessage = new AMQPMessage($message, ['delivery_mode' => 2]); // Mark message as persistent
    $channel->basic_publish($amqpMessage, '', $config['QUEUE']);

    echo " [x] Sent ", $message, "\n";

    // Cleanup
    $channel->close();
    $connection->close();
}

$command = ['command' => 'fetchNews'];
publishToQueue(json_encode($command));

