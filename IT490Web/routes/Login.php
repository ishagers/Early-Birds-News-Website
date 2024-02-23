<?php
require_once __DIR__ . '/vendor/autoload.php'; // Adjust the path as necessary

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Start the session
session_start();

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password']; 

    // Connect to RabbitMQ
    $connection = new AMQPStreamConnection('localhost', 5672, 'user', 'password'); // Adjust these values
    $channel = $connection->channel();

    // Declare a queue for login requests
    $queueName = 'login_requests';
    $channel->queue_declare($queueName, false, true, false, false);

    // Generate a unique correlation ID
    $correlationId = uniqid('login_', true);

    // Prepare the message payload
    $data = json_encode([
        'username' => $username,
        'password' => $password, // Send plaintext password
    ]);

    // Set message properties, including the correlation ID
    $msgProperties = [
        'correlation_id' => $correlationId,
        'reply_to' => 'login_responses', // Assuming you have a response queue
        'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
    ];
    $msg = new AMQPMessage($data, $msgProperties);

    // Publish the message
    $channel->basic_publish($msg, '', $queueName);

    // Close the channel and connection
    $channel->close();
    $connection->close();

    // Return the correlation ID to the client for polling
    header('Content-Type: application/json');
    echo json_encode(['correlationId' => $correlationId]);
}
?>
