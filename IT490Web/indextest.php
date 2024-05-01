<?php
ob_start();
session_start();
require('rabbitmqphp_example/databaseFunctions.php'); // Database connection and utility functions
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function doStoreAndSendVerification($username)
{
    $userInfoResponse = getUserInfoByUsername($username);
    if (!$userInfoResponse['status']) {
        return ["returnCode" => '1', 'message' => $userInfoResponse['message']];
    }

    $userInfo = $userInfoResponse['data'];
    if (!isset($userInfo['email'])) {
        echo "Email key not found. Check the database query and result.";
        return;
    }
    $userEmail = $userInfo['email'];
    $storeResult = storeVerificationCode($username);
    if (!$storeResult['status']) {
        return ["returnCode" => '1', 'message' => "Failed to store verification code"];
    }

    $verificationCode = $storeResult['code'];
    $emailResult = sendVerificationEmail($userEmail, $verificationCode);
    if ($emailResult['status']) {
        return ["returnCode" => '0', 'message' => "Verification code sent to user's email"];
    } else {
        return ["returnCode" => '1', 'message' => "Failed to send email: " . $emailResult['message']];
    }
}

function doTwoFactorAuthCheck($username, $submittedCode)
{
    $userInfoResponse = getUserInfoByUsername($username);
    if (!$userInfoResponse['status']) {
        return ["returnCode" => '1', 'message' => $userInfoResponse['message']];
    }

    $userInfo = $userInfoResponse['data'];
    if (!isset($userInfo['2fa'], $userInfo['2faExpire'])) {
        return ["returnCode" => '1', 'message' => "2FA details not found"];
    }

    $currentDateTime = new DateTime();
    $expireDateTime = new DateTime($userInfo['2faExpire']);
    if ($userInfo['2fa'] === $submittedCode && $currentDateTime < $expireDateTime) {
        return ["returnCode" => '0', 'message' => "2FA verification successful"];
    } else {
        return ["returnCode" => '1', 'message' => "Invalid 2FA code or code has expired"];
    }
}

if (!empty($_POST['username']) && !empty($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Assume a function for verifying username and password
    $loginResult = login($username, $password);

    if ($loginResult) {
        $_SESSION['username'] = $username;
        // Assume we fetch user_id or similar unique identifier from DB
        $_SESSION['user_id'] = getUserID($username);

        $verificationResult = doStoreAndSendVerification($username);
        if ($verificationResult && $verificationResult['returnCode'] == '0') {
            header("Location: verify.php");
            exit();
        } else {
            echo "<script>alert('{$verificationResult['message']}');</script>";
        }
    } else {
        echo "<script>alert('Login failed. Please try again.');</script>";
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
                <a href="createAccount.php">Create Account</a>
            </p>
        </form>
    </div>
</body>
</html>
