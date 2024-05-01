<?php
session_start();
require('rabbitmqphp_example/session.php');
require('rabbitmqphp_example/SQLPublish.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function redirectWithError($message, $redirect = 'verify.php')
{
    echo "<script>alert('" . htmlspecialchars($message) . "'); window.location.href = '$redirect';</script>";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['2fa_code'])) {
    $submittedCode = trim($_POST['2fa_code']);
    $username = $_SESSION['username'] ?? ''; // Use null coalescing operator for better error handling

    // Simple server-side validation for the 2FA code format
    if (!preg_match('/^\d{3}$/', $submittedCode)) {
        redirectWithError("Invalid code format. Please try again.");
    } else {
        $queryValues = [
            'type' => '2fa_check',
            'username' => $username,
            '2fa_code' => $submittedCode,
        ];

        $result = publisher($queryValues);

        if ($result && isset($result['2fa'])) {
            $currentDateTime = new DateTime();
            $expireDateTime = new DateTime($result['2faExpire']);

            if ($submittedCode == $result['2fa'] && $currentDateTime < $expireDateTime) {
                // Correct code and not expired - proceed to secure area
                header("Location: rabbitmqphp_example/mainMenu.php");
                exit();
            } else {
                redirectWithError("Invalid code or the code has expired. Please try again.");
            }
        } else {
            // Handle the error if the query did not execute properly
            redirectWithError("There was an error processing your request. Please try again.");
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
