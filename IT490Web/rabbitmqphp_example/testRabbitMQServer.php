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
