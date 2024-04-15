<?php

require 'databaseFunctions.php';
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: ../index.php');
    exit;
}

if (isset($_POST['deleteFriendUsername'])) {
    $conn = getDatabaseConnection();
    $currentUsername = $_SESSION['username'];
    $friendUsername = $_POST['deleteFriendUsername'];

    $result = deleteFriend($conn, $currentUsername, $friendUsername);

    if ($result['success']) {
        $_SESSION['message'] = "Friend deleted successfully: " . $result['message'];
    } else {
        $_SESSION['message'] = "Failed to delete friend: " . $result['message'];
    }

    // Ensure this is the correct relative or absolute URL
    header('Location: accountPreferences.php?friendsUpdated=true');
    exit;
}

// Optionally add an else clause to handle unexpected accesses
else {
    header('Location: accountPreferences.php');
    exit;
}
?>

