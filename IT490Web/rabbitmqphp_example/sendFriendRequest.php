<?php
require 'databaseFunctions.php'; 
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: ../index.php');
    exit;
}

if (isset($_POST['friend_username'])) {
    $username1 = $_SESSION['username'];
    $username2 = $_POST['friend_username'];

    if ($username1 === $username2) {
        echo "You cannot send a friend request to yourself.";
        exit;
    }

    $conn = getDatabaseConnection();

    try {
        // Fetch user IDs based on usernames
        $stmt = $conn->prepare("SELECT username, id FROM users WHERE username IN (?, ?)");
        $stmt->execute([$username1, $username2]);
        $results = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        if (count($results) === 2) {

            $user_id1 = $results[$username1];
            $user_id2 = $results[$username2];

            // Prepare and execute the insertion of the friend request
            $stmt = $conn->prepare("INSERT INTO friends (user_id1, user_id2, status, action_user_id) VALUES (?, ?, 'pending', ?)");
            $stmt->execute([$user_id1, $user_id2, $user_id1]);

            if ($stmt->rowCount() > 0) {
                echo "Friend request sent successfully!";
            } else {
                echo "Failed to send friend request.";
            }
        } else {
            echo "One or both users not found.";
        }
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
    }
} else {
    echo "Invalid request.";
}
?>

