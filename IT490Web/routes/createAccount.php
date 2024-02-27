<?php
require_once __DIR__ . '/vendor/autoload.php'; // FOR RABBITMQ COMPOSER DEPENDENCIES


use PhpAmqpLib\Connection\AMQPStreamConnection; //Necessary classes to connect with RabbitMQ to
use PhpAmqpLib\Message\AMQPMessage;             //work with AMQP messages

if ($_SERVER["REQUEST_METHOD"] == "POST") { //Form submission via POST and retrieves data from form
    $name = $_POST['name'];
    $email = $_POST['email'];
    $username = $_POST['new_username'];
    $password = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($password !== $confirmPassword) { //MAKE SURE PASSWORDS ARE THE SAME
        echo "<script>alert('Passwords do not match. Please try again.'); window.location.href='accountCreation.php';</script>";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT); //HASH PASSWORD

        $connection = new AMQPStreamConnection('10.147.17.178', 5672, 'test', 'test'); //ESTABLISH RABBITMQ CONNECTION
        $channel = $connection->channel(); //OPENS A CHANNEL ON THE CONNECTION FOR COMMUNICATION

        $queueName = 'testQueue';
        $channel->queue_declare($queueName, false, true, false, false); //DECLARES A QUEUE WITH NAME 'testQueue'

        $data = json_encode([ //ENCODE VALUES AS JSON STRING
            'type' => 'create_account', // Specify the action type
            'name' => $name,
            'email' => $email,
            'username' => $username,
            'password' => $hashedPassword,
        ]);
        $msg = new AMQPMessage($data, ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);// WRAP VALUES IT A AMQP MESSAGE

        $channel->basic_publish($msg, '', $queueName); //SEND MESSAGE TO 'testQueue' QUEUE

        // Close the channel and connection
        $channel->close();
        $connection->close();

        echo "<script>alert('Account creation request sent successfully.'); window.location.href='../index.html';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Create Account</title>
    <link rel="stylesheet" href="styles.css" />
</head>
<body>
    <div class="container">
        <div class="title">Create Account</div>
        <form action="createAccount.php" method="post">
            <p>
                <label for="name">Name</label>
                <input type="text" id="name" name="name" required />
            </p>

            <p>
                <label for="email">Email</label>
                <input type="text" id="email" name="email" required />
            </p>

            <p>
                <label for="new_username">Username</label>
                <input type="text" id="new_username" name="new_username" required />
            </p>

            <p>
                <label for="new_password">Password</label>
                <input type="password" id="new_password" name="new_password" required />
            </p>

            <p>
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required />
            </p>

            <p>
                <button type="submit">Create Account</button>
            </p>
        </form>
    </div>
</body>
</html>
