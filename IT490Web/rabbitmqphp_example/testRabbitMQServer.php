#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
require_once('databaseFunctions.php');

function doCreateComment($articleId, $content, $username)
{
    // Assuming login function returns an associative array with 'status' and 'message'
    $result = submitComment($articleId, $content, $username);

    if ($result['status']) {
        // Login successful
        return ["returnCode" => '0', 'message' => "Saved Comment"];
    } else {
        // Login failed
        return ["returnCode" => '1', 'message' => "Error"];
    }
}
function doCreateArticle($title, $content,$author)
{
    // Assuming login function returns an associative array with 'status' and 'message'
    $result = createArticle($title, $content, $author);

    if ($result['status']) {
        // Login successful
        return ["returnCode" => '0', 'message' => "Saved Article"];
    } else {
        // Login failed
        return ["returnCode" => '1', 'message' => "return 1 Error"];
    }
}
function doLogin($username, $password)
{
    // Assuming login function returns an associative array with 'status' and 'message'
    $result = login($username, $password);

    if ($result['status']) {
        // Login successful
        return ["returnCode" => '0', 'message' => "Login successful"];
    } else {
        // Login failed
        return ["returnCode" => '1', 'message' => "Invalid username or password"];
    }
}

function doStoreAndSendVerification($username)
{
    $userInfoResponse = getUserInfoByUsername($username);

    if (!$userInfoResponse['status']) {
        return ["returnCode" => '1', 'message' => $userInfoResponse['message']];
    }

    // Now we extract the 'data' since we know the status was true
    $userInfo = $userInfoResponse['data'];

    if (!isset($userInfo['email'])) {
        // Handle the error, maybe the user does not exist or the query failed
        echo "Email key not found. Check the database query and result.";
        return; // or continue depending on your logic
    }
    $userEmail = $userInfo['email'];

    // Store the verification code in the database
    $storeResult = storeVerificationCode($username); // Ensure this function returns both 'status' and 'code'

    if (!$storeResult['status']) {
        return ["returnCode" => '1', 'message' => "Failed to store verification code"];
    }

    $verificationCode = $storeResult['code']; // Get the verification code from the store result

    // Send the verification code to the user's email
    $emailResult = sendVerificationEmail($userEmail, $verificationCode); // Ensure 'sendVerificationEmail' function is defined and included

    if ($emailResult['status']) {
        return ["returnCode" => '0', 'message' => "Verification code sent to user's email"];
    } else {
        return ["returnCode" => '1', 'message' => "Failed to send email: " . $emailResult['message']];
    }
}

function doCreateAccount($name, $email, $username, $hashedPassword)
{
    $result = createUser($name, $username, $email, $hashedPassword, 'defaultRole');
    if ($result['status']) {
        // Account creation successful
        return ["returnCode" => '0', 'message' => "Account created successfully\n"]; // Use generic success message or $result['message']
    } else {
        // Account creation failed
        return ["returnCode" => '1', 'message' => $result['message']]; // Use $result['message'] directly
    }
}

function doTwoFactorAuthCheck($username, $submittedCode)
{
    $userInfo = getUserInfoByUsername($username);

    if (!$userInfo) {
        return ["returnCode" => '1', 'message' => "User not found"];
    }

    $currentDateTime = new DateTime();
    $expireDateTime = new DateTime($userInfo['2faExpire']);

    if ($userInfo['2fa'] === $submittedCode && $currentDateTime < $expireDateTime) {
        return ["returnCode" => '0', 'message' => "2FA verification successful"];
    } else {
        return ["returnCode" => '1', 'message' => "Invalid 2FA code or code has expired"];
    }
}

function requestProcessor($request)
{
    echo "received request" . PHP_EOL;
    var_dump($request);
    if (!isset($request['type'])) {
        return "ERROR: unsupported message type";
    }
    switch ($request['type']) {
        case "login":
            echo "You have succesfully logged in.\n ";
            return doLogin($request['username'], $request['password']);

        case "store_and_send_verification":
            // You need to pass the username and the verification code here
            return doStoreAndSendVerification($request['username']); // Make sure these are the correct keys from the RMQ message

        case "2fa_check":
            echo "Performing 2FA check\n";
            return doTwoFactorAuthCheck($request['username'], $request['2fa_code']);

        case "create_account":
            echo "You have succesfully created an account! \n";
            return doCreateAccount($request['name'], $request['email'], $request['username'], $request['password']);

        case "create_article":
            echo "Article Created";
            return doCreateArticle($request['title'], $request['content'], $request['author']);

        case "create_comment":
            echo "Comment made";
            return doCreateComment($request['articleId'], $request['content'], $request['username']);
    }
    return array("returnCode" => '0', 'message' => "Server received request and processed");
}

$server = new rabbitMQServer("testRabbitMQ.ini", "testServer");

echo "testRabbitMQServer BEGIN" . PHP_EOL;
$server->process_requests('requestProcessor');
echo "testRabbitMQServer END" . PHP_EOL;
?>
