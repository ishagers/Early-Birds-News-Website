#!/usr/bin/php
<?php
require_once __DIR__ . '/../rabbitmqphp_example/vendor/autoload.php';  // Make sure this path is correct

use PhpAmqpLib\Connection\AMQPStreamConnection;

require_once __DIR__ . '/../rabbitmqphp_example/path.inc';
require_once __DIR__ . '/../rabbitmqphp_example/get_host_info.inc';
require_once __DIR__ . '/../rabbitmqphp_example/rabbitMQLib.inc';
require_once __DIR__ . '/../rabbitmqphp_example/databaseFunctions.php';
use RabbitMQApp\rabbitMQClient;
$iniFilePath = __DIR__ . '/DMZServer.ini'; // Ensure this path is correct



$client = new rabbitMQClient($iniFilePath, "DMZServer");


function processMessage($msg) {
    echo "Processing: {$msg->body}\n";
    $data = json_decode($msg->body, true);

    if (!isset($data['title']) || !isset($data['content'])) {
        echo "Invalid message format. Required fields missing.\n";
        return;
    }

    $title = $data['title'];
    $content = $data['content'];
    $source = $data['source'] ?? 'user'; // Assume 'user' if not provided
    $url = $data['url'] ?? null; // Default to null if not provided

    // Decide which function to use based on the source
    if ($source === 'api') {
        // Handle API sourced articles
        $response = saveApiArticle($title, $content, $source, $url);
    } else {
        // Handle articles from internal users
        $author = $data['author'] ?? 'defaultAuthor'; // Default author if not provided
        $response = createArticle($title, $content, $author, $source, $url);
    }

    if ($response['status']) {
        echo "Article saved to database\n";
    } else {
        echo "Failed to save article: " . $response['message'] . "\n";
    }

    $msg->ack(); // Acknowledge message processing is complete
}

$client = new rabbitMQClient($iniFilePath, "DMZServer");

// Setup consumer
$connection = new AMQPStreamConnection(
    $client->get('BROKER_HOST'),
    $client->get('BROKER_PORT'),
    $client->get('USER'),
    $client->get('PASSWORD'),
    $client->get('VHOST')
);

$channel = $connection->channel();

$queueName = $client->get('QUEUE');
$autoDelete = strtolower($client->get('AUTO_DELETE')) === 'true' ? true : false;

$channel->queue_declare($queueName, false, true, false, $autoDelete);

echo " [*] Waiting for messages. To exit press CTRL+C\n";

$callback = function($msg) {
    processMessage($msg);
};

$channel->basic_consume($queueName, '', false, false, false, false, $callback);

while ($channel->is_consuming()) {
    $channel->wait();
}

$channel->close();
$connection->close();

?>

