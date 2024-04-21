<?php

require_once '../rabbitmqphp_example/databaseFunctions.php';

// Enable error reporting for debugging (turn off error reporting in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);

$db = getDatabaseConnection();

// Prepare a statement to fetch messages with user information
$stmt = $db->prepare("SELECT u.username, m.message, m.timestamp FROM chat_messages m INNER JOIN users u ON m.user_id = u.id ORDER BY m.id DESC LIMIT 20");

$stmt->execute();

// Fetch the results directly from the $stmt object using fetchAll()
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Encode the array to JSON and output it
$json = json_encode($messages);

if (json_last_error() !== JSON_ERROR_NONE) {
    error_log('JSON encode error: ' . json_last_error_msg());
    echo json_encode(["error" => "An error occurred while encoding JSON."]);
    exit;
}

echo $json;

// Properly releasing the resources
$stmt = null; // This effectively closes the statement
$db = null; // This closes the connection

?>

