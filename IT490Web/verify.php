<?php
session_start();
require('rabbitmqphp_example/databaseFunctions.php'); // Make sure this file includes your database connection and necessary functions
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function redirectWithError($message, $redirect = 'verify.php')
{
    echo "<script>alert('" . htmlspecialchars($message) . "'); window.location.href = '$redirect';</script>";
    exit();
}

function checkTwoFactorCode($username, $submittedCode)
{
    $conn = getDatabaseConnection();
    $stmt = $conn->prepare("SELECT two_fa_code, two_fa_expire FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $stmt->rowCount() > 0) {
        $currentDateTime = new DateTime();
        $expireDateTime = new DateTime($user['two_fa_expire']);

        if ($submittedCode == $user['two_fa_code'] && $currentDateTime < $expireDateTime) {
            return ['status' => true];
        } else {
            return ['status' => false, 'message' => "Invalid code or the code has expired"];
        }
    }
    return ['status' => false, 'message' => "No 2FA details found"];
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['2fa_code'])) {
    $submittedCode = trim($_POST['2fa_code']);
    $username = $_SESSION['username'] ?? ''; // Use null coalescing operator for better error handling

    if (!preg_match('/^\d{3}$/', $submittedCode)) {
        redirectWithError("Invalid code format. Please try again.");
    } else {
        $result = checkTwoFactorCode($username, $submittedCode);

        if ($result['status']) {
            header("Location: rabbitmqphp_example/mainMenu.php");
            exit();
        } else {
            redirectWithError($result['message']);
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Two-Factor Authentication</title>
    <link rel="stylesheet" href="routes/styles.css" />
</head>
<body>
    <div class="container">
        <div class="title">Enter your verification code</div>
        <form method="post" action="verify.php">
            <p>
                <label for="2fa_code">Verification Code</label>
                <input type="text" id="2fa_code" name="2fa_code" required />
            </p>
            <p>
                <button type="submit">Verify</button>
            </p>
        </form>
    </div>
</body>
</html>
