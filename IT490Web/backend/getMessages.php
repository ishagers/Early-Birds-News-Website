<?php

require_once '../rabbitmqphp_example/databaseFunctions.php';

// Disable error display, log errors instead
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/your/error.log'); // Change this to your actual log file path

// Prepare a statement to fetch messages with user information
$stmt = $db->prepare("SELECT u.username, m.message, m.timestamp FROM chat_messages m INNER JOIN users u ON m.user_id = u.id ORDER BY m.id DESC LIMIT 20");
$stmt->execute();
$result = $stmt->get_result();

$messages = array();
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

// Encode the array to JSON and output it
$json = json_encode($messages);
if (json_last_error() !== JSON_ERROR_NONE) {
    error_log('JSON encode error: ' . json_last_error_msg());
    // Provide a generic error message or handle the error appropriately
    echo json_encode(["error" => "An error occurred while encoding JSON."]);
    exit;
}

echo $json;

$stmt->close();
$db->close();
?>

