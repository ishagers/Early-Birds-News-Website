<?php
require_once '../rabbitmqphp_example/databaseFunctions.php';  // Updated path to databaseFunctions.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if both user_id and message are provided
if (isset($_POST['user_id']) && isset($_POST['message'])) {
    $user_id = $_POST['user_id'];
    $message = $_POST['message'];

    // Prepare a statement to insert data
    $stmt = $db->prepare("INSERT INTO chat_messages (user_id, message) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $message);
    if ($stmt->execute()) {
        echo "Message sent successfully";
    } else {
        echo "Error: " . $stmt->error;
        error_log('Insert message failed: ' . $stmt->error); // Log error to server log
    }
    $stmt->close();
} else {
    echo "User ID or message not provided";
    error_log('User ID or message not provided in sendMessage.php');
}
$db->close();
?>
