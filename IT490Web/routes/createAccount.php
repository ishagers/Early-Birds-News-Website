<?php
// require_once __DIR__ . '/vendor/autoload.php'; // EDITED OUT, ADJUST PATH

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $username = $_POST['new_username'];
    $password = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    // Simple validation: Ensure passwords match
    if ($password !== $confirmPassword) {
        echo "<script>alert('Passwords do not match. Please try again.');</script>";
    } else {
        // Hash the password before sending it to RabbitMQ
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Assume successful validation
        // Connect to RabbitMQ
        $connection = new AMQPStreamConnection('localhost', 5672, 'admin', 'admin'); // Adjust these values
        $channel = $connection->channel();

        // Declare a queue
        $queueName = 'account_creation';
        $channel->queue_declare($queueName, false, true, false, false);

        // Prepare the message payload with the hashed password
        $data = json_encode([
            'name' => $name,
            'email' => $email,
            'username' => $username,
            'password' => $hashedPassword,
        ]);
        $msg = new AMQPMessage($data, ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);

        // Publish the message
        $channel->basic_publish($msg, '', $queueName);

        // Close the channel and connection
        $channel->close();
        $connection->close();

        echo "<script>alert('Account creation request sent successfully.');</script>";
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
