<?php
require once_DIR . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$host = '10.147.17.178'; // RabbitMQ server IP or hostname
$port = 5672; // Default RabbitMQ port
$user = 'test';
$password = 'test';
$queue = 'testQueue';

$mysqlHost = '10.147.17.233'; // MySQL server IP or hostname
$mysqlDb = 'EARLYBIRD';
$mysqlUser = 'IT490DB';
$mysqlPassword = 'IT490DB';

try {
    // Connect to RabbitMQ
    $connection = new AMQPStreamConnection($host, $port, $user, $password);
    $channel = $connection->channel();

    $channel->queue_declare($queue, false, true, false, false);

    echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";

    $callback = function($msg) use ($mysqlHost, $mysqlDb, $mysqlUser, $mysqlPassword) {
        echo " [x] Received ", $msg->body, "\n";
        $data = json_decode($msg->body, true); // Assuming the message is JSON encoded

        // Connect to MySQL
        $pdo = new PDO("mysql:host=$mysqlHost;dbname=$mysqlDb", $mysqlUser, $mysqlPassword);
        $sql = "INSERT INTO users (name, email, username, hash) VALUES (:value1, :value2, :value3, :value4)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':value1' => $data['value1'], ':value2' => $data['value2']]);

        echo " [x] Data inserted to MySQL", "\n";
    };

    $channel->basic_consume($queue, '', false, true, false, false, $callback);

    while ($channel->is_consuming()) {
        $channel->wait();
    }

    $channel->close();
    $connection->close();
} catch (Exception $e) {
    echo 'Error: ', $e->getMessage(), "\n";
}
?>