<?php
session_start(); 
require('rabbitmqphp_example/session.php');
require('rabbitmqphp_example/databaseFunctions.php');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require('rabbitmqphp_example/SQLPublish.php');

// Define the generateToken function if it's not elsewhere in your included files
function generateToken($length = 64) {
    return bin2hex(random_bytes($length));
}

if (!empty($_POST['username']) && !empty($_POST['password'])) {
    $queryValues = [
        'type' => 'login',
        'username' => $_POST['username'],
        'password' => $_POST['password'], // Ensure this data is sent over HTTPS
    ];

    $result = publisher($queryValues);

    if ($result && $result['returnCode'] == '0') {
        // Login successful
        echo "Great, we found you: " . htmlspecialchars($result['message']);

        $_SESSION['username'] = $_POST['username'];
        if(isset($result['user_id'])) {
            $_SESSION['user_id'] = $result['user_id']; // Store user_id in session

            // Generate a token for session identification
            $token = generateToken();
            // Store the token in the session or send it to the client
            $_SESSION['token'] = $token;
        }

        header("Location: rabbitmqphp_example/mainMenu.php"); // Redirect to the home page or dashboard
        exit();
    } else {
        // Login failed or result is not properly formatted
        $errorMessage = isset($result['message']) ? $result['message'] : "Login failed. Please try again.";
        echo "<script>alert('" . htmlspecialchars($errorMessage) . "');</script>";
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

