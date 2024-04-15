<?php

require 'databaseFunctions.php';
session_start();

if (!isset($_SESSION['username'])) {
    // Redirect to the login page, adjust path if necessary
    header('Location: ../index.php');
    exit;
}

if (isset($_POST['deleteFriendUsername'])) {
    $conn = getDatabaseConnection();
    $currentUsername = $_SESSION['username'];
    $friendUsername = $_POST['deleteFriendUsername'];

    // Assuming deleteFriend function exists and works correctly
    $result = deleteFriend($conn, $currentUsername, $friendUsername);

    if ($result['success']) {
        $_SESSION['message'] = "Friend deleted successfully: " . $result['message'];
        // Make sure this path is correct and reachable
        header('Location: accountPreferences.php?friendsUpdated=true');
    } else {
        $_SESSION['message'] = "Failed to delete friend: " . $result['message'];
        // Make sure this path is correct and reachable
        header('Location: accountPreferences.php');
    }
    exit;
}

?>

