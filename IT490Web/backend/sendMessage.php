<?php
require_once '../rabbitmqphp_example/databaseFunctions.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);

$db = getDatabaseConnection();

if (isset($_POST['user_id']) && isset($_POST['message'])) {
    $user_id = $_POST['user_id'];
    $message = $_POST['message'];

    // Check if user_id exists in the users table
    $userCheck = $db->prepare("SELECT id FROM users WHERE id = ?");
    $userCheck->bindParam(1, $user_id, PDO::PARAM_INT);
    $userCheck->execute();

    if ($userCheck->fetch()) {
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

    $stmt = null;
} else {
    echo "User ID or message not provided";
}

$db = null;

?>

