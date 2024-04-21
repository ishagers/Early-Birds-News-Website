<?php
require_once '../rabbitmqphp_example/databaseFunctions.php';  // Correct path as needed

// Enable error reporting for debugging (disable in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);

$db = getDatabaseConnection();  // Get the PDO connection

// Check if both user_id and message are provided
if (isset($_POST['user_id']) && isset($_POST['message'])) {
    $user_id = $_POST['user_id'];
    $message = $_POST['message'];

    // Prepare a statement to insert data
    $stmt = $db->prepare("INSERT INTO chat_messages (user_id, message) VALUES (?, ?)");
    // Bind parameters
    $stmt->bindParam(1, $user_id, PDO::PARAM_INT);
    $stmt->bindParam(2, $message, PDO::PARAM_STR);

    if ($stmt->execute()) {
        echo "Message sent successfully";
    } else {
        echo "Error: " . $stmt->errorInfo()[2];  // Get errorInfo from the PDO statement
    }

    $stmt = null;  // Close the statement
} else {
    echo "User ID or message not provided";
}

$db = null;  // Close the connection
?>

