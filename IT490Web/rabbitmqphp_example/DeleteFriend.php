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

    // Assuming deleteFriend function exists and works correctly
    $result = deleteFriend($conn, $currentUsername, $friendUsername);

    if ($result['success']) {
        $_SESSION['message'] = "Friend deleted successfully: " . $result['message'];
        header('Location: accountPreferences.php?friendsUpdated=true'); // Redirect to refresh friend list
    } else {
        $_SESSION['message'] = "Failed to delete friend: " . $result['message'];
        header('Location: accountPreferences.php'); // Redirect without updating friend list
    }
    exit;
}

?>

