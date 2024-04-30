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
        'password' => $_POST['password'],
    ];

    $result = publisher($queryValues);

    if ($result && $result['returnCode'] == '0') {
        // Login successful
        $_SESSION['username'] = $_POST['username'];  // Assuming you store username in session after successful login
        $queryValues = [
            'type' => 'store_and_send_verification',
            'username' => $_POST['username'],
        ];
        $verificationResult = publisher($queryValues);
        
        if ($verificationResult && $verificationResult['returnCode'] == '0') {
            header("Location: verify.php"); // Server-side redirection
            exit();
        } else {
            $errorMessage = isset($verificationResult['message']) ? $verificationResult['message'] : "Verification process failed.";
            echo "<script>alert('" . htmlspecialchars($errorMessage) . "');</script>";
        }
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


