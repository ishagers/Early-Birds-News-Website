#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
require_once('createAccountSQL.php'); // Ensure this file has the createUser function

function doLogin($username, $password)
{
    // Your login logic here
    return true;
}

function doCreateAccount($name, $email, $username, $hashedPassword)
{
    createUser($name, $username, $email, $hashedPassword, 'defaultRole'); 
    return true; 
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
            return doLogin($request['username'], $request['password']);
        case "create_account":
            // Ensure the correct parameters are passed to doCreateAccount
            return doCreateAccount($request['name'], $request['email'], $request['username'], $request['password']);
    }
    return array("returnCode" => '0', 'message' => "Server received request and processed");
}

$server = new rabbitMQServer("testRabbitMQ.ini", "testServer");

echo "testRabbitMQServer BEGIN" . PHP_EOL;
$server->process_requests('requestProcessor');
echo "testRabbitMQServer END" . PHP_EOL;
?>
