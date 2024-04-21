<?php

require_once '../rabbitmqphp_example/databaseFunctions.php';

session_start();  // Ensure session is started

$db = getDatabaseConnection();

if (isset($_POST['message'])) {
    $user_id = $_SESSION['user_id'];  // Retrieve user ID from session

    // Debug statement to display the user ID
    echo "Session User ID: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : "Not set") . "<br>";

    $message = $_POST['message'];

    // First, validate that the user_id exists in the users table
    $userCheck = $db->prepare("SELECT id FROM users WHERE id = ?");
    $userCheck->bindParam(1, $user_id, PDO::PARAM_INT);
    $userCheck->execute();

    if ($userCheck->fetch()) {
        // User ID is valid, proceed with inserting the message
        $stmt = $db->prepare("INSERT INTO chat_messages (user_id, message) VALUES (?, ?)");
        $stmt->bindParam(1, $user_id, PDO::PARAM_INT);
        $stmt->bindParam(2, $message, PDO::PARAM_STR);

        if ($stmt->execute()) {
            echo "Message sent successfully";
        } else {
            echo "Error sending message: " . $stmt->errorInfo()[2];
        }
    } else {
        echo "Error: User ID does not exist.";
    }
} else {
    echo "Message not provided";
}

$db = null;  // Close the connection by setting it to null

?>

