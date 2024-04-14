<?php
require 'databaseFunctions.php'; 
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (isset($_POST['friend_id'])) {
    $user_id1 = $_SESSION['user_id'];
    $user_id2 = $_POST['friend_id'];

    if ($user_id1 == $user_id2) {
        echo "You cannot friend yourself.";
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO friends (user_id1, user_id2, status, action_user_id) VALUES (?, ?, 'pending', ?)");
    try {
        $stmt->execute([$user_id1, $user_id2, $user_id1]);
        if ($stmt->rowCount() > 0) {
            echo "Friend request sent!";
        } else {
            echo "Failed to send friend request.";
        }
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
    }
} else {
    echo "Invalid request.";
}
?>

