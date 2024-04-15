<?php
require 'databaseFunctions.php'; 
session_start();

if (!isset($_SESSION['username'])) {
    echo "You must be logged in to send a friend request.";
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

    // Call the function and handle the return
    $result = sendFriendRequest($conn, $username1, $username2);
    if ($result['status']) {
        echo $result['message'];
    } else {
        echo $result['message'];
    }
} else {
    echo "Invalid request.";
}
?>

