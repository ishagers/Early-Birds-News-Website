<?php
session_start();  // Start the session at the very beginning

require('rabbitmqphp_example/session.php');  // Assuming this includes session-related utility functions

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);  // Good for debugging, consider turning off in production

require('rabbitmqphp_example/SQLPublish.php');  // Assuming this handles communication with your RabbitMQ server

// Processing login only if the correct POST variables are received
if (!empty($_POST['username']) && !empty($_POST['password'])) {
    $queryValues = [
        'type' => 'login',
        'username' => $_POST['username'],
        'password' => $_POST['password'], // Comment about HTTPS suggests this should be secure
    ];

    $result = publisher($queryValues);
    error_log("Publisher result: " . print_r($result, true));  // Good for debugging

    // Ensure the response contains 'returnCode' and 'user_id'
    if ($result && $result['returnCode'] == '0' && isset($result['user_id'])) {
        $_SESSION['username'] = $_POST['username'];
        $_SESSION['user_id'] = $result['user_id'];

        // Redirect to main menu if headers not already sent
        if (!headers_sent()) {
            header("Location: rabbitmqphp_example/mainMenu.php");
            exit();
        } else {
            die('Error: Headers already sent, cannot redirect');  // Better error handling
        }
    } else {
        $errorMessage = isset($result['message']) ? $result['message'] : "Login failed. Please try again.";
        error_log("Login failed or no user ID: " . print_r($result, true));  // Debugging log
        echo "<script>alert('" . htmlspecialchars($errorMessage) . "');</script>";  // Show error to user
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Early Bird</title>
    <link rel="stylesheet" href="routes/styles.css" />
</head>
<body>
    <div class="container">
        <div class="title">Early Bird News Log In</div>
        <form method="post">
            <p>
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required />
            </p>
            <p>
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required />
            </p>
            <p>
                <button type="submit">Login</button>
            </p>
            <p>
                <a href="rabbitmqphp_example/createAccount.php">Create Account</a>
            </p>
        </form>
    </div>
</body>
</html>

