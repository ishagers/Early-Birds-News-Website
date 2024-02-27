<?php
require("session.php");
//require_once __DIR__ . '/vendor/autoload.php'; // FOR RABBITMQ COMPOSER DEPENDENCIES
//To access Database RabbitMQ Publisher
require('SQLPublish.php');

//use PhpAmqpLib\Connection\AMQPStreamConnection; //Necessary classes to connect with RabbitMQ to
//use PhpAmqpLib\Message\AMQPMessage;             //work with AMQP messages

if (!empty($_POST['new_username']) && !empty($_POST['new_password']) && !empty($_POST['name']) && !empty($_POST['email'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $username = $_POST['new_username'];
    $password = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($password !== $confirmPassword) { //MAKE SURE PASSWORDS ARE THE SAME
        echo "<script>alert('Passwords do not match. Please try again.'); window.location.href='accountCreation.php';</script>";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT); //HASH PASSWORD

        //Setting array and its values to send to RabbitMQ
        $queryValues = array();

        $queryValues['type'] = 'create_account';
        $queryValues['username'] = $username;
        $queryValues['password'] = $hashedPassword;
        $queryValues['name'] = $name;
        $queryValues['email'] = $email;

        //Printing Array and executing SQL Publisher function
        print_r($queryValues);
        $result = publisher($queryValues);

        //If returned 1, it means it was pushed to the database. Otherwise, echo error
        if ($result == 1) {
            echo "Just signed up: ";

            if (isset($_SESSION)) {
                session_destroy();
                session_start();
            } else {
                session_start();
            }

            $_SESSION['username'] = $_POST['username'];
            echo $_SESSION['username'];
            header("Refresh: 2; url=../index.html");
        } else {
            echo $result;
        }

        /* OLD CONNECTION (NOT WORKING)
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
    }*/
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Account</title>
    <link rel="stylesheet" href="../routes/styles.css" />
    <meta content="text/html;charset=utf-8" http-equiv="Content-Type" />
    <meta content="utf-8" http-equiv="encoding" />
</head>
<body>
    <div class="container">
        <div class="title">Create Account</div>
        <form method="post">
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
