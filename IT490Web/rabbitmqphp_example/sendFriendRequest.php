<?php

require 'databaseFunctions.php'; 
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}

if (isset($_POST['friend_username'])) {
    $username1 = $_SESSION['username'];
    $username2 = $_POST['friend_username'];

    if ($username1 == $username2) {
        echo "You cannot friend yourself.";
        exit;
    }

    try {
        $conn = getDatabaseConnection();

        // Fetch user IDs based on usernames
        $stmt = $conn->prepare("SELECT id FROM users WHERE username IN (?, ?)");
        $stmt->execute([$username1, $username2]);
        $ids = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($ids) == 2) {
            // Ensure both users are found
            $user_id1 = $ids[0]['id'];
            $user_id2 = $ids[1]['id'];

            // Insert friend request
            $stmt = $conn->prepare("INSERT INTO friends (user_id1, user_id2, status, action_user_id) VALUES (?, ?, 'pending', ?)");
            $stmt->execute([$user_id1, $user_id2, $user_id1]);
            
            if ($stmt->rowCount() > 0) {
                echo "Friend request sent!";
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

