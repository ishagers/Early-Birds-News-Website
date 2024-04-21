<?php

require_once '../rabbitmqphp_example/databaseFunctions.php';

session_start();  // Ensure session is started

$db = getDatabaseConnection();

if (isset($_POST['message'])) {
    $username = $_SESSION['username'];  // Retrieve username from session

    // Debug statement to display the username
    echo "Session Username: " . (isset($_SESSION['username']) ? $_SESSION['username'] : "Not set") . "<br>";

    $message = $_POST['message'];

    // First, validate that the username exists in the users table
    $userCheck = $db->prepare("SELECT id FROM users WHERE username = ?");
    $userCheck->bindParam(1, $username, PDO::PARAM_STR);
    $userCheck->execute();

    if ($user = $userCheck->fetch()) {
        // Username is valid, proceed with inserting the message
        // We need to use the user ID in the messages table to maintain integrity and relationships
        $stmt = $db->prepare("INSERT INTO chat_messages (user_id, message) VALUES (?, ?)");
        $stmt->bindParam(1, $user['id'], PDO::PARAM_INT);  // Use the user's ID from the database
        $stmt->bindParam(2, $message, PDO::PARAM_STR);

        if ($stmt->execute()) {
            echo "Message sent successfully";
        } else {
            echo "Error sending message: " . $stmt->errorInfo()[2];
        }
    } else {
        echo "Error: Username does not exist.";
    }
} else {
    echo "Message not provided";
}

$db = null;  // Close the connection by setting it to null

?>

