<?php
session_start(); 
require('rabbitmqphp_example/session.php');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require('rabbitmqphp_example/SQLPublish.php');

if (!empty($_POST['username']) && !empty($_POST['password'])) {
    $queryValues = [
        'type' => 'login',
        'username' => $_POST['username'],
        'password' => $_POST['password'], // Ensure this data is sent over HTTPS
    ];

    $result = publisher($queryValues);
    error_log("Publisher result: " . print_r($result, true));
	if ($result && $result['returnCode'] == '0') {
	    // Login successful
	    $_SESSION['username'] = $_POST['username'];
	    $_SESSION['user_id'] = $result['user_id'];
	    if (headers_sent()) {
		    die('Headers already sent');
		}
	    header("Location: rabbitmqphp_example/mainMenu.php");
	    exit();
	} else {
	    // Login failed or user ID not set in response
	    $errorMessage = isset($result['message']) ? $result['message'] : "Login failed. Please try again.";
	    error_log("Login failed or no user ID: " . print_r($result, true));
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

