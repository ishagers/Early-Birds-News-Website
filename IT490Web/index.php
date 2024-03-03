<?php
require('session.php');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require('rabbitmqphp_example/SQLPublish.php');

if (!empty($_POST['username']) && !empty($_POST['password'])) {
    $queryValues = [
        'type' => 'login',
        'username' => $_POST['username'],
        'password' => $_POST['password'], // Send plaintext password to be hashed and verified on the server
    ];

    $result = publisher($queryValues);

    if ($result['returnCode'] == '0') { // Assuming '0' means success
        // Login successful
        echo "Great, we found you: " . htmlspecialchars($result['message']);

        if (!isset($_SESSION)) {
            session_start();
        }

        $_SESSION['username'] = $_POST['username'];
        header("Location: routes/mainMenu.html"); // Redirect to the home page or dashboard
        exit();
    } else {
        // Login failed
        echo "<script>alert('" . htmlspecialchars($result['message']) . "');</script>";
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
