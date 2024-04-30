<?php
session_start();
require('rabbitmqphp_example/session.php');
require('rabbitmqphp_example/SQLPublish.php');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!empty($_POST['username']) && !empty($_POST['password'])) {
    $queryValues = [
        'type' => 'login',
        'username' => $_POST['username'],
        'password' => $_POST['password'],
    ];

    $result = publisher($queryValues);

    if ($result && $result['returnCode'] == '0') {
        $_SESSION['username'] = $_POST['username'];

        $queryValues = [
            'type' => 'store_and_send_verification',
            'username' => $_POST['username'],
        ];
        $verificationResult = publisher($queryValues);

        if ($verificationResult && $verificationResult['returnCode'] == '0') {
            // Use header to redirect to verify.php page
            header('Location: verify.php');
            exit();
        } else {
            $errorMessage = isset($verificationResult['message']) ? $verificationResult['message'] : "Verification process failed.";
            echo "<script>alert('" . htmlspecialchars($errorMessage) . "');</script>";
        }
    } else {
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
