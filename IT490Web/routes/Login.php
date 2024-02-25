<?php
require_once __DIR__ . '../vendor/autoload.php'; // FOR RABBITMQ COMPOSER DEPENDENCIES

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") { //CHECK FORM SUBMISSION
    $username = $_POST['username'];
    $password = $_POST['password'];

    $connection = new AMQPStreamConnection('10.147.17.178', 15672, 'admin', 'admin'); //ESTABLISH RABBITMQ CONNECTION
    $channel = $connection->channel(); //Opens a channel over the established connection

    $queueName = 'login_requests';
    //*RabbitMQ will ensure the queue exists before attempting to use it.*
    $channel->queue_declare($queueName, false, true, false, false); //Declares queue named 'login_requests'

    //*Important for correlating requests with responses*
    $correlationId = uniqid('login_', true); //UNIQUE IDENTIFIER FOR LOGIN REQUEST.

    $data = json_encode([ //PREP THE MESSAGE IN JSON STRING
        'username' => $username,
        'password' => $password, //PLAINTEXT PASSWORD *NOT HASHED*
    ]);

    $msgProperties = [ //PREPS MESSAGE, CorrelationID for tracking request, reply_to queue for response
        'correlation_id' => $correlationId,
        'reply_to' => 'login_responses', //ASSUMING RESPONSE QUEUE EXISTS
        'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
    ];

    $msg = new AMQPMessage($data, $msgProperties);

    $channel->basic_publish($msg, '', $queueName);//SENDS OUT MESSAGE TO login_requests QUEUE

    // Close the channel and connection
    $channel->close();
    $connection->close();

    // Return the correlation ID to the client for polling
    header('Content-Type: application/json');
    echo json_encode(['correlationId' => $correlationId]);
}
?>
