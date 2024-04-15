<?php

require 'databaseFunctions.php'; 
session_start();

if (!isset($_SESSION['username'])) {
    $_SESSION['message'] = "You must be logged in to send a friend request.";
    header('Location: login.php'); // Adjust the redirection to your login page as necessary
    exit;
}

if (isset($_POST['friend_username'])) {
    $username1 = $_SESSION['username'];
    $username2 = $_POST['friend_username'];

    if ($username1 === $username2) {
        $_SESSION['message'] = "You cannot send a friend request to yourself.";
        header('Location: accountPreferences.php');
        exit;
    }

    $conn = getDatabaseConnection();

    // Call the function and handle the return
    $result = sendFriendRequest($conn, $username1, $username2);
    $_SESSION['message'] = $result['message'];  // Store the result message in session to display in accountPreferences.php

    header('Location: accountPreferences.php'); // Redirect back to account preferences page
    exit;
} else {
    $_SESSION['message'] = "Invalid request.";
    header('Location: accountPreferences.php'); // Redirect back if accessed improperly
    exit;
}
?>

